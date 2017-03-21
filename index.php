<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<p class="heading">Welcome to Travel Mapping</p>

<p class="text">

Travel Mapping is a collaborative project implemented and maintained
by a <a href="credits.php#contributors">group</a> of travel
enthusiasts who enjoy tracking their cumulative highway travels.  This
site allows its <a href="/stat.php">users</a> to submit lists of
highway segments they've traveled on the <a href="/hb">highway
systems</a> that have been included in the project.  Those lists are
then imported into the project's database, to be included along with
other users' stats and maps.

</p>

<p class="heading">Travel Mapping Status (motd)</p>

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

<p class="heading">Travel Mapping Highway Data</p>

<p class="text">

Travel Mapping currently includes highway data for 
<?php
echo tm_count_rows("systems", "WHERE level='active'");
?>

"active" systems.  Active systems are those which we believe are
accurate and complete, and for which any changes that affect users
will be noted in the <a href="/devel/updates.php#updates">highway data
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
 more are in development but are not yet complete.  Active systems
encompass 
<?php
echo number_format(tm_count_rows("connectedRoutes", "LEFT JOIN systems ON connectedRoutes.systemName = systems.systemName WHERE systems.level = 'active'"));
?>
 routes for
<?php
echo number_format(tm_sum_column("overallMileageByRegion", "activeMileage"));
?>
 miles of "clinchable" highways, and that expands to
<?php
echo number_format(tm_count_rows("connectedRoutes", "LEFT JOIN systems ON connectedRoutes.systemName = systems.systemName WHERE systems.level = 'active' OR systems.level = 'preview'"));
?>
 routes for
<?php
echo number_format(tm_sum_column("overallMileageByRegion", "activePreviewMileage"));
?>
 miles when preview systems are included.

</p>

<p class="heading">How to Participate</p>

<p class="text">

Anyone can submit their travels to be included in the site.  Please
see the information in <a href="/forum">the project forum</a> for how
to create and submit your data.

</p>

<p class="text">

Once your data is in the system, you will be listed on the
main <a href="/stat.php">traveler stats page</a>, and you can see a
summary of your travels on your <a href="/user">user page</a>.  Click
around on the various links and table entries to find more ways to see
your travels, both as tabular statistics and plotted on maps.

</p>

<p class="text">

Some experienced users volunteer to help the project.  If this
interests you, start by reporting problems with existing highway data.
Those who have learned the project's structure and highway data rules
and guidelines can help greatly by providing review of new highway
systems in development.  Highly experienced users can learn how to
plot new highway systems under the guidance of experienced
contributors.  Again, see <a href="/forum">the project forum</a> for
more information.
</p>

<p class="heading">What's New with Highway Data?</p>

<p class="text">
Highway data is <a href="/devel/updates.php">updated</a> almost daily
as corrections are made and progress is made on systems in
development.  When a highway system is deemed correct and complete to
the best of our knowledge, it becomes "active".  Here are the newest
systems to become active, with their activation dates:</p>
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
The most recent site update completed at <?php echo tm_update_time(); ?> US/Eastern.
</p>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
