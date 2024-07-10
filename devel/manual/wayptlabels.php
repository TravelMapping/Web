<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping Manual: Labeling Waypoints</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: Labeling Waypoints</h1>



<p class="heading">
  Contents</p>

<div class="text">
<ul>
  <li><a href="#labeling">Labeling Waypoints</a></li>
  <li><a href="#borderpoints">Border Points</a></li>
  <li><a href="#exitnumbered">Interchanges on exit-numbered highways</a></li>
  <li><a href="#visiblynumbered">Intersections with visibly numbered highways</a></li>
  <li><a href="#named">Intersections with named highways</a></li>
  <li><a href="#other">Other intersections</a></li>
  <li><a href="#highwayends">Highway ends at non-intersections or non-borders</a></li>
  <li><a href="#2highways">Putting two highways in a waypoint label</a></li>
  <li><a href="#multiplex">Waypoint labels for multiplexes</a></li>
  <li><a href="#disambiguation">Distinguishing otherwise identical waypoints (not for exit numbers)</a></li>
  <li><a href="#auxroutes">Waypoints for ends of auxiliary routes (bannered routes and the suffixed equivalents)</a></li>
  <li><a href="#noname">Waypoints for roads that no longer have a name or no longer exist as a road</a></li>
</ul>
</div>



<p class="heading"><a name="labeling"></a><a style="text-decoration:none" href="#labeling">&#x1f517</a>
  Labeling Waypoints</p>

<div class="text">
The following list shows the preferred waypoint type in order. Choose the first type that applies.
<ul>
  <li><a name="desired_waypoints"></a><a style="text-decoration:none" href="#desired_waypoints">&#x1f517</a>
    Desired waypoints.
    <ol>
      <li><a name="desired_borderpoints"></a><a style="text-decoration:none" href="#desired_borderpoints">&#x1f517</a>
	<a href="#borderpoints"><b>Border points:</b></a> These points often begin and end files. If a 
	required intersection coincides with a border point, use a border point 
	and skip the intersection point. The only border points in use are 
	international boundaries (all countries) and subdivisional boundaries 
	(for only countries we subdivide in this project). </li>
      <li><a name="desired_exitnumbers"></a><a style="text-decoration:none" href="#desired_exitnumbers">&#x1f517</a>
	<a href="#exitnumbered"><b>Exit numbers:</b></a> If the highway has interchanges with exit numbers for itself.</li>
      <li><a name="desired_visiblynumbered"></a><a style="text-decoration:none" href="#desired_visiblynumbered">&#x1f517</a>
	<a href="#visiblynumbered"><b>Visibly numbered cross road designation:</b></a> US 42, A17, I-40 Business Loop, etc. </li>
      <li><a name="desired_named"></a><a style="text-decoration:none" href="#desired_named">&#x1f517</a>
	<a href="#named"><b>Truncated, visible cross road name:</b></a> Magothy Bridge Road, etc.</li>
    </ol>
    </li>
  <li><a name="inadequatedata"></a><a style="text-decoration:none" href="#inadequatedata">&#x1f517</a>
	Waypoints for inadequate highway data or other rare situations. 
    Use these only when you are unable to properly label the waypoint 
    according to the above options.
    <ol start="5">
      <li><a name="junctionname"></a><a style="text-decoration:none" href="#junctionname">&#x1f517</a>
	<b>Truncated junction name:</b> For freeways/expressways with 
	official destination-based named junctions (e.g., some European 
	countries use these).</li>
      <li><a name="trailblazer"></a><a style="text-decoration:none" href="#trailblazer">&#x1f517</a>
	<b>Trailblazer label:</b> The type ToA5, if the cross road immediately serves a more major highway like A5. </li>
      <li><a name="parks"></a><a style="text-decoration:none" href="#parks">&#x1f517</a>
	<b>Entrance roads for national/state/local parks or other tourist attractions:</b>
    If immediately served by the cross road, a truncated version of the park's or attraction's name is sufficent.</li>
      <li><a name="nearbytown"></a><a style="text-decoration:none" href="#nearbytown">&#x1f517</a>
	<b>Truncated, nearby town name:</b> For a town immediately served by the unnamed cross road.</li>
      <li><a name="distanttown"></a><a style="text-decoration:none" href="#distanttown">&#x1f517</a>
	<b>Truncated, distant town name:</b> A more distant location that 
	either the cross road serves or that is mentioned on guide signs at the 
	junction. You couldn't find anything near the intersection and now 
	you're grabbing for just about anything to use to identify the waypoint.</li>
    </ol>
  </li>
  <li><a name="avoidsecret"></a><a style="text-decoration:none" href="#avoidsecret">&#x1f517</a>
    Waypoint labels should avoid secret highway designations, i.e., 
    designations travelers will not know about by driving and reading signs.
 </li>
  <li><a name="nospaces"></a><a style="text-decoration:none" href="#nospaces">&#x1f517</a>
    Waypoint labels never have spaces. </li>
  <li><a name="latin"></a><a style="text-decoration:none" href="#latin">&#x1f517</a>
    Waypoint labels may never use international characters. Instead, use the closest latin character without a diacritical mark.</li>
  <li><a name="englishsuffix"></a><a style="text-decoration:none" href="#englishsuffix">&#x1f517</a>
    Suffixes such as _W always refer to English direction words such 
    as "west" and never to direction words in other languages, i.e., don't 
    use _O for "oeste."</li>
</ul>
</div>



<p class="heading"><a name="borderpoints"></a><a style="text-decoration:none" href="#borderpoints">&#x1f517</a>
  Border Points</p>

<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="country"></a><a style="text-decoration:none" href="#country">&#x1f517</a></td>
    <td>USA/CAN</td>
    <td>International borders use the 3-letter country codes with a 
      slash in between. Put them in the order that matches the waypoint order.
      USA/CAN is the first waypoint for a Canadian highway beginning at the 
      USA border, or the last waypoint for a USA highway ending at the CAN 
      border. </td>
  </tr>
  <tr valign="top">
    <td><a name="subdiv"></a><a style="text-decoration:none" href="#subdiv">&#x1f517</a></td>
    <td>AL/MS</td>
    <td>Subdivision (state/province/oblast etc.) borders are included 
      only for countries that we subdivide.</td>
  </tr>
  <tr valign="top">
    <td><a name="skipcountry"></a><a style="text-decoration:none" href="#skipcountry">&#x1f517</a></td>
    <td>CHIH/SON</td>
    <td>For subdivision borders in subdivided countries whose region codes have the country code
      prepended, skip the country code. MEX-CHIH/MEX-SON becomes CHIH/SON. </td>
  </tr>
  <tr valign="top">
    <td><a name="county"></a><a style="text-decoration:none" href="#county">&#x1f517</a></td>
    <td>Yor/Lan</td>
    <td>In the rare case of a highway ending at a county or other 
      border, use the first three letters of the subdivision name for each 
      side. </td>
  </tr>
  <tr valign="top">
    <td><a name="city"></a><a style="text-decoration:none" href="#city">&#x1f517</a></td>
    <td>HarLim</td>
    <td>In the rare case of a highway ending at city limits and the city
      is part of the surronding area rather than considered separate from it,
      truncate the city name like a named highway (see below) and add "Lim". </td>
  </tr>
</tbody></table>



<p class="heading"><a name="exitnumbered"></a><a style="text-decoration:none" href="#exitnumbered">&#x1f517</a>
  Interchanges on exit-numbered highways</p>

<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="exitnumbers"></a><a style="text-decoration:none" href="#exitnumbers">&#x1f517</a></td>
    <td>PA581:<br />
      4<br />
      16<br />
      18<br />
      21</td>
    <td>If the highway is a freeway and has a unique exit numbering 
      system based on its own designation without the exit numbers restarting,
      use the exit numbers as waypoint labels.</td>
  </tr>
  <tr valign="top">
    <td><a name="lowernumber"></a><a style="text-decoration:none" href="#lowernumber">&#x1f517</a></td>
    <td>4</td>
    <td>Exits 4 &amp; 5 in one interchange. For a single interchange with ramps given different exit numbers, use the lower number. </td>
  </tr>
  <tr valign="top">
    <td><a name="lowerletter"></a><a style="text-decoration:none" href="#lowerletter">&#x1f517</a></td>
    <td>4B</td>
    <td>Exits 4B &amp; 4C in one interchange. Use the lower (toward A) letter.</td>
  </tr>
  <tr valign="top">
    <td><a name="dropa"></a><a style="text-decoration:none" href="#dropa">&#x1f517</a></td>
    <td>4 or 4A </td>
    <td>Exits 4A &amp; 4B in one interchange. Usually drop the letter if
      the lower letter is A. If there is another interchange with the same 
      number and different letters, optionally keep the A. </td>
  </tr>
  <tr valign="top">
    <td><a name="keepletter"></a><a style="text-decoration:none" href="#keepletter">&#x1f517</a></td>
    <td>4A or 4 </td>
    <td>If an interchange has a letter-suffixed exit number in one direction
      only, usually keep the letter. If the letter is A, optionally drop it. </td>
  </tr>
  <tr valign="top">
    <td><a name="nextsuffix"></a><a style="text-decoration:none" href="#nextsuffix">&#x1f517</a></td>
    <td>6<br />
      6A</td>
    <td>Two separate interchanges numbered 6. Distinguish them by 
      picking the next available letter suffix for the second interchange. 
      Number the first interchange normally. </td>
  </tr>
  <tr valign="top">
    <td><a name="exitconc_num_on_num"></a><a style="text-decoration:none" href="#exitconc_num_on_num">&#x1f517</a></td>
    <td>I-80:<br />
      56<br />
        87(75)<br />
        89(75)<br />
        63
      </td>
    <td>In multiplexes where the concurrency uses exit numbers from the 
      other highway, put the highway number in parentheses. Drop the letter 
      prefix of the concurrent highway if it is more than one character long: 
      I-75 becomes (75). A5 can stay as (A5). </td>
  </tr>
  <tr valign="top">
    <td><a name="exitconc_name_on_num"></a><a style="text-decoration:none" href="#exitconc_name_on_num">&#x1f517</a></td>
    <td>I-80:<br />
      56<br />
        87(Gar)<br />
        89(Gar)<br />
        63
      </td>
    <td>If the concurrent highway uses exit numbers but has a name 
      instead of a number, use the truncated first word: Garden State Parkway 
      is truncated as GarStaPkwy, and use the first part that is not the 
      generic highway type: (Gar) for Garden State Parkway, (Bol) for 
      Tangenziale di Bologna. </td>
  </tr>
  <tr valign="top">
    <td><a name="noconc"></a><a style="text-decoration:none" href="#noconc">&#x1f517</a></td>
    <td>A5:<br />
      56<br />
      57<br />
      1(A)<br />
      4(A)<br />
      17(A)</td>
    <td>If there is more than one exit number sequence for a highway and
      no concurrent route to explain it, suggest a proposal in the forum what parenthetical 
      distinction should be used. All exit numbers in a sequence get the same 
      parenthetical suffix. </td>
  </tr>
</tbody></table>



<p class="heading"><a name="visiblynumbered"></a><a style="text-decoration:none" href="#visiblynumbered">&#x1f517</a>
  Intersections with visibly numbered highways</p>

<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="numbereddesignation"></a><a style="text-decoration:none" href="#numbereddesignation">&#x1f517</a></td>
    <td>I-80<br />
      US42<br />
      MI67</td>
    <td>Generally use the number designation instead of a highway name.</td>
  </tr>
  <tr valign="top">
    <td><a name="stateabbrev"></a><a style="text-decoration:none" href="#stateabbrev">&#x1f517</a></td>
    <td>CHIH7<br />
      UT65<br />
      NL420</td>
    <td>USA &amp; MEX state and CAN provincial numbered highways begin 
with their abbreviation regardless of a local convention (Michigan State
 Highway 43 is MI43, not M-43). </td>
  </tr>
  <tr valign="top">
    <td><a name="hyphen"></a><a style="text-decoration:none" href="#hyphen">&#x1f517</a></td>
    <td>I-80<br />
      A-73</td>
    <td>USA Interstates and Quebec Autoroutes retain hyphens.</td>
  </tr>
  <tr valign="top">
    <td><a name="drophyphen"></a><a style="text-decoration:none" href="#drophyphen">&#x1f517</a></td>
    <td>I49<br />
      A5<br />
      CR576<br />
      CRDD<br />
      SR7100
      <br />      </td>
    <td>All other numbered designations drop their hyphens (Spain AP-7 becomes AP7) and slashes (Czech I/49 becomes I49). </td>
  </tr>
  <tr valign="top">
    <td><a name="keephyphen"></a><a style="text-decoration:none" href="#keephyphen">&#x1f517</a></td>
    <td>M22-1</td>
    <td>Hyphens between numbers can be kept. This example is Serbian M22-1, a branch of M22.</td>
  </tr>
  <tr valign="top">
    <td><a name="bannerafternumber"></a><a style="text-decoration:none" href="#bannerafternumber">&#x1f517</a></td>
    <td>US73:<br />
      US40Bus<br />
      I-585BS<br />
      US20Alt</td>
    <td>Add banners after the number.<br />
      <br />
      For the US Highways, local conventions of using letter suffixes 
      instead of banners are ignored. Use banners; NY US 20A is US20Alt 
      here.
  </tr>
  <tr valign="top">
    <td><a name="abbrev"></a><a style="text-decoration:none" href="#abbrev">&#x1f517</a></td>
    <td>US40:<br />
        US40BusWhi<br />
        <br />
        US73:<br />
        US40BusWhi<br />
        US40BusTho
        <br />
      A3:<br />
        A3Zur</td>
    <td>Distinguish two different same-bannered same-numbered routes as 
      needed with the 3-letter city abbreviations. Also use the city 
      abbreviation for bannerless same-designation spurs or branches, such as 
      the Zurich A3 spur intersecting the main A3. </td>
  </tr>
</tbody></table>



<p class="heading"><a name="named"></a><a style="text-decoration:none" href="#named">&#x1f517</a>
  Intersections with named highways</p>

<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="truncate"></a><a style="text-decoration:none" href="#truncate">&#x1f517</a></td>
    <td>FaiRd <br />
      PapMillRd<br />
      <br />
      MarKingBlvd<br/>
      MLKingBlvd<br />
      <br/>
      MLKBlvd<br/>
      UCSBBlvd
      </td>
    <td>Abbreviate the generic road type (Rd for Road, Blvd for 
      Boulevard, etc.) if it's one of the very common types. Otherwise, use 
      the <em>first </em>three letters: Uli for Ulica. Skip the final period. <br />
      <br />      
      For up to two other (specifying) words, truncate the word as follows:<br />
      1-4 letters - use whole word<br />
      5+ letters - use the <em>first</em> 3 letters. Don't use a made-up abbreviation.
      Fairchild Road becomes FaiRd, not FrchldRd or FchRd or anything else. <br />
      <br />
      If the cross road name has more than 3 words, use one of three options:<br />
      1. Pick out the two most important words besides the road type 
      and use only those: Martin Luther King  Boulevard becomes MarKingBlvd. 
      Three words in total are included in shortened form. <br />
      2. Pick out one important word besides the road type and use it 
      and the initials of the other words: Martin Luther King  Boulevard 
      becomes MLKingBlvd. Two words in total are included in shortened form 
      along with initials of the rest.<br/>
      3. Use initials only besides the road type: Martin Luther King Boulevard becomes MLKBlvd,
      University of California Santa Barbara Boulevard becomes UCSBBlvd.
      </td>
  </tr>
  <tr valign="top">
    <td><a name="noprepositions"></a><a style="text-decoration:none" href="#noprepositions">&#x1f517</a></td>
    <td>BlvdAll<br />
      RuePeu</td>
    <td>Ignore any prepositions ("of", "de", "del", etc.), articles 
      ("the", "a", "des", etc.), and conjunctions ("and", etc.) in any 
      language.  Boulevard of the Allies becomes BlvdAll. Rue de Peu becomes 
      RuePeu. Titles of people (Dr., Jr., etc.) can also be omitted. <br />
      Exception: If the word is foreign (e.g., Spanish in the USA) and is an 
      essential part of a place name, keep the word. Los Angeles Avenue 
      becomes LosAngAve.</td>
  </tr>
  <tr valign="top">
    <td><a name="dropdirection"></a><a style="text-decoration:none" href="#dropdirection">&#x1f517</a></td>
    <td>6thSt<br />
      33rdAve<br />
      SeeLn</td>
    <td>Ignore any non-essential direction specifier. N. 6th St becomes 
      6thSt. 33rd Avenue SW becomes 33rdAve. W. Seedy Lane becomes SeeLn. </td>
  </tr>
  <tr valign="top">
    <td><a name="keepdirection"></a><a style="text-decoration:none" href="#keepdirection">&#x1f517</a></td>
    <td>NorPkwy<br />
      SouBlvd</td>
    <td>But keep directions that are the main part of the road name, 
      such as NorPkwy for Northern Parkway or SouBlvd for Southeast Boulevard.</td>
  </tr>
</tbody></table>



<p class="heading"><a name="other"></a><a style="text-decoration:none" href="#other">&#x1f517</a>
  Other intersections</p>
    
<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="park"></a><a style="text-decoration:none" href="#park">&#x1f517</a></td>
    <td>RayWinSP<br />
      GreSmoNP</td>
    <td>For a park or other non-commercial point, abbreviate 
      the name as if it were a named highway (see rules above). Use <code>NP</code>
      for national park, <code>PP</code> for provincial park, <code>SP</code> for state park. </td>
  </tr>
  <tr valign="top">
    <td><a name="uturn"></a><a style="text-decoration:none" href="#uturn">&#x1f517</a></td>
    <td>A3_U<br />
      A3_U1<br />
      A3_U2</td>
    <td>Use a <code>_U</code> suffix for interchanges that are nothing more than a 
      U-turn ramp. If more than one is needed for the same highway, use <code>_U1</code>, 
      <code>_U2</code>, etc. </td>
  </tr>
  <tr valign="top">
    <td><a name="ferry"></a><a style="text-decoration:none" href="#ferry">&#x1f517</a></td>
    <td>Fry<br />
      LilProFry</td>
    <td><code>Fry</code> is normally the appropriate label for ferry terminals. If required or desired to distinguish ferry terminals, additional names can be added, e.g. departure or destination locations.</td>
  </tr>
</tbody></table>



<p class="heading"><a name="highwayends"></a><a style="text-decoration:none" href="#highwayends">&#x1f517</a>
  Highway ends at non-intersections or non-borders</p>
    
<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="continuing"></a><a style="text-decoration:none" href="#continuing">&#x1f517</a></td>
    <td>PapMillRd</td>
    <td>For sudden ends at no particular intersection or landmark, the 
      name of the continuing highway can be used if it begins where the 
      highway in question ends, i.e., is not concurrent. Note that this case 
      does not apply to adding extra waypoints to ramps of the final 
      interchange of a highway, since the waypoint for the center of the 
      interchange is understood to include the end. </td>
  </tr>
  <tr valign="top">
    <td><a name="end"></a><a style="text-decoration:none" href="#end">&#x1f517</a></td>
    <td>End<br />
      NEnd<br />
      WEnd</td>
    <td>In the rare case of having nothing but a pavement change, 
      barricade, railroad tracks, or a bridge to end a highway (no 
      intersection, park, airport, border, etc.), simply use End. In the rarer
       case of this situation applying to both ends of a highway, put a 
      direction letter at the front of End, such as NEnd, to distinguish the 
      two points. </td>
  </tr>
  <tr valign="top">
    <td><a name="bridge"></a><a style="text-decoration:none" href="#bridge">&#x1f517</a></td>
    <td>BigBlueBri<br />
      SusRiv</td>
    <td>For a highway ending at a bridge and not at an intersection, End
      can be the label. If the bridge's name is official and signed, the name
      of the bridge can be used, truncated like a highway name. If there is 
      no official bridge name but the bridge traverses only a river with an 
      official name, use the river name. </td>
  </tr>
</tbody></table>



<p class="heading"><a name="2highways"></a><a style="text-decoration:none" href="#2highways">&#x1f517</a>
  Putting two highways in a waypoint label</p>

<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="slash"></a><a style="text-decoration:none" href="#slash">&#x1f517</a></td>
    <td>US80/42<br />
      A5/A6<br />
      I-5/6<br />
      I-80/90<br />
      I-80/6
      </td>
    <td>Two numbered highways may appear in a waypoint. You can use 
      two designations if the cross road has 2+ numbered designations or if 2+
      numbered highways are cross roads at the same point. <br />
      <br />
      Put the primary highway first, followed by a slash, followed by the 
      second highway. Drop the prefix of the second highway if it is more than
      one character long. A5/A6 becomes A5/A6. I-5/I-6 becomes I-5/6. 
      I-25/US50 becomes I-25/50. <br />
      <br />
      If both numbered designations are of the same type, it's 
      probably useful to mention both.  If the 2nd highway is a lesser type, 
      the 2nd highway can be skipped or mentioned. I-80/I-90 should be 
      I-80/90. I-80/US 6 can be I-80 or I-80/6, whichever is deemed more 
      useful. US 422/PA 271 can be either US422 or US422/271.<br />
      <br />
      If one of the two highways is already long as a label (e.g., a
      bannered route like US42BusKin), consider skipping the city abbrev. or 
      even skipping the whole second route.<br />
      <br />
      Never may three or more designations appear in a single 
      waypoint. If the cross road has 3+ designations, use the main one or two
      designations. </td>
  </tr>
  <tr valign="top">
    <td><a name="dropnamed"></a><a style="text-decoration:none" href="#dropnamed">&#x1f517</a></td>
    <td>I-95</td>
    <td> If you encounter the need for using both a named and numbered 
      designation in the waypoint label, or the need for two named 
      designations, pick only one of the two for brevity. I-95/New Jersey 
      Turnpike becomes I-95. </td>
  </tr>
  <tr valign="top">
    <td><a name="avoidsuffix"></a><a style="text-decoration:none" href="#avoidsuffix">&#x1f517</a></td>
    <td>US50/60<br />
      <br />      </td>
    <td>Avoid extra distinguishing suffixes on labels with 2 numbered designations unless necessary. </td>
  </tr>
  <tr valign="top">
    <td><a name="identicalmultiplex"></a><a style="text-decoration:none" href="#identicalmultiplex">&#x1f517</a></td>
    <td>US50/60_W<br />
      US50/60_E</td>
    <td>Only in the case of two identical two-designation waypoints 
      should the suffixes be added for distinction. Prefer the single-letter 
      suffixes in this case. </td>
  </tr>
    </tbody></table>
    


<p class="heading"><a name="multiplex"></a><a style="text-decoration:none" href="#multiplex">&#x1f517</a>
  Waypoint labels for multiplexes</p>
    
<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="exitconc_num_on_unnum"></a><a style="text-decoration:none" href="#exitconc_num_on_unnum">&#x1f517</a></td>
    <td>US90:<br />
      LA76
      <br />
      I-49(45)<br />
      I-49(47)<br />
      I-49(52)
      <br />
      US40</td>
    <td>For non-exit-numbered routes concurrent with a <em>numbered</em>, exit-numbered route, use the concurrent highway designation with the exit numbers in parentheses. </td>
  </tr>
  <tr valign="top">
    <td><a name="exitconc_name_on_unnum"></a><a style="text-decoration:none" href="#exitconc_name_on_unnum">&#x1f517</a></td>
    <td>US90:<br />
      LA76 <br />
      Gar(45)<br />
      Gar(47)<br />
      Gar(52) <br />
      US40 </td>
    <td>For non-exit-numbered routes concurrent with a <em>named</em>, exit-numbered route, use the first part of the truncated name followed by the exit numbers in parentheses.  </td>
  </tr>
  <tr valign="top">
    <td><a name="split"></a><a style="text-decoration:none" href="#split">&#x1f517</a></td>
    <td>US25:<br /> 
      US80_W<br /> 
      US80_E<br /></td>
    <td>For non-exit-numbered routes concurrent with another 
      non-exit-numbered route, use normal waypoint labels for the intermediate
      points.<br />
      <br />
      For the multiplex splits, add a suffix: an underscore followed by a
      direction letter. The direction letter should match the signed 
      direction the concurrent route is splitting toward. US80_W in the US25 
      file means that US 80 heads west from US 25 at that point but is 
      concurrent to the east. </td>
  </tr>
  <tr valign="top">
    <td><a name="bothroutes"></a><a style="text-decoration:none" href="#bothroutes">&#x1f517</a></td>
    <td>E40:<br />
      A5/A13</td>
    <td>If the highway jumps from one numbered multiplex to another, the
      most useful waypoint label would include both routes without any 
      suffixes. </td>
  </tr>
  <tr valign="top">
    <td><a name="plexnosuffix"></a><a style="text-decoration:none" href="#plexnosuffix">&#x1f517</a></td>
    <td>US25:<br />
      US90_W<br />
      US80_W<br />
      US80/90</td>
    <td>At  splits where two concurrent routes leave, usually no suffix 
      is needed. So if US 90 joins US 25, then US 80 joins, and then US 80/US 
      90 splits off together, the US80/90 point needs no suffix. </td>
  </tr>
</tbody></table>



<p class="heading"><a name="disambiguation"></a><a style="text-decoration:none" href="#disambiguation">&#x1f517</a>
  Distinguishing otherwise identical waypoints (not for exit numbers)</p>
    
<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td>Link:</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="nosuffix"></a><a style="text-decoration:none" href="#nosuffix">&#x1f517</a></td>
    <td>US25:<br />
        I-80<br />
        GarStaPkwy</td>
    <td>If a highway is a cross road only once, no suffix should appear. </td>
  </tr>
  <tr valign="top">
    <td><a name="parens"></a><a style="text-decoration:none" href="#parens">&#x1f517</a></td>
    <td>US25:<br />
      I-80(173)<br />
      I-80(256)<br />
      I-80/90
      <br />
      Gar(53)<br />
      Gar(56)</td>
    <td>If an exit numbered highway is a cross road twice, exit numbers 
      in parentheses can be used to distinguish them. Avoid using two 
      designations and a parenthetical suffix in the same label. </td>
  </tr>
  <tr valign="top">
    <td><a name="underscore"></a><a style="text-decoration:none" href="#underscore">&#x1f517</a></td>
    <td>US25:<br />
      US90_S<br />
      US90_N</td>
    <td>If a non-exit-numbered highway is a cross road twice, add an 
      underscored suffix. The direction letter refers to the relative position
      of the intersection along the route whose file is being made. US90_S is
      the southern of the two US 90 junctions along US 25, which runs S-N. </td>
  </tr>
  <tr valign="top">
    <td><a name="suffixless"></a><a style="text-decoration:none" href="#suffixless">&#x1f517</a></td>
    <td>US25:<br />
      US90_S<br />
      US90<br />
      US90_N</td>
    <td>If a non-exit-numbered highway is a cross road a third time, a suffixless label is an option. </td>
  </tr>
  <tr valign="top">
    <td><a name="putbackdirections"></a><a style="text-decoration:none" href="#putbackdirections">&#x1f517</a></td>
    <td>N6thSt<br />
      S6thSt</td>
    <td>If non-essential directions were omitted and they are different,
      they can be put back in. N. 6th St. and S. 6th St. would both be 
      labeled 6thSt, so include the initial N and S for distinction. </td>
  </tr>
  <tr valign="top">
    <td><a name="over2"></a><a style="text-decoration:none" href="#over2">&#x1f517</a></td>
    <td>US90_A<br />
      US90_B<br />
      US90_C</p>
      <br />
      NV57_Ren<br />
      NV57_Tah<br />
      NV88_PitS<br />
      NV88_PitN<br />
      ME161_FtKS<br />
      ME161_FtKN<br /></td>
    <td>If more than two points for the same non-exit-numbered cross 
      road are needed, there are two options which can be used in combination 
      with or ignoring the previous options for pairs of identical labels.<br />
      1. Use alphabetical suffixes _A, _B, _C, etc.<br />
      2. Choose 3-letter suffixes for nearby towns if they are fairly close. The 3-letter suffix should be the <em>first</em>
      3 letters of the town name. or a desired 3-letter abbreviation if the name consists
      of more than one word. Add a suffix with an underscore and those 3 letters.
      <ul>
      <li>Standard town prefixes like "Bad", "Le", "Saint", "San", "Sankt" can be omitted or abbreviated.</li>
      <li>If you need the same town twice, add a 4th letter that is a 
      direction letter (_PitS and _PitN for southern and northern junctions 
      near Pittston or _FtKN and _FtKS for southern and northern junctions near Fort Kent).</li>
      <li>If the town suffixes are not useful, are confusing,
      require further elaboration (3+ junctions with same town),
      or no towns are nearby, use the county name or alphabetical suffixes instead.</li>
      </ul></td>
  </tr>
  <tr valign="top">
    <td><a name="differentnames"></a><a style="text-decoration:none" href="#differentnames">&#x1f517</a></td>
    <td>MilPkwy<br />
      MilfPkwy</td>
    <td>If two named cross roads have different names but would have 
      identical labels (Milford Parkway &amp; Millville Parkway both would be 
      MilPkwy), either add a 4th letter to a 3-letter part of one of the 
      labels, or choose 3 different letters for that label. </td>
  </tr>
  <tr valign="top">
    <td><a name="loopexit"></a><a style="text-decoration:none" href="#loopexit">&#x1f517</a></td>
    <td>A10:<br />
        1<br />
        ...<br />
        A11</td>
    <td>Loop roads need one point to appear as both the first and last 
      waypoints. If the loop is exit-numbered, the first point is usually the 
      lowest exit number. The second point can be the cross road name. Here 
      Exit 1 of A10 is where A11 intersects.</td>
  </tr>
  <tr valign="top">
    <td><a name="loopsuffix"></a><a style="text-decoration:none" href="#loopsuffix">&#x1f517</a></td>
    <td>A10:<br />
      A8_W<br />
      ...<br />
      A8_E</td>
    <td>If the loop road is not exit-numbered, use the cross road twice,
      each with a direction suffix (underscore + one letter) representing the
      direction to the adjacent waypoint. If the second waypoint is west of 
      the first point, append _W to the first point and _E to the last point. </td>
  </tr>
</tbody></table>



<p class="heading"><a name="auxroutes"></a><a style="text-decoration:none" href="#auxroutes">&#x1f517</a>
  Waypoints for ends of auxiliary routes (bannered routes and the suffixed equivalents)</p>

<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="parentplussuffixes"></a><a style="text-decoration:none" href="#parentplussuffixes">&#x1f517</a></td>
    <td>US15BusGet:<br />
      US15_S<br />
      ...<br />
      US15_N</td>
    <td>For auxiliary routes connecting to the parent route at both 
      ends, mention only the parent route (even if it is concurrent with other
      routes) and add direction suffixes.</td>
  </tr>
  <tr valign="top">
    <td><a name="parentexitnumbers"></a><a style="text-decoration:none" href="#parentexitnumbers">&#x1f517</a></td>
    <td>US12BusBar:<br />
      US12(212)<br />
      ...<br />
      US12(219)</td>
    <td>For auxiliary routes connecting to the parent route at both
    ends at interchanges that have posted exit numbers, using labels
    that include said exit numbers instead of direction suffixes will
    be allowed, as long as both ends have them (and as long as the
    exit numbers are for the parent route).</td>
  </tr>
  <tr valign="top">
    <td><a name="parentonly"></a><a style="text-decoration:none" href="#parentonly">&#x1f517</a></td>
    <td>US61SprLit:<br />
      US61<br />
      ...<br />
      AR549</td>
    <td>For spurs that connect only once to the parent route, simply 
      mention the parent route for that end and ignore any concurrent routes. </td>
  </tr>
  <tr valign="top">
    <td><a name="override"></a><a style="text-decoration:none" href="#override">&#x1f517</a></td>
    <td>&nbsp;</td>
    <td>These two rules override other directions. </td>
  </tr>
</tbody></table>



<p class="heading"><a name="noname"></a><a style="text-decoration:none" href="#noname">&#x1f517</a>
  Waypoints for roads that no longer have a name or no longer exist as a road</p>

<table border="1" cellpadding="2" cellspacing="2" width="640" class="text">
  <tbody><tr valign="top">
    <td width="40">Link</td>
    <td width="200">File:<br />
      Waypoint </td>
    <td width="400">Description</td>
  </tr>
  <tr valign="top">
    <td><a name="useposted"></a><a style="text-decoration:none" href="#useposted">&#x1f517</a></td>
    <td>MainSt</td>
    <td>If the old highway has a posted name or number, use that name or
      number (don't make one up), and label the waypoint according to the 
      usual rules. <br />
      If US 30/Main Street becomes Main Street, then the label is MainSt, not something like OldUS30.</td>
  </tr>
  <tr valign="top">
    <td><a name="olddesignation"></a><a style="text-decoration:none" href="#olddesignation">&#x1f517</a></td>
    <td>OldUS40</td>
    <td>If the old highway has a posted name that mentions the old 
      designation, then applying the above rule will result in a label like 
      OldUS40 for "Old Route 40" if the route was formerly US 40.</td>
  </tr>
  <tr valign="top">
    <td><a name="includeold"></a><a style="text-decoration:none" href="#includeold">&#x1f517</a></td>
    <td>OldLeeHwy<br />OldRedRd</td>
    <td>If the old road still exists but has no name or number, or the 
      road was closed so it can't have a name or number, then make up a name 
      using the former name or number along with the native language word for 
      "Old" prepended or appended according to the grammar rules for that 
      language. Then follow the usual rules for shortening that name into a 
      label, being sure that the word for "Old" is included in the label. In 
      English, this means prepending "Old" to the former name.<br />
      If the road was formerly Lee Highway and now has no name or number, then
      the label is OldLeeHwy.  If the road was formerly Red Hill Road and was
      closed, then the new label is OldRedRd, not OldRedHill or RedHillRd, so
      that "Old" and "Rd" are included.</td>
  </tr>
  <tr valign="top">
    <td><a name="multipledesignations"></a><a style="text-decoration:none" href="#multipledesignations">&#x1f517</a></td>
    <td>OldUS11</td>
      <td>If the old highway had multiple numbered designations and you 
      need to make up a label according to the previous instruction, use only 
      the primary route number.<br />For example, if you need a label for the 
      now-nameless highway formerly designated US 11/US 15, then the label 
      should include only US 11: OldUS11.</td>
  </tr>
  <tr valign="top">
    <td><a name="genericnumbred"></a><a style="text-decoration:none" href="#genericnumbred">&#x1f517</a></td>
    <td>OldUS63<br />OldRt15</td>
    <td>For numbered (alphanumeric) designations like US63, use the 
      former designation type, like US in US 63, in a label like OldUS63, if 
      that designation type is known. If it is not known, use a generic word 
      like Route (Rt) or Highway (Hwy) in English or an analogous word in 
      another language.</td>
  </tr>
</tbody></table>

<p class="text">If some of your waypoint labels don't match any of the
types above, look again for the best match. If you have found a case
not yet covered, let us know on the forum and we will come to a preferred
solution.</p>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>	
