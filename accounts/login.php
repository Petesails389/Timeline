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
    if (isset($_SESSION['loginError'])){
        $error = $_SESSION['loginError'];
        echo "<br><p>$error</p>";
        unset($_SESSION['loginError']);
    }
    include "login.html.php";
    include "../footer.html";
    exit;
}

$username = $_POST["username"];
$password = $_POST["password"];

if(GetUserID($username) == NULL){
    $_SESSION['loginError'] = "Username does not exist! <a href='register.php?redirect=$endpoint'>Register here</a>.";
    header("Location: login.php?redirect=$endpoint");
    exit;
} 

if (!Login($username,$password)){
    $_SESSION['loginError'] = 'Password wrong!';
    header("Location: login.php?redirect=$endpoint");
    exit;
} 

$_SESSION['username'] = $username;

echo "<br><p>Logged in! If you're not redirected shortly please press <a href='..$endpoint'>here</a>.</p>";
header("Location: ..$endpoint");


?>
