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
<title>Travel Mapping Developer Tools and Links</title>
</head>

<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<h1>Travel Mapping Developer Tools and Links</h1>


<p class="text">

This page gathers information, tools, and other links primarily of
interest to Travel Mapping project contributors.
Most of the files below are regenerated during each site update.
</p>

<p class="heading">Developer Tools</p>

<div class="text">
<ul>
<li><a href="http://cmap.m-plex.com/tools/manual.php">CHM's Instruction Manual</a> (TM generally follows these rules, but we do need to develop our own manual)</li>
<li><a href="/wptedit/">TM's update of CHM's WPT file Editor</a> (Goal: develop our own new version)</li>
<li><a href="http://courses.teresco.org/metal/hdx/">Highway Data Examiner</a> (HDX) to view graph and near-miss data</li>
</ul>
</div>

<p class="heading">Highway Data Development Logs</p>

<?php
//$sql_command = "select count(*) as c from datacheckErrors join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.level=\"active\" and falsePositive=\"0\"";
$activedc = tm_count_rows("datacheckErrors", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.level=\"active\" and falsePositive=\"0\"");
?>

<div class="text">
<ul>
  <li><a href="datacheck.php">Datacheck</a> (Currently <?php echo $activedc; ?> errors in active systems)<br />
    These errors should be corrected, or reported as false positives (FPs) by adding the entry from the last column to the datacheck FP list.</li>
  <li><a href="../logs/unmatchedfps.log">Datacheck false positive entries that did not correspond to any detected datacheck error</a>
    <br />
  Cleaning these are low priority tasks for the project.</li>
<li><a href="../logs/nearmatchfps.log">Datacheck entries that nearly match false positive entries</a></li>
  <li><a href="../graphs/">Travel Mapping Graph Data</a>
    <br />
    Graphs can be loaded into HDX to verify unexpected or broken concurrencies.</li>
  <li><a href="../logs/nearmisspoints.log">Log of points that are very close together ("near-miss points, or NMPs") and might be candidates to merge</a></li>
  <li><a href="../logs/tm-master.nmp">Master Near-Miss Point (NMP) file</a>
    <br />
    NMP file can be loaded into HDX to find very nearby points that might be appropriate to combine.</li>
  <li><a href="../logs/nmpbyregion/">NMP files filtered by region (individual files)</a></li>
  <li><a href="../logs/nmpbyregion/nmpbyregion.zip">NMP files filtered by region (zip archive)</a></li>
  <li><a href="../logs/nmpfpsunmatched.log">Log of FP entries from nmpfps.log that did not match any entry in nearmisspoints.log</a></li>
  <li><a href="../logs/unprocessedwpts.log">List of unprocessed wpt files</a>
    <br />
    Waypoint files in the repository that were not processed because they were not listed in any highway system's csv files</li>
  <li><a href="../logs/pointsinuse.log">Waypoint labels in use by current TM users</a>
    <br />
    To be considered if wpt files of active systems are modified to avoid breaking user files, can be loaded to CHM's WPT file Editor.</li>
  <li><a href="../logs/unusedaltlabels.log">Alternate (i.e., hidden) waypoint labels not in use by current TM users</a>
    <br />
    These alternate labels can safely be removed from wpt files.</li>
</ul>
</div>

<p class="heading">Web Site Development and Site Update Logs</p>

<div class="text">
<ul>
<li><a href="../logs/siteupdate.log">Complete output log from the site update process</a></li>
<li><a href="../logs/highwaydatastats.log">Log of highway data stats, useful to verify web code</a></li>
<li><a href="../logs/concurrencies.log">List of highway concurrencies detected</a></li>
</ul>
</div>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
