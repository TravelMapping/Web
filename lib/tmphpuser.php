<?php
/*
     PHP to include in any Travel Mapping page that needs to
     set the $tmuser variable based on the u= QS parameter
     or the cookie.

     This has to be done before any output is generated to avoid
     a warning from setcookie, so this file should be required
     as the first line of any other php file that needs to use it.

     Also take care of the $tmunits variable based on the units=
     QS parameter and/or the "units" cookie.
*/

// u= is the user, stored in $tmuser variable, can also come from the
// lastuser cookie, and will be stored in that cookie when specified
// note: must be alphanumeric or is ignored
define("TM_NO_USER", "null");
$tmuser = TM_NO_USER;

if (array_key_exists("u", $_GET)) {
    $tmusertemp = str_replace("_", "", $_GET['u']);
    if (ctype_alnum($tmusertemp)) {
        $tmuser = $_GET['u'];
        setcookie("lastuser", $tmuser, time() + (86400 * 30), "/");
    }
} else if (isset($_COOKIE['lastuser'])) {
    header("Location: ?u=" . $_COOKIE['lastuser'] . "&" . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)); /* Redirect browser */
    exit;
}

// units= is the units preference, stored in $tmunits variable, can
// also come from units cookie, and will be stored in that cookie
// when specified
$tmunits = "miles";
// associative array of supported units and their conversions from miles
$tm_supported_units = array(
  "miles"=>1.0,
  "km"=>1.609344,
  "ft"=>5280,
  "meters"=>1609.344
);


if (array_key_exists("units", $_GET)) {
   $unitsparam = strtolower($_GET['units']);
   if (array_key_exists($unitsparam, $tm_supported_units)) {
      $tmunits = $unitsparam;
      setcookie("units", $unitsparam, time() + (86400 * 30), "/");
   }
   else {
      // default to miles for unknown units
      $tmunits = "miles";
      setcookie("units", "miles", time() + (86400 * 30), "/");
   }
}
else if (isset($_COOKIE['units'])) {
   header("Location: ?units=" .$_COOKIE['units'] . "&" . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)); /* Redirect browser */
   exit;
}

?>
