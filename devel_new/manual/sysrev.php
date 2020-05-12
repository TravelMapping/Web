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
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1 style="color:red">Travel Mapping Manual: Review a preview highway system - <i>Draft</i></h1>

<p class="text" style="color:red">ToDo: Complete the descriptions with more details, make correct sentences, add links!</p>

<div class="text">
  Any kind of review helps. Anyone can report issues to the system / region on the forum. Active systems vs. preview systems in the respective board etc.
  </br>
  Thorough peer-review of a preview system to get it active:
  <ol>
    <li><a name="ask"></a><a style="text-decoration:none" href="#ask">&#x1f517</a>
    Agree with the developer of the highway system that you will do a peer-review (on the forums thread of the highway system). You should be familar with the manual.</li>
    <li><a name="sources"></a><a style="text-decoration:none" href="#sources">&#x1f517</a>
    Check sources for route list / map indicated on the thread, credits, readme.md,... Have a general look on the highway system to get familar with the region-specific attitudes. Ask for sources if missing, find additional sources.</li>
    <li><a name="dataerror"></a><a style="text-decoration:none" href="#dataerror">&#x1f517</a>
    Data check errors incl. NMPs. If there are others than VD, SA, ask the developer for fixing first.</li>
    <li><a name="generalcheck"></a><a style="text-decoration:none" href="#generalcheck">&#x1f517</a>
    General mapview, HB or HDX check for missing routes or anything generally looking odd.</li>
    <li><a name="names"></a><a style="text-decoration:none" href="#names">&#x1f517</a>
    Check route names, banners, city names etc. in HB route list.</li>
    <li><a name="concurrency"></a><a style="text-decoration:none" href="#concurrency">&#x1f517</a>
    Thorough HDX concurrencies check - not just system but region - to avoid getting confused later. Fixing this is also a danger to break things. Better to have it done before the peer-review.</li>
    <li><a name="wpteditor"></a><a style="text-decoration:none" href="#wpteditor">&#x1f517</a>
    WPT editor check route by route for exceeding limits + routing + end position + missing wps, then wp by wp for position and name of non-HB-routes (GSV). <a href="http://forum.travelmapping.net/index.php?topic=3039.msg17115#msg17115">wp off</a></li>
    <li><a name="hb"></a><a style="text-decoration:none" href="#hb">&#x1f517</a>
    HB check route by route to check intersecting HB routes and their names.</li>
    <li><a name="questions"></a><a style="text-decoration:none" href="#questions">&#x1f517</a>
    Check forum for questions.</li>
    <li><a name="finalcheck"></a><a style="text-decoration:none" href="#finalcheck">&#x1f517</a>
    Final general mapview, HB or HDX check for anything generally looking odd.</li>
  </ol>
  Report everything to the forum thread, clearly indicate the route names. Make batches especially when it's one of your first or the developers first highway systems.
  Note on the forum if you skipped a step, for instance if HDX is too fancy for you.
</div>
  
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
