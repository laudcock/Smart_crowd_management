<?php
include('../include/db2.inc.php');

$res = curl_init();
$url = 'https://telraam-api.net/v1/reports/traffic_snapshot';
curl_setopt( $res, CURLOPT_URL, $url );
//curl_setopt( $res, CURLOPT_POST, 3 ); 
//curl_setopt( $res, CURLOPT_POSTFIELDS, $fields );
curl_setopt( $res, CURLOPT_RETURNTRANSFER, true ); 
curl_setopt($res, CURLOPT_HTTPHEADER, array(
    "X-Api-Key: GQY2NW1bdYa6e0kZLHqX5aSA5zg9SdMzaGFUNjhJ",
	"Content-Type: application/json",
	"Accept: application/json"
    )); //you can specify multiple custom keys.
curl_setopt($res, CURLOPT_POST, 1);
	curl_setopt($res, CURLOPT_POSTFIELDS,
            "{'time':'live','contents':'minimal','area':'3.58,50.74,4.41,51.52'}");

$result = curl_exec( $res );
$dec = json_decode($result, true);

//echo $result;

  $query = "truncate table telraam";

   $result = @pg_query_params($GLOBALS['dbconn'], $query, array()) 
		or die ("error1: ".pg_last_error($GLOBALS['dbconn']));

		
		
foreach ($dec['features'] as $value) {
	
//print_r($value);
	
  $geojson = json_encode($value['geometry']);

   $pedestrian = json_encode($value['properties']['pedestrian']);
    if ($pedestrian=='""'){$pedestrian=0;}
 
  $query = "insert into telraam(geom, pedestrian) VALUES(ST_GeomFromGeoJSON($1),$2)";

   $result = @pg_query_params($GLOBALS['dbconn'], $query, array($geojson, $pedestrian)) 
		or die ("error1: ".pg_last_error($GLOBALS['dbconn']));
  
}

 $query = "update ways set pedestrian = 0";

   $result = @pg_query_params($GLOBALS['dbconn'], $query, array()) 
		or die ("error1: ".pg_last_error($GLOBALS['dbconn']));


  $query = "update ways set pedestrian = foo.pedestrian
from
(
select f1.tgid, f1.wgid, f1.pedestrian from
(select t.gid tgid, w.gid wgid, t.pedestrian, st_length(st_intersection(st_buffer(t.geom,0.0002), w.the_geom)) l
from telraam t, ways w
where st_intersects(st_buffer(t.geom,0.0002), w.the_geom)
order by tgid, l) as f1,
(select t.gid tgid,  max(st_length(st_intersection(st_buffer(t.geom,0.0002), w.the_geom))) maxl
from telraam t, ways w
where st_intersects(st_buffer(t.geom,0.0002), w.the_geom)
group by tgid)as f2
where f1.l = f2.maxl and f1.tgid = f2.tgid
) as foo
where ways.gid = foo.wgid";

   $result = @pg_query_params($GLOBALS['dbconn'], $query, array()) 
		or die ("error1: ".pg_last_error($GLOBALS['dbconn']));


?>