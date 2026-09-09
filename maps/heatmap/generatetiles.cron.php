<?php
include "imageprocessing.inc";
include "../util.inc";
include "../mapinfo.inc";

//Get all unprocessed points (just recent ones for now)
$points = GetPoints($mapID,strtotime(date("Y-m-d")));



for ($i = 1; $i < count($points); $i++) {
    

    $last = array_pop($points);
}

?>