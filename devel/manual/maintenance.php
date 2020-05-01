<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Mapping Manual: Maintenance of Highway Data</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: Maintenance of Highway Data</h1>

<p class="text">
  Highway systems in TM are categorized in one of three groups,
  depending on its level of completeness and the maintainers'
  confidence in its accuracy:
</p>

<div class="text">
  <ul>
    <li><b>Active</b> systems are those which we believe are accurate and complete.</li>
    <li><b>Preview</b> systems are substantially complete, but still
    undergoing a peer review and/or final revisions.</li>
    <li><b>In-Development</b> systems are not yet complete.</li>
  </ul>
</div>

<p class="text">
  Systems in development are not included in user maps and stats, and
  can be modified as their developes see fit.  Preview systems are
  included in maps and stats, but changes may be made as needed to
  improve the system as it completes its review.  For active systems,
  care is taken to ensure that changes do not "break" a user's list
  file.  In many cases, however, the changes needed will break user
  lists.  In those situations, changes must be logged in
  the <a href="https://github.com/TravelMapping/HighwayData/blob/master/updates.csv">updates
  list</a>, which is
  available <a href="http://travelmapping.net/devel/updates.php">on
  the updates page</a> to help users keep their list files accurate.
</p>

<p class="heading">Which changes to activated routes are newsworthy
  (need an updates entry)?</p>

<div class="text">
<ul>
  <li><span class="postbody"><span style="font-weight: bold;">Waypoint label is wrong. (Not newsworthy)</span> <br />
      <br />
Waypoint P1 is mislabeled but at the correct location. It should be NewP1 instead. <br />
<br />
Check
the <a href="http://travelmapping.net/logs/pointsinuse.log">points in
use log</a>. <br />
<br />
If the label is not in use: <br />
Simply change P1 to NewP1. The line is: NewP1 [url]<br />
<br />
If the label is in use: <br />
Put the new label first in the line, and add a + to the old label: NewP1 +P1 [url]. The coordinates should remain identical. <br />
+P1 is now an alternate (= deprecated) label and is hidden in the HB, 
but it can still be used by everyone who is trying to use it. The 
correct label NewP1 is the new primary label will appear in the HB for 
future use for the same point. </span></li>
  <li><span class="postbody"><span style="font-weight: bold;">An actual route was truncated. (Newsworthy)</span> <br />
      <br />
You need to truncate the route by the deleting the waypoints that are no
 longer part of the route. This action will break the route for anyone 
using the removed points, but there is no way around it. <br />
<br />
Possibly there will be the need to change the label of the new end waypoint. See "Waypoint label is wrong." above. </span></li>
  <li><span class="postbody"><span style="font-weight: bold;">An actual route was extended. (Newsworthy)</span> <br />
      <br />
Adding new waypoints can be done without worrying about breaking the route for anyone using it. <br />
<br />
Possibly there will be the need to change the label of the old end waypoint. See "Waypoint label is wrong." above. </span></li>
  <li><span class="postbody"><span style="font-weight: bold;">Part of an actual route was relocated. (Newsworthy)</span> <br />
      <br />
To update the route, you need to remove some waypoints in the middle of 
the file and insert new ones there. Anyone using a removed waypoint will
 have the route break for them, but it is unavoidable. <br />
<br />
Possibly there will be the need to change the labels of the two "pivot 
waypoints", where the old and new alignments meet. This should be done 
without breaking the route for anyone. See "Waypoint label is wrong." 
above. </span></li>
  <li><span class="postbody"><span style="font-weight: bold;">One route 
was newsworthily changed (extended/truncated/relocated) and begs for a 
now- or previously intersecting route's waypoint to change.</span> <br />
<br />
See "Waypoint label is wrong." above. </span></li>
  <li><span class="postbody"><span style="font-weight: bold;">A route has new exit numbers, so many of its waypoints should be relabeled with the new exit numbers. (Possibly newsworthy)</span> <br />
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
  <li><span class="postbody"><span style="font-weight: bold;">An intersection was closed.</span> <br />
      <br />
Add an asterisk(*) at the beginning of the waypoint and leave it 
otherwise unchanged. If the point 37 was closed, change it to *37. Any 
.list file using "37" or "*37" will use this point, since asterisks are 
ignored when matching waypoints in .list files with waypoints in the HB.
 Note that if you give a route both of the points 37 and *37, this is a 
      duplicated label error that needs to be corrected. </span></li>

  <ul>
  </div>
  
  <p class="subheading">FAQs:</p>

  <p class="text">
Q: I found a route where most of the labels are correct but are applied 
to the wrong waypoints. To fix it, I should use many of the same labels 
but shift them to the correct waypoints. Since that fix will alter but 
not really break most of the points in use, can I just reenter the whole
 route and forget about hiding old points? <br />
A: Probably. Mention the problem in this forum so we can make sure that breaking the route is warranted. It might be needed. <br />
<br />
Q: I want to add a new system of highways to a region, and I found some 
changes to make to an activated system in the same region. Since I 
expect users to update their .list file with the new highways, can I 
break those activated highways at the same time rather than hiding used 
points I changed? <br />
A: No. Adding a new highway system shouldn't be an excuse to break routes in another system. <br />
<br />
Q: Don't you think you're way too gung-ho about not breaking routes? I'm
 only making changes one or twice a year that would break only a route 
or two, so it's not a burden for the users to update their files once in
 awhile. It should be fine to "unnecessarily" break a small number of 
routes infrequently. <br />
A; No. There are more than 10 of you sporadically (and sometimes, 
frequently) making minor changes, and without hiding copies of used 
waypoints, that adds up to breaking routes every few weeks, sometimes 
even more often. Users shouldn't have to make frequent changes to 
1000-line files every few weeks when the changes don't reflect real 
changes to highway alignments or real changes to the extent of their 
travels. <br />
<br />
Q: Is there a preference for the order of hidden/visible pairs of same-location waypoints? <br />
A: The visible label should always come first. <br />
<br />
Q: Can I delete unused hidden points with a visible companion point? <br />
A: If you have verified that a hidden point isn't in use (we
have <a href="http://travelmapping.net/logs/unusedaltlabels.log">a log
      file for that</a>) and that there 
is a visible point at the same location, then you can delete the unused 
hidden point. Just make sure you don't accidentally remove a shaping 
point or a used point or a not-duplicated point. <br />
<br />
Q: I made a necessary change that will unavoidably break a route for 
some users. So would you include "Update your .list file if you've 
included any part of this route." with the entry on the Updates page? <br />
A: No. That note is implied for most or all entries on that page, so it's not worth mentioning on any individual basis. </span>
</p>

<p class="heading">Format for reporting newsworthy changes</p>

<p class="text">Please include updates entries when you
  make changes to the data files. Don't let the changes get lost in the
  forum or elsewhere! </p>

<div class="text">
<p>
  Updates are included in CSV format, so be sure to include all
fields.  The last field is the description, and should be in plain
English.  Keep them concise but just specific enough for someone to
understand in the HB since they can't compare with the old
route.  Examples of common changes are here: <br />
<br />
New route: <br />
Pennsylvania I-67: Added route. <br />
<br />
Deleted route: <br />
England M1: Deleted route. <br />
<br />
Extended route: <br />
Pennsylvania I-99: Extended northward from Exit 52 (PA 350) near Bald Eagle to MusLn (Musser Lane) near Bellefonte. <br />
(mentions intersections at old and new ends) <br />
<br />
Truncated route: <br />
Maryland US 15 Business (Emmitsburg): Truncated from the old south end at US 15 to the new end at Main St in Emmitsburg. <br />
(This mentions intersections at old and new ends. As you compose lines 
like this one, remember that highways can be truncated, but end points 
cannot.) <br />
<br />
Relocated route (in middle of route): <br />
Pennsylvania US 220: Removed from Main Street and 5th Avenue, and 
relocated onto a new northern Georgetown bypass, between US 23 and PA 
70. 
<br />
(mentions both ends of the new part, that is, the intersections/places 
where the old and new alignments meet) and both the old and new routes <br />
<br />
Relocated route (at end of route): <br />
Pennsylvania US 220: Removed from Main Street and 5th Avenue between 4th
 Street and 9th Street, and relocated onto a new northern Georgetown 
bypass between 4th Street  and PA 70. 
<br />
(mentions both the new and old routings as well as the bounding intersections of each)<br />
<br />
Changed label or recycled label: <br />
Ontario ON 444: Changed waypoint labels 51 to 52 and 52 to 53. <br />
(mention old and new waypoints for a small number of changes) <br />
New York I-490: Reentered route with corrected waypoint labels. <br />
(if there are too many changes to mention individually) <br />
<br />
You can also mention actual waypoints labels where skipping them isn't clear. 
<br /><br />
Avoid the following in the entries:
<ul>
<li>Don't refer to "the old route" or "the old end". Instead, say what the old route or end was.</li>
<li>Don't refer to "the new route" or "the new end". Instead, say what the new route is.</li>
<li>Don't use vague phrases like "the correct location", "onto new construction". Instead, say what the location or highway is.</li>
<li>Wherever possible, avoid describing old and new routings by highway 
names that changed.  Use stable highway names if they are available, or 
describe the type and location of the highway.</li>
</ul>
</span></li>
</ul>

</div>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
