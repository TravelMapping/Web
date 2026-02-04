<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping Manual: Highway Data (.wpt) Files</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="/favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: Highway Data (<code>.wpt</code>) Files</h1>

<div class="text">Each route in TM is represented by a highway data
file, which we often refer to as a "waypoint file".  These files have
a <code>.wpt</code> extension.</div>

<p class="heading">
  Contents</p>

<div class="text">
<ul>
  <li><a href="#file"><code>.wpt</code> Files</a></li>
  <li><a href="#filename"><code>.wpt</code> Filenames</a></li>
  <li><a href="#format"><code>.wpt</code> Data Format</a></li>
</ul>
</div>



<p class="heading"><a name="file"></a><a style="text-decoration:none" href="#file">&#x1f517</a>
<code>.wpt</code> File</p>

<div class="text">
<ul>
  <li><a name="fileborder"></a><a style="text-decoration:none" href="#fileborder">&#x1f517</a>
  One <code>.wpt</code> file is needed for each highway in each region. Highways 
  crossing into multiple subdivisions of a subdivided country (e.g., 
  states in the USA), or crossing into multiple countries (e.g., 
  UNECE International 'E' Roads in Europe) must be chopped at borders into separate files for
  each region. </li>
  <li><a name="concurrencies"></a><a style="text-decoration:none" href="#concurrencies">&#x1f517</a>
  Concurrencies within which not all concurrent routes are signed</br>
  This section concerns typically well signed routes
  that whose numbers are signed with trailblazers or are not signed at
  all within a section of highway concurrent with other routes.  For
  example, France's A4 and A26 merge and split, but along the merged
  section, A4 is signed and A26 is not, but both routes are signed
  beyond the concurrent section. Should the not-signed routes be
  chopped into its signed pieces or made continuous and concurrent with
  the signed route?</br>
  </br>
  We have 4 cases that are treated differently. The descriptions refer 
  to concurrencies of two routes, but the ideas generalize to 
  concurrencies of more routes.</li>
  </br>
  <ol>
    <li><a name="impliedmultiplexes"></a><a style="text-decoration:none" href="#impliedmultiplexes">&#x1f517</a>
    <strong>Unsigned but implied multiplexes: Treat as continuous routes with one <code>.wpt</code> file.</strong></br>
    This is the case where only one route is signed where another one route 
    merges onto the same road. Usually the unsigned route splits off at 
    another point, then it's signed beyond the concurrency. Continuity is 
    still implied by the way the routes are numbered even if the signs are 
    simplified to show only one route, so we treat each route as a 
    continuous one.</br>
    </br>
    Examples:</br>
    <ul>
      <li>USA MD 23/MD 165:</br>
      MD 23 was continuously signed before a relocation that created the 
      duplex.  In the current state, MD 23 is signed as "TO MD 23" at its 
      approaches to the duplex, and MD 165 is signed continuously. MD 23 
      should continue to be treated as continuous.</li>
      <li>ENG A414:</br>
      Follow the length of A414 and you'll see several concurrent routes, 
      sometimes shown as A414 and sometimes as the other route, at least as 
      Google Maps shows it. A system of surface highways with a bypass here 
      and there is bound to be full of concurrencies, and so chopping half the
      routes into pieces around the concurrent parts would create a zillion 
      "extra" files for short pieces of routes.</li>
      <li>FRA A4/A26:</br>
      The two freeways merge and split. The pieces of A26 could have been 
      given different numbers, but instead they were given the same number, as
      if it should be one long route rather than two.</li>
    </ul>
    </li>
    </br>
    <li><a name="discontinuous"></a><a style="text-decoration:none" href="#discontinuous">&#x1f517</a>
    <strong>Bypassed, segmented routes: Discontinuous routes with more than one <code>.wpt</code> file.</strong></br>
    Here some pieces of an old route were bypassed by a new route, but 
    other pieces of the old route were upgraded into the new route. This 
    makes a continuously signed new route with pieces of the old route 
    beginning and ending at various places along the new route.</br>
    </br>
    Examples:</br>
    <ul>
      <li>Bannered highways, like Alternate and Business routes, in the US:
      Many US highways, for example, have many auxiliary routes with the same 
      designation, like US 40 having many US 40 Business routes.  The 
      auxiliary routes are treated discontinuously, rather than having one 
      long, continuous US 40 Business concurrent along sections of US 40.</br>
      US 40/MD 144:</br>
      There are several pieces of MD 144 along the old alignment of US 40. The
      pieces act like Business or Alternate routes and are never signed to 
      suggest continuity.</li>
    </ul>
    </li>
    </br>
    <li><a name="alternating"></a><a style="text-decoration:none" href="#alternating">&#x1f517</a>
    <strong>Alternating designation: Discontinuous routes with more than one <code>.wpt</code> file.</strong></br>
    A road changes designations back and forth without either route splitting off on its own.</br>
    </br>
    Example:</br>
    <ul>
      <li>Ireland's M/N routes come to mind here. Part of N8 was upgraded to M8, 
      but there is no alternative N8 along that section. However, N8 leads 
      straight into M8 at each end of M8. So if the highway goes N8-M8-N8, 
      we'll have three files for these three routes.</li>
    </ul>
    </li>
    </br>
    <li><a name="notconcurrent"></a><a style="text-decoration:none" href="#notconcurrent">&#x1f517</a>
    <strong>Like designations that aren't concurrent: Discontinuous routes  with more than one <code>.wpt</code> file.</strong></br>
    By whatever reasoning, two unrelated, distant highways were given the same designation.</br>
    </br>
    Example:</br>
    <ul>
      <li>PA 97 (in NW Pennsylvania) and PA 97 (in southern PA), both part of the state highway system in Pennsylvania.</li>
    </ul>
    </li>
  </ol>
</ul>
</div>

<p class="heading"><a name="filename"></a><a style="text-decoration:none" href="#filename">&#x1f517</a>
<code>.wpt</code> Filenames</p>

<div class="text">
<ul>
  <li><a name="lowercase"></a><a style="text-decoration:none" href="#lowercase">&#x1f517</a>
  Filenames are entirely lowercase and have a <code>.wpt</code> extension.</li>
  <li><a name="fileextension"></a><a style="text-decoration:none" href="#fileextension">&#x1f517</a>
  Be sure you can see file extensions in your operating system. 
  Windows hides known file extensions by default, so if you see a "<code>.wpt</code>" 
  extension, the file might actually have something else appended, such as
  "<code>.wpt.txt</code>". This is no good. In Windows Explorer, you can disable the 
  "Hide extensions for known file types" option in Tools menu &gt; Folder 
  Options &gt; View tab. </li>
  <li><a name="regionroute"></a><a style="text-decoration:none" href="#regionroute">&#x1f517</a>
  <code>region</code> + <code>.</code> + <code>route</code> ( + <code>banner</code> + <code>abbreviation</code> ) + <code>.wpt</code> is the format for the filename.
    <ul>
      <li><a name="region"></a><a style="text-decoration:none" href="#region">&#x1f517</a>
      <code>Region</code> is the region code without any hyphens:</li>
      <ul>
      <li><a name="undivided"></a><a style="text-decoration:none" href="#undivided">&#x1f517</a>
      For undivided countries, the <code>region</code> is the 3-letter ISO 3166-1 alpha-3 country code,
      e.g. <code>IDN</code> for Indonesia, <code>LUX</code> for Luxembourg, <code>NZL</code> for New Zealand.
      See our list <a href="https://github.com/TravelMapping/HighwayData/blob/master/countries.csv">on
      GitHub</a>, which is based
      on the <a href="https://www.cia.gov/library/publications/the-world-factbook/appendix/appendix-d.html">CIA
      World Fact Book</a>. </li>
      <li><a name="divided"></a><a style="text-decoration:none" href="#divided">&#x1f517</a>
      For divided countries the <code>region</code> is an abbreviation for the subdivision rather
      than for the country.</li>
      <ul>
        <li>The USA and Canada use the standard postal codes.</li>
        <li>The United Kingdom uses <code>ENG</code>, <code>NIR</code>, <code>SCT</code> and <code>WLS</code>.</li>
        <li>Mexico uses the subdivision codes that appear on state route shields with the country code
        prepended to avoid collisions with subdivision codes of other countries (<code>NL</code> = Newfoundland and
        Labrador in Canada, <code>MEX-NL</code> = Nuevo León in Mexico).</li>
        <li>The ISO 3166-2 country subdivision codes with the country code prepended are used for other
        countries (e.g. <code>AUS-ACT</code>, <code>CHN-AH</code>, <code>DEU-BW</code>, <code>ESP-AN</code>, <code>FRA-ARA</code>, <code>IND-AN</code>).</li>
        <li>See our list
        <a href="https://github.com/TravelMapping/HighwayData/blob/master/regions.csv">on GitHub</a>.</li>
      </ul>
      </ul>
      <li><a name="route"></a><a style="text-decoration:none" href="#route">&#x1f517</a>
      The <code>route</code> is the name of the highway (number padded with zeroes as needed), ignoring any banners or qualifiers.
      No spaces! <code>us034</code> for US 34, <code>oh017</code> for OH 17, <code>pa066</code> for Business PA 66, <code>a007</code> for French Autoroute A7.
      Skip hyphens and slashes.</li>
      <li><a name="banner"></a><a style="text-decoration:none" href="#banner">&#x1f517</a>
      The <code>banner</code> (if needed) is a 3-letter banner abbreviation (<code>bus</code>, <code>alt</code>, <code>spr</code>, <code>trk</code> etc.).
      <code>lp</code> for Loop is two letters. No more than six characters (for double-bannered routes) are allowed.</li>
      <li><a name="abbreviation"></a><a style="text-decoration:none" href="#abbreviation">&#x1f517</a>
      The <code>abbreviation</code> is a 3-letter abbreviation for auxiliary highways or for most piecemeal highways
      if needed to distinguish between otherwise identically named routes of the same highway in the same region.
      <code>pit</code> for Truck US 19 (Pittsburgh).</li>
      <li><a name="filenameexample"></a><a style="text-decoration:none" href="#filenameexample">&#x1f517</a>
      Examples:
      <ul>
        <li><code>aut.a003.wpt</code> = Austria A3.</li>
        <li><code>pa.pa042trkeag.wpt</code> = Pennsylvania PA 42 Truck (Eagles Mere).</li>
        <li><code>grc.a005.wpt</code> = Greece A5 (main piece).</li>
        <li><code>grc.a005art.wpt</code> = Greece A5 Arta section.</li>
      </ul>
    </ul>
  </li>
</ul>
</div>

<p class="heading"><a name="format"></a><a style="text-decoration:none" href="#format">&#x1f517</a>
<code>.wpt</code> Data Format</p>

<div class="text">
<ul>
  <li><a name="wptosm"></a><a style="text-decoration:none" href="#wptosm">&#x1f517</a>
  The format is simple. Each line as two fields separated by a space.
    <ol>
      <li>Waypoint label. This identifies the waypoint. </li>
      <li>OpenStreetMap URL that includes coordinates.</li>
    </ol>
  </li>
  <li><a name="formatexample"></a><a style="text-decoration:none" href="#formatexample">&#x1f517</a>
  For example, the first few lines of PA US 11
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
  <li><a name="noblank"></a><a style="text-decoration:none" href="#noblank">&#x1f517</a>
  No blank or comment lines. The file may end with a final return character.</li>
</ul>
</div>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
