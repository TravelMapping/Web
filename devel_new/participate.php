<?php require $_SERVER['DOCUMENT_ROOT'] . "/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping Manual: How to participate in the project</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1 style="color:red">Travel Mapping Manual: How to participate in the project - <i>Draft</i></h1>

<p class="heading">
  Contents</p>

<div class="text">
<ul>
  <li><a href="#userlistfile">How to create a user list file</a></li>
  <ul>
    <li><a href="#firststeps">First steps with email submission</a></li>
    <li><a href="#problems">Solutions to common problems</a></li>
    <li><a href="#examples">More examples of how to break down your travels</a></li>
    <li><a href="#advanced">Advanced features</a></li>
  </ul>
  <li><a href="#hwydatamanager">How to become a highway data manager</a></li>
</ul>
</div>

<p class="heading"><a name="userlistfile"></a><a style="text-decoration:none" href="#userlistfile">&#x1f517</a>
How to create a user list file</p>

<div class="text">
  The basic idea is to make a list of highway sections you have traveled and collect them into a <em>plain text file</em>
  to be submitted to Travel Mapping. The text file will be 
  processed to populate a set of custom stats pages and maps 
  describing your travels.</br>
  </br>
  For examples, you can browse the <a href="http://travelmapping.net/stat.php">travel summaries</a> of any of our users.
</div>

<p class="subheading"><a name="firststeps"></a><a style="text-decoration:none" href="#firststeps">&#x1f517</a>
First steps with email submission</p>
<div class="text" >
  <ol>
    <li><a name="username"></a><a style="text-decoration:none" href="#username">&#x1f517</a>
      <b>Choose an alphanumeric username.</b></br>
      </br>
      Use only English letters (<code>A-Z</code> or <code>a-z</code>), numbers (<code>0-9</code>), and underscores <code>_</code> in your username,
      and keep the name at or under 48 characters long. Avoid characters with diacritical marks,
      and characters from other alphabets; otherwise your file will not be processed.
    </li>
    </br>
    <li><a name="plainfile"></a><a style="text-decoration:none" href="#plainfile">&#x1f517</a>
      <b>Make a plain text file.</b></br>
      </br>
      Name the file <code>username.list</code>, replacing <code>username</code> with the username you chose.
      For example, if you choose the name <code>highwayguy</code>, name your file <code>highwayguy.list</code>.
    </li>
    </br>
    <li><a name="userentry"></a><a style="text-decoration:none" href="#userentry">&#x1f517</a>
      <b>Make a list of highway sections you have traveled.</b></br>
      </br>
      The <a href="/hb/">Highway Browser</a> is designed to help you with this process.
      The Highway Browser lists all available systems and routes that you can use.
      For each route,
      the Highway Browser also provides a list of <i>waypoints</i> that you can use as a start or end point.</br>
      </br>
      
      Break up your travels into traveled sections by region and route (we recommend starting with only
      <a href="manual/sysdef.php#active"><span style="background-color: #CCFFCC;">active</span> systems</a>).
      For each route section, add one line to your <code>.list</code> file with the following format:</br>
      </br>
      <code>Region Route Waypoint1 Waypoint2</code></br>
      </br>
      For example, if you traveled in the United States on Interstate 70 in Illinois between Exit 52 and the Missouri border, you would put the following line in your file:</br>
      </br>
      <code>IL I-70 52 MO/IL</code></br>
      </br>
      <ul>
        <li>The region and route combination is indicated on the left after <code>.list name:</code> in the <a href="/hb/index.php?r=il.i070">Highway Browser</a>.</li>
        <li>Click the start point <code>52</code> of your travel segment on the table
        to center the map at this point.</li>
        <li>Copy the first line of the info window to get the correct name <code>52</code>.</li>
        <li>Click on the end point <code>MO/IL</code> or click the waypoint on the map
        to open the info window and copy the label name <code>MO/IL</code>.</li>
        <li>The waypoints at the ends of the traveled segment may be listed in either order.</li>
      </ul></br>
      You most likely continued your travel in Missouri. If you left Interstate 70 at Exit 249, you would put the second line in your file:</br>
      </br>
      <code>MO I-70 MO/IL 249</code></br>
      </br>
      <ul>
        <li>Click the <code>MO I-70</code> link under <code>Intersecting/Concurrent Routes:</code> on the info window to open
        the Missouri continuation of Interstate 70 within the Highway Browser.</li>
        <li>Copy the code behind <code>.list name:</code> for your second line in your
        <code>.list</code> file.</li>
        <li>Then click on the border waypoint to catch the first waypoint,
        and on exit 249 to get the second waypoint.</br>
        </li>
      </ul>
      </br>
      Continue to complete your travels. <a href="#examples">See below</a> if you need more examples on how to break down your travels into "sections" to enter into the file.</br>
      </br>
      Note that a mechanism is in place to automatically include concurrent highways and credits you for all of them.</br>
      </br>
      We recommend starting with a small number of routes and submitting an initial draft to be
      included in the site update to make sure that you understood the procedure well.</br>
    </li>
    </br>
    <li><a name="checkwork"></a><a style="text-decoration:none" href="#checkwork">&#x1f517</a>
      <b>Check your work.</b></br>
      </br>
      Make sure the following items are correct:</br>
      </br>
      <ul>
        <li><b>Each line has exactly the four required fields:</b> region, highway, and two waypoint labels.</li>
        <li><b>The fields have only spaces or tabs between them.</b> Other
        delimiters may prevent the mapping script from parsing your file correctly.</li>
        <li><b>The file is saved as plain text with a <code>.list</code> extension.</b> Word processor files (Microsoft Word,
        Open Office Write, etc.) and rich-text formats will not work. If you use a word processor to create your file,
        be sure to select "Save As..." and save the file in a plain text format.</li>
      </ul>
    </li>
    </br>
    <li><a name="emailsubmission"></a><a style="text-decoration:none" href="#emailsubmission">&#x1f517</a>
      <b>Send your <code>.list</code> file by email to <code>travmap@teresco.org</code> destination.
      Mention in the subject line that your username is a new one.</b></br>
      </br>
      You have now finished your part! Your file will be included in the next site update, which typically occurs nightly between 9 and 11 PM US/Eastern.</br></i></span></li>
    </br>
    <li><a name="aftersiteupdate"></a><a style="text-decoration:none" href="#aftersiteupdate">&#x1f517</a>
      <b>After the next site update, look for your name on the <a href="http://travelmapping.net/stat.php">Traveler List</a>.</b></br>
      </br>
      Once your file has been processed, your name will appear in this list along with all the other travelers.
      Click on your username and enjoy your traveled highway stats and maps.</br>
      Lots of stats are available in csv files linked from <a href="/stats/">http://travelmapping.net/stats/</a>.</li>
    </br>
    <li><a name="errorlog"></a><a style="text-decoration:none" href="#errorlog">&#x1f517</a>
      <b>Check the user log file.</b></br>
      </br>
      Check your <a href="/logs/users/">online log file</a> (also directly linked on your user stats pages).
      If you included a highway or point label that the mapping script does not recognize, it will tell you
      in your log file. Sometimes highway data is updated, and this may generate a new error. Check
      <a href="http://travelmapping.net/devel/updates.php">the updates page</a> for info on what has been changed
      to the route. You should also check the updates page frequently to find highway changes like simple relocations
      that don't break your <code>.list</code> file and cannot be reported in your log file.
      Changes to <a href="manual/sysdef.php#preview">systems in preview state</a> are not notified.
    </li>
    </br>
    <li><a name="nextupdate"></a><a style="text-decoration:none" href="#nextupdate">&#x1f517</a>
      <b>Update your file as needed by emailing an updated copy.</b></br>
      </br>
      If you do more traveling, you can update your <code>.list</code> file to reflect the new highways on which you
      have traveled. To submit your updated file, just email it again, and it will be processed in the subsequent site update.</li>
  </ol>
  Travel Mapping's volunteers hope that you enjoy your maps and stats pages from this free service!
</div>


<p class="subheading"><a name="problems"></a><a style="text-decoration:none" href="#problems">&#x1f517</a>
Solutions to common problems</p>
<div class="text" >
  <ol>
    <li><a name="invaliduser"></a><a style="text-decoration:none" href="#invaliduser">&#x1f517</a>
      <b>An invalid username was chosen.</b></br>
      </br>
      Only the characters <code>A-Z</code>, <code>a-z</code>, <code>0-9</code>, and the underscore <code>_</code>
      are allowed in usernames. If you used other
      characters, such as non-English characters or symbols, remove them.
    </li>
    </br>
    <li><a name="namemismatch"></a><a style="text-decoration:none" href="#namemismatch">&#x1f517</a>
      <b>The filename does not match the username.</b></br>
      </br>
      If the username is <code>highwayguy80</code>, then the filename should be <code>highwayguy80.list</code>, not <code>highwayguy80.txt</code>,
      <code>HighwayGuy80.list</code>, <code>arkansas.list</code>, <code>motorways.list</code>, nor other filenames.
      Note that since usernames generally do not
      change, the valid filename also generally does not change.</br>
      </br>
      Due to Mac and Windows operating systems hiding the file extension <code>.list</code> by default and/or including an extra, hidden <code>.txt</code>
      extension, a <code>.list.txt</code> extension is also accepted (e.g., <code>highwayguy80.list.txt</code>). However, users are asked to avoid the
      double extension so that files do not need to be renamed after being submitted.
    </li>
    </br>
    <li><a name="notemailed"></a><a style="text-decoration:none" href="#notemailed">&#x1f517</a>
      <b>The file was not emailed.</b></br>
      </br>
      Check that you attached your file to the email you thought you sent, and check that the email was sent. Files that are not
      received cannot be processed.
    </li>
    </br>
    <li><a name="notprocessed"></a><a style="text-decoration:none" href="#notprocessed">&#x1f517</a>
      <b>The file was sent but has not yet been processed.</b></br>
</br>

Site updates typically occur daily, and the
time of the last update can be found at the bottom of the project's
home page (the most recent update was completed at <?php echo
tm_update_time(); ?> US/Eastern).

  If your file was sent more than about 30 minutes before the most
  recent update, you can check on its status with an additional email
  or simply resend. </br>

      Note that occasionally processing may be delayed.  Any expected delays will be mentioned in the project forum.
    </li>
    </br>
    <li><a name="vanished"></a><a style="text-decoration:none" href="#vanished">&#x1f517</a>
      <b>The new file was processed, but all the highways from the previous file have vanished.</b></br>
      </br>
      The new file should contain all your traveled highways, not just additions to those from a previously submitted file.
      The new file always replaces the previous file rather than supplementing it. This allows users to not only add
      or modify lines in their file but also to delete them as needed.
    </li>
  </ol>
</div>

<p class="subheading"><a name="examples"></a><a style="text-decoration:none" href="#examples">&#x1f517</a>
More examples of how to break down your travels</p>

<div class="text">
Below are several example entries for a user's <code>.list</code> file.</br>
</br>
<ul>
  <li><a name="exsingleregion"></a><a style="text-decoration:none" href="#exsingleregion">&#x1f517</a>
  <b>Highway segment within a single region.</b></br>
  </br>
  For the segment of I-80 between Exits 161 and 224 in Pennsylvania, USA, look up the highway and waypoint
  labels for those interchanges in the <a href="/hb/index.php?r=pa.i080">Highway Browser</a>. The Highway Browser
  shows the highway as <code>PA I-80</code> and lists the waypoints as <code>161</code> and <code>224</code>.
  Enter the following line into your <code>.list</code> file:</br>
  <pre>
PA I-80 161 224
  </pre></li>
  <li><a name="exmultipleregions"></a><a style="text-decoration:none" href="#exmultipleregions">&#x1f517</a>
  <b>Highway segment crossing into multiple regions.</b></br>
  </br>
  To enter the segment of I-81 between Exit 300 in Virginia and Exit 10 in New York, break up the segment into
  smaller pieces by region. The Virginia segment begins at Exit 300 and ends at the West Virginia border, so use
  waypoints <code>300</code> and <code>VA/WV</code> for the Virginia segment. I-81 runs from border to border in
  West Virginia, Maryland, and Pennsylvania, so use waypoints <code>WV/MD</code>, <code>MD/PA</code>, and
  <code>PA/NY</code> for these states. The New York segment begins at the Pennsylvania border and ends at Exit 10,
  so use waypoints <code>PA/NY and 10</code>. All of these waypoints are listed in the Highway Browser. Start with
  <a href="/hb/index.php?r=va.i081">Virgina</a> and click on the intersecting route links to change the regions
  till you've reached New York.</br>
  </br>
  Catch up the entries for your <code>.list</code> which should be read as follows for the whole segment:</br>
  <pre>
VA I-81 300 VA/WV
WV I-81 VA/WV WV/MD
MD I-81 WV/MD MD/PA
PA I-81 MD/PA PA/NY
NY I-81 PA/NY 10
  </pre></li>
  <li><a name="exbeltway"></a><a style="text-decoration:none" href="#exbeltway">&#x1f517</a>
  <b>Beltways (full loop highways) within a single region.</b></br>
  </br>
  In single-region beltways, a waypoint has been selected as both the highway's <i>beginning</i> and <i>end</i>.
  This location is used for both the first and last waypoints of the highway, with a different waypoint label for each end.
  If the segment you wish to enter into your <code>.list</code> file crosses that point, split your segment into two at that point.</br>
  </br>
  Consider first the segment of the Baltimore Beltway (<a href="/hb/index.php?r=md.i695">MD I-695</a>) between Exits
  17 and 31 along the <i>northern</i> side of Baltimore. I-695 begins and ends at a southern waypoint called both 0 and 48.
  Since the waypoints run continuously from 17 to 31 without crossing that southern point (compare to next example),
  treat this segment simply as beginning at waypoint <code>17</code> and ending at waypoint <code>31</code>:</br>
  <pre>
MD I-695 17 31
  </pre>
  Now consider the <i>southern</i> segment of the Baltimore Beltway between the same Exits 17 and 31. The waypoints numbers
  reset in this range: 17, 16, ..., 1, 0, 48, 44, ..., 31. Waypoints 0 and 48 refer to the same point. With this in mind,
  treat your whole segment as two, one with ends at <code>0</code> and <code>17</code>, and the other with ends at
  <code>31</code> and <code>48</code>:</br>
  <pre>
MD I-695 0 17
MD I-695 31 48
  </pre>
  To enter the entire Baltimore Beltway, use the two labels for the common end points, which are the first and last
  labels shown in the Highway Browser:</br>
  <pre>
MD I-695 0 48
  </pre></li>
  <li><a name="exbeltwaymultiregions"></a><a style="text-decoration:none" href="#exbeltwaymultiregions">&#x1f517</a>
  <b>Beltways crossing into multiple regions.</b></br>
  </br>
  Treat segments along these beltways just as you would any other segment that crosses into multiple regions:
  one <code>.list</code> file line per region.</br></li>
</ul>
</div>

<p class="subheading"><a name="advanced"></a><a style="text-decoration:none" href="#advanced">&#x1f517</a>
Advanced features</p>

<div class="text">
<ul>
  <li><a name="mapview"></a><a style="text-decoration:none" href="#mapview">&#x1f517</a>
    Open Highway Browser from a map</br>
    </br>
    Viewing regions or systems on a map helps to see all your traveled segments. Click on the overlays to open the info window.
    The link directly opens the route in the Highway Browser so that you can create your <code>.list</code> file line.
    </br>
    </br>
  </li>
  <li><a name="comment"></a><a style="text-decoration:none" href="#comment">&#x1f517</a>
    Using comments</br>
    </br>
    Just as is the case in programming languages, Travel Mapping
    allows you to add <i>comments</i> to your <code>.list</code>
    file.  All text on a given line after a <code>#</code> is treated
    as a comment, and is ignored by the site update process.  You can
    comment out entire lines, or use them to include an annotatation at
    the end of the line.</br>
    </br>
    <code>#My first travel section</code></br>
    <code>IL I-70 52 MO/IL  #This is the section in Illinois</code></br>
    <code>MO I-70 MO/IL 249 #This is the section in Missouri</code>
    </br>
    </br>
  </li>
  <li><a name="structure"></a><a style="text-decoration:none" href="#structure">&#x1f517</a>
    Structure <code>.list</code> file</br>
    </br>
    Many users break up <code>.list</code> files into chunks by system, region
    or individual travel to make it easier to read or manage.
    We recommend sorting by system to keep the <code>.list</code> file small.
    You can also use blank lines as desired to enhance readability.
  </br>
  </br>
  </li>
  <li><a name="githubsubmit"></a><a style="text-decoration:none" href="#githubsubmit">&#x1f517</a>
    GitHub submission</br>
</br>
When you submit a <code>.list</code> file, it is placed under source
    code control using GitHub.
    Instead of emailing updates, some users fork our GitHub repository at
    <a href="https://github.com/TravelMapping/UserData">https://github.com/TravelMapping/UserData</a> and submit a pull request.</br>
    </br>
    Using GitHub directly is by no means required.  Unless you are an
    experienced Git/GitHub user, it's usually best to submit at least
    your initial file by <a href="#emailsubmission">email</a> to
    ensure it ends up in the correct location and does not have any
    problems that would cause problems with the system.</br>
    </br>
    <a name="gitinit"></a><a style="text-decoration:none" href="#gitinit">&#x1f517</a>
    <b>Initial setup to use GitHub directly:</b></br>
    <ol>
      <li><a name="gitsign"></a><a style="text-decoration:none" href="#gitsign">&#x1f517</a>
      Sign up on <a href="https://github.com">https://github.com</a> and create your own Github user.</li>
      <li><a name="gitrepo"></a><a style="text-decoration:none" href="#gitrepo">&#x1f517</a>
      Go to <a href="https://github.com/TravelMapping/UserData">https://github.com/TravelMapping/UserData</a>.</li>
      <li><a name="gitfork"></a><a style="text-decoration:none" href="#gitfork">&#x1f517</a>
      Press <code>Fork</code> at top of the page.</li>
      <li><a name="gitforkexe"></a><a style="text-decoration:none" href="#gitforkexe">&#x1f517</a>
      Click on your <i>Github user name</i> icon.</li>
    </ol>
    </br>
    <a name="gitmod"></a><a style="text-decoration:none" href="#gitmod">&#x1f517</a>
    <b>To be done with every modification of your user list file:</b>
    <ol>
      <li><a name="gitopen"></a><a style="text-decoration:none" href="#gitopen">&#x1f517</a>
      Go to your user list file:
      <span style="color: brown;">https://github.com/<i>&lt;your <b>Github</b> user name&gt;</i>/UserData/blob/master/list_files/<i>&lt;your <b>Travelmapping</b> user name&gt;</i>.list</span>.</li>
      <li><a name="gitedit"></a><a style="text-decoration:none" href="#gitedit">&#x1f517</a>
      Click on the <i>Edit this file</i> icon.</li>
      <li><a name="gitchange"></a><a style="text-decoration:none" href="#gitchange">&#x1f517</a>
      Make your edits or copy the content of your offline user list file.</li>
      <li><a name="gitcommitdir"></a><a style="text-decoration:none" href="#gitcommitdir">&#x1f517</a>
      Select <code>Commit directly to the master branch</code> on the bottom of the page.</li>
      <li><a name="gitcommit"></a><a style="text-decoration:none" href="#gitcommit">&#x1f517</a>
      Press <code>Commit changes</code>.</li>
      <li><a name="gitswitch"></a><a style="text-decoration:none" href="#gitswitch">&#x1f517</a>
      Go to <span style="color: brown;">https://github.com/<i>&lt;your <b>Github</b> user name&gt;</i>/UserData</span> (link on top of the page).</li>
      <li><a name="gitnewpull"></a><a style="text-decoration:none" href="#gitnewpull">&#x1f517</a>
      Press <code>Pull request</code>.</li>
      <li><a name="gitcheck"></a><a style="text-decoration:none" href="#gitcheck">&#x1f517</a>
      Check your changes indicated in <span style="color: green; background-color: #CCFFCC;">green</span> and <span style="color: red; background-color: #FFCCCC;">red</span>.</li>
      <li><a name="gitcreatepull"></a><a style="text-decoration:none" href="#gitcreatepull">&#x1f517</a>
      Press <code>Create pull request</code>.</li>
      <li><a name="gitcreatepullagain"></a><a style="text-decoration:none" href="#gitcreatepullagain">&#x1f517</a>
      Press <code>Create pull request</code> again.</li>
    </ol>
    Your pull request will be merged by an admin before the next site update.</br>
    To undo your changes, press <code>Close pull request</code>.
    </br>
    </br>
  </li>
  <li><a name="verification"></a><a style="text-decoration:none" href="#verification">&#x1f517</a>
    Data verification</br>
    </br>
    For advanced users, it is possible to check the changes to the <code>.list</code> file by running the site update to
    create the user log file before <a href="#gitnewpull">submitting the Github pull request</a>. You can use the
    <a href="https://github.com/TravelMapping/DataProcessing/blob/master/SETUP.md">data verification</a> tool.
    </br>
    </br>
  </li>
</ul>
</div>

<p class="heading"><a name="hwydatamanager"></a><a style="text-decoration:none" href="#hwydatamanager">&#x1f517</a>
How to become a highway data manager</p>

<div class="text">
Some experienced users volunteer to help the project.  If this interests you, start by reporting problems with existing highway data.
Those who have learned the project's structure and highway data rules and guidelines can help greatly by providing review of new highway
systems in development.  Highly experienced users can learn how to plot new highway systems under the guidance of experienced contributors.
</br>
The steps to become a highway data manager are as follows:
  <ol>
    <li><a name="activeuser"></a><a style="text-decoration:none" href="#activeuser">&#x1f517</a>
    Become an active user and get familiar with the project structure. Create a <a href="#userlistfile">list files</a> and understand how routes are organized into highway systems.</li>
    <li><a name="follow"></a><a style="text-decoration:none" href="#follow">&#x1f517</a>
    Catch up on previous discussions and follow current discussions <a href="http://forum.travelmapping.net/">on the forum</a> about highway data updates.</li>
    <li><a name="report"></a><a style="text-decoration:none" href="#report">&#x1f517</a>
    Report updates and problems in existing highway systems <a href="http://forum.travelmapping.net/">on the forum</a>.</li>
    <li><a name="manual"></a><a style="text-decoration:none" href="#manual">&#x1f517</a>
    Read and understand the <a href="devel.php#participate">developer manual</a>.</li>
    <li><a name="review"></a><a style="text-decoration:none" href="#review">&#x1f517</a>
    Participate in <a href="manual/sysrev.php">peer review</a> of a preview highway system.</li>
    <li><a name="structure"></a><a style="text-decoration:none" href="#structure">&#x1f517</a>
    Understand the project structure and how highway data is <a href="https://github.com/TravelMapping/HighwayData/blob/master/README.md">organised on Github</a>.</li>
    <li><a name="develop"></a><a style="text-decoration:none" href="#develop">&#x1f517</a>
    Develop a <a href="manual/sysnew.php">new highway system</a></li>
    <li><a name="maintain"></a><a style="text-decoration:none" href="#maintain">&#x1f517</a>
    Take <a href="manual/maintenance.php">responsibility for updates</a> in an unclaimed region, or by requesting to become the maintainer for a region from someone looking to unload some of theirs</li>
  </ol>
</div>


<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
