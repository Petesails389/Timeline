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
        $routePoints = GetPoints($_GET["mapID"],$route[1],$route[1]-$route[0]);
        $finalRoute = [$route,$routePoints];
        array_push($finalRoutes, $finalRoute);
    }
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
    "home"=>GetCenter($mapID)
);
if ($permissions[0]){$result["markers"] = $markers;}
if ($permissions[1]){$result["last"] = $last;}

//close db to save memory before encoding
$db->close();

//json encode result
echo json_encode($result);
?>