<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<!-- jQuery -->
<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<!-- TableSorter -->
<script src="/lib/jquery.tablesorter.min.js"></script>
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
<script type="application/javascript">
    $(document).ready(function () {
            $("#sysupdates").tablesorter({
                headers: {1: {sorter: false}, 3: {sorter: false}}
            });
            $("#updates").tablesorter({
                headers: {2: {sorter: false}, 4: {sorter: false}}
            });
            $('td').filter(function() {
                return this.innerHTML.match(/^[0-9\s\.,%]+$/);
            }).css('text-align','right');
        }
    );
</script>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<h1>Travel Mapping Highway Data Updates</h1>


<p class="info">Quick links: <a href="#sysupdates">[Highway System Status Changes]</a><a href="#updates">[Updates to Highway Data in Active Systems]</a>.</p>


<h3><a name="sysupdates">Highway System Status Changes</a></h3>

<div id="sysupdates">
  <table class="tablesorter" border="1"><tr><th class="sortable">Date</th><th class="nonsortable">Country/Region</th><th class="sortable">System Code</th><th class="nonsortable">System Description</th><th class="sortable">New Status</th></tr>
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

<h3><a name="updates">Updates to Highway Data in Active Systems</a></h3>

<div id="updates">
  <table class="tablesorter" border="1"><tr><th class="sortable">Date</th><th class="sortable">Region</th><th class="nonsortable">Route</th><th class="sortable">File Root</th><th class="nonsortable">Description</th></tr>
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
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $db->close();
?>
</html>
