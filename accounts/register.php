<?php
include "../head.php";
include "../header.php";

$endpoint = "/accounts/account.php";
if (isset($_GET['redirect'])){
    $endpoint = $_GET['redirect'];
}

if (isset($_SESSION['username'])) {
  header("Location: ..$endpoint");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    if (isset($_SESSION['registerError'])){
        echo $_SESSION['registerError'];
        unset($_SESSION['registerError']);
    }
    include "register.html.php";
    include "../footer.html";
    exit;
}

$username = $_POST["username"];
$passwordHash = password_hash($_POST["password"], PASSWORD_ARGON2I);
$confirmPassword = $_POST["confirmPassword"];

if(GetUserID($username) != NULL){
    $_SESSION['registerError'] = '<br><p>Username taken!</p>';
    header("Location: register.php?redirect=$endpoint");
    exit;
} 

if (!password_verify($confirmPassword,$passwordHash)){
    $_SESSION['registerError'] = '<br><p>Passwords did not match!</p>';
    header("Location: register.php?redirect=$endpoint");
    exit;
}

AddUser($username,$passwordHash);
echo "<br><p>Account made! If you're not redirected shortly please press <a href='$endpoint'>here</a>.</p";
header("Location: ..$endpoint");

?>
