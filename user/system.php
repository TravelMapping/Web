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
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
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
        $redir_url = "/user/region.php?u={$tmuser}&rg={$_GET['rg']}";
        echo "<script>window.location = '{$redir_url}';</script>";
        echo "Please go to <a href='$redir_url'>{$redir_url}</a> if you are not automatically redirected.";
        exit();
    }

    ?>
    <title><?php
        echo $systemName." (".$system.")";
        if ($region != "") {
            echo " in " . $region;
        }
        echo " - ".$tmuser;
        ?></title>
    <?php tm_common_js(); ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
</head>
<body 
<?php
if (( $tmuser != "null") || ( $system != "" )) {
  echo "onload=\"loadmap(); waypointsFromSQL(); updateMap(null,null,null);\"";
}
?>
>

<script type="text/javascript">
    $(document).ready(function () {
            $("#regionsTable").tablesorter({
                sortList: [[4, 1]]
            });
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
	<label>Units: </label>
	<?php tm_units_select(); ?>
        <input type="submit" value="Update Map and Stats" />
    </form>
    <a href="/user/index.php">Back to User Page</a>
    <?php
        echo " -- <a href='/user/mapview.php?u={$tmuser}&sys={$system}";
        if ($region != "") {
            echo "&rg={$region}";
        }
        echo "'>View Larger Map</a>";
        echo "<h1>";
        echo "Traveler Statistics for " . $tmuser . " on " . $systemName;
        if ($region != "") {
            echo " in " . $regionName;
        }
        echo "</h1>";
    ?>
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
            <thead><tr><th colspan="2">System Statistics for <?php echo "$systemName"; ?></th></tr></thead>
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
                ORDER BY clinchedMileage DESC;
SQL;
            }
            $res = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($res, 'clinchedMileage');
            $res->free();
	    $percentage = 0;
	    if ($system_mileage != 0) {
  	        $percentage = $row['clinchedMileage'] / $system_mileage * 100;
	    }
            $link = "window.open('/shields/clinched.php?u=" . $tmuser . "&amp;sys=" . $system . "')";
            echo "<tr style=\"background-color:#EEEEFF\"><td>Distance Traveled</td><td>".tm_convert_distance($row['clinchedMileage'])." of ".tm_convert_distance($system_mileage)." ".$tmunits." (".sprintf('%0.2f',$percentage)."%) Rank: {$row['rank']}</td></tr>";

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
            echo "<tr onClick=\"" . $link . "\"><td>Routes Traveled</td><td>" . $row['driven'] . " of ".$totalRoutes." (" . round($row['driven'] / $totalRoutes * 100, 2) ."%)</td></tr>";
	    echo "<tr onClick=\"" . $link . "\"><td>Routes Clinched</td><td>" . $row['clinched'] . " of " . $totalRoutes . " (" . round($row['clinched'] / $totalRoutes * 100, 2) . "%) Rank: {$row['rank']}</td></tr>\n";
            ?>
            </tbody>
        </table>
        <?php
        if($region == "") {
            echo <<<HTML
                <table class="gratable tablesorter" id="regionsTable">
                    <caption>TIP: Click on a column head to sort. Hold SHIFT in order to sort by multiple columns.</caption>
                    <thead>
                    <tr><th colspan="4">Statistics by Region</th></tr>
                    <tr>
                        <th class="sortable">Region</th>
                        <th class="sortable">Clinched ({$tmunits})</th>
                        <th class="sortable">Total ({$tmunits})</th>
                        <th class="sortable">%</th>
                    </tr>
                    </thead>
                    <tbody>
HTML;
            $sql_command = <<<SQL
            SELECT
              miByRegion.region,
              ROUND(IFNULL(clinchedByRegion.mileage, 0), 2) as clinchedMileage,
              ROUND(miByRegion.mileage, 2) as totalMileage,
              ROUND(IFNULL(clinchedByRegion.mileage, 0) / miByRegion.mileage * 100, 2) as percentage
            FROM systemMileageByRegion as miByRegion
            LEFT JOIN clinchedSystemMileageByRegion as clinchedByRegion 
                ON clinchedByRegion.region = miByRegion.region AND clinchedByRegion.systemName = '{$system}' AND clinchedByRegion.traveler='$tmuser'
            WHERE miByRegion.systemName = '{$system}'
            ORDER BY percentage DESC ;
SQL;
            $res = tmdb_query($sql_command);
            while ($row = $res->fetch_assoc()) {
		$clinched = tm_convert_distance($row['clinchedMileage']);
		$total = tm_convert_distance($row['totalMileage']);
                echo <<<HTML
                <tr onclick='window.open("/user/system.php?u={$tmuser}&sys={$system}&rg={$row['region']}")'>
                    <td>{$row['region']}</td>
                    <td>{$clinched}</td>
                    <td>{$total}</td>
                    <td>{$row['percentage']}%</td>
                </tr>
HTML;
            }
            $res->free();
            echo "</tbody></table>";
        }
        ?>
        <table class="gratable tablesorter" id="routeTable">
            <thead>
            <tr>
                <th colspan="8">Statistics by Route</th>
            </tr>
            <tr>
                <th class="nonsortable">Route</th>
                <th class="sortable">#</th>
                <th class="nonsortable">Banner</th>
                <th class="nonsortable">Abbrev</th>
                <th class="nonsortable">Section</th>
                <th class="sortable">Clinched (<?php tm_echo_units(); ?>)</th>
                <th class="sortable">Total (<?php tm_echo_units(); ?>)</th>
                <th class="sortable">%</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $sql_command = "";
            if ($region != "") {
                $sql_command = "SELECT r.banner, r.abbrev, r.route, r.root, r.city, ROUND((COALESCE(r.mileage, 0)),2) AS totalMileage, ROUND((COALESCE(cr.mileage, 0)),2) AS clinchedMileage, ROUND((COALESCE(cr.mileage,0)) / (COALESCE(r.mileage, 0)) * 100,2) AS percentage, SUBSTRING(root, LOCATE('.', root)) AS routeNum FROM routes AS r LEFT JOIN clinchedRoutes AS cr ON r.root = cr.route AND traveler = 'xxxxxxxxxxxxxxxxx' WHERE systemName = 'yyyyyyyyyyyyyyyyy' AND region = '" . $region . "' ORDER BY r.csvOrder;";
            } else {
                $sql_command = "SELECT r.banner, r.route, r.groupName AS city, r.firstRoot AS root, ROUND((COALESCE(r.mileage, 0)),2) AS totalMileage, ROUND((COALESCE(cr.mileage, 0)),2) AS clinchedMileage, ROUND((COALESCE(cr.mileage,0)) / (COALESCE(r.mileage, 0)) * 100,2) AS percentage, SUBSTRING(firstRoot, LOCATE('.', firstRoot)) AS routeNum FROM connectedRoutes AS r LEFT JOIN clinchedConnectedRoutes AS cr ON r.firstRoot = cr.route AND traveler = 'xxxxxxxxxxxxxxxxx' WHERE systemName = 'yyyyyyyyyyyyyyyyy' ORDER BY r.csvOrder;";
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
                echo "<td>" . tm_convert_distance($row['clinchedMileage']) . "</td>";
                echo "<td>" . tm_convert_distance($row['totalMileage']) . "</td>";
                echo "<td>" . $row['percentage'] . "%</td></tr>\n";
            }
            $res->free();
            ?>
            </tbody>
        </table>
    </div>
</div>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
<script type="application/javascript" src="../lib/waypoints.js.php?<?php echo $_SERVER['QUERY_STRING']?>"></script>
</html>
