<?
session_start();
include_once('db.php');

if (array_key_exists('f', $_GET)) {
    switch ($_GET['f']) {
    case 'logout':
        logout();
        break;
    case 'lostpassword':
        lostpassword();
        break;
    case 'recover':
        recover();
        break;
    default:
        echo "Invalid";
        return;
    }
}
else {
    login();
}

return;

function logout () {
    session_destroy();
    header("Location: https://cwops.telegraphy.de/");
    return;
}

function lostpassword () {
    global $db;

    $call = $_POST['recover'];

    if (!valid_call($call)) {
        echo "Callsign can only contain A-Z, 0-9 and /.<br>";
        echo "<a href='/'>Return to home page</a>";
        return;
    }

    $q = mysqli_query($db, "SELECT * from cwops_users where `callsign`='$call'");
    $r = mysqli_fetch_object($q);

    if ($r->email) {
        echo "Sending recovery email to the saved email address. If you don't receive it, check your spam folder or get in touch with Fabian, DJ1YFK (fabian@fkurz.net) to request a new password.<br>";

        $link = "https://cwops.telegraphy.de/recovery/".sha1($r->password)."/".$r->callsign;

        $subject = "Account recovery for CWops Award Tools";
        $mailtext = "Hello,\n
someone, probably you requested an account recovery mail for https://cwops.telegraphy.de/.

You can immediately log in to the site with the following link, and then set a new password in your 'Account' tab:

$link

If you didn't request this mail yourself, please disregard this message.

73,
 Fabian, DJ1YFK (Administrator of CWops Award Tools)
";

        mail($r->email, $subject, $mailtext, "From: CWops Award Tools <help@cwops.telegraphy.de>\r\nBcc: fabian@fkurz.net", "-fhelp@cwops.telegraphy.de");

        echo "<a href='/'>Return to home page</a>";
    }
    else {
        echo "No email address in the database. Please get in touch with Fabian, DJ1YFK (fabian@fkurz.net) to request a new password.<br>";
        echo "<a href='/'>Return to home page</a>";
    }
    return;
} # lostpassword

function recover () {
    global $db;
    $call = $_GET['u'];
    $hash = $_GET['h'];

    error_log("recovery: $call - $hash");

    if (valid_call($call) and valid_call($hash)) {
        $q = mysqli_query($db, "SELECT * from cwops_users where callsign='$call'");
        $user = mysqli_fetch_object($q);

        if ($user->callsign == $call and sha1($user->password) == $hash) {
            header("Location: https://cwops.telegraphy.de/");
            $_SESSION['id'] = $user->id;
            $_SESSION['callsign'] = $user->callsign;
            $_SESSION['email'] = $user->email;
            echo "Login successful! Forwarding...";
            error_log("successful recovery of ".$user->callsign);
        }
        else {
            echo "Invalid data";
        }
    }
    else {
            echo "Invalid data";
    }
    return;
}

function login () {
    $call = strtoupper($_POST['callsign']);
    $password = $_POST['password'];

    # check validity 
    if (!valid_call($call)) {
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
}

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
            <a href='/'>Return to home page</a> or<br>
            <form action='/lostpassword' method="POST">
                <input type="hidden" name="recover" value="<?=$call;?>">
                <input type="submit" value="Request account recovery email for <?=$call;?>">
            </form>
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

function valid_call($call) 
{
    return preg_match('/^[a-z0-9\/]+$/i', $call);
}



?>
