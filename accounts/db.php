<?php
$db = new SQLite3('/var/www//html/accounts/db.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

$db->query('CREATE TABLE IF NOT EXISTS "users" (
    "id" INTEGER PRIMARY KEY,
    "username" VARCHAR,
    "password" VARCHAR,
    "admin" BOOLEAN
)');

$statement = $db->prepare('SELECT count(id) FROM "users"');
$result = $statement->execute();
if ($result->fetchArray(SQLITE3_NUM)[0] == 0) {
    $db->query('CREATE INDEX "userIndex" ON "users" ("username")');
    AddUser("Admin",password_hash("Admin123", PASSWORD_ARGON2I),true);
}

function AddUser($username, $password, $admin = false) {
    global $db;
    $statement = $db->prepare('INSERT INTO "users" ("username", "password","admin") VALUES (:username, :password, :admin)');
    $statement->bindValue(':username',$username);
    $statement->bindValue(':password',$password);
    $statement->bindValue(':admin',$admin);
    $statement->execute();
}

function ChangePassword($username, $password) {
    global $db;
    $statement = $db->prepare('UPDATE "users" SET "password" = :password WHERE "username" = :username');
    $statement->bindValue(':username',$username);
    $statement->bindValue(':password',$password);
    $statement->execute();
}

function DeleteUser($username) {
    global $db;
    $statement = $db->prepare('DELETE FROM "users" WHERE "username" = :username');
    $statement->bindValue(':username',$username);
    $statement->execute();
}

function GetUserID($username) {
    global $db;
    $statement = $db->prepare('SELECT id FROM "users" WHERE "username" = :username');
    $statement->bindValue(':username',$username);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM);
    if ($result == false){
        return NULL;
    }
    return $result[0];
}

function GetUser($id) {
    global $db;
    $statement = $db->prepare('SELECT * FROM "users" WHERE "id" = :id');
    $statement->bindValue(':id',$id);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM);
    if ($result == false){
        return NULL;
    }
    return $result;
}

function IsAdmin($username) {
    global $db;
    $statement = $db->prepare('SELECT "admin" FROM "users" WHERE "username" = :username');
    $statement->bindValue(':username',$username);
    $result = $statement->execute();
    return $result->fetchArray(SQLITE3_NUM)[0] == 1;
}

function Login($username, $password) {
    global $db;
    $statement = $db->prepare('SELECT "password" FROM "users" WHERE "username" = :username');
    $statement->bindValue(':username',$username);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM)[0];
    return password_verify($password, $result);
}

#logs in with the ID
function LoginID($id, $password) {
    global $db;
    $statement = $db->prepare('SELECT "password" FROM "users" WHERE "id" = :id');
    $statement->bindValue(':id',$id);
    $result = $statement->execute()->fetchArray(SQLITE3_NUM)[0];
    return password_verify($password, $result);
}

function GetAllAccounts() {
    global $db;
    $statement = $db->prepare('SELECT id, username FROM "users"');
    $results = [];
    $result = $statement->execute();
    $nextItem = $result->fetchArray(SQLITE3_NUM);
    while ($nextItem) {
        $results[] = $nextItem;
        $nextItem = $result->fetchArray(SQLITE3_NUM);
    }
    return $results;
}


//not really the right place for it but this function is handy...
function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}

?>