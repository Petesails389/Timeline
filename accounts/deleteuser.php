<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
  $_SESSION["loginError"] = "Please login in order to make changes to an account!";
  header("Location: login.php");
  exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION["accountError"] = "An error occured please try again!";
    header("Location: account.php");
    exit;
}

$username = $_SESSION['username'];
$password = $_POST['password'];

if (!Login($username,$password)){
    $_SESSION["accountError"] = "Password incorrect!";
    header("Location: account.php");
    exit;
}

if (!IsAdmin($username)) {
    DeleteUser($username);
    header("Location: logout.php");
    exit;
}


if(!isset($_POST["id"])){
    if ($username == "Admin"){
        $_SESSION["accountError"] = "Cannot delete the Admin account!";
        header("Location: account.php");
        exit;
    }
    DeleteUser($username);
    header("Location: logout.php");
    exit;
}

$username = GetUser($_POST["id"])[1];

if($username == null) {
    $_SESSION['accountError'] = 'User does not exist!';
    header("Location: account.php");
    exit;
}

if ($username == "Admin"){
    $_SESSION["accountError"] = "Cannot delete the Admin account!";
    header("Location: account.php");
    exit;
}
DeleteUser($username);
header("Location: adminpage.php");
exit;

?>
