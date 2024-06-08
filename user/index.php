<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	A basic user stats page. 
	URL Params honored:
		u - the user, which is also taken from a cookie if 
		previously provided to any TM page.
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
    <style type="text/css">
        #body {
            left: 0px;
            top: 80px;
            bottom: 0px;
            overflow: auto;
            padding: 20px;
        }

        #body h2 {
            margin: auto;
            text-align: center;
            padding: 10px;
        }
        #userLinks {
        	text-align: center;
    		font-size: 18px;
        }
        #logLinks {
        	text-align: center;
    		font-size: 14px;
        }
        #scrollableMapview {
        	text-align: center;
    		font-size: 24px;
        }
        #topstats {
        	text-align: center;
    		font-size: 24px;
        }
    </style>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
    <?php tm_common_js(); ?>
<script type="text/javascript">
    $(document).ready(function () {
            $("#clinchedheader").click();
            $("#sortsecond").click();
            });
</script>
<title>
        <?php
        echo "Main Travel Mapping - ".$tmMode_p." User Page for " . $tmuser;
        ?>
    </title>
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<div id="userbox">

<?php

tm_user_select_form();

if ( $tmuser == "null") {
    echo "<h1>Select a User to Continue</h1>\n";
    tm_user_select_form();
    echo "</div>\n";
    require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php";
    echo "</body>\n";
    echo "</html>\n";
    exit;
}
echo "<h1>Main Travel Mapping - ".$tmMode_p." User Page for ".$tmuser."</h1>";
?>
</div>
<div id="body">
    <h2>User Links</h2>
    <ul class="text">
      <li><a href="/logs/users/<?php echo $tmuser; ?>.log">Log File</a>, where you can find any errors from processing <a href="https://github.com/TravelMapping/UserData/blob/master/<?php echo $tmlistdir."/".$tmuser; ?>.list"><?php echo $tmuser; ?>.list</a>, and statistics.</li>
      <li>Browse the <?php echo $tmmode_s;?> travels of <?php echo $tmuser; ?> with <a href="mapview.php?v">Mapview</a>.</li>
      <li><a href="topstats.php">Browse the top stats for the <?php echo $tmmode_s;?> travels of <?php echo $tmuser; ?></a>.</li>
      <?php
       if ($tmmode_s == "highway") {
          echo '<li><a href="routesbynumber.php">Table of routes traveled by number for '.$tmuser.'</a>.</li>';
      } ?>
    </ul>
    <div id="overall">
        <h2>Overall Stats</h2>
	<p class="text">
	Click on "Routes Traveled" or "Routes Clinched" to see lists of all routes traveled/clinched by <?php echo $tmuser; ?>.
	</p>
        <table class="gratable" style="width: 60%" id="tierTable">
	    <thead>
	    <tr><th /><th>Active Systems</th><th>Active+Preview Systems</th></tr>
	    </thead>
            <tbody>
            <?php
            //First fetch mileage driven, both active and active+preview
            $sql_command = <<<SQL
WITH RankedMileage AS (
  SELECT traveler,
  ROUND(SUM(o.activeMileage), 2) AS totalActiveMileage,
  RANK() OVER (ORDER BY SUM(o.activeMileage) DESC) AS rankActiveMileage,
  ROUND(SUM(COALESCE(co.activeMileage, 0)), 2) AS clinchedActiveMileage,
  ROUND(SUM(COALESCE(co.activeMileage, 0)) / SUM(o.activeMileage) * 100, 2) AS activePercentage,
  ROUND(SUM(o.activePreviewMileage), 2) AS totalActivePreviewMileage,
  RANK() OVER (ORDER BY SUM(o.activePreviewMileage) DESC) AS rankActivePreviewMileage,
  ROUND(SUM(COALESCE(co.activePreviewMileage, 0)), 2) AS clinchedActivePreviewMileage,
  ROUND(SUM(COALESCE(co.activePreviewMileage, 0)) / SUM(o.activePreviewMileage) * 100, 2) AS activePreviewPercentage
  FROM overallMileageByRegion o
  LEFT JOIN clinchedOverallMileageByRegion co ON co.region = o.region
  GROUP BY traveler )
  SELECT * FROM RankedMileage WHERE traveler = '$tmuser';
SQL;
            $res = tmdb_query($sql_command);
            $row = $res->fetch_assoc();
            $res->free();
            echo "<tr class='notclickable'><td>Distance Traveled</td>";
	    echo '<td style="background-color: ';
	    echo tm_color_for_amount_traveled($row['clinchedActiveMileage'],$row['totalActiveMileage']);
	    echo ';">' . tm_convert_distance($row['clinchedActiveMileage']);
	    echo "/" . tm_convert_distance($row['totalActiveMileage']) . " ";
	    tm_echo_units();
	    echo " (" . $row['activePercentage'] . "%) Rank: ".$row['rankActiveMileage']."</td>";
	    echo '<td style="background-color: ';
	    echo tm_color_for_amount_traveled($row['clinchedActivePreviewMileage'],$row['totalActivePreviewMileage']);
	    echo ';">' . tm_convert_distance($row['clinchedActivePreviewMileage']);
	    echo "/" . tm_convert_distance($row['totalActivePreviewMileage']) . " ";
	    tm_echo_units();
	    echo " (" . $row['activePreviewPercentage'] . "%) Rank: ".$row['rankActivePreviewMileage']."</td>";
	    echo "</tr>";


            //Second, fetch routes driven/clinched active only
	    $sql_command = "SELECT COUNT(cr.route) AS total FROM connectedRoutes AS cr LEFT JOIN systems ON cr.systemName = systems.systemName WHERE (systems.level = 'active');";
            $res = tmdb_query($sql_command);
            $row = $res->fetch_assoc();
	    $activeRoutes = $row['total'];
	    $res->free();

	    $sql_command = <<<SQL
WITH TravelerStats AS (
    SELECT
        ccr.traveler,
        COUNT(ccr.route) AS driven,
        SUM(ccr.clinched) AS clinched,
        ROUND(COUNT(ccr.route) / ".$activeRoutes." * 100, 2) AS drivenPercent,
        ROUND(SUM(ccr.clinched) / ".$activeRoutes." * 100, 2) AS clinchedPercent
    FROM
        connectedRoutes AS cr
    LEFT JOIN
        clinchedConnectedRoutes AS ccr ON cr.firstRoot = ccr.route
    LEFT JOIN
        routes ON ccr.route = routes.root
    LEFT JOIN
        systems ON routes.systemName = systems.systemName
    WHERE
        systems.level = 'active'
    GROUP BY
        ccr.traveler
)
SELECT *
FROM (
    SELECT
        traveler,
        driven,
        clinched,
        drivenPercent,
        clinchedPercent,
        RANK() OVER (ORDER BY driven DESC) AS drivenRank,
        RANK() OVER (ORDER BY clinched DESC) AS clinchedRank
    FROM
        TravelerStats
) AS RankedStats
WHERE
    traveler = '$tmuser'
ORDER BY
    traveler;
SQL;
            $res = tmdb_query($sql_command);
            $row = $res->fetch_assoc();
	    $activeDriven = $row['driven'];
	    $activeDrivenPct = $row['drivenPercent'];
	    $activeDrivenRank = $row['drivenRank'];
	    $activeClinched = $row['clinched'];
	    $activeClinchedPct = $row['clinchedPercent'];
	    $activeClinchedRank = $row['clinchedRank'];
	    $res->free();

	    // and active+preview
	    $sql_command = "SELECT COUNT(cr.route) AS total FROM connectedRoutes AS cr LEFT JOIN systems ON cr.systemName = systems.systemName WHERE (systems.level = 'active' OR systems.level = 'preview');";
            $res = tmdb_query($sql_command);
            $row = $res->fetch_assoc();
	    $activePreviewRoutes = $row['total'];
	    $res->free();

	    $sql_command = <<<SQL
WITH TravelerStats AS (
    SELECT
        ccr.traveler,
        COUNT(ccr.route) AS driven,
        SUM(ccr.clinched) AS clinched,
        ROUND(COUNT(ccr.route) / ".$activePreviewRoutes." * 100, 2) AS drivenPercent,
        ROUND(SUM(ccr.clinched) / ".$activePreviewRoutes." * 100, 2) AS clinchedPercent
    FROM
        connectedRoutes AS cr
    LEFT JOIN
        clinchedConnectedRoutes AS ccr ON cr.firstRoot = ccr.route
    LEFT JOIN
        routes ON ccr.route = routes.root
    LEFT JOIN
        systems ON routes.systemName = systems.systemName
    WHERE
        systems.level = 'active' OR systems.level = 'preview'
    GROUP BY
        ccr.traveler
)
SELECT *
FROM (
    SELECT
        traveler,
        driven,
        clinched,
        drivenPercent,
        clinchedPercent,
        RANK() OVER (ORDER BY driven DESC) AS drivenRank,
        RANK() OVER (ORDER BY clinched DESC) AS clinchedRank
    FROM
        TravelerStats
) AS RankedStats
WHERE
    traveler = '$tmuser'
ORDER BY
    traveler;
SQL;
            $res = tmdb_query($sql_command);
            $row = $res->fetch_assoc();
	    $activePreviewDriven = $row['driven'];
	    $activePreviewDrivenPct = $row['drivenPercent'];
	    $activePreviewDrivenRank = $row['drivenRank'];
	    $activePreviewClinched = $row['clinched'];
	    $activePreviewClinchedPct = $row['clinchedPercent'];
	    $activePreviewClinchedRank = $row['clinchedRank'];
	    $res->free();

            echo "<tr onclick=\"window.open('/shields/clinched.php?u={$tmuser}&amp;cort=traveled')\">";
	    echo "<td>Routes Traveled</td>";
	    echo '<td style="background-color: ';
	    echo tm_color_for_amount_traveled($activeDriven,$activeRoutes);
	    echo ';">'.$activeDriven." of " . $activeRoutes . " (" . $activeDrivenPct . "%) Rank: ".$activeDrivenRank."</td>";
	    echo '<td style="background-color: ';
	    echo tm_color_for_amount_traveled($activePreviewDriven,$activePreviewRoutes);
	    echo ';">'.$activePreviewDriven." of " . $activePreviewRoutes . " (" . $activePreviewDrivenPct . "%) Rank: ".$activePreviewDrivenRank."</td>";
	    echo "</tr>";

            echo "<tr onclick=\"window.open('/shields/clinched.php?u={$tmuser}')\">";
	    echo "<td>Routes Clinched</td>";
	    echo '<td style="background-color: ';
	    echo tm_color_for_amount_traveled($activeClinched,$activeRoutes);
	    echo ';">'.$activeClinched." of " . $activeRoutes . " (" . $activeClinchedPct . "%) Rank: ".$activeClinchedRank."</td>";
	    echo '<td style="background-color: ';
	    echo tm_color_for_amount_traveled($activePreviewClinched,$activePreviewRoutes);
	    echo ';">'.$activePreviewClinched." of " . $activePreviewRoutes . " (" . $activePreviewClinchedPct . "%) Rank: ".$activePreviewClinchedRank."</td>";
	    echo "</tr>";
            ?>
            </tbody>
        </table>
    </div>
    <h2>Stats by Region</h2>
    <p class="text">
    User <?php echo $tmuser; ?> has <?php echo $tmmode_s;?> travels in <?php echo tm_count_rows("clinchedOverallMileageByRegion", "where traveler='$tmuser'"); ?> regions.  Click in a row to view detailed stats for the region, on the "Map" link to load the region in Mapview, and the "HB" link to get a list of <?php echo $tmmode_p;?> in the region.
    </p>
    <table class="sortable gratable" id="regionsTable">
        <thead>
	<tr><th colspan="2" /><th colspan="3">Active Systems Only</th>
	    <th colspan="3">Active+Preview Systems</th><th colspan="2" /></tr>
        <tr>
            <th>Country</th>
            <th>Region</th>
            <th>Clinched (<?php tm_echo_units(); ?>)</th>
            <th>Overall (<?php tm_echo_units(); ?>)</th>
            <th>%</th>
            <th id="clinchedheader">Clinched (<?php tm_echo_units(); ?>)</th>
            <th>Overall (<?php tm_echo_units(); ?>)</th>
            <th>%</th>
            <th colspan="2" class="no-sort">Map</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql_command = <<<SQL
SELECT rg.country, 
  rg.code, 
  rg.name, 
  co.activeMileage AS clinchedActiveMileage, 
  o.activeMileage AS totalActiveMileage, 
  co.activePreviewMileage AS clinchedActivePreviewMileage, 
  o.activePreviewMileage AS totalActivePreviewMileage 
FROM overallMileageByRegion AS o 
  INNER JOIN clinchedOverallMileageByRegion AS co ON co.region = o.region 
  INNER JOIN regions AS rg ON rg.code = co.region 
WHERE co.traveler = '$tmuser';
SQL;
        $res = tmdb_query($sql_command);
        while ($row = $res->fetch_assoc()) {
            if ( $row['totalActiveMileage'] == 0) {
                $activePercent = "0.00";
            }
            else {
                $activePercent = round($row['clinchedActiveMileage'] / $row['totalActiveMileage'] * 100.0, 2);
     	        $activePercent = sprintf('%0.2f', $activePercent);
            }
            $activePreviewPercent = round($row['clinchedActivePreviewMileage'] / $row['totalActivePreviewMileage'] * 100.0, 2);
	    $activePreviewPercent = sprintf('%0.2f', $activePreviewPercent);
	    $activeStyle = 'style="text-align: right; background-color: '.tm_color_for_amount_traveled($row['clinchedActiveMileage'],$row['totalActiveMileage']).';"';
	    $activePreviewStyle = 'style="text-align: right; background-color: '.tm_color_for_amount_traveled($row['clinchedActivePreviewMileage'],$row['totalActivePreviewMileage']).';"';
            echo "<tr onclick=\"window.document.location='/user/region.php?u=" . $tmuser . "&amp;rg=" . $row['code'] . "'\"><td>" . $row['country'] . "</td><td>" . $row['name'] . '</td><td '.$activeStyle.'>' . tm_convert_distance($row['clinchedActiveMileage']) . "</td><td ".$activeStyle.">" . tm_convert_distance($row['totalActiveMileage']) . "</td><td ".$activeStyle." data-sort=\"".$activePercent."\">" . $activePercent . "%</td><td ".$activePreviewStyle.">" . tm_convert_distance($row['clinchedActivePreviewMileage']) . "</td><td ".$activePreviewStyle.">" . tm_convert_distance($row['totalActivePreviewMileage']) . "</td><td ".$activePreviewStyle." data-sort=\"".$activePreviewPercent."\">" . $activePreviewPercent . "%</td><td class='link'><a href=\"/user/mapview.php?u=" . $tmuser . "&amp;rg=" . $row['code'] . "\">Map</a></td><td class='link'><a href='/hb?rg={$row['code']}'>HB</a></td></tr>";
        }
        $res->free();
        ?>
        </tbody>
    </table>
    <h2>Stats by System</h2>
    <p class="text">
    User <?php echo $tmuser.' has '.$tmmode_s;?> travels in <?php echo tm_count_distinct_rows("clinchedSystemMileageByRegion", "systemName", "where traveler='$tmuser'").' '.$tmmode_s ?> systems.  Click in a row to view detailed stats for the system, on the "Map" link to load the system in Mapview, and the "HB" link to get a list of <?php echo $tmmode_p;?> in the system.
    </p>
    <table class="gratable sortable" id="systemsTable">
        <thead>
        <tr>
            <th>Country</th>
            <th>System Code</th>
            <th>System Name</th>
            <th>Tier</th>
            <th>Status</th>
            <th>Clinched (<?php tm_echo_units(); ?>)</th>
            <th>Total (<?php tm_echo_units(); ?>)</th>
            <th id="sortsecond" data-sort-tbr="6">% Clinched</th>
            <th colspan="2" class="no-sort">Map</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // need to build system mileages from systemMileageByRegion
        // and clinchedSystemMileageByRegion tables since they already
        // take concurrencies into account properly
        $sql_command = <<<SQL
SELECT
sys.countryCode,
sys.systemName,
sys.level,
sys.tier,
sys.fullName,
COALESCE(ROUND(SUM(csm.mileage), 2), 0) AS clinchedMileage,
COALESCE(ROUND(SUM(sm.mileage), 2), 0) AS totalMileage,
COALESCE(ROUND(SUM(csm.mileage)/ SUM(sm.mileage) * 100, 2), 0) AS percentage
FROM systems as sys
INNER JOIN systemMileageByRegion AS sm 
  ON sm.systemName = sys.systemName
LEFT JOIN clinchedSystemMileageByRegion AS csm 
  ON sm.region = csm.region AND 
     csm.systemName = sys.systemName AND
     csm.traveler = '$tmuser'
WHERE (sys.level = 'active' OR sys.level = 'preview')
GROUP BY sm.systemName;
SQL;
        $res = tmdb_query($sql_command);
        while ($row = $res->fetch_assoc()) {
	    if ($row['clinchedMileage'] == 0) continue;
	    $systemStyle = 'style="text-align: right; background-color: '.tm_color_for_amount_traveled($row['clinchedMileage'],$row['totalMileage']).';"';
            echo "<tr onclick=\"window.document.location='/user/system.php?u=" . $tmuser . "&amp;sys=" . $row['systemName'] . "'\" class=\"status-" . $row['level'] . "\">";
            echo "<td>" . $row['countryCode'] . "</td>";
            echo "<td>" . $row['systemName'] . "</td>";
            echo "<td>" . $row['fullName'] . "</td>";
            echo "<td>Tier " . $row['tier'] . "</td>";
            echo "<td>" . $row['level'] . "</td>";
            echo "<td ".$systemStyle.">" . tm_convert_distance($row['clinchedMileage']) . "</td>";
            echo "<td ".$systemStyle.">" . tm_convert_distance($row['totalMileage']) . "</td>";
            echo "<td ".$systemStyle." data-sort=\"".$row['percentage']."\">" . $row['percentage'] . "%</td>";
            echo "<td class='link'><a href=\"/user/mapview.php?u={$tmuser}&amp;sys={$row['systemName']}\">Map</a></td>";
            echo "<td class='link'><a href='/hb?sys={$row['systemName']}'>HB</a></td></tr>";
        }
        $res->free();
        ?>
        </tbody>
    </table>
</div>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
