<?php
set_time_limit(0);
session_start();
session_reset();
include "mapsdb.php";
include "../accounts/db.php";
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

//adjust duration based on routes in time specified
$routes = GetRoutes($_GET["mapID"], $day, $duration);
if (count($routes) > 0){
    $first = $routes[0][0];
    $duration = $day-$first;
    
    //clear routes
    ClearRoutes($mapID, $day, $duration);
}

//get points
$points = GetPoints($_GET["mapID"], $day, $duration);

//rearange points into routes
$routes = [];
$route = [$points[0]];
$displayPoints = [];
$stoppedTime = 0;

if (count($points) > 0){
    for ($key = 1; $key < count($points); $key ++) {

        // ---------------------------------- ADVANCED ROUTE FORMING BETA ----------------------------------------------------
        $speed = averageSpeed($points[$key], $points[$key - 1]);
        //add point to route if still moving
        if ($speed > 0.05){ 
            //add point
            array_push($route,[$points[$key][0],$points[$key][1],$points[$key][2]]);
            $stoppedTime = 0;
        } else{
            $stoppedTime += $points[$key][2] - $points[$key - 1][2];
        }

        // ---------------------------------- SIMPLE ROUTE FORMING ----------------------------------------------------
        // //add point
        // array_push($route,[$points[$key][0],$points[$key][1],$points[$key][2]]);
        // //stopped time 
        // $stoppedTime = $points[min($key + 1, count($points) - 1)][2] - $points[$key][2];

        //see if we should end the route
        if ($key == count($points)-1 || $stoppedTime > 300) {
            if (count($route) > 1) {
                array_push($routes,$route);
            }
            $route = [$points[min($key + 1, count($points) - 1)]];
        }
    }
}

foreach ($routes as $route) {
    $averagespeed = averageRouteSpeed($route);
    $routeType = 0;
    switch (true) {
        case $averagespeed < 4.5:
            $routeType = 1;
            break;
        case $averagespeed < 10:
            $routeType = 2;
            break;
        case $averagespeed < 40:
            $routeType = 3;
            break;
        default:
            $routeType = 4;
            break;
    }
    AddRoute($mapID, $route[0][2], $route[count($route)-1][2], $routeType);
}

$numRoutes = count($routes);
echo "Split into $numRoutes route(s)!";
?>