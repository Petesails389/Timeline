<?php
$mapID = $_GET["mapID"];

if (!isset($_SESSION['username']) && !isset($_GET["shareCode"])) {
    $redirect = $_SERVER["PHP_SELF"] . "?" . $_SERVER['QUERY_STRING'];
    header("Location: ../accounts/login.php?redirect=$redirect");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] != "GET"){
    header("Location: ../index.php");
    exit;
}
if ($mapID == NULL) {
    http_response_code(400);
    exit;
}
if (CheckMapID($mapID) == NULL){
    http_response_code(404);
    exit;
}

if (isset($_GET["shareCode"])) {
    $shareCode = $_GET["shareCode"];
} else {
    $shareCode = NULL;
}

$permissions = GetMapPermissions($mapID,GetUserID($_SESSION['username']),$shareCode);
if (!GetMapPermission($mapID,GetUserID($_SESSION['username']),$shareCode)){
    http_response_code(404); //return 404 to hide map IDs from unauthorised
    exit;
}

//sets day and range of data (permission will return the duration of the whole map if they are the owner)
$day = $permissions["startTime"];
$duration = $permissions["endTime"]-$permissions["startTime"];
$maxDuration = $duration;

if ($permissions['owner']){
    $duration = 86400;
    $day = strtotime(date("Y-m-d"));
}

//if allowed to browse history
if ($permissions["history"]){
    if (isset($_GET["duration"])) {
        $duration = min($maxDuration, $_GET["duration"]);
    }
    if (isset($_GET["day"])) {
        $day = max(min($permissions["endTime"]-$duration,$_GET["day"]),$permissions["startTime"]);
    }
}

//prevents day being 0
if ($day == 0) {
    $day = strtotime(date("Y-m-d"));
}

if (!$permissions["current"]) {
    //TODO-NEEDS FIXING
    //$day = min(time()-(86400+3600),$day + $duration - 86400);// limit veiw to be delayed by an hour
}

//map info
$name = GetMapName($mapID);
$markers = GetMarkers($mapID);
?>