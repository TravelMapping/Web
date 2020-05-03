<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Mapping Manual: Deal with data errors</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>

<style>
green {background-color: #CCFFCC;}
yellow {background-color: #FFFFCC;}
red {background-color: #FFCCCC;}
</style>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: Deal with data errors</h1>

<p class="text">
  The developer tools execute different kind of data checks to indicate potential errors.
  Highway data managers need to check for these errors to avoid bothering TM users with incorrect data.
  Most of the data checks are available during the work on highway data and before submitting changes, but some need attention after the next site update.
</p>

<p class="heading"><a name="list"></a><a style="text-decoration:none" href="#list">&#x1f517</a>
Data errors</p>

<p class="text">
  When working with the <a href="/wptedit/">WPT file editor</a>, a data check for the loaded route is executed with every modification. The <code>code</code> of a data error is indicated in the last column of the waypoint table. Use the tool tip for additional info.
  After manual changes within the editor field, the data check is done on the next editor action, e.g. when pressing the <code>Load</code> button.
  </br>
  </br>
  Check the table for errors before saving the wpt file. Fix unintended errors or mark intended errors <a href="#falsepositive">false positive</a>.
  </br>
  </br>
  Note: Not all data errors are yet detected by the WPT file editor, see last column below.
  </br>
</p>

<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="40">Code</td>
    <td width="200">Full Code</td>
    <td width="400">Description</td>
    <td width="50">WPT File</br>Editor</td>
  </tr>
  <tr valign="top">
    <td><a name="BA"></a><a style="text-decoration:none" href="#BA">&#x1f517</a></td>
    <td>BA</td>
    <td>BAD_ANGLE</td>
    <td>Angles cannot be computed for 2 adjacent points @ same coords. Instead, use AltLabels or fix coords of 1 point or both.</td>
    <td>NO</td>
  </tr>
  <tr valign="top">
    <td><a name="IB"></a><a style="text-decoration:none" href="#IB">&#x1f517</a></td>
    <td>IB</td>
    <td>BUS_WITH_I</td>
    <td>Label looks like an Interstate with <code>Bus</code> banner instead of <code>BL</code> or <code>BS</code>.</td>
    <td>YES</td>
  </tr>
  <tr valign="top">
    <td>...</td>
    <td>...</td>
    <td>...</td>
    <td>...</td>
    <td>...</td>
  </tr>
  <tr valign="top">
    <td>...</td>
    <td>...</td>
    <td>...</td>
    <td>...</td>
    <td>...</td>
  </tr>
  <tr valign="top">
    <td>...</td>
    <td>...</td>
    <td>...</td>
    <td><a href="https://github.com/TravelMapping/Web/issues/374"><i>list to be completed</i></a></td>
    <td>...</td>
  </tr>
  <tr valign="top">
    <td>...</td>
    <td>...</td>
    <td>...</td>
    <td>...</td>
    <td>...</td>
  </tr>
  <tr valign="top">
    <td>...</td>
    <td>...</td>
    <td>...</td>
    <td>...</td>
    <td>...</td>
  </tr>
</tbody></table>


<p class="heading"><a name="list"></a><a style="text-decoration:none" href="#list">&#x1f517</a>
Highway data error list</p>

<p class="text">
  When changes to highway data have been submitted and are live on the site, all routes of the project have been checked during the site update process. They are reported on the <a href="../dataerrors.php">data error list</a>. The list is sorted by the system categories active, preview and in-development.
  </br>
  <ul>
    <li>Check the table for errors. It is possible to filter the list by system or by region.</li>
    <li>Click on the link in the <code>Route</code> column to load the route into the HB.
    <ul>
      <li>Fix unintended errors by using the <a href="/wptedit/">WPT file editor</a>.</li>
      <li>Mark intended errors <a href="#falsepositive">false positive</a>.</li>
      <li>Some of the errors are likely fixable from the information in the <a href="../../logs/nearmatchfps.log">Log of Near-Match FPs from datacheckfps.csv</a>. These are FP entries which have previously been added but due to minor changes, e.g. reposition of a waypoint, they do no longer match the actual FP entry.</li>
    </ul>
  </ul>
  Note: It is possible to edit the url in the address bar of the browser to filter for more than one region or system. For instance, it is possible to create a link to all regions a highway data manager maintains. Save it to your browser bookmark and load it when needed for the check.
  </br>
</p>


<p class="heading"><a name="falsepositive"></a><a style="text-decoration:none" href="#falsepositive">&#x1f517</a>
Marking errors false positive (FP)</p>

<p class="text">
  <ul>
    <li>The last column of the <a href="../dataerrors.php">data error list</a> contains the <FP entry to Submit>.</li>
    <ul>
      <li>If no code is specified, it is always a true error and cannot be marked false positive.</li>
    </ul>
    <li>If a code is specified, mark and copy it.</li>
    <ul>
      <li>To mark the whole code, click three times onto the entry.</li>
    </ul>
    <li>Paste it into your copy of <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the datacheck FP list</a>.</li>
    <ul>
      <li>The list is sorted by region codes. Insert your entry at the right position.</li>
    </ul>
    <li>Submit the change. The data error will disappear from the list with the next site updated.</li>
  </ul>
</p>

<p class="text">
  <ul>
    <li>Do not forget checking for <a href="../../logs/unmatchedfps.log">unmatched FPs</a>. Remove them from <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the datacheck FP list</a>.</li>
  </ul>
</p>

<p class="heading"><a name="concurrency"></a><a style="text-decoration:none" href="#concurrency">&#x1f517</a>
Concurrency check</p>

<p class="text">
  Concurrent segments of different routes are detected and considered for calculating user stats. If a concurrency is broken by accident, stats are falsified.
  Highway data managers need to pay attention to check broken concurrencies in their regions. The check can not be done before submitting changes but is currently only possible with the data of the last site update.
  </br>
  </br>
  The best practise to do this, is as follows:
  </br>
  <ul>
    <li>Open the <a href="http://courses.teresco.org/metal/hdx/">Highway Data Examiner</a> (HDX).</li>
    <li>Go to <code>Option 1</code> on the left and enter the name of the region you want to check.</li>
    <li>Press enter.</li>
    <li>Wait. The graph is automatically loaded after a few seconds but it might take longer with large graphs.</li>
    <ul>
      <li>You might open up a small graph first, e.g. the District of Columbia graph, then deselect <code>Show Markers</code> and select whatever region graph you are actually interested in to speed up loading.</li>
    </ul>
    <li>Press <code>Done</code> on the left to close the AV table.</li>
    <li>Deselect <code>Show Data Tables</code> to close the table to the right.</li>
    <li>You see a map of the selected region with yellow dots.</li>
    <li>If you hardly see anything but only yellow dots, deselect <code>Show Markers</code>.</li>
    <li>You should see a map of the selected region with funny colors now.</li>
    <li>Select <code>Show Markers</code> again, it was just to show you what happened.</li>
    <li>Zoom-in where you wanna check concurrencies. Start top left or go directly to a special location where you expect broken concurrencies.</li>
    <li>The funny colors are created by lines connecting the waypoints.</li>
    <ul>
      <li>Blue line = one route only</li>
      <li>Green line = two concurrent routes</li>
      <li>Magenta line = three concurrent routes</li>
      <li>etc.</li>
      <li><i><red>ToDo: Add complete complete color list from ???</red></i></li>
    </ul>
    <li>When there is a green line - 2 routes concurrent - but interrupted by a dark blue line at visible or hidden wps, it's a sign that concurrencies are broken</li>
    <ul>
      <li>The dark blue is created when two normal blue lines are overlapping.</li>
    </ul>
    <li>All <i>non-standard colors</i> like the dark blue are possible broken concurrencies.</li>
    <li>When you spot errors, click on the lines or markers to get info about the route and waypoint labels. It is sometimes difficult to click all routes when they are mostly overlapped.</li>
    <li>Fix the coordinates in the corresponding wpt files.</li>
    <li>Load the changed wpt files into the <a href="/wptedit/">WPT file editor</a> to avoid causing unintended <a href="#list">data errors</a>.</li>
    <li>Broken concurrencies of short segments can hardly be found this way. It is recommended to check <a href="#nearmisspoint"> NMPs</a> to find these errors.</li>
  </ul>
</p>

<p class="heading"><a name="nearmisspoint"></a><a style="text-decoration:none" href="#nearmisspoint">&#x1f517</a>
Near-miss points</p>
<p class="text">
  Where two or more routes intersect, the routes must have a waypoint. If the coordinats of the waypoints are identical, the graph is connected and the Highway Browser can indicate intersecting routes to ease the navigation through the routes when mapping travels. Near-miss points (NMPs) are waypoints very close together. They should be checked whether they are candidates to merge to fix broken intersecting links, and broken concurrencies.
  </br>
  <a href="../logs.php#nmplogs">NMP files</a> can also be loaded into HDX to visualize their positions on a map. The desired NMP file cannot be selceted directly but needs to be downloaded first.
  </br>
  </br>
  The best practise to check NMPs, is as follows:
  </br>
  <ul>
    <li>Open the <a href="http://travelmapping.net/logs/nmpbyregion/">NMP files filtered by region</a> directory.</li>
    <li>Select the nmp file for the region you want to check.</li>
    <li>Open the file.</li>
    <ul>
      <li>If it is blank, the region has no NMPs.</li>
    </ul>
    <li>If there are entries, download the file.</li>
    <li>Open the <a href="http://courses.teresco.org/metal/hdx/">Highway Data Examiner</a> (HDX).</li>
    <li>Go to <code>Option 3</code> on the left and select the downloaded nmp file.</li>
    <ul>
      <li>You can see your region with some colored dots on the map now. These are NMP hotspots.</li>
    </ul>
    <li>Zoom-in to investigate the points. Use the table on the right to go through the NMP hotspots.</br>Since all pairs of all involved routes are reported, very often more than just one line of the table on the right corresponds to a NMP hotspot.</li>
    <ul>
      <li><green>Green</green> dots are those which are already marked FP.</li>
      <li><yellow>Yellow</yellow> dots are off by exactly <code>0.000001°</code>. This is likely intentional to brake concurrencies and the waypoints are candidates to be marked FP.</li>
      <li><red>Red</red> dots must be checked more detailed. These are most likely broken concurrencies or intersecting routes where the waypoints do not match.</li>
    </ul>
    <li>Click on the NMP hotspot lines or their endpoints to get info about the involved routes and waypoint labels.</li>
    <li>Since you only see the points but not the whole network graph, you might need to open another HDX instance on load the region graph from <code>Option 1</code> where you can get the whole picture. To figure out which routes should intersect, what's going on there etc. For instance, it's possible that concurrent routes are only broken on a very short segment you don't see (or missed) with the <code>Option 1</code> view style.</li>
    <li>Fix the coordinates in the corresponding wpt files.</li>
    <li>Load the changed wpt files into the <a href="/wptedit/">WPT file editor</a> to avoid causing unintended <a href="#list">data errors</a>.</li>
  </ul>
</p>


<p class="heading"><a name="nmpfp"></a><a style="text-decoration:none" href="#nmpfp">&#x1f517</a>
Marking NMPs FP</p>

<p class="text">
  Marking NMPs false positive is trickier than for simple data errors but works the same way.
  <ul>
    <li>The <code>FP Entry to Submit</code> can only be found in <a href="../../logs/nearmisspoints.log">nearmisspoints.log</a> which contains all NMP entries including those which are already marked FP. The list is sorted by region code.
    <ul>
      <li>Best practise is to use the browser search function to find <code>lat</code> or <code>lon</code> coordinates (or route code) which one can copy from the HDX table.</li>
      <li>There are minimum two FP entries per hotspot but very often even more, please refer to the issue with multiple lines on the HDX table mentioned above.</li>
    </ul>
    <li>The entry must be added to <a href="https://github.com/TravelMapping/HighwayData/blob/master/nmpfps.log">nmpfps.log</a>.</li>
    <ul>
      <li>The list is sorted by region codes. Insert your entry at the right position.</li>
      <li>If the entry ends with <code>[LOOKS INTENTIONAL]</code>, this part of the entry must be removed.</li>
      <li>If not all entries for a NMP hotspot have previously been marked FP, only the missing entries need to be added. Entries ending with <code>[MARKED FP]</code> are already entered as FP.</li>
    </ul>
  </ul>
</p>

<p class="text">
  <ul>
    <li>Do not forget checking for <a href="../../logs/nmpfpsunmatched.log">unmatched NMP FPs</a>. Remove them from <a href="https://github.com/TravelMapping/HighwayData/blob/master/nmpfps.log">nmpfps.log</a>.</li>
  </ul>
</p>


<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
