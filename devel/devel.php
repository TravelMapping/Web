<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />


<link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />

<!-- jQuery -->
<script type="application/javascript" src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping Developer Tools and Links</title>
</head>

<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<h1>Travel Mapping Developer Tools and Links</h1>

<style>
active {background-color: #CCFFCC;}
preview {background-color: #FFFFCC;}
devel {background-color: #FFCCCC;}
</style>

<p class="text">

This page gathers information, tools, and other links primarily of
interest to Travel Mapping project contributors.
</br></br>
  The content at the pages linked below serve as the manual to help the
  <a href="/credits.php#contributors">volunteer project contributors</a>
  to maintain consistent and high-quality highway data
  for the project.  It is based on the manual from TM's predecessor,
  the Clinched Highway Mapping project.
</p>

<p class="heading"><a name="participate"></a><a style="text-decoration:none" href="#participate">&#x1f517</a>
Participate in the project</i></p>

<div class="text">
  Anyone can submit their travels to be included in the site. Some experienced users volunteer to help the project.
  </br></br>
  Learn more on <a href="/participate.php">how to participate</a>.
</div>


<p class="subheading"><a name="systems"></a><a style="text-decoration:none" href="#systems">&#x1f517</a>
Highway Systems Tutorials</p>

<div class="text">
  <ul>
    <li><a href="manual/sysdef.php">Definition of a highway system</a></li>
    <li><a href="manual/sysnew.php">Create a new highway system</a></li>
    <ul>
      <li><a href="manual/hwydata.php">Highway data files</a></li>
      <li><a href="manual/includepts.php">Waypoints to include</a></li>
      <li><a href="manual/points.php">Positioning waypoints</a></li>
      <li><a href="manual/wayptlabels.php">Labeling waypoints</a></li>
      <li><a href="manual/syshwylist.php">Highway system lists</a></li>
    </ul>
    <li><a href="manual/syserr.php">Deal with data errors</a></li>
    <li><a href="manual/sysrev.php">Review a preview highway system</a></li>
    <li><a href="manual/maintenance.php">Maintain highway data</a></li>
  </ul>
</div>

<p class="subheading"><a name="tools"></a><a style="text-decoration:none" href="#tools">&#x1f517</a>
Tools</p>

<?php
//$sql_command = "select count(*) as c from datacheckErrors join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.level=\"active\" and falsePositive=\"0\"";
$activedc = tm_count_rows("datacheckErrors", "join routes on datacheckErrors.route = routes.root join systems on routes.systemName = systems.systemName where systems.level=\"active\" and falsePositive=\"0\"");
?>

<div class="text">
  <ul>
    <li><a href="/wptedit/">Waypoint File Editor</a>
      <br/>
      Create or modify route files
      <br/>
      <i>TM's update of CHM's WPT file Editor; Goal: develop our own new version</i>
    </li>
    <br/>
    <li><a href="https://courses.teresco.org/metal/hdx/?noav">Highway Data Examiner</a> (HDX)
      <br/>
      View graph and near-miss data
    </li>
    <br/>
    <li><a href="https://github.com/TravelMapping/DataProcessing/blob/master/SETUP.md">Data Verification</a>
      <br/>
      Run site update program to generate the same logs, stats, and database file that are produced as part of the regular site update process
    </li>
    <br/>
    <li><a href="datacheck.php">Data Check</a>
      <br/>
      Check highway data errors (Currently <span style="color: red;"><?php echo $activedc; ?> errors</span> in <active>active</active> systems)
    </li>
    <br/>
    <li><a href="logs.php">Log Files</a>
      <br/>
      Additional developer logs for highway data and web site
    </li>
  </ul>
</div>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
