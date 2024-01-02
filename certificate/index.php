<?
    $arr = file("data.txt");

    foreach ($arr as $a) {
        $line = preg_split("/,/", $a);
        $calls[$line[0]] = $line[1];
    }
?>
<!DOCTYPE html>
<!--
<?
    print_r($calls);
?>
-->
<html>
		<head>
				<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
				<link rel="stylesheet" type="text/css" href="/style.css">
				<title>CWops CWT Certificate Download</title>
				<link rel="icon" href="/favicon.ico">
				<link rel="shortcut icon" href="/favicon.ico">
		</head>
		<body>
<img src="certs.jpg" align="right">
                <h1>CWops CWT Certificate Download</h1>
<?
    $cs = strtoupper($_GET['callsign']);
    if ($calls[$cs]) {
            $cs = strtoupper($_GET['callsign']);
            $lock_count = 0;
            while (file_exists("/tmp/cwt-cert/lock")) {
                $f = file_get_contents("/tmp/cwt-cert/lock");
                error_log("$cs: locking due to: $f");
                sleep(1);
                $lock_count++;

                if ($lock_count > 5) {
                    error_log("$cs: lock_count exceeds 5 ... ignoring lock!");
                    break;
                }
            }
            file_put_contents("/tmp/cwt-cert/lock", $cs);
            $pdf = file_get_contents("https://cwops.telegraphy.de/certificate/generate.php?c=".$cs."&s=".$calls[$cs]);
            unlink("/tmp/cwt-cert/lock");
            file_put_contents("download/$cs.pdf", $pdf);
?>
    <p>Hello <?=$cs;?>, your certificate is ready to download. <a href="download/<?=$cs?>.pdf">Click this link to retrieve it</a>, or right-click and select "Save Link As..." to save it to your computer.</p>
<?
    }
    else if ($cs) {
?>
    <p>Sorry, no certificate for <?=$cs?> found! Please contact Rich, VE3KI if your score is missing.</p>
<?
    }
    else {
?>
<p>Please enter your callsign in the form and hit submit to generate and download the certificate for your participation in the 2023 CWTs.</p>

<form action="/certificate/" method="GET">
Callsign: <input type="text" size="12" name="callsign">
<input type="submit" value="Go">
</form>

<?
    }
?>
<br>
<p><a href="/">Back to cwops.telegraphy.de</a></p>
<hr>
<p>Last modified: <? echo date ("Y-m-d",  filemtime("index.php")); ?> - <a href="https://fkurz.net/">Fabian Kurz, DJ5CW</a> <a href="mailto:fabian@fkurz.net">&lt;fabian@fkurz.net&gt;</a>
- <a href="/privacy">Impressum / Datenschutz / Privacy Policy</a>
</p>

</body>
</html>



