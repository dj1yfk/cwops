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
    }



    function upload() {
        if (isset($_FILES['uploaded_file'])) {
            $filename_original = $_FILES['uploaded_file']['name'];
            $filename_local    = "/tmp/".md5(time() . $filename_original . rand(1,999));
            error_log("Upload  $filename_original to  $filename_local");
            
            if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $filename_local)) {
                error_log("move ok");
                echo import(file_get_contents($filename_local), $_SESSION['callsign']);
            }

        }


    }







?>
