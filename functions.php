<?php
include_once("db.php");

function stats($c) {
    global $db;
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
# 2. Parse ADI into an array, omitting all calls that are not members (considering the date of the QSO) and non-CW-QSOs
# 3. Iterate through imported log and based on (1) decide which QSOs are saved in the database.

function import($adif, $callsign) {
    $members = get_memberlist();
    $qsos = parse_adif($adif, $members);

    print_r($qsos);

    echo "HHHHHHHHHHHHHHHHHHHHHHHHH\n";

    $qsos = filter_qsos($qsos, $callsign);

    print_r($qsos);


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


# parse ADIF and return member QSOs (matched by date) 
function parse_adif($adif, $members) {

    $out = array();

    # make hash table for quicker member lookup
    $mh = array();
    foreach ($members as $m) {
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
            $call = $qsocall;      # TODO

            # Check if there's a "CWO:" specified somewhere, e.g. in the
            # comment field
            if (preg_match('/CWO:([A-Z0-9]+)/', $q, $match)) {
                $call = $match[1];
            }

            # check if it's a member and then date vs. membership date
            if (array_key_exists($call, $mh)) {
                preg_match('/<QSO_DATE:\d+(:\w)?()>([0-9]+)/', $q, $match);
                if ($match[3] && $match[3] >= $mh[$call]['joined'] && $match[3] <= $mh[$call]['left']) {
                    $date = $match[3];

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

                    # see if there's a state in ADIF which overrides the state
                    # from the database
                    if (preg_match('/<STATE:\d+(:\w)?()>([A-Z]+)/', $q, $match)) {
                        $qso['was'] = $match[3];
                    }

                    # CQ zone
                    if (preg_match('/<CQZ:\d+(:\w)?()>([0-9]+)/', $q, $match)) {
                        $qso['waz'] = $match[3];
                    }
                    else {
                        $qso['waz'] = 0;
                    }

                    # WAE "Region" http://adif.org/310/ADIF_310.htm#Region_Enumeration
                    if (preg_match('/<REGION:\d+(:\w)?()>([A-Z]+)/', $q, $match)) {
                        $qso['wae'] = $match[3];
                    }
                    else {
                        $qso['wae'] = '';
                    }

                    # DXCC
                    if (preg_match('/<DXCC:\d+(:\w)?()>([0-9]+)/', $q, $match)) {
                        $qso['dxcc'] = $match[3];
                    }
                    else {
                        $qso['dxcc'] = 0;
                    }

                    if ($qso['waz'] == 0) {
                        $qso['waz'] = lookup($qsocall, 'waz');
                    }
                    
                    if ($qso['dxcc'] == 0) {
                        $qso['dxcc'] = lookup($qsocall, 'adif');
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
}

# look up calls on HamQTH's API
# Save all data in a local Redis Database to avoid flooding the API
function lookup ($call, $what) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $data = $redis->get("HamQTH".$call);

    if (!$data) {
        error_log("Call: $call not found in Redis cache.");
        $data = file_get_contents("https://www.hamqth.com/dxcc_json.php?callsign=$call");
    }

    $o = json_decode($data);

    if (!$o) {
        error_log("Could not parse $data for $call.");
        return "";
    }
    else {
        $redis->set("HamQTH".$call, $data);
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

# ACA: New QSO with this member this year?
function new_aca($qso, $c) {
    global $db;
    $query = "SELECT count(*) from cwops_log where mycall='$c' and nr=".$qso['nr']." and year=YEAR(CURDATE())";
    error_log($query);
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
    $q = mysqli_query($db, "SELECT count(*) from cwops_log where mycall='$c' and waz=".$qso['waz']." and band=".$qso['band']);
    $r = mysqli_fetch_row($q);
    return ($r[0] == 0); 
}


function insert_qso($qso, $c) {
    global $db;
    $q = mysqli_query($db, "INSERT into cwops_log (`mycall`, `date`, `year`, `band`, `nr`, `hiscall`, `dxcc`, `wae`, `waz`, `was`) VALUES ".
        "('$c', '".$qso['date']."', '".substr($qso['date'], 0, 4)."', ".$qso['band'].", ".$qso['nr'].", '".$qso['call']."', ".$qso['dxcc'].",
            '".$qso['wae']."', ".$qso['waz'].", '".$qso['was']."');");
    if (!$q) {
        error_log(mysqli_error($db));
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





import(file_get_contents("dj1yfk.adi"), "y");

$time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
echo "time: $time\n";


?>
