<?php
include "util.inc";
include "mapinfo.inc";
include "../head.php";
?>

<script src="plotly-3.1.0.min.js" charset="utf-8"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>
<script src="leaflet-corridor.js?1"></script>
<script src="leaflet.icon-material.js"></script>
<script src="map.js?b18"></script>

<style>
    .content {
	margin: 0px !important;
}
</style>

<?php
include "../header.php";
?>


<script>
function menu_open() {
  document.getElementById("menu").style.display = "block";
  document.getElementById("menuButton").setAttribute('onclick','menu_close()');
  document.getElementById("menuButton").innerHTML  = "<span class='material-symbols-outlined' id='navBarButton'>close</span>";
}

function menu_close() {
  document.getElementById("menu").style.display = "none";
  document.getElementById("menuButton").setAttribute('onclick','menu_open()');
  document.getElementById("menuButton").innerHTML  = "<span class='material-symbols-outlined' id='navBarButton'>menu</span>";
}
</script>

 <!-- menu -->
<div style='position: relative;'>
    <div class='w3-bar w3-theme-d1' style='position: absolute; top: 0px; right: 0px; width: fit-content; z-index: 1000; margin: 10px;'>
            <div class="w3-hide-small w3-bar-item w3 right" style="padding: 0px; width: 344px;">
                <h5 id='title' style='padding: 10px; margin: 0px;' class='w3-theme-d1'>Map</h5>
            </div>
            <a class='w3-bar-item w3-button w3-hover-theme w3-right' href='settings.php?mapID=<?php echo $_GET["mapID"]; ?>'>
                <span class='material-symbols-outlined w3-display-center'>settings</span>
            </a>
    </div>
</div>


 <div class="w3-flex" style="flex-direction:column; height: 100%;"> 
 <!-- display map -->
<div id='map' style='height: 100%; margin: 0px;'></div>

<?php
if ($permissions[0]) {?>
<!-- Timeline -->
<div style="width: 100%; height: 50%; max-height: 200px; font-size: 0;">
    <button class='w3-button w3-hover-theme' onclick="changeDate(-1)" style="display:inline-block; height: 100%; width:50px; vertical-align:top; padding:8px;">
        <span class="material-symbols-outlined">chevron_left</span>
    </button>
    <div style="display: inline-block; height: 100%; width:calc(100% - 100px)">
        <form style ="width: 100%; font-size: medium; height: 55px;">
            <h4 style="text-align: center; margin: 0px;">Timeline:</h4>
            <div style="margin: auto; width: fit-content;">
                <input onChange="getData()" name="day" id="day" type="date" value="<?php echo Date("Y-m-d",$day-86400);?>">
                <select onChange="getData()" id="duration" name="duration">
                    <option value="86400" <?php if ($duration == 86400){echo"selected";}?>>1 day</option>
                    <option value="604800" <?php if ($duration == 604800){echo"selected";}?>>1 week</option>
                    <option value="2678400" <?php if ($duration == 2678400){echo"selected";}?>>1 month</option>
                    <option value="31536000" <?php if ($duration == 31536000){echo"selected";}?>>1 year</option>
                    <option value="10000000000" <?php if ($duration == 10000000000){echo"selected";}?>>All Time</option>
                </select>
            </div>
        </form>
        <div id="timeline" style="display:inline-block; height: calc(100% - 55px); width:100%;" ></div>
    </div>
    <button class='w3-button w3-hover-theme' onclick="changeDate(1)" style="display:inline-block; height: 100%; width:50px; vertical-align:top; padding:8px;">
        <span class="material-symbols-outlined">chevron_right</span>
    </button>
</div>
<?php
}?>

<script>
    drawMap();
</script>



</div>
</div>
</body>
</html>