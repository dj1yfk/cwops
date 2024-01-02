<?

include("../functions.php");

$call    = strtoupper($_GET['c']);
if (!preg_match('/^[a-z0-9]+$/i', $call)) {
    echo "Invalid call.";
    exit;
}
$score   = $_GET['s']+0;

if (!is_int($score)) {
    echo "Invalid score.";
    exit;
}

$year    = 2023;
$cont = lookup($call, 'continent', '2023-01-01');

if ($cont == "NA" or $cont == "EU") {
    $gold = 120;
    $silv = 80;
    $bron = 50;
}
else {
    $gold = 90;
    $silv = 40;
    $bron = 24;
}

if ($score >= $gold) {
    $type = 'gold';
    $tex = file_get_contents("template-gold.tex");
} 
else if ($score >= $silv) {
    $type = 'silver';
    $tex = file_get_contents("template.tex");
}
else if ($score >= $bron) {
    $type = 'bronze';
    $tex = file_get_contents("template.tex");
}
else {
    $type = 'entry-level';
    $tex = file_get_contents("template-entry.tex");
}


$tex = preg_replace("/CALL/", $call, $tex);
$tex = preg_replace("/SCORE/", $score, $tex);
$tex = preg_replace("/YEAR/", $year, $tex);



mkdir("/tmp/cwt-cert");
system("cp /home/fabian/sites/cwops.telegraphy.de/pdf/cwt/CWOPs_".$type."_text.pdf /tmp/cwt-cert/template_latex.pdf");
chdir("/tmp/cwt-cert");
#echo $tex;
#return;
file_put_contents("/tmp/cwt-cert/$call.tex", $tex);
system("pdflatex /tmp/cwt-cert/$call.tex > /dev/null", $ret);
$out = file_get_contents("/tmp/cwt-cert/$call.pdf");
header("Content-Type: application/pdf");
echo $out;
system("rm -f /tmp/cwt-cert/$call.pdf");

?>

