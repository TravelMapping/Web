<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Mapping Manual: Positioning Waypoints</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: Positioning Waypoints</h1>

<p class="heading"><a name="thumb"></a><a style="text-decoration:none" href="#thumb">&#x1f517</a>
Rules of thumb</p>

<div class="text">
<ul>
  <li><a name="shaping_points"></a><a style="text-decoration:none" href="#shaping_points">&#x1f517</a>
At non-intersections (such as hidden shaping points), position the waypoint on the centerline of the highway.</li>
  <li><a name="hwy_cross"></a><a style="text-decoration:none" href="#hwy_cross">&#x1f517</a>
Usually  position the waypoint at the point where the centerlines 
of the two highways cross. Often the same coordinates can be used for 
both highways. </li>
  <li><a name="through_lanes"></a><a style="text-decoration:none" href="#through_lanes">&#x1f517</a>
These centerlines are defined by through lanes, not by turning lanes at intersections nor the equivalent in interchange ramps. </li>
  <li><a name="couplets"></a><a style="text-decoration:none" href="#couplets">&#x1f517</a>
For couplets and divided highways, usually split the difference between the two roadways. </li>
  <li><a name="conn_ramps"></a><a style="text-decoration:none" href="#conn_ramps">&#x1f517</a>
There are common exceptions to positioning 
at centerline crossings, such as interchanges where ramps connect 
nearby, non-intersecting highways, or where a short access road connects
 a road to another with a trumpet or similar interchange. In these 
cases, the waypoints for the same interchange on the separate highways 
cannot be at the same coordinates. Instead, the waypoints should be 
where the connecting ramps or access road interchange with each highway.
</li>
  <li><a name="designation"></a><a style="text-decoration:none" href="#designation">&#x1f517</a>
Designations of the roadways are irrelevant. The physical configuration of the junction is what matters. <br />
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
Some interchanges have unusual shapes or are stretched-out versions of normal interchanges. Use your best judgment. </li>
    </ul>
    </div>
    
    <p class="heading"><a name="interchanges"></a><a style="text-decoration:none" href="#interchanges">&#x1f517</a>
    Interchanges</p>

    <div class="text">
<ul>
  <li>Diamond interchange: where the centerlines cross.
    <!-- img src="staticmap_006.png" -->
    <!-- img src="staticmap_013.png" -->
    <!-- img src="staticmap_011.png" -->
</li>
  <li>Double half interchanges: Usually use one central point and treat 
both halves as a single, full interchange. Exceptions: a clear gap of at
 least  0.5 mi/0.8 km separates the two halves, or each half connects to
 a different highway that we are also mapping.
    <!-- img src="staticmap_009.png" -->
  </li>
  <li>Partial cloverleaf interchanges: usually where the centerlines cross.
      <!-- img src="staticmap_014.png" -->
      <!-- img src="staticmap_002.png" -->
      <!-- img src="staticmap_010.png" -->
  </li>
  <li>Misbehaving diamond/partial cloverleaf interchanges: some cases 
are better handled by putting the point where the ramps connect to the 
freeway. In this case, the points on the freeway and the cross road will
 not line up.
    <!-- img src="staticmap_012.png" -->
  </li>
  <li>Cloverleaf interchanges: where the centerlines cross.
      <!-- img src="staticmap_016.png" -->
      <!-- img src="staticmap_005.png" -->
    </li>
  <li>Trumpet interchanges: where the access road centerline crosses the
 freeway centerline. Double trumpet interchanges get separate points at 
    each trumpet.
    <!-- img src="staticmap_007.png" -->
    <!-- img src="staticmap_004.png" -->
    <!-- img src="staticmap_008.png" --></li>
  
  <li>2-way high-speed interchanges: where the centerlines would cross 
if it were an at-grade intersection with the same shape. Not where the 
ramps of one road connect to the other.
        <!-- img src="staticmap_003.png" -->
    </li>
  <li>3-way high-speed interchanges: in the middle of the central ramp triangle, not necessarily on a ramp.
      <!-- img src="staticmap.png" -->
    </li>
</ul>
  </div>
  
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
