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
<a href="/">Home</a> &nbsp;&nbsp;&nbsp;
<a href="/stat.php">Traveler Stats</a> &nbsp;&nbsp;&nbsp;
<a href="/hb">Highway Browser</a> &nbsp;&nbsp;&nbsp;
<a href="/participate.php">Get Started!</a> &nbsp;&nbsp;&nbsp;
<a href="https://forum.travelmapping.net">Project Forum</a> &nbsp;&nbsp;&nbsp;
<a href="/devel/updates.php">Updates</a>
</p>

<?php
$tmupdating = file_exists($_SERVER['DOCUMENT_ROOT']."/dbupdating");
if ($tmupdating) {
  echo <<<END
<p class="errorbar">
Travel Mapping database update in progress.  Some functionality might
not work.  Please try again in a few minutes if you notice problems.
</p>
END;
}
?>

<!-- /lib/tmheader.php: END -->
