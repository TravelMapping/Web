<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
</style>
<?php
  // establish connection to db
  $db = new mysqli("localhost","travmap","clinch","TravelMapping") or die("Failed to connect to database");

  # function to generate a table with FP or not
  function writeTable($db, $fpVal, $joins) {
      // select all errors in the DB with the given $fpVal
      $sql_command = "select datacheckErrors.* from datacheckErrors ".$joins." falsePositive=".$fpVal.";";
      echo "<!-- SQL: ".$sql_command." -->\n";
      $res = $db->query($sql_command);

      while ($row = $res->fetch_assoc()) {
        echo "<tr><td><a href=\"../hbtest/?r=".$row['route']."\">".$row['route']."</a></td><td>";
        if (strcmp($row['label1'],"") != 0) {
          echo $row['label1'];
        }
        if (strcmp($row['label2'],"") != 0) {
          echo ",".$row['label2'];
        }
        if (strcmp($row['label3'],"") != 0) {
          echo ",".$row['label3'];
        }
        echo "</td><td>".$row['code']."</td><td>";
        if (strcmp($row['value'],"") != 0) {
          echo $row['value'];
        }
        echo "</td><td><tt>".$row['route'].";".$row['label1'].";".$row['label2'].";".$row['label3'].";".$row['code'].";".$row['value']."</tt></td></tr>\n";
      }
      $res->free();
  }
?>
<script>
</script>
<title>Travel Mapping Highway Data Datacheck Errors</title>
</head>

<body onload="populate_dbarrays()">
<h1>Travel Mapping Highway Data Datacheck Errors</h1>

<p>Quick links: <a href="#active">[Errors to be Addressed]</a><a href="#indev">[Errors in In-Dev Systems]</a><a href="#marked">[Errors Marked as FPs]</a>.</p>

<p>See also the <a href="../logs/unmatchedfps.log">[Log of Unmatched
FPs from datacheckfps.csv]</a> and
the <a href="../logs/unprocessedwpts.log">[Log of Unprocessed WPTs in
the Repository]</a>.  Cleaning these up are low priority tasks for the
project.</p>

<div id="errors">

  <h3>Errors to be Addressed (not FPs)</h3>
  <p><a name="active"></a>These errors should be corrected, or reported as false positives by adding the entry from the last column to <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the datacheck FP list</a> as soon as possible.  Ideally, this list should always be empty.</p>
  <table border="1" style="background-color:#fcc"><tr><th>Route</th><th>Waypoints</th><th>Error</th><th>Info</th><th>FP Entry to Submit</th></tr>
    <?php
      writeTable($db, "0", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.active=\"1\" and ");
    ?>
  </table>
  <h3>Errors in In-Development Systems (not FPs)</h3>
  <p><a name="indev"></a>These errors should be corrected, or reported as false positives by adding the entry from the last column to <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the datacheck FP list</a> before the system is activated.</p>
  <table border="1" style="background-color:#cfc"><tr><th>Route</th><th>Waypoints</th><th>Error</th><th>Info</th><th>FP Entry to Submit</th></tr>
    <?php
      writeTable($db, "0", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.active=\"0\" and ");
    ?>
  </table>
  <h3>Errors Marked as FPs ("Crossed Off")</h3>
  <p><a name="marked"></a>These have been marked as FPs in <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the datacheck FP list</a> and can normally be safely ignored.  However, if any of these are discovered to be true errors, they should be removed from the list and fixed in the highway data.</p>
  <table border="1" style="background-color:#ccc;font-size:60%"><tr><th>Route</th><th>Waypoints</th><th>Error</th><th>Info</th><th>FP Entry Matched</th></tr>
    <?php
      writeTable($db, "1", " where ");
    ?>
  </table>
</div>
</body>
<?php
    $db->close();
?>
</html>
