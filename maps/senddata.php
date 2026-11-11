<?php
include "util.inc";
include "httpbasicauth.inc";

#check all values are set
if (!isset($_POST["mapID"])){
    echo "No map id was provided!";
    http_response_code(400);
    exit;
}
$mapID = $_POST["mapID"];

if (!isset($_POST["lat"])){
    echo "No Latitude data was provided!";
    http_response_code(400);
    exit;
}
$lat = $_POST["lat"];

if (!isset($_POST["lng"])){
    echo "No Longitude data was provided!";
    http_response_code(400);
    exit;
}
$lng = $_POST["lng"];

if (!isset($_POST["time"])){
    echo "No timestamp was provided!";
    http_response_code(400);
    exit;
}
$time = $_POST["time"];

#check all values are valid
if (CheckMapID($mapID) == NULL){
    echo "Map ID was invalid!";
    http_response_code(404);
    exit;
}
if (CheckMapOwner($mapID,$userID) == NULL){
    echo "Insufficent permision for this map!";
    http_response_code(403);
    exit;
}

$latArray = explode(",",$lat);
$lngArray = explode(",",$lng);
$timeArray = explode(",",$time);

if (count($latArray) != count($lngArray) || count($latArray) != count($timeArray)) {
    http_response_code(400);
}

for ($x = 0; $x < count($latArray); $x++) {
    if (AddPoint($mapID, $latArray[$x], $lngArray[$x], $timeArray[$x], NULL, NULL) == false) {
        http_response_code(500);
    }
}

?>