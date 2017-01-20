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
        #logLinks {
        	text-align: center;
    		font-size: 14px;
        }
    </style>
    <!-- jQuery -->
    <script type="application/javascript" src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- TableSorter -->
    <script type="application/javascript" src="/lib/jquery.tablesorter.min.js"></script>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
    <title>
        <?php
        echo "Traveler Stats for " . $tmuser;
        ?>
    </title>
</head>
<body>
<script type="text/javascript">
    $(document).ready(function () {
            $("#regionsTable").tablesorter({
                sortList: [[5,1], [4, 1]],
                headers: {0: {sorter: false}, 6: {sorter: false}}
            });
            $("#systemsTable").tablesorter({
                sortList: [[7,1], [6, 1]],
                headers: {0: {sorter: false}, 9: {sorter: false}}
            });
            $('td').filter(function() {
                return this.innerHTML.match(/^[0-9\s\.,%]+$/);
            }).css('text-align','right');
        }
    );
</script>
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
echo "<h1>Traveler Stats for ".$tmuser."</h1>";
?>
</div>
<div id="body">
	<div id="logLinks">
		<a href="/logs/<?php echo $tmuser; ?>.log">Log File</a>
	</div>
    <div id="overall">
        <h2>Overall Stats</h2>
        <table class="gratable" style="width: 60%" id="tierTable">
	    <thead>
	    <tr><th /><th>Active Systems</th><th>Active+Preview Systems</th></tr>
	    </thead>
            <tbody>
            <?php
            //First fetch mileage driven, both active and active+preview
            $sql_command = <<<SQL
SELECT
round(sum(o.activeMileage), 2) as totalActiveMileage,
round(sum(coalesce(co.activeMileage, 0)), 2) as clinchedActiveMileage,
round(sum(coalesce(co.activeMileage, 0)) / sum(o.activeMileage) * 100, 2) AS activePercentage,
round(sum(o.activePreviewMileage), 2) as totalActivePreviewMileage,
round(sum(coalesce(co.activePreviewMileage, 0)), 2) as clinchedActivePreviewMileage,
round(sum(coalesce(co.activePreviewMileage, 0)) / sum(o.activePreviewMileage) * 100, 2) AS activePreviewPercentage
FROM overallMileageByRegion o
LEFT JOIN clinchedOverallMileageByRegion co ON co.region = o.region AND traveler = '$tmuser'
SQL;
            $res = tmdb_query($sql_command);
            $row = $res->fetch_assoc();
            $res->free();
            echo "<tr class='notclickable' style=\"background-color:#EEEEFF\"><td>Miles Driven</td>";
	    echo "<td>" . $row['clinchedActiveMileage'];
	    echo "/" . $row['totalActiveMileage'] . " mi (";
	    echo $row['activePercentage'] . "%) Rank: TBD</td>";
	    echo "<td>" . $row['clinchedActivePreviewMileage'];
	    echo "/" . $row['totalActivePreviewMileage'] . " mi (";
	    echo $row['activePreviewPercentage'] . "%) Rank: TBD</td>";
	    echo "</tr>";


            //Second, fetch routes driven/clinched active only
	    $sql_command = "SELECT COUNT(cr.route) AS total FROM connectedRoutes AS cr LEFT JOIN systems ON cr.systemName = systems.systemName WHERE (systems.level = 'active');";
            $res = tmdb_query($sql_command);
            $row = $res->fetch_assoc();
	    $activeRoutes = $row['total'];
	    $res->free();

            $sql_command = "SELECT COUNT(ccr.route) AS driven, SUM(ccr.clinched) AS clinched, ROUND(COUNT(ccr.route) / ".$activeRoutes." * 100,2) AS drivenPercent, ROUND(SUM(ccr.clinched) / ".$activeRoutes." * 100,2) AS clinchedPercent FROM connectedRoutes AS cr LEFT JOIN clinchedConnectedRoutes AS ccr ON cr.firstRoot = ccr.route AND traveler = '" . $tmuser . "' LEFT JOIN routes ON ccr.route = routes.root LEFT JOIN systems ON routes.systemName = systems.systemName WHERE systems.level = 'active';";
            $res = tmdb_query($sql_command);
            $row = $res->fetch_assoc();
	    $activeDriven = $row['driven'];
	    $activeDrivenPct = $row['drivenPercent'];
	    $activeClinched = $row['clinched'];
	    $activeClinchedPct = $row['clinchedPercent'];
	    $res->free();

	    // and active+preview
	    $sql_command = "SELECT COUNT(cr.route) AS total FROM connectedRoutes AS cr LEFT JOIN systems ON cr.systemName = systems.systemName WHERE (systems.level = 'active' OR systems.level = 'preview');";
            $res = tmdb_query($sql_command);
            $row = $res->fetch_assoc();
	    $activePreviewRoutes = $row['total'];
	    $res->free();

            $sql_command = "SELECT COUNT(ccr.route) AS driven, SUM(ccr.clinched) AS clinched, ROUND(COUNT(ccr.route) / ".$activeRoutes." * 100,2) AS drivenPercent, ROUND(SUM(ccr.clinched) / ".$activeRoutes." * 100,2) AS clinchedPercent FROM connectedRoutes AS cr LEFT JOIN clinchedConnectedRoutes AS ccr ON cr.firstRoot = ccr.route AND traveler = '" . $tmuser . "' LEFT JOIN routes ON ccr.route = routes.root LEFT JOIN systems ON routes.systemName = systems.systemName WHERE (systems.level = 'active' OR systems.level = 'preview');";
            $res = tmdb_query($sql_command);
            $row = $res->fetch_assoc();
	    $activePreviewDriven = $row['driven'];
	    $activePreviewDrivenPct = $row['drivenPercent'];
	    $activePreviewClinched = $row['clinched'];
	    $activePreviewClinchedPct = $row['clinchedPercent'];
	    $res->free();

            echo "<tr onclick=\"window.open('/shields/clinched.php?u={$tmuser}&amp;cort=traveled')\">";
	    echo "<td>Routes Driven</td>";
	    echo "<td>".$activeDriven." of " . $activeRoutes . " (" . $activeDrivenPct . "%) Rank: TBD</td>";
	    echo "<td>".$activePreviewDriven." of " . $activePreviewRoutes . " (" . $activePreviewDrivenPct . "%) Rank: TBD</td>";
	    echo "</tr>";

            echo "<tr onclick=\"window.open('/shields/clinched.php?u={$tmuser}')\">";
	    echo "<td>Routes Clinched</td>";
	    echo "<td>".$activeClinched." of " . $activeRoutes . " (" . $activeClinchedPct . "%) Rank: TBD</td>";
	    echo "<td>".$activePreviewClinched." of " . $activePreviewRoutes . " (" . $activePreviewClinchedPct . "%) Rank: TBD</td>";
	    echo "</tr>";
            ?>
            </tbody>
        </table>
    </div>
    <h2>Stats by Region</h2>
    <!-- h3>Legend: A=active systems only, A+P=active and preview systems</h3> -->
    <table class="gratable tablesorter" id="regionsTable">
        <thead>
	<tr><th colspan="2" /><th colspan="3">Active Systems Only</th>
	    <th colspan="3">Active+Preview Systems</th><th colspan="2" /></tr>
        <tr>
            <th class="sortable">Country</th>
            <th class="sortable">Region</th>
            <th class="sortable">Clinched (mi)</th>
            <th class="sortable">Overall (mi)</th>
            <th class="sortable">% Clinched</th>
            <th class="sortable">Clinched (mi)</th>
            <th class="sortable">Overall (mi)</th>
            <th class="sortable">% Clinched</th>
            <th colspan="2">Map</th>
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
            echo "<tr onclick=\"window.document.location='/user/region.php?u=" . $tmuser . "&amp;rg=" . $row['code'] . "'\"><td>" . $row['country'] . "</td><td>" . $row['name'] . "</td><td>" . sprintf('%0.2f', $row['clinchedActiveMileage']) . "</td><td>" . sprintf('%0.2f', $row['totalActiveMileage']) . "</td><td>" . $activePercent . "%</td><td>" . sprintf('%0.2f', $row['clinchedActivePreviewMileage']) . "</td><td>" . sprintf('%0.2f', $row['totalActivePreviewMileage']) . "</td><td>" . $activePreviewPercent . "%</td><td class='link'><a href=\"/user/mapview.php?u=" . $tmuser . "&amp;rg=" . $row['code'] . "\">Map</a></td><td class='link'><a href='/hb?rg={$row['code']}'>HB</a></td></tr>";
        }
        $res->free();
        ?>
        </tbody>
    </table>
    <h2>Stats by System</h2>
    <table class="gratable tablesorter" id="systemsTable">
        <thead>
        <tr>
            <th class="sortable">Country</th>
            <th class="sortable">System Code</th>
            <th class="sortable">System Name</th>
            <th class="sortable">Tier</th>
            <th class="sortable">Status</th>
            <th class="sortable">Clinched Mileage</th>
            <th class="sortable">Total Mileage</th>
            <th class="sortable">Percent</th>
            <th colspan="2">Map</th>
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
            echo "<tr onclick=\"window.document.location='/user/system.php?u=" . $tmuser . "&amp;sys=" . $row['systemName'] . "'\" class=\"status-" . $row['level'] . "\">";
            echo "<td>" . $row['countryCode'] . "</td>";
            echo "<td>" . $row['systemName'] . "</td>";
            echo "<td>" . $row['fullName'] . "</td>";
            echo "<td>Tier " . $row['tier'] . "</td>";
            echo "<td>" . $row['level'] . "</td>";
            echo "<td>" . $row['clinchedMileage'] . "</td>";
            echo "<td>" . $row['totalMileage'] . "</td>";
            echo "<td>" . $row['percentage'] . "%</td>";
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
