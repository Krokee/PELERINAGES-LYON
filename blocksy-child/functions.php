<?php

if (! defined('WP_DEBUG')) {
	die( 'Direct access forbidden.' );
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
});


// LOG FUNCTION DO NOT ENABLE IT ON PRODUCTION
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
	');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

function register_leaflet_styles() {
	wp_register_style('leaflet', 'https://pelerinageslyon.fr/wp-content/plugins/wp-grid-builder-map-facet/assets/css/vendors/leaflet.css', array(), '1.9.3', 'all');
	wp_enqueue_style('leaflet');
}
add_action('wp_enqueue_scripts', 'register_leaflet_styles');



function register_leaflet_scripts() {
	wp_register_script('leaflet', 'https://pelerinageslyon.fr/wp-content/plugins/wp-grid-builder-map-facet/assets/js/vendors/leaflet.js', array(), '1.9.3', false);
  wp_register_script('leaflet-polyline-decorator', 'https://cdn.jsdelivr.net/npm/leaflet.polyline.decorator@1.6.0/dist/leaflet.polyline.decorator.min.js');
	wp_enqueue_script('leaflet');
  wp_enqueue_script('leaflet-polyline-decorator');
}
add_action('wp_enqueue_scripts', 'register_leaflet_scripts');

function enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_font_awesome');


///////////////////////////////////////// 

function display_map($etapes) {
  ?>
  <div id="map" style="height: 300px;"></div>

  <script>

    // Créer un marqueur personnalisé pour chaque type de point
    var departIcon = L.divIcon({
	  className: 'custom-icon',
	  html: '<i class="fa-solid fa-plane-departure" style="font-size: 26px;"></i>',
	  iconSize: [26, 26],
	  iconAnchor: [26, 26] // [0, 0] = top-left | [26, 0] = top-right | [0, 26] = bottom-left | [26, 26] = bottom-right
	});

	var pointEtapeIcon = L.divIcon({
	  className: 'custom-icon',
	  html: '<i class="fa-solid fa-location-dot" style="font-size: 20px;"></i>',
	  iconSize: [20, 20],
	  iconAnchor: [10, 10] // middle-center for step icon
	});

	var arriveeIcon = L.divIcon({
	  className: 'custom-icon',
	  html: '<i class="fa-solid fa-plane-arrival" style="font-size: 26px;"></i>',
	  iconSize: [26, 26],
	  iconAnchor: [0, 26]
	});

    // Convertir les données PHP en JavaScript
    var etapes = <?php echo json_encode($etapes); ?>;

    // Créer le tableau d'objets L.LatLng pour les limites et l'itinéraire
    var latLngs = etapes.map(function(point) {
      return L.latLng(point.lat, point.lng);
    });

    // Créer la carte avec un emplacement temporaire
    var map = L.map('map', {
      center: [0, 0],
      zoom: 1,
      scrollWheelZoom: false
    });

    // Ajouter une tuile de fond à la carte
    L.tileLayer("https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png", {
      attribution: "Map data &copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> contributors, <a href=\"https://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>",
      maxZoom: 18
    }).addTo(map);

    // Ajouter les marqueurs pour chaque point
	etapes.forEach(function(point, index) {
	  var marker;
	  if (index === 0) {
		marker = L.marker([point.lat, point.lng], { icon: departIcon });
	  } else if (index === etapes.length - 1) {
		marker = L.marker([point.lat, point.lng], { icon: arriveeIcon });
	  } else {
		marker = L.marker([point.lat, point.lng], { icon: pointEtapeIcon });
	  }

	  marker.bindPopup(point.name);
	  marker.addTo(map);
	});

    // Calculer les limites de la carte
    var bounds = L.latLngBounds(latLngs);

    // Calculer le centre des limites
    var center = bounds.getCenter();

    // Calculer le niveau de zoom approprié
    var zoom = map.getBoundsZoom(bounds);

    // Modifier la carte avec le centre et le niveau de zoom corrects
    map.setView(center, zoom);
	
	// Obtenir les coordonnées du premier point d'étape
    var startPoint = latLngs[0];

    // Décaler des coordonnées du premier point d'étape pour ajouter une marge si nécessaire
    var offset = 0; // -0.001 Ajustez la valeur de décalage selon vos besoins
    var startPointWithOffset = L.latLng(startPoint.lat + offset, startPoint.lng);

    // Créer un tableau d'itinéraire avec les coordonnées décalées
    var itineraryLatLngs = [startPointWithOffset].concat(latLngs.slice(1));

    
    // Créer la polyligne pour l'itinéraire -> ligne seule
    var itinerary = L.polyline(itineraryLatLngs, { color: '#8ed1fc', weight: 5 }).addTo(map);

    // Ajouter les flèches à la polyligne de l'itinéraire
    var arrowOffset = 15; // Décalage des flèches par rapport à la ligne
    var arrowHead = L.polylineDecorator(itinerary, {
      patterns: [
        { offset: '50%', repeat: 0, symbol: L.Symbol.arrowHead({ pixelSize: 10, polygon: false, pathOptions: { stroke: true, color: '#8ed1fc', weight: 2 } }) }
      ]
    }).addTo(map);

  </script>
  <?php
}

function create_map($etapes) {
  $etapes_inverse = array_reverse($etapes);
  ob_start();
  display_map($etapes_inverse);
  $output = ob_get_clean();
  return $output;
}
