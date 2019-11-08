<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- /devel/datacheck.php main Datacheck page -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
<link rel="shortcut icon" type="image/png" href="/favicon.png">
<style type="text/css">
</style>
<?php

  # function to generate a table with FP or not
  function writeTable($db, $fpVal, $joins, $region, $system) {
      global $tmsqldebug;

      // select all errors in the DB with the given $fpVal
      $sql_command = "select datacheckErrors.* from datacheckErrors ".$joins." falsePositive=".$fpVal;
      // check for query string parameter for system and region filters
      if ($system != "") {
          $sql_command .= " AND routes.systemName = '" .$system. "'";
      }
      if ($region != "") {
          $sql_command .= " AND routes.region = '" .$region. "'";
      }
      $sql_command .= ";";
      if ($tmsqldebug) {
          echo "<!-- SQL: ".$sql_command." -->\n";
      }
      $res = $db->query($sql_command);

      while ($row = $res->fetch_assoc()) {
        echo "<tr><td><a href=\"../hb?r=".$row['route']."\">".$row['route']."</a></td><td>";
        if (strcmp($row['label1'],"") != 0) {
          echo $row['label1'];
        }
        if (strcmp($row['label2'],"") != 0) {
          echo ",".$row['label2'];
        }
        if (strcmp($row['label3'],"") != 0) {
          echo ",".$row['label3'];
        }
	if ((strcmp($row['code'],"VISIBLE_DISTANCE") == 0) ||
	  (strcmp($row['code'],"LONG_DISTANCE") == 0) ||
	  (strcmp($row['code'],"SHARP_ANGLE") == 0)) {
	  echo "</td><td>".$row['code']."</td><td>";
	}
        else {
	  echo "</td><td style=\"color: red\">".$row['code']."</td><td>";

	}
        if (strcmp($row['value'],"") != 0) {
          echo $row['value'];
        }
	if ((strcmp($row['code'],"DUPLICATE_LABEL") == 0) ||
	  (strcmp($row['code'],"HIDDEN_TERMINUS") == 0) ||
	  (strcmp($row['code'],"LABEL_INVALID_CHAR") == 0) ||
	  (strcmp($row['code'],"LABEL_SLASHES") == 0) ||
	  (strcmp($row['code'],"LONG_UNDERSCORE") == 0) ||
	  (strcmp($row['code'],"MALFORMED_URL") == 0) ||
	  (strcmp($row['code'],"NONTERMINAL_UNDERSCORE") == 0)) {
          echo "</td><td style=\"color: gray\"><i>This is always a true error and cannot be marked false positive.</i></td></tr>\n";
	}
        else {
          echo "</td><td><tt>".$row['route'].";".$row['label1'].";".$row['label2'].";".$row['label3'].";".$row['code'].";".$row['value']."</tt></td></tr>\n";
        }
      }
      $res->free();
  }
?>

<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<?php
    // check for region and/or system parameters
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
    }
?>
<title>Travel Mapping Highway Data Datacheck Errors</title>
</head>

<body onload="populate_dbarrays()">
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<?php
$showmarked = false;
if (array_key_exists("showmarked", $_GET)) {
  $showmarked = true;
}
?>

<p class="heading">Travel Mapping Highway Data Datacheck Errors</p>

<?php
    echo "<form id=\"selectHighways\" name=\"HighwaySearch\" action=\"/devel/datacheck.php\">";
    echo "<label for=\"sys\">Filter errors by...  System: </label>";
    tm_system_select(FALSE);
    echo "<label for=\"rg\"> Region: </label>";
    tm_region_select(FALSE);
    echo "<input type=\"checkbox\" name=\"showmarked\"";
    if ($showmarked) {
       echo " checked";
    }
    echo " />";
    echo "<label for=\"showmarked\"> Show Marked FPs </label>";
    echo "<input type=\"submit\" value=\"Apply Filter\" /></form>";
?>
<p class="info">Quick links: <a href="#active">[Errors in Active Systems]</a><a href="#preview">[Errors in Preview Systems]</a><a href="#indev">[Errors in In-Dev Systems]</a>
<?php
if ($showmarked) {
  echo '<a href="#marked">[Errors Marked as FPs]</a>.';
}
//else {
//  echo '<a href="?showmarked">[Reload with Marked FPs Included]</a>.';
//}
?>
</p>

<p class="info">See also the <a href="../logs/unmatchedfps.log">[Log
of Unmatched FPs from datacheckfps.csv]</a> and
the <a href="../logs/unprocessedwpts.log">[Log of Unprocessed WPTs in
the Repository]</a>.  Cleaning these up are low priority tasks for the
project.  Some of these are likely fixable from the information in
the <a href="../logs/nearmatchfps.log">[Log of Near-Match FPs from
datacheckfps.csv]</a>.</p>

<div id="errors">

  <p class="subheading">Errors in Active Systems (not FPs)</p>

  <p class="text"><a name="active"></a>These errors should be
  corrected, or reported as false positives by adding the entry from
  the last column
  to <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the
  datacheck FP list</a> as soon as possible.  Ideally, this list
  should always be empty.</p>


  <table border="1" style="background-color:#fcc"><tr><th>Route</th><th>Waypoints</th><th>Error</th><th>Info</th><th>FP Entry to Submit</th></tr>
    <?php
      writeTable($tmdb, "0", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.level=\"active\" and ", $region, $system);
    ?>
  </table>

  <p class="subheading">Errors in Preview Systems (not FPs)</p>

  <p class="text"><a name="preview"></a>These errors should be
  corrected, or reported as false positives by adding the entry from
  the last column
  to <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the
  datacheck FP list</a> during the final review process to promote a
  system from 'preview' to 'active'.  A system should have no entries
  here before activation.  Errors shown in <span style="color:
  red">red</span> should be fixed as soon as possible, while others
  can wait until final preparation for system activation.</p>

  <table border="1" style="background-color:#ccf"><tr><th>Route</th><th>Waypoints</th><th>Error</th><th>Info</th><th>FP Entry to Submit</th></tr>
    <?php
      writeTable($tmdb, "0", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.level=\"preview\" and ", $region, $system);
    ?>
  </table>

  <p class="subheading">Errors in In-Development Systems (not FPs)</p>

  <p class="text"><a name="indev"></a>These errors should be
  corrected, or reported as false positives by adding the entry from
  the last column
  to <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the
  datacheck FP list</a> before the system is promoted from 'devel' to
  'preview'.  Note: less severe errors, such as distance and angle
  errors can be left until final preparation for promotion to
  'active'.</p>

  <table border="1" style="background-color:#cfc"><tr><th>Route</th><th>Waypoints</th><th>Error</th><th>Info</th><th>FP Entry to Submit</th></tr>
    <?php
      writeTable($tmdb, "0", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.level=\"devel\" and ", $region, $system);
    ?>
  </table>

<?php
if ($showmarked) {
?>
  <p class="subheading">Errors Marked as FPs ("Crossed Off")</p>

  <p class="text"><a name="marked"></a>These have been marked as FPs
  in <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the
  datacheck FP list</a> and can normally be safely ignored.  However,
  if any of these are discovered to be true errors, they should be
  removed from the list and fixed in the highway data.</p>

  <table border="1" style="background-color:#ccc;font-size:60%"><tr><th>Route</th><th>Waypoints</th><th>Error</th><th>Info</th><th>FP Entry Matched</th></tr>
    <?php
      writeTable($tmdb, "1", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where ", $region, $system);
    ?>
  </table>
<?php
}
?>
</div>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
