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
            $('td').filter(function() {
                return this.innerHTML.match(/^[0-9\s\.,%]+$/);
            }).css('text-align','right');
        }
    );
</script>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<div id="header">
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
    Scroll down to see statistical reports below the map
    <?php
        echo " -- <a href='/user/mapview.php?u={$tmuser}&sys={$system}";
        if ($region != "") {
            echo "&rg={$region}";
        }
        echo "'>View Larger Map</a>";
        echo '<input id="showMarkers" type="checkbox" name="Show Markers" onclick="showMarkersClicked()">&nbsp;Show Markers';
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
        <?php tm_echo_user_description(true); ?>
        <div id="map"></div>
        <table class="gratable" id="overallTable">
            <thead><tr><th colspan="2"><a href="#rankings">System Statistics for <?php echo "$systemName"; ?></a></th></tr></thead>
            <tbody>
            <?php
	    // get overall stats either for entire system or within
	    // our selected region
            if ($region == "") {
	        // overall mileage across all systems
                $system_mileage = tm_sum_column_where("systemMileageByRegion", "mileage", "systemName = '".$system."'");

		// clinched mileage across all systems
                $sql_command = <<<SQL
WITH TravelerMileage AS (
    SELECT
        csm.traveler,
        SUM(csm.mileage) AS clinchedMileage
    FROM 
        clinchedSystemMileageByRegion csm
    JOIN 
        listEntries le ON csm.traveler = le.traveler
    WHERE 
        csm.systemName = '$system'
    GROUP BY 
        csm.traveler
),
FilteredRanks AS (
    SELECT
        tm.traveler,
        tm.clinchedMileage,
        RANK() OVER (ORDER BY tm.clinchedMileage DESC, le.includeInRanks DESC) AS rankClinchedMileage
    FROM 
        TravelerMileage tm
    JOIN 
        listEntries le ON tm.traveler = le.traveler
    WHERE 
        le.includeInRanks = 1
),
RankedTravelers AS (
    SELECT
        tm.traveler,
        tm.clinchedMileage,
        le.includeInRanks,
        le.description,
        COALESCE(fr.rankClinchedMileage, -1) AS rankClinchedMileage
    FROM 
        TravelerMileage tm
    LEFT JOIN 
        FilteredRanks fr ON tm.traveler = fr.traveler
    JOIN 
        listEntries le ON tm.traveler = le.traveler
)
SELECT 
    traveler,
    clinchedMileage,
    includeInRanks,
    description,
    rankClinchedMileage
FROM 
    RankedTravelers
ORDER BY 
    clinchedMileage DESC,
    includeInRanks DESC;
SQL;
            } 
	    else {
	        // mileage for one system in one region
                $system_mileage = tm_sum_column_where("systemMileageByRegion", "mileage", "systemName = '".$system."' AND region = '".$region."'");

		// clinched mileage across all systems
                $sql_command = <<<SQL
WITH TravelerMileage AS (
    SELECT
        csm.traveler,
        csm.mileage AS clinchedMileage
    FROM 
        clinchedSystemMileageByRegion csm
    JOIN 
        listEntries le ON csm.traveler = le.traveler
    WHERE 
        csm.systemName = '$system'
        AND csm.region = '$region'
),
FilteredRanks AS (
    SELECT
        tm.traveler,
        tm.clinchedMileage,
        RANK() OVER (ORDER BY tm.clinchedMileage DESC, le.includeInRanks DESC) AS rankClinchedMileage
    FROM 
        TravelerMileage tm
    JOIN 
        listEntries le ON tm.traveler = le.traveler
    WHERE 
        le.includeInRanks = 1
),
RankedTravelers AS (
    SELECT
        tm.traveler,
        tm.clinchedMileage,
        le.includeInRanks,
        le.description,
        COALESCE(fr.rankClinchedMileage, -1) AS rankClinchedMileage
    FROM 
        TravelerMileage tm
    LEFT JOIN 
        FilteredRanks fr ON tm.traveler = fr.traveler
    JOIN 
        listEntries le ON tm.traveler = le.traveler
)
SELECT 
    traveler,
    clinchedMileage,
    includeInRanks,
    description,
    rankClinchedMileage
FROM 
    RankedTravelers
ORDER BY 
    clinchedMileage DESC,
    includeInRanks DESC;
SQL;
            }
            $res = tmdb_query($sql_command);
            $row = tm_fetch_user_row_with_rank($res, 'clinchedMileage');
            $percentage = 0;
            if ($system_mileage != 0) {
                $percentage = $row['clinchedMileage'] / $system_mileage * 100;
            }
            $link = "window.open('/shields/clinched.php?u=" . $tmuser . "&amp;sys=" . $system . "')";
            if ($row['traveler'] != "" && $row['includeInRanks'] == "1") {
                $rank = "Rank: ".$row['rank'];
            } else {
                $rank = "";
            }
            $style = 'style="background-color: '.tm_color_for_amount_traveled($row['clinchedMileage'],$system_mileage).';"';
            echo "<tr><td>Distance Traveled</td><td ".$style.">".tm_convert_distance($row['clinchedMileage'])." of ".tm_convert_distance($system_mileage)." ".$tmunits." (".sprintf('%0.2f',$percentage)."%) ".$rank."</td></tr>";

	    // build arrays that will form the contents of the travelers
	    // by region stats for active systems
	    $TravelerInfo = array();
	    $res->data_seek(0);
	    while ($row = $res->fetch_assoc()) {
		$TravelerInfo[$row['traveler']]['mileage'] = $row['clinchedMileage'];
            }
            $res->free();

            //Second, fetch routes clinched/driven
            if ($region == "") {
                $totalRoutes = tm_count_rows("connectedRoutes", "WHERE systemName='".$system."'");
                $sql_command = <<<SQL
WITH TravelerMileage AS (
    SELECT
        ccr.traveler,
        SUM(ccr.clinched) AS clinched
    FROM 
        connectedRoutes AS cr
    LEFT JOIN 
        clinchedConnectedRoutes AS ccr ON cr.firstRoot = ccr.route
    JOIN 
        listEntries le ON ccr.traveler = le.traveler
    WHERE 
        cr.systemName = '$system'
    GROUP BY 
        ccr.traveler
),
FilteredRanks AS (
    SELECT
        tm.traveler,
        tm.clinched,
        RANK() OVER (ORDER BY tm.clinched DESC, le.includeInRanks DESC) AS rankClinched
    FROM 
        TravelerMileage tm
    JOIN 
        listEntries le ON tm.traveler = le.traveler
    WHERE 
        le.includeInRanks = 1
),
RankedTravelers AS (
    SELECT
        tm.traveler,
        tm.clinched,
        le.includeInRanks,
        le.description,
        COALESCE(fr.rankClinched, -1) AS rankClinched
    FROM 
        TravelerMileage tm
    LEFT JOIN 
        FilteredRanks fr ON tm.traveler = fr.traveler
    JOIN 
        listEntries le ON tm.traveler = le.traveler
)
SELECT 
    traveler,
    clinched,
    includeInRanks,
    description,
    rankClinched
FROM 
    RankedTravelers
ORDER BY 
    clinched DESC,
    includeInRanks DESC;
SQL;
		$res = tmdb_query($sql_command);
		$row = tm_fetch_user_row_with_rank($res, 'clinched');
		if ($row['traveler'] != "") {
		    $clinched = $row['clinched'];
		}
		else {
		    $clinched = 0;
		}
		if ($row['includeInRanks'] == "1") {
		    $clinchedRank = "Rank: ".$row['rank'];
		}
		else {
		    $clinchedRank = "";
		}
		$res->free();
                $sql_command = <<<SQL
WITH TravelerMileage AS (
    SELECT
        ccr.traveler,
        COUNT(ccr.route) AS driven,
        SUM(ccr.clinched) AS clinched
    FROM 
        connectedRoutes AS cr
    LEFT JOIN 
        clinchedConnectedRoutes AS ccr ON cr.firstRoot = ccr.route
    JOIN 
        listEntries le ON ccr.traveler = le.traveler
    WHERE 
        cr.systemName = '$system'
    GROUP BY 
        ccr.traveler
),
FilteredRanks AS (
    SELECT
        tm.traveler,
        tm.driven,
        RANK() OVER (ORDER BY tm.driven DESC, le.includeInRanks DESC) AS rankDriven
    FROM 
        TravelerMileage tm
    JOIN 
        listEntries le ON tm.traveler = le.traveler
    WHERE 
        le.includeInRanks = 1
),
RankedTravelers AS (
    SELECT
        tm.traveler,
        tm.driven,
        tm.clinched,
        le.includeInRanks,
        le.description,
        COALESCE(fr.rankDriven, -1) AS rankDriven
    FROM 
        TravelerMileage tm
    LEFT JOIN 
        FilteredRanks fr ON tm.traveler = fr.traveler
    JOIN 
        listEntries le ON tm.traveler = le.traveler
)
SELECT 
    traveler,
    driven,
    clinched,
    includeInRanks,
    description,
    rankDriven
FROM 
    RankedTravelers
ORDER BY 
    driven DESC,
    includeInRanks DESC;
SQL;
		$res = tmdb_query($sql_command);
		$row = tm_fetch_user_row_with_rank($res, 'driven');
		if ($row['traveler'] != "") {
		    $driven = $row['driven'];
		}
		else {
		    $driven = 0;
		}
		if ($row['includeInRanks'] == "1") {
		    $drivenRank = "Rank: ".$row['rank'];
		}
		else {
		    $drivenRank = "";
		}
            } else {
                $totalRoutes = tm_count_rows("routes", "WHERE systemName='".$system."' AND region='".$region."'");
                $sql_command = <<<SQL
WITH TravelerMileage AS (
    SELECT
        ccr.traveler,
        SUM(ccr.clinched) AS clinched
    FROM 
        routes AS cr
    LEFT JOIN 
        clinchedRoutes AS ccr ON cr.root = ccr.route
    JOIN 
        listEntries le ON ccr.traveler = le.traveler
    WHERE 
        cr.region = '$region' 
        AND cr.systemName = '$system'
    GROUP BY 
        ccr.traveler
),
FilteredRanks AS (
    SELECT
        tm.traveler,
        tm.clinched,
        RANK() OVER (ORDER BY tm.clinched DESC, le.includeInRanks DESC) AS rankClinched
    FROM 
        TravelerMileage tm
    JOIN 
        listEntries le ON tm.traveler = le.traveler
    WHERE 
        le.includeInRanks = 1
),
RankedTravelers AS (
    SELECT
        tm.traveler,
        tm.clinched,
        le.includeInRanks,
        le.description,
        COALESCE(fr.rankClinched, -1) AS rankClinched
    FROM 
        TravelerMileage tm
    LEFT JOIN 
        FilteredRanks fr ON tm.traveler = fr.traveler
    JOIN 
        listEntries le ON tm.traveler = le.traveler
)
SELECT 
    traveler,
    clinched,
    includeInRanks,
    description,
    rankClinched
FROM 
    RankedTravelers
ORDER BY 
    clinched DESC,
    includeInRanks DESC;
SQL;
		$res = tmdb_query($sql_command);
		$row = tm_fetch_user_row_with_rank($res, 'clinched');
		if ($row['traveler'] != "") {
		    $clinched = $row['clinched'];
		}
		else {
		    $clinched = 0;
		}
		if ($row['includeInRanks'] == "1") {
		    $clinchedRank = "Rank: ".$row['rank'];
		}
		else {
		    $clinchedRank = "";
		}
		$res->free();
                $sql_command = <<<SQL
WITH TravelerMileage AS (
    SELECT
        ccr.traveler,
        COUNT(ccr.route) AS driven,
        SUM(ccr.clinched) AS clinched
    FROM 
        routes AS cr
    LEFT JOIN 
        clinchedRoutes AS ccr ON cr.root = ccr.route
    JOIN 
        listEntries le ON ccr.traveler = le.traveler
    WHERE 
        cr.region = '$region' 
        AND cr.systemName = '$system'
    GROUP BY 
        ccr.traveler
),
FilteredRanks AS (
    SELECT
        tm.traveler,
        tm.driven,
        RANK() OVER (ORDER BY tm.driven DESC, le.includeInRanks DESC) AS rankDriven
    FROM 
        TravelerMileage tm
    JOIN 
        listEntries le ON tm.traveler = le.traveler
    WHERE 
        le.includeInRanks = 1
),
RankedTravelers AS (
    SELECT
        tm.traveler,
        tm.driven,
        tm.clinched,
        le.includeInRanks,
	le.description,
        COALESCE(fr.rankDriven, -1) AS rankDriven
    FROM 
        TravelerMileage tm
    LEFT JOIN 
        FilteredRanks fr ON tm.traveler = fr.traveler
    JOIN 
        listEntries le ON tm.traveler = le.traveler
)
SELECT 
    traveler,
    driven,
    clinched,
    includeInRanks,
    description,
    rankDriven
FROM 
    RankedTravelers
ORDER BY 
    driven DESC,
    includeInRanks DESC;
SQL;
		$res = tmdb_query($sql_command);
		$row = tm_fetch_user_row_with_rank($res, 'driven');
		if ($row['traveler'] != "") {
		    $driven = $row['driven'];
		}
		else {
		    $driven = 0;
		}
		if ($row['includeInRanks'] == "1") {
		    $drivenRank = "Rank: ".$row['rank'];
		}
		else {
		    $drivenRank = "";
		}
            }
	    // add to the table of travelers by region stats
	    $res->data_seek(0);
	    while ($row = $res->fetch_assoc()) {
		$TravelerInfo[$row['traveler']]['driven'] = $row['driven'];
		$TravelerInfo[$row['traveler']]['clinched'] = $row['clinched'];
		$TravelerInfo[$row['traveler']]['includeInRanks'] = $row['includeInRanks'];
		$TravelerInfo[$row['traveler']]['description'] = $row['description'];
            }
	    $res->free();

	    $style = 'style="background-color: '.tm_color_for_amount_traveled($driven,$totalRoutes).';"';
            echo "<tr onClick=\"" . $link . "\"><td>Routes Traveled</td><td ".$style.">" . $driven   . " of " . $totalRoutes . " (" . round($driven   / $totalRoutes * 100, 2) . "%) {$drivenRank}</td></tr>\n";
	    $style = 'style="background-color: '.tm_color_for_amount_traveled($clinched,$totalRoutes).';"';
	    echo "<tr onClick=\"" . $link . "\"><td>Routes Clinched</td><td ".$style.">" . $clinched . " of " . $totalRoutes . " (" . round($clinched / $totalRoutes * 100, 2) . "%) {$clinchedRank}</td></tr>\n";
            ?>
            </tbody>
        </table>
        <?php
        if($region == "") {
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
	    if ($res->num_rows > 1) {
              echo <<<HTML
                <table class="sortable gratable" id="regionsTable">
                    <caption>TIP: Click on a column head to sort.</caption>
                    <thead>
                    <tr><th colspan="4">Statistics by Region</th></tr>
                    <tr>
                        <th>Region</th>
                        <th>Clinched ({$tmunits})</th>
                        <th>Total ({$tmunits})</th>
                        <th>%</th>
                    </tr>
                    </thead>
                    <tbody>
HTML;
              while ($row = $res->fetch_assoc()) {
		$clinched = tm_convert_distance($row['clinchedMileage']);
		$total = tm_convert_distance($row['totalMileage']);
	    	$style = 'style="background-color: '.tm_color_for_amount_traveled($row['clinchedMileage'],$row['totalMileage']).';"';
		
                echo <<<HTML
                <tr onclick='window.open("/user/system.php?u={$tmuser}&sys={$system}&rg={$row['region']}")' {$style}>
                    <td>{$row['region']}</td>
                    <td>{$clinched}</td>
                    <td>{$total}</td>
                    <td data-sort="{$row['percentage']}">{$row['percentage']}%</td>
                </tr>
HTML;
            }
            echo "</tbody></table>";
	  }
          $res->free();
        }
        ?>
        <table class="sortable gratable" id="routeTable">
	    <?php
	    if ($region != "") {
		echo <<<HTML
	    <caption>TIP: Click on a column head to sort.</caption>
HTML;
	    }
	    ?>
            <thead>
            <tr>
                <th colspan="8">Statistics by Route</th>
            </tr>
            <tr>
                <th class="no-sort">Route</th>
                <th>#</th>
                <th class="no-sort">Banner</th>
		<?php if ($region == "") {
		    echo "<th class=\"no-sort\">Abbrev</th>";
		}
		?>
                <th class="no-sort">Section</th>
                <th>Clinched (<?php tm_echo_units(); ?>)</th>
                <th>Total (<?php tm_echo_units(); ?>)</th>
                <th>%</th>
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
                    $link = "window.open('/user/mapview.php?u=" . $tmuser . "&amp;cr=" . $row['root'] . "')";
                } else {
                    $link = "window.open('/hb/showroute.php?u=" . $tmuser . "&amp;r=" . $row['root'] . "')";
                }

	    	$style = 'style="background-color: '.tm_color_for_amount_traveled($row['clinchedMileage'],$row['totalMileage']).';"';
                echo "<tr onClick=\"" . $link . "\" ".$style.">";
                echo "<td>" . $row['route'] . "</td>";
                echo "<td width='0'>" . $row['routeNum'] . "</td>";
                echo "<td>" . $row['banner'] . "</td>";
		if ($region == "") {
                    echo "<td>" . ($row['abbrev'] ?? '') . "</td>";
		}
                echo "<td>" . $row['city'] . "</td>";
                echo "<td>" . tm_convert_distance($row['clinchedMileage']) . "</td>";
                echo "<td>" . tm_convert_distance($row['totalMileage']) . "</td>";
                echo "<td data-sort=\"".$row['percentage']."\">" . $row['percentage'] . "%</td></tr>\n";
            }
            $res->free();
            ?>
            </tbody>
        </table>

    <a name="rankings"></a>
    <table class="sortable gratable" id="systemTravelersTable" style="width: auto;">
        <thead>
	    <tr><th colspan="6">Travelers on <?php echo "$systemName"; ?></th></tr>
            <tr><th>Rank</th><th>Traveler</th><th>Distance Traveled (<?php tm_echo_units(); ?>)</th><th>%</th><th>Traveled Routes</th><th>Clinched Routes</th></tr>
	  <tr style=><td></td><td>TOTAL CLINCHABLE</td><td><?php echo tm_convert_distance($system_mileage); ?></td><td>100%</td><td><?php echo "$totalRoutes"; ?></td><td><?php echo "$totalRoutes"; ?></td></tr>
        </thead>
    <tbody>
	  <?php
	  $skipped = 0;
	  $prev_mileage = 0;
	  $pre_rank = 1;
	  $tie_rank = 1;
	  foreach ($TravelerInfo as $traveler => $stats) {
	      if ($traveler == "") {
                  continue;  // this happens, but how?!
              }
              if ($traveler == $tmuser) {
                  $highlight = 'user-highlight';
              }
              else {
                 $highlight = '';
              }
              $tie_rank = ($prev_mileage == $stats['mileage']) ? $tie_rank : $pre_rank;
              if ($stats['includeInRanks'] == "1") {
                  $show_rank = $tie_rank - $skipped;
                  $ranktd = "<td>".$show_rank."</td>";
              }
              else {
                  $ranktd = "<td title=\"user ".$traveler." specified as unranked\">&nbsp;</td>";
                  $skipped++;
              }
              $travttip = "";
              if ($stats['description']) {
                   $travttip = " title=\"".$stats['description']."\"";
              }
              $mileageStyle = 'style="background-color: '.tm_color_for_amount_traveled($stats['mileage'],$system_mileage).';"';
              $drivenStyle = 'style="background-color: '.tm_color_for_amount_traveled($stats['driven'],$totalRoutes).';"';
              $clinchedStyle = 'style="background-color: '.tm_color_for_amount_traveled($stats['clinched'],$totalRoutes).';"';
              echo "<tr class=\"".$highlight."\" onClick=\"window.document.location='?u=".$traveler."&sys=$system";
              if ($region != "") echo "&rg=$region";
              echo "'\">";
              echo $ranktd;
              echo "<td".$travttip.">".$traveler."</td>";
              echo "<td ".$mileageStyle.">".tm_convert_distance($stats['mileage'])."</td>";
              $pct = round($stats['mileage'] / $system_mileage * 100, 2);
              echo "<td ".$mileageStyle." data-sort=\"".$pct."\">".$pct."%</td>";
              echo "<td ".$drivenStyle.">".$stats['driven']."</td>";
              echo "<td ".$clinchedStyle.">".$stats['clinched']."</td></tr>\n";//*/
              $pre_rank += 1;
              $prev_mileage = $stats['mileage'];
          }
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
