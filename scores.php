<!DOCTYPE html>
<head>
<title>CWops Award Scores (ACA, ACMA, CMA, DXCC, WAS, WAE, WAZ)</title>
<link rel="stylesheet" type="text/css" href="/style.css">
</head>
<h2>Score overview</h2>
<a href="/">Back</a> - <a href="/scores-by-call">Sortable and searchable score table with graphs</a> - Final scores: <a href="/scores-2022-final">2022</a> - <a href="/scores-2021-final">2021</a> -  <a href="/scores-2020-final">2020</a>, <a href="/scores-2019-final">2019</a><br><br>
<?php
session_start();
include("functions.php");

echo score_table();
?>
<br>
<a href="/">Back</a>
