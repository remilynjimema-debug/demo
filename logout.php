<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
// logout.php
include_once('config.php');

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (ini_get("session-use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Terminate session
session_destroy();

// Redirect safely to root interface login view
header("Location: index.php");
exit;
?>
</body>
</html>