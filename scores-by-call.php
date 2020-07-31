<head>
<link rel="stylesheet" type="text/css" href="/style.css">
</head>
<h2>Score overview</h2>
<a href="/">Back</a> - <a href="/scores">Score table</a><br><br>
<?php
session_start();
include("functions.php");

echo score_table_by_call();
?>
<br>
<a href="/">Back</a>
