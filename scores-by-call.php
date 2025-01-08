<!DOCTYPE html>
<head>
<title>CWops Award Scores (ACA, ACMA, CMA, DXCC, WAS, WAE, WAZ)</title>
<link rel="stylesheet" type="text/css" href="/style.css">
</head>
<h2>Sortable and searchable table</h2>
<a href="/">Back</a> - <a href="/scores">Score overview</a><br><br>



<?php
session_start();
include("functions.php");
?>

<?
echo score_table_by_call();
?>


<div style="position:fixed;top:50px;left:550px">
<h2>ACA Graph</h2>

<canvas id="c" width="800" height="600"></canvas>

<p>The graph shows the ACA score of the selected station(s) on each week's Tuesday (i.e. before the CWTs) of the current year, and the latest/current score.</p>

</div>

<script>

var plot_calls = ["<? if ($_SESSION['callsign']) { echo $_SESSION['callsign']; } else { echo "DJ5CW"; }?>"];

var plot_colors = {};

function rc (c) {

    if (plot_colors[c]) {
        // nothing :-)
    }
    else {
        var a = '0123456789';
        var ret = '#';
        for (var i = 0; i < 6; i++) {
            ret += a[Math.floor(Math.random() * a.length)];
        }
        plot_colors[c] = ret;
    }
    return plot_colors[c];
}

// called when a checkbox changes
function plot_update(c, e) {
    console.log("plot_update: " + c + " " + e);
    console.log(plot_calls);
    var j = plot_calls.indexOf(c);
    if (e == false) {
        if (j >= 0) {
            plot_calls.splice(j, 1);
        }
    }
    else {
        if (j == -1) {
            plot_calls.push(c);
        }
    }
    console.log(plot_calls);
    plot();
}


function plot() {
    console.log("plot...");
    var request =  new XMLHttpRequest();
    var pc = plot_calls.join(",");
    request.open("GET", "/api.php?action=plot&type=ACA&year=2025&calls="+pc, true);
    request.onreadystatechange = function() {
        var done = 4, ok = 200;
        if (request.readyState == done && request.status == ok) {
            var data = JSON.parse(request.responseText);

            var c = document.getElementById('c');
            var ctx = c.getContext("2d");
            var w = c.width;
            var h = c.height;

            ctx.clearRect(0,0, w, h);
            ctx.fillStyle = '#efefef';
            ctx.fillRect(0,0, w, h);

            // find max value for scaling
            var max = 0;
            for (call in data) {
                data[call].forEach(d => { d = parseInt(d); if (d > max) { max = d; }  });
            }

            // set top value to next full 500
            var max = max - max % 500 + 500;
            // horizontal: keep 100 pixels at the end for callsigns
            var hw = w - 50;

            // draw a horizontal line for each 500
            ctx.strokeStyle = '#999999';
            for (var i = 500; i < max; i += 500) {
                ctx.fillStyle = 'black';
                ctx.fillText(i, 0, h - h/max * i + 4);
                ctx.beginPath();
                ctx.moveTo(30, h - h/max * i);
                ctx.lineTo(hw, h - h/max * i);
                ctx.stroke();
            }

            for (call in data) {
                ctx.beginPath();
                var xpos = 0;
                var ypos = h;
                ctx.moveTo(xpos,ypos);
                for (var i = 0; i < data[call].length; i++) {
                    xpos = i * (hw / 52);
                    ypos = h - h/max * data[call][i];
                    ctx.lineTo(xpos, ypos);
                    ctx.arc(xpos, ypos, 1, 0, Math.PI * 2, true); 
                    ctx.lineTo(xpos, ypos);
                }
                var col = rc(call);
                ctx.strokeStyle = col;
                ctx.stroke();
                ctx.fillStyle = col;
                ctx.fillText(call, xpos + 5, ypos + 3);
                ctx.stroke();
            }

        }
    }
    request.send();
}


plot();

</script>



<br>
<a href="/">Back</a>
