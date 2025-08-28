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

$accounts = GetAllAccounts();
?>

<h1>Manage Accounts:</h1>

<input type="text" id="Search_Users" onkeyup="search('Users')" placeholder="Search for names..">

<ul id="Users" class="w3-ul">
<?php
foreach ($accounts as $account) {
    $id = $account[0];
    $username = $account[1];
    echo "<li><a href='manageaccount.php?id=$id'>$username</a></li>";
}
?>
</ul>

<?php
include "../footer.html";
?>