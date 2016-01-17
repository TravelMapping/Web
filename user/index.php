<?php
include $_SERVER['DOCUMENT_ROOT']."/login.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	A basic user stats page. 
	URL Params:
		u - the user.
		db - the database being used. Use 'TravelMappingDev' for in-development systems.
		(u, [db])
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
            width: 100%;
            overflow: auto;
            padding: 20px;
        }

        #body h2 {
            margin: auto;
            text-align: center;
            padding: 10px;
        }
    </style>
    <!-- jQuery -->
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- TableSorter -->
    <script src="/lib/jquery.tablesorter.min.js"></script>
    <title>
        <?php
        echo "Traveler Stats for " . $user;

        // establish connection to db: mysql_ interface is deprecated, should learn new options
        $db = new mysqli("localhost", "travmap", "clinch", $dbname) or die("Failed to connect to database");

        # functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
        function startsWith($haystack, $needle)
        {
            // search backwards starting from haystack length characters from the end
            return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
        }

        function endsWith($haystack, $needle)
        {
            // search forward starting from end minus needle length characters
            return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
        }

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
    <div id="overall">
        <h2>Overall Stats</h2>
        <table class="gratable" style="width: 30%" id="tierTable">
            <tbody>
            <?php
            //First fetch overall mileage
            $sql_command = <<<SQL
SELECT
round(sum(o.mileage), 2) as totalMileage,
round(sum(coalesce(co.mileage, 0)), 2) as clinchedMileage,
round(sum(coalesce(co.mileage, 0)) / sum(o.mileage) * 100, 2) AS percentage
FROM overallMileageByRegion o
LEFT JOIN clinchedOverallMileageByRegion co ON co.region = o.region AND traveler = '$user'
SQL;

            echo "<!-- SQL:" . $sql_command . "-->";
            $res = $db->query($sql_command);
            $row = $res->fetch_assoc();
            $res->free();
            echo "<b><tr class='notclickable' style=\"background-color:#EEEEFF\"><td>Overall</td><td colspan=2>Miles Driven: " . $row['clinchedMileage'] . " mi (" . $row['percentage'] . "%)</td><td>Total: " . $row['totalMileage'] . " mi</td></tr></b>\n";

            //Second, fetch routes driven/clinched
            $sql_command = "SELECT COUNT(cr.route) AS total, COUNT(ccr.route) AS driven, SUM(ccr.clinched) AS clinched, ROUND(COUNT(ccr.route) / COUNT(cr.route) * 100,2) AS drivenPercent, ROUND(SUM(ccr.clinched) / COUNT(cr.route) * 100,2) AS clinchedPercent FROM connectedRoutes AS cr LEFT JOIN clinchedConnectedRoutes AS ccr ON cr.firstRoot = ccr.route AND traveler = '" . $user . "';";
            echo "<!-- SQL:" . $sql_command . "-->";
            $res = $db->query($sql_command);
            $row = $res->fetch_assoc();
            echo "<tr onClick=\"window.open('/shields/clinched.php?u={$user}')\"><td>Routes</td><td>Driven: " . $row['driven'] . " (" . $row['drivenPercent'] . "%)</td><td>Clinched: " . $row['clinched'] . " (" . $row['clinchedPercent'] . "%)</td><td>Total: " . $row['total'] . "</td></tr>\n";
            $res->free;

            ?>
            </tbody>
        </table>
    </div>
    <h2>Stats by Region</h2>
    <table class="gratable tablesorter" id="regionsTable">
        <thead>
        <tr>
            <th colspan="7">Clinched Mileage by Region:</th>
        </tr>
        <tr>
            <th class="sortable">Country</th>
            <th class="sortable">Region</th>
            <th class="sortable">Clinched Mileage</th>
            <th class="sortable">Overall Mileage</th>
            <th class="sortable">Percent Clinched</th>
            <th colspan="2">Map</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql_command = "SELECT rg.country, rg.code, rg.name, co.mileage AS clinchedMileage, o.mileage AS totalMileage FROM overallMileageByRegion AS o INNER JOIN clinchedOverallMileageByRegion AS co ON co.region = o.region INNER JOIN regions AS rg ON rg.code = co.region WHERE co.traveler = '" . $user . "';";
        echo "<!-- SQL: " . $sql_command . "-->";
        $res = $db->query($sql_command);
        while ($row = $res->fetch_assoc()) {
            $percent = round($row['clinchedMileage'] / $row['totalMileage'] * 100.0, 2);
            echo "<tr onClick=\"window.document.location='/user/region.php?u=" . $user . "&rg=" . $row['code'] . "'\"><td>" . $row['country'] . "</td><td>" . $row['name'] . "</td><td>" . $row['clinchedMileage'] . "</td><td>" . $row['totalMileage'] . "</td><td>" . $percent . "%</td><td class='link'><a href=\"/hbtest/mapview.php?u=" . $user . "&rg=" . $row['code'] . "\">Map</a></td><td class='link'><a href='/devel/hb.php?rg={$row['code']}'>HB</a></td></tr>";
        }
        $res->free();
        ?>
        </tbody>
    </table>
    <h2>Stats by System</h2>
    <table class="gratable tablesorter" id="systemsTable">
        <thead>
        <tr>
            <th colspan="10">Clinched Mileage by System</th>
        </tr>
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
        $sql_command = "SELECT sys.countryCode, sys.systemName, sys.level, sys.tier, sys.fullName, r.root, COALESCE(ROUND(SUM(cr.mileage), 2),0) AS clinchedMileage, COALESCE(ROUND(SUM(r.mileage), 2), 0) AS totalMileage, COALESCE(ROUND(SUM(cr.mileage) / SUM(r.mileage) * 100, 2), 0) AS percentage FROM systems AS sys INNER JOIN routes AS r ON r.systemName = sys.systemName LEFT JOIN clinchedRoutes AS cr ON cr.route = r.root AND cr.traveler = '" . $user . "' GROUP BY r.systemName";
        $sql_command .= ";";
        echo "<!-- SQL: " . $sql_command . "-->";
        $res = $db->query($sql_command);
        while ($row = $res->fetch_assoc()) {
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