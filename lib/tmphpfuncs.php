<!-- /lib/tmphbfuncs.php: common PHP functionality for Travel Mapping -->
<?php
/*
     PHP to include in any Travel Mapping page that needs to access the DB.

     This parses the dbname= query string parameter to override the
     default DB (TravelMapping), but no longer stores it in a cookie
     since this functionality would be used only rarely.  Other DB
     connection parameters can also be specified with the QS
     parameters dbuser, dbpasswd, and dbhost.

     It then attempts to connect to the mysql DB on localhost with that
     name using the mysqli interface.  On failure, an error message is
     displayed.

     Also parses other commonly used QS parameters and places the values
     in PHP variables.

     This would normally be included at the start of the page, right 
     around the <title> element.
*/

// always attempt to establish a connection to the db, allow QS parameters
// to override defaults

$tmdbname = "TravelMapping";
$tmdbuser = "travmap";
$tmdbpasswd = "clinch";
$tmdbhost = "localhost";

// Google Maps API Key
// FIXME: Change to project-specific key (this is my (Thing342's) personal key)
$gmaps_api_key = 'AIzaSyDAKyOlyGyVeWVEaMqRmWkYQh-5RkwhUY0';

if (array_key_exists("dbname", $_GET)) {
    $tmdbname = $_GET['dbname'];
}

if (array_key_exists("dbuser", $_GET)) {
    $tmdbuser = $_GET['dbuser'];
}

if (array_key_exists("dbpasswd", $_GET)) {
    $tmdbpasswd = $_GET['dbpasswd'];
}

if (array_key_exists("dbhost", $_GET)) {
    $tmdbhost = $_GET['dbhost'];
}

// sqldebug QS param to tell pages to show SQL commands in HTML comments
$tmsqldebug = FALSE;

if (array_key_exists("sqldebug", $_GET)) { 
   $tmsqldebug = TRUE;
}

// get other common QS parameters

// Note: u= is the user, stored in $tmuser variable, but this
// is set instead by the tmphpuser.php file which needs
// to be included before any other output is generated
// to avoid warnings from setcookie

// make the connection
echo "<!-- mysqli connecting to database ".$tmdbname." on ".$tmdbhost." -->\n";
mysqli_report(MYSQLI_REPORT_STRICT);
try {
    $tmdb = new mysqli($tmdbhost, $tmdbuser, $tmdbpasswd, $tmdbname);
}
catch ( Exception $e ) {
   echo "<h1 style='color: red'>Failed to connect to database ".$tmdbname." on ".$tmdbhost." Please try again later.</h1>";
   exit;
}

// function to combine a query with a little error check
function tmdb_query($sql_command) {

    global $tmdb;
    global $tmsqldebug;
    if ($tmsqldebug) {
        echo "<!-- SQL: ".$sql_command." -->\n";
    }
    $res = $tmdb->query($sql_command);
    if ($res) return $res;
    die("<h1 style=\"color: red\">Query failed: ".$sql_command."</h1>");
}

// function to call to free the DB instance (or just put this code
// at the bottom of a page

function tmdb_close() {
    global $tmdb;
    $tmdb->close();
}

// function to generate a populated region "select" object, but only regions
// where some route exists, 
function tm_region_select($multiple) {
    global $tmdb;

    if ($multiple) {
        echo "<select name=\"rg[]\" multiple=\"multiple\">\n";
    }
    else {
        echo "<select name=\"rg\">\n";
    }
    $regions = tm_qs_multi_or_comma_to_array("rg");
    echo "<option value=\"null\">[None Selected]</option>\n";
    $res = tmdb_query("SELECT * FROM regions WHERE code IN (SELECT region FROM routes) ORDER BY country;");
    while ($row = $res->fetch_assoc()) {
        echo "<option value=\"".$row['code']."\"";
        if (in_array($row['code'], $regions)) {
            echo " selected=\"selected\"";
        }
	echo ">".$row['name']."</option>\n";
    }
    $res->free();
    echo "</select>\n";
}

// function to generate a populated systems "select" object
function tm_system_select($multiple) {
    global $tmdb;

    if ($multiple) {
        echo "<select name=\"sys[]\" multiple=\"multiple\">\n";
    }
    else {
        echo "<select name=\"sys\">\n";
    }
    $systems = tm_qs_multi_or_comma_to_array("sys");
    echo "<option value=\"null\">[None Selected]</option>\n";
    $res = tmdb_query("SELECT * FROM systems;");
    while ($row = $res->fetch_assoc()) {
        echo "<option value=\"".$row['systemName']."\"";
        if (in_array($row['systemName'], $systems)) {
            echo " selected=\"selected\"";
        }
	echo ">".$row['fullName']."</option>\n";
    }
    $res->free();
    echo "</select>\n";
}

// function to generate a user selection "select" object
function tm_user_select() {
    global $tmdb;
    global $tmuser;
    echo "<select name=\"u\">\n";
    echo "<option value=\"null\">[None Selected]</option>\n";
    $res = tmdb_query("SELECT DISTINCT traveler FROM clinchedOverallMileageByRegion ORDER by traveler ASC;");
    while ($row = $res->fetch_assoc()) {
        echo "<option value=\"".$row['traveler']."\"";
        if ($row['traveler'] == $tmuser) {
	    echo " selected=\"selected\"";
        }
	echo ">".$row['traveler']."</option>\n";
    }
    $res->free();
    echo "</select>\n";
}

// function to generate a user selection input form
function tm_user_select_form() {
    // TODO: action should come as a parameter
    echo "<form id=\"userselect\" action=\".\"><p>\n";
    echo "<label>Current User: </label>\n";
    tm_user_select();
    echo "<input type=\"submit\" value=\"Select User\" />\n";
    echo "</p></form>\n";
}

// function to get a count from a table of rows matching a "where" clause
function tm_count_rows($table, $clause) {
    global $tmdb;
    $sql_command = "SELECT COUNT(*) AS c FROM ".$table." ".$clause.";";
    $res = tmdb_query($sql_command);
    $row = $res->fetch_assoc();
    $ans = $row['c'];
    $res->free();
    return $ans;
}


// function to get a sum of a column from a table
function tm_sum_column($table, $column) {
    global $tmdb;
    $sql_command = "SELECT SUM(".$column.") AS s FROM ".$table.";";
    global $tmsqldebug;
    if ($tmsqldebug) {
        echo "<!-- SQL: ".$sql_command." -->\n";
    }
    $res = tmdb_query($sql_command);
    $row = $res->fetch_assoc();
    $ans = $row['s'];
    $res->free();
    return $ans;
}

// function to get a sum of a column from a table with a "where"
function tm_sum_column_where($table, $column, $where) {
    global $tmdb;
    $sql_command = "SELECT SUM(".$column.") AS s FROM ".$table." WHERE ".$where.";";
    global $tmsqldebug;
    if ($tmsqldebug) {
        echo "<!-- SQL: ".$sql_command." -->\n";
    }
    $res = tmdb_query($sql_command);
    $row = $res->fetch_assoc();
    $ans = $row['s'];
    $res->free();
    return $ans;
}

// Function to retrieve the name of a region by code from the DB
function tm_region_code_to_name($code) {
    global $tmdb;
    $res = tmdb_query("SELECT * FROM regions where code = '".$code."';");
    $row = $res->fetch_assoc();
    $ans = $row['name'];
    $res->free();
    return $ans;
}

// Function to retrieve the name of a system by code from the DB
function tm_system_code_to_name($code) {
    global $tmdb;
    $res = tmdb_query("SELECT * FROM systems where systemName = '".$code."';");
    $row = $res->fetch_assoc();
    $ans = $row['fullName'];
    $res->free();
    return $ans;
}

// Function to get an array of either multiple or comma-separated
// qs parameters.
//
// For example, either of the following:
// rg[]=VT&rg[]=MA&rg[]=NY
// rg=VT,MA,NY
//
// should get back an array containing VT, MA, and NY
//
// special functionality: ignore "null" entry/entries
function tm_qs_multi_or_comma_to_array($param) {

    $array = array();
    if (array_key_exists($param, $_GET)) {
        if (is_array($_GET[$param])) {
          foreach ($_GET[$param] as $p) {
            $array = array_merge($array, explode(',',$p));
          }
        }
        else {
          $array = explode(",", $_GET[$param]);
        }
    }
    return array_diff($array, array("null"));
}

// Function to generate JS code to fill in customColorCodes array based
// on colors QS parameter
function tm_generate_custom_colors_array() {

    // check for custom colors query string parameters
    $customColors = array();
    if (array_key_exists("colors",$_GET)) {
        $customColors = explode(';',$_GET['colors']);
        $colorNum = 0;
        foreach ($customColors as $customColor) {
            $colorEntry = array();
            $colorEntry = explode(':',$customColor);
            echo "customColorCodes[".$colorNum."] = { name: \"".$colorEntry[0]."\", unclinched: \"".$colorEntry[1]."\", clinched: \"".$colorEntry[2]."\" };\n";
            $colorNum = $colorNum + 1;
        }
    }
}

// get the timestamp of most recent DB update
function tm_update_time() {
    global $tmdb;
    global $tmdbname;
    $sql_command = "SELECT create_time FROM information_schema.tables WHERE TABLE_SCHEMA = '".$tmdbname."' ORDER BY create_time DESC;";
    global $tmsqldebug;
    if ($tmsqldebug) {
        echo "<!-- SQL: ".$sql_command." -->\n";
    }
    $res = tmdb_query($sql_command);
    $row = $res->fetch_assoc();
    $ans = $row['create_time'];
    $res->free();
    return $ans;
}

// function to find the rank of a given traveler in a SQL result
// ranked by a given field, rank is given in a returned row (an
// associative array) as the value associated with a new key 'rank'.
function tm_fetch_user_row_with_rank($res, $rankBy) {
    global $tmuser;
    $nextRank = 1;
    $rank = 1;
    $score = 0;
    $row = array();
    while($row['traveler'] != $tmuser && $row = $res->fetch_assoc()) {
        if ($score != $row[$rankBy]) {
            $score = $row[$rankBy];
            $rank = $nextRank;
        }
        $nextRank++;
        //error_log("($rank, {$row['traveler']}, {$row[$rankBy]})");
    }
    $row['rank'] = $rank;
    return $row;
}

// functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

?>
<!-- /lib/tmphpfuncs.php END -->
