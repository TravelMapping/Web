<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Highway Browser Main Page.
 * If a root is supplied, a map will show that root's path along with its waypoints.
 * Otherwise, it will show a list of routes that the user can select from, with filters by region and system availible.
 * If all three of lat, lon, and zoom are provided, the map will initialize to those coordinates and zoom level.
 * Otherwise, the map will pan and zoom to fit the entire route at the center.
 * URL Params:
 *  r - root of route to view waypoints for. When set, the page will display a map with the route params. (required for displaying map)
 *  u - user to display highlighting for on map (optional)
 *  lat - initial latitude at center of map
 *  lon - initial longitude at center of map
 *  zoom - initial zoom level of map
 *  rg - region to filter for on the highway browser list (optional)
 *  sys - system to filter for on the highway browser list (optional)
 *  ([r [u] [lat lon zoom]] [rg] [sys])
 ***
 -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
    <link rel="stylesheet" type="text/css" href="/fonts/roadgeek.css" />
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
    <style type="text/css">
        #headerbox {
            position: absolute;
            top: 0px;
            bottom: 50px;
            width: 100%;
            overflow: hidden;
            text-align: center;
            font-size: 20px;
            font-family: "Times New Roman", serif;
            font-style: bold;
        }

        #routebox {
            position: fixed;
            left: 0px;
            top: 110px;
            bottom: 0px;
            width: 100%;
            overflow: auto;
        }

        #pointbox {
            position: fixed;
            left: 0px;
            top: 30px;
            right: 275px;
            bottom: 0px;
            width: 275px;
            overflow: auto;
            font-size: 18px;
        }

        #controlbox {
            position: fixed;
            top: 30px;
            bottom: 60px;
            height: 100%;
            left: 300px;
            right: 0px;
            overflow: auto;
            padding: 5px;
            font-size: 18px;
        }

        #map {
            position: absolute;
            top: 70px;
            bottom: 0px;
            left: 275px;
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

        #pointbox table {
            width: 95%;
            margin-bottom: 15px;
        }

        #routeInfo td {
            text-align: right;
        }

	.status-active {
	    background-color: #CCFFCC;
            font-size: 14px;
	}

	.status-preview {
	    background-color: #FFFFCC;
            font-size: 14px;
	}
	
	.status-devel {
	    background-color: #FFCCCC;
            font-size: 14px;
	}

    </style>
    <?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
    <?php
    // check for region and/or system parameters
    $regions = tm_qs_multi_or_comma_to_array("rg");
    if (count($regions) > 0) {
        $region = $regions[0];
        $regionName = tm_region_code_to_name($region);
    }
    else {
        $region = "";
        $regionName = "No Region Specified";
    }

    $systems = tm_qs_multi_or_comma_to_array("sys");
    if (count($systems) > 0) {
        $system = $systems[0];
        $systemName = tm_system_code_to_name($system);
    }
    else {
        $system = "";
        $systemName = "No System Specified";
    }

    // if a specific route is specified, that's what we'll view
    if (array_key_exists("r", $_GET)) {
        $routeparam = tm_validate_root($_GET['r']);
    } else {
        $routeparam = "";
    }

    // parse lat, lon, zoom parameters if present
    $lat = "null";
    $lon = "null";
    $zoom = "null";
    if (array_key_exists("lat", $_GET)) {
        $lat = floatval($_GET["lat"]);
    }
    if (array_key_exists("lon", $_GET)) {
        $lon = floatval($_GET["lon"]);
    }
    if (array_key_exists("zoom", $_GET)) {
        $zoom = intval($_GET["zoom"]);
    }

    ?>
    <?php tm_common_js(); ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    <script type="application/javascript">
        $(document).ready(function () {
                $("#routes").tablesorter({
                    sortList: [[0, 0]],
                    headers: {0: {sorter: false}, 4: {sorter: false}, 5: {sorter: false}}
                });
            }
        );
    </script>
    <title><?php
        if ($routeparam != "") {
            $sql_command = "SELECT * FROM routes LEFT JOIN systems on routes.systemName = systems.systemName WHERE root = '" . $routeparam . "'";
            $res = tmdb_query($sql_command);
            $routeInfo = $res->fetch_array();
            $res->free();
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
$nobigheader = 1;

if ($routeparam == "") {
    echo "<body>\n";
    require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php";
    echo "<h1>Travel Mapping Highway Browser (Draft)</h1>";
    echo "<form id=\"selectHighways\" name=\"HighwaySearch\" action=\"/hb/index.php?u={$tmuser}\">";
    echo "<label for=\"sys\">Filter routes by...  System: </label>";
    tm_system_select(FALSE);
    echo "<label for=\"rg\"> Region: </label>";
    tm_region_select(FALSE);
    // should be taken care of by the cookie:
    //echo "<input type=\"hidden\" name=\"u\" value=\"{$tmuser}\" />";
    echo "<input type=\"submit\" value=\"Apply Filter\" /></form>";

} 
else {
    echo "<body onload=\"loadmap(); waypointsFromSQL(); updateMap(".$lat.",".$lon.",".$zoom.");\">\n";
    require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php";
      
}
?>
<script type="text/javascript">
    function initFloatingHeaders($table) {
        var $col = $table.find('tr.float');
        var $th = $col.find('th');
        var tag = "<tr style='height: 22px'></tr>";
        $(tag).insertAfter($col);
        $th.each(function (index) {
            var $row = $table.find('tr td:nth-child(' + (index + 1) + ')');
            if ($row.outerWidth() > $(this).width()) {
                $(this).width($row.width());
            } else {
                $row.width($(this).width());
            }
            var pos =  $row.position().left - $table.offset().left - 2;
            //console.log($table.offset().left);
            $(this).css({left: pos})
        });
    }

    $(document).ready(function () {
            <?php
                if ($routeparam != "") {

                } elseif (($region != "") or ($system != "")) {
                    echo <<<JS
                    routes = $("#routes");
                    routes.tablesorter({
                        sortList: [[0, 0], [6, 0]],
                        headers: {0: {sorter: false}}
                    });
                    initFloatingHeaders(routes);
JS;
                } else {
                    echo <<<JS
                    systems = $('#systemsTable');
                    systems.tablesorter({
                        sortList: [[0, 0], [4, 0], [3, 0]],
                        headers: {0: {sorter: false}}
                    });
JS;
                }
            ?>
        }
    );
</script>

<?php
if ($routeparam != "") {
    require $_SERVER['DOCUMENT_ROOT'] . "/shields/shieldgen.php";
    echo "<div id=\"pointbox\">\n";
    echo "<span class='status-".$routeInfo['level']."' style='text-align:center'><a href='/hb/?sys=".$routeInfo['systemName']."'>".$routeInfo['fullName']." (".$routeInfo['systemName'].")</a></span>\n";
    if ($routeInfo['level'] == 'preview') {
        echo "<span class='status-preview' style='text-align:center' title='Preview systems are substantially complete, but are undergoing final review and revisions. These may still undergo significant changes without notification in the updates log. Users can include these systems in list files for mapping and stats, but should expect to revise as the system progresses toward activation.  Users plotting travels in preview systems may wish to follow the forum discussions to see the progress and find out about revisions being made.'>Warning: ".$routeInfo['systemName']." is a preview system.</span>\n";
    }
    else if ($routeInfo['level'] == 'devel') {
        echo "<span class='status-devel' style='text-align:center' title='Devel systems are a work in progress. Routes in these systems are not yet available for mapping and inclusion in user stats, and are shown in the Highway Browser primarily for the benefit of the highway data managers who are developing the system. Once the system is substantially complete it will be upgraded to preview status, at which time users can begin to plot their travels in the system.'>Warning: ".$routeInfo['systemName']." is an in-development system.</span>\n";
    }
    echo "<span class='bigshield'>" . generate($routeparam, true) . "</span>\n";
    echo "<span>" . $routeInfo['banner'];
    if (strlen($routeInfo['city']) > 0) {
        echo " (" . $routeInfo['city'] . ")";
    }
    echo "</span>\n";
    echo "<span>.list name: <span style='font-family:courier'>" . $routeInfo['region'] . " " . $routeInfo['route'] . $routeInfo['banner'] . $routeInfo['abbrev'] . "</span></span>\n";

    echo "<table id='routeInfo' class=\"gratable\"><thead><tr><th colspan='2'>Route Stats</th></tr></thead><tbody>\n";
    $sql_command = "SELECT COUNT(DISTINCT traveler) as numUsers FROM clinchedOverallMileageByRegion";
    $row = tmdb_query($sql_command) -> fetch_assoc();
    $numUsers = $row['numUsers'];
    $sql_command = "SELECT * FROM routes WHERE root = '$routeparam'";
    $row = tmdb_query($sql_command) -> fetch_assoc();
    $totalMileage = $row['mileage'];
    $sql_command = <<<SQL
      SELECT
          COUNT(*) as numDrivers,
          IFNULL(SUM(clinched), 0) as numClinched,
          GROUP_CONCAT(traveler SEPARATOR ', ') as drivers,
          GROUP_CONCAT(IF(clinched = 1, traveler, null) separator ', ') as clinchers,
          AVG(mileage) as avgMileage
        FROM clinchedRoutes
        WHERE route = '$routeparam'
SQL;
    $row = tmdb_query($sql_command) -> fetch_assoc();
    $numDrivers = $row['numDrivers'];
    echo "    <tr><td class=\"important\">Total Length</td><td>".tm_convert_distance($totalMileage)." ".$tmunits."</td></tr>\n";
    echo "    <tr title=\"".$row['drivers']."\"><td>Total Drivers</td><td>".$numDrivers." (".round($numDrivers / $numUsers * 100, 2)."%)</td>\n";
    if ($numDrivers == 0) {
        echo "    <tr class=\"link\" title=\"".$row['clinchers']."\"><td>Total Clinched</td><td>".$row['numClinched']." (".round($row['numClinched'] / $numUsers * 100, 2)."%)</td>\n";
    } else {
        echo "    <tr class=\"link\" title=\"".$row['clinchers']."\"><td rowspan=\"2\">Total Clinched</td><td>".$row['numClinched']." (".round($row['numClinched'] / $numUsers * 100, 2)."%)</td>\n";
        echo "    <tr class=\"link\" title=\"".$row['clinchers']."\"><td>".round($row['numClinched'] / $numDrivers * 100, 2)."% of drivers</td>\n";
    }
    echo "    <tr><td>Average Traveled</td><td>".tm_convert_distance(round($row['avgMileage'],2))." ".$tmunits." (".round(100 * $row['avgMileage'] / $totalMileage, 2)."%)</td></tr>\n";
    if ($tmuser != "null") {
      $sql_command = "SELECT mileage FROM clinchedRoutes where traveler='" . $tmuser . "' AND route='" . $routeparam . "'";
      $row = tmdb_query($sql_command) -> fetch_assoc();
      echo "    <tr><td>{$tmuser} Traveled</td><td>".tm_convert_distance($row['mileage'])." ".$tmunits." (".round(100 * $row['mileage'] / $totalMileage, 2)."%)</td></tr>\n";
    }
    echo"</tbody></table>\n";
    echo "<table id='waypoints' class=\"gratable\"><thead><tr><th colspan=\"2\">Waypoints</th></tr><tr><th>Name</th><th title='Percent of people who have driven this route who have driven the segment starting at this point.'>%</th></tr></thead><tbody>\n";
    $sql_command = <<<SQL
        SELECT pointName, latitude, longitude, driverPercent, segmentId
        FROM waypoints
        LEFT JOIN (
            SELECT
              waypoints.pointId,
              sum(!ISNULL(clinched.traveler)) / $numDrivers * 100 as driverPercent,
              segments.segmentId
            FROM segments
            LEFT JOIN clinched ON segments.segmentId = clinched.segmentId
            LEFT JOIN waypoints ON segments.waypoint1 = waypoints.pointId
            WHERE segments.root = '$routeparam'
            GROUP BY segments.segmentId
        ) as pointStats on pointStats.pointId = waypoints.pointId
        WHERE root = '$routeparam';
SQL;
    $res = tmdb_query($sql_command);
    $waypointnum = 0;
    while ($row = $res->fetch_assoc()) {
        # only visible points should be in this table
        if (!startsWith($row['pointName'], "+")) {
	    if (tm_count_rows("clinched", "WHERE traveler='" .$tmuser."' AND segmentId='".$row['segmentId']."'") > 0) {
		$color1 = "rgb(255,167,167)";
	    }
	    else {	      
		$color1 = "rgb(255,255,255)";
	    }
            $colorFactor = $row['driverPercent'] / 100;
            $colors = [255, 255 - round($colorFactor * 128), 255 - round($colorFactor * 128)];
            echo "<tr onClick='javascript:labelClick(" . $waypointnum . ",\"" . $row['pointName'] . "\"," . $row['latitude'] . "," . $row['longitude'] . ",0);'><td class='link' style='background-color: ".$color1."'>" . $row['pointName'] . "</td><td style='background-color: rgb({$colors[0]},{$colors[1]},{$colors[2]})'>";
            if ($row['driverPercent'] != null) {
                echo round($row['driverPercent'],2);
            }
            echo "</td></tr>\n";
        }
        $waypointnum = $waypointnum + 1;
    }
    $res->free();
    echo <<<ENDA
</table>
</div>
  <div id="controlbox">
      <span id="controlboxroute">
ENDA;
    if ($routeparam != "") {
        echo "<table><tbody><tr><td>";
    	echo "<a href='/user/mapview.php?rte={$routeInfo['route']}'>Related Routes</a>";
        echo "</td><td>";
        echo "<input id=\"showMarkers\" type=\"checkbox\" name=\"Show Markers\" onclick=\"showMarkersClicked()\" checked=\"false\" />&nbsp;Show Markers&nbsp;";
        echo "</td><td>";
        echo "<form id=\"userForm\" action=\"/hb/index.php\">";
        echo "User: ";
        tm_user_select();
	echo "<label>Units: </label>\n";
        tm_units_select();
        echo "</td><td>";
        echo "<input type=\"hidden\" name=\"r\" value=\"".$routeparam."\" />";
        echo "<input type=\"submit\" value=\"Apply\" />";
        echo "</td><td>";
        echo "<a href='/hb/?r=".$routeparam."'>Zoom to Fit</a>";
        echo "</td></tr></tbody></table>\n";
    }
    echo <<<ENDB
  </span>
</div>
<div id="map">
</div>
ENDB;
} elseif (($region != "") or ($system != "")) {  // we have no r=, so we will show a list of all
    $sql_command = "SELECT * FROM routes LEFT JOIN systems ON systems.systemName = routes.systemName";
    // check for query string parameter for system and region filters
    if ($system != "") {
        $sql_command .= " WHERE routes.systemName = '" .$system. "'";
        if ($region != "") {
            $sql_command .= "AND routes.region = '" .$region. "'";
        }
    } else if ($region != "") {
        $sql_command .= " WHERE routes.region = '" .$region. "'";
    }

    $sql_command .= ";";
    echo "<div id=\"routebox\">\n";
    echo "<table class=\"gratable tablesorter ws_data_table\" id=\"routes\"><thead><tr><th colspan=\"7\">Select Route to Display (click a header to sort by that column)</th></tr><tr class='float'><th class=\"sortable\">Tier</th><th class=\"sortable\">System</th><th class=\"sortable\">Region</th><th class=\"sortable\">Route&nbsp;Name</th><th>.list Name</th><th class=\"sortable\">Level</th><th>Root</th></tr></thead><tbody>\n";
    $res = tmdb_query($sql_command);
    while ($row = $res->fetch_assoc()) {
        echo "<tr class=\"notclickable status-" . $row['level'] . "\"><td>{$row['tier']}</td><td>" . $row['systemName'] . "</td><td>" . $row['region'] . "</td><td>" . $row['route'] . $row['banner'];
        if (strcmp($row['city'], "") != 0) {
            echo " (" . $row['city'] . ")";
        }
        echo "</td><td>" . $row['region'] . " " . $row['route'] . $row['banner'] . $row['abbrev'] . "</td><td>" . $row['level'] . "</td><td><a href=\"/hb/index.php?u={$tmuser}&r=" . $row['root'] . "\">" . $row['root'] . "</a></td></tr>\n";
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
    $res = tmdb_query($sql_command);
    while ($row = $res->fetch_assoc()) {
        $linkJS = "window.open('/hb/index.php?sys={$row['systemName']}&u={$tmuser}')";
        echo "<tr class='status-" . $row['level'] . "' onClick=\"$linkJS\">";
        if (strlen($row['name']) > 15) {
            echo "<td>{$row['code']}</td>";
        } else {
            echo "<td>{$row['name']}</td>";
        }

        echo "<td>{$row['fullName']}</td><td>{$row['systemName']}</td><td>{$row['level']}</td><td>Tier {$row['tier']}</td></tr>\n";
    }

    echo "</tbody></table>";
}
$tmdb->close();
?>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<script type="application/javascript" src="../lib/waypoints.js.php?<?php echo "r=$routeparam&u=$tmuser";?>"></script>
</html>
