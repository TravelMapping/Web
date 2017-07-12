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
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
    <style type="text/css">

    	table.travelersTable {
    	    font-size: 10pt;
    	    border: 1px solid black;
    	    border-spacing: 0px;
    	    margin-left: auto;
    	    margin-right: auto;
    	    background-color: white;
        }
	
        table.gratable {
            max-width: 50%;
            width: 700px;
            margin-bottom: 15px;
            margin-top: 15px;
        }
	
        table.travelersTable {
            max-width: 50%;
            width: 100px;
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
	<label>Units: </label>
	<?php tm_units_select(); ?>
        <input type="submit" value="Update Map and Stats" />
    </form>
    Scroll down to see statistical reports below the map --
    <a href="/user/index.php">User Page</a>
    <?php
        echo " -- <a href='/user/mapview.php?u={$tmuser}&rg={$region}'>View Larger Map</a>";
        echo "<h1>Traveler Statistics for {$tmuser} in {$region}</h1>";
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
    <table class="gratable" id="overallTable">
        <thead>
            <tr><th colspan="3">Overall <?php echo "$region"; ?> Region Statistics</th></tr>
	    <tr><th /><th>Active Systems</th><th>Active+Preview Systems</th></tr>
        </thead>
        <tbody>
            <?php
            //First fetch overall mileage, active only
            $sql_command = <<<SQL
            SELECT o.activeMileage AS totalActiveMileage, c.traveler, c.activeMileage as activeClinched, round(c.activeMileage / o.activeMileage * 100, 2) AS activePercentage
            FROM clinchedOverallMileageByRegion AS c
            LEFT JOIN overallMileageByRegion AS o ON c.region = o.region
            WHERE c.region = '$region' AND c.activeMileage > 0
            ORDER BY activePercentage DESC;
SQL;
            $activeRes = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($activeRes, 'activePercentage');
//            $link = "redirect('/user/mapview.php?u=" . $tmuser . "&amp;rg=" . $region . "')";
	    $activeTotalMileage = $row['totalActiveMileage'];
	    $activeClinchedMileage = $row['activeClinched'];
	    $activeMileagePercentage = $row['activePercentage'];
	    $activeMileageRank = $row['rank'];

	    // build arrays that will form the contents of the travelers
	    // by region stats for active systems
	    $activeTravelerInfo = array();
	    $activeRes->data_seek(0);
	    while ($row = $activeRes->fetch_assoc()) {
		$activeTravelerInfo[$row['traveler']]['activeClinched'] = $row['activeClinched'];
		$activeTravelerInfo[$row['traveler']]['activePercentage'] = $row['activePercentage'];
            }

	    // and active+preview
            $sql_command = <<<SQL
            SELECT o.activePreviewMileage AS totalActivePreviewMileage, c.traveler, c.activePreviewMileage as activePreviewClinched, round(c.activePreviewMileage / o.activePreviewMileage * 100, 2) AS activePreviewPercentage
            FROM clinchedOverallMileageByRegion AS c
            LEFT JOIN overallMileageByRegion AS o ON c.region = o.region
            WHERE c.region = '$region' AND c.activePreviewMileage > 0
            ORDER BY activePreviewPercentage DESC;
SQL;
            $activePreviewRes = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($activePreviewRes, 'activePreviewPercentage');
//            $link = "redirect('/user/mapview.php?u=" . $tmuser . "&amp;rg=" . $region . "')";
	    $activePreviewTotalMileage = $row['totalActivePreviewMileage'];
	    $activePreviewClinchedMileage = $row['activePreviewClinched'];
	    $activePreviewMileagePercentage = $row['activePreviewPercentage'];
	    $activePreviewMileageRank = $row['rank'];

	    // build arrays that will form the contents of the travelers
	    // by region stats for active+preview systems
	    $activePreviewTravelerInfo = array();
	    $activePreviewRes->data_seek(0);
	    while ($row = $activePreviewRes->fetch_assoc()) {
		$activePreviewTravelerInfo[$row['traveler']]['activePreviewClinched'] = $row['activePreviewClinched'];
		$activePreviewTravelerInfo[$row['traveler']]['activePreviewPercentage'] = $row['activePreviewPercentage'];
            }

            echo "<tr class='notclickable' style=\"background-color:#EEEEFF\"><td>Distance Traveled</td>";
	    echo "<td>" . tm_convert_distance($activeClinchedMileage);
	    echo " of " . tm_convert_distance($activeTotalMileage) . " " . $tmunits . " (";
	    echo $activeMileagePercentage . "%) ";
	    echo "Rank: " . $activeMileageRank . "</td>";
	    echo "<td>" . tm_convert_distance($activePreviewClinchedMileage);
	    echo " of " . tm_convert_distance($activePreviewTotalMileage) . " " .$tmunits . " (";
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

            $activeDrivenRes = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($activeDrivenRes, 'clinchedPct');
	    $drivenActiveRoutes = $row['driven'];
	    $drivenActiveRoutesPct = $row['drivenPct'];
	    $clinchedActiveRoutes = $row['clinched'];
	    $clinchedActiveRoutesPct = $row['clinchedPct'];
	    $clinchedActiveRoutesRank = $row['rank'];
	    $activeDrivenRes->data_seek(0);
            $row = tm_fetch_user_row_with_rank($activeDrivenRes, 'drivenPct');
	    $drivenActiveRoutesRank = $row['rank'];

	    // add to the table of travelers by region stats
	    $activeDrivenRes->data_seek(0);
	    while ($row = $activeDrivenRes->fetch_assoc()) {
		$activeTravelerInfo[$row['traveler']]['driven'] = $row['driven'];
		$activeTravelerInfo[$row['traveler']]['clinched'] = $row['clinched'];
            }

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

            $activePreviewDrivenRes = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($activePreviewDrivenRes, 'clinchedPct');
	    $drivenActivePreviewRoutes = $row['driven'];
	    $drivenActivePreviewRoutesPct = $row['drivenPct'];
	    $clinchedActivePreviewRoutes = $row['clinched'];
	    $clinchedActivePreviewRoutesPct = $row['clinchedPct'];
	    $clinchedActivePreviewRoutesRank = $row['rank'];
	    $activePreviewDrivenRes->data_seek(0);
            $row = tm_fetch_user_row_with_rank($activePreviewDrivenRes, 'drivenPct');
	    $drivenActivePreviewRoutesRank = $row['rank'];

	    // add to the table of travelers by region stats
	    $activePreviewDrivenRes->data_seek(0);
	    while ($row = $activePreviewDrivenRes->fetch_assoc()) {
		$activePreviewTravelerInfo[$row['traveler']]['driven'] = $row['driven'];
		$activePreviewTravelerInfo[$row['traveler']]['clinched'] = $row['clinched'];
            }



            echo "<tr onClick=\"window.open('/shields/clinched.php?u={$tmuser}')\">";
	    echo "<td>Routes Driven</td>";
	    echo "<td>".$drivenActiveRoutes." of " . $totalActiveRoutes . " (" . $drivenActiveRoutesPct . "%) Rank: ".$drivenActiveRoutesRank."</td>";
	    echo "<td>".$drivenActivePreviewRoutes." of " . $totalActivePreviewRoutes . " (" . $drivenActivePreviewRoutesPct . "%) Rank: ".$drivenActivePreviewRoutesRank."</td>";
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
            <th colspan="6">Statistics by System</th>
        </tr>
        <tr>
            <th class="sortable">System Code</th>
            <th class="sortable">System Name</th>
            <th class="sortable">Clinched (<?php tm_echo_units(); ?>)</th>
            <th class="sortable">Total (<?php tm_echo_units(); ?>)</th>
            <th class="sortable">%</th>
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
            echo "<td>" . tm_convert_distance($row['clinchedMileage']) . "</td>";
            echo "<td>" . tm_convert_distance($row['totalMileage']) . "</td>";
            echo "<td>" . $row['percentage'] . "%</td>";
            echo "<td class='link'><a href='/hb?rg={$region}&amp;sys={$row['systemName']}'>HB</a></td></tr>";
        }
        $res->free();
        ?>
        </tbody>
    </table>
    <table class="gratable tablesorter" id="routesTable">
        <thead>
            <tr><th colspan="7">Statistics by Route: (<?php echo "<a href=\"/user/mapview.php?u=".$tmuser."&amp;rg=".$region."\">" ?>Full Map)</a></th></tr>
            <tr><th class="sortable">Tier</th><th class="sortable">Route</th><th class="sortable">#</th><th class="sortable">Clinched (<?php tm_echo_units(); ?>)</th><th class="sortable">Total (<?php tm_echo_units(); ?>)</th><th class="sortable">%</th><th class="nonsortable">Map</th></tr>
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
                    echo "<td>".tm_convert_distance($row['clinchedMileage'])."</td>";
                    echo "<td>".tm_convert_distance($row['totalMileage'])."</td>";
                    echo "<td>".$row['percentage']."%</td>";
                    echo "<td class='link'><a href='/hb?u={$tmuser}&amp;r={$row['root']}'>HB</a></td></tr>";
                }
                $res->free();
            ?>
        </tbody>
    </table>


    <table class="travelersTable">
    <thead>
      <tr><th colspan="2">Travelers in Region <?php echo "$region"; ?></th></tr>
      <tr><th>Active Systems</th><th>Active+Preview Systems</th></tr>
    </thead>
    <tbody>
    <tr><td>
    <table class="gratable tablesorter" id="activeTravelersTable" style="width: auto;">
        <thead>
            <tr><th class="sortable">Traveler</th><th class="sortable">Distance Traveled (<?php tm_echo_units(); ?>)</th><th>%</th><th class="sortable">Traveled Routes</th><th class="sortable">Clinched Routes</th></tr>
        </thead>
        <tbody>
	  <tr style=><td>TOTAL CLINCHABLE</td><td><?php echo tm_convert_distance($activeTotalMileage); ?></td><td>100.00%</td><td><?php echo "$totalActiveRoutes"; ?></td><td><?php echo "$totalActiveRoutes"; ?></td></tr>
	  <?php
	  foreach ($activeTravelerInfo as $traveler => $stats) {
	      if ($traveler == "") {
                  continue;  // this happens, but how?!
              }
              if ($traveler == $tmuser) {
                  $highlight = 'user-highlight';
              } else {
                 $highlight = '';
              }
	      echo "<tr class=\"".$highlight."\" onClick=\"window.document.location='?u=".$traveler."&rg=$region'\"><td>".$traveler."</td><td>".tm_convert_distance($stats['activeClinched'])."</td><td>".$stats['activePercentage']."%</td><td>".$stats['driven']."</td><td>".$stats['clinched']."</td></tr>\n";
          }
	  ?>
	</tbody>
	</table>
    </td><td>
    <table class="gratable tablesorter" id="activePreviewTravelersTable" style="width: auto;">
        <thead>
            <tr><th class="sortable">Traveler</th><th class="sortable">Distance Traveled (<?php tm_echo_units(); ?>)</th><th>%</th><th class="sortable">Traveled Routes</th><th class="sortable">Clinched Routes</th></tr>
        </thead>
        <tbody>
	  <tr style=><td>TOTAL CLINCHABLE</td><td><?php echo tm_convert_distance($activePreviewTotalMileage); ?></td><td>100.00%</td><td><?php echo "$totalActivePreviewRoutes"; ?></td><td><?php echo "$totalActivePreviewRoutes"; ?></td></tr>
	  <?php
	  foreach ($activePreviewTravelerInfo as $traveler => $stats) {
	      if ($traveler == "") {
	          continue;  // this happens, but how?!
              }
              if ($traveler == $tmuser) {
                  $highlight = 'user-highlight';
              } else {
                 $highlight = '';
              }
	      echo "<tr class=\"".$highlight."\" onClick=\"window.document.location='?u=".$traveler."&rg=$region'\"><td>".$traveler."</td><td>".tm_convert_distance($stats['activePreviewClinched'])."</td><td>".$stats['activePreviewPercentage']."%</td><td>".$stats['driven']."</td><td>".$stats['clinched']."</td></tr>\n";
          }
	  ?>
	</tbody>
	</table>
    </td></tr></table>
    

</div>
</div>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $activeRes->free();
    $activeDrivenRes->free();
    $activePreviewRes->free();
    $activePreviewDrivenRes->free();
    $tmdb->close();
?>
<script type="application/javascript" src="../lib/waypoints.js.php?<?php echo $_SERVER['QUERY_STRING']?>"></script>
</html>
