<?php
$mapID = $_GET["mapID"];

if (!isset($_SESSION['username'])) {
    $_SESSION["redirect"] = $_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING'];
    header("Location: ../accounts/login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] != "GET"){
    header("Location: ../index.php");
    exit;
}
if (CheckMapID($mapID) == NULL){
    http_response_code(400);
    exit;
}
$permissions = GetMapPermissions($mapID,GetUserID($_SESSION['username']));
if (!GetMapPermission($mapID,GetUserID($_SESSION['username']))){
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

//map info
$name = GetMapName($mapID);
$markers = GetMarkers($mapID);
?>