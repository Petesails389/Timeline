<?php
include "mapsdb.php";
include "../head.php";
include "../header.php";

$focus = "About";

if(isset($_GET["focus"])){
    $focus = $_GET["focus"];
}

?>

<h1 class="w3-monospace"> Welcome to maps!</h1>

<div class="w3-bar w3-card">
    <button class="w3-bar-item w3-button w3-hover-theme <?php if ($focus == "About") { echo "w3-theme-d4";}?> tablink" onclick="openTab(event, 'About')">About</button>
    <button class="w3-bar-item w3-button w3-hover-theme <?php if ($focus == "Owned") { echo "w3-theme-d4";}?> tablink" onclick="openTab(event, 'Owned')">My maps</button>
    <button class="w3-bar-item w3-button w3-hover-theme <?php if ($focus == "Shared") { echo "w3-theme-d4";}?> tablink" onclick="openTab(event, 'Shared')">Shared with me</button>
</div>

<div id="About" class="tab w3-card w3-padding" <?php if ($focus != "About") { echo "style='display: none;'";}?> >
<h2 class="w3-monospace">About:</h2>
<p>An alternative to Google Timeline that won't end up on <a href="https://killedbygoogle.com/">killedbygoogle.com<a>. Track your travel history, share your epic adventures with others, or use it to build a heatmap of your life! If you're here to setup a map of your own please contact me by <a href="mailto:peter@thesparrows.net">email</a> to request access. If you think you should be able to view another person's map that you can't, please double check with them.</p><br>
<p>The system currently works with a Tasker profile for the phone side that I will make available when I get round to it but I'm also in the process of turning it into an app (<a href="https://github.com/Petesails389/PhoneTrack">GitHub</a>) for more accurate tracks.</p><br>
</div>

<div id="Owned" class="tab w3-card w3-padding" <?php if ($focus != "Owned") { echo "style='display: none;'";}?> >
    <h3>Your maps:</h3>
    <div>
    <?php
    if (!isset($_SESSION['username'])) {
        $_SESSION["redirect"] = "/maps";
        echo "<p><a href='/accounts/login.php'>Login<a> to see Your maps!</p>";
    }
    else {
        $maps = GetMaps(GetUserID($_SESSION['username']));
        $map = $maps->fetchArray(SQLITE3_NUM);
        if ($map == false) {
            echo "<p>You have no maps of your own at the moment.</p>";
        } else{
            while ($map) {
                $name = GetMapName($map[0]);
                echo "<div class='w3-margin-bottom'>
                        <a class='w3-button w3-theme-d2 w3-hover-theme' href='viewmap.php?mapID=$map[0]'>$name</a>
                        <a class='w3-button w3-theme-d2 w3-hover-theme w3-display' style='padding: 7px 7px 1px 8px;' href='settings.php?mapID=$map[0]'>
                            <span class='material-symbols-outlined w3-display-center'>settings</span>
                        </a>
                    </div>";
                $map = $maps->fetchArray(SQLITE3_NUM);
            }
        }
    }
    ?>
    </div>
</div>

<div id="Shared" class="tab w3-card w3-padding" <?php if ($focus != "Shared") { echo "style='display: none;'";}?> >
    <h3>Shared with me:</h3>
    <div>
    <?php
    if (!isset($_SESSION['username'])) {
        $_SESSION["redirect"] = "/maps";
        echo "<p><a href='/accounts/login.php'>Login<a> to see Your maps!</p>";
    } else {
        $mapsShared = GetShared(GetUserID($_SESSION['username']));
        $map = $mapsShared->fetchArray(SQLITE3_NUM);
        if ($map == false) {
            echo "<p>You have no maps shared with you at the moment</p>";
        } else{
            while ($map) {
                $name = GetMapName($map[0]);
                echo "<div class='w3-margin-bottom'>
                        <a class='w3-button w3-theme-d2 w3-hover-theme' href='viewmap.php?mapID=$map[0]' class=''>$name</a>
                        
                    </div>";
                $map = $maps->fetchArray(SQLITE3_NUM);
            }
        }
    }
    ?>
    </div>
</div>


<?php
include "../footer.html";
?>