<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
<!-- jQuery -->
<script type="application/javascript" src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
<!-- TableSorter -->
<script type="application/javascript" src="/lib/jquery.tablesorter.min.js"></script>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
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
      $res = tmdb_query($sql_command);

      while ($row = $res->fetch_assoc()) {
        echo "<tr><td>".$row['date']."</td><td>".$row['region']."</td><td>".$row['systemName']."</td><td>".htmlspecialchars($row['description'])."</td><td>".$row['statusChange']."</td></tr>\n";
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
      $res = tmdb_query($sql_command);

      while ($row = $res->fetch_assoc()) {
        if (strcmp($row['root'],"") == 0) {
          echo "<tr><td>".$row['date']."</td><td>".$row['region']."</td><td>".$row['route']."</td><td>(NONE)</td><td>".htmlspecialchars($row['description'])."</td></tr>\n";
        }
        else {
          echo "<tr><td>".$row['date']."</td><td>".$row['region']."</td><td>".$row['route']."</td><td><a href=\"/hb?r=".$row['root']."\">".$row['root']."</a></td><td>".htmlspecialchars($row['description'])."</td></tr>\n";
        }
      }
      $res->free();
    ?>
  </table>
</div>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
