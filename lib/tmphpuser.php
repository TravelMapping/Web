<?php
/*
     PHP to include in any Travel Mapping page that needs to
     set the $tmuser variable based on the u= QS parameter
     or the cookie.

     This has to be done before any output is generated to avoid
     a warning from setcookie, so this file should be required
     as the first line of any other php file that needs to use it.
*/

// u= is the user, stored in $tmuser variable, can also come from the
// lastuser cookie, and will be stored in that cookie when specified
$tmuser = "null";

if (array_key_exists("u", $_GET)) {
    $tmuser = $_GET['u'];
    setcookie("lastuser", $tmuser, time() + (86400 * 30), "/");
} else if (isset($_COOKIE['lastuser'])) {
    header("Location: ?u=" . $_COOKIE['lastuser']); /* Redirect browser */
    exit;
}
?>
