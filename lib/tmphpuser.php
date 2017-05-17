<?php
/*
     PHP to include in any Travel Mapping page that needs to
     set the $tmuser variable based on the u= QS parameter
     or the cookie.

     This has to be done before any output is generated to avoid
     a warning from setcookie, so this file should be required
     as the first line of any other php file that needs to use it.

     Also take care of the $tmmetric variable based on the metric=
     QS parameter and/or the "metric" cookie.
*/

// u= is the user, stored in $tmuser variable, can also come from the
// lastuser cookie, and will be stored in that cookie when specified
define("TM_NO_USER", "null");
$tmuser = TM_NO_USER;

if (array_key_exists("u", $_GET)) {
    $tmuser = $_GET['u'];
    setcookie("lastuser", $tmuser, time() + (86400 * 30), "/");
} else if (isset($_COOKIE['lastuser'])) {
    header("Location: ?u=" . $_COOKIE['lastuser'] . "&" . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)); /* Redirect browser */
    exit;
}

// metric= is the metric preference, stored in $tmmetric variable, can
// also come from metric cookie, and will be stored in that cookie
// when specified
$tmmetric = FALSE;

if (array_key_exists("metric", $_GET)) {
   if (strcasecmp($_GET['metric'], "true") == 0) {
      $tmmetric = TRUE;
      setcookie("metric", "true", time() + (86400 * 30), "/");
   }
   else {
      $tmmetric = FALSE;
      setcookie("metric", "false", time() + (86400 * 30), "/");
   }
}
else if (isset($_COOKIE['metric'])) {
   header("Location: ?metric=" .$_COOKIE['metric'] . "&" , parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)); /* Redirect browser */
   exit;
}
?>
