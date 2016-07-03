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
<a href="/forum">Project Forum</a> &nbsp;&nbsp;&nbsp;
<a href="/devel/updates.php">Updates</a>
</p>

<!-- /lib/tmheader.php: END -->
