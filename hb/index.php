<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 ***
 * Highway/Other Mode Browser Main Page.
 * If a root is supplied, this page will redirect to showroute.php
 * Otherwise, it will show a list of routes that the user can select from, with filters by region and system availible.
 * URL Params:
 *  r - root of route to view waypoints for. When set, the page will display a map with the route params. (required for displaying map)
 *  u - user to display highlighting for on map (optional)
 *  rg - region to filter for on the highway browser list (optional)
 *  sys - system to filter for on the highway browser list (optional)
 *  ([r [u]] [rg] [sys])
 ***
 -->
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="css/travelMapping.css" />
    <link rel="stylesheet" type="text/css" href="fonts/roadgeek.css" />
    <link rel="shortcut icon" type="image/png" href="web/favicon.png">
    <style type="text/css">
        #routebox {
            left: 0px;
            bottom: 0px;
            width: 100%;
            overflow: auto;
        }

	.status-active {
	    background-color: #CCFFCC;
            font-size: 14px;
	}

	.status-preview {
	    background-color: #FFFFCC;
            font-size: 14px;
	}
	
	.status-devel {
	    background-color: #FFCCCC;
            font-size: 14px;
	}

    /* Collapsible region select */
    .crs-wrapper { display: inline-block; position: relative; vertical-align: middle; }
    .crs-dropdown {
        position: absolute; top: 100%; left: 0; min-width: 100%; max-height: 300px;
        overflow-y: auto; border: 1px solid #767676; background: Field; z-index: 1000; font: inherit;
    }
    .crs-option { padding: 1px 3px; cursor: default; white-space: nowrap; }
    .crs-option:hover, .crs-option.crs-selected { background-color: Highlight; color: HighlightText; }
    .crs-group-label {
        padding: 1px 3px; font-weight: bold; cursor: default;
        background-color: #ddd; background-color: ButtonFace;
        white-space: nowrap; user-select: none;
    }
    .crs-group-label:hover { background-color: #bbb; background-color: ButtonFace; filter: brightness(0.93); }
    .crs-group-item { padding-left: 20px; }
    .crs-group-arrow { display: inline-block; width: 10px; font-size: 9px; }
    .crs-option.crs-focused, .crs-group-label.crs-focused { outline: 2px solid Highlight; outline-offset: -2px; }
    </style>
    <?php
    // check for region and/or system parameters
    $regions = tm_qs_multi_or_comma_to_array("rg");
    if (count($regions) > 0) {
        $region = $regions[0];
        $regionName = tm_region_code_to_name($region);
    }
    else {
        $region = "";
        $regionName = "No Region Specified";
    }

    $systems = tm_qs_multi_or_comma_to_array("sys");
    if (count($systems) > 0) {
        $system = $systems[0];
        $systemName = tm_system_code_to_name($system);
    }
    else {
        $system = "";
        $systemName = "No System Specified";
    }

    // if a specific route is specified, that's what we'll view
    if (array_key_exists("r", $_GET)) {
        $routeparam = tm_validate_root($_GET['r']);
    } else {
        $routeparam = "";
    }

    // parse lat, lon, zoom parameters if present
    $lat = "null";
    $lon = "null";
    $zoom = "null";
    if (array_key_exists("lat", $_GET)) {
        $lat = floatval($_GET["lat"]);
    }
    if (array_key_exists("lon", $_GET)) {
        $lon = floatval($_GET["lon"]);
    }
    if (array_key_exists("zoom", $_GET)) {
        $zoom = intval($_GET["zoom"]);
    }

    ?>
    <?php tm_common_js(); ?>
    <script src="lib/tmjsfuncs.js" type="text/javascript"></script>
    <title>Travel Mapping <?php echo $tmMode_s; ?> Browser</title>
</head>
<?php 
$nobigheader = 1;

if ($routeparam == "") {
    echo "<body>\n";
    require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php";
    echo "<h1>Travel Mapping ".$tmMode_s." Browser</h1>";
    echo '<p class="text" id="hbIntro">'."\n";
    echo "In addition to the ".$tmMode_s;
echo <<<END
 Browser functionality here to search for
routes by system and region, TM's <a href="/hb/findroute.php">Route
Finder</a> can help search for routes by other criteria.
END;
    tm_dismiss_button("hbIntro");
    echo "</p>\n";
    echo "<form id=\"selectHighways\" name=\"HighwaySearch\" action=\"/hb/index.php?u={$tmuser}\">";
    echo "<label for=\"sys\">Filter routes by...  System: </label>";
    tm_system_select(FALSE);
    echo "<label for=\"rg\"> Region: </label>";
    tm_region_select(FALSE);
    echo "<input type=\"submit\" value=\"Apply Filter\" /></form>";

} 
else {
    echo "<body>\n";
    require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php";
}
?>
<script type="text/javascript">
    function initFloatingHeaders($table) {
        var $col = $table.find('tr.float');
        var $th = $col.find('th');
        var tag = "<tr style='height: 22px'></tr>";
        $(tag).insertAfter($col);
        $th.each(function (index) {
            var $row = $table.find('tr td:nth-child(' + (index + 1) + ')');
            if ($row.outerWidth() > $(this).width()) {
                $(this).width($row.width());
            } else {
                $row.width($(this).width());
            }
            var pos =  $row.position().left - $table.offset().left - 2;
            //console.log($table.offset().left);
            $(this).css({left: pos})
        });
    }

    $(document).ready(function () {
            <?php
                if ($routeparam != "") {

                } elseif (($region != "") or ($system != "")) {
                    echo <<<JS
                    routes = $("#routes");
                    initFloatingHeaders(routes);
JS;
                }
		else {
		    echo '$("#countryheader").click();';
		    echo '$("#countryheader").click();';
		}
            ?>
        }
    );
</script>
<script type="text/javascript">
function buildCollapsibleRegionSelect() {
    var $select = $("#region");
    if (!$select.length) return;

    var selectedVal = $select.val() || "null";

    var $dropdown = $('<div class="crs-dropdown" style="display:none; position:absolute; top:100%; left:0; z-index:1000; min-width:100%; max-height:300px; overflow-y:auto;"></div>');

    var $none = $('<div class="crs-option" data-value="null">[None Selected]</div>');
    if (selectedVal === "null") $none.addClass("crs-selected");
    $dropdown.append($none);

    $select.children().each(function() {
        if (this.tagName === "OPTGROUP") {
            var $group = $('<div class="crs-group"></div>');
            var $label = $('<div class="crs-group-label"><span class="crs-group-arrow">&#9654;</span> </div>');
            $label.append(document.createTextNode($(this).attr("label")));
            var $items = $('<div class="crs-group-items" style="display:none;"></div>');
            var groupHasSelected = false;
            $(this).children("option").each(function() {
                var val = this.value;
                var $item = $('<div class="crs-option crs-group-item"></div>').attr("data-value", val).text($(this).text());
                if (val === selectedVal) { $item.addClass("crs-selected"); groupHasSelected = true; }
                $items.append($item);
            });
            if (groupHasSelected) {
                $items.show();
                $label.find(".crs-group-arrow").html("&#9660;");
                $label.data("expanded", true);
            }
            $group.append($label).append($items);
            $dropdown.append($group);
        } else if (this.tagName === "OPTION" && this.value !== "null") {
            var val = this.value;
            var $opt = $('<div class="crs-option"></div>').attr("data-value", val).text($(this).text());
            if (val === selectedVal) $opt.addClass("crs-selected");
            $dropdown.append($opt);
        }
    });

    $select.wrap('<div class="crs-wrapper" style="display:inline-block; position:relative; vertical-align:middle;"></div>');
    $select.after($dropdown);
    var $wrapper = $select.parent();
    $select.attr("tabindex", "-1");
    $wrapper.attr("tabindex", "0");

    function openDropdown() {
        $dropdown.show();
        var $sel = $dropdown.find(".crs-selected:visible");
        var toFocus = $sel.length ? $sel[0] : $dropdown.find(".crs-option:visible")[0];
        setFocused(toFocus);
        if (toFocus) toFocus.scrollIntoView({ block: "nearest" });
    }

    function closeDropdown() {
        $dropdown.hide();
        $dropdown.find(".crs-focused").removeClass("crs-focused");
    }

    function setFocused(el) {
        $dropdown.find(".crs-focused").removeClass("crs-focused");
        if (el) $(el).addClass("crs-focused");
    }

    function visibleNavigable() {
        return $dropdown.find(".crs-group-label:visible, .crs-option:visible").toArray();
    }

    function moveFocus(delta) {
        var items = visibleNavigable();
        if (!items.length) return;
        var cur = $dropdown.find(".crs-focused")[0];
        var idx = items.indexOf(cur);
        idx = (idx + delta + items.length) % items.length;
        setFocused(items[idx]);
        items[idx].scrollIntoView({ block: "nearest" });
    }

    function activateFocused() {
        var $f = $dropdown.find(".crs-focused");
        if ($f.hasClass("crs-group-label")) { $f.trigger("click"); }
        else if ($f.hasClass("crs-option"))  { selectOption($f); }
    }

    function selectOption($opt) {
        var val = $opt.data("value");
        $select.val(val);
        $dropdown.find(".crs-selected").removeClass("crs-selected");
        $opt.addClass("crs-selected");
        closeDropdown();
        $wrapper[0].focus();
    }

    function expandGroupOf(el) {
        var $group = $(el).closest(".crs-group");
        if (!$group.length) return;
        var $lbl   = $group.find(".crs-group-label");
        var $items = $group.find(".crs-group-items");
        if (!$items.is(":visible")) {
            $items.show();
            $lbl.data("expanded", true);
            $lbl.find(".crs-group-arrow").html("&#9660;");
        }
    }

    var taBuffer = "", taTimer = null;

    function typeahead(ch) {
        taBuffer += ch.toLowerCase();
        clearTimeout(taTimer);
        taTimer = setTimeout(function() { taBuffer = ""; }, 800);
        var all   = $dropdown.find(".crs-option").toArray();
        var cur   = $dropdown.find(".crs-focused")[0];
        var start = cur ? (all.indexOf(cur) + 1) : 0;
        for (var i = 0; i < all.length; i++) {
            var el = all[(start + i) % all.length];
            if ($(el).text().toLowerCase().indexOf(taBuffer) === 0) {
                expandGroupOf(el);
                setFocused(el);
                el.scrollIntoView({ block: "nearest" });
                return;
            }
        }
        if (taBuffer.length > 1) { taBuffer = ch.toLowerCase(); typeahead(""); }
    }

    $select.on("mousedown", function(e) {
        e.preventDefault();
        $wrapper[0].focus();
        $dropdown.is(":visible") ? closeDropdown() : openDropdown();
    });
    $select.on("click", function(e) { e.stopPropagation(); });

    $wrapper.on("keydown", function(e) {
        var open = $dropdown.is(":visible");
        switch (e.key) {
            case "ArrowDown": e.preventDefault(); open ? moveFocus(1)      : openDropdown(); break;
            case "ArrowUp":   e.preventDefault(); open ? moveFocus(-1)     : openDropdown(); break;
            case "Enter":
            case " ":         e.preventDefault(); open ? activateFocused() : openDropdown(); break;
            case "Escape":    if (open) { e.preventDefault(); closeDropdown(); } break;
            case "Tab":       if (open) closeDropdown(); break;
            default:
                if (e.key.length === 1 && !e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                    if (!open) openDropdown();
                    typeahead(e.key);
                }
        }
    });

    $dropdown.on("click", ".crs-group-label", function(e) {
        e.stopPropagation();
        var $lbl = $(this), $items = $lbl.next(".crs-group-items"), expanded = !!$lbl.data("expanded");
        $items.toggle(!expanded);
        $lbl.data("expanded", !expanded);
        $lbl.find(".crs-group-arrow").html(!expanded ? "&#9660;" : "&#9654;");
    });

    $dropdown.on("click", ".crs-option", function(e) { e.stopPropagation(); selectOption($(this)); });
    $dropdown.on("click", function(e) { e.stopPropagation(); });
    $(document).on("click.crs", function() { closeDropdown(); });
}

$(document).ready(function() { buildCollapsibleRegionSelect(); });
</script>

<?php
if ($routeparam != "") {
    $url = "/hb/showroute.php?r=".$routeparam;
    if (array_key_exists("lat", $_GET)) {
        $url .= "&lat=".$_GET('lat');
    }
    if (array_key_exists("lon", $_GET)) {
        $url .= "&lon=".$_GET('lon');
    }
    if (array_key_exists("zoom", $_GET)) {
        $url .= "&zoom=".$_GET('zoom');
    }
    echo '<p class="text">Please continue with the <a href="'.$url.'">new showroute page for this route</a></p>';
}
elseif (($region != "") or ($system != "")) {  // we have no r=, so we will show a list of all
    $sql_command = "SELECT * FROM routes LEFT JOIN systems ON systems.systemName = routes.systemName";
    // check for query string parameter for system and region filters
    if ($system != "") {
        $sql_command .= " WHERE routes.systemName = '" .$system. "'";
        if ($region != "") {
            $sql_command .= "AND routes.region = '" .$region. "'";
        }
    } else if ($region != "") {
        $sql_command .= " WHERE routes.region = '" .$region. "'";
    }

    $sql_command .= ";";
    echo "<div id=\"routebox\">\n";
    echo "<table class=\"sortable gratable ws_data_table\" id=\"routes\"><thead><tr><th colspan=\"7\">Select Route to Display (click a header to sort by that column)</th></tr><tr><th>Tier</th><th>System</th><th>Region</th><th>Route&nbsp;Name</th><th>.list Name</th><th>Level</th><th>Root</th></tr></thead><tbody>\n";
    $res = tmdb_query($sql_command);
    while ($row = $res->fetch_assoc()) {
        echo "<tr class=\"notclickable status-" . $row['level'] . "\"><td>{$row['tier']}</td><td>" . $row['systemName'] . "</td><td>" . $row['region'] . "</td><td>" . $row['route'] . $row['banner'];
        if (strcmp($row['city'], "") != 0) {
            echo " (" . $row['city'] . ")";
        }
        echo "</td><td>" . $row['region'] . " " . $row['route'] . $row['banner'] . $row['abbrev'] . "</td><td>" . $row['level'] . "</td><td><a href=\"/hb/showroute.php?u={$tmuser}&r=" . $row['root'] . "\">" . $row['root'] . "</a></td></tr>\n";
    }
    $res->free();
    echo "</table></div>\n";
} else {
    //We have no filters at all, so display list of systems as a landing page.
    echo <<<HTML
    <table class="sortable gratable" id="systemsTable">
        <caption>TIP: Click on a column header to sort.</caption>
        <thead>
            <tr><th colspan="5">List of Systems</th></tr>
            <tr><th id="countryheader">Country</th><th>System</th><th>Code</th><th>Status</th><th>Level</th></tr>
        </thead>
        <tbody>
HTML;

    $sql_command = "SELECT * FROM systems LEFT JOIN countries ON countryCode = countries.code";
    $res = tmdb_query($sql_command);
    while ($row = $res->fetch_assoc()) {
        $linkJS = "window.open('/hb/index.php?sys={$row['systemName']}&u={$tmuser}')";
        echo "<tr class='status-" . $row['level'] . "' onClick=\"$linkJS\">";
        if (strlen($row['name']) > 15) {
            echo "<td data-sort=\"{$row['code']}{$row['tier']}{$row['systemName']}\">{$row['code']}</td>";
        } else {
            echo "<td data-sort=\"{$row['name']}{$row['tier']}{$row['systemName']}\">{$row['name']}</td>";
        }

        echo "<td>{$row['fullName']}</td><td>{$row['systemName']}</td><td>{$row['level']}</td><td>Tier {$row['tier']}</td></tr>\n";
    }

    echo "</tbody></table>";
}
$tmdb->close();
?>
</body>
</html>
