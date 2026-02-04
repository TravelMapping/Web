<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping Manual: Positioning Waypoints</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="/favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: Positioning Waypoints</h1>

<p class="heading"><a name="thumb"></a><a style="text-decoration:none" href="#thumb">&#x1f517</a>
Rules of thumb</p>

<div class="text">
<ul>
  <li><a name="shaping_points"></a><a style="text-decoration:none" href="#shaping_points">&#x1f517</a>
At <b>non-intersections</b> (such as hidden shaping points), position the waypoint on the centerline of the highway.</li>
  <li><a name="hwy_cross"></a><a style="text-decoration:none" href="#hwy_cross">&#x1f517</a>
Usually  position the waypoint at the point where the <b>centerlines 
of the two highways cross</b>. Often the same coordinates can be used for 
both highways. </li>
  <li><a name="through_lanes"></a><a style="text-decoration:none" href="#through_lanes">&#x1f517</a>
These centerlines are defined by <b>through lanes</b>, not by turning lanes at intersections nor the equivalent in interchange ramps. </li>
  <li><a name="accuracy"></a><a style="text-decoration:none" href="#accuracy">&#x1f517</a>
The <b>accuracy of the waypoint position</b> depends on the accuracy of the underlaying map.
Check the position with satellite views if possible.</li>
  <li><a name="couplets"></a><a style="text-decoration:none" href="#couplets">&#x1f517</a>
For couplets and divided highways, usually <b>split the difference between the two roadways</b>. </li>
  <li><a name="conn_ramps"></a><a style="text-decoration:none" href="#conn_ramps">&#x1f517</a>
There are common <b>exceptions to positioning 
at centerline crossings</b>, such as interchanges where ramps connect 
nearby, non-intersecting highways, or where a short access road connects
 a road to another with a trumpet or similar interchange. In these 
cases, the waypoints for the same interchange on the separate highways 
cannot be at the same coordinates. Instead, the waypoints should be 
where the <b>connecting ramps or access road interchange</b> with each highway.
</li>
  <li><a name="designation"></a><a style="text-decoration:none" href="#designation">&#x1f517</a>
Designations of the roadways are irrelevant. The <b>physical configuration of the junction</b> is what matters. <br />
    <ol>As an example, imagine a cloverleaf interchange where one loop ramp 
was replaced by a flyover ramp. Consider the I-97 &amp; MD 3 &amp; MD 32
 interchange.
    <!-- img src="staticmap_015.png" -->
    <br />
    The point should be centered in the middle, and the same point 
should be used for I-97, MD 3, and MD 32..  The centerlines are between 
the NW-SE and the NE-SW through lanes. One might have expected the point
 for I-97's file to be on the apparent SE-NE mainline, or even two 
points where MD 32 connects at the SE and MD 3 at the NE. But it is one 
interchange, and the point for all three routes should go in the middle.
 </ol></li>
  <li><a name="unusual_shapes"></a><a style="text-decoration:none" href="#unusual_shapes">&#x1f517</a>
Some interchanges have <b>unusual shapes</b> or are stretched-out versions of normal interchanges. Use your best judgment. </li>
    </ul>
    </div>
    
    <p class="heading"><a name="interchanges"></a><a style="text-decoration:none" href="#interchanges">&#x1f517</a>
    Interchanges</p>

    <div class="text">
<ul>
  <li><a name="diamond"></a><a style="text-decoration:none" href="#diamond">&#x1f517</a>
  <b>Diamond interchange:</b> Where the centerlines cross.
    <!-- img src="staticmap_006.png" -->
    <!-- img src="staticmap_013.png" -->
    <!-- img src="staticmap_011.png" -->
</li>
  <li><a name="double_half"></a><a style="text-decoration:none" href="#double_half">&#x1f517</a>
  <b>Double half interchanges:</b> Usually use one central point and treat 
  both halves as a single, full interchange. Exceptions: a clear gap of at
  least  0.5 mi/0.8 km separates the two halves, or each half connects to
  a different highway that we are also mapping.
    <!-- img src="staticmap_009.png" -->
  </li>
  <li><a name="partial_cloverleaf"></a><a style="text-decoration:none" href="#partial_cloverleaf">&#x1f517</a>
  <b>Partial cloverleaf interchanges:</b> Usually where the centerlines cross.
      <!-- img src="staticmap_014.png" -->
      <!-- img src="staticmap_002.png" -->
      <!-- img src="staticmap_010.png" -->
  </li>
  <li><a name="misbehaving"></a><a style="text-decoration:none" href="#misbehaving">&#x1f517</a>
  <b>Misbehaving diamond/partial cloverleaf interchanges:</b> Some cases 
  are better handled by putting the point where the ramps connect to the 
  freeway. In this case, the points on the freeway and the cross road will
  not line up.
    <!-- img src="staticmap_012.png" -->
  </li>
  <li><a name="cloverleaf"></a><a style="text-decoration:none" href="#cloverleaf">&#x1f517</a>
  <b>Cloverleaf interchanges:</b> Where the centerlines cross.
      <!-- img src="staticmap_016.png" -->
      <!-- img src="staticmap_005.png" -->
    </li>
  <li><a name="trumpet"></a><a style="text-decoration:none" href="#trumpet">&#x1f517</a>
  <b>Trumpet interchanges:</b> Where the access road centerline crosses the
  freeway centerline. Double trumpet interchanges get separate points at 
  each trumpet.
    <!-- img src="staticmap_007.png" -->
    <!-- img src="staticmap_004.png" -->
    <!-- img src="staticmap_008.png" --></li>
  
  <li><a name="two_way"></a><a style="text-decoration:none" href="#two_way">&#x1f517</a>
  <b>2-way high-speed interchanges:</b> Where the centerlines would cross 
  if it were an at-grade intersection with the same shape. Not where the 
  ramps of one road connect to the other.
    <!-- img src="staticmap_003.png" -->
    </li>
  <li><a name="three_way"></a><a style="text-decoration:none" href="#three_way">&#x1f517</a>
  <b>3-way high-speed interchanges:</b> In the middle of the central ramp triangle, not necessarily on a ramp.
    <!-- img src="staticmap.png" -->
    </li>
</ul>
  </div>
  
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
