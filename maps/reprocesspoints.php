<?php
set_time_limit(0);
include "util.inc";
include "mapinfo.inc";

//if not the owner don't access this page
if (!$permissions["owner"]) {
    header("Location: index.php");
    exit;
}

echo "$day $duration";
echo reprocessPoints($mapID, $day, $duration);

?>