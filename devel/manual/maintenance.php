<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping Manual: Maintenance of Highway Data</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: Maintenance of Highway Data</h1>

<p class="heading">
  Contents</p>

<div class="text">
<ul>
  <li><a href="#maintain">How to maintain a region?</a></li>
  <li><a href="#newsworthy">Which changes to activated routes are newsworthy?</a></li>
  <ul>
    <li><a href="#faq">FAQs</a></li>
  </ul>
  <li><a href="#newsreport">Format for reporting newsworthy changes</a></li>
  <ul>
    <li><a href="#newsexamples">Examples of common changes</a></li>
  </ul>
</ul>
</div>

<p class="heading"><a name="maintain"></a><a style="text-decoration:none" href="#maintain">&#x1f517</a>
How to maintain a region?</p>

<div class="text">
<ul>
  <li><a name="maintainer"></a><a style="text-decoration:none" href="#maintainer">&#x1f517</a>
  Regions are maintained by our volunteer highway data managers.
  Please refer to the <a href="https://forum.travelmapping.net/index.php?topic=42">forum</a> to see who is responsible for what region.</li>
  <li><a name="reporter"></a><a style="text-decoration:none" href="#reporter">&#x1f517</a>
  Everyone can report required changes on the <a href="https://forum.travelmapping.net/index.php?topic=20">forum</a>.
  The responsible highway data manager should frequently check the <a href="https://forum.travelmapping.net/index.php?board=3">board</a> for open issues.</li>
  <li><a name="check"></a><a style="text-decoration:none" href="#check">&#x1f517</a>
  In addition, the highway data manager should check official sources or forums proactive to get info about relevant changes.</li>
  </br>
  <li><a name="break"></a><a style="text-decoration:none" href="#break">&#x1f517</a>
  Care must be taken to ensure that changes do not "break" a user's list file.</li>
  <ul>
    <li>If a <a href="/logs/pointsinuse.log">waypoint label is in use by current TM users</a> we should
    <a href="#labelwrong">add alternative labels</a> if possible.</li>
    <li>If a <a href="/logs/listnamesinuse.log">.list name label is in use by current TM users</a> we should
    <a href="syshwylist.php#caltroute">add alternative route names</a> if possible.</li>
    <li>In many cases, however, the changes needed will break user lists.
    In those and other situations, changes are <a href="#newsworthy">newsworthy</a> and require an <a href="#newsreport">updates entry</a>
    for <span style="background-color: #CCFFCC;">active</span> systems.</li>
  </ul>
  </br>
  <li><a name="errorcheck"></a><a style="text-decoration:none" href="#errorcheck">&#x1f517</a>
  When you have submitted changes, <a href="sysnew.php#datacheck">check for errors</a> after the next site update.</li>
  </br>
  <li><a name="cleanupunused"></a><a style="text-decoration:none" href="#cleanupunused">&#x1f517</a>
  Clean-up the alternative labels and .list names if they are no longer used</li>
  <ul>
    <li>Alternate (i.e., hidden) <a href="/logs/unusedaltlabels.log">waypoint labels not in use by current TM users</a>
    can safely be removed from <code>.wpt</code> files.</li>
    <li>Alternate (i.e., hidden) <a href="/logs/unusedaltroutenames.log">route names not in use by current TM users</a>
    can safely be removed from <code>.csv</code> files.</li>
  </ul>
  <li><a name="cleanupfps"></a><a style="text-decoration:none" href="#cleanupfps">&#x1f517</a>
  Clean-up the <a href="syserr.php#falsepositive">unmatched FPs</a>.</li>
  <li><a name="cleanupnmpfps"></a><a style="text-decoration:none" href="#cleanupnmpfps">&#x1f517</a>
  Clean-up the <a href="syserr.php#nmpfp">unmatched NMP FPs</a>.</li>
</ul>
</div>

<p class="heading"><a name="newsworthy"></a><a style="text-decoration:none" href="#newsworthy">&#x1f517</a>
Which changes to activated routes are newsworthy?</p>

<div class="text">
<ul>
  <li><a name="labelwrong"></a><a style="text-decoration:none" href="#labelwrong">&#x1f517</a>
    <span class="postbody"><span style="font-weight: bold;">Waypoint label is wrong (<span style="background-color: #CCFFCC;">Not newsworthy</span>)</span> <br />
      <br />
      Example: Waypoint <code>P1</code> is mislabeled but at the correct location. It should be <code>NewP1</code> instead. <br />
      <br />
      Check the <a href="/logs/pointsinuse.log">points in use log</a> manually or
      load the corresponding line to the <code>.wpt</code> file into the <a href="/wptedit/">Waypoint File Editor</a>.<br />
      If there is no entry for the <code>.wpt</code> file at all, no label of the route is in use.<br/>
      <br />
      <ul>
        <li><b>If the label is not in use:</b> <br />
        Simply change <code>P1</code> to <code>NewP1</code>. The line is: <code>NewP1 [url]</code></li>
        <br />
        <li><b>If the label is in use:</b> <br />
        Put the new label first in the line, and add a <code>+</code> to the old label: <code>NewP1 +P1 [url]</code>. The coordinates should remain identical. <br />
        <code>+P1</code> is now an alternate (= deprecated) label and is hidden in the HB,
        but it can still be used by everyone who is trying to use it. The 
        correct label <code>NewP1</code> is the new primary label will appear in the HB for 
        future use for the same point.</span></li>
    </ul>
  </li>
  <br/>
  <li><a name="newroute"></a><a style="text-decoration:none" href="#newroute">&#x1f517</a>
    <span class="postbody"><span style="font-weight: bold;">A new route was added (<span style="background-color: #FFCCCC;">Newsworthy</span>)</span> <br />
      <br />
      New routes are always reported so that user can check whether they need to add the route.
  </li>
  <br/>
  <li><a name="delroute"></a><a style="text-decoration:none" href="#delroute">&#x1f517</a>
    <span class="postbody"><span style="font-weight: bold;">A new route was deleted (<span style="background-color: #FFCCCC;">Newsworthy</span>)</span> <br />
      <br />
      The deletion of a route is always reported even when it was not traveled by any TM user.
  </li>
  <br/>
  <li><a name="truncated"></a><a style="text-decoration:none" href="#truncated">&#x1f517</a>
    <span class="postbody"><span style="font-weight: bold;">An actual route was truncated (<span style="background-color: #FFCCCC;">Newsworthy</span>)</span> <br />
      <br />
You need to truncate the route by the deleting the waypoints that are no
 longer part of the route. This action will break the route for anyone 
using the removed points, but there is no way around it. <br />
<br />
Possibly there will be the need to change the label of the new end waypoint. Please refer to <a href="#labelwrong">Waypoint label is wrong</a>.</span></li>
  <br/>
  <li><a name="extended"></a><a style="text-decoration:none" href="#extended">&#x1f517</a>
    <span class="postbody"><span style="font-weight: bold;">An actual route was extended (<span style="background-color: #FFCCCC;">Newsworthy</span>)</span> <br />
      <br />
Adding new waypoints can be done without worrying about breaking the route for anyone using it. <br />
<br />
Possibly there will be the need to change the label of the old end waypoint. Please refer to <a href="#labelwrong">Waypoint label is wrong</a>. </span></li>
  <br/>
  <li><a name="relocated"></a><a style="text-decoration:none" href="#relocated">&#x1f517</a>
    <span class="postbody"><span style="font-weight: bold;">Part of an actual route was relocated (<span style="background-color: #FFCCCC;">Newsworthy</span>)</span> <br />
      <br />
To update the route, you need to remove some waypoints in the middle of 
the file and insert new ones there. Anyone using a removed waypoint will
 have the route break for them, but it is unavoidable. <br />
<br />
Possibly there will be the need to change the labels of the two "pivot 
waypoints", where the old and new alignments meet. This should be done 
without breaking the route for anyone. Please refer to <a href="#labelwrong">Waypoint label is wrong</a>. </span></li>
  <br/>
  <li><a name="intersectchange"></a><a style="text-decoration:none" href="#intersectchange">&#x1f517</a>
    <span class="postbody"><span style="font-weight: bold;">One route 
was newsworthily changed (extended/truncated/relocated) and begs for a 
now- or previously intersecting route's waypoint to change  (<span style="background-color: #CCFFCC;">Not newsworthy</span>)</span></span> <br />
<br />
Please refer to <a href="#labelwrong">Waypoint label is wrong</a>. </span></li>
  <br/>
  <li><a name="exitnumbers"></a><a style="text-decoration:none" href="#exitnumbers">&#x1f517</a>
    <span class="postbody"><span style="font-weight: bold;">A route has new exit numbers, so many of its waypoints should be relabeled with the new exit numbers (<span style="background-color: #FFFFCC;">Possibly newsworthy</span>)</span> <br />
      <br />
If none of the new exit numbers are the same as the old ones, then this 
update can be accomplished without breaking any routes for anyone. Hide 
copies of the old waypoints that are in use so that they continue to 
function, and delete the old waypoints that are not in use. Add the new 
waypoints. (Not newsworthy) <br />
<br />
If some of the old and new exit numbers are identical, then some route 
breaking may be necessary. Proceed in the same way to minimize the 
impact.  You can't retain old labels as alternate labels if they must 
become the primary label of a different waypoint.  (Newsworthy) <br />
<br />
Example: waypoints <span style="color: blue;">74</span>, <span style="color: green;">75</span>, <span style="color: darkred;">76</span> become <span style="color: blue;">1</span>, <span style="color: green;">2</span>, <span style="color: darkred;">3</span>; only 74 and 76 are in use: <br />
New file: <span style="color: blue;">1</span> <span style="color: blue;">+74</span>, <span style="color: green;">2</span>, <span style="color: darkred;">3</span> <span style="color: darkred;">+76</span>. <br />
74 and 76 were retained as alternate labels because they were in use. <br />
<br />
Example: waypoints <span style="color: blue;">74</span>, <span style="color: green;">75</span>, <span style="color: darkred;">76</span> become <span style="color: blue;">76</span>, <span style="color: green;">77</span>, <span style="color: darkred;">78</span>; only 74 and 76 are in use: <br />
New file: <span style="color: blue;">76</span> <span style="color: blue;">+74</span>, <span style="color: green;">77</span>, <span style="color: darkred;">78</span> <br />
75 was removed instead of demoted because no one was using it. <br />
76 is now used for the former 74 point and no longer represents its former location. <br />
The change to point 76 becomes "newsworthy". <br />
<br />
Sometimes in the latter case, a second option can be chosen for the 
duplicated point (76) that causes the small problem. If new interchange 
76 is in use has A and B exits, then this is preferable: <br />
New file: <span style="color: blue;">76A</span> <span style="color: blue;">+74</span>, <span style="color: green;">77</span>, <span style="color: darkred;">78</span> <span style="color: darkred;">+76</span>. </span></li>
  <br/>
  <li><a name="closed"></a><a style="text-decoration:none" href="#closed">&#x1f517</a>
    <span class="postbody"><span style="font-weight: bold;">An intersection was closed  (<span style="background-color: #CCFFCC;">Not newsworthy</span>)</span></span> <br />
      <br />
Add an asterisk(*) at the beginning of the waypoint and leave it 
otherwise unchanged. If the point 37 was closed, change it to *37. Any 
.list file using "37" or "*37" will use this point, since asterisks are 
ignored when matching waypoints in .list files with waypoints in the HB.
 Note that if you give a route both of the points 37 and *37, this is a 
      duplicated label error that needs to be corrected. </span></li>

  <ul>
  </div>
  
  <p class="subheading"><a name="faq"></a><a style="text-decoration:none" href="#faq">&#x1f517</a>
  FAQs:</p>

<div class="text">
<ul>
<li><a name="labelshift"></a><a style="text-decoration:none" href="#labelshift">&#x1f517</a>
Q: I found a route where most of the labels are correct but are applied 
to the wrong waypoints. To fix it, I should use many of the same labels 
but shift them to the correct waypoints. Since that fix will alter but 
not really break most of the points in use, can I just reenter the whole
 route and forget about hiding old points? <br /> <br />
A: Probably. Mention the problem in this forum so we can make sure that breaking the route is warranted. It might be needed.</li> <br />
<li><a name="newsys"></a><a style="text-decoration:none" href="#newsys">&#x1f517</a>
Q: I want to add a new system of highways to a region, and I found some 
changes to make to an activated system in the same region. Since I 
expect users to update their .list file with the new highways, can I 
break those activated highways at the same time rather than hiding used 
points I changed? <br /> <br />
A: No. Adding a new highway system shouldn't be an excuse to break routes in another system.</li> <br />
<li><a name="gung-ho"></a><a style="text-decoration:none" href="#gung-ho">&#x1f517</a>
Q: Don't you think you're way too gung-ho about not breaking routes? I'm
 only making changes one or twice a year that would break only a route 
or two, so it's not a burden for the users to update their files once in
 awhile. It should be fine to "unnecessarily" break a small number of 
routes infrequently. <br /> <br />
A; No. There are more than 10 of you sporadically (and sometimes, 
frequently) making minor changes, and without hiding copies of used 
waypoints, that adds up to breaking routes every few weeks, sometimes 
even more often. Users shouldn't have to make frequent changes to 
1000-line files every few weeks when the changes don't reflect real 
changes to highway alignments or real changes to the extent of their 
travels.</li> <br />
<li><a name="updatenote"></a><a style="text-decoration:none" href="#updatenote">&#x1f517</a>
Q: I made a necessary change that will unavoidably break a route for 
some users. So would you include "Update your .list file if you've 
included any part of this route." with the entry on the Updates page? <br /> <br />
A: No. That note is implied for most or all entries on that page, so it's not worth mentioning on any individual basis.</li> <br />
</ul>
</div>

<p class="heading"><a name="newsreport"></a><a style="text-decoration:none" href="#newsreport">&#x1f517</a>
Format for reporting newsworthy changes</p>

<div class="text">Newsworthy changes must must be logged in the <a href="https://github.com/TravelMapping/HighwayData/blob/master/updates.csv">updates list</a>,
which is  available <a href="/devel/updates.php">on the updates page</a> to help users keep their list files accurate.
The list is ordered by regions. Add a new line at the beginning of the right region.
Don't let the changes get lost in the forum or elsewhere!</br>
</br>
Updates are included in <code>.csv</code> format, so be sure to include all
fields.  The last field is the description, and should be in plain
English.  Keep them concise but just specific enough for someone to
understand in the <a href="/hb/">Highway Browser</a> since they can't compare with the old
route.</br></br></div>


<div class="text"><a name="updatesformat"></a><a style="text-decoration:none" href="#updatesformat">&#x1f517</a>
The format of the <a href="https://github.com/TravelMapping/HighwayData/blob/master/updates.csv">updates list file</a> is as follows:
    <pre>
date;region;route;root;description</br>
...
2016-03-12;Albania;A1 (Thumane);alb.a001thu;New Route
2016-03-12;Albania;A3;;Route Deleted
...
2017-08-15;(Canada) New Brunswick;NB 565;nb.nb565;Route added
...
      </pre>
The <code>date</code> refers to the day the update was made to TM. It is not the date when the actual change happened on-site.</br>
The <code>region</code> column represents the country name. If it is a multi-region country, the country name is put in brackets followed by the region name.</br>
The <code>route</code> is the full route name.</br>
The <code>root</code> is the combination of region code and route code used in the HB.</br></br>
</div>

<p class="subheading"><a name="newsexamples"></a><a style="text-decoration:none" href="#newsexamples">&#x1f517</a>
Examples of common changes</p>

<div class="text">
<ul>
  <li><a name="newsnew"></a><a style="text-decoration:none" href="#newsnew">&#x1f517</a>
  <b>New route:</b> <br />
  <code>Added route.</code></br>
  <code>New route.</code></br>
  <code>Route added.</code></li><br />
  <li><a name="newsdel"></a><a style="text-decoration:none" href="#newsdel">&#x1f517</a>
  <b>Deleted route:</b> <br />
  <code>Deleted route.</code></br>
  <code>Route removed.</code></li><br />
  <li><a name="newsext"></a><a style="text-decoration:none" href="#newsext">&#x1f517</a>
  <b>Extended route:</b> <br />
  <code>Extended northward from Exit 52 (PA 350) near Bald Eagle to MusLn (Musser Lane) near Bellefonte.</code> <br />
  Mentions intersections at old and new ends.</li><br />
  <li><a name="newstru"></a><a style="text-decoration:none" href="#newstru">&#x1f517</a>
  <b>Truncated route:</b> <br />
  <code>Truncated from the old south end at US 15 to the new end at Main St in Emmitsburg.</code> <br />
  Mentions intersections at old and new ends. As you compose lines 
  like this one, remember that highways can be truncated, but end points 
  cannot.</li><br />
  <li><a name="newsrel"></a><a style="text-decoration:none" href="#newsrel">&#x1f517</a>
  <b>Relocated route (in middle of route):</b> <br />
  <code>Removed from Main Street and 5th Avenue, and 
  relocated onto a new northern Georgetown bypass, between US 23 and PA 
  70.</code>
  <br />
  Mentions both ends of the new part, that is, the intersections/places 
  where the old and new alignments meet) and both the old and new routes.</li><br />
  <li><a name="newsrelend"></a><a style="text-decoration:none" href="#newsrelend">&#x1f517</a>
  <b>Relocated route (at end of route):</b> <br />
  <code>Removed from Main Street and 5th Avenue between 4th
  Street and 9th Street, and relocated onto a new northern Georgetown 
  bypass between 4th Street  and PA 70. </code>
  <br />
  Mentions both the new and old routings as well as the bounding intersections of each.</li><br />
  <li><a name="newslabel"></a><a style="text-decoration:none" href="#newslabel">&#x1f517</a>
  <b>Changed label or recycled label:</b> <br />
  <code>Changed waypoint labels 51 to 52 and 52 to 53.</code> <br />
  Mention old and new waypoints for a small number of changes. <br /><br />
  <code>Reentered route with corrected waypoint labels.</code> <br />
  If there are too many changes to mention individually. <br />
  <br />
  You can also mention actual waypoints labels where skipping them isn't clear.</li><br />
  <br />
  <li><a name="news"></a><a style="text-decoration:none" href="#newsavoid">&#x1f517</a>
  <b>Avoid the following in the entries:</b>
  <ul>
    <li>Don't refer to <code>the old route</code> or <code>the old end</code>. Instead, say what the old route or end was.</li>
    <li>Don't refer to <code>the new route</code> or <code>the new end</code>. Instead, say what the new route is.</li>
    <li>Don't use vague phrases like <code>the correct location</code>, <code>onto new construction</code>. Instead, say what the location or highway is.</li>
    <li>Wherever possible, avoid describing old and new routings by highway 
    names that changed.  Use stable highway names if they are available, or 
    describe the type and location of the highway.</li>
  </ul>
</ul>
</div>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
