<?php
require_once('../vendor/autoload.php');
use phpGPX\phpGPX;

include "util.inc";
include "httpbasicauth.inc";

var_dump($_POST);
$mapID = $_POST["mapID"];
if (!isset($mapID)){
    http_response_code(400);
    exit;
}

if (CheckMapID($mapID) == NULL){
    http_response_code(404);
    exit;
}

//get the rest of the information
if (CheckMapOwner($mapID,$userID) == NULL){
    http_response_code(404); //return 404 to hide map IDs from unauthorised users
    exit;
}

//cheack we can load the gpx correctly...
if (! array_key_exists("gpx", $_FILES)) {
    $_SESSION["settingsError"] = "GPX File Could not be loaded correctly";
    header("Location: settings.php?mapID=$mapID");
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