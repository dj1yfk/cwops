<?
# DB config
$mysql_host = "localhost";
$mysql_user = "cwops";
$mysql_pass = "cwops";
$mysql_dbname = "CWops";
$db = mysqli_connect($mysql_host,$mysql_user,$mysql_pass,$mysql_dbname) or die
("<h1>Sorry: Could not connect to database.</h1>");

?>
