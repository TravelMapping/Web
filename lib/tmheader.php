<!-- /lib/tmheader.php: Main title and menu bar for Travel Mapping -->

<?php
if ($nobigheader != 1) {
    echo <<<END
<p class="title">
<a href="/">Travel Mapping</a>
</p>

<p class="title2">
Tracking Cumulative Travels
</p>
END;
}
?>

<p class="menubar">
    <a href="/">TM Home</a> &nbsp;&nbsp;&nbsp;
    <?php
    if (isset($tmuser)) {
        if ($tmuser != "null") {
            echo '<a href="/user">'.$tmuser.'\'s User Page</a> &nbsp;&nbsp;&nbsp;';
	}
    }
    else if (isset($_COOKIE['lastuser']) && $_COOKIE['lastuser'] != "null") {
        echo '<a href="/user">'.$_COOKIE['lastuser'].'\'s User Page</a> &nbsp;&nbsp;&nbsp;';
    }
    ?>
    <a href="/stat.php"><?php echo $tmMode_s;?> Travelers' Stats</a> &nbsp;&nbsp;&nbsp;
    <a href="/hb"><?php echo $tmMode_s;?> Browser</a> &nbsp;&nbsp;&nbsp;
    <a href="/participate.php">Get Started!</a> &nbsp;&nbsp;&nbsp;
    <a href="https://forum.travelmapping.net">Project Forum</a> &nbsp;&nbsp;&nbsp;
    <a href="/devel/updates.php">Updates</a>
</p>

<?php
$tmupdating = file_exists($_SERVER['DOCUMENT_ROOT']."/dbupdating");
if ($tmupdating) {
    echo <<<END
<p id="updatingmsg" class="errorbar">
Travel Mapping <?php echo $tmMode_s;?> database update in progress.  Some functionality might
not work.  Please try again in a few minutes if you notice problems.
END;
    tm_dismiss_button("updatingmsg");
    echo <<<END
</p>
<script type="text/javascript">
var tmdbupdating = true;
</script>
END;
}
else {
    echo <<<END
<script type="text/javascript">
var tmdbupdating = false;
</script>
END;
}
?>

<!-- /lib/tmheader.php: END -->
