<?php
$user = "null";

if (array_key_exists("u", $_GET)) {
    $user = $_GET['u'];
    setcookie("lastuser", $user, time() + (86400 * 30), "/");
} else if (isset($_COOKIE['lastuser'])) {
    header("Location: ?u=" . $_COOKIE['lastuser']); /* Redirect browser */
    exit();
}

$dbname = "TravelMapping";
if (isset($_COOKIE['currentdb'])) {
    $dbname = $_COOKIE['currentdb'];
}

if (array_key_exists("db", $_GET)) {
    $dbname = $_GET['db'];
    setcookie("currentdb", $dbname, time() + (86400 * 30), "/");
}
?>