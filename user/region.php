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
    <?php tm_common_js(); ?>
    <script src="../lib/tmjsfuncs.js" type="text/javascript"></script>
</head>
<body 
<?php
if (( $tmuser != "null") || ( $region != "" )) {
  echo "onload=\"loadmap(); waypointsFromSQL(); updateMap(null,null,null);\"";
}
?>
>
<script type="text/javascript">
    $(document).ready(function () {
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
    <form id="userselect" action="region.php">
        <label>User: </label>
        <?php tm_user_select(); ?>
        <label>Region: </label>
	<?php tm_region_select(FALSE); ?>
	<label>Units: </label>
	<?php tm_units_select(); ?>
        <input type="submit" value="Update Map and Stats" />
    </form>
    Scroll down to see statistical reports below the map
    <?php
        echo " -- <a href='/user/mapview.php?u={$tmuser}&rg={$region}'>View Larger Map</a>";
        echo '<input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()">&nbsp;Show Markers';
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
            <tr><th colspan="3"><a href="#rankings">Overall <?php echo "$region"; ?> Region Statistics</a></th></tr>
	    <tr><th /><th>Active Systems</th><th>Active+Preview Systems</th></tr>
        </thead>
        <tbody>
            <?php
            //First fetch overall mileage
	    $sql_command = "select * from overallMileageByRegion where region = '".$region."'";
	    $row = tmdb_query($sql_command)->fetch_assoc();
	    $activeTotalMileage = $row['activeMileage'];
	    $activePreviewTotalMileage = $row['activePreviewMileage'];

	    // active only
            $sql_command = <<<SQL
            SELECT
		co.traveler,
		co.activeMileage as activeClinched,
		le.includeInRanks
            FROM clinchedOverallMileageByRegion AS co
	    JOIN listEntries le ON co.traveler = le.traveler
            WHERE co.region = '$region' AND co.activeMileage > 0
	    GROUP BY co.traveler, le.includeInRanks
            ORDER BY activeClinched DESC;
SQL;
            $activeClinchedRes = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($activeClinchedRes, 'activeClinched');
//            $link = "redirect('/user/mapview.php?u=" . $tmuser . "&amp;rg=" . $region . "')";
	    $activeClinchedMileage = $row['activeClinched'];
	    if ($row['traveler'] != "" && $row['includeInRanks'] == "1") {
		$activeMileageRank = $row['rank'];
	    } else {
		$activeMileageRank = "N/A";
	    }

	    // build arrays that will form the contents of the travelers
	    // by region stats for active systems
	    $activeTravelerInfo = array();
	    $activeClinchedRes->data_seek(0);
	    while ($row = $activeClinchedRes->fetch_assoc()) {
		$activeTravelerInfo[$row['traveler']]['activeClinched'] = $row['activeClinched'];
            }

	    // and active+preview
            $sql_command = <<<SQL
            SELECT
		co.traveler,
		co.activePreviewMileage as activePreviewClinched,
		le.includeInRanks
            FROM clinchedOverallMileageByRegion AS co
	    JOIN listEntries le ON co.traveler = le.traveler
            WHERE co.region = '$region' AND co.activePreviewMileage > 0
	    GROUP BY co.traveler, le.includeInRanks
            ORDER BY activePreviewClinched DESC;
SQL;
            $activePreviewClinchedRes = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($activePreviewClinchedRes, 'activePreviewClinched');
//            $link = "redirect('/user/mapview.php?u=" . $tmuser . "&amp;rg=" . $region . "')";
	    $activePreviewClinchedMileage = $row['activePreviewClinched'];
	    if ($row['traveler'] != "" && $row['includeInRanks'] == "1") {
		$activePreviewMileageRank = $row['rank'];
	    } else {
		$activePreviewMileageRank = "N/A";
	    }

	    // build arrays that will form the contents of the travelers
	    // by region stats for active+preview systems
	    $activePreviewTravelerInfo = array();
	    $activePreviewClinchedRes->data_seek(0);
	    while ($row = $activePreviewClinchedRes->fetch_assoc()) {
		$activePreviewTravelerInfo[$row['traveler']]['activePreviewClinched'] = $row['activePreviewClinched'];
            }

            echo "<tr class='notclickable'><td>Distance Traveled</td>";
	    $style = 'style="background-color: '.tm_color_for_amount_traveled($activeClinchedMileage,$activeTotalMileage).';"';
	    echo "<td ".$style.">" . tm_convert_distance($activeClinchedMileage);
	    echo " of " . tm_convert_distance($activeTotalMileage) . " " . $tmunits . " (" . tm_percent($activeClinchedMileage, $activeTotalMileage) . "%) ";
	    echo "Rank: " . $activeMileageRank . "</td>";
	    $style = 'style="background-color: '.tm_color_for_amount_traveled($activePreviewClinchedMileage,$activePreviewTotalMileage).';"';
	    echo "<td ".$style.">" . tm_convert_distance($activePreviewClinchedMileage);
	    echo " of " . tm_convert_distance($activePreviewTotalMileage) . " " .$tmunits . " (" . tm_percent($activePreviewClinchedMileage, $activePreviewTotalMileage) . "%) ";
	    echo "Rank: " . $activePreviewMileageRank . "</td>";
	    echo "</tr>";

            // Second, fetch routes clinched/driven
            $totalActiveRoutes = tm_count_rows("routes", "LEFT JOIN systems on routes.systemName = systems.systemName WHERE region = '$region' AND systems.level = 'active'");
            $totalActivePreviewRoutes = tm_count_rows("routes", "LEFT JOIN systems on routes.systemName = systems.systemName WHERE region = '$region' AND ".$activeClause." ");

	    // Active only, clinched
            $sql_command = <<<SQL
-- Step 1: Calculate the total clinched mileage per traveler with includeInRanks = 1 and assign ranks
WITH TravelerClinched AS (
    SELECT
        cr.traveler,
        SUM(cr.clinched) AS clinched
    FROM 
        routes AS r
    LEFT JOIN 
        clinchedRoutes AS cr ON cr.route = r.root
    LEFT JOIN 
        systems ON r.systemName = systems.systemName
    JOIN 
        listEntries le ON cr.traveler = le.traveler
    WHERE 
        r.region = '$region'
        AND systems.level = 'active'
        AND le.includeInRanks = 1
    GROUP BY 
        cr.traveler
),
RankedClinched AS (
    SELECT
        traveler,
        clinched,
        RANK() OVER (ORDER BY clinched DESC) AS rankClinched
    FROM 
        TravelerClinched
),
FinalResult AS (
    SELECT
        cr.traveler,
        SUM(cr.clinched) AS clinched,
        le.includeInRanks,
        COALESCE(rc.rankClinched, -1) AS rankClinched
    FROM 
        routes AS r
    LEFT JOIN 
        clinchedRoutes AS cr ON cr.route = r.root
    LEFT JOIN 
        systems ON r.systemName = systems.systemName
    LEFT JOIN 
        RankedClinched rc ON cr.traveler = rc.traveler
    JOIN 
        listEntries le ON cr.traveler = le.traveler
    WHERE 
        r.region = '$region'
        AND systems.level = 'active'
    GROUP BY 
        cr.traveler, le.includeInRanks, rc.rankClinched
)
SELECT 
    traveler,
    clinched,
    includeInRanks,
    rankClinched
FROM 
    FinalResult
ORDER BY 
    clinched DESC;
SQL;
            $activeClinchedRes = tmdb_query($sql_command);
	    $row = tm_fetch_user_row_with_rank($activeClinchedRes, 'clinched');
	    if ($row['traveler'] != "" && $row['includeInRanks'] == "1") {
		$clinchedActiveRoutes = $row['clinched'];
		$clinchedActiveRoutesRank = $row['rank'];
	    } else {
		$clinchedActiveRoutes = 0;
		$clinchedActiveRoutesRank = "N/A";
	    }

	    // Active only, driven
            $sql_command = <<<SQL
WITH TravelerStats AS (
    SELECT
        cr.traveler,
        COUNT(cr.route) AS driven,
        SUM(cr.clinched) AS clinched
    FROM 
        routes AS r
    LEFT JOIN 
        clinchedRoutes AS cr ON cr.route = r.root
    LEFT JOIN 
        systems ON r.systemName = systems.systemName
    JOIN 
        listEntries le ON cr.traveler = le.traveler
    WHERE 
        r.region = '$region'
        AND systems.level = 'active'
    GROUP BY 
        cr.traveler
),
FilteredRanks AS (
    SELECT
        ts.traveler,
        ts.driven,
        ts.clinched,
        RANK() OVER (ORDER BY ts.driven DESC) AS rankDriven
    FROM 
        TravelerStats ts
    JOIN 
        listEntries le ON ts.traveler = le.traveler
    WHERE 
        le.includeInRanks = 1
),
RankedTravelers AS (
    SELECT
        ts.traveler,
        ts.driven,
        ts.clinched,
        le.includeInRanks,
        COALESCE(fr.rankDriven, -1) AS rankDriven
    FROM 
        TravelerStats ts
    LEFT JOIN 
        FilteredRanks fr ON ts.traveler = fr.traveler
    JOIN 
        listEntries le ON ts.traveler = le.traveler
)
SELECT 
    traveler,
    driven,
    clinched,
    includeInRanks,
    rankDriven
FROM 
    RankedTravelers
ORDER BY 
    driven DESC;
SQL;
            $activeDrivenRes = tmdb_query($sql_command);
	    $row = tm_fetch_user_row_with_rank($activeDrivenRes, 'driven');
	    if ($row['traveler'] != "" && $row['includeInRanks'] == "1") {
		$drivenActiveRoutes = $row['driven'];
		$drivenActiveRoutesRank = $row['rank'];
	    } else {
		$drivenActiveRoutes = 0;
		$drivenActiveRoutesRank = "N/A";
	    }

	    // add to the table of travelers by region stats
	    $activeDrivenRes->data_seek(0);
	    while ($row = $activeDrivenRes->fetch_assoc()) {
		$activeTravelerInfo[$row['traveler']]['driven'] = $row['driven'];
		$activeTravelerInfo[$row['traveler']]['clinched'] = $row['clinched'];
            }

	    // Active+Preview, clinched
            $sql_command = <<<SQL
-- Step 1: Calculate the total clinched mileage per traveler with includeInRanks = 1 and assign ranks
WITH TravelerClinched AS (
    SELECT
        cr.traveler,
        SUM(cr.clinched) AS clinched
    FROM 
        routes AS r
    LEFT JOIN 
        clinchedRoutes AS cr ON cr.route = r.root
    LEFT JOIN 
        systems ON r.systemName = systems.systemName
    JOIN 
        listEntries le ON cr.traveler = le.traveler
    WHERE 
        r.region = '$region'
        AND systems.level = 'active'
        AND le.includeInRanks = 1
    GROUP BY 
        cr.traveler
),
RankedClinched AS (
    SELECT
        traveler,
        clinched,
        RANK() OVER (ORDER BY clinched DESC) AS rankClinched
    FROM 
        TravelerClinched
),
FinalResult AS (
    SELECT
        cr.traveler,
        SUM(cr.clinched) AS clinched,
        le.includeInRanks,
        COALESCE(rc.rankClinched, -1) AS rankClinched
    FROM 
        routes AS r
    LEFT JOIN 
        clinchedRoutes AS cr ON cr.route = r.root
    LEFT JOIN 
        systems ON r.systemName = systems.systemName
    LEFT JOIN 
        RankedClinched rc ON cr.traveler = rc.traveler
    JOIN 
        listEntries le ON cr.traveler = le.traveler
    WHERE 
        r.region = '$region'
        AND (systems.level = 'active' OR systems.level = 'preview')
    GROUP BY 
        cr.traveler, le.includeInRanks, rc.rankClinched
)
SELECT 
    traveler,
    clinched,
    includeInRanks,
    rankClinched
FROM 
    FinalResult
ORDER BY 
    clinched DESC;
SQL;
            $activePreviewClinchedRes = tmdb_query($sql_command);
	    $row = tm_fetch_user_row_with_rank($activePreviewClinchedRes, 'clinched');
	    if ($row['traveler'] != "" && $row['includeInRanks'] == "1") {
		$clinchedActivePreviewRoutes = $row['clinched'];
		$clinchedActivePreviewRoutesRank = $row['rank'];
	    } else {
		$clinchedActivePreviewRoutes = 0;
		$clinchedActivePreviewRoutesRank = "N/A";
	    }

	    // Active+Preview, driven
            $sql_command = <<<SQL
WITH TravelerStats AS (
    SELECT
        cr.traveler,
        COUNT(cr.route) AS driven,
        SUM(cr.clinched) AS clinched
    FROM 
        routes AS r
    LEFT JOIN 
        clinchedRoutes AS cr ON cr.route = r.root
    LEFT JOIN 
        systems ON r.systemName = systems.systemName
    JOIN 
        listEntries le ON cr.traveler = le.traveler
    WHERE 
        r.region = '$region'
        AND (systems.level = 'active' OR systems.level = 'preview')
    GROUP BY 
        cr.traveler
),
FilteredRanks AS (
    SELECT
        ts.traveler,
        ts.driven,
        ts.clinched,
        RANK() OVER (ORDER BY ts.driven DESC) AS rankDriven
    FROM 
        TravelerStats ts
    JOIN 
        listEntries le ON ts.traveler = le.traveler
    WHERE 
        le.includeInRanks = 1
),
RankedTravelers AS (
    SELECT
        ts.traveler,
        ts.driven,
        ts.clinched,
        le.includeInRanks,
        COALESCE(fr.rankDriven, -1) AS rankDriven
    FROM 
        TravelerStats ts
    LEFT JOIN 
        FilteredRanks fr ON ts.traveler = fr.traveler
    JOIN 
        listEntries le ON ts.traveler = le.traveler
)
SELECT 
    traveler,
    driven,
    clinched,
    includeInRanks,
    rankDriven
FROM 
    RankedTravelers
ORDER BY 
    driven DESC;
SQL;
            $activePreviewDrivenRes = tmdb_query($sql_command);
	    $row = tm_fetch_user_row_with_rank($activePreviewDrivenRes, 'driven');
	    if ($row['traveler'] != "") {
		$drivenActivePreviewRoutes = $row['driven'];
		$drivenActivePreviewRoutesRank = $row['rank'];
	    } else {
		$drivenActivePreviewRoutes = 0;
		$drivenActivePreviewRoutesRank = "N/A";
	    }

	    // add to the table of travelers by region stats
	    $activePreviewDrivenRes->data_seek(0);
	    while ($row = $activePreviewDrivenRes->fetch_assoc()) {
		$activePreviewTravelerInfo[$row['traveler']]['driven'] = $row['driven'];
		$activePreviewTravelerInfo[$row['traveler']]['clinched'] = $row['clinched'];
            }



            echo "<tr onClick=\"window.open('/shields/clinched.php?u={$tmuser}')\">";
	    echo "<td>Routes Traveled</td>";
	    $style = 'style="background-color: '.tm_color_for_amount_traveled($drivenActiveRoutes,$totalActiveRoutes).';"';
	    echo "<td ".$style.">".$drivenActiveRoutes." of " . $totalActiveRoutes . " (". tm_percent($drivenActiveRoutes, $totalActiveRoutes) . "%) Rank: ".$drivenActiveRoutesRank."</td>";
	    $style = 'style="background-color: '.tm_color_for_amount_traveled($drivenActivePreviewRoutes,$totalActivePreviewRoutes).';"';
	    echo "<td ".$style.">".$drivenActivePreviewRoutes." of " . $totalActivePreviewRoutes . " (" . tm_percent($drivenActivePreviewRoutes, $totalActivePreviewRoutes, 2) . "%) Rank: ".$drivenActivePreviewRoutesRank."</td>";
	    echo "</tr>";

            echo "<tr onClick=\"window.open('/shields/clinched.php?u={$tmuser}')\">";
	    echo "<td>Routes Clinched</td>";
	    $style = 'style="background-color: '.tm_color_for_amount_traveled($clinchedActiveRoutes,$totalActiveRoutes).';"';
	    echo "<td ".$style.">".$clinchedActiveRoutes." of " . $totalActiveRoutes . " (" . tm_percent($clinchedActiveRoutes, $totalActiveRoutes) . "%) Rank: ". $clinchedActiveRoutesRank."</td>";
	    $style = 'style="background-color: '.tm_color_for_amount_traveled($clinchedActivePreviewRoutes,$totalActivePreviewRoutes).';"';
	    echo "<td ".$style.">".$clinchedActivePreviewRoutes." of " . $totalActivePreviewRoutes . " (" . tm_percent($clinchedActivePreviewRoutes, $totalActivePreviewRoutes) . "%) Rank: ". $clinchedActivePreviewRoutesRank."</td>";
	    echo "</tr>";

            ?>
        </tbody>
    </table>
    <table class="sortable gratable" id="systemsTable">
        <caption>TIP: Click on a column head to sort</caption>
        <thead>
        <tr>
            <th colspan="6">Statistics by System</th>
        </tr>
        <tr>
            <th>System Code</th>
            <th>System Name</th>
            <th>Clinched (<?php tm_echo_units(); ?>)</th>
            <th>Total (<?php tm_echo_units(); ?>)</th>
            <th>%</th>
            <th class="no-sort">Map</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql_command = <<<SQL
	SELECT
	  systems.fullName,
	  systems.level,
	  miByRegion.systemName,
          ROUND(IFNULL(clinchedByRegion.mileage, 0), 2) as clinchedMileage,
          ROUND(miByRegion.mileage, 2) as totalMileage,
          ROUND(IFNULL(clinchedByRegion.mileage, 0) / miByRegion.mileage * 100, 2) as percentage
        FROM systemMileageByRegion as miByRegion
        LEFT JOIN clinchedSystemMileageByRegion as clinchedByRegion                         ON clinchedByRegion.systemName = miByRegion.systemName AND
	       clinchedByRegion.region = '{$region}' AND
	       clinchedByRegion.traveler='{$tmuser}'
	       LEFT JOIN systems ON miByRegion.systemName = systems.systemName
	       WHERE miByRegion.region = '{$region}'
         ORDER BY percentage DESC;
SQL;

        $res = tmdb_query($sql_command);
        while ($row = $res->fetch_assoc()) {
            echo "<tr onClick=\"window.open('/user/system.php?u=" . $tmuser . "&sys=" . $row['systemName'] . "&amp;rg=" . $region . "')\" class=\"status-" . $row['level'] . "\">";
	    $style = 'style="background-color: '.tm_color_for_amount_traveled($row['clinchedMileage'],$row['totalMileage']).';"';
            echo "<td>" . $row['systemName'] . "</td>";
            echo "<td>" . $row['fullName'] . "</td>";
            echo "<td ".$style.">" . tm_convert_distance($row['clinchedMileage']) . "</td>";
            echo "<td ".$style.">" . tm_convert_distance($row['totalMileage']) . "</td>";
            echo "<td ".$style." data-sort=\"".$row['percentage']."\">" . $row['percentage'] . "%</td>";
            echo "<td class='link'><a href='/hb/showroute.php?rg={$region}&amp;sys={$row['systemName']}'>HB</a></td></tr>";
        }
        $res->free();
        ?>
        </tbody>
    </table>
    <table class="sortable gratable" id="routesTable">
        <caption>Click on a column head to sort. Click on a row to see the details and a map of the route.</caption>
        <thead>
            <tr><th colspan="6">Statistics by Route: (<?php echo "<a href=\"/user/mapview.php?u=".$tmuser."&amp;rg=".$region."\">" ?>Full Map)</a></th></tr>
            <tr><th>Tier</th><th class="no-sort">Route</th><th class="sortable">#</th><th>Clinched (<?php tm_echo_units(); ?>)</th><th>Total (<?php tm_echo_units(); ?>)</th><th>%</th></tr>
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
                    LEFT JOIN connectedRouteRoots AS crr ON r.root = crr.root
                    LEFT JOIN connectedRoutes as conr on crr.firstRoot = conr.firstRoot OR conr.firstRoot = r.root
                    WHERE region = '{$region}'
                    ORDER BY sys.tier, conr.csvOrder, r.rootOrder
SQL;
                $res = tmdb_query($sql_command);
                while ($row = $res->fetch_assoc()) {
	    	    $style = 'style="background-color: '.tm_color_for_amount_traveled($row['clinchedMileage'],$row['totalMileage']).';"';
                    echo "<tr onClick=\"window.open('/hb/showroute.php?u=".$tmuser."&amp;r=".$row['root']."')\" ".$style.">";
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
                    echo "<td data-sort=\"".$row['percentage']."\">".$row['percentage']."%</td></tr>\n";
                }
                $res->free();
            ?>
        </tbody>
    </table>


    <a name="rankings"></a>
    <table class="travelersTable">
    <thead>
      <tr><th colspan="2">Travelers in Region <?php echo "$region"; ?></th></tr>
      <tr><th>Active Systems</th><th>Active+Preview Systems</th></tr>
    </thead>
    <tbody>
    <tr><td>
    <table class="sortable gratable" id="activeTravelersTable" style="width: auto;">
        <thead>
            <tr><th>Rank</th><th>Traveler</th><th>Distance Traveled (<?php tm_echo_units(); ?>)</th><th>%</th><th>Traveled Routes</th><th>Clinched Routes</th></tr>
	  <tr style=><td></td><td>TOTAL CLINCHABLE</td><td><?php echo tm_convert_distance($activeTotalMileage); ?></td><td>100%</td><td><?php echo "$totalActiveRoutes"; ?></td><td><?php echo "$totalActiveRoutes"; ?></td></tr>
        </thead>
        <tbody>
	  <?php
	  $prev_mileage = 0;
	  $pre_rank = 1;
	  $tie_rank = 1;
	  foreach ($activeTravelerInfo as $traveler => $stats) {
	      if ($traveler == "") {
                  continue;  // this happens, but how?!
              }
              if ($traveler == $tmuser) {
                  $highlight = 'user-highlight';
              } else {
                 $highlight = '';
              }
	      $tie_rank = ($prev_mileage == $stats['activeClinched']) ? $tie_rank : $pre_rank;
	      $mileageStyle = 'style="background-color: '.tm_color_for_amount_traveled($stats['activeClinched'],$activeTotalMileage).';"';
	      $drivenStyle = 'style="background-color: '.tm_color_for_amount_traveled($stats['driven'],$totalActiveRoutes).';"';
	      $clinchedStyle = 'style="background-color: '.tm_color_for_amount_traveled($stats['clinched'],$totalActiveRoutes).';"';
	      echo "<tr class=\"".$highlight."\" onClick=\"window.document.location='?u=".$traveler."&rg=$region'\">";
	      echo "<td>".$tie_rank."</td>";
	      echo "<td>".$traveler."</td>";
	      echo "<td ".$mileageStyle.">".tm_convert_distance($stats['activeClinched'])."</td>";
	      $pct = round($stats['activeClinched'] / $activeTotalMileage * 100, 2);
	      echo "<td ".$mileageStyle." data-sort=\"".$pct."\">".$pct."%</td>";
	      echo "<td ".$drivenStyle.">".$stats['driven']."</td>";
	      echo "<td ".$clinchedStyle.">".$stats['clinched']."</td></tr>\n";
	      $pre_rank += 1;
	      $prev_mileage = $stats['activeClinched'];
          }
	  ?>
	</tbody>
	</table>
    </td><td>
    <table class="sortable gratable" id="activePreviewTravelersTable" style="width: auto;">
        <thead>
            <tr><th>Rank</th><th class="sortable">Traveler</th><th>Distance Traveled (<?php tm_echo_units(); ?>)</th><th>%</th><th>Traveled Routes</th><th>Clinched Routes</th></tr>
	  <tr style=><td></td><td>TOTAL CLINCHABLE</td><td><?php echo tm_convert_distance($activePreviewTotalMileage); ?></td><td>100.00%</td><td><?php echo "$totalActivePreviewRoutes"; ?></td><td><?php echo "$totalActivePreviewRoutes"; ?></td></tr>
        </thead>
        <tbody>
	  <?php
	  $prev_mileage = 0;
	  $pre_rank = 1;
	  $tie_rank = 1;
	  foreach ($activePreviewTravelerInfo as $traveler => $stats) {
	      if ($traveler == "") {
	          continue;  // this happens, but how?!
              }
              if ($traveler == $tmuser) {
                  $highlight = 'user-highlight';
              } else {
                 $highlight = '';
              }
	      $tie_rank = ($prev_mileage == $stats['activePreviewClinched']) ? $tie_rank : $pre_rank;
	      $mileageStyle = 'style="background-color: '.tm_color_for_amount_traveled($stats['activePreviewClinched'],$activePreviewTotalMileage).';"';
	      $drivenStyle = 'style="background-color: '.tm_color_for_amount_traveled($stats['driven'],$totalActivePreviewRoutes).';"';
	      $clinchedStyle = 'style="background-color: '.tm_color_for_amount_traveled($stats['clinched'],$totalActivePreviewRoutes).';"';
	      echo "<tr class=\"".$highlight."\" onClick=\"window.document.location='?u=".$traveler."&rg=$region'\">";
	      echo "<td>".$tie_rank."</td>";
	      echo "<td>".$traveler."</td>";
	      echo "<td ".$mileageStyle.">".tm_convert_distance($stats['activePreviewClinched'])."</td>";
	      $pct = round($stats['activePreviewClinched'] / $activePreviewTotalMileage * 100, 2);
	      echo "<td ".$mileageStyle." data-sort=\"".$pct."\">".$pct."%</td>";
	      echo "<td ".$drivenStyle.">".$stats['driven']."</td>";
	      echo "<td ".$clinchedStyle.">".$stats['clinched']."</td></tr>\n";
	      $pre_rank += 1;
	      $prev_mileage = $stats['activePreviewClinched'];
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
    $activeClinchedRes->free();
    $activeDrivenRes->free();
    $activePreviewClinchedRes->free();
    $activePreviewDrivenRes->free();
    $tmdb->close();
?>
<script type="application/javascript" src="../lib/waypoints.js.php?<?php echo $_SERVER['QUERY_STRING']?>"></script>
</html>
