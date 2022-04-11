<?php
$conn_string = "host=localhost port=5432 dbname=crowd user=crowd password=TD2XAPeWuDJUG9dW";
$GLOBALS['dbconn'] = pg_connect($conn_string) or die("Could not connect");
?>