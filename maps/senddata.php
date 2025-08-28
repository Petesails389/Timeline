<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] != "POST"){
    http_response_code(440);
    exit;
}

#check all values are set
if (!isset($_POST["userID"])){
    echo "No userID was provided!";
    http_response_code(440);
    exit;
}
$userID = (int) $_POST["userID"];

if (!isset($_POST["pswd"])){
    echo "No password was provided!";
    http_response_code(440);
    exit;
}
$pswd = $_POST["pswd"];

if (!isset($_POST["mapID"])){
    echo "No map id was provided!";
    http_response_code(440);
    exit;
}
$mapID = $_POST["mapID"];

if (!isset($_POST["lat"])){
    echo "No Latitude data was provided!";
    http_response_code(440);
    exit;
}
$lat = $_POST["lat"];

if (!isset($_POST["lng"])){
    echo "No Longitude data was provided!";
    http_response_code(440);
    exit;
}
$lng = $_POST["lng"];

if (!isset($_POST["time"])){
    echo "No timestamp was provided!";
    http_response_code(440);
    exit;
}
$time = $_POST["time"];

include "mapsdb.php";
include "../accounts/db.php";
#check all values are valid
if (!LoginID($userID,$pswd)){
    echo "Invalid login!";
    http_response_code(440);
    exit;
}
if (CheckMapID($mapID) == NULL){
    echo "Map ID was invalid!";
    http_response_code(440);
    exit;
}
if (CheckMapOwner($mapID,$userID) == NULL){
    echo "Insufficent permision for this map!";
    http_response_code(440);
    exit;
}

AddPoint($mapID, $lat, $lng, $time, NULL, NULL);

?>