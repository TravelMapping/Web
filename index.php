<?php require $_SERVER['DOCUMENT_ROOT'] . "/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping - <?php echo $tmMode_p;?></title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<p class="heading">Welcome to Travel Mapping - <?php echo $tmMode_p;?></p>

<p class="text">

Travel Mapping is a collaborative project implemented and maintained
by a <a href="credits.php#contributors">group</a> of travel
enthusiasts who enjoy tracking their cumulative travels in various
modes of transportation.  You are currently on the TM site for
tracking <?php echo $tmmode_s;?> travels.  This site allows
its <a href="/stat.php">users</a> to submit lists of <?php echo
$tmmode_s;?> segments they've traveled on the <a href="/hb"><?php echo
$tmmode_s;?> systems</a> that have been included in the project.  Those
lists are then imported into the project's database, to be included
along with other users' stats and maps.

</p>

<p class="text">

  In addition to this part of the site that tracks <?php echo $tmmode_s;?> travels, TM has <ul class="text">
  <?php
  if ($tmmode_p != "highways") {
     echo '<li><a href="https://travelmapping.net/">a production site that tracks highway travels</a></li>';
  }
  if ($tmmode_p != "railways") {
     echo '<li><a href="https://tmrail.teresco.org/">a site very early in development that tracks railway travels</a></li>';
  }
?>
  </ul>
</p>

<p class="heading">Travel Mapping - <?php echo $tmMode_p;?> Status (motd)</p>

<p class="text">
<?php
if ($tmupdating) {
  echo "Travel Mapping database update in progress.  If you see errors, try back in a few minutes. <br />";
}
if (file_exists($_SERVER['DOCUMENT_ROOT']."/motd")) {
  $tmmotdfile = fopen($_SERVER['DOCUMENT_ROOT']."/motd", "r");
  if ($tmmotdfile) {
    while (!feof($tmmotdfile)) {
      echo fgets($tmmotdfile);
    }
    fclose($tmmotdfile);
  }
  else {
    echo "No news is good news.";
  }
}
else {
  echo "No news is good news.";
}
?>
</p>

<p class="heading">Travel Mapping <?php echo $tmMode_s;?> Data</p>

<p class="text">

Travel Mapping currently includes <?php echo $tmmode_s;?> data for 
<?php
echo tm_count_rows("systems", "WHERE level='active'");
?>

"active" systems.  Active systems are those which we believe are
accurate and complete, and for which any changes that affect users
will be noted in the <a href="/devel/updates.php#updates"><?php echo $tmmode_s; ?> data
updates table</a>.  An additional

<?php
echo tm_count_rows("systems", "WHERE level='preview'");
?>

systems are in "preview" status, which means they are substantially
complete, but still undergoing final revisions.  These may still
undergo significant changes without notification.

<?php
echo tm_count_rows("systems", "WHERE level='devel'");
?>
 more are in development but are not yet complete.  These "devel"
 systems are not yet included in stats or plotted on user maps.
 Active systems encompass 
<?php
echo number_format(tm_count_rows("connectedRoutes", "LEFT JOIN systems ON connectedRoutes.systemName = systems.systemName WHERE systems.level = 'active'"));
?>
 routes for
<?php
echo tm_convert_distance_wholenum(tm_sum_column("overallMileageByRegion", "activeMileage"))." ";
tm_echo_units();
?>
 of "clinchable" <?php echo $tmmode_p; ?>, and that expands to
<?php
echo number_format(tm_count_rows("connectedRoutes", "LEFT JOIN systems ON connectedRoutes.systemName = systems.systemName WHERE systems.level = 'active' OR systems.level = 'preview'"));
?>
 routes for
<?php
echo tm_convert_distance_wholenum(tm_sum_column("overallMileageByRegion", "activePreviewMileage"))." ";
tm_echo_units();
?>
 when preview systems are included.

</p>

<p class="heading">How to Participate</p>

<p class="text">

Anyone can submit their travels to be included in the site.  Please
see the information for <a href="/participate.php">how
to create and submit your data</a>.

</p>

<p class="text">

Once your data is in the system, you will be listed on the
main <a href="/stat.php">traveler stats page</a>, and you can see a
summary of your travels on your <a href="/user">user page</a>.  Click
around on the various links and table entries to find more ways to see
your travels, both as tabular statistics and plotted on maps.

</p>

<p class="text">
Project news is also posted on the <a
href="https://twitter.com/TravelMapping">Travel Mapping Twitter feed</a>.
Follow us!
</p>

<p class="heading">What's New with <?php echo $tmMode_s;?> Data?</p>

<p class="text">
<?php echo $tmMode_s;?> data is <a href="/devel/updates.php">updated</a> almost daily
as corrections are made and progress is made on systems in
development.  When a <?php echo $tmmode_s;?> system is deemed correct and complete to
the best of our knowledge, it becomes "active".  Here are the newest
<?php echo $tmmode_s;?> systems to become active, with their activation dates:</p>
<ul class="text">
<?php
$res = tmdb_query("select systemName, description, date from systemUpdates where statusChange='active'  limit 8");
while ($row = $res->fetch_assoc()) {
  echo "<li>".$row['description']." (".$row['systemName']."), ".$row['date']."</li>\n";
}
$res->free();
?>
</ul>

<p class="text">
The most recent TM - <?php echo $tmMode_p;?> site update completed at <?php echo tm_update_time(); ?> US/Eastern.
</p>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
