<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
<link rel="shortcut icon" type="image/png" href="/favicon.png">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<?php tm_common_js(); ?>
<title>Travel Mapping <?php echo $tmMode_s; ?> Data Updates</title>
</head>

<body onload="populate_dbarrays()">
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<h1>Travel Mapping <?php echo $tmMode_s; ?> Data Updates</h1>

<p class="info">Quick links: <a href="#sysupdates">[<?php echo $tmMode_s; ?> System Status Changes]</a><a href="#updates">[Updates to <?php echo $tmMode_s; ?> Data in Active Systems]</a>.</p>

<?php
$syscount = 10;
if (array_key_exists("syscount", $_GET)) {
  $syscount = $_GET["syscount"];
  if ($syscount == "all") {
    $syscount = 0;
  }
}
$updatecount = 20;
if (array_key_exists("updatecount", $_GET)) {
  $updatecount = $_GET["updatecount"];
  if ($updatecount == "all") {
    $updatecount = 0;
  }
}
?>
<h3><a name="sysupdates"><?php echo $tmMode_s; ?> System Status Changes</a>

[Show <select onchange="javascript: document.location.href = 'updates.php?syscount=' + document.getElementById('syscount').value + '&updatecount=' + document.getElementById('updatecount').value;" name="syscount" id="syscount">
<?php
  $totalSysUpdates = tm_count_rows("systemUpdates", "");
  $nextEntry = 10;
  while ($nextEntry <= $totalSysUpdates) {
    echo "<option value=\"".$nextEntry."\"";
    if ($syscount == $nextEntry) {
      echo " selected";
    }
    echo ">".$nextEntry." Newest</option>";
    $nextEntry = $nextEntry * 2;
  }
  echo "<option value=\"all\"";
  if ($syscount == 0) {
    echo " selected";
  }
  echo ">All ".$totalSysUpdates."</option>";
?>
</select>
Entries]
</h3>
<div id="sysupdates">
  <table class="sortable" border="1">
    <thead>
      <tr><th>Date</th><th>Country/Region</th><th>System Code</th><th class="no-sort">System Description</th><th>New Status</th></tr>
    </thead>
    <tbody>
  <?php
      if ($syscount == 0) {
        // select all updates in the DB
        $sql_command = "select * from systemUpdates order by date desc;";
      }
      else {
        // select limited number of updates in the DB
        $sql_command = "select * from systemUpdates order by date desc limit ".$syscount.";";
      }
      $res = tmdb_query($sql_command);

      while ($row = $res->fetch_assoc()) {
        if (($row['statusChange'] == "active") || 
            ($row['statusChange'] == "preview")|| 
            ($row['statusChange'] == "extended") || 
            ($row['statusChange'] == "re-entered") || 
            ($row['statusChange'] == "split")) {
            $syslink = "<a href=\"/hb?sys=".$row['systemName']."\">".$row['systemName']."</a>";
        }
        else {
            $syslink = $row['systemName'];
        }
        echo "<tr><td>".$row['date']."</td><td>".$row['region']."</td><td>".$syslink."</td><td>".htmlspecialchars($row['description']);
          echo "</td><td class='status-{$row['statusChange']}'>".$row['statusChange']."</td></tr>\n";
      }
      $res->free();
    ?>
  </tbody>
  </table>
</div>

<h3><a name="updates">Updates to <?php echo $tmMode_s; ?> Data in Active Systems</a>


[Show <select onchange="javascript: document.location.href = 'updates.php?syscount=' + document.getElementById('syscount').value + '&updatecount=' + document.getElementById('updatecount').value;" name="updatecount" id="updatecount">
<?php
  $totalUpdates = tm_count_rows("updates", "");
  $nextEntry = 10;
  while ($nextEntry <= $totalUpdates) {
    echo "<option value=\"".$nextEntry."\"";
    if ($updatecount == $nextEntry) {
      echo " selected";
    }
    echo ">".$nextEntry." Newest</option>";
    $nextEntry = $nextEntry * 2;
  }
  echo "<option value=\"all\"";
  if ($updatecount == 0) {
    echo " selected";
  }
  echo ">All ".$totalUpdates."</option>";
?>
</select>
Entries]
</h3>

<div id="updates">
  <table class="sortable" border="1">
    <thead>
      <tr><th>Date</th><th>Region</th><th class="no-sort">Route</th><th>File Root</th><th class="no-sort">Description</th></tr>
    </thead>
    <tbody>
  <?php
      if ($updatecount == 0) {
        // select all updates in the DB
        $sql_command = "select * from updates order by date desc, region, route;";
      }
      else {
        // select limited number of updates in the DB
        $sql_command = "select * from updates order by date desc, region, route limit ".$updatecount.";";
      }
      $res = tmdb_query($sql_command);

      while ($row = $res->fetch_assoc()) {
        if (strcmp($row['root'],"") == 0) {
          echo "<tr><td>".$row['date']."</td><td>".$row['region']."</td><td>".$row['route']."</td><td>(NONE)</td><td>".htmlspecialchars($row['description'])."</td></tr>\n";
        }
        else {
          echo "<tr><td>".$row['date']."</td><td>".$row['region']."</td><td>".$row['route']."</td><td><a href=\"/hb/showroute.php?r=".$row['root']."\">".$row['root']."</a></td><td>".htmlspecialchars($row['description'])."</td></tr>\n";
        }
      }
      $res->free();
    ?>
    </tbody>
  </table>
</div>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
