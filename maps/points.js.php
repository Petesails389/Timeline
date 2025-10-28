<?php
header('Content-Type: text/javascript');
include "util.inc";
include "mapinfo.inc";

//map info
$mapID = $_GET["mapID"];
$name = GetMapName($mapID);
$markers = GetMarkers($_GET["mapID"]);

//get routes and points on the map
$points = GetPoints($_GET["mapID"],$day,$duration);
$routes = GetRoutes($_GET["mapID"],$day,$duration);

$finalRoutes = [];

foreach ($routes as $route) {
    $routePoints = GetPoints($_GET["mapID"],$route[1],$route[1]-$route[0]);
    $finalRoute = [$route,$routePoints];
    array_push($finalRoutes, $finalRoute);
}

if (isset($_GET["RAW"])) {
    $finalRoutes = [[[$points[count($points)-1][2],$points[0][2],0],$points]];
}

if (!$permissions[0]){
    //shuffle points
    shuffle($finalRoutes);
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
    "routes"=>$finalRoutes,
);
if ($permissions[0]){$result["markers"] = $markers;}
if ($permissions[1]){$result["last"] = $last;}

//json encode result
echo json_encode($result);
?>