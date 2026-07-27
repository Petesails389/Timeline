<?php
header('Content-Type: text/javascript');
include "util.inc";
include "mapinfo.inc";

//map info
$mapID = $_GET["mapID"];
$name = GetMapName($mapID);
$markers = GetMarkers($_GET["mapID"]);

$finalRoutes = [];

if (isset($_GET["RAW"])) {
    $points = GetPoints($_GET["mapID"],$day,$duration);
    $finalRoutes = [[[$points[count($points)-1][2],$points[0][2],0],$points]];
} else {
    //get routes on the map
    $routes = GetRoutes($_GET["mapID"],$day,$duration);
    
    foreach ($routes as $route) {
        $routePoints = GetPoints($_GET["mapID"],$route[0],$route[1] - $route[0]);
        $routePoints = limitPoints($routePoints, 5);
        $finalRoute = [$route,$routePoints];
        array_push($finalRoutes, $finalRoute);
    }
}

if (!$permissions["history"]){
    //shuffle points
    shuffle($finalRoutes);
}

//curent location
$last = GetLastLocation($mapID);
$last[2] = Date("Y-m-d H:i",$last[2]);

//combine into array
$result = array(
    "history"=>$permissions["history"],
    "name"=>$name,
    "day"=>$day,
    "duration"=>$duration,
    "routes"=>$finalRoutes,
    "home"=>GetCenter($mapID)
);
if ($permissions["history"]){$result["markers"] = $markers;}
if ($permissions["current"]){$result["last"] = $last;}

//close db to save memory before encoding
$db->close();

//json encode result
echo json_encode($result);
?>