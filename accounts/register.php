<?php
include "../head.php";
include "../header.php";

if (isset($_SESSION['username'])) {
  header("Location: account.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    if (isset($_SESSION['registerError'])){
        echo $_SESSION['registerError'];
        unset($_SESSION['registerError']);
    }
    include "register.html";
    include "../footer.html";
    exit;
}

$username = $_POST["username"];
$passwordHash = password_hash($_POST["password"], PASSWORD_ARGON2I);
$confirmPassword = $_POST["confirmPassword"];

if(GetUserID($username) != NULL){
    $_SESSION['registerError'] = '<br><p>Username taken!</p>';
    header("Location: register.php");
    exit;
} 

if (!password_verify($confirmPassword,$passwordHash)){
    $_SESSION['registerError'] = '<br><p>Passwords did not match!</p>';
    header("Location: register.php");
    exit;
}

AddUser($username,$passwordHash);
echo '<br><p>Account made! If you\'re not redirected shortly please press <a href="account.php">here</a>.</p>';
$_SESSION['username'] = $username;
if (isset($_SESSION["redirect"])){
    header("Location: ". $_SESSION["redirect"]);
    unset($_SESSION["redirect"]);
    exit;
}
header("Location: account.php");

?>
