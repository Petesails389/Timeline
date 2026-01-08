<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] != "POST"){
    //http_response_code(405);
    //exit;
}

#authenticate request
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'You are not authenticated to access this page at this time';
}

$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

if (!Login($username, $password)){
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'You are not authenticated to access this page at this time';
}

$userID = GetUserID($username);

?>