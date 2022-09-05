<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping Manual: Deal with data errors</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="favicon.png">
</head>
<body>

<style>
green {background-color: #CCFFCC;}
yellow {background-color: #FFFFCC;}
red {background-color: #FFCCCC;}
conn1 {color: #0000FF;}
conn2 {color: #00FF00;}
conn3 {color: #FF00FF;}
conn4 {color: #FFFF00;}
conn5 {color: #FF0000;}
conn6 {color: #00FFFF;}
conn7 {color: #700080;}
conn8 {color: #B0B000;}
conn9 {color: #FF8000;}
conn10 {color: #808080;}
connerr1 {color: #4040A0;}
connerr2 {color: #48C0A0;}
</style>

<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<h1>Travel Mapping Manual: Deal with data errors</h1>

<div class="text">
  The developer tools execute different kind of data checks to indicate potential errors.
  Highway data managers need to check for these errors to ensure consistent and correct highway data.
  Some of the data checks are available in the waypoint editor before submitting changes, but many need attention after the next site update.
</div>

<p class="heading">
  Contents</p>

<div class="text">
<ul>
  <li><a href="#errors">Data errors</a></li>
  <li><a href="#datacheck">Highway data check list</a></li>
  <li><a href="#falsepositive">Marking errors false positive (FP)</a></li>
  <li><a href="#concurrency">Concurrency check</a></li>
  <ul>
    <li><a href="#concurrencymapview">Load graph with mapview</a></li>
    <li><a href="#concurrencyhdx">Load graph with Highway Data Examiner (HDX)</a></li>
    <li><a href="#concurrencycheck">Check for broken concurrencies</a></li>
  </ul>
  <li><a href="#nearmisspoint">Near-miss points</a></li>
  <li><a href="#nmpfp">Marking NMPs false positive FP</a></li>
</ul>
</div>

<p class="heading"><a name="errors"></a><a style="text-decoration:none" href="#errors">&#x1f517</a>
Data errors</p>

<div class="text">
  When working with the <a href="/wptedit/">WPT file editor</a>, a data check for the loaded route is executed with every modification.
  The <code>code</code> of a data error is indicated in the last column of the waypoint table. Use the tool tip for additional info.
  After manual changes within the editor field, the data check is done on the next editor action, e.g. when pressing the <code>Load</code> button.
  </br>
  </br>
  Check the table for errors before saving the <code>.wpt</code> file. Fix unintended errors or mark intended errors <a href="#falsepositive">false positive</a>.
  </br>
  </br>
  Note: Not all data errors are detected by the WPT file editor, see below: <green>supported</green>,
  <yellow>proposed but not yet supported</yellow>, <red>not possible to be detected in WPT file editor</red>.
  </br>
</div>

<table border="1" cellpadding="2" cellspacing="2" width="1200" class="text">
  <tbody><tr valign="top">
    <td width="30"><b>Link</b></td>
    <td width="270"><b>Code</b></td>
    <td width="800"><b>Description</b></td>
    <td width="50"><b>WPT File</br>Editor</b></td>
    <td width="50"><b>FP possible</b></td>
  </tr>
  <tr valign="top">
    <td><a name="ABBREV_AS_CHOP_BANNER"></a><a style="text-decoration:none" href="#ABBREV_AS_CHOP_BANNER">&#x1f517</a></td>
    <td>ABBREV_AS_CHOP_BANNER</td>
    <td>The route's <a href="syshwylist.php#chopped">chopped routes .csv file</a> entry has the <a href="syshwylist.php#cabbrev">Abbrev</a> erroneously entered into the <a href="syshwylist.php#cbanner">Banner</a> field.</td>
    <td><red>NO</red></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="ABBREV_AS_CON_BANNER"></a><a style="text-decoration:none" href="#ABBREV_AS_CON_BANNER">&#x1f517</a></td>
    <td>ABBREV_AS_CON_BANNER</td>
    <td>The 2 potential underlying causes are:
    <ol>
      <li>The route's <a href="syshwylist.php#chopped">chopped routes .csv file</a> entry has the <a href="syshwylist.php#cbanner">Banner</a> erroneously entered into the <a href="syshwylist.php#cabbrev">Abbrev</a> field.
      If the same route is also flagged as <a href="#ABBREV_NO_CITY">ABBREV_NO_CITY</a>, this is probably the case.</li>
      <li>The route's <a href="syshwylist.php#connected">connected routes .csv file</a> entry has the chopped route's <a href="syshwylist.php#cabbrev">Abbrev</a> entered as its <a href="syshwylist.php#conncbanner">Banner</a>.</li>
    </ol>
    </td>
    <td><red>NO</red></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="ABBREV_NO_CITY"></a><a style="text-decoration:none" href="#ABBREV_NO_CITY">&#x1f517</a></td>
    <td>ABBREV_NO_CITY</td>
    <td>The route's <a href="syshwylist.php#chopped">chopped routes .csv file</a> entry has an <a href="syshwylist.php#cabbrev">Abbrev</a>, but no <a href="syshwylist.php#ccity">City</a>.
    <br>If the same route is also flagged as <a href="#ABBREV_AS_CON_BANNER">ABBREV_AS_CON_BANNER</a>, the <code>Abbrev</code> data should probably be moved to the <code>Banner</code>.</li>
    <td><red>NO</red></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="BAD_ANGLE"></a><a style="text-decoration:none" href="#BAD_ANGLE">&#x1f517</a></td>
    <td>BAD_ANGLE</td>
    <td>Angles cannot be computed for two adjacent points at the same coordinates. Instead, use <code>AltLabels</code> or fix coordinates of one point or both.</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="CON_BANNER_MISMATCH"></a><a style="text-decoration:none" href="#CON_BANNER_MISMATCH">&#x1f517</a></td>
    <td>CON_BANNER_MISMATCH</td>
    <td>The route's <a href="syshwylist.php#cbanner">Banner</a> field in its <a href="syshwylist.php#chopped">chopped routes .csv file</a> mismatches the <a href="syshwylist.php#conncbanner">Banner</a> field in its <a href="syshwylist.php#connected">connected routes .csv file</a>.</td>
    <td><red>NO</red></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="CON_ROUTE_MISMATCH"></a><a style="text-decoration:none" href="#CON_ROUTE_MISMATCH">&#x1f517</a></td>
    <td>CON_ROUTE_MISMATCH</td>
    <td>The route's <a href="syshwylist.php#croute">Route</a> field in its <a href="syshwylist.php#chopped">chopped routes .csv file</a> mismatches the <a href="syshwylist.php#conncroute">Route</a> field in its <a href="syshwylist.php#connected">connected routes .csv file</a>.</td>
    <td><red>NO</red></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="BUS_WITH_I"></a><a style="text-decoration:none" href="#BUS_WITH_I">&#x1f517</a></td>
    <td>BUS_WITH_I</td>
    <td>Label looks like an Interstate with <code>Bus</code> banner instead of <code>BL</code> or <code>BS</code>.</td>
    <td><green>YES</green></td>
    <td><green>YES</green></td>
  </tr>
  <tr valign="top">
    <td><a name="DISCONNECTED_ROUTE"></a><a style="text-decoration:none" href="#DISCONNECTED_ROUTE">&#x1f517</a></td>
    <td>DISCONNECTED_ROUTE</td>
    <td>The 3 potential underlying causes are:
    <ol>
      <li>Routes that are not connected in the first place.</li>
      <li><a href="syshwylist.php#conncroots">Roots are out of order</a> within the <code>_con.csv</code> line.</li>
      <li>Just plain mismatched coordinates at a border, whether <a href="#nearmisspoint">NMP</a> or too far apart to be flagged.</li>
    </ol>
    How to fix:
    <ol>
      <li>Make sure all <a href="syshwylist.php#conncroots">roots</a> in the <code>_con.csv</code> line are in fact connected. If not, split into multiple <code>_con.csv</code> lines.</li>
      <li>Make sure the <a href="syshwylist.php#conncroots">roots are in sequential order</a> within the <code>_con.csv</code> line.</li>
      <li>Make sure coordinates match at regional boundaries. If this means changing a <code>.wpt</code> file in a region
      maintained by somebody else, coordinate as needed with that person on making the changes.</li>
    </ol>
    </td>
    <td><red>NO</red></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="DUPLICATE_COORDS"></a><a style="text-decoration:none" href="#DUPLICATE_COORDS">&#x1f517</a></td>
    <td>DUPLICATE_COORDS</td>
    <td>Duplicated coordinates for two or more waypoints</td>
    <td><green>YES</green></td>
    <td><green>YES</green></td>
  </tr>
  <tr valign="top">
    <td><a name="DUPLICATE_LABEL"></a><a style="text-decoration:none" href="#DUPLICATE_LABEL">&#x1f517</a></td>
    <td>DUPLICATE_LABEL</td>
    <td>Duplicated labels for more than one waypoint. Labels must be unique for each route.</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="HIDDEN_JUNCTION"></a><a style="text-decoration:none" href="#HIDDEN_JUNCTION">&#x1f517</a></td>
    <td>HIDDEN_JUNCTION</td>
    <td>Concurrent route splits off at a hidden waypoint. The concurrency is most likely broken by accident, or the waypoint needs to be visible.</td>
    <td><red>NO</red></td>
    <td><green>YES</green></td>
  </tr>
  <tr valign="top">
    <td><a name="HIDDEN_TERMINUS"></a><a style="text-decoration:none" href="#HIDDEN_TERMINUS">&#x1f517</a></td>
    <td>HIDDEN_TERMINUS</td>
    <td>Hidden point at the beginning or end of a route. Routes must begin and end with visible waypoints
    so that users can mark all of them as traveled.</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="INTERSTATE_NO_HYPHEN"></a><a style="text-decoration:none" href="#INTERSTATE_NO_HYPHEN">&#x1f517</a></td>
    <td>INTERSTATE_NO_HYPHEN</td>
    <td>Label looks like an Interstate without a hyphen between the <code>I</code> and numerals.</td>
    <td><yellow>NO</yellow></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="INVALID_FINAL_CHAR"></a><a style="text-decoration:none" href="#INVALID_FINAL_CHAR">&#x1f517</a></td>
    <td>INVALID_FINAL_CHAR</td>
    <td>Disallowed character at end of label</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="INVALID_FIRST_CHAR"></a><a style="text-decoration:none" href="#INVALID_FIRST_CHAR">&#x1f517</a></td>
    <td>INVALID_FIRST_CHAR</td>
    <td>Disallowed character at beginning (after any leading <code>*</code> characters) of label</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="LABEL_INVALID_CHAR"></a><a style="text-decoration:none" href="#LABEL_INVALID_CHAR">&#x1f517</a></td>
    <td>LABEL_INVALID_CHAR</td>
    <td>Label contains at least 1 invalid character. Labels may only contain letters, numerals, parentheses, slashes, underscores, hyphens, or periods, with an optional leading plus sign or asterisk.<br><br>
    An <code>Info</code> value of <code>UTF-8 BOM</code> indicates a UTF-8 byte order mark before the first waypoint, and the file should be saved as plain ASCII text.</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="LABEL_LOOKS_HIDDEN"></a><a style="text-decoration:none" href="#LABEL_LOOKS_HIDDEN">&#x1f517</a></td>
    <td>LABEL_LOOKS_HIDDEN</td>
    <td><code>X123456</code> style label without a leading <code>+</code>. </td>
    <td><green>YES</green></td>
    <td><green>YES</green></td>
  </tr>
  <tr valign="top">
    <td><a name="LABEL_PARENS"></a><a style="text-decoration:none" href="#LABEL_PARENS">&#x1f517</a></td>
    <td>LABEL_PARENS</td>
    <td>Number of parentheses do not match. Opened <code>(</code> must be closed with <code>)</code>.</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="LABEL_SELFREF"></a><a style="text-decoration:none" href="#LABEL_SELFREF">&#x1f517</a></td>
    <td>LABEL_SELFREF</td>
    <td>Label appears to reference own route.
    <br>This datacheck is designed to flag cases where a line was directly copied from another <code>.wpt</code> file without the label being changed.
    <br>
    <br>The site update program looks at intersecting routes in order to eliminate false positives.
    <br>Several error subtypes are listed in the <code>Info</code> column, with different ways they can be fixed before marking false positive:
    <ul>
      <li><a name="NO_COLOC"></a><a style="text-decoration:none" href="#NO_COLOC">&#x1f517</a> <code>NO_COLOC</code>:
      <br>An intersecting route with matching <a href="syshwylist.php#croute">Route</a>, <a href="syshwylist.php#cbanner">Banner</a>, and/or <a href="syshwylist.php#cabbrev">Abbrev</a> wasn't found.
      <br>Ensure this point has the same coordinates as intersecting/concurrent routes if appropriate.
      </li>
      <li><a name="NO_SUFFIX"></a><a style="text-decoration:none" href="#NO_SUFFIX">&#x1f517</a> <code>NO_SUFFIX</code>:
      <br>The route forms a loop, and has two intersections with itself at these coordinates.
      <br>You'll need <a href="wayptlabels.php#underscore">underscored suffixes</a> to distinguish the two waypoints, unless one or both of the labels <a href="wayptlabels.php#2highways">mentions two routes</a>.</li>
      <li><a name="NO_ABBREV"></a><a style="text-decoration:none" href="#NO_ABBREV">&#x1f517</a> <code>NO_ABBREV</code>:
      <br>The route intersects another with the same name/number and same banner, without a city abbreviation in the label.
      <br>You probably want to <a href="wayptlabels.php#abbrev">add the city abbreviation</a> (without an underscore, in order to match the intersecting route's .list name) for disambiguation purposes.</li>
      <li><a name="TRUE_ERROR"></a><a style="text-decoration:none" href="#TRUE_ERROR">&#x1f517</a> <code>TRUE_ERROR</code>:
      <br>The label is an exact match of the route's full .list name, including Banner and Abbrev.
      <li><a name="FULL_MATCH"></a><a style="text-decoration:none" href="#FULL_MATCH">&#x1f517</a> <code>FULL_MATCH</code>:
      <br>The label is a full match of the .list name followed by extra characters other than a slash or underscore.
    </ul>
    </td>
    <td><yellow>NO</yellow></td>
    <td><green>YES</green></td>
  </tr>
  <tr valign="top">
    <td><a name="LABEL_SLASHES"></a><a style="text-decoration:none" href="#LABEL_SLASHES">&#x1f517</a></td>
    <td>LABEL_SLASHES</td>
    <td>Too many slashes in label (<code>> 1</code>).</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="LABEL_TOO_LONG"></a><a style="text-decoration:none" href="#LABEL_TOO_LONG">&#x1f517</a></td>
    <td>LABEL_TOO_LONG</td>
    <td>Label is too long to fit in the space allocated for the DB field.</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="LACKS_GENERIC"></a><a style="text-decoration:none" href="#LACKS_GENERIC">&#x1f517</a></td>
    <td>LACKS_GENERIC</td>
    <td>Label begins with <a href="wayptlabels.php#genericnumbred"><code>Old</code> followed immediately by a number without a
    preceding generic designation type</a> such as <code>US</code>, <code>E</code>, <code>A</code>, <code>Hwy</code>, <code>Rte</code>, etc.</td>
    <td><green>YES</green></td>
    <td><green>YES</green></td>
  </tr>
  <tr valign="top">
    <td><a name="LABEL_UNDERSCORES"></a><a style="text-decoration:none" href="#LABEL_UNDERSCORES">&#x1f517</a></td>
    <td>LABEL_UNDERSCORES</td>
    <td>Too many underscored suffixes (<code>> 1</code>)</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="LONG_SEGMENT"></a><a style="text-decoration:none" href="#LONG_SEGMENT">&#x1f517</a></td>
    <td>LONG_SEGMENT</td>
    <td>Long segment (distance <code>> 20 mi</code>, <code>32 km</code>) between this and the previous hidden point.</td>
    <td><green>YES</green></td>
    <td><green>YES</green></td>
  </tr>
  <tr valign="top">
    <td><a name="LONG_UNDERSCORE"></a><a style="text-decoration:none" href="#LONG_UNDERSCORE">&#x1f517</a></td>
    <td>LONG_UNDERSCORE</td>
    <td>Label has long underscore suffix: <code>> 4</code> characters after underscore,
    or <code>4</code> characters not ending in a capital letter.</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="MALFORMED_LAT"></a><a style="text-decoration:none" href="#MALFORMED_LAT">&#x1f517</a></td>
    <td>MALFORMED_LAT</td>
    <td>Invalid argument after <code>lat=</code> in URL which cannot be converted to a numeric value</td>
    <td><yellow>NO</yellow></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="MALFORMED_LON"></a><a style="text-decoration:none" href="#MALFORMED_LON">&#x1f517</a></td>
    <td>MALFORMED_LON</td>
    <td>Invalid argument after <code>lon=</code> in URL which cannot be converted to a numeric value</td>
    <td><yellow>NO</yellow></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="MALFORMED_URL"></a><a style="text-decoration:none" href="#MALFORMED_URL">&#x1f517</a></td>
    <td>MALFORMED_URL</td>
    <td>URL is missing <code>lat=</code> and/or <code>lon=</code> argument(s)</td>
    <td><yellow>NO</yellow></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="NONTERMINAL_UNDERSCORE"></a><a style="text-decoration:none" href="#NONTERMINAL_UNDERSCORE">&#x1f517</a></td>
    <td>NONTERMINAL_UNDERSCORE</td>
    <td>Label has underscore suffix before slash</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="OUT_OF_BOUNDS"></a><a style="text-decoration:none" href="#OUT_OF_BOUNDS">&#x1f517</a></td>
    <td>OUT_OF_BOUNDS</td>
    <td>Latitude <code>> 90°</code> or <code>< -90°</code>, or longitude <code>> 180°</code> or <code>< -180°</code>.
    False positives are allowed for the rare potential case of routes spanning the international date line.</td>
    <td><green>YES</green></td>
    <td><green>YES</green></td>
  </tr>
  <tr valign="top">
    <td><a name="SHARP_ANGLE"></a><a style="text-decoration:none" href="#SHARP_ANGLE">&#x1f517</a></td>
    <td>SHARP_ANGLE</td>
    <td>Sharp angle (<code>> 135°</code>) with previous and next waypoint.</td>
    <td><green>YES</green></td>
    <td><green>YES</green></td>
  </tr>
  <tr valign="top">
    <td><a name="SINGLE_FIELD_LINE"></a><a style="text-decoration:none" href="#SINGLE_FIELD_LINE">&#x1f517</a></td>
    <td>SINGLE_FIELD_LINE</td>
    <td>WPT file line only contains one field. Each line must have a minimum of 2 space-separated fields: A <a href="hwydata.php#format">waypoint label followed by an OpenStreetMap URL</a>. Deprecated <a href="maintenance.php#labelwrong">alternate labels</a> may optionally be included in between.</td>
    <td><red>NO</green></td>
    <td><red>NO</green></td>
  </tr>
  <tr valign="top">
    <td><a name="US_LETTER"></a><a style="text-decoration:none" href="#US_LETTER">&#x1f517</a></td>
    <td>US_LETTER</td>
    <td>Label uses <a href="wayptlabels.php#bannerafternumber"><code>USxxxA</code> or <code>USxxxB</code> rather than <code>USxxxAlt</code>, <code>USxxxBus</code>, <code>USxxxByp</code>, etc.</td>
    <td><green>YES</green></td>
    <td><red>NO</red></td>
  </tr>
  <tr valign="top">
    <td><a name="VISIBLE_DISTANCE"></a><a style="text-decoration:none" href="#VISIBLE_DISTANCE">&#x1f517</a></td>
    <td>VISIBLE_DISTANCE</td>
    <td>Long distance (Visible distance <code>> 10 mi</code>, <code>16 km</code>) between this and the previous visible point. Not reported for active routes!</td>
    <td><green>YES</green></td>
    <td><green>YES</green></td>
  </tr>
  <tr valign="top">
    <td><a name="VISIBLE_HIDDEN_COLOC"></a><a style="text-decoration:none" href="#VISIBLE_HIDDEN_COLOC">&#x1f517</a></td>
    <td>VISIBLE_HIDDEN_COLOC</td>
    <td>Visible waypoint is hidden on intersecting/concurrent route(s).</td>
    <td><red>NO</red></td>
    <td><green>YES</green></td>
  </tr>
</tbody></table>


<p class="heading"><a name="datacheck"></a><a style="text-decoration:none" href="#datacheck">&#x1f517</a>
Highway data check list</p>

<div class="text">
  When changes to highway data have been submitted and are live on the site, all routes of the project have been checked during the site update process. They are reported on the <a href="../datacheck.php">data check list</a>. The list is sorted by the system categories active, preview and in-development.
  </br>
  <ul>
    <li>Check the table for errors. It is possible to filter the list by system or by region.</li>
    <li>Click on the link in the <code>Route</code> column to load the route into the HB.</li>
    <ul>
      <li>Fix unintended errors by using the <a href="/wptedit/">WPT file editor</a>.</li>
      <li>Mark intended errors <a href="#falsepositive">false positive</a>.</li>
      <li>Some of the errors are likely fixable from the information in the <a href="/logs/nearmatchfps.log">Log of Near-Match FPs from datacheckfps.csv</a>. These are FP entries which have previously been added but due to minor changes, e.g. repositioning a waypoint, they no longer match the actual FP entry.</li>
    </ul>
  </ul>
  Note: It is possible to edit the url in the address bar of the browser to filter for more than one region or system. For instance, it is possible to create a link to all regions a highway data manager maintains. Save it to your browser bookmark and load it when needed for the check.
  </br>
</div>


<p class="heading"><a name="falsepositive"></a><a style="text-decoration:none" href="#falsepositive">&#x1f517</a>
Marking errors false positive (FP)</p>

<div class="text">
  <ul>
    <li>The last column of the <a href="../datacheck.php">data check list</a> contains the <code>FP entry to Submit</code>.</li>
    <ul>
      <li>If no FP Entry is specified, it is always a true error and cannot be marked false positive.</li>
    </ul>
    <li>If a FP entry is specified, select and copy it.</li>
    <ul>
      <li>To quickly mark the whole FP entry, some web browsers allow to triple-click the entry.</li>
    </ul>
    <li>Paste it into your copy of <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the datacheck FP list</a>.</li>
    <ul>
      <li>The list is sorted by region codes. Insert your entry at the right position.</li>
    </ul>
    <li>Submit the change. The data error will disappear from the data check list with the next site update.</li>
    </br>
    <li>Do not forget checking for <a href="/logs/unmatchedfps.log">unmatched FPs</a>. Remove them from <a href="https://github.com/TravelMapping/HighwayData/blob/master/datacheckfps.csv">the datacheck FP list</a>.</li>
  </ul>
</div>

<p class="heading"><a name="concurrency"></a><a style="text-decoration:none" href="#concurrency">&#x1f517</a>
Concurrency check</p>

<div class="text">
  Concurrent segments of different routes are detected and considered for calculating user stats. If a concurrency is broken by accident, stats are affected.
  Highway data managers need to pay attention to check broken concurrencies in their regions. The check can not be done before submitting changes but is currently only possible with the data of the last site update.
  </br>
  </br>
  The best practise to do this, is as follows: Load the region graph either with <a href="#concurrencymapview">mapview</a>
  or with <a href="#concurrencyhdx">HDX</a> and make a manual <a href="#concurrencycheck">check</a>.
</div>

<p class="subheading"><a name="concurrencymapview"></a><a style="text-decoration:none" href="#concurrencymapview">&#x1f517</a>
Load graph with mapview</p>

<div class="text">
  <ul>
    <li>Open <a href="/user/mapview.php">mapview</a>.</li>
    <li>Select the region(s) you want to check.</li>
    <li>Press <code>OK</code>.</li>
    <li>Select <code>Color by Concurrencies</code> under <code>Visible Routes</code>.</li>
    <li>Select <code>Highlight None</code>.</li>
    <li>Zoom-in where you want to check concurrencies.</li>
  </ul>
</div>


<p class="subheading"><a name="concurrencyhdx"></a><a style="text-decoration:none" href="#concurrencyhdx">&#x1f517</a>
Load graph with Highway Data Examiner (HDX)</p>

<div class="text">
  <ul>
    <li>Open the <a href="https://courses.teresco.org/metal/hdx/?noav">Highway Data Examiner</a> (HDX).</li>
    <li>Go to <code>Option 1</code> on the left and enter the name of the region you want to check.</li>
    <li>Press enter.</li>
    <li>Wait. The graph is automatically loaded after a few seconds but it might take longer with large graphs.</li>
    <ul>
      <li>You might open up a small graph first, e.g. the District of Columbia graph, then deselect <code>Show Markers</code> and select whatever region graph you are actually interested in to speed up loading.</li>
    </ul>
    <li>Deselect <code>Show Data Tables</code> to close the table to the right.</li>
    <li>You see a map of the selected region with yellow dots.</li>
    <li>If you hardly see anything but only yellow dots, deselect <code>Show Markers</code>.</li>
    <li>You should see a map of the selected region with <i>funny colors</i> now.</li>
    <li>Select <code>Show Markers</code> again, it was just to show you what happened.</li>
    <li>Zoom-in where you want to check concurrencies.</li>
  </ul>
</div>

<p class="subheading"><a name="concurrencycheck"></a><a style="text-decoration:none" href="#concurrencycheck">&#x1f517</a>
Check for broken concurrencies</p>

<div class="text">
  <ul>
    <li>The colors are created by lines connecting the waypoints.</li>
    <ul>
      <li><conn1>Blue line = one route only</conn1></li>
      <li><conn2>Green line = two concurrent routes</conn2></li>
      <li><conn3>Magenta line = three concurrent routes</conn3></li>
      <li><conn4>Yellow line = four concurrent routes</conn4></li>
      <li><conn5>Red line = five concurrent routes</conn5></li>
      <li><conn6>Aqua line = six concurrent routes</conn6></li>
      <li><conn7>Purple line = seven concurrent routes</conn7></li>
      <li><conn8>Olive line = eight concurrent routes</conn8></li>
      <li><conn9>Orange line = nine concurrent routes</conn9></li>
      <li><conn10>Grey line = ten or more concurrent routes</conn10></li>
    </ul>
    <li>When there is a <conn2>green line</conn2> - 2 routes concurrent - but interrupted by a <connerr1>dark blue line</connerr1> at visible or hidden waypoints, it's a sign that concurrencies are broken</li>
    <ul>
      <li>The <connerr1>dark blue</connerr1> is created when two normal blue lines are overlapping.</li>
      <li>The <i>funny colors</i> are automatically created when different lines are overlapping. Zoom-in to distinguish them.</li>
    </ul>
    <li><i>Non-standard colors</i> can potentially indicate broken concurrencies.
    <ul>
      <li>When there is a <conn2>green line</conn2> (2 concurrent routes) interrupted by a <connerr1>dark blue</connerr1> line at visible or hidden waypoints, it is a sign of potentially broken concurrencies. The <connerr1>dark blue</connerr1> is created when two normal <conn1>blue lines</conn1> are overlapping.</li>
      <li>Other non-standard colors are caused by multiple lines overlapping in the same place.</li>
      <ul>
        <li>For example, a <conn2>green line</conn2> (2 concurrent routes) overlapping a <conn1>blue line</conn1> (one route only) will result in a <connerr2>darker more bluish green<connerr2>.</li>
      </ul>
    </ul>
    <li>When you spot errors, click on the lines or markers to get info about the route and waypoint labels. It is sometimes difficult to click all routes when they are mostly overlapped.</li>
    <li>Fix the coordinates in the corresponding <code>.wpt</code> files.</li>
    <li>Load the changed <code>.wpt</code> files into the <a href="/wptedit/">WPT file editor</a> to avoid causing unintended <a href="#errors">data errors</a>.</li>
    <li>Broken concurrencies of short segments can hardly be found this way. It is recommended to check <a href="#nearmisspoint"> NMPs</a> to find these errors.</li>
  </ul>
</div>

<p class="heading"><a name="nearmisspoint"></a><a style="text-decoration:none" href="#nearmisspoint">&#x1f517</a>
Near-miss points</p>
<div class="text">
  Where two or more routes intersect, the routes must have a waypoint. If the coordinates of the waypoints are identical, the graph is connected and the Highway Browser can indicate intersecting routes to ease navigation through the routes when mapping travels. Near-miss points (NMPs) are waypoints very close together. They should be checked whether they are candidates to merge to fix broken intersecting links, and broken concurrencies.
  </br>
  <a href="../logs.php#nmplogs">NMP files</a> can also be loaded into HDX to visualize their positions on a map. It is easiest to view NMPs by region as described below.
  </br>
  <ul>
    <li>Open the <a href="/logs/nmpbyregion/">NMP files filtered by region</a> index page.</li>
    <li>See if the region you are checking has any unmarked pairs (second column of the table).</li>
    <li>If so, select the "HDX" link for the region you want to check.</li>
    <ul>
      <li>You can see your region with some colored dots on the map now. These are NMP hotspots.</li>
    </ul>
    <li>Zoom-in to investigate the points. Use the table on the left to go through the NMP hotspots.</br>Since all pairs of all involved routes are reported, very often more than just one line of the table on the right corresponds to a NMP hotspot.</li>
    <ul>
      <li><green>Green</green> dots are those which are already marked FP.</li>
      <li><yellow>Yellow</yellow> dots are off by exactly <code>0.000001°</code>. This was likely done intentionally to break concurrencies and the waypoints are candidates to be marked FP.</li>
      <li><red>Red</red> dots must be checked more carefully. These could be legitmate nearby points that need to be separate (very close intersections).  Or these could be broken concurrencies or intersecting routes where the waypoints do not match precisely, but should.</li>
    </ul>
    <li>Click on the NMP hotspot lines or their endpoints to get info about the involved routes and waypoint labels.</li>
    <li>Since you only see the points but not the whole network graph, you might need to open another HDX instance on load the region graph by choosing "Basic Search" and typing the region name to find the correct graph.  To figure out which routes should intersect, what's going on there etc. For instance, it's possible that concurrent routes are only broken on a very short segment you don't see (or missed) when viewing individual routes.</li>
    <li>Fix the coordinates in the corresponding <code>.wpt</code> files.</li>
    <li>Load the changed <code>.wpt</code> files into the <a href="/wptedit/">WPT file editor</a> to avoid causing unintended <a href="#errors">data errors</a>.</li>
  </ul>
</div>


<p class="heading"><a name="nmpfp"></a><a style="text-decoration:none" href="#nmpfp">&#x1f517</a>
Marking NMPs false positive FP</p>

<div class="text">
  Marking NMPs false positive is trickier than for simple data errors but works the same way.
  <ul>
    <li>The <code>FP Entry to Submit</code> can only be found in <a href="/logs/nearmisspoints.log">nearmisspoints.log</a> which contains all NMP entries including those which are already marked FP. The list is sorted by region code.
    <ul>
      <li>Best practice is to use the browser search function to find <code>lat</code> or <code>lon</code> coordinates (or route code) which one can copy from the HDX table.</li>
      <li>There are minimum two FP entries per hotspot but very often even more, please refer to the issue with multiple lines on the HDX table mentioned above.</li>
    </ul>
    <li>The entry must be added to <a href="https://github.com/TravelMapping/HighwayData/blob/master/nmpfps.log">nmpfps.log</a>.</li>
    <ul>
      <li>The list is sorted by region codes. Insert your entry at the right position.</li>
      <li>If the entry ends with <code>[LOOKS INTENTIONAL]</code>, this part of the entry must be removed.</li>
      <li>If not all entries for a NMP hotspot have previously been marked FP, only the missing entries need to be added. Entries ending with <code>[MARKED FP]</code> are already entered as FP.</li>
    </ul>
    </br>
    <li>Do not forget to check for <a href="/logs/nmpfpsunmatched.log">unmatched NMP FPs</a>. Remove them from <a href="https://github.com/TravelMapping/HighwayData/blob/master/nmpfps.log">nmpfps.log</a>.</li>
  </ul>
</div>


<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
</html>
