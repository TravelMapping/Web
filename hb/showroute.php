<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Highway Browser Show Route page
 *
 * Two modes of operation, depending on whether cr parameter is provided
 *
 * r= the "chopped" route for the given TM root (e.g., ny.ny005) to display
 *
 * cr, if provided, will display the the entire "connected" route that contains
 *   the given TM root
 *
 * Other QS parameters:
 *
 *  u - user to display highlighting for on map (optional)
 *  lat - initial latitude at center of map
 *  lon - initial longitude at center of map
 *  zoom - initial zoom level of map
 *
 * If all three of lat, lon, and zoom are provided, the map will initialize
 * to those coordinates and zoom level.
 *
 * Otherwise, the map will pan and zoom to fit the entire route at the center.
 *
 *  r [cr] [u] [lat lon zoom]
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
    function showroute_error($msg) {

        global $tmdb;
        echo '<h1 style="color: red">'.$msg.'</h1>';
	$tmdb->close();
	echo '</body></html>';
	exit;
    }

    function check_root_error($qsparam, $rootparam, $origparam) {

        if ($rootparam == "toolong.root") {
            showroute_error("Parameter ".$qsparam."=".$origparam." is too long");
        }
        if ($rootparam == "noregion.root" ||
	    $rootparam == "badregion.root" ||
	    $rootparam == "badroute.root") {
            showroute_error("Parameter ".$qsparam."=".$origparam." is invalid.");
        }
    }
    
    // check for required parameters
    if (array_key_exists("r", $_GET)) {
        $rootparam = tm_validate_root($_GET['r']);
	check_root_error('r', $rootparam, $_GET['r']);
	$routetype = "chopped";
        $sql_command = "SELECT * FROM routes LEFT JOIN systems ON systems.systemName=routes.systemName WHERE root='". $rootparam."'";
        $res = tmdb_query($sql_command);
        $routeInfo = $res->fetch_assoc();
        $res->free();
	if ($routeInfo == NULL) {
            showroute_error("Route for r=".$rootparam." not found.");
        }
	$titleRoute = $routeInfo['region']." ".$routeInfo['route'].$routeInfo['banner'].$routeInfo['abbrev'];
    }
    else {
    	showroute_error("Query string parameter r= required.");
    }

    // build JS info about the roots to be displayed
    echo "<script type=\"application/javascript\">\n";
    echo "var showrouteParams = new Object();\n";
    echo "showrouteParams.roots = [];\n";
    
    // is the entire connected route requested?
    $connected = array_key_exists("cr", $_GET);
    if ($connected) {
        // TODO: factor out code from here and mapview into tmphpfuncs.php
        $result = tmdb_query("select firstRoot from connectedrouteroots where root='".$rootparam."';");
        $row = $result->fetch_assoc();
	$firstRoot = "";
	if ($row == NULL) {
	    $firstRoot = $rootparam;
	}
	else {
	    $firstRoot = $row['firstRoot'];
	}
	$result->free();
        echo "showrouteParams.roots.push('".$firstRoot."');\n";
        $result2 = tmdb_query("select root from connectedRouteRoots where firstRoot='".$firstRoot."';");
	while ($row2 = $result2->fetch_assoc()) {
            echo "showrouteParams.roots.push('".$row2['root']."');\n";
        }
	$result2->free();
	$result = tmdb_query("select * from connectedroutes where firstRoot='".$firstRoot."';");
	$connInfo = $result->fetch_assoc();
	$result->free();
	$titleRoute = $connInfo['route'].$connInfo['banner'];
	if ($connInfo['groupName'] != "") {
	   $titleRoute .= ' ('.$connInfo['groupName'].')';
	}
        echo "showrouteParams.connected = true;\n";
    }
    else {
        echo "showrouteParams.roots.push('".$rootparam."');\n";
        echo "showrouteParams.connected = false;\n";
    }
    echo "</script>\n";
    
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
    <script src="../lib/showroutefuncs.js" type="text/javascript"></script>
    <title><?php echo $titleRoute; ?> - TM Highway Browser</title>
</head>
<?php 
$nobigheader = 1;
echo "<body onload=\"showrouteStartup(".$lat.",".$lon.",".$zoom.");\">\n";
require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php";
require $_SERVER['DOCUMENT_ROOT'] . "/shields/shieldgen.php";
echo "<div id=\"pointbox\">\n";
echo "<span class='status-".$routeInfo['level']."' style='text-align:center'><a href='/hb/?sys=".$routeInfo['systemName']."'>".$routeInfo['fullName']." (".$routeInfo['systemName'].")</a></span>\n";
if ($routeInfo['level'] == 'preview') {
    $msg = 'Preview systems are substantially complete, but are undergoing final review and revisions. These may still undergo significant changes without notification in the updates log. Users can include these systems in list files for mapping and stats, but should expect to revise as the system progresses toward activation.  Users plotting travels in preview systems may wish to follow the forum discussions to see the progress and find out about revisions being made.';
    echo "<span class='status-preview' style='text-align:center' title='".$msg."'>Warning: ".$routeInfo['systemName']." is a preview system. <span onclick='alert(\"".$msg."\");'>(?)</span></span>\n";
}
else if ($routeInfo['level'] == 'devel') {
    $msg = 'Devel systems are a work in progress. Routes in these systems are not yet available for mapping and inclusion in user stats, and are shown in the Highway Browser primarily for the benefit of the highway data managers who are developing the system. Once the system is substantially complete it will be upgraded to preview status, at which time users can begin to plot their travels in the system.';
    echo "<span class='status-devel' style='text-align:center' title='".$msg."'>Warning: ".$routeInfo['systemName']." is an in-development system. <span onclick='alert(\"".$msg."\");'>(?)</span></span>\n";
}

// shield and other info, depending on whether we are showing
// connected or chopped route
// always have the shield based on the root
echo "<span class='bigshield'>".generate($rootparam, true)."</span>\n";
if ($connected) {
    echo "<span>" . $connInfo['banner'];
    if (strlen($connInfo['groupName']) > 0) {
        echo " (" . $connInfo['groupName'] . ")";
    }
    echo "</span>\n";
}
else {
    echo "<span>" . $routeInfo['banner'];
    if (strlen($routeInfo['city']) > 0) {
        echo " (" . $routeInfo['city'] . ")";
    }
    echo "</span>\n";
    echo "<span>.list name: <span style='font-family:courier'>" . $routeInfo['region'] . " " . $routeInfo['route'] . $routeInfo['banner'] . $routeInfo['abbrev'] . "</span></span>\n";
}

echo "<table id='routeInfo' class=\"gratable\"><thead><tr><th colspan='2'>Route Stats</th></tr></thead><tbody>\n";
$sql_command = "SELECT COUNT(DISTINCT traveler) as numUsers FROM clinchedOverallMileageByRegion";
$row = tmdb_query($sql_command) -> fetch_assoc();
$numUsers = $row['numUsers'];
$sql_command = "SELECT ROUND(mileage,4) as mileage FROM routes WHERE root = '$rootparam'";
$row = tmdb_query($sql_command) -> fetch_assoc();
$totalMileage = $row['mileage'];
$sql_command = <<<SQL
    SELECT
        COUNT(*) as numDrivers,
        IFNULL(SUM(clinched), 0) as numClinched,
        GROUP_CONCAT(traveler SEPARATOR ', ') as drivers,
        GROUP_CONCAT(IF(clinched = 1, traveler, null) separator ', ') as clinchers,
        ROUND(AVG(mileage),4) as avgMileage
      FROM clinchedRoutes
      WHERE route = '$rootparam'
SQL;
$row = tmdb_query($sql_command) -> fetch_assoc();
$numDrivers = $row['numDrivers'];
echo "    <tr><td class=\"important\">Total Length</td><td>".tm_convert_distance($totalMileage)." ".$tmunits."</td></tr>\n";
$style = 'style="background-color: '.tm_color_for_amount_traveled($numDrivers,$numUsers).';"';
echo "    <tr title=\"".$row['drivers']."\"><td>Total Drivers</td><td ".$style.">".$numDrivers." (".round($numDrivers / $numUsers * 100, 2)."%)</td>\n";
if ($numDrivers == 0) {
    $style = 'style="background-color: '.tm_color_for_amount_traveled($row['numClinched'],$numUsers).';"';
    echo "    <tr class=\"link\" title=\"".$row['clinchers']."\"><td>Total Clinched</td><td ".$style.">".$row['numClinched']." (".round($row['numClinched'] / $numUsers * 100, 2)."%)</td>\n";
}
else {
    $style = 'style="background-color: '.tm_color_for_amount_traveled($row['numClinched'],$numUsers).';"';
    echo "    <tr class=\"link\" title=\"".$row['clinchers']."\"><td rowspan=\"2\">Total Clinched</td><td ".$style.">".$row['numClinched']." (".round($row['numClinched'] / $numUsers * 100, 2)."%)</td>\n";
    $style = 'style="background-color: '.tm_color_for_amount_traveled($row['numClinched'],$numDrivers).';"';
    echo "    <tr class=\"link\" title=\"".$row['clinchers']."\"><td ".$style.">".round($row['numClinched'] / $numDrivers * 100, 2)."% of drivers</td>\n";
}
$style = 'style="background-color: '.tm_color_for_amount_traveled($row['avgMileage'],$totalMileage).';"';
echo "    <tr><td>Average Traveled</td><td ".$style.">".tm_convert_distance(round($row['avgMileage'],2))." ".$tmunits." (".round(100 * $row['avgMileage'] / $totalMileage, 2)."%)</td></tr>\n";
if ($tmuser != "null") {
    $sql_command = "SELECT round(mileage,4) as mileage FROM clinchedRoutes where traveler='" . $tmuser . "' AND route='" . $rootparam . "'";
    $row = tmdb_query($sql_command) -> fetch_assoc();
    $style = 'style="background-color: '.tm_color_for_amount_traveled($row['mileage'],$numUsers).';"';
    echo "    <tr><td>{$tmuser} Traveled</td><td ".$style.">".tm_convert_distance($row['mileage'])." ".$tmunits." (".round(100 * $row['mileage'] / $totalMileage, 2)."%)</td></tr>\n";
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
            WHERE segments.root = '$rootparam'
            GROUP BY segments.segmentId
        ) as pointStats on pointStats.pointId = waypoints.pointId
        WHERE root = '$rootparam';
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
        if ($row['driverPercent'] != null) {
	    $style = 'style="background-color: '.tm_color_for_amount_traveled($row['driverPercent'],100).';"';
 	}
	else {
	    $style = 'style="background-color: white"';
	}
	    
        echo "<tr onClick='javascript:labelClick(" . $waypointnum . ",\"" . $row['pointName'] . "\"," . $row['latitude'] . "," . $row['longitude'] . ",0);'><td class='link' style='background-color: ".$color1."'>" . $row['pointName'] . "</td><td ".$style.">";
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
echo "<table><tbody><tr><td>";
echo "<input id=\"showMarkers\" type=\"checkbox\" name=\"Show Markers\" onclick=\"showMarkersClicked()\" checked=\"false\" />&nbsp;Show Markers&nbsp;";
echo "</td><td>";
echo "<form id=\"userForm\" action=\"/hb/index.php\">";
echo "User: ";
tm_user_select();
echo "<label>Units: </label>\n";
tm_units_select();
echo "</td><td>";
echo "<input type=\"hidden\" name=\"r\" value=\"".$rootparam."\" />";
echo "<input type=\"submit\" value=\"Apply\" />";
echo "</td><td>";
echo "<a href='/hb/?r=".$rootparam."'>Zoom to Fit</a>";
echo "</td><td>";
tm_position_checkbox();
echo "</td></tr></tbody></table>\n";
echo <<<ENDB
  </span>
</div>
<div id="map">
</div>
<div id="loadingMsg" style="display: none";>
<table class="gratable">
<tr><td style="font-size: 500%;">Loading Data...</td></tr>
</table>
</div>
ENDB;
$tmdb->close();
?>
</body>
</html>
