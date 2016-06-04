<?php
include $_SERVER['DOCUMENT_ROOT']."/login.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	A basic user stats page. 
	URL Params:
		u - the user.
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
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
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- TableSorter -->
    <script src="/lib/jquery.tablesorter.min.js"></script>
    <title>
        <?php
        echo "Traveler Stats for " . $user;

        // establish connection to db
        $db = new mysqli("localhost", "travmap", "clinch", $dbname) or die("Failed to connect to database");
        ?>
    </title>
</head>
<body>
<script type="text/javascript">
    $(document).ready(function () {
            $("#regionsTable").tablesorter({
                sortList: [[4,1], [3, 1]],
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
<div id="header">
    <a href="/">Home</a>
    <a href="/hbtest">Highway Browser</a>

    <form id="userselect">
        <label>User: </label>
        <input type="text" name="u" form="userselect" value="<?php echo $user ?>">
        <input type="submit">
    </form>
    <h1>Traveler Stats for <?php echo $user; ?>:</h1>
</div>
<div id="body">
	<div id="logLinks">
		<a href="/logs/<?php echo $user; ?>.log">Log File</a>
	</div>
    <div id="overall">
        <h2>Overall Stats</h2>
        <table class="gratable" style="width: 60%" id="tierTable">
	    <thead>
	    <tr><td /><td>Active Systems</td><td>Active+Preview Systems</td></tr>
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
LEFT JOIN clinchedOverallMileageByRegion co ON co.region = o.region AND traveler = '$user'
SQL;

            echo "<!-- SQL:" . $sql_command . "-->";
            $res = $db->query($sql_command);
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
            echo "<!-- SQL:" . $sql_command . "-->";
            $res = $db->query($sql_command);
            $row = $res->fetch_assoc();
	    $activeRoutes = $row['total'];
	    $res->free();

            $sql_command = "SELECT COUNT(ccr.route) AS driven, SUM(ccr.clinched) AS clinched, ROUND(COUNT(ccr.route) / ".$activeRoutes." * 100,2) AS drivenPercent, ROUND(SUM(ccr.clinched) / ".$activeRoutes." * 100,2) AS clinchedPercent FROM connectedRoutes AS cr LEFT JOIN clinchedConnectedRoutes AS ccr ON cr.firstRoot = ccr.route AND traveler = '" . $user . "' LEFT JOIN routes ON ccr.route = routes.root LEFT JOIN systems ON routes.systemName = systems.systemName WHERE systems.level = 'active';";
            echo "<!-- SQL:" . $sql_command . "-->";
            $res = $db->query($sql_command);
            $row = $res->fetch_assoc();
	    $activeDriven = $row['driven'];
	    $activeDrivenPct = $row['drivenPercent'];
	    $activeClinched = $row['clinched'];
	    $activeClinchedPct = $row['clinchedPercent'];
	    $res->free();

	    // and active+preview
	    $sql_command = "SELECT COUNT(cr.route) AS total FROM connectedRoutes AS cr LEFT JOIN systems ON cr.systemName = systems.systemName WHERE (systems.level = 'active' OR systems.level = 'preview');";
            echo "<!-- SQL:" . $sql_command . "-->";
            $res = $db->query($sql_command);
            $row = $res->fetch_assoc();
	    $activePreviewRoutes = $row['total'];
	    $res->free();

            $sql_command = "SELECT COUNT(ccr.route) AS driven, SUM(ccr.clinched) AS clinched, ROUND(COUNT(ccr.route) / ".$activeRoutes." * 100,2) AS drivenPercent, ROUND(SUM(ccr.clinched) / ".$activeRoutes." * 100,2) AS clinchedPercent FROM connectedRoutes AS cr LEFT JOIN clinchedConnectedRoutes AS ccr ON cr.firstRoot = ccr.route AND traveler = '" . $user . "' LEFT JOIN routes ON ccr.route = routes.root LEFT JOIN systems ON routes.systemName = systems.systemName WHERE (systems.level = 'active' OR systems.level = 'preview');";
            echo "<!-- SQL:" . $sql_command . "-->";
            $res = $db->query($sql_command);
            $row = $res->fetch_assoc();
	    $activePreviewDriven = $row['driven'];
	    $activePreviewDrivenPct = $row['drivenPercent'];
	    $activePreviewClinched = $row['clinched'];
	    $activePreviewClinchedPct = $row['clinchedPercent'];
	    $res->free();

            echo "<tr onClick=\"window.open('/shields/clinched.php?u={$user}')\">";
	    echo "<td>Routes Driven</td>";
	    echo "<td>".$activeDriven." of " . $activeRoutes . " (" . $activeDrivenPct . "%) Rank: TBD</td>";
	    echo "<td>".$activePreviewDriven." of " . $activePreviewRoutes . " (" . $activePreviewDrivenPct . "%) Rank: TBD</td>";
	    echo "</tr>";

            echo "<tr onClick=\"window.open('/shields/clinched.php?u={$user}')\">";
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
        $sql_command = "SELECT rg.country, rg.code, rg.name, co.activeMileage AS clinchedActiveMileage, o.activeMileage AS totalActiveMileage, co.activePreviewMileage AS clinchedActivePreviewMileage, o.activePreviewMileage AS totalActivePreviewMileage FROM overallMileageByRegion AS o INNER JOIN clinchedOverallMileageByRegion AS co ON co.region = o.region INNER JOIN regions AS rg ON rg.code = co.region WHERE co.traveler = '" . $user . "';";
        echo "<!-- SQL: " . $sql_command . "-->";
        $res = $db->query($sql_command);
        while ($row = $res->fetch_assoc()) {
            $activePercent = round($row['clinchedActiveMileage'] / $row['totalActiveMileage'] * 100.0, 2);
	    $activePercent = sprintf('%0.2f', $activePercent);
            $activePreviewPercent = round($row['clinchedActivePreviewMileage'] / $row['totalActivePreviewMileage'] * 100.0, 2);
	    $activePreviewPercent = sprintf('%0.2f', $activePreviewPercent);
            echo "<tr onClick=\"window.document.location='/user/region.php?u=" . $user . "&rg=" . $row['code'] . "'\"><td>" . $row['country'] . "</td><td>" . $row['name'] . "</td><td>" . sprintf('%0.2f', $row['clinchedActiveMileage']) . "</td><td>" . sprintf('%0.2f', $row['totalActiveMileage']) . "</td><td>" . $activePercent . "%</td><td>" . sprintf('%0.2f', $row['clinchedActivePreviewMileage']) . "</td><td>" . sprintf('%0.2f', $row['totalActivePreviewMileage']) . "</td><td>" . $activePreviewPercent . "%</td><td class='link'><a href=\"/hbtest/mapview.php?u=" . $user . "&rg=" . $row['code'] . "\">Map</a></td><td class='link'><a href='/devel/hb.php?rg={$row['code']}'>HB</a></td></tr>";
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
        $sql_command = "SELECT sys.countryCode, sys.systemName, sys.level, sys.tier, sys.fullName, r.root, COALESCE(ROUND(SUM(cr.mileage), 2),0) AS clinchedMileage, COALESCE(ROUND(SUM(r.mileage), 2), 0) AS totalMileage, COALESCE(ROUND(SUM(cr.mileage) / SUM(r.mileage) * 100, 2), 0) AS percentage FROM systems AS sys INNER JOIN routes AS r ON r.systemName = sys.systemName LEFT JOIN clinchedRoutes AS cr ON cr.route = r.root AND cr.traveler = '" . $user . "' WHERE (sys.level = 'active' OR sys.level = 'preview') GROUP BY r.systemName";
        $sql_command .= ";";
        echo "<!-- SQL: " . $sql_command . "-->";
        $res = $db->query($sql_command);
        while ($row = $res->fetch_assoc()) {
	    if ($row['clinchedMileage'] == 0) continue;
            echo "<tr onClick=\"window.document.location='/user/system.php?u=" . $user . "&sys=" . $row['systemName'] . "'\" class=\"status-" . $row['level'] . "\">";
            echo "<td>" . $row['countryCode'] . "</td>";
            echo "<td>" . $row['systemName'] . "</td>";
            echo "<td>" . $row['fullName'] . "</td>";
            echo "<td>Tier " . $row['tier'] . "</td>";
            echo "<td>" . $row['level'] . "</td>";
            echo "<td>" . $row['clinchedMileage'] . "</td>";
            echo "<td>" . $row['totalMileage'] . "</td>";
            echo "<td>" . $row['percentage'] . "%</td>";
            echo "<td class='link'><a href=\"/hbtest/mapview.php?u={$user}&sys={$row['systemName']}\">Map</a></td>";
            echo "<td class='link'><a href='/devel/hb.php?sys={$row['systemName']}'>HB</a></td></tr>";
        }
        $res->free();
        ?>
        </tbody>
    </table>
</div>
</body>
