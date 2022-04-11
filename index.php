<html>
<head>

 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
   integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
   crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
   integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
   crossorigin=""></script>

   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
   <style>
   
   #map { height: 800px; }
   
   </style>
   
   <title>CROWD</title>
   
</head>

<body>

<div id="status">CLICK ON FIRST POINT</div>
 <div id="map"></div>

 
 <script>
 var lat1 = 0;
 var lat2 = 0;
 var lng1 = 0;
 var lng2 = 0;
 var res1;
 var res2;
 var pnt1;
 var pnt2;
 
 var map = L.map('map').setView([50.93830754222607, 4.038234594807739], 15);
 
 L.tileLayer('https://tiles.ugent.be/osmlight/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);


map.on('click', function(e) {
  if (lat1==0){
	  if(res1){
		  map.removeLayer(res1)
		  map.removeLayer(res2)
		  map.removeLayer(pnt1)
		  map.removeLayer(pnt2)
	  }
	  
	  lat1=e.latlng.lat
	  lng1=e.latlng.lng
	  pnt1=L.marker(e.latlng).addTo(map);
	document.getElementById('status').innerHTML = 'CLICK ON SECOND POINT'
  }else if(lat2==0){
	  lat2=e.latlng.lat
	  lng2=e.latlng.lng
	  pnt2=L.marker(e.latlng).addTo(map);
	  document.getElementById('status').innerHTML = 'ROUTES ARE BEING CALCULATED'

	  
	  
$.getJSON("ajax/route.php?lat1="+lat1+"&lng1="+lng1+"&lat2="+lat2+"&lng2="+lng2)
    .then(function (data) {
		document.getElementById('status').innerHTML = "Difference in crowdedness: "+ data.var1 + " pedestrians" + "<br>difference in distance: "+ data.var2 + "m"
		ped = 0;
		l=0;
        res1=L.geoJson(data.lijnen1, 
		  
		{	
	onEachFeature: function (feature, layer) {
		ped += feature.properties.pedestrian;
		l += feature.properties.length_m;
	},
	style: {
      
         "color": '#19de37',
         "opacity": 1
        }
        }).addTo(map);
		
		console.log(ped)
		console.log(l)
				ped = 0;
						l=0;
        res2=L.geoJson(data.lijnen2, {	
	style: {
      	
	onEachFeature: function (feature, layer) {
		ped += feature.properties.pedestrian;
		l += feature.properties.length_m;
		
	},
         "color": '#ac2419',
         "opacity": 1
        }
        }).addTo(map);
		
		console.log(ped)
		console.log(l)
		
		
    })
    .fail(function(err){
        console.log(err.responseText)
    });
	  
	  
	  lat1 = 0;
	  lat2 = 0;
	  lng1 = 0;
	  lng2 = 0;
	  
	  
  }
})


//''



 
 </script>


</body>
</html>