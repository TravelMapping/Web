<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
</style>
<?php
  // establish connection to db: mysql_ interface is deprecated, should learn new options
  $db = new mysqli("localhost","travmap","clinch","TravelMapping") or die("Failed to connect to database");

  # functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
  function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
  }
  function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
  }
?>
<script>
</script>
<title>Travel Mapping Highway Data Updates</title>
</head>

<body onload="populate_dbarrays()">
<h1>Travel Mapping Highway Data Updates</h1>

<h3>Highway Data System Status Changes</h3>

<div id="sysupdates">
  <table border="1"><tr><th>Date</th><th>Country/Region</th><th>System Code</th><th>System Description</th><th>New Status</th></tr>
  <?php
      // select all updates in the DB
      $sql_command = "select * from systemUpdates;";
      $res = $db->query($sql_command);

      while ($row = $res->fetch_assoc()) {
        echo "<tr><td>".$row['date']."</td><td>".$row['region']."</td><td>".$row['systemName']."</td><td>".$row['description']."</td><td>".$row['statusChange']."</td></tr>\n";
      }
      $res->free();
    ?>
  </table>
</div>

<h3>Updates to Highway Data in Active Systems</h3>

<div id="updates">
  <table border="1"><tr><th>Date</th><th>Region</th><th>Route</th><th>File Root</th><th>Description</th></tr>
  <?php
      // select all updates in the DB
      $sql_command = "select * from updates;";
      $res = $db->query($sql_command);

      while ($row = $res->fetch_assoc()) {
        if (strcmp($row['root'],"") == 0) {
          echo "<tr><td>".$row['date']."</td><td>".$row['region']."</td><td>".$row['route']."</td><td>(NONE)</td><td>".$row['description']."</td></tr>\n";
        }
        else {
          echo "<tr><td>".$row['date']."</td><td>".$row['region']."</td><td>".$row['route']."</td><td><a href=\"../hbtest/?r=".$row['root']."\">".$row['root']."</a></td><td>".$row['description']."</td></tr>\n";
        }
      }
      $res->free();
    ?>
  </table>
</div>
</body>
<?php
    $db->close();
?>
</html>
