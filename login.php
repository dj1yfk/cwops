<?
session_start();
include_once('db.php');

if ($_GET['f'] == 'logout') {
    session_destroy();
    header("Location: http://cwops.telegraphy.de/");
    return;
}


$call = strtoupper($_POST['callsign']);
$password = $_POST['password'];

# check validity 

if (!preg_match('/^[a-z0-9\/]+$/i', $call)) {
    echo "Callsign can only contain A-Z, 0-9 and /.";
    echo "<a href='/'>Return to home page</a>";
    return;
}

log_in_or_create($call, $password, true);

function log_in_or_create ($call, $password, $recursive) {
    global $db;

    $q = mysqli_query($db, "SELECT * from cwops_users where callsign='$call'");

    $user = mysqli_fetch_object($q);

    if ($user) {
        if (password_verify($password, $user->password)) {
            header("Location: http://cwops.telegraphy.de/");
            $_SESSION['id'] = $user->id;
            $_SESSION['callsign'] = $user->callsign;
            echo "Login successful! Forwarding...";
            error_log("successful login of ".$user->callsign);
            return;
        }
        else {
            echo "Password incorrect. Try again.";
            echo "<a href='/'>Return to home page</a>";
            return;
        }

    }
    else {  # create account
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $q = mysqli_query($db, "INSERT into cwops_users (`callsign`, `password`) VALUES ('$call', '$hash');");

        # now log in
        if ($recursive) {
            log_in_or_create($call, $password, false);
        }

    }

}


?>
