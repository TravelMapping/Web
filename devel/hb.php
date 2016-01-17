<?php
include $_SERVER['DOCUMENT_ROOT']."/shields/index.php";

if (array_key_exists("u", $_GET)) {
    setcookie("lastuser", $_GET['u'], time() + (86400 * 30), "/");
} else if (isset($_COOKIE['lastuser'])) {
    $_GET['u'] = $_COOKIE['lastuser'];
}

$dbname = "TravelMapping";
if (isset($_COOKIE['currentdb'])) {
    $dbname = $_COOKIE['currentdb'];
}

if (array_key_exists("db", $_GET)) {
    $dbname = $_GET['db'];
    setcookie("currentdb", $dbname, time() + (86400 * 30), "/");
}

if (array_key_exists("rg", $_GET) and strlen($_GET['rg']) > 0) {
    $region = $_GET['rg'];
}
if (array_key_exists("sys", $_GET) and strlen($_GET['sys']) > 0) {
    $system = $_GET['sys'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Highway Browser Main Page. If a root is supplied, a map will show that root's path along with its waypoints.
 * Otherwise, it will show a list of routes that the user can select from, with filters by region and system availible.
 * URL Params:
 *  r - root of route to view waypoints for. When set, the page will display a map with the route params. (required for displaying map)
 *  u - user to display highlighting for on map (optional)
 *  rg - region to filter for on the highway browser list (optional)
 *  sys - system to filter for on the highway browser list (optional)
 *  db - database to use (optional, defaults to TravelMapping
 *  ([r [u]] [rg | sys], [db])
 ***
 -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
    <link rel="stylesheet" type="text/css" href="/fonts/roadgeek.css">
    <style type="text/css">
        #headerbox {
            position: absolute;
            top: 0px;
            bottom: 50px;
            width: 100%;
            overflow: hidden;
            text-align: center;
            font-size: 30px;
            font-family: "Times New Roman", serif;
            font-style: bold;
        }

        #routebox {
            position: fixed;
            left: 0px;
            top: 80px;
            bottom: 0px;
            width: 100%;
            overflow: auto;
        }

        #pointbox {
            position: fixed;
            left: 0px;
            top: 70px;
            right: 400px;
            bottom: 0px;
            width: 400px;
            overflow: auto;
        }

        #controlbox {
            position: fixed;
            top: 65px;
            bottom: 100px;
            height: 100%;
            left: 400px;
            right: 0px;
            overflow: auto;
            padding: 5px;
            font-size: 20px;
        }

        #map {
            position: absolute;
            top: 100px;
            bottom: 0px;
            left: 400px;
            right: 0px;
            overflow: hidden;
        }

        #map * {
            cursor: crosshair;
        }

        #waypoints:hover {
            cursor: pointer;
        }

        #pointbox span {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 5px;
        }


    </style>
    <script
        src="http://maps.googleapis.com/maps/api/js?sensor=false"
        type="text/javascript"></script>
    <!-- jQuery -->
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- TableSorter -->
    <script src="/lib/jquery.tablesorter.min.js"></script>

    <?php
    // establish connection to db: mysql_ interface is deprecated, should learn new options
    $db = new mysqli("localhost", "travmap", "clinch", $dbname) or die("Failed to connect to database");
    # functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
    function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    if (array_key_exists("r", $_GET)) {
        $showingmap = 1;
    } else {
        $showingmap = 0;
    }
    ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    <script>
        function waypointsFromSQL() {
            <?php
              if (array_key_exists("r",$_GET)) {
                // select all waypoints matching the root given in the "r=" query string parameter
                $sql_command = "SELECT pointName, latitude, longitude FROM waypoints WHERE root = '".$_GET['r']."';";
                $res = $db->query($sql_command);
                $pointnum = 0;
                while ($row = $res->fetch_assoc()) {
                  echo "waypoints[".$pointnum."] = new Waypoint(\"".$row['pointName']."\",".$row['latitude'].",".$row['longitude'].");\n";
                  $pointnum = $pointnum + 1;
                }
                $res->free();
              }
              else {
                // nothing to select waypoints, we're done
                echo "return;\n";
              }
              // check for query string parameter for traveler clinched mapping of route
              if (array_key_exists("u",$_GET)) {
                 echo "traveler = '".$_GET['u']."';\n";
                 if (array_key_exists("r",$_GET)) {
                   // retrieve list of segments for this route
                   echo "// SQL: select segmentId from segments where root = '".$_GET['r']."';\n";
                   $sql_command = "SELECT segmentId FROM segments WHERE root = '".$_GET['r']."';";
                   $res = $db->query($sql_command);
                   $segmentIndex = 0;
                   while ($row = $res->fetch_assoc()) {
                     echo "segments[".$segmentIndex."] = ".$row['segmentId'].";\n";
                     $segmentIndex = $segmentIndex + 1;
                   }
                   $res->free();
                   $sql_command = "SELECT segments.segmentId FROM segments RIGHT JOIN clinched ON segments.segmentId = clinched.segmentId WHERE segments.root='".$_GET['r']."' AND clinched.traveler='".$_GET['u']."';";
                   $res = $db->query($sql_command);
                   $segmentIndex = 0;
                   while ($row = $res->fetch_assoc()) {
                     echo "clinched[".$segmentIndex."] = ".$row['segmentId'].";\n";
                     $segmentIndex = $segmentIndex + 1;
                   }
                   $res->free();
                 }
                 echo "mapClinched = true;\n";
              }
            ?>
            genEdges = true;
        }

        $(document).ready(function () {
                $("#routes").tablesorter({
                    sortList: [[0, 0]],
                    headers: {0: {sorter: false}, 4: {sorter: false}, 5: {sorter: false}}
                });
            }
        );
    </script>
    <title><?php
        if ($showingmap == 1) {
            $sql_command = "SELECT * FROM routes WHERE root = '".$_GET['r']."'";
            $routeInfo = $db->query($sql_command)->fetch_array();
            echo $routeInfo['region'] . " " . $routeInfo['route'];
            if (strlen($routeInfo['banner']) > 0) {
                echo " " . $routeInfo['banner'];
            }
            if (strlen($routeInfo['city']) > 0) {
                echo " (" . $routeInfo['city'] . ")";
            }
            echo " - ";
        }
        ?>Travel Mapping Highway Browser (Draft)</title>
</head>

<?php
if ($showingmap == 0) {
    echo "<body>\n";
    if (array_key_exists("u", $_GET)) echo "<a href=\"/user?u=".$_GET['u']."\">".$_GET['u']."</a>-";
    echo "<a href=\"/\">Home</a>-";
    echo "<a href=\"/hbtest\">Highway Browser</a>";
    echo "<form id=\"selectHighways\" name=\"HighwaySearch\" action=\"hb.php\">";
    echo "<label for=\"sys\">Filter routes by...  System: </label>";
    echo "<input id=\"sys\" type=\"text\" placeholder=\"usaus\" name=\"sys\" value=\"" . $_GET["sys"] . "\"></input>";
    echo "<label for=\"rg\"> Region: </label>";
    echo "<input id=\"rg\" type=\"text\" placeholder=\"AL\" name=\"rg\" value=\"" . $_GET["rg"] . "\"></input>";
    echo "<input type=\"submit\" value=\"Search\"></input></form>";

} else {
    echo "<body onload=\"loadmap();\">\n";
    if (array_key_exists("u", $_GET)) echo "<a href=\"/user?u=".$_GET['u']."\">".$_GET['u']."</a>-";
    echo "<a href=\"/\">Home</a>-";
    echo "<a href=\"/hbtest\">Highway Browser</a>";
}
?>

<h1>Travel Mapping Highway Browser (Draft)</h1>
<script type="text/javascript">
    function collapse_col($col) {

    }

    $(document).ready(function () {
            $("#routes").tablesorter({
                sortList: [[0, 0]],
                headers: {0: {sorter: false},}
            });
            $("#systemsTable").tablesorter({
                sortList: [[0, 0], [4, 0], [3, 0]],
                headers: {0: {sorter: false},}
            });
        }
    );
</script>

<?php
if ($showingmap == 1) {
    echo "<div id=\"pointbox\">\n";
    echo "<span class='bigshield'>".generate($_GET['r'], true)."</span>";
    echo "<span><a href='/hbtest/mapview.php?u={$_GET['u']}&rte={$routeInfo['route']}'>View Associated Routes</a></span>";
    echo "<table id='waypoints' class=\"gratable\"><thead><tr><th colspan=\"2\">Waypoints</th></tr><tr><th>Coordinates</th><th>Waypoint Name</th></tr></thead><tbody>\n";
    $sql_command = "SELECT pointName, latitude, longitude FROM waypoints WHERE root = '" . $_GET['r'] . "';";
    $res = $db->query($sql_command);
    $waypointnum = 0;
    while ($row = $res->fetch_assoc()) {
        # only visible points should be in this table
        if (!startsWith($row['pointName'], "+")) {
            echo "<tr><td>(" . $row['latitude'] . "," . $row['longitude'] . ")</td><td class='link'><a onClick='javascript:LabelClick(" . $waypointnum . ",\"" . $row['pointName'] . "\"," . $row['latitude'] . "," . $row['longitude'] . ",0);'>" . $row['pointName'] . "</a></td></tr>\n";
        }
        $waypointnum = $waypointnum + 1;
    }
    $res->free();
    echo <<<ENDA
</table>
</div>
  <div id="controlbox">
    <input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()" checked="false">&nbsp;Show Markers
      
      <span id="controlboxroute">
ENDA;
    if (array_key_exists("r", $_GET)) {
        $sql_command = "SELECT region, route, banner, city FROM routes WHERE root = '" . $_GET['r'] . "';";
        $res = $db->query($sql_command);
        $row = $res->fetch_assoc();
        echo $row['region'] . " " . $row['route'];
        if (strlen($row['banner']) > 0) {
            echo " " . $row['banner'];
        }
        if (strlen($row['city']) > 0) {
            echo " (" . $row['city'] . ")";
        }
        echo ": ";
        $res->free();
    }
    echo <<<ENDB
  </span>
<span id="controlboxinfo"></span>
</div>
<div id="map">
</div>
ENDB;
} elseif (!is_null($region) or !is_null($system)) {  // we have no r=, so we will show a list of all
    $sql_command = "SELECT * FROM routes LEFT JOIN systems ON systems.systemName = routes.systemName";
    //check for query string parameter for system and region filters
    if (array_key_exists("sys", $_GET) && strlen($_GET["sys"]) > 0) {
        $sql_command .= " where routes.systemName = '" . $_GET["sys"] . "'";
        if (array_key_exists("rg", $_GET) && strlen($_GET["rg"]) > 0) {
            $sql_command .= "and routes.region = '" . $_GET["rg"] . "'";
        }
    } else if (array_key_exists("rg", $_GET) && strlen($_GET["rg"]) > 0) {
        $sql_command .= " where routes.region = '" . $_GET["rg"] . "'";
    }

    $sql_command .= ";";
    echo "<!-- SQL: " . $sql_command . " -->\n";
    echo "<div id=\"routebox\">\n";
    echo "<table class=\"gratable tablesorter ws_data_table\" id=\"routes\"><thead><tr><th colspan=\"6\">Select Route to Display (click a header to sort by that column)</th></tr><tr><th class=\"sortable\">System</th><th class=\"sortable\">Region</th><th class=\"sortable\">Route Name</th><th>.list Name</th><th class=\"sortable\">Level</th><th>Root</th></tr></thead><tbody>\n";
    $res = $db->query($sql_command);
    while ($row = $res->fetch_assoc()) {
        echo "<tr class=\"notclickable status-" . $row['level'] . "\"><td>" . $row['systemName'] . "</td><td>" . $row['region'] . "</td><td>" . $row['route'] . $row['banner'];
        if (strcmp($row['city'], "") != 0) {
            echo " (" . $row['city'] . ")";
        }
        echo "</td><td>" . $row['region'] . " " . $row['route'] . $row['banner'] . $row['abbrev'] . "</td><td>" . $row['level'] . "</td><td><a href=\"hb.php?r=" . $row['root'] . "\">" . $row['root'] . "</a></td></tr>\n";
    }
    $res->free();
    echo "</table></div>\n";
} else {
    //We have no filters at all, so display list of systems as a landing page.
    echo <<<HTML
    <table class="gratable tablesorter" id="systemsTable">
        <caption>TIP: Click on a column header to sort. Hold SHIFT to sort by multiple columns.</caption>
        <thead>
            <tr><th colspan="5">List of Systems</th></tr>
            <tr><th class="sortable">Country</th><th class="sortable">System</th><th class="sortable">Code</th><th class="sortable">Status</th><th class="sortable">Level</th></tr>
        </thead>
        <tbody>
HTML;

    $sql_command = "SELECT * FROM systems LEFT JOIN countries ON countryCode = countries.code";
    $res = $db->query($sql_command);
    while ($row = $res->fetch_assoc()) {
        $linkJS = "window.open('hb.php?sys={$row['systemName']}')";
        echo "<tr class='status-".$row['level']."' onClick=\"$linkJS\">";
        if (strlen($row['name']) > 15) {
            echo "<td>{$row['code']}</td>";
        } else {
            echo "<td>{$row['name']}</td>";
        }

        echo "<td>{$row['fullName']}</td><td>{$row['systemName']}</td><td>{$row['level']}</td><td>Tier {$row['tier']}</td></tr>\n";
    }

    echo "</tbody></table>";
}
$db->close();
?>
</body>
</html>
