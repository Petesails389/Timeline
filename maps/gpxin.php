<?php
require_once('../vendor/autoload.php');
use phpGPX\phpGPX;

include "util.inc";
include "httpbasicauth.inc";

//cheack we can load the gpx correctly...
if (! array_key_exists("gpx", $_FILES)) {     
    header("Location: settings.php");
    exit;
}

if ($_FILES['gpx']['error']) { 
    echo "Error uploading file: ".$_FILES['gpx']['error'];
    exit;
}

$gpx = new phpGPX();
$fileName = $_FILES['gpx']['tmp_name'];

if(file_exists($fileName)) {
	$file = $gpx->load($fileName);
} else {
    http_response_code(500);
    exit;
}

//get the rest of the information
if (!isset($_POST["mapID"])){
    echo "No map id was provided!";
    http_response_code(400);
    exit;
}
$mapID = $_POST["mapID"];

if (CheckMapID($mapID) == NULL){
    var_dump($mapID);
    echo "$mapID Map ID was invalid!";
    //http_response_code(404);
    exit;
}
if (CheckMapOwner($mapID,$userID) == NULL){
    echo "Insufficent permision for this map!";
    http_response_code(403);
    exit;
}

//format points into array
$formatedPoints = [];
foreach($file->tracks as $track) {
    foreach ($track->segments as $segment){
        $points = $segment->points;
        foreach ($points as $point){
            $lat = $point->latitude;
            $lng = $point->longitude;
            $time = $point->time->getTimestamp();
            $elevation = $point->elevation;

            array_push($formatedPoints, [$lat, $lng, $time, NULL, $elevation]);
        }
    }
}

AddPoints($mapID, $formatedPoints);

header("Location: settings.php?mapID=$mapID");
?>