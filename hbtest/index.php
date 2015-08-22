<?php
    header("Status: 301 Moved Permanently");
    header("Location:../devel/hb.php?". $_SERVER['QUERY_STRING']);
    exit;
?>
