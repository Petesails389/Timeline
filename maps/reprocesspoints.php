<?php
set_time_limit(0);
include "util.inc";
include "mapinfo.inc";

//if not the owner don't access this page
if (!$permissions[4]) {
    header("Location: index.php");
    exit;
}

echo reprocessPoints($mapID, $day, $duration);

?>