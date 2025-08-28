<?php
include "mapsdb.php";
include "../head.php";

if ($_SERVER["REQUEST_METHOD"] != "POST"){
    header("Location: ../index.php");
    exit;
}

if (!isset($_SESSION['username'])) {
    $_SESSION["redirect"] = $_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING'];
    header("Location: ../accounts/login.php");
    exit;
}

//check it's a valid map
if (!isset($_POST["mapID"])){
    http_response_code(400);
    exit;
}
$mapID = $_POST["mapID"];
if (CheckMapID($mapID) == NULL){
    http_response_code(400);
    exit;
}

//check they own the map
if (!CheckMapOwner($mapID,GetUserID($_SESSION['username']))) {
    http_response_code(403);
    exit;
}

#check all values are set
if (!isset($_POST["lat"])){
    http_response_code(400);
    exit;
}
if (!isset($_POST["lng"])){
    http_response_code(400);
    exit;
}
if (!isset($_POST["icon"])){
    http_response_code(400);
    exit;
}
if (!isset($_POST["name"])){
    http_response_code(400);
    exit;
}

if (isset($_POST["markerID"])){
    if($_POST["submit"] == "Delete") {
        DeleteMarker($_POST["markerID"]);
        header("Location: settings.php?mapID=$mapID");
        exit;
    }
    UpdateMarker($_POST["markerID"], $_POST["lat"], $_POST["lng"], $_POST["name"], $_POST["icon"], $mapID);
    header("Location: settings.php?mapID=$mapID");
    exit;
}


NewMarker($_POST["lat"], $_POST["lng"], $_POST["name"], $_POST["icon"], $mapID);
header("Location: settings.php?mapID=$mapID");