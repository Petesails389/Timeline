<?php
include "util.inc";
include "mapinfo.inc";
include "../head.php";
include "../header.php";

if (!$permissions[4]){
    header("Location: index.php");
    exit;
}

$focus = "";

if(isset($_GET["focus"])){
    $focus = $_GET["focus"];
}

if ($focus != "Markers" && $focus != "Shares") {
    $focus = "Settings";
}

$mapID = $_GET["mapID"];

$name = GetMapName($mapID);
$markers = GetMarkers($mapID);
$shares = GetShares($mapID);
?>

<div class="w3-bar w3-card">
    <button class="w3-bar-item w3-button w3-hover-theme <?php if ($focus == "Settings") { echo "w3-theme-d4";}?> tablink" onclick="openTab(event, 'Settings')">Settings</button>
    <button class="w3-bar-item w3-button w3-hover-theme <?php if ($focus == "Markers") { echo "w3-theme-d4";}?> tablink" onclick="openTab(event, 'Markers')">Markers</button>
    <button class="w3-bar-item w3-button w3-hover-theme <?php if ($focus == "Shares") { echo "w3-theme-d4";}?> tablink" onclick="openTab(event, 'Shares')">Shares</button>
    <a class='w3-bar-item w3-right w3-button w3-theme-d2 w3-hover-theme' href='viewmap.php?mapID=<?php echo $mapID; ?>'>View Map</a>
</div>

<div id="Settings" class="tab w3-card w3-padding" <?php if ($focus != "Settings") { echo "style='display: none;'";}?>>
    <h4>Manually add route (GPX):</h4>
    <form action="/maps/gpxin.php" method="post" enctype="multipart/form-data">
        <input type='hidden' name='mapID' value='<?php echo $mapID; ?>'>
        <input type="file" name="gpx" size="25" /><br><br>
	    <input class="w3-button w3-theme w3-hover-theme" type="submit" name="submit" value="Upload" />
    </form>
</div>


<div id="Markers" style="padding: 16px; <?php if ($focus != "Markers") { echo "display: none;";}?>" class="tab w3-card">
    <div class="w3-grid" style="gap:16px; grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
    <?php 
    foreach ($markers as $marker){
        echo "<div class='w3-card w3-padding'><form action='updatemarker.php' method='post'>
            <h3>$marker[2]: <span class='material-symbols-outlined mapIcon'>$marker[3]</span></h3>
            <div>
                <input type='hidden' name='mapID' value='$mapID'>
                <input type='hidden' name='markerID' value='$marker[4]'>
                <div>
                <label>Marker name:</label><br>
                <input type='text'  class='w3-border-theme-select' name='name' value='$marker[2]' required>
                </div>
                <div>
                <label>Icon (<a href='https://fonts.google.com/icons'>format</a>):</label><br>
                <input type='text'  class='w3-border-theme-select' name='icon' value='$marker[3]' required>
                </div>
                <div>
                <label>Latitude:</label><br>
                <input type='text'  class='w3-border-theme-select' name='lat' value='$marker[0]' required>
                </div>
                <div>
                <label>Latitude:</label><br>
                <input type='text'  class='w3-border-theme-select' name='lng' value='$marker[1]' required>
                </div><br>
                <input type='submit' name='submit' class='w3-button w3-theme-d2 w3-hover-theme' value='Update'>
                <input type='submit' name='submit' class='w3-button w3-theme-d2 w3-hover-theme' value='Delete'><br>
            </div></form></div>";
    }
    ?>
    <div class='w3-card w3-padding'><form action='updatemarker.php' method='post'>
        <h3>New marker:  <span class='material-symbols-outlined mapIcon'>add_location</span></h3>
        <div>
            <input type='hidden' name='mapID' value='<?php echo $mapID; ?>'>
            <div>
                <label>Marker name:</label><br>
                <input type='text'  class='w3-border-theme-select' name='name' required>
            </div>
            <div>
                <label>Icon (<a href='https://fonts.google.com/icons'>format</a>):</label><br>
                <input type='text'  class='w3-border-theme-select' name='icon' required>
            </div>
            <div>
                <label>Latitude:</label><br>
                <input type='text'  class='w3-border-theme-select' name='lat' value='0' required>
            </div>
            <div>
                <label>Latitude:</label><br>
                <input type='text'  class='w3-border-theme-select' name='lng' value='0' required>
            </div><br>
            <input type='submit' name='submit' class='w3-button w3-theme-d2 w3-hover-theme' value='Add'>
        </div>
    </form></div>

    </div>
</div>



<div style="padding: 16px;  <?php if ($focus != "Shares") { echo "display: none;";}?>" id="Shares" class="tab w3-card">

    <div class="w3-grid" style="gap:16px; grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
    <?php 
    foreach ($shares as $share){
        $username = GetUser($share[1])[1];
        echo "<div class='w3-card w3-padding'><form action='updateshare.php' method='post'>
        <h3>Shared with $username:</h3>
        <div>
            <input type='hidden' name='mapID' value='$mapID'>
            <input type='hidden'  class='w3-border-theme-select' name='username' value='$username' required>
            <div>
                <label class='tooltip'>heatmap only:<span class='tooltiptext'>Prevents users from seeing when your trips took place.</span></label>
                <input type='checkbox'  class='w3-border-theme-select' name='heatmap' ";if ($share[2] == 0) {echo "checked";} echo ">
            </div>
            <div>
                <label class='tooltip'>Live only:<span class='tooltiptext'>Prevents users from seeing trips at all.</span></label>
                <input type='checkbox'  class='w3-border-theme-select' name='live'";if ($share[3] == 1) {echo "checked";} echo ">
            </div>
            <div>
                <label>Start Date:</label><br>
                <input type='datetime-local'  class='w3-border-theme-select' name='start' value='"; echo date("Y-m-d\TH:i", $share[4]); echo "'>
            </div>
            <div>
                <label>End Date:</label><br>
                <input type='datetime-local'  class='w3-border-theme-select' name='end' value='"; echo date("Y-m-d\TH:i", $share[5]); echo "'>
            </div>
            <div>
                <label>Expires:</label><br>
                <input type='datetime-local'  class='w3-border-theme-select' name='expires' value='"; echo date("Y-m-d\TH:i", $share[6]); echo "'>
            </div><br>
            <input type='submit' name='submit' class='w3-button w3-theme-d2 w3-hover-theme' value='Update'>
            <input type='submit' name='submit' class='w3-button w3-theme-d2 w3-hover-theme' value='Delete'>
        </div>
    </form></div>";
    }
    ?>
    <div class='w3-card w3-padding'><form action='updateshare.php' onsubmit='OnSubmit("newShare")' method='post' name="newShare">
        <h3>New Map Share:</h3>
        <div>
            <input type='hidden' name='mapID' value='<?php echo $mapID; ?>'>
            <div>
                <label>Username:</label><br>
                <input type='text'  class='w3-border-theme-select' name='username' value="Test" required>
            </div>
            <div>
                <label>Mode:</label><br>
                <select name="mode" onchange="modeUpdate('newShare')">
                    <option value="HeatMap">Heat Map (default)</option>
                    <option value="Live1h">Live (1 hour)</option>
                    <option value="Live1d">Live (1 day)</option>
                    <option value="Today">Today</option>
                    <option value="AnyTime">Any Time</option>
                    <option value="1d">Custom Day</option>
                    <option value="1w">Custom week</option>
                    <option value="Custom">Custom</option>
                </select>
            </div>
            <div>
                <label for="heatmap" class="tooltip">Heatmap only:<span class="tooltiptext">Prevents users from seeing when your trips took place.</span></label>
                <input id="heatmap" type='checkbox'  class='w3-border-theme-select' name='heatmap'>
            </div>
            <div>
                <label for="live" class="tooltip">Live only:<span class="tooltiptext">Prevents users from seeing trips at all.</span></label>
                <input type='checkbox'  class='w3-border-theme-select' name='live'>
            </div>
            <div>
                <label for="start">Start Date:</label><br>
                <input type='datetime-local'  class='w3-border-theme-select' name='start' onchange="dateUpdate('newShare')" >
            </div>
            <div>
                <label for="end">End Date:</label><br>
                <input type='datetime-local'  class='w3-border-theme-select' name='end'>
            </div>
            <div>
                <label for="expires">Expires:</label><br>
                <input type='datetime-local'  class='w3-border-theme-select' name='expires''>
            </div><br>
            <input type='submit' name='submit' class='w3-button w3-theme-d2 w3-hover-theme' value='Add'>
        </div>
    </form></div>
    </div>
</div>



<script src="settings.js"></script>

<?php
include "../footer.html";
?>