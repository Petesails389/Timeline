<?php
include "../head.php";
include "../header.php";


if (isset($_SESSION['username'])) {
    header("Location: account.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    if (isset($_SESSION['loginError'])){
        $error = $_SESSION['loginError'];
        echo "<br><p>$error</p>";
        unset($_SESSION['loginError']);
    }
    include "login.html";
    include "../footer.html";
    exit;
}

$username = $_POST["username"];
$password = $_POST["password"];

if(GetUserID($username) == NULL){
    $_SESSION['loginError'] = 'Username does not exist! <a href="register.php">Register here</a>.';
    header("Location: login.php");
    exit;
} 

if (!Login($username,$password)){
    $_SESSION['loginError'] = 'Password wrong!';
    header("Location: login.php");
    exit;
} 

$_SESSION['username'] = $username;

echo '<br><p>Logged in! If you\'re not redirected shortly please press <a href="account.php">here</a>.</p>';
if (isset($_SESSION["redirect"])){
    header("Location: " . $_SESSION["redirect"]);
    unset($_SESSION["redirect"]);
    exit;
}
header("Location: account.php");


?>
