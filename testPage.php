<!DOCTYPE html>
<html>
<head>
  <title>Test Page</title>
  <!-- jQuery -->
  <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
  <!-- TableSorter -->
  <script src="lib/jquery.tablesorter.min.js"></script>
  <style type="text/css" src="css/tablesorter.css"></style>
</head>
<body>
  <script>
  $(document).ready(function()
    {
      $("#churches").tablesorter({
        sortList: [[0,0]],
        headers: {3:{sorter:false}}
      });
      $("#churches2").tablesorter({
        sortList: [[0,0]],
        headers: {3:{sorter:false}}
      });
    }
  );
  </script>
  <table id="churches" class="tablesorter ws_data_table">
    <thead>
      <tr>
        <th>Congregation</th>
        <th>City</th>
        <th>County</th>
        <th>Web</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>All Saints' Episcopal Church</td>
        <td>St. Louis</td>
        <td>St. Louis City</td>
        <td><a href="http://allsaintsstl.org/">Web</a></td>
      </tr>
      <tr>
        <td>All Saints' Episcopal Church</td>
        <td>Farmington</td>
        <td>St. Francois</td>
        <td><a href="http://diocesemo.org">Web</a></td>
      </tr>
      <tr>
        <td>St. John’s</td>
        <td>St. Louis</td>
        <td>St. Louis</td>
        <td><a href="http://towergrovechurch.org">Web</a></td>
      </tr>
    </tbody>
  </table>
  <table id="churches2" class="tablesorter ws_data_table">
    <thead>
      <tr>
        <th>Congregation</th>
        <th>City</th>
        <th>County</th>
        <th>Web</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>All Saints' Episcopal Church</td>
        <td>St. Louis</td>
        <td>St. Louis City</td>
        <td><a href="http://allsaintsstl.org/">Web</a></td>
      </tr>
      <tr>
        <td>All Saints' Episcopal Church</td>
        <td>Farmington</td>
        <td>St. Francois</td>
        <td><a href="http://diocesemo.org">Web</a></td>
      </tr>
      <tr>
        <td>St. John’s</td>
        <td>St. Louis</td>
        <td>St. Louis</td>
        <td><a href="http://towergrovechurch.org">Web</a></td>
      </tr>
    </tbody>
  </table>
</body>
</html>