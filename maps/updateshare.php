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
if (!isset($_POST["username"])){
    http_response_code(400);
    exit;
}
if (!isset($_POST["start"])){
    http_response_code(400);
    exit;
}
if (!isset($_POST["end"])){
    http_response_code(400);
    exit;
}
if (!isset($_POST["expires"])){
    http_response_code(400);
    exit;
}

$shareUserID = GetUserID($_POST["username"]);
$heatmap = isset($_POST["heatmap"]);
$live = isset($_POST["live"]);
$startDate = strtotime($_POST["start"]);
$endDate = strtotime($_POST["end"]);
$expires = strtotime($_POST["expires"]);

#check user exists
if ($shareUserID == NULL) {
    header("Location: settings.php?mapID=$mapID");
    exit;
}

if($_POST["submit"] == "Delete") {
    DeleteShare($mapID, $shareUserID);
    header("Location: settings.php?mapID=$mapID");
    exit;
}

UpdateShare($mapID, $shareUserID, !$heatmap, $live, $startDate, $endDate, $expires);
header("Location: settings.php?mapID=$mapID");