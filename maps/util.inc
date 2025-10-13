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

function totalRouteRotation($route){
    if (count($route) <= 2) {
        return 0;
    }
    $totalRotation = 0;
    $lastbearing = bearing($route[0],$route[1]);
    for ($key = 2; $key < count($route); $key ++) {
        $bearing = bearing($route[$key -1], $route[$key]);
        $difference = $lastbearing-$bearing;
        if ($difference < -180) {
            $difference = $difference + 360;
        }
        if ($difference > 180) {
            $difference = $difference - 360;
        }
        $totalRotation += $difference;
        $lastbearing = $bearing;
    }

    return $totalRotation;
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

if (!isset($_SESSION['username'])) {
    $_SESSION["redirect"] = $_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING'];
    header("Location: ../accounts/login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] != "GET"){
    header("Location: ../index.php");
    exit;
}
if (CheckMapID($_GET["mapID"]) == NULL){
    http_response_code(400);
    exit;
}
$permissions = GetMapPermissions($_GET["mapID"],GetUserID($_SESSION['username']));
if (!GetMapPermission($_GET["mapID"],GetUserID($_SESSION['username']))){
    header("Location: ../index.php");
    exit;
}

//sets day and range of data (permission will return the duration of the whole map if they are the owner)
$day = $permissions[2];
$duration = $permissions[2]-$permissions[3];

//if allowed to browse history
if ($permissions[0]){
    if (isset($_GET["day"])) {
        $day = min(max($permissions[3],strtotime($_GET["day"])),$permissions[2])+86400;
    }
    if (isset($_GET["duration"])) {
        $duration = max(min($day-$permissions[3],$_GET["duration"]),1);
    }
}

//prevents day being 0
if ($day == 0) {
    $day = strtotime(date("Y-m-d"));
}

if (!$permissions[1]) {
    $day = min(time()-(8640+3600),$day);// limit veiw to be delayed by an hour
}

//get markers
$markers = GetMarkers($_GET["mapID"]);
?>