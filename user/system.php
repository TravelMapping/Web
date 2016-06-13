<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	Shows a user's stats for a particular system, whether overall or limited to a single region.  
	URL Params:
		u - the user.
        sys - The system being viewed on this page
        rg - The region to study this system
		(u, sys, [rg])
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
    <style type="text/css">
        table.gratable {
            max-width: 50%;
            width: 700px;
            margin-bottom: 15px;
            margin-top: 15px;
        }

        #mapholder {
            position: relative;
            margin: auto;
            width: 90%;
        }

        #map {
            height: 500px;
            overflow: hidden;
        }

        @media screen and (max-width: 720px) {
            #mapholder {
                width: 100%;
            }
        }

        #map * {
            cursor: crosshair;
        }

        #body {
            left: 0px;
            top: 80px;
            bottom: 0px;
            width: 100%;
            overflow: auto;
            padding: 20px;
        }
    </style>
    <?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
    <?php
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

    ?>
    <title><?php
        echo $systemName." (".$system.")";
        if ($region != "") {
            echo " in " . $region;
        }
        echo " - ".$tmuser;
        ?></title>
    <script
        src="http://maps.googleapis.com/maps/api/js?sensor=false"
        type="text/javascript"></script>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    <!-- jQuery -->
    <script src="http://code.jquery.com/jquery-1.11.0.min.js" type="text/javascript"></script>
    <!-- TableSorter -->
    <script src="/lib/jquery.tablesorter.min.js" type="text/javascript"></script>
    <script>
        function waypointsFromSQL() {
            <?php
              // restrict to routes in the given system, alternately 
              // restricted further by region, if specified

              $select_region = "";
              if ($region != "") {
                  $select_region = "region='".$region."' AND ";
              }
              $sql_command = "SELECT waypoints.pointName, waypoints.latitude, waypoints.longitude, waypoints.root, systems.tier, systems.color, systems.systemname FROM waypoints JOIN routes ON routes.root = waypoints.root AND ".$select_region."routes.systemName = '".$system."' JOIN systems ON routes.systemname = systems.systemname ORDER BY root, waypoints.pointId;";
              $res = tmdb_query($sql_command);

              $routenum = 0;
              $pointnum = 0;
              $lastRoute = "";
              while ($row = $res->fetch_assoc()) {
                if (!($row['root'] == $lastRoute)) {
                   echo "newRouteIndices[".$routenum."] = ".$pointnum.";\n";
                   echo "routeTier[".$routenum."] = ".$row['tier'].";\n";
                   echo "routeColor[".$routenum."] = '".$row['color']."';\n";
                   echo "routeSystem[".$routenum."] = '".$row['systemname']."';\n";
                   $lastRoute = $row['root'];
                   $routenum = $routenum + 1;
                }
                echo "waypoints[".$pointnum."] = new Waypoint(\"".$row['pointName']."\",".$row['latitude'].",".$row['longitude']."); // Route = ".$row['root']." (".$row['color'].")\n";
                $pointnum = $pointnum + 1;
              }
              $res->free();

              // check for query string parameter for traveler clinched mapping of route
              echo "traveler = '".$tmuser."';\n";
              // retrieve list of segments for this system, and region 
              // if needed
              $sql_command = "SELECT segments.segmentId, segments.root FROM segments JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname WHERE ".$select_region."routes.systemName = '".$system."' ORDER BY root, segments.segmentId;";
              $res = tmdb_query($sql_command);
              $segmentIndex = 0;
              while ($row = $res->fetch_assoc()) {
                 echo "segments[".$segmentIndex."] = ".$row['segmentId']."; // route=".$row['root']."\n";
                 $segmentIndex = $segmentIndex + 1;
              }
              $res->free();

              $sql_command = "SELECT segments.segmentId, segments.root FROM segments RIGHT JOIN clinched ON segments.segmentId = clinched.segmentId JOIN routes ON routes.root = segments.root JOIN systems ON routes.systemname = systems.systemname WHERE ".$select_region."routes.systemName = '".$system."' AND clinched.traveler='".$tmuser."' ORDER BY root, segments.segmentId;";
              $res = tmdb_query($sql_command);
              $segmentIndex = 0;
              while ($row = $res->fetch_assoc()) {
                echo "clinched[".$segmentIndex."] = ".$row['segmentId']."; // route=".$row['root']."\n";
                $segmentIndex = $segmentIndex + 1;
              }
              $res->free();

              echo "mapClinched = true;\n";

              // insert custom color code if needed
              tm_generate_custom_colors_array();
            ?>
            genEdges = true;
        }
    </script>
</head>
<body 
<?php
if (( $tmuser != "null") || ( $system != "" )) {
  echo "onload=\"loadmap();\"";
}
?>
>

<script type="text/javascript">
    $(document).ready(function () {
            $("#routeTable").tablesorter({
                sortList: [[0, 0]],
                headers: {0: {sorter: false}, 1: {sorter: false}, 3: {sorter: false},}
            });
            $('td').filter(function() {
                return this.innerHTML.match(/^[0-9\s\.,%]+$/);
            }).css('text-align','right');
        }
    );
</script>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<div id="header">
    <input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()">&nbsp;Show Markers
    <form id="userselect" action="system.php">
        <label>User: </label>
        <?php tm_user_select(); ?>
        <label>System: </label>
	<?php tm_system_select(FALSE); ?>
        <label>Region: </label>
	<?php tm_region_select(FALSE); ?>
        <input type="submit" value="Update Map and Stats" />
    </form>
    <h1><?php
        echo "Traveler Stats for " . $tmuser . " on " . $systemName;
        if ($region != "") {
            echo " in " . $regionName;
        }
        ?>:</h1>
</div>
<?php
if (( $tmuser == "null") || ( $system == "" )) {
    echo "<h1>Select a User and System to Continue</h1>\n";
    echo "</div>\n";
    require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php";
    echo "</body>\n";
    echo "</html>\n";
    exit;
}
?>
<div id="body">
    <div id="mapholder">
        <div id="controlboxinfo"></div>
        <div id="map"></div>
        <table class="gratable tablesorter" id="overallTable">
            <thead><tr><th colspan="2">System Stats</th></tr></thead>
            <tbody>
            <?php
	    // get overall stats either for entire system or within
	    // our selected region
            if ($region == "") {
	        // overall mileage across all systems
                $system_mileage = tm_sum_column_where("systemMileageByRegion", "mileage", "systemName = '".$system."'");

		// clinched mileage across all systems
                $sql_command = <<<SQL
                SELECT
                    traveler, SUM(mileage) as clinchedMileage
                FROM clinchedSystemMileageByRegion
                WHERE systemName = '$system'
		GROUP BY traveler
                ORDER BY clinchedMileage DESC;
SQL;
            } 
	    else {
	        // mileage for one system in one region
                $system_mileage = tm_sum_column_where("systemMileageByRegion", "mileage", "systemName = '".$system."' AND region = '".$region."'");

		// clinched mileage across all systems
                $sql_command = <<<SQL
                SELECT
                    traveler, mileage as clinchedMileage
                FROM clinchedSystemMileageByRegion
                WHERE systemName = '$system'
		AND region = '$region'
		GROUP BY traveler
                ORDER BY clinchedMileage DESC;
SQL;
            }
            $res = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($res, 'clinchedMileage');
            $res->free();
	    $percentage = $row['clinchedMileage'] / $system_mileage * 100;
            $link = "window.open('/user/mapview.php?u=" . $user . "&amp;sys=" . $system . "')";
            echo "<tr style=\"background-color:#EEEEFF\"><td>Miles Driven</td><td>".sprintf('%0.2f', $row['clinchedMileage'])." of ".sprintf('%0.2f', $system_mileage)." mi (".sprintf('%0.2f',$percentage)."%) Rank: {$row['rank']}</td></tr>";

            //Second, fetch routes clinched/driven
            if ($region == "") {
                $totalRoutes = tm_count_rows("connectedRoutes", "WHERE systemName='".$system."'");
                $sql_command = <<<SQL
                SELECT
                    ccr.traveler,
                    count(ccr.route) as driven,
                    sum(ccr.clinched) as clinched
                FROM connectedRoutes as cr
                LEFT JOIN clinchedConnectedRoutes as ccr
                ON cr.firstRoot = ccr.route
                WHERE cr.systemName = '$system'
                GROUP BY traveler
                ORDER BY clinched DESC;
SQL;
            } else {
                $totalRoutes = tm_count_rows("routes", "WHERE systemName='".$system."' AND region='".$region."'");
                $sql_command = <<<SQL
                SELECT
                    ccr.traveler,
                    count(ccr.route) as driven,
                    sum(ccr.clinched) as clinched
                FROM routes as cr
                LEFT JOIN clinchedRoutes as ccr
                ON cr.root = ccr.route
                WHERE cr.region = '$region' AND cr.systemName = '$system'
                GROUP BY ccr.traveler
                ORDER BY clinched DESC
SQL;
            }
            $res = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($res, 'clinched');
            $res->free();
            echo "<tr onClick=\"" . $link . "\"><td>Routes Driven</td><td>" . $row['driven'] . " of ".$totalRoutes." (" . round($row['driven'] / $totalRoutes * 100, 2) ."%)</td></tr>";
	    echo "<tr onClick=\"" . $link . "\"><td>Routes Clinched</td><td>" . $row['clinched'] . " of " . $totalRoutes . " (" . round($row['clinched'] / $totalRoutes * 100, 2) . "%) Rank: {$row['rank']}</td></tr>\n";
            ?>
            </tbody>
        </table>
        <table class="gratable tablesorter" id="routeTable">
            <caption>TIP: Click on a column head to sort. Hold SHIFT in order to sort by multiple columns.</caption>
            <thead>
            <tr>
                <th colspan="8">Statistics per Route</th>
            </tr>
            <tr>
                <th class="nonsortable">Route</th>
                <th class="sortable">#</th>
                <th class="nonsortable">Banner</th>
                <th class="nonsortable">Abbrev</th>
                <th class="nonsortable">Section</th>
                <th class="sortable">Clinched Mileage</th>
                <th class="sortable">Total Mileage</th>
                <th class="sortable">Percentage</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $sql_command = "";
            if ($region != "") {
                $sql_command = "SELECT r.banner, r.abbrev, r.route, r.root, r.city, ROUND((COALESCE(r.mileage, 0)),2) AS totalMileage, ROUND((COALESCE(cr.mileage, 0)),2) AS clinchedMileage, ROUND((COALESCE(cr.mileage,0)) / (COALESCE(r.mileage, 0)) * 100,2) AS percentage, SUBSTRING(root, LOCATE('.', root)) AS routeNum FROM routes AS r LEFT JOIN clinchedRoutes AS cr ON r.root = cr.route AND traveler = 'xxxxxxxxxxxxxxxxx' WHERE systemName = 'yyyyyyyyyyyyyyyyy' AND region = '" . $region . "' ORDER BY routeNum;";
            } else {
                $sql_command = "SELECT r.banner, r.route, r.groupName AS city, r.firstRoot AS root, ROUND((COALESCE(r.mileage, 0)),2) AS totalMileage, ROUND((COALESCE(cr.mileage, 0)),2) AS clinchedMileage, ROUND((COALESCE(cr.mileage,0)) / (COALESCE(r.mileage, 0)) * 100,2) AS percentage, SUBSTRING(firstRoot, LOCATE('.', firstRoot)) AS routeNum FROM connectedRoutes AS r LEFT JOIN clinchedConnectedRoutes AS cr ON r.firstRoot = cr.route AND traveler = 'xxxxxxxxxxxxxxxxx' WHERE systemName = 'yyyyyyyyyyyyyyyyy' ORDER BY routeNum;";
            }

            $sql_command = str_replace("xxxxxxxxxxxxxxxxx", $tmuser, $sql_command);
            $sql_command = str_replace("yyyyyyyyyyyyyyyyy", $system, $sql_command);
            $res = tmdb_query($sql_command);

            while ($row = $res->fetch_assoc()) {
                if ($region == "") {
                    $link = "window.open('/user/mapview.php?u=" . $tmuser . "&amp;rte=" . $row['route'] . "')";
                } else {
                    $link = "window.open('/hb?u=" . $tmuser . "&amp;r=" . $row['root'] . "')";
                }

                echo "<tr onClick=\"" . $link . "\">";
                echo "<td>" . $row['route'] . "</td>";
                echo "<td width='0'>" . $row['routeNum'] . "</td>";
                echo "<td>" . $row['banner'] . "</td>";
                echo "<td>" . $row['abbrev'] . "</td>";
                echo "<td>" . $row['city'] . "</td>";
                echo "<td>" . $row['clinchedMileage'] . "</td>";
                echo "<td>" . $row['totalMileage'] . "</td>";
                echo "<td>" . $row['percentage'] . "%</td></tr>";
            }
            $res->free();
            ?>
            </tbody>
        </table>
    </div>
</div>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
