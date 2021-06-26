<?php
include_once("db.php");
include_once("states.php");
include_once("dxccs.php");
include_once("wae.php");
$call_exceptions = unserialize(file_get_contents("db/calls.phpserial"));

$arr_states = array("AK"=>1, "HI"=>1, "CT"=>1, "ME"=>1, "MA"=>1, "NH"=>1, "RI"=>1, "VT"=>1, "NJ"=>1, "NY"=>1, "DE"=>1, "MD"=>1, "PA"=>1, "AL"=>1, "FL"=>1, "GA"=>1, "KY"=>1, "NC"=>1, "SC"=>1, "TN"=>1, "VA"=>1, "AR"=>1, "LA"=>1, "MS"=>1, "NM"=>1, "OK"=>1, "TX"=>1, "CA"=>1, "AZ"=>1, "ID"=>1, "MT"=>1, "NV"=>1, "OR"=>1, "UT"=>1, "WA"=>1, "WY"=>1, "MI"=>1, "OH"=>1, "WV"=>1, "IL"=>1, "IN"=>1, "WI"=>1, "CO"=>1, "IA"=>1, "KS"=>1, "MN"=>1, "MO"=>1, "NE"=>1, "ND"=>1, "SD"=>1);

function stats($c) {
    if ($_SESSION['manual']) {
        stats_manual($c);
    }
    else {
        stats_default($c);
    }
}

function stats_manual($c) {
    global $db;

    $q = mysqli_query($db, "select aca, cma, was, dxcc, wae, waz from cwops_scores where uid=".$_SESSION['id']);
    $r = mysqli_fetch_row($q);
?>
    <h2>Statistics for <?=$_SESSION['callsign'];?></h2>
<table>
<tr><th>Award</th><th>Score</th><th>PDF</th></tr>
<tr><td>ACA</td> <td><input size="3" value="<?=$r[0];?>" id="acamanual" onchange="update_manual(this.id);"></td> <td><a href="/api.php?action=award_pdf&type=aca">Download PDF award</a></td></tr>
<tr><td>CMA</td> <td><input size="3" value="<?=$r[1];?>"id="cmamanual" onchange="update_manual(this.id);"></td> <td><a href="/api.php?action=award_pdf&type=cma">Download PDF award</a></td></tr>
<tr><td>WAS</td> <td><input size="3" value="<?=$r[2];?>" id="wasmanual" onchange="update_manual(this.id);"></td> <td><a href="/api.php?action=award_pdf&type=was">Download PDF award</a></td></tr>
<tr><td>DXCC</td><td><input size="3" value="<?=$r[3];?>" id="dxccmanual" onchange="update_manual(this.id);"></td> <td><a href="/api.php?action=award_pdf&type=dxcc">Download PDF award</a></td></tr>
<tr><td>WAE</td> <td><input size="3" value="<?=$r[4];?>" id="waemanual" onchange="update_manual(this.id);"></td> <td><a href="/api.php?action=award_pdf&type=wae">Download PDF award</a></td></tr>
<tr><td>WAZ</td> <td><input size="3" value="<?=$r[5];?>" id="wazmanual" onchange="update_manual(this.id);"></td> <td><a href="/api.php?action=award_pdf&type=waz">Download PDF award</a></td></tr>
</table>
<?
}

function stats_default($c) {
    global $db;
    global $wae_adif;

    # ACA

    # $q = mysqli_query($db, "SELECT count(distinct(`nr`)) from cwops_log where `mycall`='$c' and year=2021");  # roll over => keep last year's scores
    $q = mysqli_query($db, "SELECT count(distinct(`nr`)) from cwops_log where `mycall`='$c' and year=YEAR(CURDATE())");
    $r = mysqli_fetch_row($q);
    $aca = $r[0];

    $q = mysqli_query($db, "SELECT count(distinct `nr`, `band`) from cwops_log where `mycall`='$c'");
    $r = mysqli_fetch_row($q);
    $cma = $r[0];

    $q = mysqli_query($db, "SELECT count(distinct(`was`)) from cwops_log where length(`was`) =  2 and `mycall`='$c'");
    $r = mysqli_fetch_row($q);
    $was = $r[0];

    $q = mysqli_query($db, "SELECT count(distinct(`dxcc`)) from cwops_log where `dxcc` > 0 and `mycall`='$c'");
    $r = mysqli_fetch_row($q);
    $dxcc = $r[0];

    $q = mysqli_query($db, "SELECT count(distinct(`dxcc`)) from cwops_log where `dxcc` > 0 and `mycall`='$c' and dxcc in (".implode(',', $wae_adif).")");
    $r = mysqli_fetch_row($q);
    $wae = $r[0];
    # add special WAE areas
    $q = mysqli_query($db, "SELECT count(distinct(`wae`)) from cwops_log where LENGTH(wae) = 2 and `mycall`='$c' and dxcc in (".implode(',', $wae_adif).")");
    $r = mysqli_fetch_row($q);
    $wae += $r[0];

    $q = mysqli_query($db, "SELECT count(distinct(`waz`)) from cwops_log where waz > 0 and waz < 41 and `mycall`='$c'");
    $r = mysqli_fetch_row($q);
    $waz = $r[0];

?>
    <h2>Statistics for <?=$_SESSION['callsign'];?></h2>
<!-- p>Note: The year for which the scores are calculated will remain 2020 until January 5th, 2021, to give you sufficient time to upload your remaining 2020 logs. After that, it will switch to 2021 and the 2020 score table will be archived.</p -->
<table>
<tr><th>Award</th><th>Score</th><th>Details</th><th>PDF</th></tr>
<tr><td>ACA</td> <td><?=$aca?></td> <td><?=award_details('aca', 'y');?></td><td><a href="/api.php?action=award_pdf&type=aca">Download PDF award</a></td></tr>
<tr><td>CMA</td> <td><?=$cma?></td> <td><?=award_details('cma', 'x');?></td><td><a href="/api.php?action=award_pdf&type=cma">Download PDF award</a></td></tr>
<tr><td>WAS</td> <td><?=$was?></td> <td><?=award_details('was', 'b');?></td><td><a href="/api.php?action=award_pdf&type=was">Download PDF award</a></td></tr>
<tr><td>DXCC</td><td><?=$dxcc?></td><td><?=award_details('dxcc', 'b');?></td><td><a href="/api.php?action=award_pdf&type=dxcc">Download PDF award</a></td></tr>
<tr><td>WAE</td><td><?=$wae?></td><td><?=award_details('wae', 'b');?></td><td><a href="/api.php?action=award_pdf&type=wae">Download PDF award</a></td></tr>
<tr><td>WAZ</td> <td><?=$waz?></td> <td><?=award_details('waz', 'b');?></td><td><a href="/api.php?action=award_pdf&type=waz">Download PDF award</a></td></tr>
</table>

<br>

<div id="details"></div>

    <script>
    function load_stats(t, d) {
        if (document.getElementById('band'+t)) {
            var band = document.getElementById('band'+t).value;
        }
        else {
            var band = "";
        }

        if (document.getElementById('year'+t)) {
            var year = document.getElementById('year'+t).value;
        }
        else {
            var year = "";
        }

        console.log('Details for ' + t + ' on ' + band + " and year = " + year);

        var request =  new XMLHttpRequest();
        request.open("GET", '/api?action=' + t + '&band=' + band + '&year=' + year, true);
        request.onreadystatechange = function() {
                var done = 4, ok = 200;
                if (request.readyState == done && request.status == ok) {
                        if (request.responseText) {
                                var s = document.getElementById(d);
                                s.innerHTML = request.responseText;
                        }
                };
        }
        request.send();

    }
    </script>

<?
    # finally, save to cwops_scores table
    $q = mysqli_query($db, "delete from cwops_scores where `uid` = ".$_SESSION['id']);
    $q = mysqli_query($db, "insert into cwops_scores (`uid`, `aca`, `cma`, `was`, `dxcc`, `wae`, `waz`, `updated`) VALUES (".$_SESSION['id'].", $aca, $cma, $was, $dxcc, $wae, $waz, NOW());");
    if (!$q) {
        error_log("score update failed".mysqli_error($db));
    }

}   # stats

function award_details($t, $b) {
    $ret = "<button id='$t' onClick='javascript:load_stats(this.id, \"details\");'>Show details</button>";

    if ($b == 'b') {
        $ret .= "<select name=\"band\" id=\"band$t\" size=1>
               <option>all</option>
               <option>160</option>
               <option>80</option>
               <option>60</option>
               <option>40</option>
               <option>30</option>
               <option>20</option>
               <option>17</option>
               <option>15</option>
               <option>12</option>
               <option>10</option>
               <option>6</option>
               <option>2</option></select>";
    }
    else if ($b == 'y') {
        $ret .= "<select name=\"year\" id=\"year$t\" size=1>\n";
        for ($i = date("Y"); $i >= 2010; $i--) {
            if ($i == date("Y")) {
                $selected = "selected";
            }
            else {
                $selected = "";
            }
            $ret .= "<option $selected>$i</option>\n";
        }
        $ret .= "</select>";
    }
    return $ret;
}

function aca($c, $y) {
    global $db;
    $ret = "";
    $q = mysqli_query($db, "SELECT `nr`, hiscall, date, band from cwops_log where `mycall`='$c' and year=$y group by `nr`");
    if(!$q) {
        echo mysqli_error($db);
    }
    $cnt = 1;
    $ret .= "<table><tr><th>Count</th><th>CWops</th><th>Call</th><th>Date</th><th>Band</th></tr>\n";
    while ($r = mysqli_fetch_row($q)) {
        $ret .= "<tr><td>".$cnt++."</td><td>".$r[0]."</td><td>".$r[1]."</td><td>".$r[2]."</td><td>".$r[3]."</td></tr>\n";
    }
    $ret .= "</table>";
    $cnt--;
    $ret = "<h2>ACA details for $c ($y): $cnt</h2>".$ret;
    return $ret;
}

function cma($c) {
    global $db;
    $ret = "<h2>CMA details for $c</h2>";
    $q = mysqli_query($db, "SELECT nr, hiscall, date, band from cwops_log where `mycall`='$c' group by nr, band");
    if(!$q) {
        echo mysqli_error($db);
    }
    $cnt = 1;
    $ret .= "<table><tr><th>Count</th><th>CWops</th><th>Call</th><th>Date</th><th>Band</th></tr>\n";
    while ($r = mysqli_fetch_row($q)) {
        $ret .= "<tr><td>".$cnt++."</td><td>".$r[0]."</td><td>".$r[1]."</td><td>".$r[2]."</td><td>".$r[3]."</td></tr>\n";
    }
    $ret .= "</table>";
    return $ret;
}


function was($c, $b) {
    global $db;
    global $arr_states; 

    $ret = "<h2>WAS details for $c";
    if ($b != "all") {
        $band = " and band=$b ";
        $ret .= " (".$b."m)";
    }
    $ret .= "</h2>"; 

    $q = mysqli_query($db, "SELECT `was`, `nr`, hiscall, date, band from cwops_log where `mycall`='$c'  and LENGTH(`was`) = 2 $band group by `was`");
    if(!$q) {
        echo mysqli_error($db);
    }

    $states_needed = $arr_states;
    
    $cnt = 1;
    $ret .= "<table><tr><th>Count</th><th>State</th><th>CWops</th><th>Call</th><th>Date</th><th>Band</th></tr>\n";
    while ($r = mysqli_fetch_row($q)) {
        $ret .= "<tr><td>".$cnt++."</td><td>".$r[0]."</td><td>".$r[1]."</td><td>".$r[2]."</td><td>".$r[3]."</td><td>".$r[4]."</td></tr>\n";
        unset($states_needed[$r[0]]);
    }
    $ret .= "</table>";
    $ret .= "<p>Needed: ".implode(", ", array_keys($states_needed))."</p>";
    return $ret;
}

function waz($c, $b) {
    global $db;
    $ret = "<h2>WAZ details for $c";

    if ($b != "all") {
        $band = " and band=$b ";
        $ret .= " (".$b."m)";
    }
    $ret .="</h2>";

    $q = mysqli_query($db, "SELECT `waz`, `nr`, hiscall, date, band from cwops_log where `mycall`='$c' and waz > 0 $band group by `waz`");
    if(!$q) {
        echo mysqli_error($db);
    }

    $cnt = 1;
    $ret .= "<table><tr><th>Count</th><th>Zone</th><th>CWops</th><th>Call</th><th>Date</th><th>Band</th></tr>\n";
    while ($r = mysqli_fetch_row($q)) {
        $ret .= "<tr><td>".$cnt++."</td><td>".$r[0]."</td><td>".$r[1]."</td><td>".$r[2]."</td><td>".$r[3]."</td><td>".$r[4]."</td></tr>\n";
    }
    $ret .= "</table>";
    return $ret;
}

function dxcc($c, $b) {
    global $db;
    global $dxcc;

    $ret = "<h2>DXCC details for $c";

    if ($b != "all") {
        $band = " and band=$b ";
        $ret .= " (".$b."m)";
    }
    $ret .="</h2>";

    $q = mysqli_query($db, "SELECT `dxcc`, `nr`, hiscall, date, band from cwops_log where `mycall`='$c' and dxcc > 0 $band group by `dxcc` order by hiscall asc");
    if(!$q) {
        echo mysqli_error($db);
    }

    $cnt = 1;
    $ret .= "<table><tr><th>Count</th><th>DXCC</th><th>CWops</th><th>Call</th><th>Date</th><th>Band</th></tr>\n";
    while ($r = mysqli_fetch_row($q)) {
        $ret .= "<tr><td>".$cnt++."</td><td>".$dxcc[$r[0]]." (".$r[0].")</td><td>".$r[1]."</td><td>".$r[2]."</td><td>".$r[3]."</td><td>".$r[4]."</td></tr>\n";
    }
    $ret .= "</table>";
    return $ret;
}

function wae($c, $b) {
    global $db;
    global $waes;
    global $wae_adif;
    global $dxcc;

    $needed = array();
    foreach (array_merge($wae_adif, array_keys($waes)) as $k) {
        $needed[$k] = 1;
    }
    

    $ret = "<h2>WAE details for $c";

    if ($b != "all") {
        $band = " and band=$b ";
        $ret .= " (".$b."m)";
    }
    $ret .="</h2>";

    $q = mysqli_query($db, "SELECT `dxcc`, `nr`, hiscall, date, band from cwops_log where `mycall`='$c' and dxcc in (".implode(',', $wae_adif).") and wae='' $band group by `dxcc` order by hiscall asc");
    if(!$q) {
        echo mysqli_error($db);
    }

    $cnt = 1;
    $ret .= "<table><tr><th>Count</th><th>DXCC</th><th>CWops</th><th>Call</th><th>Date</th><th>Band</th></tr>\n";
    while ($r = mysqli_fetch_row($q)) {
        $ret .= "<tr><td>".$cnt++."</td><td>".$dxcc[$r[0]]." (".$r[0].")</td><td>".$r[1]."</td><td>".$r[2]."</td><td>".$r[3]."</td><td>".$r[4]."</td></tr>\n";
        unset($needed[$r[0]]); 
    }

    # fetch extra WAE entities
    $q = mysqli_query($db, "SELECT `wae`, `nr`, hiscall, date, band from cwops_log where `mycall`='$c' and LENGTH(wae) = 2  $band group by `wae`");
    if(!$q) {
        echo mysqli_error($db);
    }
    
    while ($r = mysqli_fetch_row($q)) {
        $ret .= "<tr><td>".$cnt++."</td><td>".$waes[$r[0]]." (".$r[0].")</td><td>".$r[1]."</td><td>".$r[2]."</td><td>".$r[3]."</td><td>".$r[4]."</td></tr>\n";
        unset($needed[$r[0]]); 
    }

    // replace numeric ADIF numbers with DXCC names and WAE abbreviations with
    // full name
    foreach (array_keys($needed) as $k) {
        if (is_numeric($k)) {
            $needed[$dxcc[$k]] = 1;
        }
        else {
            $needed[$waes[$k]] = 1;
        }
        unset($needed[$k]);
    }

    $ret .= "</table><br>Still needed:<br>".implode('<br>', array_keys($needed));
    return $ret;
}



# import an ADIF file to the log of $callsign
#
# Only import QSOs when it's a new
# - Member (identified by his number) on this band OR in a new year
# - State on this band
# - DXCC on this band
# - WAZ on this band
# - WAE (on any band)

# Steps:
# 1. Load the current member list into an array
# 2. Parse ADIF/CSV into an array, omitting all calls that are not members (considering the date of the QSO) and non-CW-QSOs
# 3. Iterate through imported log and based on (1) decide which QSOs are saved in the database.

# If ign is set, ignore DXCC, WAZ and State info from the uploaded log

function import($filename, $adif, $callsign, $ign) {
    global $db;

    $ret = "<br>Starting import ($filename) for $callsign...<br>";
    $members = get_memberlist();
    $ret .= "Loaded member list with ".count($members)." entries.<br>";

    # detect start date for importing QSOs, based on user's callsign.
    $startdate = get_joindate($callsign);

    if ($startdate) {
        $ret .= "CWops join date for $callsign is ".$startdate." <br>";
        $startdate = preg_replace('/\-/', '', $startdate);
    }
    else {
        $ret .= "<b>Could not identify $callsign as a CWops member (possibly because you recently joined and the member list on this site is not yet updated). Starting import from 2010-01-01. If you joined CWops later, please only upload logs after your start date!</b><br>";
    }

    # detect data format. it may be ADIF, CSV export from CAM
    # or Cabrillo. If it is Cabrillo or CSV, convert it to ADIF
    # first and then import it

    if (strstr($adif, "START-OF-LOG:")) {
        $ret .= "Data format looks like Cabrillo. Trying to convert...<br>";
        $adif = parse_cam_cbr($adif, $members, "CBR");
    }
    else if (!(strstr($adif, "<eoh>") or strstr($adif, "<EOH>"))) { # no end of ADIF header
        $ret .= "Data format looks like CAM exported CSV (not ADIF). Trying to convert...<br>";
        $adif = parse_cam_cbr($adif, $members, "CAM");
    }

    $qsos = parse_adif($adif, $members, $ign, $startdate);
    $ret .= "Parsed log file with ".count($qsos)." QSOs with CWops members.<br>";

    $qsos = filter_qsos($qsos, $callsign);
    $ret .= "Imported ".count($qsos)." QSOs which were new for award purposes.<br>";

    $nr = rand(0,10000);

    $ret .= "Full log of the import below. <a href='#' onClick='javascript:document.getElementById(\"import_log$nr\").style.display = \"none\";'>Click here to hide</a>";

    $ret .= "<pre id='import_log$nr'>";
    $import_log = "";
    foreach ($qsos as $q) {
        $import_log .= "QSO: ".$q['call']." ".$q['date']." ".$q['band']." needed for: ".$q['reasons']."<br>";
    }
    $ret .= $import_log;
    $ret .= "</pre>";

    # save this to the upload history
    $q = mysqli_query($db, "insert into cwops_uploads (`uid`, `ts`, `count`, `result`) 
         VALUES (".$_SESSION['id'].", NOW(), ".count($qsos)." ,'".mysqli_real_escape_string($db, $import_log)."')");

    if (!$q) {
        error_log("import: insert into cwops_upload failed: ".mysqli_error($db));
    }

    return $ret;
}

function get_memberlist() {
    global $db;
    $q = mysqli_query($db, "SELECT * from cwops_members;");
    
    $members = array();
    while ($r = mysqli_fetch_array($q, MYSQLI_ASSOC)) {
        array_push($members, $r);
    }

    return $members;
}

# parse CAM CSV file format or Cabrillo log and make ADIF
# CAM: 20100101,1111,N3JT,40M,CW,K,VA,JIM,1
# CBR: QSO:  3500 CW 2019-10-03 0721 DA0HSC        599 M      DL5AXX        599 GI

function parse_cam_cbr ($data, $members, $type) {

    # make hash table for quicker member lookup
    $mh = array();        # call -> info
    $mhnr = array();    # nr   -> call
    foreach ($members as $m) {
        $mh[$m["callsign"]] = $m;
        $mhnr[$m["nr"]] = $m["callsign"];
    }

    $data = strtoupper($data);
    $data = preg_replace('/\r/', '', $data);
    $qsos = explode("\n", $data);

    $adif = "header\n<EOH>\n";
    foreach ($qsos as $q) {

        if ($type == "CAM") {
            $a = explode(",", $q);

            if (count($a) != 9) {
                continue;
            }

            $qso_date = $a[0];
            $call = $a[2];
            $band = $a[3];
            $state = $a[6];
        }
        if ($type == "CBR") {
            $a = preg_split('/\s+/', $q); 

            if ($a[0] != "QSO:") {
                continue;
            }

            $qso_date = preg_replace('/\-/', '', $a[3]);
            # find the call. most of the time it's the 9th field...
            $call = $a[8];

            # if not, search after the 7th field
            $i = 6;
            while (!is_call($call)) {
                $call = $a[$i++];
                if ($i > count($a)) {
                    break;
                }
            }

            $band = f2b($a[1]/1000)."m";
            $state = "--";
        }

        $adif .= makeadi('qso_date', $qso_date); 
        $adif .= makeadi('call', $call); 
        $adif .= makeadi('band', $band); 
        $adif .= makeadi('state', $state); 
        $adif .= makeadi('mode', "CW"); 

        # CAM: Is this a member call? If not, but the CWops nr is valid,
        # add the member's call as a remark
        if ($type == "CAM" and !array_key_exists($a[2], $mh) and array_key_exists($a[8], $mhnr)) {
            $adif .=  "CWO:".$mhnr[$a[8]]." ";
        }
        $adif .= " <EOR>\n";
    }
    file_put_contents("/tmp/adif", $adif);
    return $adif;
}


function makeadi ($field, $value) {
    return "<".$field.":".strlen($value).">".$value." ";
}

# parse ADIF and return member QSOs (matched by date) 
function parse_adif($adif, $members, $ign, $startdate) {

    $out = array();

    # make hash table for quicker member lookup
    $mh = array();
    foreach ($members as $m) {
        # fix date
        $m['joined'] = preg_replace('/-/','',  $m['joined']);
        $m['left']   = preg_replace('/-/','',  $m['left']);
        $mh[$m["callsign"]] = $m;
    }

    $adif = strtoupper($adif);
    $qsos = explode("<EOR>", $adif);

    foreach ($qsos as $q) {
        unset($call); unset($date); unset($band);
        if (preg_match('/<CALL:\d+(:\w)?()>([A-Z0-9\/]+)/', $q, $match)) {
            $qsocall = $match[3];

            # check if QSO is CW mode
            if (!preg_match('/<MODE:2(:\w)?()>CW/', $q)) {
                continue;
            }
            
            # strip call (portable stuff etc.)
            if (preg_match('/^(\w{1,3}\/)?(\w{3,99})(\/\w{1,3})?$/', $qsocall, $match)) {
                $call = $match[2];
            }
            else {
                $call = $qsocall;
            }

            # Check if there's a "CWO:" specified somewhere, e.g. in the
            # comment field
            if (preg_match('/CWO:([A-Z0-9\/]+)/', $q, $match)) {
                $call = $match[1];
            }

            # check if it's a member and then date vs. membership date
            if (array_key_exists($call, $mh)) {
                preg_match('/<QSO_DATE:\d+(:\w)?()>([0-9]+)/', $q, $match);
                if ($match[3] && $match[3] >= $mh[$call]['joined'] && $match[3] <= $mh[$call]['left']) {
                    $date = $match[3];

                    # we have a start date. if the QSO is before this date,
                    # ignore it.
                    if ($startdate && $date < $startdate) {
                        continue;
                    }

                    # Valid QSO. Now find out band and optionally state, wae,
                    # waz and dxcc.

                    # band?
                    if (preg_match('/<BAND:\d+(:\w)?()>([0-9]+)(C)?M/', $q, $match)) {
                        $band = $match[3];
                    }
                    # freq?
                    else if (preg_match('/<FREQ:\d+(:\w)?()>([0-9]+)/', $q, $match)) {
                        $band = f2b($match[3]);
                    }

                    $qso = array();
                    $qso['call'] = $qsocall;
                    $qso['date'] = $date;
                    $qso['band'] = $band;
                    $qso['nr'] = $mh[$call]['nr'];
                    $qso['was'] = $mh[$call]['was'];
                    $qso['waz'] = 0;
                    $qso['wae'] = '';
                    $qso['dxcc'] = 0;

                    if (!$ign) {
                        # see if there's a state in ADIF which overrides the state
                        # from the database
                        if (preg_match('/<STATE:\d+(:\w)?()>([A-Z]+)/', $q, $match)) {
                            $qso['was'] = $match[3];
                        }

                        # CQ zone
                        if (preg_match('/<CQZ:\d+(:\w)?()>([0-9]+)/', $q, $match)) {
                            $qso['waz'] = $match[3];
                        }

                        # DXCC
                        if (preg_match('/<DXCC:\d+(:\w)?()>([0-9]+)/', $q, $match)) {
                            $qso['dxcc'] = $match[3];
                        }
                    }

                    # WAE "Region" http://adif.org/310/ADIF_310.htm#Region_Enumeration
                    if (preg_match('/<REGION:\d+(:\w)?()>([A-Z]+)/', $q, $match)) {
                        $qso['wae'] = $match[3];
                    }
                    else {
                        if (substr($qso['call'], 0, 3) == "IT9") {
                            $qso['wae'] = 'SY';
                        }
                    }

                    # sanitize WAZ: Some logs may contain the ITU zone instead
                    # of the CQ zone in the CQZ field, or a completely invalid
                    # value.

                    $itu = lookup($qsocall, 'itu', $date);
                    if ($qso['waz'] == 0 or $qso['waz'] > 40 or $qso['waz'] == $itu) {
                        $qso['waz'] = lookup($qsocall, 'waz', $date);
                    }
                    # hamqth returns empty value for /mm
                    if ($qso['waz'] == "") { $qso['waz'] = 0; }

                    if ($qso['dxcc'] == 0) {
                        $qso['dxcc'] = lookup($qsocall, 'adif', $date);
                    }

                    # as per WAS rules, DC counts as Maryland
                    if ($qso['was'] == "DC") {
                        $qso['was'] = "MD";
                    }

                    # remove state for cases where it's not applicable, e.g.
                    # KP2/W1XYZ
                    #                   USA                   KL7                    KH6
                    if ($qso['dxcc'] != 291 && $qso['dxcc'] != 6 && $qso['dxcc'] != 110) {
                        $qso['was'] = "";
                    }

                    if ($qso['was'] == "--") {
                        $qso['was'] = "";
                    }

                    # finally, some hard-coded exceptions:
                    if ($qso['call'] == "K7SV") {
                        $qso['waz'] = 5;
                    }

                    array_push($out, $qso);

                }
            }
        }
    }

    return $out;
}

function f2b ($f) {
    $f *= 1000;
    if ($f < 2000) { return "160"; }
    elseif ($f < 4000) { return "80"; }
    elseif ($f < 5500) { return "60"; }
    elseif ($f < 7300) { return "40"; }
    elseif ($f < 10150) { return "30"; }
    elseif ($f < 14350) { return "20"; }
    elseif ($f < 18168) { return "17"; }
    elseif ($f < 21450) { return "15"; }
    elseif ($f < 24990) { return "12"; }
    elseif ($f < 29700) { return "10"; }
    elseif ($f < 54000) { return "6"; }
    elseif ($f < 75000) { return "4"; }
    elseif ($f < 148000) { return "2"; }
    elseif ($f < 440000) { return "0.7"; }

    return 0;
}

# look up calls on HamQTH's API
# Save all data in a local Redis Database to avoid flooding the API
# Also load exceptions from OK1RR's country file.
function lookup ($call, $what, $date) {
    global $call_exceptions;

    if (!$call) {
        return "";
    }

    # check if this callsign is in OK1RR's exception list

    if (isset($call_exceptions->$call)) {
        error_log("Exceptions for $call found (date: $date): ");

        $exc = $call_exceptions->$call;

        # check if the date range fits
        foreach ($exc as $e) {
            error_log($e->start." -> ".$e->stop);
            if ($date >= $e->start and $date <= $e->stop) {
                if ($what == 'json') {
                    return json_encode($e);
                }
                else {
                    return $e->$what;
                }
                error_log(json_encode($e));
            }
        }
    }

    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $data = $redis->get("HamQTH".$call);

    if (!$data) {
        error_log("Call: $call not found in Redis cache.");
        $data = file_get_contents("https://www.hamqth.com/dxcc_json.php?callsign=$call");
    }

    $data = preg_replace("/\"Artificial Aerial\"/", "", $data); # bug in HamQTH lookup interface, unescaped quotes

    $o = json_decode($data);

    if (!$o) {
        error_log("Could not parse $data for $call.");
        return "{}";
    }
    else {
        $redis->set("HamQTH".$call, $data);
        if ($what == 'json') {
            return $data;
        }
        return $o->$what;
    }

}

# in:   An array of QSOs (containing call, date, band, nr, state, waz, wae, dxcc)
# out:  An array of *new* QSOs (i.e. new ACA, CMA, WAS, WAE, DXCC or WAZ for
#       this member)
function filter_qsos ($qsos, $callsign) {

    $out = array();

    foreach ($qsos as $q) {

        $reason = array();
        if (new_aca($q, $callsign)) {
            array_push($reason, "ACA");
        }
        if (new_cma($q, $callsign)) {
            array_push($reason, "CMA");
        } 
        if (new_was($q, $callsign)) {
            array_push($reason, "WAS");
        }
        if (new_dxcc($q, $callsign)) {
            array_push($reason, "DXCC");
        }
        if (new_wae($q, $callsign)) {
            array_push($reason, "WAE");
        }
        if (new_waz($q, $callsign)) {
            array_push($reason, "WAZ");
        }

        # attach list of reasons why the QSO was added to the QSO record
        if (count($reason)) {
            $q['reasons'] = implode(", ", $reason);
            array_push($out, $q);
            insert_qso($q, $callsign);
        }
    }

    return $out;
}

# ACA: New QSO with this member in the year of the QSO?
function new_aca($qso, $c) {
    global $db;

    $qsoyear = substr($qso['date'], 0, 4);

    $query = "SELECT count(*) from cwops_log where mycall='$c' and nr=".$qso['nr']." and year=$qsoyear";
    $q = mysqli_query($db, $query);
    $r = mysqli_fetch_row($q);
    return ($r[0] == 0); 
}


# CMA: new all-time band point?
function new_cma($qso, $c) {
    global $db;
    $q = mysqli_query($db, "SELECT count(*) from cwops_log where mycall='$c' and nr=".$qso['nr']." and band=".$qso['band']);
    $r = mysqli_fetch_row($q);
    return ($r[0] == 0); 
}

# WAS: New state on this band?
function new_was($qso, $c) {
    global $db;

    if ($qso['was'] == "") {
        return false;
    }

    $q = mysqli_query($db, "SELECT count(*) from cwops_log where mycall='$c' and was='".$qso['was']."' and band=".$qso['band']);
    $r = mysqli_fetch_row($q);
    return ($r[0] == 0); 
}

# DXCC: New DXCC on this band?
function new_dxcc($qso, $c) {
    global $db;

    if ($qso['dxcc'] == 0) {
        return false;
    }

    $q = mysqli_query($db, "SELECT count(*) from cwops_log where mycall='$c' and dxcc=".$qso['dxcc']." and band=".$qso['band']);
    $r = mysqli_fetch_row($q);
    return ($r[0] == 0); 
}

# WAE: New WAE (any band)
function new_wae($qso, $c) {
    global $db;

    if ($qso['wae'] == "") {
        return false;
    }

    $q = mysqli_query($db, "SELECT count(*) from cwops_log where mycall='$c' and wae='".$qso['wae']."' and band=".$qso['band']);
    $r = mysqli_fetch_row($q);
    return ($r[0] == 0); 
}

# WAZ: New Zone on this band?
function new_waz($qso, $c) {
    global $db;

    if ($qso['waz'] == 0) {
        return false;
    }

    $q = mysqli_query($db, "SELECT count(*) from cwops_log where mycall='$c' and waz=".$qso['waz']." and band=".$qso['band']);
    $r = mysqli_fetch_row($q);
    return ($r[0] == 0); 
}


function insert_qso($qso, $c) {
    global $db;
    $query = "INSERT into cwops_log (`mycall`, `date`, `year`, `band`, `nr`, `hiscall`, `dxcc`, `wae`, `waz`, `was`) VALUES ".
        "('$c', '".$qso['date']."', '".substr($qso['date'], 0, 4)."', ".$qso['band'].", ".$qso['nr'].", '".$qso['call']."', ".$qso['dxcc'].",
            '".$qso['wae']."', ".$qso['waz'].", '".$qso['was']."');";
    $q = mysqli_query($db, $query);
    if (!$q) {
        error_log(mysqli_error($db));
        error_log("insert_qso error: ".$query);
    }
}

# retrieve all saved QSOs for $call
function get_log ($call) {
    global $db;
    $q = mysqli_query("select * from cwops_log where mycall='$call'");
    $out = array();
    while ($r = mysqli_fetch_array($q, MYSQLI_ASSOC)) {
        array_push($out, $r);
    }
    return $out;
}

function editformline($hiscallv, $nrv, $datev, $bandv, $dxccv, $wazv, $wasv, $waev, $edit) {
    global $dxcc;
    global $states;
    global $waes;

    if ($edit == "new") {
        $edit = 0;
        $new = 1;
    }
    else {
        $new = 0;
    }

?>
<tr>
<td>
<input type="text" name="hiscall<?=$edit;?>" id="hiscall<?=$edit;?>" value="<?=$hiscallv;?>" <?
if ($new) {
?>
onblur="javascript:dxcc_lookup(this.value);"
<?
}
?> size=10>
</td>
<td>
<input type="text" name="nr<?=$edit;?>" id="nr<?=$edit;?>" value="<?=$nrv;?>" size=4>
</td>
<td>
<input type="text" name="date<?=$edit;?>" id="date<?=$edit;?>" placeholder="YYYY-MM-DD" value="<?=$datev;?>" size=10>
</td>
<td>
<input type="text" name="band<?=$edit;?>" id="band<?=$edit;?>" value="<?=$bandv;?>" size=4>
</td>
<td>
<select name="dxcc<?=$edit;?>" id="dxcc<?=$edit;?>" size="1">
<?
    if ($dxccv == "") {
?>
    <option value='0'>any</option>
<?
    }
    foreach ($dxcc as $n => $d) {
        $selected = ($n == $dxccv) ? " selected" : "";
        echo "<option value='$n'$selected>".$d." ($n)</option>\n";
    }
?>
</select>
</td>
<td>
<select name="waz<?=$edit;?>" id="waz<?=$edit;?>" size="1">
<?
    if ($wazv == "") {
?>
    <option value='0'>any</option>
<?
    }
    for ($i = 1; $i <= 40; $i++) {
        $selected = ($i == $wazv) ? " selected" : "";
        echo "<option value='$i'$selected>".$i."</option>\n";
    }
?>
</select>
</td>
<td>
<select name="was<?=$edit;?>" id="was<?=$edit;?>" size="1">
<?
    if ($wasv == "") {
?>
    <option value='0'>any/none</option>
<?
    }
    foreach ($states as $n => $d) {
        $selected = ($d == $wasv) ? " selected" : "";
        echo "<option value='$d'$selected>".$d."</option>\n";
    }
?>
</select>
</td>
<td>
<select name="wae<?=$edit;?>" id="wae<?=$edit;?>" size="1">
<?
    if ($waev == "") {
?>
    <option value='0'>any/none</option>
<?
    }
    foreach ($waes as $n => $d) {
        $selected = ($n == $waev) ? " selected" : "";
        echo "<option value='$n'$selected>".$d." (".$n.")</option>\n";
    }
?>
</select>
</td>
<?
    if ($new or $edit) {
?>
    <td><button id="save<?=$edit;?>" onClick="javascript:save(<?=$edit;?>);">Save</button></td>
<?
    }

    if ($edit) {
?>
    <td><button id="del<?=$edit;?>" onClick="javascript:del(<?=$edit;?>);">Delete</button></td>
<?
    }
?>
</tr>
<?
}



function validate_get ($i) {
    if (array_key_exists($i, $_GET)) {
        $val = $_GET[$i];
    }
    else {
        $val = "";
    }
    return validate($i, $val);
}

function validate ($type, $value) {
    switch ($type) {
    case 'callsign':
    case 'hiscall':
    case 'mycall':
        $value = strtoupper($value);
        if (preg_match('/^[A-Z0-9\/]+$/', $value)) {
            return $value;
        }
        else {
            return "";
        }
        break;
    case 'type':
        $value = strtoupper($value);
        if (in_array($value, array("ACA", "CMA", "WAZ", "WAS", "WAE", "DXCC"))) {
            return $value;
        }
        else {
            return "";
        }
        break;
    case 'nr':
    case 'dxcc':
    case 'waz':
    case 'band':
    case 'id':
        if (is_numeric($value)) {
            return $value;
        }
        else {
            return 0;
        }
        break;
    case 'was':
    case 'wae':
        $value = strtoupper($value);
        if (preg_match('/^[A-Z]{2,2}$/', $value)){
            return $value;
        }
        else {
            return "";
        }
        break;
    case 'date':
        if (preg_match('/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}$/', $value)) {
            return $value;
        }
        else {
            return "";
        }
        break;
    default:
        return "";
    }
}

function score_table() {
    global $db;

    echo "<div class='container'>";
    # aca / cma combined table
    $q = mysqli_query($db, "select cwops_users.callsign as callsign, cwops_scores.aca as aca, cwops_scores.cma as cma from cwops_users inner join cwops_scores on cwops_users.id = cwops_scores.uid  order by aca desc, cma desc;");
    echo "<table><tr><th>Call</th><th>ACA</th><th>CMA</th></tr>\n";
    while ($r = mysqli_fetch_row($q)) {
        if ($r[0] != "TEST" and $r[2] > 0) {
            echo "<tr><td onmouseout=\"hlcall('$r[0]', 0);\" onmouseover=\"hlcall('$r[0]', 1);\" name=\"$r[0]\">$r[0]</td><td class='score'>$r[1]</td><td class='score'>$r[2]</td></tr>\n";
        }
    }
    echo "</table>";

    $items = array("dxcc", "was", "wae", "waz");

    foreach ($items as $i) {
        $q = mysqli_query($db, "select cwops_users.callsign as callsign, cwops_scores.$i as $i from cwops_users inner join cwops_scores on cwops_users.id = cwops_scores.uid  order by $i desc;");
        echo "<table><tr><th>Call</th><th>".strtoupper($i)."</th></tr>\n";
        while ($r = mysqli_fetch_row($q)) {
            if ($r[0] != "TEST" and $r[1] > 0) {
                echo "<tr><td onmouseout=\"hlcall('$r[0]', 0);\" onmouseover=\"hlcall('$r[0]', 1);\" name=\"$r[0]\">$r[0]</td><td class='score'>$r[1]</td></tr>\n";
            }
        }
        echo "</table>";
    }
    echo "</div>";

?>
<script>
    function hlcall(c, o) {
        var el = document.getElementsByName(c);
        for (var i = 0; i < el.length; i++) {
            if (o) {
                el[i].style.background = "LightGreen";
            }
            else {
                el[i].style.background = "White";
            }
        }
    }
</script>
<?
}

function score_table_by_call() {
    global $db;

    $q = mysqli_query($db, "select cwops_users.callsign as callsign, cwops_scores.aca as aca, cwops_scores.cma as cma, cwops_scores.dxcc as dxcc, cwops_scores.was as was, cwops_scores.wae as wae, cwops_scores.waz as waz, cwops_scores.updated as upd from cwops_users inner join cwops_scores on cwops_users.id = cwops_scores.uid  order by callsign;");
    echo "<table><tr><th>Call</th><th>ACA</th><th>CMA</th><th>DXCC</th><th>WAS</th><th>WAE</th><th>WAZ</th><th>Updated</th></tr>\n";
    while ($r = mysqli_fetch_row($q)) {
        if ($r[0] != "TEST") {
            echo "<tr>";
            echo "<td>".$r[0]."</td>";
            for ($i = 1; $i <= 7; $i++) {
                echo "<td class='score'>".$r[$i]."</td>";
            }
            echo "</tr>\n";
        }
    }
    echo "</table>";
}

function get_joindate($callsign) {
    global $db;

    $q = mysqli_query($db, "SELECT nr from cwops_members where callsign='$callsign'");
    $r = mysqli_fetch_row($q);
    if ($r[0]) {
        # find earliest entry in member list with this nr (may be a previous call)

        $q = mysqli_query($db, "SELECT min(joined) from cwops_members where nr=".$r[0]);
        $r = mysqli_fetch_row($q);
        if ($r[0]) {
            return $r[0];
        }
        else {
            return 0;
        }
    }
    else {
        return 0;
    }

}

function create_award ($callsign, $uid, $type, $score, $date) {
    global $db;

    error_log("create_award: $callsign, $uid, $type, $score, $date");

    # validated in api.php
    if ($type == "") {
        echo "Invalid type.";
    }

    $type = strtolower($type);

    # get CWops number
    $q = mysqli_query($db, "select nr from cwops_members where `callsign`='$callsign'");
    $nr = 0;
    if ($r = mysqli_fetch_row($q)) {
        $nr = $r[0];
    }

    $template = file_get_contents("pdf/cwops-$type.fdf");

    if ($type == "aca") {
        $fdf = sprintf($template, $callsign, date("Y")-1, $nr, $date, $score);
    }
    else {
        $fdf = sprintf($template, $callsign, $nr, $date, $score);
    }
    $filename = "/tmp/award-$uid-$type";
    file_put_contents("$filename.fdf", $fdf);
    system("pdftk pdf/cwops-$type.pdf fill_form $filename.fdf output $filename.pdf");
    return file_get_contents("$filename.pdf");
}

function is_call ($c) {
    $ret = false;

    if (strpos($c, '/') !== false) {
        $split = explode('/', $c);
        foreach ($split as $s) {
            $ret |= is_call($s);
        }
    }
    else {
        if (preg_match('/^[A-Z]+(\d+)[A-Z]+/',  $c)) { $ret = true; }
        if (preg_match('/^\d[A-Z]+(\d+)[A-Z]+/', $c)) { $ret = true; }
    }
    return $ret;
}

function site_stats() {
    global $db;
    $ret = "";
    $q = mysqli_query($db, "select * from cwops_members order by nr desc limit 1");
    $member = mysqli_fetch_object($q);
    $ret .= "Newest member in database: $member->callsign (#$member->nr, $member->joined) - ";
    $q = mysqli_query($db, "select count(*) as c from cwops_users");
    $cnt = mysqli_fetch_object($q);
    $ret .= "Number of accounts: $cnt->c - ";
    $q = mysqli_query($db, "select count(*) as c from cwops_log");
    $cnt = mysqli_fetch_object($q);
    $ret .= "Number of QSOs: $cnt->c";

    return $ret;
}

?>
