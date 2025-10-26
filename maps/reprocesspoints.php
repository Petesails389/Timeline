<?php
set_time_limit(0);
session_start();
session_reset();
include "mapsdb.php";
include "../accounts/db.php";
include "util.inc";

# forms a set of routes by splitting when current speed drops below threshold
function SplitByCurrentSpeed($points, $threshold = 0.15) {
    //rearange points into routes
    $routes = [];
    $route = [$points[0]];
    $stoppedTime = 0;

    if (count($points) < 0){
        return $route;
    }

    for ($key = 1; $key < count($points); $key ++) {
        $speed = averageSpeed($points[$key], $points[$key - 1]);
        //add point to route if still moving
        if ($speed > $threshold){ 
            //add point
            array_push($route,[$points[$key][0],$points[$key][1],$points[$key][2]]);
            $stoppedTime = 0;
        } else{
            $stoppedTime += $points[$key][2] - $points[$key - 1][2];
        }

        //see if we should end the route
        if ($key == count($points)-1 || $stoppedTime > 300) {
            if (count($route) > 1) {
                array_push($routes,$route);
            }
            $route = [$points[min($key + 1, count($points) - 1)]];
        }
    }
    return $routes;
}

# form a set of routes by splitting if the route rotates too much
function SplitByRotation($points, $rotaionFactor = 3){
    $routes = [];
    $route = [];

    # loop through all points
    while (count($points) > 0){
        array_push($route,array_shift($points));
        if (count($route) > 2) {
            $rotation = totalRouteRotation($route);
            if ($rotation > 360*$rotaionFactor || $rotation < -360*$rotaionFactor){
                array_push($routes, $route);
                $route = [];
            }
        }
    }
    array_push($routes, $route);
    return $routes;
}

# HidePoints by how far has been traveled in 2 min
function HideBy2minDistance($points, $threshold = [170, 60]) {
    $final = [];
    $lastTwoMins = [];
    # loop through all points
    while (count($points) > 0){
        array_push($lastTwoMins,array_shift($points));
    
        if ($lastTwoMins[count($lastTwoMins) - 1][2] - $lastTwoMins[0][2] > 200) {
            $maxDeviation = maxDeviation($lastTwoMins);
            $deviation = distance($lastTwoMins[count($lastTwoMins) - 1], $lastTwoMins[0]);
            array_shift($lastTwoMins);

            if ($maxDeviation < $threshold[0] && $deviation < $threshold[1]) {
                //if final already contains the last two mins then just add the last one
                if (count($final) == 0 || $final[count($final) -1] == $lastTwoMins[count($lastTwoMins) -2]){
                    array_push($final, $lastTwoMins[count($lastTwoMins)-1]);
                }
                else {
                    $final = array_merge($final, $lastTwoMins);
                }
            }
        }
    }
    return $final;
}

//map info
$name = GetMapName($mapID);
$markers = GetMarkers($mapID);

//if not the owner don't access this page
if (!$permissions[4]) {
    header("Location: index.php");
    exit;
}

//adjust duration based on routes in time specified
$routes = GetRoutes($mapID, $day, $duration);
if (count($routes) > 0){
    $first = $routes[0][0];
    $duration = $day-$first;
    
    //clear routes
    ClearRoutes($mapID, $day, $duration);
    ClearPoints($mapID, $day, $duration);
    
}

//get points
$points = GetPoints($mapID, $day, $duration);

//hide points that are too close together
HidePoints($mapID, HideBy2minDistance($points));

//get points again
$points = GetPoints($mapID, $day, $duration);

//initial split
$routesFirstSplit = SplitByCurrentSpeed($points);

//second split
$routesSecondSplit = [];
foreach ($routesFirstSplit as $route){
    $routesSecondSplit = array_merge($routesSecondSplit, SplitByRotation($route));

}

//asign probable activity types to each route and add to DB
foreach ($routesSecondSplit as $route) {
    $averagespeed = averageRouteSpeed($route);
    $routeType = 0;
    switch (true) {
        case $averagespeed < 4.5:
            $routeType = 1;
            break;
        case $averagespeed < 7:
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

// add future route if needed
if ($day > strtotime(date("Y-m-d"))) {
    $lastRoute = $routesSecondSplit[count($routesSecondSplit) - 1];
    $lastPoint = $lastRoute[count($lastRoute) - 1];

    $end = $lastPoint[2];
    $future =  strtotime(date("Y-m-d")) + 604800;
    
    AddRoute($mapID, $end, $future, 0);
}

$num1 = count($routesFirstSplit);
$num2 = count($routesSecondSplit);

echo "Split into $num1 route(s) FIRST!<br>";
echo "Split into $num2 route(s) SECOND!<br>";
?>