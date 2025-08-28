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

//get points on the map
if (!$permissions[1]) {
    $day = min(time()-(8640+3600),$day);// limit veiw to be delayed by an hour
}
$points = GetPoints($_GET["mapID"],$day+86400-$duration,$duration);

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

if (!$permissions[0]){
    //shuffle points
    shuffle($routes);
    shuffle($displayPoints);
}

//curent location
$last = GetLastLocation($mapID);
$last[2] = Date("Y-m-d H:i",$last[2]);

//format $day
$day = Date("Y-m-d",$day);

//combine into array
$result = array(
    "history"=>$permissions[0],
    "name"=>$name,
    "day"=>$day,
    "duration"=>$duration,
    "routes"=>$routes,
    "displayPoints"=>$displayPoints,
);
if ($permissions[0]){$result["markers"] = $markers;}
if ($permissions[1]){$result["last"] = $last;}

//json encode result
echo json_encode($result);
?>