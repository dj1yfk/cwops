<?
session_start();
include('functions.php');
?>
<!DOCTYPE html>
<html>
		<head>
				<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=iso-8859-1">
				<link rel="stylesheet" type="text/css" href="/style.css">
				<title>CWops Award Tools</title>
				<!-- link rel="icon" href="/favicon.ico">
				<link rel="shortcut icon" href="/favicon.ico" -->
		</head>
		<body>
				<h1>CWops Award Tools</h1>
				<p>This server provides services for members of <a href="https://cwops.org/">CWops</a>. It's maintained by <a href="https://fkurz.net">Fabian, DJ1YFK</a> (CWops #1566) and is not an official web site of the club.</p> <hr>

<h2>ACA, CMA, WAS, WAE and WAZ tracking</h2>
<?
if ($_SESSION['id']) {
?>
    <p>Logged in as <?=$_SESSION['callsign'];?>. <a href="/logout">Log out</a></p>
<?

    echo stats($_SESSION['callsign']);

}
else {
?>
<p>In order to track your standings for the various <a href="https://cwops.org/contact-us/awards/">CWops awards</a>, create a free account <em>or</em> if you already have an account, log in with the form below:</p>

<form action='/login' method='POST'>
<table><tr><td>Callsign:</td><td> <input name='callsign' type='text' size='10'></td></tr>
<tr><td>Password:</td><td><input name='password' type='password' size='10'></td></tr>
</table>
  <input type='submit' value='Log in or create new account'>
</form>

<p>Lost your password? Get in touch with <a href="mailto:fabian@fkurz.net">Fabian, DJ1YFK</a> to reset your account.</p>

<?
}
?>

<hr>
<p>Last modified: <? echo date ("Y-m-d",  filemtime("index.php")); ?> - <a href="http://fkurz.net/">Fabian Kurz, DJ1YFK</a> <a href="mailto:fabian@fkurz.net">&lt;fabian@fkurz.net&gt;</a>
<? 
		if (!$_SERVER['HTTPS']) { ?> - <a rel="nofollow" href="https://cwops.telegraphy.de/">Switch to https</a> <? } 
		else { ?> - <a rel="nofollow" href="http://cwops.telegraphy.de/">Switch to http</a> <? } 
?>
- <a href="/privacy">Impressum / Datenschutz / Privacy Policy</a>
</p>

</body>
</html>



