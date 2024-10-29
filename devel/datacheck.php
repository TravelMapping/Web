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
  function writeTable($db, $fpVal, $joins) {
      global $tmsqldebug;

      // select all errors in the DB with the given $fpVal
      $sql_command = "select datacheckErrors.* from datacheckErrors ".$joins." falsePositive=".$fpVal;
      // check for query string parameter for system and region filters
      if (array_key_exists("sys", $_GET)) {
          $sql_command .= " AND ".orClauseBuilder('sys', 'systemName', 'routes');
      }
      if (array_key_exists("rg", $_GET)) {
          $sql_command .= " AND ".orClauseBuilder('rg', 'region', 'routes');
      }
      if (array_key_exists("show", $_GET)) {
          $sql_command .= " AND ".orClauseBuilder('show', 'code', 'datacheckErrors');
      }
      if (array_key_exists("hide", $_GET)) {
          $sql_command .= " AND NOT ".orClauseBuilder('hide', 'code', 'datacheckErrors');
      }
      $sql_command .= ";";
      if ($tmsqldebug) {
          echo "<!-- SQL: ".$sql_command." -->\n";
      }
      $res = $db->query($sql_command);

      while ($row = $res->fetch_assoc()) {
        // find coords of error waypoint for HB link
        if (strcmp($row['label2'],"") != 0) {
          $label = $row['label2'];
        }
        else {
          $label = $row['label1'];
        }

        // write table row
	// Route
        $sql_command = "select latitude, longitude from waypoints where pointName = '".$label."' and root = '".$row['route']."';";
        $row2 = tmdb_query($sql_command)->fetch_assoc();
        echo "<tr><td><a href=\"../hb/showroute.php?r=".$row['route'];
	// successful lookup of waypoint label links to panned & zoomed map
	if ($row2 != NULL) {
	  echo "&lat=".$row2['latitude']."&lon=".$row2['longitude']."&zoom=";
	  if (strcmp($row['code'],"COMBINE_CON_ROUTES") == 0) {
	    echo "10";
	  }
	  else {
	    echo "17";
	  }
	}
	// the following errors link to a ConnectedRoute
	if ((strcmp($row['code'],"ABBREV_AS_CON_BANNER") == 0) ||
	  (strcmp($row['code'],"COMBINE_CON_ROUTES") == 0) ||
	  (strcmp($row['code'],"CON_ROUTE_MISMATCH") == 0) ||
	  (strcmp($row['code'],"CON_BANNER_MISMATCH") == 0) ||
	  (strcmp($row['code'],"DISCONNECTED_ROUTE") == 0)) {
	  echo "&cr";
	}
	echo "\">".$row['route']."</a></td><td>";

	// Waypoints
        if (strcmp($row['label1'],"") != 0) {
          echo $row['label1'];
        }
        if (strcmp($row['label2'],"") != 0) {
          echo ",".$row['label2'];
        }
        if (strcmp($row['label3'],"") != 0) {
          echo ",".$row['label3'];
        }

	// Error
	echo "</td><td><a style=\"color: ";
	if ((strcmp($row['code'],"ABBREV_AS_CHOP_BANNER") == 0) ||
	  (strcmp($row['code'],"ABBREV_AS_CON_BANNER") == 0) ||
	  (strcmp($row['code'],"ABBREV_NO_CITY") == 0) ||
	  (strcmp($row['code'],"BUS_WITH_I") == 0) ||
	  (strcmp($row['code'],"CON_BANNER_MISMATCH") == 0) ||
	  (strcmp($row['code'],"CON_ROUTE_MISMATCH") == 0) ||
	  (strcmp($row['code'],"INTERSTATE_NO_HYPHEN") == 0) ||
	  (strcmp($row['code'],"INVALID_FINAL_CHAR") == 0) ||
	  (strcmp($row['code'],"INVALID_FIRST_CHAR") == 0) ||
	  (strcmp($row['code'],"LABEL_LOOKS_HIDDEN") == 0) ||
	  (strcmp($row['code'],"LABEL_LOWERCASE") == 0) ||
	  (strcmp($row['code'],"LABEL_PARENS") == 0) ||
	  (strcmp($row['code'],"LABEL_SELFREF") == 0) ||
	  (strcmp($row['code'],"LABEL_SLASHES") == 0) ||
	  (strcmp($row['code'],"LABEL_UNDERSCORES") == 0) ||
	  (strcmp($row['code'],"LACKS_GENERIC") == 0) ||
	  (strcmp($row['code'],"LONG_SEGMENT") == 0) ||
	  (strcmp($row['code'],"LONG_UNDERSCORE") == 0) ||
	  (strcmp($row['code'],"LOWERCASE_SUFFIX") == 0) ||
	  (strcmp($row['code'],"NONTERMINAL_UNDERSCORE") == 0) ||
	  (strcmp($row['code'],"US_LETTER") == 0) ||
	  (strcmp($row['code'],"VISIBLE_HIDDEN_COLOC") == 0) ||
	  (strcmp($row['code'],"VISIBLE_DISTANCE") == 0)) {
	  echo "blue";
	}
        else {
	  echo "red";
	}
	echo "\" href=\"manual/syserr.php#".$row['code']."\">".$row['code']."</a></td><td>";

	// Info
        if (strcmp($row['value'],"") != 0) {
	  // LABEL_SELFREF links to syserr.php
	  if ((strcmp($row['code'],"LABEL_SELFREF") == 0)) {
            echo "<a href=\"manual/syserr.php#".$row['value']."\">".$row['value']."</a>";
	  }
	  // ABBREV_AS_CON_BANNER links to both system CSVs on GitHub
	  elseif ((strcmp($row['code'],"ABBREV_AS_CON_BANNER") == 0)) {
	    $acb_info = explode(',', $row['value']);
            echo "<a href=\"https://github.com/TravelMapping/HighwayData/blob/master/hwy_data/_systems/".$acb_info[0].".csv#L".$acb_info[1]."\">".$acb_info[0].".csv#L".$acb_info[1]."</a><br>";
            echo "<a href=\"https://github.com/TravelMapping/HighwayData/blob/master/hwy_data/_systems/".$acb_info[0]."_con.csv#L".$acb_info[2]."\">".$acb_info[0]."_con.csv#L".$acb_info[2]."</a>";
	  }
	  // ABBREV_AS_CHOP_BANNER & ABBREV_NO_CITY link to chopped route CSVs on GitHub
	  elseif ((strcmp($row['code'],"ABBREV_NO_CITY") == 0) ||
	    (strcmp($row['code'],"ABBREV_AS_CHOP_BANNER") == 0)) {
            echo "<a href=\"https://github.com/TravelMapping/HighwayData/blob/master/hwy_data/_systems/".$row['value']."\">".$row['value']."</a>";
	  }
	  // COMBINE_CON_ROUTES links to 2nd ConnectedRoute
	  elseif (strcmp($row['code'],"COMBINE_CON_ROUTES") == 0) {
	    $r_at_l = explode('@', $row['value']);
	    $sql_command = "select latitude, longitude from waypoints where pointName = '".$r_at_l[1]."' and root = '".$row['route']."';";
	    $row2 = tmdb_query($sql_command)->fetch_assoc();
            echo "<a href=\"../hb/showroute.php?cr&r=".$r_at_l[0]."&lat=".$row2['latitude']."&lon=".$row2['longitude']."&zoom=10\">".$row['value']."</a>";
	  }
	  else {
            echo $row['value'];
	  }
        }

	// FP Entry to Submit
	// If not an error type for which FPs are
	// allowed, don't print an FP entry.
	// This list is in descending order by frequency, to reduce
	// the amount of conditional branching & unnecessary tests
	if (strcmp($row['code'],"VISIBLE_DISTANCE") &&
	  strcmp($row['code'],"SHARP_ANGLE") &&
	  strcmp($row['code'],"LABEL_SELFREF") &&
	  strcmp($row['code'],"LABEL_LOOKS_HIDDEN") &&
	  strcmp($row['code'],"DUPLICATE_COORDS") &&
	  strcmp($row['code'],"LONG_SEGMENT") &&
	  strcmp($row['code'],"VISIBLE_HIDDEN_COLOC") &&
	  strcmp($row['code'],"HIDDEN_JUNCTION") &&
	  strcmp($row['code'],"LACKS_GENERIC") &&
	  strcmp($row['code'],"COMBINE_CON_ROUTES") &&
	  strcmp($row['code'],"BUS_WITH_I") &&
	  strcmp($row['code'],"OUT_OF_BOUNDS")) {

          echo "</td><td style=\"color: gray\"><i>This is always a true error and cannot be marked false positive.</i></td></tr>\n";
	}
	elseif (strcmp($row['value'],"TRUE_ERROR") == 0) {
	  echo "</td><td></td></tr>\n";
	}
        else {
          echo "</td><td><tt>".$row['route'].";".$row['label1'].";".$row['label2'].";".$row['label3'].";".$row['code'].";".$row['value']."</tt></td></tr>\n";
        }
      }
      $res->free();
  }
?>

<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
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

<style>
active {background-color: #CCFFCC;}
preview {background-color: #FFFFCC;}
devel {background-color: #FFCCCC;}
</style>


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
<p class="info">Quick links: <a href="#active">[Errors in <active>Active</active> Systems]</a>
<a href="#preview">[Errors in <preview>Preview</preview> Systems]</a>
<a href="#indev">[Errors in <devel>In-Dev</devel> Systems]</a>
<a href="manual/syserr.php">[Manual]</a>
<?php
if ($showmarked) {
  echo '<a href="#marked">[Errors Marked as FPs]</a>.';
}
//else {
//  echo '<a href="?showmarked">[Reload with Marked FPs Included]</a>.';
//}
?>
</p>

<p class="info">See also the <a href="/logs/unmatchedfps.log">[Log
of Unmatched FPs from datacheckfps.csv]</a>.
Cleaning these up are low priority tasks for the
project.  Some of these are likely fixable from the information in
the <a href="/logs/nearmatchfps.log">[Log of Near-Match FPs from
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
      writeTable($tmdb, "0", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.level=\"active\" and ");
    ?>
  </table>

  <p class="subheading">Errors in Preview Systems (not FPs)</p>

  <p class="text"><a name="preview"></a>These errors should be
  corrected, or reported as false positives by adding the entry from
  the last column
  to <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the
  datacheck FP list</a> during the final review process to promote a highway
  system from 'preview' to 'active'.  A highway system should have no entries
  here before activation.  Errors shown in <span style="color:
  red">red</span> potentially effect travelers and should be fixed as soon as possible, while others
  can wait until final preparation for system activation.</p>

  <table border="1" style="background-color:#ccf"><tr><th>Route</th><th>Waypoints</th><th>Error</th><th>Info</th><th>FP Entry to Submit</th></tr>
    <?php
      writeTable($tmdb, "0", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.level=\"preview\" and ");
    ?>
  </table>

  <p class="subheading">Errors in In-Development Systems (not FPs)</p>

  <p class="text"><a name="indev"></a>These errors should be
  corrected, or reported as false positives by adding the entry from
  the last column
  to <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the
  datacheck FP list</a> before the highway system is promoted from 'devel' to
  'preview'.  Note: less severe errors, such as distance and angle
  errors can be left until final preparation for promotion to
  'active'.</p>

  <table border="1" style="background-color:#cfc"><tr><th>Route</th><th>Waypoints</th><th>Error</th><th>Info</th><th>FP Entry to Submit</th></tr>
    <?php
      writeTable($tmdb, "0", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.level=\"devel\" and ");
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
      writeTable($tmdb, "1", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where ");
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
