<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Mapping Manual: How to contribute to the project</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1 style="color:red">Travel Mapping Manual: How to contribute to the project - <i>Draft</i></h1>

<p class="heading">
  Contents</p>

<div class="text">
<ul>
  <li><a href="#userlistfile">How to create a user list file</a></li>
  <ul>
    <li><a href="#firststeps">First steps with email submission</a></li>
    <li><a href="#problems">Solutions to common problems</a></li>
    <li><a href="#advanced">Advanced features</a></li>
  </ul>
  <li><a href="#hwydatamanager">How to become a highway data manager</a></li>
</ul>
</div>

<p class="heading"><a name="userlistfile"></a><a style="text-decoration:none" href="#userlistfile">&#x1f517</a>
How to create a user list file</p>

<div class="text">
  The basic idea is to make a list of highway sections you have traveled and collect them into a <em>plain text file</em>
  to be submitted to Travelmapping. The text file will be 
  processed and converted into a set of custom stats pages and maps 
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
      Use only English letters (A-Z or a-z), numbers (0-9), and underscores (_) in your username,
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
      The Highway Browser lists all available systems and routes that you can use
      (we recommend starting with <a href="sysdef.php#active">active systems</a>). For each route,
      the Highway Browser also provides a list of waypoints that you can use as a start or end point.</br>
      </br>
      
      Break up your travels into traveled sections by region and route. For each route section,
      add one line to your <code>.list</code> file with the following format:</br>
      </br>
      <code>Region Route Waypoint1 Waypoint2</code></br>
      </br>
      For example, if you traveled on Interstate 70 in Illinois, USA between Exit 52 and the Missouri border, you would put the following line in your file:</br>
      </br>
      <code>IL I-70 52 MO/IL</code></br>
      </br>
      <ul>
        <li>The region and route combination is indicated on the left after <code>.list name:</code> in the Highway Browser.</li>
        <li>Click the start point <code>52</code> of your travel segment on the table
        to center the map at this point.</li>
        <li>Copy the first line of the info window to get the correct name <code>52</code>.</li>
        <li>Click on the end point <code>MO/IL</code> or click the waypoint on the map
        to open the info window and copy the label name <code>MO/IL</code>.</li>
        <li>The order of the waypoints is not relevant for the process.</li>
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
      Continue to complete your travels.
      <span style="color: red;">See our Examples page for more examples of how to break down your travels into "sections" to enter into the file.</br>
      <i>TODO, do we still need the <a href="https://web.archive.org/web/20181117145826/http://cmap.m-plex.com/docs/examples.php">examples page we had on CHM?</a>
      Replace it by a link to the list files on Github?</i></span></br>
      </br>
      Note that a mechanism is in place to automatically include concurrent highways.</br>
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
        <li><span style="color: red;"><b>The fields have only a single space between them.</b> Tabs, commas, or other
        delimiters may prevent the mapping script from parsing your file correctly.
        </br><i>TODO: <a href="http://forum.travelmapping.net/index.php?topic=3172.msg18595#msg18595">wait for yakra</a> what's actually implemented!</i></span></li>
        <li><b>The file is saved as plain text with a .list extension.</b> Word processor files (Microsoft Word,
        Open Office Write, etc.) and rich-text formats will not work. If you use a word processor to create your file,
        be sure to select "Save As..." and save the file in a plain text format.</li>
        <li><b>Including a small number of blank lines is acceptable.</b> You might wish to break up your list into
        chunks by region or highway type to make it easier to read or manage.</li>
      </ul>
    </li>
    </br>
    <li><a name="emailsubmission"></a><a style="text-decoration:none" href="#emailsubmission">&#x1f517</a>
      <b>Send your <code>.list</code> file by email to <code>travmap@teresco.org</code> destination.
      Mention in the subject line that your username is a new one.</b></br>
      </br>
      You have now finished your part! Your file will be included in the next site update, <span style="color: red;">which usually occurs more often than weekly.
      <i></br>TODO, <a href="http://forum.travelmapping.net/index.php?topic=3172.msg18595#msg18595">wait for Jim</a>
      whether we really want to say that we usually update it once per day?</br></i></span></li>
    </br>
    <li><a name="aftersiteupdate"></a><a style="text-decoration:none" href="#aftersiteupdate">&#x1f517</a>
      <b>Later, look for your name on the <a href="http://travelmapping.net/stat.php">Traveler List</a>.</b></br>
      </br>
      Once your file has been processed, your name will appear in this list along with all the other travelers.
      Click on your username and enjoy your traveled highway stats and maps.</br>
      Lots of stats are available in csv files linked from <a href="/stats/">http://travelmapping.net/stats/</a>.</li>
    </br>
    <li><a name="errorlog"></a><a style="text-decoration:none" href="#errorlog">&#x1f517</a>
      <b>Check the user log file.</b></br>
      </br>
      Check your <a href="../logs/users/">online log file</a> (also directly linked on your user stats pages).
      If you included a highway or point label that the mapping script does not recognize, it will tell you
      in your log file. Sometimes the highway data is updated, and this may generate a new error. Check
      <a href="http://travelmapping.net/devel/updates.php">the updates page</a> for info on what has been changed
      to the route. You should also check the updates page frequently to find highway changes like simple relocations
      that don't break your list file and cannot be reported in your log file.
      Changes to <a href="sysdef.php#preview">systems in preview state</a> are not notified.
    </li>
    </br>
    <li><a name="nextupdate"></a><a style="text-decoration:none" href="#nextupdate">&#x1f517</a>
      <b>Update your file as needed by emailing an updated copy.</b></br>
      </br>
      If you do more traveling, you can update your <code>.list</code> file to reflect the new highways on which you
      have traveled. To update your file, just email it again, and it will be processed in the subsequent site update.</li>
  </ol>
  The Travelmapping collaborators hope that you enjoy your free maps and stats pages.
</div>


<p class="subheading"><a name="problems"></a><a style="text-decoration:none" href="#problems">&#x1f517</a>
Solutions to common problems</p>
<div class="text" >
  <ol>
    <li><a name="invaliduser"></a><a style="text-decoration:none" href="#invaliduser">&#x1f517</a>
      <b>An invalid username was chosen.</b></br>
      </br>
      Only the characters A-Z, a-z, 0-9, and the underscore (_) are allowed in usernames. If you used other
      characters, such as non-English characters or symbols, remove them.
    </li>
    </br>
    <li><a name="namemismatch"></a><a style="text-decoration:none" href="#namemismatch">&#x1f517</a>
      <b>The filename does not match the username.</b></br>
      </br>
      If the username is highwayguy80, then the filename should be highwayguy80.list, not highwayguy80.txt,
      HighwayGuy80.list, arkansas.list, motorways.list, nor other filenames. Note that since usernames generally do not
      change, the valid filename also generally does not change.</br>
      </br>
      Due to Windows operating systems hiding the file extension .list by default and/or including an extra, hidden .txt
      extension, a .list.txt extension is also accepted (e.g., highwayguy80.list.txt). However, users are asked to avoid the
      double extension so that files do not need to be renamed after being submitted.
    </li>
    </br>
    <li><a name="notemailed"></a><a style="text-decoration:none" href="#notemailed">&#x1f517</a>
      <b>The file was not emailed.</b></br>
      </br>
      Check that you attached your file to the email you thought you sent, and check that the email was sent. Files that are not
      emailed cannot be processed.
    </li>
    </br>
    <li><a name="notprocessed"></a><a style="text-decoration:none" href="#notprocessed">&#x1f517</a>
      <b>The file was sent but has not yet been processed.</b></br>
      </br>
      <span style="color: red;">File processing is not automated at this time, and a few days may pass between receipt of the file and its processing.
      If your file has not been processed and more than 7 days have passed since you sent the email with the file attached to it,
      then simply send your file by email once more. If instead, 7 or less days have passed, no action is needed on your part.</br>
      </br>
      Note that occasionally processing may take place more than 7 days after receipt. If this is the case, a notice will appear
      on the home page to inform you of the temporary suspension of file processing. Your file will be processed when processing
      resumes without any further action needed on your part.
      </br></br><i>TODO, <a href="http://forum.travelmapping.net/index.php?topic=3172.msg18595#msg18595">wait for Jim</a> how we should change this!</i></span>
    </li>
    </br>
    <li><a name="vanished"></a><a style="text-decoration:none" href="#vanished">&#x1f517</a>
      <b>The new file was processed, but all the highways from the previous file have vanished.</b></br>
      </br>
      The new file should contain all your traveled highways, not just additions to those from a previously submitted file.
      The new file always replaces the previous file rather than supplementing it. This system allows users to not only add
      or modify lines in their file but also to delete them as needed.
    </li>
  </ol>
</div>

<p class="subheading"><a name="advanced"></a><a style="text-decoration:none" href="#advanced">&#x1f517</a>
Advanced features</p>

<p class="text"><a name="mapview"></a><a style="text-decoration:none" href="#mapview">&#x1f517</a>
Open Highway Browser from a map</p>

<div class="text">
  Viewing regions or systems on a map helps to see all your traveled segments. Click on the overlays to open the info window.
  The link directly opens the route in the Highway Browser so that you can create your <code>.list</code> file line.
</div>

<p class="text"><a name="comment"></a><a style="text-decoration:none" href="#comment">&#x1f517</a>
Using comments</p>

<div class="text">
  Use <code>#</code> to comment lines you like to add for info purpose.</br>
  In-line comments are also possible:</br>
  </br>
  <code>#My first travel section</code></br>
  <code>IL I-70 52 MO/IL  #This is the section in Illinois</code></br>
  <code>MO I-70 MO/IL 249 #This is the section in Missouri</code>
</div>


<p class="text"><a name="githubsubmit"></a><a style="text-decoration:none" href="#githubsubmit">&#x1f517</a>
Github submission</p>

<div class="text">
  Instead of emailing, you can also fork our GitHub repository at
  <a href="https://github.com/TravelMapping/UserData">https://github.com/TravelMapping/UserData</a> and submit a pull request.</br>
  </br>
  Your first user list file submission should be done by <a href="#emailsubmission">sending an email</a>.</br>
  Your user will be added with the next site update and your user list file will be published on GitHub.</br>
  </br>
  <a name="gitinit"></a><a style="text-decoration:none" href="#gitinit">&#x1f517</a>
  <b>To be done initially:</b></br>
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
</div>
<p class="subheading"><a name="verification"></a><a style="text-decoration:none" href="#verification">&#x1f517</a>
Data verification</p>

<div class="text">
  For advanced users, it is possible to check the changes to the <code>.list</code> file by running the site update to
  create the user log file before <a href="#gitnewpull">submitting the Github pull request</a>. You can use the
  <a href="https://github.com/TravelMapping/DataProcessing/blob/master/SETUP.md">data verification</a> tool.
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
    Become an active user get familiar with the project structure. Create a <a href="#userlistfile">list files</a> and understand how routes are organized into highway systems.</li>
    <li><a name="follow"></a><a style="text-decoration:none" href="#follow">&#x1f517</a>
    Catch up on previous discussions and follow current discussions <a href="http://forum.travelmapping.net/">on the forum</a> about highway data updates.</li>
    <li><a name="report"></a><a style="text-decoration:none" href="#report">&#x1f517</a>
    Report updates and problems in existing highway systems <a href="http://forum.travelmapping.net/">on the forum</a>.</li>
    <li><a name="review"></a><a style="text-decoration:none" href="#review">&#x1f517</a>
    Participate in <a href="sysrev.php">peer review</a> of a preview highway system.</li>
    <li><a name="structure"></a><a style="text-decoration:none" href="#structure">&#x1f517</a>
    Understand the project structure and how highway data is <a href="https://github.com/TravelMapping/HighwayData/blob/master/README.md">organised on Github</a>.</li>
    <li><a name="develop"></a><a style="text-decoration:none" href="#develop">&#x1f517</a>
    Develop a <a href="sysnew.php">new highway system</a></li>
    <li><a name="maintain"></a><a style="text-decoration:none" href="#maintain">&#x1f517</a>
    Take <a href="maintenance.php">responsibility for updates</a> in an unclaimed region, or by requesting to become the maintainer for a region from someone looking to unload some of theirs</li>
  </ol>
</div>


<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
