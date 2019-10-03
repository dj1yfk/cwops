<?
    session_start();

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
    }

?>
