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

<div class="text">Each route in TM is represented by a highway data
file, which we often refer to as a "waypoint file".  These files have
a .wpt extension.</div>

<p class="heading">
  Contents</p>

<div class="text">
<ul>
  <li><a href="#filename">.wpt Filenames</a></li>
  <li><a href="#format">.wpt Data Format</a></li>
</ul>
</div>



<p class="heading"><a name="filename"></a><a style="text-decoration:none" href="#filename">&#x1f517</a>
.wpt Filenames</p>

<div class="text">
<ul>
  <li><a name="fileborder"></a><a style="text-decoration:none" href="#fileborder">&#x1f517</a>
  One .wpt file is needed for each highway in each region. Highways 
  crossing into multiple subdivisions of a subdivided country (e.g., 
  states in the USA), or crossing into multiple countries (e.g., 
  UNECE International 'E' Roads in Europe) must be chopped at borders into separate files for
  each region. </li>
  <li><a name="lowercase"></a><a style="text-decoration:none" href="#lowercase">&#x1f517</a>
  Filenames are entirely lowercase and have a .wpt extension.</li>
  <li><a name="fileextension"></a><a style="text-decoration:none" href="#fileextension">&#x1f517</a>
  Be sure you can see file extensions in your operating system. 
  Windows hides known file extensions by default, so if you see a ".wpt" 
  extension, the file might actually have something else appended, such as
  ".wpt.txt". This is no good. In Windows Explorer, you can disable the 
  "Hide extensions for known file types" option in Tools menu &gt; Folder 
  Options &gt; View tab. </li>
  <li><a name="regionroute"></a><a style="text-decoration:none" href="#regionroute">&#x1f517</a>
  <code>region</code> + <code>.</code> + <code>route</code> ( + <code>banner</code> + <code>abbreviation</code> ) + <code>.wpt</code> is the format for the filename.
    <ul>
      <li><code>Region</code> is the region code without any hyphens:</li>
      <ul>
      <li><a name="undivided"></a><a style="text-decoration:none" href="#undivided">&#x1f517</a>
      For undivided countries, the <code>region</code> is the 3-letter ISO 3166-1 alpha-3 country code,
      e.g. IDN for Indonesia, LUX for Luxembourg, NZL for New Zealand. See our list
      <a href="https://github.com/TravelMapping/HighwayData/blob/master/countries.csv">on
      GitHub</a>, which is based
      on the <a href="https://www.cia.gov/library/publications/the-world-factbook/appendix/appendix-d.html">CIA
      World Fact Book</a>. </li>
      <li><a name="divided"></a><a style="text-decoration:none" href="#divided">&#x1f517</a>
      For divided countries the <code>region</code> is an abbreviation for the subdivision rather
      than for the country.</li>
      <ul>
        <li>The USA and Canada use the standard postal codes.</li>
        <li>The United Kingdom uses WLS, SCT, ENG, NIR.</li>
        <li>Mexico uses the subdivision codes that appear on state route shields with the country code
        prepended to avoid collisions with subdivision codes of other countries (NL = Newfoundland and
        Labrador in Canada, MEX-NL = Nuevo León in Mexico).</li>
        <li>The ISO 3166-2 country subdivision codes with the country code prepended are used for other
        countries (e.g. AUS-ACT, CHN-AH, DEU-BW, ESP-AN, FRA-ARA, IND-AN).</li>
        <li>See our list
        <a href="https://github.com/TravelMapping/HighwayData/blob/master/regions.csv">on GitHub</a>.</li>
      </ul>
      <li><a name="route"></a><a style="text-decoration:none" href="#route">&#x1f517</a>
      The <code>route</code> is the name of the highway (number padded with zeroes as needed), ignoring any banners or qualifiers.
      No spaces! us034 for US 34, oh017 for OH 17, pa066 for Business PA 66, a007 for French Autoroute A7.
      Skip hyphens and slashes.</li>
      <li><a name="banner"></a><a style="text-decoration:none" href="#banner">&#x1f517</a>
      The <code>banner</code> (if needed) is a 3-letter banner abbreviation (bus, alt, spr, trk etc.).
      lp for Loop is two letters. No more than six characters (for double-bannered routes) are allowed.</li>
      <li><a name="abbreviation"></a><a style="text-decoration:none" href="#abbreviation">&#x1f517</a>
      The <code>abbreviation</code> (if needed) is a 3-letter city abbreviation if needed for auxiliary highways
      or for most piecemeal highways. pit for Truck US 19 (Pittsburgh).</li>
      <li><a name="filenameexample"></a><a style="text-decoration:none" href="#filenameexample">&#x1f517</a>
      Examples:
      <ul>
        <li>aut.a003.wpt = Austria A3.</li>
        <li>pa.pa042trkeag.wpt = Pennsylvania PA 42 Truck (Eagles Mere).</li>
        <li>  grc.a005.wpt = Greece A5 (main piece).</li>
        <li>grc.a005art.wpt = Greece A5 Arta section.</li>
      </ul>
      </li>
    </ul>
  </li>
</ul>
</div>

<p class="heading"><a name="format"></a><a style="text-decoration:none" href="#format">&#x1f517</a>
.wpt Data Format</p>

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
