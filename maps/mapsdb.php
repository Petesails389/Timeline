<?php
$db = new SQLite3('/var/www//html/accounts/db.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
$db->exec("PRAGMA foreign_keys = ON;");

#$db->exec('DROP TABLE IF EXISTS maps');
$db->exec("CREATE TABLE IF NOT EXISTS maps (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ownerID INTEGER, 
    mapName TEXT(256),
    FOREIGN KEY (ownerID) REFERENCES users(id) ON DELETE CASCADE
);");
#$db->exec('INSERT OR IGNORE INTO maps (id, ownerID, mapName) VALUES (1, 1,"Peter\'s Map")');

#$db->exec('DROP TABLE IF EXISTS mapDataPoints');
$db->exec("CREATE TABLE IF NOT EXISTS mapDataPoints (
    mapID INTEGER, 
    lat REAL NOT NULL,
    lng REAL NOT NULL,
    time INT NOT NULL,
    speed REAL,
    elevation REAL,
    PRIMARY KEY (mapID,time),
    FOREIGN KEY (mapID) REFERENCES maps(id) ON DELETE CASCADE
);");

// Rotue types:
// 0 - UNDEFINED (default)
// 2 - walking
// 1 - cycling
// 3 - driving
// 4 - fast (train/plane?)

#$db->exec('DROP TABLE IF EXISTS mapRoutes');
$db->exec("CREATE TABLE IF NOT EXISTS mapRoutes (
    mapID INTEGER,
    startTime INT NOT NULL,
    endTime INT NOT NULL,
    routeType INT DEFAULT 0,
    PRIMARY KEY (mapID,startTime),
    FOREIGN KEY (mapID) REFERENCES maps(id) ON DELETE CASCADE
);");

#$db->exec('DROP TABLE IF EXISTS mapShares');
$db->exec("CREATE TABLE IF NOT EXISTS mapShares (
    mapID INTEGER,
    userID INTEGER, 
    history BOOLEAN,
    current BOOLEAN,
    startTime INT,
    endTime INT,
    expires INT,
    PRIMARY KEY (mapID,userID),
    FOREIGN KEY (mapID) REFERENCES maps(id) ON DELETE CASCADE
    FOREIGN KEY (userID) REFERENCES users(id) ON DELETE CASCADE
);");

#d$db->exec('DROP TABLE IF EXISTS mapMarkers');
$db->exec("CREATE TABLE IF NOT EXISTS mapMarkers (
    markerID INTEGER PRIMARY KEY AUTOINCREMENT,
    mapID INT,
    lat REAL NOT NULL,
    lng REAL NOT NULL,
    markerName TEXT(256),
    icon TEXT(256),
    FOREIGN KEY (mapID) REFERENCES maps(id) ON DELETE CASCADE
);");

function CheckMapID($mapID){
    global $db;
    $statement = $db->prepare('SELECT id FROM maps WHERE id = :mapID');
    $statement->bindValue(':mapID',$mapID);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM);
    if($result == false){
        return NULL;
    }
    return $result[0];
}

function GetMapName($mapID){
    global $db;
    $statement = $db->prepare('SELECT mapName FROM maps WHERE id = :mapID');
    $statement->bindValue(':mapID',$mapID);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM);
    if($result == false){
        return NULL;
    }
    return $result[0];
}

function CheckMapOwner($mapID,$ownerID) {
    global $db;
    $statement = $db->prepare('SELECT id FROM maps WHERE id = :mapID AND ownerID = :ownerID');
    $statement->bindValue(':mapID',$mapID);
    $statement->bindValue(':ownerID',$ownerID);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM);
    if($result == false){
        return NULL;
    }
    return $result[0];
}

# returns an array format [history, current, startdate, enddate, owner]
function GetMapPermissions($mapID,$userID){
    global $db;
    $statement = $db->prepare('SELECT history, current, startTime, endTime FROM mapShares WHERE userID = :userID AND mapID = :mapID');
    $statement->bindValue(':userID',$userID);
    $statement->bindValue(':mapID',$mapID);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM);
    if($result == false){
        if( CheckMapOwner($mapID,$userID) != NULL){
            return [true, true, strtotime(date("Y-m-d"))+86400, GetMapStartDate($mapID), true];
        }
        return [0, 0, 0, 0, false];
    }
    return [$result[0]==1,$result[1]==1, $result[3], $result[2], false];
}

function GetMapPermission($mapID,$userID) {
    global $db;
    $owner = CheckMapOwner($mapID,$userID) != NULL;
    $statement = $db->prepare('SELECT userID FROM mapShares WHERE userID = :userID AND mapID = :mapID');
    $statement->bindValue(':userID',$userID);
    $statement->bindValue(':mapID',$mapID);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM);
    if($result == false){
        return $owner;
    }
    return true;
}

function GetMaps($ownerID){
    global $db;
    $statement = $db->prepare('SELECT id FROM maps WHERE ownerID = :ownerID');
    $statement->bindValue(':ownerID',$ownerID);
    $result = $statement->execute();
    return $result;
}

function GetShared($userID){
    global $db;
    $statement = $db->prepare('SELECT mapID FROM mapShares WHERE userID = :userID');
    $statement->bindValue(':userID',$userID);
    $result = $statement->execute();
    return $result;
}

function GetCenter($mapID){
    global $db;
    $statement = $db->prepare('SELECT centerLat,CenterLng FROM maps WHERE id = :mapID');
    $statement->bindValue(':mapID',$mapID);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM);
    if($result == false){
        return NULL;
    }
    return $result;
}

function AddPoint($mapID, $lat, $lng, $time, $speed = NULL, $elevation = NULL, $mapType = 0) {
    global $db;
    $statement = $db->prepare('INSERT OR REPLACE INTO mapDataPoints (mapID, lat, lng, time, speed, elevation) VALUES (:mapID, :lat, :lng, :time, :speed, :elevation)');
    $statement->bindValue(':mapID',$mapID);
    $statement->bindValue(':lat',$lat);
    $statement->bindValue(':lng',$lng);
    $statement->bindValue(':time',$time);
    $statement->bindValue(':speed',$speed);
    $statement->bindValue(':elevation',$elevation);
    $statement->bindValue(':mapType',$mapType);
    $statement->execute();
}

function AddRoute($mapID, $startTime, $endTime, $routeType = 0) {
    global $db;
    $statement = $db->prepare('INSERT OR REPLACE INTO mapRoutes (mapID, startTime, endTime, routeType) VALUES (:mapID, :startTime, :endTime, :routeType)');
    $statement->bindValue(':mapID',$mapID);
    $statement->bindValue(':startTime',$startTime);
    $statement->bindValue(':endTime',$endTime);
    $statement->bindValue(':routeType',$routeType);
    $statement->execute();
}

function ClearRoutes($mapID,$day = NULL, $duration=86400){
    if ($day == NULL) {
        $day = strtotime(date("Y-m-d"));
    }
    global $db;
    $statement = $db->prepare('DELETE FROM mapRoutes WHERE mapID = :mapID AND startTime >= :startTime AND startTime <= :endTime');
    $statement->bindValue(':mapID',$mapID);
    $statement->bindValue(':startTime',$day-$duration);
    $statement->bindValue(':endTime',$day);
    $statement->execute();
}

function GetPoints($mapID,$day = NULL, $duration=86400){
    if ($day == NULL) {
        $day = strtotime(date("Y-m-d"));
    }
    global $db;
    $statement = $db->prepare('SELECT lat,lng,time FROM mapDataPoints WHERE mapID = :mapID AND time >= :startTime AND time <= :endTime ORDER BY time');
    $statement->bindValue(':mapID',$mapID);
    $statement->bindValue(':startTime',$day-$duration);
    $statement->bindValue(':endTime',$day);
    $results = [];
    $result = $statement->execute();
    $next = $result->fetchArray(SQLITE3_NUM);
    while ($next != false){
        $results[] = $next;
        $next = $result->fetchArray(SQLITE3_NUM);
    }
    $result->finalize();
    return $results;
}

function GetRoutes($mapID,$day = NULL, $duration=86400){
    if ($day == NULL) {
        $day = strtotime(date("Y-m-d"));
    }
    global $db;
    $statement = $db->prepare('SELECT startTime, endTime, routeType FROM mapRoutes WHERE mapID = :mapID AND endTime >= :startTime AND startTime <= :endTime ORDER BY startTime');
    $statement->bindValue(':mapID',$mapID);
    $statement->bindValue(':startTime',$day-$duration);
    $statement->bindValue(':endTime',$day);
    $results = [];
    $result = $statement->execute();
    $next = $result->fetchArray(SQLITE3_NUM);
    while ($next != false){
        $results[] = $next;
        $next = $result->fetchArray(SQLITE3_NUM);
    }
    $result->finalize();
    return $results;
}

function GetLastLocation($mapID){
    global $db;
    $statement = $db->prepare('SELECT lat,lng,time FROM mapDataPoints WHERE mapID = :mapID ORDER BY time DESC');
    $statement->bindValue(':mapID',$mapID);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM);
    if($result == false){
        return GetCenter($mapID);
    }
    return $result;
}
function GetMapStartDate($mapID){
    global $db;
    $statement = $db->prepare('SELECT time FROM mapDataPoints WHERE mapID = :mapID ORDER BY time ASC');
    $statement->bindValue(':mapID',$mapID);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM);
    if($result == false){
        return GetCenter($mapID);
    }
    return $result[0];
}

function GetMarkers($mapID){
    global $db;
    $markers = [];
    $statement = $db->prepare('SELECT lat,lng, markerName, icon, markerID FROM mapMarkers WHERE mapID = :mapID');
    $statement->bindValue(':mapID',$mapID);
    $results = $statement->execute();
    $result = $results->fetchArray(SQLITE3_NUM);
    while ($result != false){
        $markers[] = $result;
        $result = $results->fetchArray(SQLITE3_NUM);
    }
    return $markers;
}

function UpdateMarker($markerID, $lat, $lng, $markerName, $icon, $mapID){
    global $db;
    $statement = $db->prepare('UPDATE mapMarkers SET lat = :lat, lng = :lng, markerName = :markerName, icon = :icon WHERE markerID = :markerID');
    $statement->bindValue(':markerID',$markerID);
    $statement->bindValue(':lat',$lat);
    $statement->bindValue(':lng',$lng);
    $statement->bindValue(':markerName',$markerName);
    $statement->bindValue(':icon',$icon);
    $statement->execute();
}

function NewMarker($lat, $lng, $markerName, $icon, $mapID){
    global $db;
    $statement = $db->prepare('INSERT INTO mapMarkers (mapID,lat,lng, markerName, icon) VALUES (:mapID,:lat,:lng, :markerName, :icon)');
    $statement->bindValue(':mapID',$mapID);
    $statement->bindValue(':lat',$lat);
    $statement->bindValue(':lng',$lng);
    $statement->bindValue(':markerName',$markerName);
    $statement->bindValue(':icon',$icon);
    $statement->execute();
}

function DeleteMarker($markerID){
    global $db;
    $statement = $db->prepare('DELETE FROM mapMarkers WHERE markerID = :markerID');
    $statement->bindValue(':markerID',$markerID);
    $statement->execute();
}

function GetShares($mapID){
    global $db;
    $shares = [];
    $statement = $db->prepare('SELECT * FROM mapShares WHERE mapID = :mapID');
    $statement->bindValue(':mapID',$mapID);
    $results = $statement->execute();
    $result = $results->fetchArray(SQLITE3_NUM);
    while ($result != false){
        $shares[] = $result;
        $result = $results->fetchArray(SQLITE3_NUM);
    }
    return $shares;
}

function UpdateShare($mapID, $userID, $history, $current, $startDate, $endDate, $expires){
    global $db;
    $statement = $db->prepare('INSERT OR REPLACE INTO mapShares (mapID, userID, history, current, startTime, endTime, expires)  VALUES(:mapID, :userID, :history, :current, :startDate, :endDate, :expires)');
    $statement->bindValue(':mapID',$mapID);
    $statement->bindValue(':userID',$userID);
    $statement->bindValue(':history',$history);
    $statement->bindValue(':current',$current);
    $statement->bindValue(':startDate',$startDate);
    $statement->bindValue(':endDate',$endDate);
    $statement->bindValue(':expires',$expires);
    $statement->execute();
}

function DeleteShare($mapID, $userID){
    global $db;
    $statement = $db->prepare('DELETE FROM mapShares WHERE mapID = :mapID AND userID = :userID');
    $statement->bindValue(':mapID',$mapID);
    $statement->bindValue(':userID',$userID);
    $statement->execute();
}

?>