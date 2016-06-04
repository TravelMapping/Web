<?php
include $_SERVER['DOCUMENT_ROOT']."/login.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	A rankings page.
	URL Params:
		u - the user, to show highlighting on page.
		db - the database being used. Use 'TravelMappingDev' for in-development systems. 
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css"/>
<!-- jQuery -->
<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<!-- TableSorter -->
<script src="/lib/jquery.tablesorter.min.js"></script>
<title>Traveler Statistics</title>
    <style type="text/css">
        #rankingstable {
            width: 33%;
            float: left;
            margin: auto;
        }
    </style>
</head>
<body>
<script type="text/javascript">
    $(document).ready(function () {
        $(".rankingstable").tablesorter({
            sortList: [[0,0]],
            headers: {0: {sorter: false}}
        });
        $('td').filter(function() {
            return this.innerHTML.match(/^[0-9\s\.,%]+$/);
        }).css('text-align','right');
    });
</script>
	<h1>Traveler Statistics</h1>
	<table style="margin: auto">
        <tr><td>
	<table class="gratable tablesorter" id="rankingstable">
		<thead>
			<tr><th colspan="5">Traveler Mileage in Active Systems</th></tr>
			<tr><th class="sortable">Rank</th><th class="sortable">Username</th><th class="sortable">Miles Traveled</th><th class="sortable">%</th></tr>
		</thead>
		<tbody>
			<?php
            $dbname = "TravelMappingTest";
            $db = new mysqli("localhost","travmap","clinch",$dbname) or die("Failed to connect to database");
            $sql = "SELECT sum(o.activeMileage) as totalMileage FROM overallMileageByRegion o";
            $totalMileage = $db->query($sql)->fetch_assoc()['totalMileage'];
            $sql = <<<SQL
            SELECT
              traveler,
              ROUND(SUM(COALESCE(co.activeMileage, 0)), 2) AS clinchedMileage,
              round(SUM(coalesce(co.activeMileage, 0)) / $totalMileage * 100, 2) AS percentage
            FROM clinchedOverallMileageByRegion co
            GROUP BY co.traveler ORDER BY clinchedMileage DESC;
SQL;
            $res = $db->query($sql);
            $rank = 1;
            while ($row = $res->fetch_assoc()) {
                if($row['traveler'] == $user) {
                    $highlight = 'user-highlight';
                } else {
                    $highlight = '';
                }
                echo <<<HTML
                <tr class="$highlight" onClick="window.document.location='/user?u={$row['traveler']}';">
                <td>{$rank}</td><td>{$row['traveler']}</td><td>{$row['clinchedMileage']}</td><td>{$row['percentage']}%</td>
                </tr>
HTML;
                $rank++;
            }

			?>
		</tbody>
	</table>
	</td>
	<td>
	<table class="gratable tablesorter" id="rankingstable">
		<thead>
			<tr><th colspan="5">Traveler Mileage in Active and Preview Systems</th></tr>
			<tr><th class="sortable">Rank</th><th class="sortable">Username</th><th class="sortable">Miles Traveled</th><th class="sortable">%</th></tr>
		</thead>
		<tbody>
			<?php
            $dbname = "TravelMappingTest";
            $db = new mysqli("localhost","travmap","clinch",$dbname) or die("Failed to connect to database");
            $sql = "SELECT sum(o.activePreviewMileage) as totalMileage FROM overallMileageByRegion o";
            $totalMileage = $db->query($sql)->fetch_assoc()['totalMileage'];
            $sql = <<<SQL
            SELECT
              traveler,
              ROUND(SUM(COALESCE(co.activePreviewMileage, 0)), 2) AS clinchedMileage,
              round(SUM(coalesce(co.activePreviewMileage, 0)) / $totalMileage * 100, 2) AS percentage
            FROM clinchedOverallMileageByRegion co
            GROUP BY co.traveler ORDER BY clinchedMileage DESC;
SQL;
            $res = $db->query($sql);
            $rank = 1;
            while ($row = $res->fetch_assoc()) {
                if($row['traveler'] == $user) {
                    $highlight = 'user-highlight';
                } else {
                    $highlight = '';
                }
                echo <<<HTML
                <tr class="$highlight" onClick="window.document.location='/user?u={$row['traveler']}';">
                <td>{$rank}</td><td>{$row['traveler']}</td><td>{$row['clinchedMileage']}</td><td>{$row['percentage']}%</td>
                </tr>
HTML;
                $rank++;
            }

			?>
		</tbody>
	</table>
        </td>
	</tr>
	</table>
</body>
</html>