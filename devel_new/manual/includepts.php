<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Mapping Manual: Waypoints to Include</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: Waypoints to Include</h1>

<p class="text">Highway files should include two kinds of waypoints.</p>

<p class="subheading"><a name="intersections"></a><a style="text-decoration:none" href="#intersections">&#x1f517</a>
Required intersections</p>

<div class="text">
  <ul>
    <li><a name="intborder"></a><a style="text-decoration:none" href="#intborder">&#x1f517</a>
    International border points (if applicable).</li>
    <li><a name="border"></a><a style="text-decoration:none" href="#border">&#x1f517</a>
    State/subdivisional border points (if applicable and only if 
    in a country we have split into subdivisions, such as the US, Canada, 
    Mexico, the UK, Russia, etc.) . </li>
    <li><a name="numberedhwy"></a><a style="text-decoration:none" href="#numberedhwy">&#x1f517</a>
    Visibly numbered Interstate highways, US highways, and state 
    highways, or the equivalent in countries besides the US. 
    County/local/secondary/municipal/township etc. routes are not required 
    points. The highway designation types to be included should be clarified
    before work is begun.</li>
    <li><a name="interchange"></a><a style="text-decoration:none" href="#interchange">&#x1f517</a>
    Highways intersecting at an interchange. </li>
    <li><a name="majorhwy"></a><a style="text-decoration:none" href="#majorhwy">&#x1f517</a>
    Other major highways that serve regional (not only local) travelers:
    <ul>
      <li><a name="parallel"></a><a style="text-decoration:none" href="#parallel">&#x1f517</a>
      Connections to a nearby parallel expressway.</li>
      <li><a name="bridge"></a><a style="text-decoration:none" href="#bridge">&#x1f517</a>
      Connections to a nearby bridge over a large creek or medium/large 
      river (not a small creek) large enough to noticeably restrict the number
      of bridges that cross it. </li>
      <li><a name="ferry"></a><a style="text-decoration:none" href="#ferry">&#x1f517</a>
      Connections to a nearby, major, public car ferry over a similarly 
      large creek, river, or bay, but without any other water crossing serving
      as a reasonably shorter (in distance or time) alternative for crossing.</li>
      <li><a name="urbanblvd"></a><a style="text-decoration:none" href="#urbanblvd">&#x1f517</a>
      A major unnumbered urban boulevard or arterial highway to 
      fill in a gap of 1.5 or more miles between visible waypoints in urban 
      areas.</li>
      <li><a name="attraction"></a><a style="text-decoration:none" href="#attraction">&#x1f517</a>
      Road (not driveway/parking lot) to a national or state-level park, major airport, or popular tourist attraction.</li>
      <li><a name="nottoinclude"></a><a style="text-decoration:none" href="#nottoinclude">&#x1f517</a>
      <span style="color: red;">Not roads to specific businesses (malls, restaurants, parking 
      garages, gas stations, etc.), your relative/friend's house, minor 
      tourist attractions, or destinations not regularly used by regional 
      traveler. Most automobile travel begins and ends with local/non-regional
      travel, but our target is places a regional traveler would likely 
      enter/exit a road.</span></li>
    </ul>
    </li>
    <li><a name="longsegment"></a><a style="text-decoration:none" href="#longsegment">&#x1f517</a>
    Intersections to split up long segments of 10+ miles (16+ km).
    Usually there are some. In some cases there are not (long freeway 
    sections between interchanges, highways in remote areas). </li>
  </ul>
</div>

<p class="subheading"><a name="intersections"></a><a style="text-decoration:none" href="#intersections">&#x1f517</a>
Shaping points</p>

<div class="text">
  <ul>
    <li><a name="check"></a><a style="text-decoration:none" href="#check">&#x1f517</a>
    Once the required intersections are added, look at the trace 
    on top of OpenStreetMap in the Waypoint Editor or in the Highway 
    Browser.
    <!-- For the Highway Browser, manually add "&amp;highlight=1" to the
    hwymap.php URL to turn on the red line. The highlight variable has a 
    few options with values 0, 1, and 2. -->
    </li>
    <li><a name="exceed"></a><a style="text-decoration:none" href="#exceed">&#x1f517</a>
    Identify sections of the route that go outside the thick red 
    line overlaid on the map.
    <!-- This line is the same as the blue line at the 5
         mi/10 km scale zoom level of the Google Maps API. -->
    Add just enough extra
    shaping points to your file to re-trace the thick red line so that the 
    centerline of your route as shown in OpenStreetMap stays within the 
    line. All waypoints should be positioned on the highway. The highlight 
    line helps you decide if another shaping point is needed.</li>
    <li><a name="sharpturns"></a><a style="text-decoration:none" href="#sharpturns">&#x1f517</a>
    If the route has sharp turns or switchbacks and adding a few 
    more shaping points there would significantly improve the trace, 
    consider adding a few more, but be conservative. Not every curve needs a
    shaping point. Few curves ever need more than one shaping point. </li>
    <li><a name="preferintersection"></a><a style="text-decoration:none" href="#preferintersection">&#x1f517</a>
    Prefer an intersection to act as a shaping point location 
    wherever possible. Shaping points that coincide with intersections 
    should be added as normal, visible waypoints labeled in the usual way.</li>
    <li><a name="hiddenwpt"></a><a style="text-decoration:none" href="#hiddenwpt">&#x1f517</a>
    Shaping points that do not coincide with intersections should 
    be added as hidden points beginning with <code>+X</code> and followed by a number, 
    i.e., <code>+X1, +X2, +X3</code> etc. The plus <code>+</code> hides the point in the Highway 
    Browser.  The number does not matter but must make the label unique for 
    the highway.  The Waypoint Editor uses random 6-digit numbers, like 
    <code>+X845202</code>.</li>
    <li><a name="nottoomany"></a><a style="text-decoration:none" href="#nottoomany">&#x1f517</a>
    With some practice, you can learn to identify where most of 
    the needed shaping points should go on your first pass. Be careful not 
    to add too many shaping points, just the needed ones as described. </li>
    </ul>
  </li>
</div>

<p class="subheading"><a name="bestpractise"></a><a style="text-decoration:none" href="#bestpractise">&#x1f517</a>
Best practise</p>

<p class="text">Properly shaped routes in non-remote areas 
typically have average waypoint spacing of 1.5-2.5 miles for surface 
routes and 2.0-3.0 miles for freeways, considering all visible and 
hidden waypoints together. There are routes that should fall outside 
those ranges, but those ranges are the norm.  These averages are not 
suggested spacings to aim for.  When the routes are worked out according
 to the above instructions, these ranges are typical of the average 
waypoint spacing.</p>


<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
