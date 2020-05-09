<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Mapping Manual: System Definition</title>
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

<h1>Travel Mapping Manual: System Definition</h1>

<p class="text" style="color:red">
  <i>
    TO DO:</br>
    What we consider as a highway system (how to be distinguished from other systems)</br>
    What needs to be fulfilled for routes to be added (signed in field, signed on maps, indicated in official documents,...)</br>
    Which sources are required (likely different depending on the region, country or continent)
</br>
Especially "Select" systems need a clear definition (eursf, usansf, usasf, usanp, eurtr, cannf, mexsf,...), please refer to http://forum.travelmapping.net/index.php?topic=3480</i>
  </br>
</p>

<p class="heading"><a name="status"></a><a style="text-decoration:none" href="#status">&#x1f517</a>
System Status</p>

<p class="text">
  Highway systems in TM are categorized in one of three groups,
  depending on its level of completeness and the maintainers'
  confidence in its accuracy:
</p>


<div class="text">
  <ul>
    <li><a name="active"></a><a style="text-decoration:none" href="#active">&#x1f517</a>
    <b><active>Active systems</active></b> are those which we believe are accurate and complete.
      Care is taken to ensure that changes do not "break" a user's list file. In many cases, however, the changes needed will break user lists. In those situations, changes must be logged in the <a href="https://github.com/TravelMapping/HighwayData/blob/master/updates.csv">updates list</a>, which is  available <a href="http://travelmapping.net/devel/updates.php">on the updates page</a> to help users keep their list files accurate.
      </br>
    </li>
    </br>
    <li><a name="preview"></a><a style="text-decoration:none" href="#preview">&#x1f517</a>
    <b><preview>Preview systems</preview></b> are substantially complete, but are undergoing final review and revisions. These may still undergo significant changes without notification in the updates log. Users can include these systems in list files for mapping and stats, but should expect to revise as the system progresses toward activation.  Users plotting travels in preview systems may wish to follow the forum discussions to see the progress and find out about revisions being made. The activation of a system is logged in the <a href="https://github.com/TravelMapping/HighwayData/blob/master/systemupdates.csv">system updates list</a>, and notified <a href="http://travelmapping.net/devel/updates.php">on the updates page</a>.
    </li>
    </br>
    <li><a name="devel"></a><a style="text-decoration:none" href="#devel">&#x1f517</a>
    <b><devel>In-Development systems</devel></b> are a work in progress. Routes in these systems are not yet available for mapping and inclusion in user stats, and are shown in the Highway Browser primarily for the benefit of the highway data managers who are developing the system. Once the system is substantially complete it will be upgraded to preview status, at which time users can begin to plot their travels in the system. It is also logged in the <a href="https://github.com/TravelMapping/HighwayData/blob/master/systemupdates.csv">system updates list</a>, and notified <a href="http://travelmapping.net/devel/updates.php">on the updates page</a>.
    </li>
  </ul>
</div>
<p class="text">
  The system categories are indicated by color-code throughout the site. User stats are generally divided into stats for travels in <active>active</active> systems and <active>active</active> + <preview>preview</preview> systems.
</p>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
