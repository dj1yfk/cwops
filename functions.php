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


# parse ADIF but only QSOs which are included in $members
function parse_adif($adif, $members) {
    # make hash table for quicker member lookup
    $mh = array();
    foreach ($members as $m) {
        $mh[$m["callsign"]] = $m["nr"];
    }

    







}


import("x", "y");



?>
