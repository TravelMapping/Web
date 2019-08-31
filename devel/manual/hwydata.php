<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Mapping Manual: Highway Data (.wpt) Files</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: Highway Data (.wpt) Files</h1>

<p class="text">Each route in TM is represented by a highway data
file, which we often refer to as a "waypoint file".  These files have
a .wpt extension.</p>

<p class="heading">.wpt Filenames</p>

<div class="text">
<ul>
  <li>One .wpt file is needed for each highway in each region. Highways 
crossing into multiple subdivisions of a subdivided country (e.g., 
states in the USA), or crossing into multiple countries (e.g., 
Euroroutes in Europe) must be chopped at borders into separate files for
 each region. </li>
  <li>Filenames are entirely lowercase and have a .wpt extension.</li>
  <li>Be sure you can see file extensions in your operating system. 
Windows hides known file extensions by default, so if you see a ".wpt" 
extension, the file might actually have something else appended, such as
 ".wpt.txt". This is no good. In Windows Explorer, you can disable the 
"Hide extensions for known file types" option in Tools menu &gt; Folder 
Options &gt; View tab. </li>
  <li><em>region</em>.<em>route</em>.wpt is the format for the filename.
    <ul>
      <li>For undivided countries, the <em>region</em> is the 3-letter
      ISO alpha-3 country code. See our
      list <a href="https://github.com/TravelMapping/HighwayData/blob/master/countries.csv">on
      GitHub</a>, which is based
      on the <a href="https://www.cia.gov/library/publications/the-world-factbook/appendix/appendix-d.html">CIA
      World Fact Book</a>. </li>
      <li>For divided countries (USA, CAN, MEX, GBR, RUS, KAZ),
 the <em>region</em> is an abbreviation for the subdivision rather
 than for the country. The USA and CAN use the standard postal
 codes. GBR (United Kingdom) uses WLS, SCT, ENG, NIR. For other
 countries, ask about the subdivision codes. Some countries (e.g.,
 MEX) have the country code prepended to avoid collisions with
 subdivision codes of other countries (NL = Newfoundland and Labrador
 in Canada , MEX-NL = Nuevo León in Mexico).  See our
 list <a href="https://github.com/TravelMapping/HighwayData/blob/master/regions.csv">on
 GitHub</a>.</li>
      <li>The <em>route</em> is a concatenation of 3-digit zero-padded 
number designation (e.g., a005, us042, pa343, la3132), the banner if 
applicable (bus, trk), and the 3-letter Abbrev. if applicable (e.g., 
cat, pit). </li>
      <li>Examples:
        <ul>
          <li>fra.a021.wpt = France A21.</li>
          <li>pa.pa042trkeag.wpt = Pennsylvania PA 42 Truck (Eagles Mere).</li>
          <li>  grc.a005.wpt = Greece A5 (main piece).</li>
          <li>grc.a005art.wpt = Greece A5 Arta section.</li>
          </ul>
      </li>
      </ul>
  </li>
</ul>
</div>

<p class="heading">.wpt Data Format</p>

<div class="text">
<ul>
  <li>The format is simple. Each line as two fields separated by a space.
    <ol>
      <li>Waypoint label. This identifies the waypoint. </li>
      <li>OpenStreetMap URL that includes coordinates.</li>
    </ol>
    </li>
  <li>For example, the first few lines of PA US 11
    (<tt>pa.us011.wpt</tt>) are:

    <pre>
MD/PA http://www.openstreetmap.org/?lat=39.721015&lon=-77.724087
PA163 http://www.openstreetmap.org/?lat=39.721396&lon=-77.724141
I-81(3) http://www.openstreetmap.org/?lat=39.755966&lon=-77.726984
PA16 http://www.openstreetmap.org/?lat=39.791111&lon=-77.731190
PA914 http://www.openstreetmap.org/?lat=39.865200&lon=-77.698738
SocIslRd http://www.openstreetmap.org/?lat=39.873043&lon=-77.687918
GuiSprRd http://www.openstreetmap.org/?lat=39.897681&lon=-77.675327
OrcDr http://www.openstreetmap.org/?lat=39.914143&lon=-77.668498
PA316 http://www.openstreetmap.org/?lat=39.927337&lon=-77.662439
US30 http://www.openstreetmap.org/?lat=39.936539&lon=-77.660417
    </pre>
    
  </li>
  <li>No blank or comment lines. The file may end with a final return character.</li>
  </ul>
  </div>
  
  <p class="heading">Getting a coordinate URL</p>

  <div class="text">

    <ul>
      <li>The easiest way to find the coordinates to
    be used in waypoint files is to use the <a href="/wptedit/">WPT
	  File Editor</a>.</li>
      <li>It is also helpful to get coordinates from
    existing routes where they intersect the route you are editing to
    avoid "near miss points" which enables links to intersecting
    routes and improves the quality of the <a href="/graphs">graph
    data</a> used
    by <a href="http://courses.teresco.org/metal/">METAL</a>, TM's
	academic offshoot.</li>
      <li> Do not use commercial mapping sources, such as Google Maps, Yahoo Maps, or Bing Maps.</li>
  </ul>
  </div>

<p class="heading">Multiplexes</p>

<div class="text">
<ul>
  <li>Concurrent highways (multiplexes) have multiple designations for the same section of highway.</li>
  <li><strong>The .wpt files of concurrent highways must have the same waypoints with exactly identical coordinates.</strong> This will allow my scripts to auto-detect multiplexes and remove duplicated mileage where appropriate.</li>
  <li>If you encounter a multiplex in your work and the concurrent highways have not been worked out, then proceed normally.</li>
  <li>If instead the concurrent highway is already worked out, copy the 
concurrent waypoints from the .wpt file of the concurrent route. 
Remember to put the waypoints in the correct order, which may be 
backwards from the order of the concurrent highway.  See
    the <a href="https://github.com/TravelMapping/HighwayData/tree/master/hwy_data">HighwayData
      repository on GitHub</a>
    for the latest copies of the .wpt files for completed highways.</li>
  <li>US 222/US 422 example:    <br />
    <br />
    <tt>pa.us222.wpt</tt><br />

	<pre>
US222Bus_S http://www.openstreetmap.org/?lat=40.297514&lon=-76.000028
PA724 http://www.openstreetmap.org/?lat=40.314070&lon=-75.996353
US422Bus http://www.openstreetmap.org/?lat=40.328473&lon=-75.978610
US422_W http://www.openstreetmap.org/?lat=40.329763&lon=-75.977046
StaHillRd http://www.openstreetmap.org/?lat=40.337248&lon=-75.967214
PapMillRd http://www.openstreetmap.org/?lat=40.344089&lon=-75.967026
US422/12 +PA12 +US422_E http://www.openstreetmap.org/?lat=40.350982&lon=-75.958260
BroRd http://www.openstreetmap.org/?lat=40.358692&lon=-75.977934
SprRidDr http://www.openstreetmap.org/?lat=40.363335&lon=-75.989280
</pre>
    <br />
    <tt>pa.us422rea.wpt</tt><br />
<pre>
PA419 http://www.openstreetmap.org/?lat=40.369934&lon=-76.188756
HighSt http://www.openstreetmap.org/?lat=40.361069&lon=-76.174414
BerRd http://www.openstreetmap.org/?lat=40.354344&lon=-76.143129
FurRd http://www.openstreetmap.org/?lat=40.331460&lon=-76.087886
PA724_W http://www.openstreetmap.org/?lat=40.325440&lon=-76.016126
US422Bus_W http://www.openstreetmap.org/?lat=40.327972&lon=-75.984170
US222_S http://www.openstreetmap.org/?lat=40.329763&lon=-75.977046
StaHillRd http://www.openstreetmap.org/?lat=40.337248&lon=-75.967214
PapMillRd http://www.openstreetmap.org/?lat=40.344089&lon=-75.967026
US222/12 +US222_N +PA12 http://www.openstreetmap.org/?lat=40.350982&lon=-75.958260
WyoBlvd http://www.openstreetmap.org/?lat=40.344163&lon=-75.952046
US422Bus http://www.openstreetmap.org/?lat=40.335187&lon=-75.939630
    </pre>
    <br />
    The waypoints from <tt>US422_W</tt> to <tt>US422/12</tt> in US 222
    and from <tt>US222_S</tt> to <tt>US222/12</tt> in US 422 are
    concurrent, and identical coordinates are used. The waypoints in
    some lines have been edited to refer to the correct highway in
    each file. </li>
  <li>Sometimes the waypoints are listed in different orders between the
 concurrent highway files. You might need to flip the waypoint order 
after pasting the lines into your file.</li>
</ul>
</div>

<p class="heading">Waypoint order</p>

<div class="text">
<ul>
  <li>In general, put the waypoints in the order normally used by the 
country. Exit numbers or roadside distance markers often reveal this 
order. </li>
  <li>In the US and Canada, most highways should have waypoints in order
 from west to east or south to north. Some spurs might not follow this 
as they begin at a parent highway and end away from it (e.g., NC I-795: 
north (I-95) to south). </li>
  <li>Euroroutes should run west to east and north to south.</li>
  <li>Some countries use a major city as the origin and have exit 
numbers increasing radially outward and circumferentially in a certain 
direction.</li>
</ul>
</div>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
