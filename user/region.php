<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	Shows a user's stats for a particular region. 
	URL Params:
		u - the user.
        rg - the region viewing stats for.
		(u, rg)
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
            clear: left;
        }

        #colorBox {
            position: relative;
            float: right;
            margin: auto;
            padding: 10px;
        }

        #systemsTable {
            clear: both;
        }

        @media screen and (max-width: 720px) {

            #mapholder {
                width: 100%;
            }
        }

        #map * {
            cursor: crosshair;
        }
        
        #overallTable, #routesTable {
            margin: 5px auto 5px auto;
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
    $activeClause = "(systems.level='preview' OR systems.level='active')";

    ?>
    <title><?php echo $regionName." - ".$tmuser; ?></title>
    <script
        src="http://maps.googleapis.com/maps/api/js?key=<?php echo $gmaps_api_key ?>&sensor=false"
        type="text/javascript"></script>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
    <!-- jQuery -->
    <script src="http://code.jquery.com/jquery-1.11.0.min.js" type="text/javascript"></script>
    <!-- TableSorter -->
    <script src="/lib/jquery.tablesorter.min.js" type="text/javascript"></script>
    <script type="application/javascript" src="../api/waypoints.js.php?<?php echo $_SERVER['QUERY_STRING']?>"></script>
</head>
<body 
<?php
if (( $tmuser != "null") || ( $region != "" )) {
  echo "onload=\"loadmap(); waypointsFromSQL(); updateMap();\"";
}
?>
>
<script type="text/javascript">
    $(document).ready(function () {
            $("table.tablesorter").tablesorter({
                sortList: [[0,0],[2,0]],
                headers: {0: {sorter: false}}
            });
            $('td').filter(function() {
                return this.innerHTML.match(/^[0-9\s\.,%]+$/);
            }).css('text-align','right');
        }
    );

    function redirect($link) {
        window.document.location=$link;
    }
</script>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<div id="header">
    <input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()">&nbsp;Show Markers
    <form id="userselect" action="region.php">
        <label>User: </label>
        <?php tm_user_select(); ?>
        <label>Region: </label>
	<?php tm_region_select(FALSE); ?>
        <input type="submit" value="Update Map and Stats" />
    </form>
    <a href="/user/index.php">Back to User Page</a>
    <?php
        echo " -- <a href='/user/mapview.php?u={$tmuser}&rg={$region}'>View Larger Map</a>";
        echo "<h1>Traveler Stats for {$tmuser} in {$region}:</h1>";
    ?>
</div>
<?php
if (( $tmuser == "null") || ( $region == "" )) {
    echo "<h1>Select a User and Region to Continue</h1>\n";
    echo "</div>\n";
    require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php";
    echo "</body>\n";
    echo "</html>\n";
    exit;
}
?>
<div id="body">
    <div id="mapholder">
        <div id="colorBox">

        </div>
        <div id="map"></div>
    </div>
    <h6>TIP: Click on a column head to sort. Hold SHIFT in order to sort by multiple columns.</h6>
    <table class="gratable" id="overallTable">
        <thead>
            <tr><th colspan="3">Overall Region Stats</th></tr>
	    <tr><td /><td>Active Systems</td><td>Active+Preview Systems</td></tr>
        </thead>
        <tbody>
            <?php
            //First fetch overall mileage, active only
            $sql_command = <<<SQL
            SELECT o.activeMileage AS totalActiveMileage, c.traveler, c.activeMileage as activeClinched, round(c.activeMileage / o.activeMileage * 100, 2) AS activePercentage
            FROM clinchedOverallMileageByRegion AS c
            LEFT JOIN overallMileageByRegion AS o ON c.region = o.region
            WHERE c.region = '$region'
            ORDER BY activePercentage DESC;
SQL;
            $res = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($res, 'activePercentage');
            $res->free();
            $link = "redirect('/user/mapview.php?u=" . $tmuser . "&amp;rg=" . $region . "')";
	    $activeTotalMileage = $row['totalActiveMileage'];
	    $activeClinchedMileage = $row['activeClinched'];
	    $activeMileagePercentage = $row['activePercentage'];
	    $activeMileageRank = $row['rank'];

	    // and active+preview
            $sql_command = <<<SQL
            SELECT o.activePreviewMileage AS totalActivePreviewMileage, c.traveler, c.activePreviewMileage as activePreviewClinched, round(c.activePreviewMileage / o.activePreviewMileage * 100, 2) AS activePreviewPercentage
            FROM clinchedOverallMileageByRegion AS c
            LEFT JOIN overallMileageByRegion AS o ON c.region = o.region
            WHERE c.region = '$region'
            ORDER BY activePreviewPercentage DESC;
SQL;
            $res = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($res, 'activePreviewPercentage');
            $res->free();
            $link = "redirect('/user/mapview.php?u=" . $tmuser . "&amp;rg=" . $region . "')";
	    $activePreviewTotalMileage = $row['totalActivePreviewMileage'];
	    $activePreviewClinchedMileage = $row['activePreviewClinched'];
	    $activePreviewMileagePercentage = $row['activePreviewPercentage'];
	    $activePreviewMileageRank = $row['rank'];
 
            echo "<tr class='notclickable' style=\"background-color:#EEEEFF\"><td>Miles Driven</td>";
	    echo "<td>" . $activeClinchedMileage;
	    echo "/" . $activeTotalMileage . " mi (";
	    echo $activeMileagePercentage . "%) ";
	    echo "Rank: " . $activeMileageRank . "</td>";
	    echo "<td>" . $activePreviewClinchedMileage;
	    echo "/" . $activePreviewTotalMileage . " mi (";
	    echo $activePreviewMileagePercentage . "%) ";
	    echo "Rank: " . $activePreviewMileageRank . "</td>";
	    echo "</tr>";

            // Second, fetch routes clinched/driven
            $totalActiveRoutes = tm_count_rows("routes", "LEFT JOIN systems on routes.systemName = systems.systemName WHERE region = '$region' AND systems.level = 'active'");

            $totalActivePreviewRoutes = tm_count_rows("routes", "LEFT JOIN systems on routes.systemName = systems.systemName WHERE region = '$region' AND ".$activeClause." ");

            $sql_command = <<<SQL
            SELECT
              traveler,
              COUNT(cr.route) AS driven,
              SUM(cr.clinched) AS clinched,
              ROUND(COUNT(cr.route) / $totalActiveRoutes * 100, 2) as drivenPct,
              ROUND(sum(cr.clinched) / $totalActiveRoutes * 100, 2) as clinchedPct
            FROM routes AS r
              LEFT JOIN clinchedRoutes AS cr
                ON cr.route = r.root
              LEFT JOIN systems
                ON r.systemName = systems.systemName
            WHERE (r.region = '$region' AND 
                systems.level = 'active')
            GROUP BY traveler
            ORDER BY clinchedPct DESC;
SQL;

            $res = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($res, 'clinchedPct');
            $res->free();
	    $drivenActiveRoutes = $row['driven'];
	    $drivenActiveRoutesPct = $row['drivenPct'];
	    $clinchedActiveRoutes = $row['clinched'];
	    $clinchedActiveRoutesPct = $row['clinchedPct'];
	    $clinchedActiveRoutesRank = $row['rank'];

            $sql_command = <<<SQL
            SELECT
              traveler,
              COUNT(cr.route) AS driven,
              SUM(cr.clinched) AS clinched,
              ROUND(COUNT(cr.route) / $totalActivePreviewRoutes * 100, 2) as drivenPct,
              ROUND(sum(cr.clinched) / $totalActivePreviewRoutes * 100, 2) as clinchedPct
            FROM routes AS r
              LEFT JOIN clinchedRoutes AS cr
                ON cr.route = r.root
              LEFT JOIN systems
                ON r.systemName = systems.systemName
            WHERE (r.region = '$region' AND 
                (systems.level='preview' OR systems.level='active'))
            GROUP BY traveler
            ORDER BY clinchedPct DESC;
SQL;

            $res = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($res, 'clinchedPct');
            $res->free();
	    $drivenActivePreviewRoutes = $row['driven'];
	    $drivenActivePreviewRoutesPct = $row['drivenPct'];
	    $clinchedActivePreviewRoutes = $row['clinched'];
	    $clinchedActivePreviewRoutesPct = $row['clinchedPct'];
	    $clinchedActivePreviewRoutesRank = $row['rank'];



            echo "<tr onClick=\"window.open('/shields/clinched.php?u={$tmuser}')\">";
	    echo "<td>Routes Driven</td>";
	    echo "<td>".$drivenActiveRoutes." of " . $totalActiveRoutes . " (" . $drivenActiveRoutesPct . "%) Rank: TBD</td>";
	    echo "<td>".$drivenActivePreviewRoutes." of " . $totalActivePreviewRoutes . " (" . $drivenActivePreviewRoutesPct . "%) Rank: TBD</td>";
	    echo "</tr>";

            echo "<tr onClick=\"window.open('/shields/clinched.php?u={$tmuser}')\">";
	    echo "<td>Routes Clinched</td>";
	    echo "<td>".$clinchedActiveRoutes." of " . $totalActiveRoutes . " (" . $clinchedActiveRoutesPct . "%) Rank: ". $clinchedActiveRoutesRank."</td>";
	    echo "<td>".$clinchedActivePreviewRoutes." of " . $totalActivePreviewRoutes . " (" . $clinchedActivePreviewRoutesPct . "%) Rank: ". $clinchedActivePreviewRoutesRank."</td>";
	    echo "</tr>";

            ?>
        </tbody>
    </table>
    <table class="gratable tablesorter" id="systemsTable">
        <caption>TIP: Click on a column head to sort. Hold SHIFT in order to sort by multiple columns.</caption>
        <thead>
        <tr>
            <th colspan="6">Clinched Mileage by System</th>
        </tr>
        <tr>
            <th class="sortable">System Code</th>
            <th class="sortable">System Name</th>
            <th class="sortable">Clinched Mileage</th>
            <th class="sortable">Total Mileage</th>
            <th class="sortable">Percent</th>
            <th class="nonsortable">Map</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql_command = <<<SQL
          SELECT
            sys.systemName,
            sys.tier,
            sys.level AS status,
            sys.fullName,
            COALESCE(ROUND(SUM(cr.mileage), 2), 0) AS clinchedMileage,
            COALESCE(ROUND(SUM(r.mileage), 2), 0) AS totalMileage,
            COALESCE(ROUND(SUM(cr.mileage) / SUM(r.mileage) * 100, 2), 0) AS percentage
          FROM systems as sys
          INNER JOIN routes AS r
            ON r.systemName = sys.systemName
          LEFT JOIN clinchedRoutes AS cr
            ON cr.route = r.root AND cr.traveler = '{$tmuser}'
          WHERE r.region = '{$region}'
          GROUP BY r.systemName
          ORDER BY sys.tier, sys.systemName;
SQL;

        $res = tmdb_query($sql_command);
        while ($row = $res->fetch_assoc()) {
            echo "<tr onClick=\"window.open('/user/system.php?u=" . $tmuser . "&sys=" . $row['systemName'] . "&amp;rg=" . $region . "')\" class=\"status-" . $row['status'] . "\">";
            echo "<td>" . $row['systemName'] . "</td>";
            echo "<td>" . $row['fullName'] . "</td>";
            echo "<td>" . $row['clinchedMileage'] . "</td>";
            echo "<td>" . $row['totalMileage'] . "</td>";
            echo "<td>" . $row['percentage'] . "%</td>";
            echo "<td class='link'><a href='/hb?rg={$region}&amp;sys={$row['systemName']}'>HB</a></td></tr>";
        }
        $res->free();
        ?>
        </tbody>
    </table>
    <table class="gratable tablesorter" id="routesTable">
        <thead>
            <tr><th colspan="7">Stats by Route: (<?php echo "<a href=\"/user/mapview.php?u=".$tmuser."&amp;rg=".$region."\">" ?>Full Map)</a></th></tr>
            <tr><th class="sortable">Tier</th><th class="sortable">Route</th><th class="sortable">#</th><th class="sortable">Clinched Mileage</th><th class="sortable">Total Mileage</th><th class="sortable">%</th><th class="nonsortable">Map</th></tr>
        </thead>
        <tbody>
            <?php
                $sql_command = <<<SQL
                    SELECT r.route, r.root, r.banner, r.city, r.systemName, sys.tier,
                      ROUND((COALESCE(r.mileage, 0)),2) AS totalMileage, 
                      ROUND((COALESCE(cr.mileage, 0)),2) AS clinchedMileage, 
                      COALESCE(ROUND((COALESCE(cr.mileage,0)) / (COALESCE(r.mileage, 0)) * 100,2), 0) AS percentage 
                    FROM routes AS r 
                    LEFT JOIN clinchedRoutes AS cr ON r.root = cr.route AND traveler = '{$tmuser}'
                    LEFT JOIN systems AS sys ON r.systemName = sys.systemName 
                    WHERE region = '{$region}'
                    ORDER BY sys.tier, r.root
SQL;
                $res = tmdb_query($sql_command);
                while ($row = $res->fetch_assoc()) {
                    echo "<tr onClick=\"window.open('/hb?u=".$tmuser."&amp;r=".$row['root']."')\">";
                    echo "<td>{$row['tier']}</td>";
                    echo "<td>".$row['route'];
                    if (strlen($row['banner']) > 0) {
                        echo " ".$row['banner']." ";
                    }
                    if (strlen($row['city']) > 0) {
                        echo " (".$row['city'].")";
                    }
                    echo "</td>";
                    echo "<td>{$row['systemName']}.{$row['root']}</td>";
                    echo "<td>".$row['clinchedMileage']."</td>";
                    echo "<td>".$row['totalMileage']."</td>";
                    echo "<td>".$row['percentage']."%</td>";
                    echo "<td class='link'><a href='/hb?u={$tmuser}&amp;r={$row['root']}'>HB</a></td></tr>";
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
</html>
