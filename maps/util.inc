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

//returns the average speed between 2 points in m/s
function averageSpeed($p1, $p2) {
    return vincentyGreatCircleDistance($p1[0],$p1[1],$p2[0],$p2[1]) / abs($p1[2] - $p2[2]);
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

//prevents day being 0
if ($day == 0) {
    $day = strtotime(date("Y-m-d"));
}

//get markers
$markers = GetMarkers($_GET["mapID"]);
?>