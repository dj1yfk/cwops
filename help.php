<!DOCTYPE html>
<html>
		<head>
				<link rel="stylesheet" type="text/css" href="/style.css">
				<title>CWops Award Tools - Help and Documentation</title>
				<link rel="icon" href="/favicon.ico">
				<link rel="shortcut icon" href="/favicon.ico">
		</head>
		<body>
				<h1>CWops Award Tools - Help and Documentation</h1>

<p><a href="/">Back to the main website</a></p>

<p>This is a list of questions that users asked or <em>might</em> ask. It's a work in progress and it will be extended over time.</p>

<h2>Where do I find the rules for the awards?</h2>
<p>On the <a href="https://cwops.org/contact-us/awards/">CWops website</a>.</p>

<h2>How to I report my scores?</h2>
<p>Scores are automatically reported to the awards manager. There's nothing you need to do!</p>

<h2>Can I import my contacts from CAM?</h2>
<p>Yes, just upload the "yourcall.data" file from CAM instead of an ADIF file.</p>

<h2>How are different callsigns handled?</h2>
<p>All logs you upload are credited to the account you are logged in with, regardless of the callsign you made the contacts with. This means you can upload your ADIF files from portable, holiday or DXpedition operations just like those from your home station. They are all merged automatically.</p>

<h2>Uploading the logs is very slow!</h2>
<p>Depending on the current load on the server and your internet connection, uploading and processing 1000 QSOs takes about one minute. It is recommended to split up your log into smaller chunks if it's very large. Most logging programs will allow you to export ADIF logs by date, so it would be a reasonable idea to upload logs year by year.</p>

<h2>How can I make QSOs with members on DXpeditions count?</h2>
<p>Many additional (individual) member calls are already in the database and will be recognized automatically. If a contact is not counted, there are two options: Either enter the homecall of the operator in the comment field of your logbook in the format: <pre>CWO:DJ1YFK</pre> (no spaces, all upper case). Alternatively, just enter the contact manually in the "Enter QSOs" tab.</p>

<h2>What's the checkbox about DXCC, WAZ and WAS all about?</h2>
<p>There's a checkbox on the log upload form saying "Take DXCC, WAZ and WAS values from the database (not from ADIF; recommended)". If this is checked, the application will try to resolve DXCCs, CQ zones (WAZ) and states by means of OK1RR's country files and the CWops member database. If it's not checked, it will blindly accept any values that your logger wrote into the ADIF file. From experience, the quality of exported data from many loggers isn't very good. More often than not, CQ and ITU zones are swapped, or some more exotic DXCC prefixes are not recognized as such.</p>
<p>Therefore the recommendation (and default) is to keep this box checked.</p>

<h2>Thanks</h2>
<p>Thanks to the following individuals for their help and contributions:</p>
<ul>
<li>Pete, W1RM (beta testing)</li>
<li>Bud, AA3B (beta testing)</li>
<li>Bill, W0TG (beta testing)</li>
<li>Bob, N7WY (membership roster)</li>
<li>Martin, OK1RR (Country resolution files, testing)</li>
<li>Petr, OK2CQR/OK7AN (HamQTH DXCC lookup API)</li>
<li>Adam, SQ9S (beta testing)</li>
<li>Giu, IT9VDQ (beta testing)</li>
</ul>

<h2>Future plans</h2>
<ul>
<li>RBN telnet server with filtering options for CWops members</li>
</ul>

<p><a href="/">Back to the main website</a></p>
<hr>
<p>Last modified: <? echo date ("Y-m-d",  filemtime("index.php")); ?> - <a href="http://fkurz.net/">Fabian Kurz, DJ1YFK</a> <a href="mailto:fabian@fkurz.net">&lt;fabian@fkurz.net&gt;</a>
- <a href="/privacy">Impressum / Datenschutz / Privacy Policy</a> - <a href="https://git.fkurz.net/dj1yfk/cwops">Source code repository</a>
</p>

</body>
</html>
