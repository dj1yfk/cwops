<?
#    error_reporting(0);
    session_start();

    $access = false;

    // API functions that are allowed without login etc.
    if ($_GET['action'] == 'plot') {
        $access = true;
    }

    // API functions called by RBN Club Spotter
    if ($_GET['action'] == 'export_rbn' && $_SERVER['REMOTE_ADDR'] == "88.99.84.60") {
        $access = true;
    }

    // Existing session allows access
    if (array_key_exists('id', $_SESSION)) {
        $access = true;
    }

    if (!$access) {
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
    case 'acma':
        $year = $_GET['year'];
        if ($year >= 2010 && $year <= date("Y")) {     
            echo acma($_SESSION['callsign'], $year);
        }
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
    case 'qtx':
        echo qtx($_SESSION['callsign']);
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
    case 'member_lookup':
        $call = validate_get('hiscall');
        echo member_lookup($call);
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
    case 'award_pdf_old':
        award_pdf_old();
        break;
    case 'award_pdf':
        award_pdf();
        break;
    case 'list':
        member_list();
        break;
    case 'update_manual_score':
        update_manual_score();
        break;
    case 'plot':
        plot();
        break;
    case 'export_rbn':
        echo export_rbn($_GET['c']);
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
        # remove plot cache 
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->del("plotACA".$_SESSION['callsign']);

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

            $items = array("id", "hiscall", "nr", "date", "band", "dxcc", "waz", "was", "wae", "qsolength");

            if ($o->was == "0") {
                $o->was = "";
            }
            if ($o->wae == "0") {
                $o->wae = "";
            }
            if ($o->qsolength == "") {
                $o->qsolength = 1;
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
                    if (!($i == "nr" and $o->nr == 0 and $o->qsolength > 9)) {
                        $err .= "$i (".$o->$i.") ";
                    }
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
                array_push($qsos, array('call' => $o->hiscall, 'nr' => $o->nr, 'date' => $o->date, 'qsolength' => $o->qsolength, 'band' => $o->band, 'dxcc' => $o->dxcc, 'was' => $o->was, 'waz' => $o->waz, 'wae' => $o->wae));
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
        $qsolength = validate_get('qsolength');

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

        if ($qsolength) {
            array_push($conditions, " qsolength='$qsolength'");
        }
        if (!count($conditions)) {
            echo "Invalid search parameters!";
            return;
        }

        $query .= implode(" and ", $conditions);
        $q = mysqli_query($db, $query);

        $count = 0;

        echo "<h2>Search results</h2><table><tr><th>Callsign</th><th>CWops #</th><th>Date (YYYY-MM-DD)</th><th>Band</th><th>DXCC</th><th>WAZ</th><th>WAS</th><th>WAE</th><th>Length (min)</th><th>Submit</th><th>Delete</th></tr>\n";
        while ($r = mysqli_fetch_array($q, MYSQLI_ASSOC)) {
            $count++;

            editformline($r['hiscall'], $r['nr'], $r['date'], $r['band'], $r['dxcc'], $r['waz'], $r['was'], $r['wae'], $r['qsolength'], $r['id']);

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
            $value = "'".$value."'";
            break;
        case 'email':
            $value = mysqli_real_escape_string($db, $o->value);
            $_SESSION['email'] = $value;
            $value = "'".$value."'";
            break;
        case 'manual':
            error_log($o->value);
            $value = $o->value ? 1 : 0;
            $_SESSION['manual'] = $value;
            break;
        default:
            echo "Invalid data.";
            return;
            break;
        }

        $query = "update cwops_users set ".$o->item." = ".$value." where id=".$_SESSION['id'];
        $q = mysqli_query($db, $query);
        if ($q) {
            echo "Updated.";
        }
        else {
            echo "Data base error. Contact administrator if this persists.";
            error_log(mysqli_error($db));
        }
    }

    function update_manual_score () {
        global $db;

        $postdata = file_get_contents("php://input");

        $o = json_decode($postdata);

        if (! (in_array($o->item, array("aca", "acma", "cma", "was", "dxcc", "wae", "waz")) and is_int(0+$o->value))) {
            echo "Invalid data.";
            return;
        }

        $query = "update cwops_scores set ".$o->item." = ".$o->value." where uid=".$_SESSION['id'];
        error_log($query);
        $q = mysqli_query($db, $query);
        if ($q) {
            echo "Updated.";
        }
        else {
            echo "Data base error. Contact administrator if this persists.";
            error_log(mysqli_error($db));
        }
    }


    function award_pdf_old () {
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
            echo create_award_old ($callsign, $_SESSION['id'], $type, $score, date("d-M-Y"));
        }
        else {
            echo "wrong type or callsign";
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



    function member_list () {
        global $db;
        header("Content-type: text/csv");
        $q = mysqli_query($db, "select * from cwops_members order by nr;");
        while ($r = mysqli_fetch_row($q)) {
            echo join(";", $r)."\n";
        }
    }

    # return JSON object to plot weekly data of the given calls for given award type (for now, only support ACA)
    function plot () {
        global $db;
        $type = validate_get('type');
        $year = validate_get('year');

        if ($type != "ACA") {
            exit();
        }

        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);

        $calls = explode(',', $_GET['calls']);

        $ret = Array();

        # get weekly scores from Tuesdays (i.e. before CWTs)
        foreach ($calls as $c) {
            if (!is_call($c))
                continue;

            $data = $redis->get("plotACA".$c);

            if ($data) {
                $ret[$c] = unserialize($data);
            }
            else {
                $ret[$c] = Array();

                $date = new DateTime("2024-01-01");
                for ($i = 1; $i <= 52; $i++) {
                    $date->modify('next tuesday');
                    $tue = $date->format('Y-m-d');
                    $q = mysqli_query($db, "SELECT count(distinct(`nr`)) from cwops_log where `mycall`='$c' and year=2024 and date <= '$tue'");
                    $r = mysqli_fetch_row($q);
                    $aca = $r[0];
                    array_push($ret[$c], $aca);
                }
                $redis->set('plotACA'.$c, serialize($ret[$c]), 60*60*24);
            }
        }

        echo json_encode($ret);

    }



?>
