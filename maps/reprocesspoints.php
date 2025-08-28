<?php
header('Content-Type: text/javascript');
session_start();
session_reset();
include "../accounts/db.php";
include "mapsdb.php";
include "util.inc";

//map info
$mapID = $_GET["mapID"];
$name = GetMapName($mapID);
$markers = GetMarkers($_GET["mapID"]);

//if not the owner don't access this page
if (!$permissions[4]) {
    header("Location: index.php");
    exit;
}

//get all points
$points = GetPoints($_GET["mapID"], NULL, 3155692600);

//process points
//rearange points into $routes and points
$routes = [];
$route = [$points[0]];
$displayPoints = [];
$stoppedTime = 0;

if (count($points) > 0){
    for ($key = 1; $key < count($points); $key ++) {
        //add point to route if still moving
        if (averageSpeed($points[$key], $points[$key - 1]) > 0.1){
            //add point
            if ($permissions[0]) {
                array_push($route,[$points[$key][0],$points[$key][1],$points[$key][2]]);
            } else{
                array_push($route,[$points[$key][0],$points[$key][1],0]);
            }

            $stoppedTime = 0;
            continue;
        }

        //see if we should end the route
        $stoppedTime += $points[$key][2] - $points[$key - 1][2];
        if ($key == count($points)-1 || $stoppedTime > 120) {
            if (count($route) == 1) {
                array_push($displayPoints,[$route[0][0],$route[0][1]]);
                $route = [];
            } else if (count($route) != 0) {
                array_push($routes,$route);
                $route = [];
            }
        }
    }
}
?>