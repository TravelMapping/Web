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

// function to generate a user selection input form
function tm_user_select_form() {
    global $tmdb;
    global $tmuser;
    echo "<form id=\"userselect\" action=\".\"><p>\n";
    echo "<label>Current User: </label>\n";
    //echo "<input type=\"text\" name=\"u\" form=\"userselect\" value=\"".$tmuser."\">\n";
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
    echo "<input type=\"submit\" value=\"Select User\" />\n";
    echo "</p></form>\n";
}

// function to get a count from a table of rows matching a "where" clause
function tm_count_rows($table, $clause) {
    global $tmdb;
    $sql_command = "SELECT COUNT(*) AS c FROM ".$table." ".$clause.";";
    global $tmsqldebug;
    if ($tmsqldebug) {
        echo "<!-- SQL: ".$sql_command." -->\n";
    }
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
?>
<!-- /lib/tmphpfuncs.php END -->
