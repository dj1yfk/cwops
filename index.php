<?
session_start();
include('functions.php');
?>
<!DOCTYPE html>
<html>
		<head>
				<link rel="stylesheet" type="text/css" href="/style.css">
				<title>CWops Award Tools</title>
				<link rel="icon" href="/favicon.ico">
				<link rel="shortcut icon" href="/favicon.ico">
		</head>
		<body>
				<h1>CWops Award Tools</h1>
				<p>This server provides services for members of <a href="https://cwops.org/">CWops</a>. <a href="/help">Help and Documentation</a> - <a href="/intro">Introduction Video</a></p>

<h2>ACA, CMA, WAS, WAE and WAZ tracking</h2>
<?
if (array_key_exists("id", $_SESSION)) {
?>
    <p>Logged in as <?=$_SESSION['callsign'];?>. <a href="/logout">Log out</a></p>

    <P>Upload new ADIF/CAM data:
    <input type="file" id="file" /> <button id='upload' onClick='javascript:upload();'>Upload</button>
    <input id="cbignore" type="checkbox" name="cbignore" value="1" checked> Take DXCC, WAZ and WAS values from the database (not from ADIF; recommended)
    </p>

    <div id="upload_result"></div>

    <script>
	function ol () {
		check_email();
	}


    window.setInterval(keepalife, 300000);
    function keepalife () {
        var request =  new XMLHttpRequest();
        request.open("GET", "/style.css", true);
        request.send();
    }

    function upload () {
        document.getElementById('upload').disabled = true;
        document.getElementById('upload').innerHTML = "Upload in progress...";
        var ign = document.getElementById('cbignore').checked;
        var f = document.getElementById('file');
        var file = f.files[0];
        var data = new FormData();
        data.append("uploaded_file", file);
        var request =  new XMLHttpRequest();
        request.open("POST", '/api?action=upload&ign=' + (ign ? '1' : '0'), true);
        request.onreadystatechange = function() {
            var done = 4, ok = 200;
            if (request.readyState == done && request.status == ok) {
                if (request.responseText) {
                    document.getElementById('upload_result').innerHTML = request.responseText;
                    reload_stats();
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
        var items = [ "stats", "edit", "log", "uploads", "account" ];

        for (var i = 0; i < items.length; i++) {
            console.log(items[i]);
            document.getElementById(items[i]).style.fontWeight = "normal";
            document.getElementById(items[i] + "_div").style.display = "none";
        } 

        document.getElementById(item + "_div").style.display = "inline"; 
        document.getElementById(item).style.fontWeight = "bold";
    }

    function search () {
        var hiscall = document.searchform.hiscall.value;
        var nr = document.searchform.nr.value;
        var band = document.searchform.band.value;
        var ddate = document.searchform.band.value;
        var dxcc = document.searchform.dxcc.value;
        var waz = document.searchform.waz.value;
        var was = document.searchform.was.value;
        var wae = document.searchform.wae.value;

        console.log("search " + hiscall+ " " + nr + " " + band + " " + dxcc + " " + waz + " " + was + " " + wae);

        var request =  new XMLHttpRequest();
        request.open("GET", '/api?action=search&hiscall=' + hiscall + "&nr=" + nr + "&date=" + ddate + "&band=" + band + "&dxcc=" + dxcc + "&waz=" + waz + "&was=" + was + "&wae=" + wae, true);
        request.onreadystatechange = function() {
            var done = 4, ok = 200;
            if (request.readyState == done && request.status == ok) {
                if (request.responseText) {
                    document.getElementById('search_results').innerHTML = request.responseText;
                }
            }
            else if (request.readyState == done) {
                document.getElementById('search_results').innerHTML = "An error occured. Please try again."; 
            }
        }
        request.send();
    }

    function save (id) {
        //try {
        var items = ['hiscall', 'nr', 'date', 'band', 'dxcc', 'waz', 'was', 'wae'];
        var o = new Object();

        for (var i = 0; i < items.length; i++) {
            console.log(items[i] + id);
            o[items[i]] = document.getElementById(items[i] + id).value;
        }

        o['id'] = id;

        var request =  new XMLHttpRequest();
        request.open("POST", '/api?action=save', true);
        request.onreadystatechange = function() {
            var done = 4, ok = 200;
            if (request.readyState == done && request.status == ok) {
                if (request.responseText) {
                    alert( request.responseText);
                }
            }
            else if (request.readyState == done) {
                alert("An error occured. Please try again.");
                return false;
            }
        }
        console.log(JSON.stringify(o));
        request.send(JSON.stringify(o));
    }

    function reload_stats() {
        var request =  new XMLHttpRequest();
        request.open("GET", '/api?action=stats', true);
        request.onreadystatechange = function() {
            var done = 4, ok = 200;
            if (request.readyState == done && request.status == ok) {
                if (request.responseText) {
                    document.getElementById('stats_div').innerHTML = request.responseText;
                }
            }
            else if (request.readyState == done) {
                document.getElementById('stats_div').innerHTML = "Error loading stats...";
            }
        }
        request.send();
    }

    // When entering a QSO manually, fill DXCC and WAZ automatically
    // based on the callsign
    function dxcc_lookup(c) {
        var request =  new XMLHttpRequest();
        request.open("GET", '/api?action=lookup&hiscall=' + c, true);
        request.onreadystatechange = function() {
            var done = 4, ok = 200;
            if (request.readyState == done && request.status == ok) {
                if (request.responseText) {
                    try {
                        var o = JSON.parse(request.responseText);
                        if (o['adif']) {
                            var d = document.getElementById('dxcc0');
                            d.value = o['adif'];
                        }
                        if (o['waz']) {
                            var d = document.getElementById('waz0');
                            d.selectedIndex = o['waz'];
                        }

                    }
                    catch (e) {
                        console.log("parsing lookup json failed");
                    }
                }
            }
        }
        request.send();
    }

    function clear_form (nr) {
        var items = ['hiscall', 'nr', 'date', 'band'];

        for (var i = 0; i < items.length; i++) {
            document.getElementById(items[i] + nr).value = "";
        }
        
        document.getElementById('was' + nr).selectedIndex = 0;
        document.getElementById('wae' + nr).selectedIndex = 0;

    }

    function reload_upload_history() {
        var request =  new XMLHttpRequest();
        request.open("GET", '/api?action=upload_history', true);
        request.onreadystatechange = function() {
            var done = 4, ok = 200;
            if (request.readyState == done && request.status == ok) {
                if (request.responseText) {
                    document.getElementById('uploads_div').innerHTML = request.responseText;
                }
            }
        }
        request.send();
    }

    function wipe() {
        if (!confirm("Really delete all QSOs?")) {
            alert("Aborted...");
            return;
        }
        var request =  new XMLHttpRequest();
        request.open("GET", '/api?action=wipe', true);
        request.onreadystatechange = function() {
            var done = 4, ok = 200;
            if (request.readyState == done && request.status == ok) {
                if (request.responseText) {
                    alert(request.responseText);
                }
            }
        }
        request.send();
    }

    function reload_account() {
        console.log("reload account");
    }

    function update_account(item) {
        var value = document.getElementById(item + '_field').value;
        console.log('update account ' + item + ' => ' + value);

        if (!value) {
            alert("Value for " + item  + " must not be empty!");
            return;
        }

        var request =  new XMLHttpRequest();
        request.open("POST", '/api?action=update_account', true);
        request.onreadystatechange = function() {
            var done = 4, ok = 200;
            if (request.readyState == done && request.status == ok) {
                if (request.responseText) {
                    alert(request.responseText);
                }
            }
        }
        request.send(JSON.stringify({"item": item, "value": value}));
    }

    function check_email () {
        var f = document.getElementById('email_field');
        var b = document.getElementById('setemail');
        // check for email in format .+@.*\..+
        var r = new RegExp('.+@.+[.].+');
        if (f.value.match(r)) {
            f.style.color = '#119911';
			b.disabled = false;
        }
        else {
            f.style.color = '#ff0000';
			b.disabled = true;
        }
    }
    </script>

<br>

<button id='stats' style="font-weight:bold" onClick="javascript:show(this.id);reload_stats();">Show Stats</button>
<button id='edit' onClick="javascript:show(this.id);">Edit QSOs</button>
<button id='log' onClick="javascript:show(this.id);">Enter QSOs</button>
<button id='uploads' onClick="javascript:show(this.id);reload_upload_history();">Show upload history</button>
<button id='account' onClick="javascript:show(this.id);reload_account();">Account</button>

<br>

<div id="edit_div" style="display:none;">
<h2>Edit a QSO</h2>
<p>Here you can edit a QSO you previously
uploaded to the database. Enter at least one
search item below and hit <button id='search' onClick="javascript:search();">Search</button>.

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

<br>
If you like to start over (re-upload your whole log), you can delete all QSOs that were saved with the following button: <button id='wipe' onClick="javascript:wipe();">Delete (Reset) whole log</button>

</div> <!-- edit_div -->
<div id="log_div" style="display:none;">
<h2>Log contacts manually</h2>
<p>Here you can easily enter contacts manually, for example to add QSOs with members on DXpeditions. <button id='search' onClick="javascript:clear_form(0);">Clear Form</button></p>
<table>
<tr><th>Callsign</th><th>CWops #</th><th>Date (YYYY-MM-DD)</th><th>Band</th><th>DXCC</th><th>WAZ</th><th>WAS</th><th>WAE</th><th>Save</th></tr>
<?
    editformline("", "", "", "", "", "", "", "", "new");
?>
</table>

</div>
<div id="stats_div">
<?
    echo stats($_SESSION['callsign']);
?>
</div>
<div id="uploads_div" style="display:none;">
<h2>QSO upload history</h2>
</div>
<div id="account_div" style="display:none;">

<h2>Change Account Settings</h2>

<p>Here you can change your password and enter an email address (for account recovery). If you changed your callsign and want to change the account name, please send a mail to Fabian, DJ1YFK <a href="mailto:fabian@fkurz.net">&lt;fabian@fkurz.net&gt;</a>.</p>

<table>
<tr><td>Password:</td><td> <input id='password_field' name='password' type='password' size='15'></td><td><button onClick="javascript:update_account('password');">Set new password</button></td></tr>
<tr><td>E-Mail:</td><td><input  oninput="check_email();" id='email_field' name='email' type='text' size='15' value="<?=$_SESSION['email'];?>"></td><td><button id="setemail" onClick="javascript:update_account('email');">Save email address</button></td></tr>
</table>

</div>
<div id="summary_div" style="display:none;">
</div>

    <script>
    ol();
    </script>

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

<p>When signing up, you accept the <a href="/privacy">privacy policy</a> of this site.</p>


<p>Lost your password and no email address in account profile? Get in touch with <a href="mailto:help@cwops.telegraphy.de">Fabian, DJ1YFK</a> to reset your account.</p>

<?
}
?>

<hr>
<p>Last modified: <? echo date ("Y-m-d",  filemtime("index.php")); ?> - <a href="http://fkurz.net/">Fabian Kurz, DJ1YFK</a> <a href="mailto:fabian@fkurz.net">&lt;fabian@fkurz.net&gt;</a>
- <a href="/privacy">Impressum / Datenschutz / Privacy Policy</a> - <a href="https://git.fkurz.net/dj1yfk/cwops">Source code repository</a>
</p>

</body>
</html>



