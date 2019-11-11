<?
    session_start();

    if (!array_key_exists('id', $_SESSION)) {
        echo "Not logged in. Please refresh your login.";
        return;
    }

    include("functions.php");

    $bands = array("160", "80", "60", "40", "30", "20", "17", "15", "12", "10", "6", "2", "all");

    switch ($_GET['action']) {
    case 'stats':
        echo stats($_SESSION['callsign']);
        break;
    case 'aca':
        $year = $_GET['year'];
        if ($year >= 2010 && $year <= date("Y")) {     
            echo aca($_SESSION['callsign'], $year);
        }
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
    case 'wae':
        $band = $_GET['band'];
        if (in_array($band, $bands)) {     
            echo wae($_SESSION['callsign'], $band);
        }
        break;
    case 'lookup':
        $call = validate_get('hiscall');
        $date = validate_get('date');
        if ($date) {
            $date = preg_replace('/\-/', '', $date);
        }
        else {
            $date = date("Ymd");
        }
        echo lookup($call, 'json', $date);
        break;
    case 'overview':
        echo stats($_SESSION['callsign']);
        break;
    case 'upload':
        if ($_GET['ign'] == "1") {
            $ign = 1;
        }
        else {
            $ign = 0;
        }
        upload($ign);
        break;
    case 'wipe':
        wipe();
        break;
    case 'upload_history':
        upload_history();
        break;
    case 'upload_details':
        upload_details();
        break;
    case 'search':
        search();
        break;
    case 'save':
        save();
        break;
    case 'del':
        $id = validate_get('nr');
        del($id);
        break;
    case 'update_account':
        update_account();
        break;
    case 'award_pdf':
        award_pdf();
        break;
    }

    function upload($ign) {
        $ret = "";
        foreach ($_FILES["uploaded_files"]["error"] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $filename_original = $_FILES['uploaded_files']['name'][$key];
                $filename_local    = "/tmp/".md5(time() . $filename_original . rand(1,999));

                error_log("Upload  $filename_original to  $filename_local");
                if (move_uploaded_file($_FILES['uploaded_files']['tmp_name'][$key], $filename_local)) {
                    error_log("move ok. ign = $ign");
                    $ret .= import($filename_original, file_get_contents($filename_local), $_SESSION['callsign'], $ign);
                }
            }
        }
        echo $ret;
    }

    function del ($nr) {
        global $db;
        $q = mysqli_query($db, "DELETE from cwops_log where mycall='".$_SESSION['callsign']."' and id=$nr");
        if ($q) {
            echo "OK";
        }
        else {
            echo "An error occured. Maybe the QSO was deleted in the meantime or you tried to delete a QSO from a different user.";
            error_log("del: ".mysqli_error($q));
        }
    }

    function save() {
        global $db;

        $postdata = file_get_contents("php://input");

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

                # strip "m" from band
                if ($i == "band" && preg_match('/(\d+)/', $o->$i, $match)) {
                    $o->$i = $match[1];
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
                    echo "Saved QSO: ".$qso_filtered[0]['call']." ".$qso_filtered[0]['date']." ".$qso_filtered[0]['band']." needed for: ".$qso_filtered[0]['reasons']."\n";
                }
                else {
                    echo "QSO not added (not a new point for any award)!";
                } 
            }
        }
        else {
            echo "invalid data" + $postdata;
        }
    }

    function wipe () {
        global $db;
        $q = mysqli_query($db, "delete from cwops_log where mycall='".$_SESSION['callsign']."'");
        if ($q) {
            echo "OK - all QSOs deleted.";
        }
        else {
            echo "An error occured. Please try again.";
            error_log(mysqli_error($db));
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
        $q = mysqli_query($db, $query);

        $count = 0;

        echo "<h2>Search results</h2><table><tr><th>Callsign</th><th>CWops #</th><th>Date (YYYY-MM-DD)</th><th>Band</th><th>DXCC</th><th>WAZ</th><th>WAS</th><th>WAE</th><th>Submit</th><th>Delete</th></tr>\n";
        while ($r = mysqli_fetch_array($q, MYSQLI_ASSOC)) {
            $count++;

            editformline($r['hiscall'], $r['nr'], $r['date'], $r['band'], $r['dxcc'], $r['waz'], $r['was'], $r['wae'], $r['id']);

            if ($count > 100) {
                echo "Stopped after 100 results. Use finer search query please.<br>";
                break;
            }


        }
    }


    function upload_history () {
        global $db;

        $ret = "<h2>Upload history</h2>";

        $q = mysqli_query($db, "select * from cwops_uploads where uid=".$_SESSION['id']." order by ts desc limit 25");

        $ret .= "<pre>";
        while ($r = mysqli_fetch_array($q)) {
            $ret .= "Upload date: ".$r['ts'].", Imported QSOs: ".sprintf("%4d", $r['count']).". <a target='_new' href='/api?action=upload_details&id=".$r['id']."'>Show details</a> (opens in new window)<br>";
        }
        $ret .= "</pre>";

        echo $ret;
    }

    function upload_details () {
        global $db;

        if (is_numeric($_GET['id'])) {
            $id = $_GET['id'];
        }
        else {
            echo "Invalid ID";
            return;
        }

        $ret = "<pre>";
        $q = mysqli_query($db, "select * from cwops_uploads where uid=".$_SESSION['id']." and id=$id");

        while ($r = mysqli_fetch_array($q)) {
            $ret .= "Upload date: ".$r['ts'].", Imported QSOs: ".$r['count']."<br>".$r['result'];
        }
        $ret .= "</pre>";

        echo $ret;
    }


    function update_account () {
        global $db;

        $postdata = file_get_contents("php://input");

        $o = json_decode($postdata);

        if (!$o) {
            echo "Invalid data.";
            return;
        }

        switch ($o->item) {
        case 'password':
            if (strlen($o->value)) {
                $value = password_hash($o->value, PASSWORD_DEFAULT);
            }
            else {
                echo "Password must not be empty.";
                return;
            }
            break;
        case 'email':
            $value = mysqli_real_escape_string($db, $o->value);
            $_SESSION['email'] = $value;
            break;
        default:
            echo "Invalid data.";
            return;
            break;
        }

        $q = mysqli_query($db, "update cwops_users set ".$o->item." = '".$value."' where id=".$_SESSION['id']);
        if ($q) {
            echo "Updated.";
        }
        else {
            echo "Data base error. Contact administrator if this persists.";
            error_log(mysqli_error($db));
        }
    }

    function award_pdf () {
        $type = validate_get('type');
        $callsign = $_SESSION['callsign'];

        if ($type && $callsign) {

            # query score...
            global $db;
            $q = mysqli_query($db, "select $type from cwops_scores where uid=".$_SESSION['id']);
            if ($r = mysqli_fetch_row($q)) {
                $score = $r[0];
            }


            header("Content-type: application/pdf");
            header("Content-Disposition: attachment; filename=\"$callsign-$type.pdf\"");
            echo create_award ($callsign, $_SESSION['id'], $type, $score, date("d-M-Y"));
        }
        else {
            echo "wrong type or callsign";
        }
    }




?>
