<!DOCTYPE html>
<head>
<link rel="stylesheet" type="text/css" href="/style.css">
</head>
<h2>Score overview</h2>
<a href="/">Back</a> - <a href="/scores-2019-final">Final scores 2019</a><br><br>
<?php
session_start();
include("functions.php");

echo score_table();
?>
<br>
<a href="/">Back</a>
