<?
    session_start();

    if (!$_SESSION['id']) {
        return;
    }

    include("functions.php");

    $bands = array("160", "80", "60", "40", "30", "20", "17", "15", "12", "10", "6", "2", "all");

    switch ($_GET['action']) {
    case 'aca':
        echo aca($_SESSION['callsign']);
        break;
    case 'cma':
        echo cma($_SESSION['callsign']);
        break;
    case 'was':
        $band = $_GET['band'];
        if (in_array($band, $bands)) {     
            echo was($_SESSION['callsign'], $band);
        }
        break;
    case 'waz':
        $band = $_GET['band'];
        if (in_array($band, $bands)) {     
            echo waz($_SESSION['callsign'], $band);
        }
        break;
    case 'dxcc':
        $band = $_GET['band'];
        if (in_array($band, $bands)) {     
            echo dxcc($_SESSION['callsign'], $band);
        }
        break;
    case 'overview':
        echo stats($_SESSION['callsign']);
        break;
    case 'upload':
        upload();
        break;
    case 'search':
        search();
        break;
    }

    function upload() {
        if (isset($_FILES['uploaded_file'])) {
            $filename_original = $_FILES['uploaded_file']['name'];
            $filename_local    = "/tmp/".md5(time() . $filename_original . rand(1,999));
            error_log("Upload  $filename_original to  $filename_local");
            
            error_log(print_r($_FILES, TRUE));
            if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $filename_local)) {
                error_log("move ok");
                echo import(file_get_contents($filename_local), $_SESSION['callsign']);
            }
        }
    }


    function search () {
        global $db;
        $callsign = validate_get('callsign');
        $nr = validate_get('nr');
        $date = validate_get('date');
        $band = validate_get('band');
        $dxcc = validate_get('dxcc');
        $waz = validate_get('waz');
        $was = validate_get('was');
        $wae = validate_get('wae');

        $query = "select * from cwops_log where mycall='".$_SESSION['callsign']."' and ";

        $conditions = array();

        if ($callsign) {
            array_push($conditions, " hiscall like '%$callsign%' ");
        }

        if ($nr) {
            array_push($conditions, " nr = $nr ");
        }

        if ($date) {
            array_push($conditions, " date = '$date' ");
        }

        if ($band) {
            array_push($conditions, " band = $band ");
        }

        if ($dxcc) {
            array_push($conditions, " dxcc= $dxcc ");
        }

        if ($waz) {
            array_push($conditions, " waz= $waz ");
        }

        if ($was) {
            array_push($conditions, " was='$was'");
        }

        if ($wae) {
            array_push($conditions, " wae='$wae'");
        }

        if (!count($conditions)) {
            echo "Invalid search parameters!";
            return;
        }

        $query .= implode(" and ", $conditions);
        error_log($query);
        $q = mysqli_query($db, $query);

        $count = 0;

        echo "<h2>Search results</h2><table><tr><th>Callsign</th><th>CWops #</th><th>Date (YYYY-MM-DD)</th><th>Band</th><th>DXCC</th><th>WAZ</th><th>WAS</th><th>WAE</th><th>Submit</th></tr>\n";
        while ($r = mysqli_fetch_array($q, MYSQLI_ASSOC)) {
            $count++;

            editformline($r['hiscall'], $r['nr'], $r['date'], $r['band'], $r['dxcc'], $r['waz'], $r['was'], $r['wae'], $r['id']);

            if ($count > 100) {
                echo "Stopped after 100 results. Use finer search query please.<br>";
                break;
            }


        }

    }

?>
