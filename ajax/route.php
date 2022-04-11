<?php
include('../include/db.inc.php');

$command = 'pwd';
$lat1=floatval($_GET['lat1']);
$lng1=floatval($_GET['lng1']);
$lat2=floatval($_GET['lat2']);
$lng2=floatval($_GET['lng2']);
$command = '/home/laudcock/anaconda3/envs/geo_env/bin/python3 /home/laudcock/code_3.py '.$lng1.' '.$lat1.' '.$lng2.' '.$lat2.' False mean';
//echo $command;
$output = shell_exec($command);
//echo $output;
$output = trim($output);
$output = explode(PHP_EOL,$output);

$instructies = explode('#',$output[sizeof($output)-1]);



$ids1=explode(", ",trim($instructies[2],"[] "));
$ids2=explode(", ",trim($instructies[3],"[] "));
//print_r($instructies);
//print_r($ids1);

$vvalue = 0;
$edges1arr=[];
foreach ($ids1 as $value) {
	if($vvalue >0){
		array_push($edges1arr, ("ARRAY[".$vvalue."::bigint,".$value."::bigint]"));
	}
  $vvalue = $value;
}
$edges1 = implode(',',$edges1arr);


$vvalue = 0;
$edges2arr=[];
foreach ($ids2 as $value) {
	if($vvalue >0){
		array_push($edges2arr, ("ARRAY[".$vvalue."::bigint,".$value."::bigint]"));
	}
  $vvalue = $value;
}
$edges2 = implode(',',$edges2arr);



//print_r($edges1);


$komma = "";

?>{
	
	"var1": <?php echo $instructies[0];?>,
	"var2": <?php echo $instructies[1];?>,
"lijnen1":	
{
    "type": "FeatureCollection",
    "features": [
<?php


$query = '	select ST_AsGeoJSON(geom) as geojson, gid, oneway 
			from aalst_edges
			where ARRAY[u::bigint,v::bigint] in ('.$edges1.')
			  OR ARRAY[v::bigint,u::bigint] in ('.$edges1.')
			';
			
			//echo $query;

$result = @pg_query_params($GLOBALS['dbconn'], $query, array()) 
		or die ("error1: ".pg_last_error($GLOBALS['dbconn']));


    while($var_gegevens = pg_fetch_array($result)) {
		

		
		   echo $komma;
	
       
       echo '
	   {
            "type": "Feature",
            "geometry":'.$var_gegevens['geojson'].',
			"properties":{"oneway": "'.$var_gegevens['oneway'].'"},
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


$query = '	select ST_AsGeoJSON(geom) as geojson, gid, oneway 
			from aalst_edges
			where ARRAY[u::bigint,v::bigint] in ('.$edges2.')
			  OR ARRAY[v::bigint,u::bigint] in ('.$edges2.')
			';

$result = @pg_query_params($GLOBALS['dbconn'], $query, array()) 
		or die ("error2: ".pg_last_error($GLOBALS['dbconn']));


    while($var_gegevens = pg_fetch_array($result)) {
		

		
		   echo $komma;
	
       
       echo '
	   {
            "type": "Feature",
            "geometry":'.$var_gegevens['geojson'].',
			"properties":{"oneway": "'.$var_gegevens['oneway'].'"},
            "id": '.$var_gegevens['gid'].'
        }';
    	
      
$komma = "
,
";

    	
    }?>

    ]
}
}