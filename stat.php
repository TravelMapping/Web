<?php require $_SERVER['DOCUMENT_ROOT'] . "/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	A rankings page.
	URL Params:
		u - the user, to show highlighting on page.
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css"/>
    <!-- jQuery -->
    <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- TableSorter -->
    <script src="/lib/jquery.tablesorter.min.js"></script>
    <title>Traveler Statistics</title>
    <style type="text/css">
        table.rankingstable {
            margin: auto;
            width: 90%;
        }

        #usertable {
            width: 100%;
            margin: auto;
            margin-bottom: 20px;
        }

        #usertable td {
            text-align: center;
        }
    </style>
    <?php require $_SERVER['DOCUMENT_ROOT'] . "/lib/tmphpfuncs.php" ?>
</head>
<body>
<script type="text/javascript">
    $(document).ready(function () {
        $(".rankingstable").tablesorter({
            sortList: [[0, 0]],
            headers: {0: {sorter: false}}
        });
        $('td').filter(function () {
            return this.innerHTML.match(/^[0-9\s\.,%]+$/);
        }).css('text-align', 'right');
    });
</script>
<?php require $_SERVER['DOCUMENT_ROOT'] . "/lib/tmheader.php"; ?>
<h1>Traveler Statistics</h1>
<table style="margin: auto">
    <?php
    $totalMileage = round(tm_sum_column("overallMileageByRegion", "activeMileage"));
    $totalPreviewMileage = round(tm_sum_column("overallMileageByRegion", "activePreviewMileage"));
    if ($tmuser === null) {
        echo "<form id=\"userselect\" action=\"\"><p>\n";
        echo "<label>Current User: </label>\n";
        tm_user_select();
        echo "<input type=\"submit\" value=\"Select User\" />\n";
        echo "</p></form>\n";
    }else{
        echo <<<HTML
            <tr><td colspan="2">
                <table class="gratable" id="usertable">
                    <thead>
                    <tr><th colspan="5">Current User</th></tr>
                    <tr><th>User</th><th>Active Mileage</th><th>Rank</th><th>Active + Preview Mileage</th><th>Rank</th></tr>
                    </thead><tbody>
HTML;
        echo "<tr onClick=\"window.document.location='/user?u={$tmuser}';\">";
        $sql = <<<SQL
                            SELECT
                              traveler,
                              ROUND(SUM(COALESCE(co.activeMileage, 0)), 2) AS clinchedMileage,
                              round(SUM(coalesce(co.activeMileage, 0)) / $totalMileage * 100, 2) AS percentage
                            FROM clinchedOverallMileageByRegion co
                            GROUP BY co.traveler ORDER BY clinchedMileage DESC;
SQL;
        $res = tmdb_query($sql);
        $row = tm_fetch_user_row_with_rank($res, 'clinchedMileage');
        echo "<td>{$row['traveler']}</td>";
        echo "<td>{$row['clinchedMileage']} / {$totalMileage} ({$row['percentage']}%)</td>";
        echo "<td>{$row['rank']}</td>";

        $sql = <<<SQL
                            SELECT
                              traveler,
                              ROUND(SUM(COALESCE(co.activePreviewMileage, 0)), 2) AS clinchedMileage,
                              round(SUM(coalesce(co.activePreviewMileage, 0)) / $totalPreviewMileage * 100, 2) AS percentage
                            FROM clinchedOverallMileageByRegion co
                            GROUP BY co.traveler ORDER BY clinchedMileage DESC;
SQL;
        $res = tmdb_query($sql);
        $row = tm_fetch_user_row_with_rank($res, 'clinchedMileage');
        echo "<td>{$row['clinchedMileage']} / {$totalPreviewMileage} ({$row['percentage']}%)</td>";
        echo "<td>{$row['rank']}</td>";
        echo <<<HTML
                    </tr>
                    </tbody></table>
            </td></tr>
HTML;
    }
    ?>
    <tr>
        <td>
            <table class="gratable tablesorter rankingstable">
                <thead>
                <tr>
                    <th colspan="5">Traveler Mileage in Active Systems</th>
                </tr>
                <tr>
                    <th class="sortable">Rank</th>
                    <th class="sortable">Username</th>
                    <th class="sortable">Miles Traveled</th>
                    <th class="sortable">%</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sql = <<<SQL
            SELECT
              traveler,
              ROUND(SUM(COALESCE(co.activeMileage, 0)), 2) AS clinchedMileage,
              round(SUM(coalesce(co.activeMileage, 0)) / $totalMileage * 100, 2) AS percentage
            FROM clinchedOverallMileageByRegion co
            GROUP BY co.traveler ORDER BY clinchedMileage DESC;
SQL;
                $res = tmdb_query($sql);
                $rank = 1;
                while ($row = $res->fetch_assoc()) {
                    if ($row['traveler'] == $tmuser) {
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
                $res->free();

                ?>
                </tbody>
            </table>
        </td>
        <td>
            <table class="gratable tablesorter rankingstable">
                <thead>
                <tr>
                    <th colspan="5">Traveler Mileage in Active and Preview Systems</th>
                </tr>
                <tr>
                    <th class="sortable">Rank</th>
                    <th class="sortable">Username</th>
                    <th class="sortable">Miles Traveled</th>
                    <th class="sortable">%</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sql = <<<SQL
            SELECT
              traveler,
              ROUND(SUM(COALESCE(co.activePreviewMileage, 0)), 2) AS clinchedMileage,
              round(SUM(coalesce(co.activePreviewMileage, 0)) / $totalPreviewMileage * 100, 2) AS percentage
            FROM clinchedOverallMileageByRegion co
            GROUP BY co.traveler ORDER BY clinchedMileage DESC;
SQL;
                $res = tmdb_query($sql);
                $rank = 1;
                while ($row = $res->fetch_assoc()) {
                    if ($row['traveler'] == $tmuser) {
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
                $res->free();

                ?>
                </tbody>
            </table>
        </td>
    </tr>
</table>
</body>
<?php require $_SERVER['DOCUMENT_ROOT'] . "/lib/tmfooter.php"; ?>
</html>