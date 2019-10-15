<?
session_start();
include_once('db.php');

if (array_key_exists('f', $_GET) && $_GET['f'] == 'logout') {
    session_destroy();
    header("Location: http://cwops.telegraphy.de/");
    return;
}


$call = strtoupper($_POST['callsign']);
$password = $_POST['password'];

# check validity 

if (!preg_match('/^[a-z0-9\/]+$/i', $call)) {
    echo "Callsign can only contain A-Z, 0-9 and /.<br>";
    echo "<a href='/'>Return to home page</a>";
    return;
}

if (!strlen($password)) {
    echo "Password must not be empty.<br>";
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
            header("Location: https://cwops.telegraphy.de/");
            $_SESSION['id'] = $user->id;
            $_SESSION['callsign'] = $user->callsign;
            $_SESSION['email'] = $user->email;
            echo "Login successful! Forwarding...";
            error_log("successful login of ".$user->callsign);
            return;
        }
        else {
?>
            Password incorrect. <br>
            <a href='/'>Return to home page</a><br>
<?
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
