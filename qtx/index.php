<?php
    include_once("../db.php");

    $q = "select cwops_users.callsign as callsign, cwops_scores.qtx as qtx, cwops_scores.mqtx as mqtx, cwops_scores.cmqtx as cmqtx, cwops_scores.cmmqtx as cmmqtx, cwops_scores.ltqtx as ltqtx, cwops_scores.ltmqtx as ltmqtx, cwops_scores.updated as upd from cwops_users inner join cwops_scores on cwops_users.id = cwops_scores.uid  order by callsign;";

    echo "callsign;qtx;mqtx;cmqtx;cmmqtx;ltqtx;ltmqtx;updated\n";
    $mq = mysqli_query($db, $q);
    while ($r = mysqli_fetch_row($mq)) {
        $sum = 0;
        for ($i = 1; $i < 6; $i++) {
            $sum += $r[$i];
        }
        if ($sum)
            echo join(";",$r)."\n";
    }
