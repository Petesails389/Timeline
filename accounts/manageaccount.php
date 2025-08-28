<?php
include "../head.php";
include "../header.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] != "GET"){
    header("Location: ../index.php");
    exit;
}

if (!IsAdmin($_SESSION['username'])){
    header("Location: ../index.php");
    exit;
}

$id = $_GET["id"];
$user = GetUser($id);

if($user == NULL){
    $_SESSION['accountError'] = 'User does not exist!';
    header("Location: account.php");
    exit;
}

$username = $user[1];

if ($username == "Admin"){
    header("Location: account.php");
    exit;
}
?>

<h1>Manage <?php echo "$username"; ?>'s account:</h1>

<h2>Change <?php echo "$username"; ?>'s Password:</h2>
<form action="changepassword.php" method="post">
    <div class="w3-panel">
        <label>Admin Password:</label><br>
        <input type="password"  class="w3-border-theme-select" name="currentPassword" placeholder="Current Password" required>
    </div>
    <div class="w3-panel">
        <label>Temp Password:</label><br>
        <input type="Text"  class="w3-border-theme-select" name="newPassword" value="Password123!" required>
    </div>
    <div class="w3-panel">
        <input type="submit" name="submit" class="w3-button w3-theme-d2 w3-hover-theme" value="Set">
    </div>
    <input type="hidden" id="id" name="id" value="<?php echo "$id"; ?>">
</form>


<h2>Delete <?php echo "$username"; ?>'s Account:</h2>
<form action="deleteuser.php" method="post" onsubmit="return confirm('This is a distructive action and cannot be undone! Do you want to proceed?');">
    <div class="w3-panel">
        <label>Confirm Admin Password:</label><br>
        <input type="password"  class="w3-border-theme-select" name="password" placeholder="Password" required>
    </div>
    <div class="w3-panel">
        <input type="submit" name="submit" class="w3-button w3-theme-d2 w3-hover-theme" value="Delete!">
    </div>
    <input type="hidden" id="id" name="id" value="<?php echo "$id"; ?>">
</form>
