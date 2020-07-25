<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Mapping Manual: System Highway Lists</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: System Highway Lists</h1>

<div class="text">Each highway system needs to have files that list basic 
information about each route that is part of the system. The Chopped 
Routes File (e.g., <a href="https://github.com/TravelMapping/HighwayData/blob/master/hwy_data/_systems/usai.csv"><code>usai.csv</code></a>)
tells our scripts which files to look for and to load into the web site. The Connected Routes File (e.g., 
<a href="https://github.com/TravelMapping/HighwayData/blob/master/hwy_data/_systems/usai_con.csv"><code>usai_con.csv</code></a>)
lists how those chopped routes are connected across boundaries to make full-length routes.</div>

<p class="heading">
  Contents</p>

<div class="text">
<ul>
  <li><a href="#createfiles">Creating the files</a></li>
  <ul>
    <li><a href="#chopped">Chopped Routes File (e.g., <code>usai.csv</code>) format</a></li>
    <li><a href="#connected">Connected Routes File (e.g., <code>usai_con.csv</code>) format</a></li>
    <li><a href="#intcharacters">International characters</a></li>
  </ul>
  <li><a href="#examplesystem">Example system: Takoma National H Routes (tach)</a></li>
  <li><a href="#concurrencies">Concurrencies within which not all concurrent routes are signed</a></li>
</ul>
</div>

<p class="heading"><a name="createfiles"></a><a style="text-decoration:none" href="#createfiles">&#x1f517</a>
Creating the files</p>

<div class="text">
<ul>
  <li><a name="csvfile"></a><a style="text-decoration:none" href="#csvfile">&#x1f517</a>
  Make a text file with comma-separated values containing all the needed info for each of these files.
  Use a <a href="https://en.wikipedia.org/wiki/Text_editor">text editor</a> or a spreadsheet program such as
  <a href="https://www.openoffice.org/product/calc.html">OpenOffice Calc</a> (it's free).
  Then save the file with <a href="https://en.wikipedia.org/wiki/Comma-separated_values"><code>.csv</code></a> filename extention.</li>
  <li><a name="csvsemicolon"></a><a style="text-decoration:none" href="#csvsemicolon">&#x1f517</a>
  Use a semicolon (not a comma!) as the field delimiter, and don't 
  use any text delimiters (no quotes!). In OpenOffice Calc, the default 
  delimiters are comma and double quotes, but instead you must specify 
  semicolons and none for the two types. Saving in UTF-8 encoding is necessary to ensure that international characters are properly handled.</li>
  <ul>
    <li><a name="csvoo"></a><a style="text-decoration:none" href="#csvoo">&#x1f517</a>
    <strong>In OpenOffice Calc, the three correct options when saving 
    the sheet as a <code>.csv</code> file are: UTF-8 encoding, semicolon delimiter, no 
    text delimiter. </strong></li>
  </ul>
  <li><a name="csvexcel"></a><a style="text-decoration:none" href="#csvexcel">&#x1f517</a>
  Excel doesn't cooperate with UTF-8 encoding and will not use semicolon delimiters when saving a <code>.csv</code> file. 
  It will save the file using commas. This is not good. You could do a search
  and replace on the <code>.csv</code> file to turn the commas into semicolons, but 
  beware: sometimes commas are part of the City and Roots fields.</br>
  <b>Best advice: Don't use Excel but download and use OpenOffice Calc or use a text editor.</b></li>
</ul>
</div>

<p class="subheading"><a name="chopped"></a><a style="text-decoration:none" href="#chopped">&#x1f517</a>
Chopped Routes File (e.g., <code>usai.csv</code>) format</p>

<div class="text">
<ul>
  <li><a name="spreatsheet"></a><a style="text-decoration:none" href="#spreatsheet">&#x1f517</a>
  Make a <code>.csv</code> file with the name <code>systemcode.csv</code>. Replace <code>systemcode</code>
  with the lowercase <a href="sysnew.php#developsystem">system code assigned to your system</a>. E.g., the 
  spreadsheet for the Ohio State Highways (system code = <code>usaoh</code>) is <code>usaoh.csv</code>.</li>
  <li><a name="header"></a><a style="text-decoration:none" href="#header">&#x1f517</a>
  The spreadsheet must have 8 columns and 1 header row. Use the 
  header row to label the columns, not to enter the first highway. In 
  processing, the first row is always ignored.<br />
  <br />
  <a name="columns"></a><a style="text-decoration:none" href="#columns">&#x1f517</a>
  The columns:</li>
  <ol>
    <li><a name="csystem"></a><a style="text-decoration:none" href="#csystem">&#x1f517</a>
    <strong>System</strong>: the short, all-lowercase system code 
    for the system. It should appear in every row 
    of the spreadsheet even though it seems redundant. It is also used for 
    the filename above.</li>
    <li><a name="cregion"></a><a style="text-decoration:none" href="#cregion">&#x1f517</a>
    <strong>Region</strong>:
    For undivided countries, this is the uppercase 3-letter country abbreviation in which the highway is located.
    Country subdivision codes are used instead or in addition for divided countries as <a href="hwydata.php#regionroute">
    used in the <code>.wpt</code> file name</a> but all-uppercase and with hyphen (no spaces),
    e.g. <code>FL</code> for Florida, <code>NT</code> for Northwest Territories,
    <code>DEU-TH</code> for Thuringia, <code>ESP-AR for</code> for Aragon, <code>MEX-BC</code> for Baja California.</li>
    <li><a name="croute"></a><a style="text-decoration:none" href="#croute">&#x1f517</a>
    <strong>Route</strong>: The name of the highway, ignoring any banners or qualifiers. No spaces! <code>US34</code> for US 34,
    <code>OH17</code> for OH 17, <code>PA66</code> for Business PA 66, <code>A7</code>
    for French Autoroute A7. Skip hyphens and slashes unless they separate 
    two numbers (M22-1), the route is a US Interstate or Business Interstate (I-80BL),
    or is a Quebec Autoroute (A-50). </li>
    <li><a name="cbanner"></a><a style="text-decoration:none" href="#cbanner">&#x1f517</a>
    <strong>Banner</strong>: For bannered routes, put the 3-letter banner abbreviation(s) (<code>Bus</code>, <code>Alt</code>, <code>Spr</code>, etc.)
    here only if needed. Otherwise leave it completely blank (no whitespace). <code>Lp</code> for Loop is two letters. No more than six characters
    (for double-bannered routes) are allowed.</li>
    <li><a name="cabbrev"></a><a style="text-decoration:none" href="#cabbrev">&#x1f517</a>
    <strong>Abbreviation</strong>: The three-letter abbreviation for auxiliary highways or for most piecemeal highways
    if needed to distinguish between otherwise identically named routes of the same highway in the same region.
    <code>Pit</code> for Truck US 19 (Pittsburgh). Otherwise leave it blank (no whitespace).</li>
    <li><a name="ccity"></a><a style="text-decoration:none" href="#ccity">&#x1f517</a>
    <strong>City</strong>: Used to distinguish between otherwise identically named routes of the same highway in the same region.
    <code>Pittsburgh</code> for Truck US 19 (Pittsburgh).
    Prefer using city names (if applicable) or other common geographical names to distinguish, e.g. island names.
    This is the text that will appear below 
    the shield in the highway browser. Usually no text is needed except for 
    auxiliary highways, so leave it blank (no whitespace) in most cases. The
    name should be spelled as the locals spell it and using international 
    characters as needed. </li>
    <li><a name="croot"></a><a style="text-decoration:none" href="#croot">&#x1f517</a>
    <strong>Filename root:</strong> The <a href="hwydata.php#regionroute">name of the <code>.wpt</code> file<a> with the extension omitted.
    <code>pa.us019trkpit</code>, <code>oh.oh007</code>, etc.</li>
    <li><a name="caltroute"></a><a style="text-decoration:none" href="#caltroute">&#x1f517</a>
    <strong>Alt Route Names</strong> A comma-separated list of 
    deprecated route names, or blank if none.  This field is blank for most 
    routes and not blank only when necessary to handle route merges or name 
    changes.  While the primary route name is usually split into the Route, 
    Banner, and Abbrev columns, any alt route names have these 3 parts 
    concatenated. The result should be whatever would be entered in a <code>.list</code> file for the old name of the route, e.g. I-22FutTup.</li>
  </ol>
  </br>
  <li><a name="routeorder"></a><a style="text-decoration:none" href="#routeorder">&#x1f517</a>
  Order of the routes in the file</li>
<ul>
  <li><a name="rascending"></a><a style="text-decoration:none" href="#rascending">&#x1f517</a>
  <b>Route numbers are ascending by route number.</b> <br />
  PA3, PA5, PA8, etc. </li>
  <li><a name="rsuffix"></a><a style="text-decoration:none" href="#rsuffix">&#x1f517</a>
  <b>For like route numbers</b>, suffixless routes come first, followed by suffixed routes of the same number. <br />
  MA2, MA3, MA3A, MA3B, MA4, MA4B, MA4H, MA5, etc.</li>
  <li><a name="rusualorder"></a><a style="text-decoration:none" href="#rusualorder">&#x1f517</a>
  For pieces of the same route, put them in the usual order for the country (i.e., south to north or west to east in the US).<br />
  ..., PA42, PA43 Chadville, PA43 Brownsville, PA43 Pittsburgh, PA43AltCal, PA44, etc. </li>
  <li><a name="rbanner"></a><a style="text-decoration:none" href="#rbanner">&#x1f517</a>
  <b>Bannered routes of a certain number</b> come immediately after the 
  bannerless route of the same number and before any suffixed routes of 
  the same number. Bannered routes of the same number but with different 
  banners go in order of the banners. Bannered routes of the same number 
  and banner go in the usual order for the country (e.g., south to north 
  or west to east in the US). <br />
  ..., PA42, PA42AltCen, PA42AltBlo, PA42TrkEag, PA42A, PA42ABusPit, PA42B, PA43, ...</li>
  <li><a name="rbannerdouble"></a><a style="text-decoration:none" href="#rbannerdouble">&#x1f517</a>
  <b>Doubly bannered routes</b> come right after the matching singly 
  bannered route. The banner immediately after the route number here 
  matches the banner immediately above the route number as signed in a 
  shield. <br />
  US50AltBusDun is the Dunkirk City business route of US50Alt. 
  US50AltBusDun is signed with a Business banner on top, Alternate banner 
  in the middle, and the number shield at the bottom on a roadside sign 
  assembly.<br />
  ..., US50, US50AltGeo, US50AltBusDun, US50BusFay, US50BusTrkUni, US50ScePin, US50TrkSno, US 51, ...<br /></li>
</ul>
</ul>
</div>

<p class="subheading"><a name="connected"></a><a style="text-decoration:none" href="#connected">&#x1f517</a>
Connected Routes File (e.g., <code>usai_con.csv</code>) format</p>

<div class="text">
<ul>
  <li><a name="connchop"></a><a style="text-decoration:none" href="#connchop">&#x1f517</a>
  The Chopped Routes File lists all the highways in the highway system
  after being chopped at national and sometimes subdivisional (state, 
  province, oblast, etc.) borders.  This Connected Routes File gives the 
  information about which chopped routes should be connected to 
  reconstruct each full route.</li>
  <li><a name="connname"></a><a style="text-decoration:none" href="#connname">&#x1f517</a>
  The filename is <code>systemcode_con.csv</code>, with <code>systemcode</code> replaced by the <a href="sysnew.php#developsystem">code for the system</a>.</li>
  <li><a name="connheader"></a><a style="text-decoration:none" href="#connheader">&#x1f517</a>
  The spreadsheet must have 5 columns and 1 header row. Use the 
  header row to label the columns, not to enter the first highway. In 
  processing, the first row is always ignored.</li>
  </br>
  <li><a name="conncolumns"></a><a style="text-decoration:none" href="#conncolumns">&#x1f517</a>
  The columns:</li>
  <ol>
    <li><a name="conncsystem"></a><a style="text-decoration:none" href="#conncsystem">&#x1f517</a>
    <b>System:</b> system code.</li>
    <li><a name="conncroute"></a><a style="text-decoration:none" href="#conncroute">&#x1f517</a>
    <b>Route:</b> the common Route name, like US52.</li>
    <li><a name="conncbanner"></a><a style="text-decoration:none" href="#conncbanner">&#x1f517</a>
    <b>Banner:</b> the common Banner, if the route is bannered, or left blank if not.</li>
    <li><a name="conncname"></a><a style="text-decoration:none" href="#conncname">&#x1f517</a>
    <b>Name:</b> like the <a href="#ccity">City field of the Chopped Routes File</a>, the 
    Name field is used to distinguish between otherwise identically named 
    routes (same Route and Banner fields) or to give extra info about 
    numberless or bannered/repeatable designations.
    Use geographical names, e.g. city names, island names or country names.
    <ul>
      <li><a name="conncname_oneroute"></a><a style="text-decoration:none" href="#conncname_oneroute">&#x1f517</a>
      <b>If there is exactly one row with a certain Route+Banner combination,</b>
      the Name should be blank.</li>
      <li><a name="conncname_multirows"></a><a style="text-decoration:none" href="#conncname_multirows">&#x1f517</a>
      <b>If there are multiple rows with the same Route+Banner combination</b>,
      each row should have a nonblank Name. In short, long, multi-region 
      routes will have Names composed from the region names, while shorter, 
      single-region routes will have Names devised identically to the Name
      field in the Chopped Routes File. Here "region" 
      means the country or the subdivision in a divided country.</li>
      <li><a name="conncname_oneregion"></a><a style="text-decoration:none" href="#conncname_oneregion">&#x1f517</a>
      <b>If the route is lengthy and within one region</b>, use the region
      name as the Name.  For example, the southern US 9 is entirely in 
      Delaware, so the Name should be "Delaware" (and not "DE").</li>
      <li><a name="conncname_multiregion"></a><a style="text-decoration:none" href="#conncname_multiregion">&#x1f517</a>
      <b>If the route is lengthy and spans multiple regions</b>, use both 
      region names separated by space-hyphen-space.  Put the region names in 
      the usual order for the system (e.g., region containing the southern or 
      western end first if in the US).  For example, the northern US 9 spans 
      New Jersey and New York in that order, so the Name should be "New Jersey
      - New York".</li>
      <li><a name="conncname_direction"></a><a style="text-decoration:none" href="#conncname_direction">&#x1f517</a>
      <b>If applying these rules results in the same region used in different Names for rows with the same Route+Banner</b>,
      add an abbreviated direction (NW., W., C., etc.) to that region name in
      each Name it appears.  For example, the western US 422 would be Named 
      "Ohio - W. Pennsylvania" and the eastern US 422 would be Named "E. 
      Pennsylvania".  Alternatively, if one route is wholly on a large island 
      within the region (of size like that of Crete or larger), use the island
      name along with the region abbreviation.  For example, E25 has a piece 
      "Netherlands - Italy" that includes mainland France, as well as pieces 
      in the large islands of Corsica, France, and Sardinia, Italy.  The three
      piece Names should be "Netherlands - Italy", "Corse, FRA", and 
      "Sardegna, ITA".</li>
      <li><a name="conncname_shorter"></a><a style="text-decoration:none" href="#conncname_shorter">&#x1f517</a>
      <b>If a route is more local (shorter), is a full beltway, or is a bannered/repeatable route type</b>
      (mandatory City and Abbrev in the Chopped Routes File), then the Name 
      should be devised in the same way as the City field in the Chopped 
      Routes File (and in most cases, the Name and City fields should be 
      identical).</li> 
      <li><a name="conncname_discontinuous"></a><a style="text-decoration:none" href="#conncname_discontinuous">&#x1f517</a>
      <b>If there are two or more discontinuous routes of the same 
      Route+Banner combination, each wholly within the same, single region, 
      and one is significantly longer than the rest</b>, the above rules would
      suggest using the region name for the long piece and city names for the
      short ones.  Since the region name as the Name doesn't distinguish the 
      pieces well, change the Name to either "(Main)" (parentheses included) 
      or give this piece a city name.  For example, if there are 200-mile and 8-mile
      disconnected sections of US 47 in the same state, use "(Main)" or a
      city for the long section and a city name for the short section.</li>
    </ul>
    </li>
    <li><a name="conncroots"></a><a style="text-decoration:none" href="#conncroots">&#x1f517</a>
    <b>Roots:</b> a comma-separated list (no spaces!) of filename roots 
    of the chopped routes that connect to form this connected route.  
    Continuous roots that were chopped at boundaries will have a list of 2+ 
    file roots, while a route that exists entirely in one region will simply
    have one root. Always put the list of file roots in the correct order to make sure
    that the chopped routes indeed compose a continuous route. Remove all spaces.</li>
  </ol>
  </br>
  <li><a name="connorder"></a><a style="text-decoration:none" href="#connorder">&#x1f517</a>
  Each row gives info about the connected routes in the <a href="#routeorder">same order as they are given in the Chopped Routes File</a>.</li>
</ul>
</div>

<p class="subheading"><a name="intcharacters"></a><a style="text-decoration:none" href="#intcharacters">&#x1f517</a>
International characters</p>

<div class="text">
<ul>
  <li><a name="intnames"></a><a style="text-decoration:none" href="#intnames">&#x1f517</a>
  The System, Region, Route, Banner, Abbreviation, Filename Root, Alt Route Names and Roots
  fields must be devoid of international characters 
  since these appear in <code>.list</code> files and as filenames. Pick the closest 
  character without any diacritical marks, e.g., "o" for "ö", if it would 
  appear in the Abbreviation field: "Kol" for Köln.</li>
  <li><a name="intnative"></a><a style="text-decoration:none" href="#intnative">&#x1f517</a>
  The <a href="#ccity">City</a> and the <a href="#conncname">Name</a> fields should use the native language name for a place, and it may
  use international characters. "München", not "Munchen" nor "Munich". </li>
  <li><a name="intencoded"></a><a style="text-decoration:none" href="#intencoded">&#x1f517</a>
  International characters will appear as weird symbols or question 
  marks on the web site if the encoding isn't properly done. OpenOffice Calc
  will do this properly when saving the spreadsheet as a <code>.csv</code> file.
  Type the international characters into the spreadsheet. Choose "UTF-8" 
  when saving as a <code>.csv</code> file, see <a href="#csvoo">above</a>.</li>
</ul>
</div>
    
<p class="heading"><a name="examplesystem"></a><a style="text-decoration:none" href="#examplesystem">&#x1f517</a>
Example system: Takoma National H Routes (tach)</p>

<p class="text">Takoma is the country, and it has 3 states:<br />
NT - North Takoma<br />
CT - Central Takoma<br />
ST - South Takoma<br />
(Or Takoma could be some large area with these as 3 countries. It does not matter.)
</p>

<p class="text">The national H route system spans the 3 states with routes H1 - H4.</p>

<p class="text">Map:
<img src="tach_002.gif"></p>


<p class="text">H1 is in NT only.<br />
H2 passes through ST, CT, and NT.<br />
H3 has two parts, one spanning ST and CT, and the other in NT.<br />
H3 has a bannered Alt route around Capital City, CT.<br />
H4 has two parts, both in NT. The Springfield part is the main part, and the Shelbyville part is shorter.</p>

<p class="text">The chopped routes file tach.xls looks like this*:<br />
<img src="tach.gif">
</p>

<p class="text">It lists each highway once per region. This is the same format we've been using since 2009*.</p>

<p style="font-size:x-small;">*This section of the manual was written 
before the AltRouteNames column was added to the Chopped Routes files.  
The AltRouteNames column should be included in these files, even though 
these examples don't show it.</p>
<p class="text">The connected routes file tach_con.xls looks like this:<br />
<img src="tach_con.gif">
</p>

<p class="text">Each line represents one route in total rather than one in each 
region. The Roots column shows a list of the file root names that 
comprise that route, delimited by commas only and not also with spaces.</p>

<p class="text">The Name column takes on a role similar to the City column of the 
chopped routes file. It is a short amount of text to distinguish between
 routes with identical Route and Banner fields.</p>

<p class="text">The Region and Abbrev columns do not appear in the 
connected routes file because my scripts can look up this info from the 
chopped roots file by connecting the two files via the file roots. So 
this info is not repeated in the <code>.csv</code> files.</p>

<p class="text">Since H1 and H2 are unique after being connected across the boundaries, no Name is necessary.</p>

<p class="text">There are multiple H3's and H4's, so each piece gets a Name. The 
two-state H3 Name lists the states at its ends with a hyphen in between.
 If there were more states and it spanned more than two, only the two 
endpoint states would be the ones listed. The other H3 is within a 
single state, so that state is the Name.</p>

<p class="text">The two H4s are in the same region, so the Name is the main city that
 each serves, and in fact it is the City from the chopped routes file.</p>

<p class="text">The City for bannered routes like H3 Alt is mandatory in the chopped 
routes file, and the same city is used as the Name in the connected 
routes file.</p>

<p class="text">Many of our highway systems do not have highways spanning more than 
one region. For example, the Pennsylvania State Highways are completely 
within Pennsylvania. This means that all the rows of the connected 
routes file will have a single file root. The bannered routes like PA 8 
Truck and the duplicated routes like PA 29 and PA 97 will need Name 
fields.</p>

<p class="text">In some other systems, there are only a small number of routes that 
cross a border without changing designation. For example, the New York 
State Highways, has only NY 17 running into PA and NY 120A dipping into 
CT. Most rows of the connected routes file will have a single file root,
 but the row for NY 17 and the row for NY 120A will have a few roots 
separated by commas.</p>

<p class="text">In the major multi-region-spanning systems, like the I &amp; US 
highways in the US, the TCH in Canada, the M and A routes in Great 
Britain, and the Int'l E Roads, the rows of the connected routes file 
will vary greatly in the number of file roots listed.</p>


<p class="heading"><a name="concurrencies"></a><a style="text-decoration:none" href="#concurrencies">&#x1f517</a>
Concurrencies within which not all concurrent routes are signed</p>

<p class="text">This section concerns typically well signed routes
 that whose numbers are signed with trailblazers or are not signed at
 all within a section of highway concurrent with other routes.  For
 example, France's A4 and A26 merge and split, but along the merged
 section, A4 is signed and A26 is not, but both routes are signed
 beyond the concurrent section. Should the not-signed routes be
 chopped into its signed pieces or made continuous and concurrent with
 the signed route?</p>

<p class="text">We have 4 cases that are treated differently. The descriptions refer 
to concurrencies of two routes, but the ideas generalize to 
concurrencies of more routes.</p>

<div class="text">
<ol>
  <li><p><a name="impliedmultiplexes"></a><a style="text-decoration:none" href="#impliedmultiplexes">&#x1f517</a>
  <strong>Unsigned but implied multiplexes: Treat as continuous routes.</strong></p>

This is the case where only one route is signed where another one route 
merges onto the same road. Usually the unsigned route splits off at 
another point, then it's signed beyond the concurrency. Continuity is 
still implied by the way the routes are numbered even if the signs are 
simplified to show only one route, so we treat each route as a 
continuous one. 

<p>Examples:</p>
<p>USA MD 23/MD 165: 
MD 23 was continuously signed before a relocation that created the 
duplex.  In the current state, MD 23 is signed as "TO MD 23" at its 
approaches to the duplex, and MD 165 is signed continuously. MD 23 
should continue to be treated as continuous.</p>

<p>ENG A414:
Follow the length of A414 and you'll see several concurrent routes, 
sometimes shown as A414 and sometimes as the other route, at least as 
Google Maps shows it. A system of surface highways with a bypass here 
and there is bound to be full of concurrencies, and so chopping half the
 routes into pieces around the concurrent parts would create a zillion 
"extra" files for short pieces of routes.</p>

<p>FRA A4/A26:
The two freeways merge and split. The pieces of A26 could have been 
given different numbers, but instead they were given the same number, as
 if it should be one long route rather than two.</p>
  </li>
  <li><p><a name="discontinuous"></a><a style="text-decoration:none" href="#discontinuous">&#x1f517</a>
  <strong>Bypassed, segmented routes: Discontinuous routes.</strong></p>

<p>Here some pieces of an old route were bypassed by a new route, but 
other pieces of the old route were upgraded into the new route. This 
makes a continuously signed new route with pieces of the old route 
beginning and ending at various places along the new route. </p>

<p>Examples:</p>

<p>Bannered highways, like Alternate and Business routes, in the US:
Many US highways, for example, have many auxiliary routes with the same 
designation, like US 40 having many US 40 Business routes.  The 
auxiliary routes are treated discontinuously, rather than having one 
long, continuous US 40 Business concurrent along sections of US 40.</p>
<p>US 40/MD 144: 
There are several pieces of MD 144 along the old alignment of US 40. The
 pieces act like Business or Alternate routes and are never signed to 
suggest continuity. </p>
  </li>
  <li><p><a name="alternating"></a><a style="text-decoration:none" href="#alternating">&#x1f517</a>
  <strong>Alternating designation: Discontinuous routes.</strong></p>
  <p>A road changes designations back and forth without either route splitting off on its own.</p>
  <p>Example:</p>
  Ireland's M/N routes come to mind here. Part of N8 was upgraded to M8, 
  but there is no alternative N8 along that section. However, N8 leads 
  straight into M8 at each end of M8. So if the highway goes N8-M8-N8, 
  we'll have three files for these three routes. 
  </li>
  <li><p><a name="notconcurrent"></a><a style="text-decoration:none" href="#notconcurrent">&#x1f517</a>
  <strong>Like designations that aren't concurrent: Discontinuous routes.</strong></p>
  <p>By whatever reasoning, two unrelated, distant highways were given the same designation.</p>
  <p>Example:</p>
  <p>PA 97 (in NW Pennsylvania) and PA 97 (in southern PA), both part of the state highway system in Pennsylvania.</p>
  </li>
</ol>
</div>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
