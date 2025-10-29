<?php
// Add this file to a cron job if automatic point processing is desired.

// EXAMPLE:
// 0 3 * * *  cd /var/www/html/maps ; php pointsprocessing.cron.php

// NOTE:
// If the system is hosting users in multiple locations this may result in interesting results for users who are likely to be uploading data whilst processing is running.
// I intent to fix this at a later date by changing the cron job to run every hour and adding a feature where users can change the time they want the data to be processed. This will also allow users not to process data if they do not wish to.

include "util.inc";

$maps = GetAllMapIDs();

//set day and duration
$day = strtotime(date("Y-m-d"));
$duration = 172800;

//get output file 
$file = 'ProcessingResults.txt';
$current = file_get_contents($file);
$current .= date("Y-m-d")."\n";

foreach ($maps as $mapID) {
    $current .= "    Map: $mapID\n        ";
    $current .= str_replace("\n", "\n        ", reprocessPoints($mapID, $day, $duration, False));
    $current .= "\n";
}

// Write the contents back to the file
file_put_contents($file, $current);
?>