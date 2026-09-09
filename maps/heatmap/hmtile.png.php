<?php
include "../util.inc";
include "../mapinfo.inc";
include "imageprocessing.inc";

$z = $_GET["z"];
$x = $_GET["x"];
$y = $_GET["y"];

if (!isset($z) || !isset($x) || !isset($y)){
    http_response_code(400);
    exit;
}

// check if file exists and create it if not
$fileName = "/var/www/data/heatmaptiles/$z/$x/$y/$mapID.png";
if (!file_exists($fileName)){
    $fileName = "/var/www/data/heatmaptiles/default.png";
}

//open the file in a binary mode
$tile = fopen($fileName, "rb");

// send the right headers
header("Content-Type: image/png");
header("Content-Length: " . filesize($fileName));

// dump the picture and stop the script
fpassthru($tile);
?>