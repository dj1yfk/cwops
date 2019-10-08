<?
    session_start();

    if (!$_SESSION['id']) {
        return;
    }

    include("functions.php");

    $bands = array("160", "80", "60", "40", "30", "20", "17", "15", "12", "10", "6", "2", "all");

    switch ($_GET['action']) {
    case 'stats':
        echo stats($_SESSION['callsign']);
        break;
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
    case 'lookup':
        $call = validate_get('hiscall');
        echo lookup($call, 'json');
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
    case 'save':
        save();
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

    function save() {
        global $db;

        $postdata = file_get_contents("php://input");

        error_log($postdata);

        if ($o = json_decode($postdata)) {
            # check validity

            $items = array("id", "hiscall", "nr", "date", "band", "dxcc", "waz", "was", "wae");

            if ($o->was == "0") {
                $o->was = "";
            }
            if ($o->wae == "0") {
                $o->wae = "";
            }

            $err = "";
            foreach ($items as $i) {

                if (($i == "was" or $i == "wae") && $o->$i == "") {
                    continue;
                }

                if ($i == "id" && $o->$i == 0) {
                    continue;
                }

                if (!validate($i, $o->$i)) {
                    $err .= "$i (".$o->$i.") ";
                } 
            }

            if ($err) {
                echo "Invalid data: ".$err;
                return;
            }

            $o->hiscall = strtoupper($o->hiscall);

            # EDIT an existing QSO
            if ($o->id) {
                $q = mysqli_query($db, "select * from cwops_log where `id`=".$o->id." and mycall='".$_SESSION['callsign']."'");
                if ($orig = mysqli_fetch_object($q)) {

                    # check what changed
                    $changes = array();
                    $sql = array();
                    foreach ($items as $i) {
                        if ($o->$i != $orig->$i) {
                            array_push($changes, "$i (".$orig->$i." --> ".$o->$i.")");
                            array_push($sql, "$i = '".$o->$i."'");

                        }
                    }

                    if (count($changes)) {
                        echo "Changes: ".implode(', ', $changes)."\n\n";
                        $query = "update cwops_log set ".implode(', ', $sql)." where id=".$o->id;
                        $q = mysqli_query($db, $query);

                        if ($q) {
                            echo "Done";
                        }
                        else {
                            echo "Database error";
                        }


                    }
                    else {
                        echo "Nothing changed.";
                    }

                }
                else {
                    echo "You tried to edit a QSO that is not in the database...";
                    return;
                }
            }
            # Log a new QSO
            else {
                $qsos = array();
                array_push($qsos, array('call' => $o->hiscall, 'nr' => $o->nr, 'date' => $o->date, 'band' => $o->band, 'dxcc' => $o->dxcc, 'was' => $o->was, 'waz' => $o->waz, 'wae' => $o->wae));
                $qso_filtered =  filter_qsos($qsos, $_SESSION['callsign']);
                if (count($qso_filtered)) {
                    echo "QSO: ".$qso_filtered[0]['call']." ".$qso_filtered[0]['date']." ".$qso_filtered[0]['band']." needed for: ".$qso_filtered[0]['reasons']."<br>";
                }
                else {
                    echo "QSO not added (not a new point for any award).";
                } 
            }
        }
        else {
            echo "invalid data" + $postdata;
        }
    }



    function search () {
        global $db;
        $hiscall = validate_get('hiscall');
        $nr = validate_get('nr');
        $date = validate_get('date');
        $band = validate_get('band');
        $dxcc = validate_get('dxcc');
        $waz = validate_get('waz');
        $was = validate_get('was');
        $wae = validate_get('wae');

        $query = "select * from cwops_log where mycall='".$_SESSION['callsign']."' and ";

        $conditions = array();

        if ($hiscall) {
            array_push($conditions, " hiscall like '%$hiscall%' ");
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
