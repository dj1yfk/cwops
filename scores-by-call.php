<!DOCTYPE html>
<head>
<title>CWops Award Scores (ACA, CMA, DXCC, WAS, WAE, WAZ)</title>
<link rel="stylesheet" type="text/css" href="/style.css">
</head>
<h2>Sortable and searchable table</h2>
<a href="/">Back</a> - <a href="/scores">Score overview</a><br><br>


<!-- div style="float:right;border:1px solid">
<canvas id="c" width="800" height="600">
</canvas>
</div -->

<?php
session_start();
include("functions.php");
echo score_table_by_call();
?>


<script>
function plot() {
    console.log("plot...");
    var request =  new XMLHttpRequest();
    request.open("GET", "/api.php?action=plot&type=ACA&year=2021&calls=AA3B,KR2Q,K3WW,N5RZ,N5AW,K3WJV,NA8V,W1RM,K3JT,K7QA,K1VUT,K4WW,KG9X,N7US,VE3KI,WT9U,K3PP,WT3K,W0UO,W9ILY", true);
    request.onreadystatechange = function() {
        var done = 4, ok = 200;
        if (request.readyState == done && request.status == ok) {
            var data = JSON.parse(request.responseText);

            var c = document.getElementById('c');
            var ctx = c.getContext("2d");
            var w = c.width;
            var h = c.height;

            // find max value for scaling
            var max = 0;
            for (call in data) {
                data[call].forEach(d => { d = parseInt(d); console.log(d); if (d > max) { max = d; }  });
            }

            // set top value to next full 500
            var max = max - max % 500 + 500;

            // horizontal: keep 100 pixels at the end for callsigns
            var hw = w - 50;

            console.log(data);

            for (call in data) {
                console.log(call);
                ctx.beginPath();
                var xpos = 0;
                var ypos = h;
                ctx.moveTo(xpos,ypos);
                for (var i = 0; i < data[call].length; i++) {
                    xpos = i * (hw / 52);
                    ypos = h - h/max * data[call][i];
                    ctx.lineTo(xpos, ypos);
                }
                ctx.strokeStyle = "red";
                ctx.fillStyle = "red";
                ctx.stroke();
                ctx.fillText(call, xpos + 5, ypos + 3);
            }

        }
    }
    request.send();
}



</script>



<br>
<a href="/">Back</a>
