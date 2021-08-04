<!DOCTYPE html>
<head>
<title>CWops Award Scores (ACA, CMA, DXCC, WAS, WAE, WAZ)</title>
<link rel="stylesheet" type="text/css" href="/style.css">
</head>
<h2>Sortable and searchable table</h2>
<a href="/">Back</a> - <a href="/scores">Score overview</a><br><br>
<?php
session_start();
include("functions.php");

echo score_table_by_call();
?>
<br>
<a href="/">Back</a>
