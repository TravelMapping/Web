<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
</style>
<?php
  // establish connection to db: mysql_ interface is deprecated, should learn new options
  $db = new mysqli("localhost","travmap","clinch","TravelMapping") or die("Failed to connect to database");

  # functions from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
  function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
  }
  function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
  }
?>
<script>
</script>
<title>Travel Mapping Highway Data Updates</title>
</head>

<body onload="populate_dbarrays()">
<h1>Travel Mapping Highway Data Updates</h1>

<div id="updates">
  <table><tr><th>Date</th><th>Region</th><th>Route</th><th>Description</th></tr>
  <?php
      // select all updates in the DB
      $sql_command = "select * from updates;";
      $res = $db->query($sql_command);

      while ($row = $res->fetch_assoc()) {
        echo "<tr><td>".$row['date']."</td><td>".$row['region']."</td><td>".$row['route']."</td><td>".$row['description']."</td></tr>\n";
      }
      $res->free();
    ?>
  </table>
</div>
</body>
<?php
    $db->close();
?>
</html>
