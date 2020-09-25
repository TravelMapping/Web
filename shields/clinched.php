<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!-- 
	Shows a user's clinched highways within the project
	URL Params:
		u - the user.
                cort - traveled or clinched to display
-->
<html>
    <?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<head>
    <title>Clinched Routes for <?php echo $tmuser ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/css/travelMapping.css" />
    <link rel="stylesheet" type="text/css" href="/fonts/roadgeek.css" />
    <link rel="shortcut icon" type="image/png" href="/favicon.png">
</head>
<body>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>
<?php require "shieldgen.php"; ?>
<?php
    if (array_key_exists("cort", $_GET) && ($_GET['cort'] == "traveled")) {
        $cort = "t";
    }
    else {
        $cort = "c";
    }
?>
    <form id="userselect" action="clinched.php">
        <label for="user">User: </label>
        <?php tm_user_select(); ?>
        <input type="radio" name="cort" value="traveled" <?php if ($cort == "t") echo "checked=\"checked\""; ?> />All Traveled
        <input type="radio" name="cort" value="clinched" <?php if ($cort == "c") echo "checked=\"checked\""; ?> />Clinched Only
        <input type="checkbox" name="reload" id="reload" value="true" />
        <label for="reload">Disable Caching (slows loading)</label>
        <input type="submit" value="Update" />
    </form>
<?php
    if ($cort == "t") {
        echo "<h1>Traveled Routes for $tmuser: </h1>\n";
    }
    else {
        echo "<h1>Clinched Routes for $tmuser: </h1>\n";
    }

if ( $tmuser == "null") {
    echo "<h1>Select a User to Continue</h1>\n";
    echo "</div>\n";
    require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php";
    echo "</body>\n";
    echo "</html>\n";
    exit;
}
?>
    <table>
    <?php
    if ($cort == "t") {
       $clinched_clause = "";
       $having_clause = "HAVING (count(ccr.route) >= 1)";
    }
    else {
       $clinched_clause = "AND ccr.clinched = 1";
       $having_clause = "HAVING (sum(ccr.clinched) >= 1)";
    }
    $sql = <<<SQL
      SELECT sys.fullName, sys.systemName,
        group_concat(ccr.route ORDER BY SUBSTRING(ccr.route, LOCATE('.', ccr.route))) AS clinchedRoutes,
        count(cr.route) as total,
        count(ccr.route) as traveled,
        sum(ccr.clinched) as clinched
      FROM connectedRoutes as cr
      LEFT JOIN systems AS sys ON sys.systemName = cr.systemName
      LEFT JOIN clinchedConnectedRoutes AS ccr ON cr.firstRoot = ccr.route AND ccr.traveler = '{$tmuser}' {$clinched_clause}
      GROUP BY sys.systemName
      {$having_clause}
      ORDER BY sys.tier, sys.systemName;
SQL;
    $tmdb->query("SET SESSION  group_concat_max_len = 5555555;");
    $res = tmdb_query($sql);
    while($row = $res->fetch_assoc()) {
        echo "<h4><a href='/user/system.php?sys={$row['systemName']}&amp;u={$tmuser}'>{$row['fullName']}\n";
        if ($cort == "c") {
            echo " ({$row['clinched']} ";
        }
        else {
            echo " ({$row['traveled']} ";
        }
        echo "/ {$row['total']})</a></h4>";
        $rootList = explode(",", $row['clinchedRoutes']);
        $col = 0;
        foreach($rootList as $root) {
            echo "<a href='/hb/showroute.php?u=$tmuser&amp;r=$root'><span class='shield'>".generate($root, $_GET['reload'])."</span></a>\n";
            $col++;
            if ($col > 20) {
                echo "<br/>\n";
                $col = 0;
            }
        }
    }
    $res->free();
    ?>
    </table>
<?php require  $_SERVER['DOCUMENT_ROOT']."/lib/tmfooter.php"; ?>
</body>
<?php
    $tmdb->close();
?>
</html>
