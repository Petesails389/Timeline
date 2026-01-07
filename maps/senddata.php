<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] != "POST"){
    http_response_code(405);
    exit;
}

include "mapsdb.php";
include "../accounts/db.php";

#authenticate request
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'You are not authenticated to access this page at this time';

$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

if (!Login($username, $password)){
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'You are not authenticated to access this page at this time';
}

$userID = GetUserID($username);

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

AddPoint($mapID, $lat, $lng, $time, NULL, NULL);

?>