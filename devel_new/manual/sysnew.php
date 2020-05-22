<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Mapping Manual: Create a new highway system</title>
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

<h1 style="color:red">Travel Mapping Manual: Create a new highway system - <i>Draft</i></h1>



<div class="text">New highway systems are developed by our volunteer highway data managers who went through the
  <a href="../participate.php#hwydatamanager">procedure of getting a contributor</a>. When they have reached the
  right level of experience, they can start developing further highway systems. The subsequent list describes the
  procedure on how to develop a new highway system. It is mandatory for new highway data managers to work through
  it step by step. It is recommended for highly experienced highway data managers.</div>

<p class="heading"><a name="overview"></a><a style="text-decoration:none" href="#overview">&#x1f517</a>
Steps to introduce a new highway system</p>

<div class="text">
  <ol>
    <li><a href="#research">Research about the highway system and discussion on the forum</a></li>
    <li><a href="#existing">Check existing routes and add waypoints</a></li>
    <li><a href="#develop">Develop routes in devel status</a></li>
    <li><a href="#preview">Promote highway system to preview</a></li>
    <li><a href="#datacheck">Clear datacheck, broken concurrencies and NMP issues</a></li>
    <li><a href="#peerreview">Request peer review</a></li>
    <li><a href="#review">Make changes based on peer review</a></li>
    <li><a href="#datacheck">Clear datacheck, broken concurrencies and NMP issues again</a></li>
    <li><a href="#activate">Activate the highway system</a></li>
    <li><a href="#maintain">Maintain the highway system</a></li>
  </ol>
</div>


<p class="subheading"><a name="research"></a><a style="text-decoration:none" href="#research">&#x1f517</a>
Research about the highway system and discussion on the forum</p>

<div class="text" >
Before you can start the development of a new highway system, the following requirements must be fulfilled:
<ul>
  <li><a name="researchcheck"></a><a style="text-decoration:none" href="#researchcheck">&#x1f517</a>
  Check which regions are covered by the highway system you want to develop</li>
  <ul>
    <li>Are there already TM routes in that regions?</li>
    <ul>
      <li>Check the routes whether they need a revision because of changed alignments, new routes or low data quality.</li>
    </ul>
    <li>Does another highway data manager claim any region affected?</li>
    <ul>
      <li>You need to ask the manager whether he/she wants to develop the highway system by himself/herself.</li>
    </ul>
  </ul>
  <li><a name="researchfamilar"></a><a style="text-decoration:none" href="#researchfamilar">&#x1f517</a>
  Get familar with the highway systems of the region.</li>
  <ul>
    <li>Search for route lists and maps of the region(s) and highway system.</li>
    <li>Is your highway system the highest system of the region(s) that is not yet covered by TM?</li>
    <ul>
      <li>We usually start with the freeway systems and develop the other systems top down. Consider to develop the higher system first.</li>
    </ul>
    <li>Does the highway system in question fulfill the <a href="sysdef.php#tmsys">TM highway system requirements</a>?</li>
  </ul>
  <li><a name="researchforum"></a><a style="text-decoration:none" href="#researchforum">&#x1f517</a>
  Open a new thread on the <a href="http://forum.travelmapping.net/index.php?board=6">In-progress Highway Systems & Work</a> board of the forum.</li>
  <ul>
    <li>Present your source for the routes.</li>
    <li>Describe what you found out about the highway system, especially about the quality of signposting.</li>
    <li>Ask whether there are objections to add the highway system.</li>
    <li>Suggest <a href="#developsystem">system code, tier index and color code</a> for the system.</li>

<!-- Do we need a description for tier and system colors? -->


    <li>Be patient since we are volunteering contributors and don't have time to answer immediately. Accept asks
    for better research and accept when we don't see the system as a future TM highway system.</li>
  </ul>
</ul>
Note that there are a few exceptions to the strict <a href="sysdef.php#tmsys">TM highway system requirements</a>.
Don't hestiate with opening a thread on the forum to ask whether the potential highway system is qualified for TM.
</div>

<p class="subheading"><a name="existing"></a><a style="text-decoration:none" href="#existing">&#x1f517</a>
Check existing routes and add waypoints</p>

<div class="text" >
<ul>
  <li><a name="existingcheck"></a><a style="text-decoration:none" href="#existingcheck">&#x1f517</a>
  If there are already routes in the region(s) of the future highway system, check them for accuracy first.</li>
  <ul>
    <li>If the routes are active, they need <a href="maintenance.php">special treatment</a>.</li>
  </ul>
  <li><a name="existingadd"></a><a style="text-decoration:none" href="#existingadd">&#x1f517</a>
  Add the waypoints which are required for the future highway system to the existing routes.</li>
</ul>
The existing wpt files are stored on <a href="https://github.com/TravelMapping/HighwayData">Github</a>. They can be
loaded into the <a href="/wptedit/">Waypoint File Editor</a> for modifications. The changed files must be submitted
to Github with a pull request. Please refer to <a href="#develop">the development instructions</a> for more info about
how to deal with the files and tools.
</div>

<p class="subheading"><a name="develop"></a><a style="text-decoration:none" href="#develop">&#x1f517</a>
Develop routes in devel status</p>

<div class="text" >
<ul>
  <li><a name="developwpt"></a><a style="text-decoration:none" href="#developwpt">&#x1f517</a>
  Develop routes by creating <a href="hwydata.php">highway data files</a>.</li>
  <ul>
    <li>Create the files route by route in a desired order but make sure that you don't miss any route.</li>
    <ul>
      <li><a href="manual/includepts.php">Waypoints to include</a></li>
      <li><a href="manual/points.php">Positioning waypoints</a></li>
      <li><a href="manual/wayptlabels.php">Labeling waypoints</a></li>
    </ul>
    <li>Only add routes which are fully signed:</li>
    <ul>
      <li>Signed at the beginning of the route.</li>
      <li>Signed at the end of the route.</li>
      <li>Signed along the route so that there is no doubt about the actual routing.</li>
    </ul>
    <li>If the routing is partially not clear or obvious, you need to shorten or interupt the route.</li>
    <li>If the number of fully unsigned routes is <i>small</i>, they can be included when there is a clear evidence for their existance.</li>
    <ul>
      <li>Evidence is, when they are on the latest official list from the responsible road authority or state institution.</li>
      <li>For some regions, e.g. with poor street view coverage, it is sufficient when the routing is indicated on maps.</li>
    </ul>
    <li>Notes to exceptions should be clearly described on the forum thread and / or in a README.md file on the Github folder for the wpt files.</li>
  </ul>
  <li><a name="developcsv"></a><a style="text-decoration:none" href="#developcsv">&#x1f517</a>
  Make the <a href="syshwylist.php">highway system lists (.csv)</a></li>
  <li><a name="developsystem"></a><a style="text-decoration:none" href="#developsystem">&#x1f517</a>
  Promote the system to <a href="sysdef.php#devel"><devel>devel</devel> status</a> when a first batch of routes is available.</li>
  <ul>
    <li>This requires a new entry to <a href="https://github.com/TravelMapping/HighwayData/blob/master/systems.csv">system.csv</a> on Github.</li>
    <ul>
      <li>Enter a new line to the last segment of the file where the in-development systems are listed. The structure is as follows:</li>
      <pre>
System;CountryCode;Name;Color;Tier;Level</br>
...
brabr;BRA;Brazil Rodovias Federais;green;4;devel
...
      </pre>
      <li>The <code>system</code> code starts with the country, region or continent code, followed by a system abbreviation</li>
      <li>The region or continent or <code>country code</code> can be found in
      <a href="https://github.com/TravelMapping/HighwayData/blob/master/regions.csv">regions.csv</a></li>
      <li>The system <code>name</code> starts with the English region name followed by the system name in native language</li>
      <ul>
        <li>Use an English term like <i>Motorways</i> or <i>Main Roads</i> if the native name is not using a Latin-script alphabet.</li>
      </ul>
      <li>The <code>color</code> represents the color which is used on maps to draw the TM graph.</li>
      <ul>
        <li>The color must be unique for each region to distinguish the systems.</li>
        <li>The color should represent the color which is used on signs to indicate the routes.</li>
        <li>Standard colors are often used for a continent to avoid confusing users.</li>
      </ul>
      <li>The <code>tier</code> index is used to distinguish systems top down.</li>
      <ul>
        <li>It is used on maps to draw the TM graph. If there are concurrent routes, the color of the higher level system is on top.</li>
        <li>Standard tiers are often used for a continent to avoid confusing users.</li>
      </ul>
      <li>The <code>level</code> is always <devel>devel</devel> at this point.</li>
    </ul>
  </ul>
  <li><a name="developshield"></a><a style="text-decoration:none" href="#developshield">&#x1f517</a>
  Create the system shield <i>(optionally)</i></li>
  <ul>
    <li>A svg file is used to show a system specific shield in the highway browser. They are generic and hosted on
    <a href="https://github.com/TravelMapping/Web/tree/master/shields">the web repository on Github</a>.</li>
    <li>If no specific svg is available for the system, the standard shield is used.</li>
  </ul>
</ul>
We don't notify the promotion of a highway system to in-development <a href="http://travelmapping.net/devel/updates.php">
on the updates page</a> but on the forum thread only.
</br>
<b>When promoting a system to <devel>devel</devel>, the system is processed for the first time.
If any wpt or csv file has an incorrect format or reference, the site update will fail. Please run
<a href="https://github.com/TravelMapping/DataProcessing/blob/master/SETUP.md">data verification</a>
to find the errors before submitting the files.</b>
</div>

<p class="subheading"><a name="preview"></a><a style="text-decoration:none" href="#preview">&#x1f517</a>
Promote highway system to preview</p>

<div class="text" >When the first draft of all routes is in the highway browser, you can promote the highway
system to <a href="sysdef.php#preview"><preview>preview</preview> status</a>.
<ul>
  <li><a name="previewsystem"></a><a style="text-decoration:none" href="#previewsystem">&#x1f517</a>
  The entry from <a href="https://github.com/TravelMapping/HighwayData/blob/master/systems.csv">system.csv</a>
must be moved from the in-development segment of the file to the preview segment.</li>
  <ul>
    <li>The <code>level</code> column must be changed from <devel>devel</devel> to <preview>preview</preview>.</li>
    <pre>
System;CountryCode;Name;Color;Tier;Level</br>
...
brabr;BRA;Brazil Rodovias Federais;green;4;preview
...
    </pre>
  </ul>
  <li><a name="previewupdate"></a><a style="text-decoration:none" href="#previewupdate">&#x1f517</a>
  It must be logged on top of the <a href="https://github.com/TravelMapping/HighwayData/blob/master/systemupdates.csv">system updates list</a>,
  so that the notification will appear <a href="http://travelmapping.net/devel/updates.php">on the updates page</a>.</li>
</ul>
Especially when drafting a huge highway system, it is possible to split it into several sub-systems with partial preview promotion.
Ask on the forum thread for more details.
</div>

<p class="subheading"><a name="datacheck"></a><a style="text-decoration:none" href="#datacheck">&#x1f517</a>
Clear datacheck, broken concurrencies and NMP issues</p>

<div class="text" >
A first complete check should be done when the system is in <preview>preview</preview> status,
a second run must be done <b>before</b> the activation of the system.
<ul>
  <li><a name="datacheckerrors"></a><a style="text-decoration:none" href="#datacheckerrors">&#x1f517</a>
  <a href="syserr.php#errors">Data errors</a> can be checked as soon as the system is in <devel>devel</devel> state.</li>
  <li><a name="datacheckconcurrency"></a><a style="text-decoration:none" href="#datacheckconcurrency">&#x1f517</a>
  <a href="syserr.php#concurrency">Concurrency checks</a> can be done when the system is minimum in <preview>preview</preview> state.</li>
  <li><a name="datachecknearmisspoint"></a><a style="text-decoration:none" href="#datachecknearmisspoint">&#x1f517</a>
  <a href="syserr.php#nearmisspoint">Near-miss point checks</a> can be done when the system is minimum in <preview>preview</preview> state.</li>
</ul>

<b>Errors from <active>active</active> systems should always be corrected or reported as false positives short-term.</b>

</div>

<p class="subheading"><a name="peerreview"></a><a style="text-decoration:none" href="#peerreview">&#x1f517</a>
Request peer review</p>

<div class="text" >
<ul>
  <li><a name="peerreviewrequest"></a><a style="text-decoration:none" href="#peerreviewrequest">&#x1f517</a>
  Notify on the forum thread that the system is ready for a review by another contributor.</li>
  <li><a name="peerreviewany"></a><a style="text-decoration:none" href="#peerreviewany">&#x1f517</a>
  Any other user can also report issues which should be considered.</li>
  <li><a name="peerreviewcomplete"></a><a style="text-decoration:none" href="#peerreviewcomplete">&#x1f517</a>
  The peer review is complete, when the reviewer says it is complete.</li>
</div>

<p class="subheading"><a name="review"></a><a style="text-decoration:none" href="#review">&#x1f517</a>
Make changes based on peer review</p>

<div class="text" >
Changes proposed on the forum thread should be processed short-term while the peer-review is still ongoing.
<ul>
  <li><a name="reviewchanges"></a><a style="text-decoration:none" href="#reviewchanges">&#x1f517</a>
  Make changes to the routes as required from users on the forum.</li>
  <li><a name="reviewrejects"></a><a style="text-decoration:none" href="#reviewrejects">&#x1f517</a>
  If you don't agree with proposed changes, explain on the forum why they should not be made.
  Provide a link to the manual or other sources if possible.</li>
  <li><a name="reviewbreak"></a><a style="text-decoration:none" href="#reviewbreak">&#x1f517</a>
  Care is taken to ensure that changes do not "break" a user's list file.</li>
  <ul>
    <li>If a <a href="../logs/pointsinuse.log">waypoint labels is in use by current TM users</a> we should
    <a href="maintenance.php#labelwrong">add alternative labels</a> if possible.</li>
    <li>We don't add notifications <a href="http://travelmapping.net/devel/updates.php">to the updates page</a>
    because we are still in <preview>preview</preview>.</li>
  </ul>
</ul>
</div>

<p class="subheading"><a name="activate"></a><a style="text-decoration:none" href="#activate">&#x1f517</a>
Activate the highway system</p>

<div class="text" >When the system is complete and there are no critical open issues, promote the highway system to
<a href="sysdef.php#active"><active>active</active> status</a>.
<ul>
  <li><a name="activatesystem"></a><a style="text-decoration:none" href="#activatesystem">&#x1f517</a>
  The entry from <a href="https://github.com/TravelMapping/HighwayData/blob/master/systems.csv">system.csv</a>
  must be moved from the preview segment of the file to the segment with active systems.</li>
  <ul>
    <li>The <code>level</code> column must be changed from <preview>preview</preview> to <active>active</active>.</li>
    <pre>
System;CountryCode;Name;Color;Tier;Level</br>
...
brabr;BRA;Brazil Rodovias Federais;green;4;active
...
    </pre>
  </ul>
  <li><a name="activateupdate"></a><a style="text-decoration:none" href="#activateupdate">&#x1f517</a>
  It must be logged on top of the <a href="https://github.com/TravelMapping/HighwayData/blob/master/systemupdates.csv">
  system updates list</a>, so that the notification <a href="http://travelmapping.net/devel/updates.php">on the updates page</a> will appear.</li>
</ul>
</div>

<p class="subheading"><a name="maintain"></a><a style="text-decoration:none" href="#maintain">&#x1f517</a>
Maintain the highway system</p>

<div class="text" >
<ul>
  <li><a name="maintainregion"></a><a style="text-decoration:none" href="#maintainregion">&#x1f517</a>
  If you are responsible for the region(s) of the highway system, you need to <a href="maintenance.php">maintain the system</a>.
  Follow the discussion on the forum.</li>
  <li><a name="maintainupdates"></a><a style="text-decoration:none" href="#maintainupdates">&#x1f517</a>
  All user-relevant changes to the routes must be notified <a href="http://travelmapping.net/devel/updates.php">on the updates page</a>
  now since the system is <active>active</active>.</li>
</ul>
</div>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
