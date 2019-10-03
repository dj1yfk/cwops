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

    echo count($qsos);

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
        preg_match('/<CALL:\d+(:\w)?()>([A-Z0-9\/]+)/', $q, $match);
        if ($match[3]) {
            $call = $match[3];

            # check if QSO is CW mode
            if (!preg_match('/<MODE:2(:\w)?()>CW/', $q)) {
                continue;
            }

            # check date vs. membership date
            if ($mh[$call]) {
                preg_match('/<QSO_DATE:\d+(:\w)?()>([0-9]+)/', $q, $match);
                if ($match[3] && $match[3] >= $mh[$call]['joined'] && $match[3] <= $mh[$call]['left']) {
                    $date = $match[3];

                    # Valid QSO. Now find out band and optionally state, wae,
                    # zone and dxcc.

                    # band?
                    if (preg_match('/<BAND:\d+(:\w)?()>([0-9]+(C)?M)/', $q, $match)) {
                        $band = $match[3];
                    }
                    # freq?
                    else if (preg_match('/<FREQ:\d+(:\w)?()>([0-9]+)/', $q, $match)) {
                        $band = f2b($match[3]);
                    }

                    $qso = array();
                    $qso['call'] = $call;
                    $qso['date'] = $date;
                    $qso['band'] = $band;
                    $qso['state'] = "XX";
                    $qso['wae'] = "XX";
                    $qso['zone'] = 0;
                    $qso['dxcc'] = 0;

                    array_push($out, $qso);

                }
            }
        }
    }

    return $out;
}

function f2b ($f) {
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



import(file_get_contents("dj1yfk.adi"), "y");

$time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
echo "time: $time\n";


?>
