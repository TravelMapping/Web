<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping Manual: Definition of a highway system</title>
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

<h1>Travel Mapping Manual: Definition of a highway system</h1>

<p class="heading"><a name="roadsys"></a><a style="text-decoration:none" href="#roadsys">&#x1f517</a>
What is a highway system?</p>

<p class="text" >
  Highways are often named and numbered by the governments that typically develop and maintain them. National highway systems
  are usually defined as a network by law. In addition, the regional commissions of the United Nations Economic and Social Council
  define international routes.
  </br>
  </br>
  The regions usually have different classifications. A typical classification is as follows (top down): freeways, national primary
  highways, national or state secondary roads, district or county roads, and local roads. The classification also depends on the law of
  the specific region. Different systems usually have different names with specific route prefixes and a specific numbering.
  </br>
  </br>
  The routes of each highway system are typically indicated with signs on the road. Route numbers or names are usually indicated on direction
  signs. Especially routes of lower classification networks are sometimes only indicated on mile posts. The classifications are often indicated
  with different colors or symbols. The frequency and quality of signposting is highly depending on the maintaining authorities.
  </br>
  </br>
  Route numbers are usually indicated on printed and online maps.
</p>

<p class="heading"><a name="tmsys"></a><a style="text-decoration:none" href="#tmsys">&#x1f517</a>
What are Travel Mapping highway systems?</p>

<p class="text" >
  The Travel Mapping project aims to cover as many highway systems as possible from all over the world. But we want to have a data set with
  high quality so that roadgeeks can safely claim having clinched a whole highway systems. We only include highway systems which are signed
  along the routes. Please refer to the <a href="sysnew.php">Create a new highway system</a> for further information to the requirements.
  We sometimes expand the term beyond formally-defined highway systems to include collections of related routes like tourist routes which
  are defined by other national or international organizations.
</p>


<p class="heading"><a name="selectsys"></a><a style="text-decoration:none" href="#selectsys">&#x1f517</a>
What are <i>Select</i> highway systems?</p>

<p class="text" >
  There are routes with high importance to travelers which do not belong to any highway system. These are routes which are maintained by
  local authorities, are privately owned, or belong to systems which are extensive and not yet included to the project. Travel Mapping
  categorizes these routes to an own Travel Mapping highway system if possible. These systems are called <i>Select</i> because they cannot
  be considered being complete. We only include routes which the highway data manager of the region considers being relevant.
</p>

<p class="heading"><a name="status"></a><a style="text-decoration:none" href="#status">&#x1f517</a>
System Status</p>

<div class="text">
  Highway systems in TM are categorized in one of three groups,
  depending on its level of completeness and the maintainers'
  confidence in its accuracy:
</div>


<div class="text">
  <ul>
    <li><a name="active"></a><a style="text-decoration:none" href="#active">&#x1f517</a>
    <b><active>Active systems</active></b> are those which we believe are accurate and complete.
      Care is taken to ensure that changes do not "break" a user's list file. In many cases, however,
      the changes needed will break user lists. In those situations, changes must be logged in the
      <a href="https://github.com/TravelMapping/HighwayData/blob/master/updates.csv">updates list</a>,
      which is  available <a href="/devel/updates.php">on the updates page</a> to help users keep their list files accurate.
      </br>
    </li>
    </br>
    <li><a name="preview"></a><a style="text-decoration:none" href="#preview">&#x1f517</a>
    <b><preview>Preview systems</preview></b> are substantially complete, but are undergoing final review and revisions.
    These may still undergo significant changes without notification in the updates log. Users can include these systems in
    list files for mapping and stats, but should expect to revise as the system progresses toward activation. Users plotting
    travels in preview systems may wish to follow the forum discussions to see the progress and find out about revisions being made.
    The activation of a system is logged in the <a href="https://github.com/TravelMapping/HighwayData/blob/master/systemupdates.csv">
    system updates list</a>, and notified <a href="/devel/updates.php">on the updates page</a>.
    </li>
    </br>
    <li><a name="devel"></a><a style="text-decoration:none" href="#devel">&#x1f517</a>
    <b><devel>In-Development systems</devel></b> are a work in progress. Routes in these systems are not yet available for mapping
    and inclusion in user stats, and are shown in the Highway Browser primarily for the benefit of the highway data managers who are
    developing the system. Once the system is substantially complete it will be upgraded to preview status, at which time users can begin
    to plot their travels in the system. It is also logged in the <a href="https://github.com/TravelMapping/HighwayData/blob/master/systemupdates.csv">
    system updates list</a>, and notified <a href="/devel/updates.php">on the updates page</a>.
    </li>
  </ul>
</div>
<div class="text">
  The system categories are indicated by color-code throughout the site. User stats are generally divided into stats for travels in
  <active>active</active> systems and <active>active</active> + <preview>preview</preview> systems.
</div>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
