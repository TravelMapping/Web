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

// Function to retrieve the name of a region by code from the DB
function tm_region_code_to_name($code) {
    global $tmdb;
    $res = tmdb_query("SELECT * FROM regions where code = '".$code."';");
    $row = $res->fetch_assoc();
    $ans = $row['name'];
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


?>
<!-- /lib/tmphpfuncs.php END -->
