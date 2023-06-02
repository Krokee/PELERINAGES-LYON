var mymap = L.map('mapEtapes', {
	center: [45.87, 3.27], // Coordinates for the center of the massif central region
	zoom: 5,
	scrollWheelZoom: false
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
   maxZoom: 19,
   attribution: 'Map data © <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
}).addTo(mymap);

