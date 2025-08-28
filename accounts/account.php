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
?>

<h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>

<?php
if (isset($_SESSION['accountError'])){
    $error = $_SESSION['accountError'];
    echo "<p>$error</p>";
    unset($_SESSION['accountError']);
}
?>

<h2>Change Password:</h2>
<form action="changepassword.php" method="post">
    <div class="w3-panel">
        <label>Current Password:</label><br>
        <input type="password"  class="w3-border-theme-select" name="currentPassword" placeholder="Current Password" required>
    </div>
    <div class="w3-panel">
        <label>New Password:</label><br>
        <input type="password"  class="w3-border-theme-select" name="newPassword" placeholder="New Password" required><br><br>
        <input type="password"  class=" w3-border-theme-select" name="confirmPassword" placeholder="Confirm Password" required>
    </div>
    <div class="w3-panel">
        <input type="submit" name="submit" class="w3-button w3-theme-d2 w3-hover-theme" value="Submit">
    </div>
</form>

<?php
if (IsAdmin($_SESSION['username'])){
    echo "
    <h2>Admin Contols</h2>
    <a class='w3-button w3-theme-d2 w3-hover-theme' href='adminpage.php'>Admin Panel</a>
    ";
    include "../footer.html";
    exit;
}
?>

<h2>Delete Account:</h2>
<form action="deleteuser.php" method="post">
    <div class="w3-panel">
        <label>Confirm Password:</label><br>
        <input type="password"  class="w3-border-theme-select" name="password" placeholder="Password" required>
    </div>
    <div class="w3-panel">
        <input type="submit" name="submit" class="w3-button w3-theme-d2 w3-hover-theme" value="DELETE!">
    </div>
</form>



<?php
include "../footer.html";
?>