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
$password = $_POST['currentPassword'];
$passwordHash = password_hash($_POST["newPassword"], PASSWORD_ARGON2I);
$confirmPassword = $_POST["confirmPassword"];

$redirect = "Location: account.php";

if (!Login($username,$password)){
    $_SESSION["accountError"] = "Password incorrect!";
    header("Location: account.php");
    exit;
} 

if (IsAdmin($username) && isset($_POST["id"]) && GetUser($_POST["id"])[1] != NULL){
    $username = GetUser($_POST["id"])[1];
    $confirmPassword = $_POST["newPassword"];

    $id = $_POST["id"];
    $redirect = "Location: manageaccount.php?id=$id";
}

if (!password_verify($confirmPassword,$passwordHash)){
    $_SESSION["accountError"] = "Passwords do not match!";
    header("Location: account.php");
    exit;
}

ChangePassword($username,$passwordHash);
header($redirect);

?>
