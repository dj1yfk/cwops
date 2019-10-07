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
				<p>This server provides services for members of <a href="https://cwops.org/">CWops</a>. It's maintained by <a href="https://fkurz.net">Fabian, DJ1YFK</a> (CWops #1566) and is not (yet!) an official web site of the club.</p> <hr>

<h2>ACA, CMA, WAS, WAE and WAZ tracking</h2>
<?
if ($_SESSION['id']) {
?>
    <p>Logged in as <?=$_SESSION['callsign'];?>. <a href="/logout">Log out</a></p>

    <P>Upload new ADIF:
    <input type="file" id="file" /> <button id='upload' onClick='javascript:upload();'>Upload</button>
    </p>

    <div id="upload_result"></div>

    <script>
    function upload () {
        document.getElementById('upload').disabled = true;
        document.getElementById('upload').innerHTML = "Upload in progress...";
        var f = document.getElementById('file');
        var file = f.files[0];
        var data = new FormData();
        data.append("uploaded_file", file);
        var request =  new XMLHttpRequest();
        request.open("POST", '/api?action=upload', true);
        request.onreadystatechange = function() {
            var done = 4, ok = 200;
            if (request.readyState == done && request.status == ok) {
                if (request.responseText) {
                    document.getElementById('upload_result').innerHTML = request.responseText;
                    load_stats('overview', 'stat_main');
                }
            }
            else if (request.readyState == done) {
                document.getElementById('upload_result').innerHTML = "An error occured during the upload. Please try again. Split very large ADIF files (> 20MB) into smaller parts if possible.";
            }
            document.getElementById('upload').disabled = false;
            document.getElementById('upload').innerHTML= "Upload";
        }
        request.send(data);
    }

    function show(item) {
        document.getElementById("stats_div").style.display = "none";
        document.getElementById("edit_div").style.display = "none";
        document.getElementById("log_div").style.display = "none";
        document.getElementById(item + "_div").style.display = "inline"; 
    }

    function search () {
        var callsign = document.searchform.callsign.value;
        var nr = document.searchform.nr.value;
        var band = document.searchform.band.value;
        var ddate = document.searchform.band.value;
        var dxcc = document.searchform.dxcc.value;
        var waz = document.searchform.waz.value;
        var was = document.searchform.was.value;
        var wae = document.searchform.wae.value;

        console.log("search " + callsign + " " + nr + " " + band + " " + dxcc + " " + waz + " " + was + " " + wae);

        var request =  new XMLHttpRequest();
        request.open("GET", '/api?action=search&callsign=' + callsign + "&nr=" + nr + "&date=" + ddate + "&band=" + band + "&dxcc=" + dxcc + "&waz=" + waz + "&was=" + was + "&wae=" + wae, true);
        request.onreadystatechange = function() {
            var done = 4, ok = 200;
            if (request.readyState == done && request.status == ok) {
                if (request.responseText) {
                    console.log("Reply:" + request.responseText);
                    document.getElementById('search_results').innerHTML = request.responseText;
                }
            }
            else if (request.readyState == done) {
                document.getElementById('search_results').innerHTML = "An error occured. Please try again."; 
            }
        }
        console.log("sent...");
        request.send();
    }

    function save (id) {
        var callsign = document.getElementById('callsign' + id).value;
        alert(callsign);
    }

    </script>

<br>

<button id='stats' onClick="javascript:show(this.id);">Show Stats</button>
<button id='edit' onClick="javascript:show(this.id);">Edit QSOs</button>
<button id='log' onClick="javascript:show(this.id);">Enter QSOs</button>

<br>

<div id="edit_div" style="display:none;">
<h2>Edit a QSO</h2>
<p>Here you can edit a QSO you previously
uploaded to the database. Enter at least one
search item below and hit <button id='search' onClick="javascript:search();">Search</button>.</p>

<form name="searchform">
<table>
<tr><th>Callsign</th><th>CWops #</th><th>Date (YYYY-MM-DD)</th><th>Band</th><th>DXCC</th><th>WAZ</th><th>WAS</th><th>WAE</th></tr>

<?
    editformline("", "", "", "", "", "", "", "", "");
?>
</table>
</form>

<br>

<div id="search_results">
</div>

</div> <!-- edit_div -->
<div id="log_div" style="display:none;">Log</div>
<div id="stats_div">
<?
    echo stats($_SESSION['callsign']);
?>
</div>
<?
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
- <a href="/privacy">Impressum / Datenschutz / Privacy Policy</a> - <a href="https://git.fkurz.net/dj1yfk/cwops">Source code repository</a>
</p>

</body>
</html>



