<?php

function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $lonDelta = $lonTo - $lonFrom;
    $a = pow(cos($latTo) * sin($lonDelta), 2) +
    pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
    $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

    $angle = atan2(sqrt($a), $b);
    return $angle * $earthRadius;
}

function getRhumbLineBearing($lat1, $lon1, $lat2, $lon2) {
  //difference in longitudinal coordinates
  $dLon = deg2rad($lon2) - deg2rad($lon1);

  //difference in the phi of latitudinal coordinates
  $dPhi = log(tan(deg2rad($lat2) / 2 + pi() / 4) / tan(deg2rad($lat1) / 2 + pi() / 4));

  //we need to recalculate $dLon if it is greater than pi
  if(abs($dLon) > pi()) {
    if($dLon > 0) {
      $dLon = (2 * pi() - $dLon) * -1;
    }
    else {
      $dLon = 2 * pi() + $dLon;
    }
  }
  //return the angle, normalized
  return (rad2deg(atan2($dLon, $dPhi)) + 360) % 360;
}

function bearing($p1, $p2){
    return getRhumbLineBearing($p1[0],$p1[1],$p2[0],$p2[1]);
}

// returns an angle between -180 and 180
function angleNormalise($angle){
    while ($angle < -180) {
        $angle = $angle + 360;
    }
    while ($angle > 180) {
        $angle = $angle - 360;
    }
    return $angle;
}

function totalRouteRotation($route){
    if (count($route) <= 2) {
        return 0;
    }
    $totalRotation = 0;
    $lastbearing = bearing($route[0],$route[1]);
    for ($key = 2; $key < count($route); $key ++) {
        $bearing = bearing($route[$key -1], $route[$key]);
        $difference = $lastbearing-$bearing;
        $diiference = angleNormalise($difference);
        $totalRotation += $difference;
        $lastbearing = $bearing;
    }

    return $totalRotation;
}

function angularSpeed($p1, $p2, $p3){
    if ($p1[2] - $p3[2] == 0) {
        return 0;
    }

    $a1 = angleNormalise(bearing($p1, $p2));
    $a2 = angleNormalise(bearing($p2, $p3));

    return  ($a1 - $a2) / abs($p1[2] - $p3[2]); 
}

function distance($p1, $p2){
    return vincentyGreatCircleDistance($p1[0],$p1[1],$p2[0],$p2[1]);
}

//returns the average speed between 2 points in m/s
function averageSpeed($p1, $p2) {
    if ($p1[2] - $p2[2] == 0) {
        return 0;
    }
    return vincentyGreatCircleDistance($p1[0],$p1[1],$p2[0],$p2[1]) / abs($p1[2] - $p2[2]);
}

function totalRouteDistance($route){
    if (count($route) <= 1){
        return 0;
    }
    $distance = 0;
    for ($key = 1; $key < count($route); $key ++) {
        $p1 = $route[$key -1];
        $p2 = $route[$key];
        $distance += vincentyGreatCircleDistance($p1[0],$p1[1],$p2[0],$p2[1]);
    }
    return $distance;
}

function averageRouteSpeed($route){
    if (count($route) == 0) {
        return 0;
    }
    if (($route[count($route) - 1][2] - $route[0][2]) == 0) {
        return 0;
    }
    return totalRouteDistance($route)  / ($route[count($route) - 1][2] - $route[0][2]);
}

function maxDeviation($route){
    if (count($route) <= 1){
        return 0;
    }
    $distance = 0;
    for ($key = 0; $key < count($route); $key ++) {
        for ($key2 = $key + 1; $key2 < count($route); $key2 ++) {
            $p1 = $route[$key];
            $p2 = $route[$key2];
            $distance = max($distance, vincentyGreatCircleDistance($p1[0],$p1[1],$p2[0],$p2[1]));
        }
    }
    return $distance;
}

// function to check if point q lies on line segment 'pr'
function onSegment($p, $q, $r){
    return ($q[0] <= max($p[0], $r[0]) && $q[0] >= min($p[0], $r[0]) && $q[1] <= max($p[1], $r[1]) && $q[1] >= min($p[1], $r[1]));
}

// function to find orientation of ordered triplet (p, q, r)
// 0 --> p, q and r are collinear
// 1 --> Clockwise
// 2 --> Counterclockwise
function orientation($p, $q, $r){
    $val = ($q[1] - $p[1]) * ($r[0] - $q[0]) - ($q[0] - $p[0]) * ($r[1] - $q[1]);

    # collinear
    if ($val == 0){
        return 0;
    }

    # clock or counterclock wise
    # 1 for clockwise, 2 for counterclockwise
    if (val > 0) {return 1; } else {return 2;}
}


# function to check if two line segments intersect
function doIntersect($points){
    # find the four orientations needed
    # for general and special cases
    $o1 = orientation($points[0][0], $points[0][1], $points[1][0]);
    $o2 = orientation($points[0][0], $points[0][1], $points[1][1]);
    $o3 = orientation($points[1][0], $points[1][1], $points[0][0]);
    $o4 = orientation($points[1][0], $points[1][1], $points[0][1]);

    # general case
    if ($o1 != $o2 && $o3 != $o4){
        return True;
    }

    # special cases
    # p1, q1 and p2 are collinear and p2 lies on segment p1q1
    if ($o1 == 0 && onSegment($points[0][0], $points[1][0], $points[0][1])){
        return True;
    }

    # p1, q1 and q2 are collinear and q2 lies on segment p1q1
    if ($o2 == 0 && onSegment($point[0][0], $point[1][1], $point[0][1])){
        return True;
    }

    # p2, q2 and p1 are collinear and p1 lies on segment p2q2
    if ($o3 == 0 && onSegment($point[1][0], $point[0][0], $point[1][1])){
        return True;
    }

    # p2, q2 and q1 are collinear and q1 lies on segment p2q2 
    if ($o4 == 0 && onSegment($point[1][0], $point[0][1], $point[1][1])){
        return True;
    }

    return False;

}

// $points = [[[1, 1], [10, 1]], [[1, 2], [10, 2]]]
//     if doIntersect($points):
//         print("Yes")
//     else:
//         print("No")

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

//reporcesses points and returns in html (with <br>) or plain text (with \n)
function reprocessPoints($mapID, $day, $duration, $html = True){
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

    if(count($points) < 2){
        if ($html){
            return "Not enough points to process<br>";
        } else {
            return "Not enough points to process\n";
        }
    }

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
    if ($day >= strtotime(date("Y-m-d"))) {
        $lastRoute = $routesSecondSplit[count($routesSecondSplit) - 1];
        $lastPoint = $lastRoute[count($lastRoute) - 1];

        $now = time();
        $future =  strtotime(date("Y-m-d")) + 604800;
        
        AddRoute($mapID, $now, $future, 0);
    }

    $num1 = count($routesFirstSplit);
    $num2 = count($routesSecondSplit);

    if ($html){
        return "Split into $num1 route(s) FIRST!<br>Split into $num2 route(s) SECOND!<br>";
    } else {
        return "Split into $num1 route(s) FIRST!\nSplit into $num2 route(s) SECOND!\n";
    }
}

session_start();
session_reset();
include "mapsdb.php";
include "../accounts/db.php";
?>