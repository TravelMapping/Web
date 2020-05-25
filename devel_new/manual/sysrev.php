<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Mapping Manual: Review a preview highway system</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>

<style>
active {background-color: #CCFFCC;}
preview {background-color: #FFFFCC;}
devel {background-color: #FFCCCC;}
</style>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1 style="color:red">Travel Mapping Manual: Review a preview highway system - <i>Draft</i></h1>

<div class="text">
If you notice incorrect highway data, please report it. If the route belongs to a
<a href="sysdef.php#active"><active>active</active> system</a>, the issue should be reported
on the <a href="http://forum.travelmapping.net/index.php?topic=20">forum</a>.
If the route belongs to a system with <a href="sysdef.php#devel"><devel>in-development</devel> status</a> or
<a href="sysdef.php#preview"><preview>preview</preview> status</a>, or the issue is in relation to such a system,
you can report it anytime on the existing <a href="sysnew.php#research">forum thread</a> for the system.</br>
</br>
The introduction of a <a href="sysnew.php">new highway system</a> requires a peer-review</a>.
This is usually done by a volunteer contributor when the review was <a href="sysnew.php#peerreview">requested
by the developer</a> of the system.</br>
</br>
The procedure below describes a thorough peer-review of a <preview>preview</preview> system on the way to get it active.
</div>

<p class="heading"><a name="overview"></a><a style="text-decoration:none" href="#overview">&#x1f517</a>
Steps to review a preview highway system</p>

<div class="text">
<ol>
  <li><a href="#announce">Announce that you will do a peer-review</a></li>
  <li><a href="#completeness">Check the highway system for completeness</a></li>
  <li><a href="#datacheck">Check highway data errors, concurrencies and near-miss points</a></li>
  <li><a href="#generalcheck">General mapview or HDX check</a></li>
  <li><a href="#names">Check route names in Highway Browser route list</a></li>
  <li><a href="#wpteditor">Check routes with Waypoint File Editor</a></li>
  <li><a href="#hb">Check routes with Highway Browser</a></li>
  <li><a href="#questions">Check forum for questions</a></li>
  <li><a href="#finalcheck">Final general check</a></li>
</ol>
Report the issues to the forum thread. Clearly indicate the route name. Make batches especially when it's one of your
first or the developer's first highway systems.
Note on the forum if you skipped a step, for instance if using the HDX is too fancy for you.
</div>

<p class="subheading"><a name="announce"></a><a style="text-decoration:none" href="#announce">&#x1f517</a>
Announce that you wil do a peer-review</p>

<div class="text">
<ul>
  <li>If you want to make a thorough peer-review, you must have reached the <a href="../participate.php#hwydatamanager">
  required level on the way to become a highway data manager</a>.</li>
  <li>The thorough peer-review can begin when the system is in <preview>preview</preview> and when the developer of the system
  has made his final checks. The developer usually <a href="sysnew.php#peerreview">notifies this on the forum's thread</a>.</li>
  <ul>
    <li>If the system looks like being in this state for awhile, you can directly ask the developer whether the system is ready.</li>
  </ul>
  <li>Clearly announce on the forum's thread and agree with the developer of the system, that you will do a peer-review.</li>
</div>

<p class="subheading"><a name="completeness"></a><a style="text-decoration:none" href="#completeness">&#x1f517</a>
Check the highway system for completeness</p>

<div class="text">
<ul>
  <li>Have a general look on the highway system to get familar with the region-specific attitudes.</li>
  <li>Check the sources indicated on the <a href="sysnew.php#research">forum's thread</a>, under
  <a href="/credits.php#regionsources">credits.php</a> or README.md on
  <a href="https://github.com/TravelMapping/HighwayData/tree/master/hwy_data">Github</a>.</li>
  <ul>
    <li>Ask on the forum's thread for sources if they are not indicated.</li>
    <li>Find additional sources if required.</li>
  </ul>
  <li>Check that all routes from the sources are available in the <a href="/hb/">Highway Browser</a>.</li>
  <ul>
    <li>If a route is missing and there is no notification why it is missing, report it on the forum.</li>
  </ul>
</ul>
</div>

<p class="subheading"><a name="datacheck"></a><a style="text-decoration:none" href="#datacheck">&#x1f517</a>
Check highway data errors, concurrencies and near-miss points</p>

<div class="text">
A thorough data error check helps to sort out general issues and avoids getting confused with individual route draftings later on.
Since fixing of these errors is always a risk to break things, it is better to have it fixed before the detailed peer-review starts.
<ul>
  <li>Check the <a href="../datacheck.php">data check list</a> for errors to the system.</li>
  <ul>
    <li>Ignore less important errors like <a href="syserr.php#VISIBLE_DISTANCE">VISIBLE_DISTANCE</a> or <a href="syserr.php#SHARP_ANGLE">SHARP_ANGLE</a>
    but ask the developer to fix more critical errors before you proceed the review.</li>
  </ul>
  <li>Check <a href="syserr.php#concurrency">concurrencies</a> for the region(s) with HDX</li>
  <li>Check <a href="syserr.php#nearmisspoint">near-miss points</a> for the region(s) with HDX</li>
</ul>
You can skip the HDX checks if it is too fancy for your actual skills but please notify it on the forum.
</div>

<p class="subheading"><a name="generalcheck"></a><a style="text-decoration:none" href="#generalcheck">&#x1f517</a>
General mapview or HDX check</p>

<div class="text">
<ul>
  <li>Assuming that the system developer had to make changes, you might have another general check with the HDX
  or user maps (mapview.php) of the region(s).</li>
  <li>If you have skipped the HDX checks before, have a thorough check with user maps for anything looking odd.</li>
</ul>
</div>

<p class="subheading"><a name="names"></a><a style="text-decoration:none" href="#names">&#x1f517</a>
Check route names in Highway Browser route list</p>

<div class="text">
<ul>
  <li>Check that the <a href="/hb/">Highway Browser route list</a> is correct, please refer to <a href="syshwylist.php">highway system lists (.csv)</a>.
  </br>Pay special attention to:</li>
  <ul>
    <li>Route names</li>
    <li>Banners</li>
    <li>City names</li>
</ul>
</div>

<p class="subheading"><a name="wpteditor"></a><a style="text-decoration:none" href="#wpteditor">&#x1f517</a>
Check routes with Waypoint File Editor</p>

<div class="text">
<ul>
  <li>Load the <a href="hwydata.php">wpt files</a> into the <a href="/wptedit/">Waypoint File Editor</a> and check route by route.
  </br>Pay special attention to:</li>
  <ul>
    </br>Check with normal zoom into the map:
    <li><a href="includepts.php#exceed">Sections of the route that go outside the thick red line overlaid on the map</a>.</li>
    <li><a href="includepts.php#intersections">Missing required waypoints</a>.</li>
    <li><a href="sysnew.php#developwpt">Signed endpoints for the route and correct routing</a>.</li>
    </br>Check with deep zoom into the map:
    <li><a href="points.php">Position of the waypoints</a>.
    <li><a href="wayptlabels.php">Label names</a> of waypoints which do <b>not</b> refer to other TM routes in Highway Browser.</li>
  </ul>
</ul>
</div>

<p class="subheading"><a name="hb"></a><a style="text-decoration:none" href="#hb">&#x1f517</a>
Check routes with Highway Browser</p>

<div class="text">
<ul>
  <li>Open the <a href="/hb/">Highway Browser</a> and check route by route.
  </br>Pay special attention to:</li>
  <ul>
    <li>Label names of intersecting TM routes.
    </br>Click on the table to open the info window for each waypoint and compare the intersecting route names to the waypoint label:</li>
    <ul>
      <li>Check for transposed digits.</li>
      <li>Check for missing use of TM routes in the label name.</li>
      <li>Check that TM routes used in the label name do have the intersecting TM route link.
      </br>Note that there are rare exceptions where the waypoint does not necessarily match.</li>
      <li>Check for correct usage of the direction letters.</li>
    </ul>
  </ul>
</ul>
</div>

<p class="subheading"><a name="questions"></a><a style="text-decoration:none" href="#questions">&#x1f517</a>
Check forum for questions</p>

<div class="text">
<ul>
  <li>Revisit the forum's thread while the developer is working on the <a href="sysnew.php#review">review</a> to fix your reported issues.</li>
  <li>Response to questions in case that your reports are not understood.</li>
</ul>
</div>

<p class="subheading"><a name="finalcheck"></a><a style="text-decoration:none" href="#finalcheck">&#x1f517</a>
Final general check</p>

<div class="text">
<ul>
  <li>Have another final check with the HDX or user maps (mapview.php) of the region(s).</li>
  <li>Report on the forum's thread when your <a href="sysnew.php#peerreviewcomplete">peer-review</a> is complete and you think that the developer can <active>activate</active> the system.
</ul>

</div>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
