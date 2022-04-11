<?php
include('../include/db2.inc.php');

$lat1=floatval($_GET['lat1']);
$lng1=floatval($_GET['lng1']);
$lat2=floatval($_GET['lat2']);
$lng2=floatval($_GET['lng2']);

$komma = "";

?>{
	
	"var1": 2,
	"var2": 4,
"lijnen1":	
{
    "type": "FeatureCollection",
    "features": [
<?php


$query = "	select w.gid, ST_AsGeoJSON(w.the_geom) as geojson, w.pedestrian, w.length_m
from
(SELECT * FROM pgr_dijkstra(
    '
      SELECT gid AS id,
        source,
        target,
        length_m AS cost
      FROM ways
    ',
	
	
(SELECT gid
FROM
  nodes
ORDER BY
  geom <-> st_setsrid(st_point($1,$2),4326)
LIMIT 1),
 
 
 (SELECT gid
FROM
  nodes
ORDER BY
  geom <-> st_setsrid(st_point($3,$4),4326)
LIMIT 1),
    directed := false))as foo,
ways w
where w.gid = foo.edge
			";
			
			//echo $query;

$result = @pg_query_params($GLOBALS['dbconn'], $query, array($lng1,$lat1,$lng2,$lat2)) 
		or die ("error1: ".pg_last_error($GLOBALS['dbconn']));


    while($var_gegevens = pg_fetch_array($result)) {
		

		
		   echo $komma;
	
       
       echo '
	   {
            "type": "Feature",
            "geometry":'.$var_gegevens['geojson'].',
			"properties":{"pedestrian": "'.$var_gegevens['pedestrian'].'","length_m": '.$var_gegevens['length_m'].'},
            "id": '.$var_gegevens['gid'].'
        }';
    	
      
$komma = "
,
";

    	
    }
	
	
	
	
	
	$komma = "";
	?>

    ]
}
,


"lijnen2":	
{
    "type": "FeatureCollection",
    "features": [
<?php


$query = "	select w.gid, ST_AsGeoJSON(w.the_geom) as geojson, w.pedestrian, w.length_m
from
(SELECT * FROM pgr_dijkstra(
    '
      SELECT gid AS id,
        source,
        target,
        (length_m+pedestrian*10000) AS cost
      FROM ways
    ',
	
	
(SELECT gid
FROM
  nodes
ORDER BY
  geom <-> st_setsrid(st_point($1,$2),4326)
LIMIT 1),
 
 
 (SELECT gid
FROM
  nodes
ORDER BY
  geom <-> st_setsrid(st_point($3,$4),4326)
LIMIT 1),
    directed := false))as foo,
ways w
where w.gid = foo.edge
			";
			
			//echo $query;

$result = @pg_query_params($GLOBALS['dbconn'], $query, array($lng1,$lat1,$lng2,$lat2)) 
		or die ("error1: ".pg_last_error($GLOBALS['dbconn']));


    while($var_gegevens = pg_fetch_array($result)) {
		

		
		   echo $komma;
	
       
       echo '
	   {
            "type": "Feature",
            "geometry":'.$var_gegevens['geojson'].',
			"properties":{"pedestrian": "'.$var_gegevens['pedestrian'].'","length_m": '.$var_gegevens['length_m'].'},
            "id": '.$var_gegevens['gid'].'
        }';
    	
      
$komma = "
,
";

    	
    }?>

    ]
}
}