<?PHP
require_once("config.php");
require_once("functions.php");
require_once("validations.php");

// set up db connection
$db = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
if ($db->connect_errno) {
    exit("SQL Connection Error - {$db->connect_errorno}: {$db->connect_error}");
}
$db->set_charset('utf8');