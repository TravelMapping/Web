<?php require $_SERVER['DOCUMENT_ROOT'] . "/lib/tmphpuser.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="apple-itunes-app" content="app-id=6761671062">
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmphpfuncs.php" ?>
<title>Travel Mapping for iPhone and iPad</title>
<link rel="stylesheet" type="text/css" href="/css/travelMapping.css">
<link rel="shortcut icon" type="image/png" href="/favicon.png">
<style>
.iosapp { max-width: 640px; margin: 0 auto; text-align: center; }
.iosapp img.icon { width: 112px; height: 112px; border-radius: 24px; margin-top: 12px; }
.iosapp img.badge { height: 50px; margin: 12px 0 4px; }
.iosapp ul { text-align: left; display: inline-block; }
</style>
</head>
<body>
<?php require $_SERVER['DOCUMENT_ROOT']."/lib/tmheader.php"; ?>

<div class="iosapp">
<p class="heading">Travel Mapping for iPhone and iPad</p>

<img class="icon" src="app-icon.png" alt="Travel Mapping app icon" />

<p class="text">
A free app for following your Travel Mapping travels on the go. It shows
the same data as this site: your maps, stats, and the leaderboard for
highways and railways.
</p>

<p>
<a href="https://apps.apple.com/app/id6761671062"><img class="badge" src="app-store-badge.svg" alt="Download on the App Store" /></a>
</p>

<div class="text">
<ul>
  <li>Browse any traveler's map, stats and region-by-region progress</li>
  <li>Record a road trip with GPS and see which segments are new for you</li>
  <li>Plan a route and see how many new segments each option would clinch</li>
  <li>Check your list file against the nightly log and fix lines that didn't count</li>
  <li>Optionally send new lines to TravelMapping/UserData as a pull request from your own GitHub account, for normal review</li>
</ul>
</div>

<p class="text">
The app is developed independently by Preston Siegel (psiegel18), a Travel
Mapping user. It isn't part of the Travel Mapping project. Questions and bug
reports go to the <a href="https://github.com/psiegel18/TravelMapIOS/issues">app's issue tracker</a>,
not the project forum.
</p>
</div>

</body>
</html>
