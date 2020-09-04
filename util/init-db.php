<?PHP
# script to reset database

require_once("config.php");
require_once("functions.php");
require_once("validations.php");

// set up db connection
$db = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
if ($db->connect_errno) {
    exit("SQL Connection Error - {$db->connect_errorno}: {$db->connect_error}");
}
$db->set_charset('utf8');
// use custom instance dir since this is run from CLI

$dir_instance = "../instance/";
// prepare vars 
$file_pointer = fopen($dir_instance. "bible.txt", "r");
$books = array();
$i = 0;

// parse data from files
while(True) {
    // get reference, book, and verse
    $line = fgets($file_pointer);
    if(!$line) {
        break;
    }
    $arr = explode("\t", $line);
    $ref = $arr[0];
    $chapter = get_chapter($ref);
    $verse = trim($arr[1]);
    $verse_id = get_verse_number($ref);
    $book = str_replace(" ", "", strtolower(rtrim($ref, " 0123456789:")));

    // create new table if it does not exist
    if (!in_array($book, $books)) {
        array_push($books, $book);
        if (!create_table($db, $book)) {
            exit("table " . $book . " was not created.");
        }
    }
    
    // insert data into database
    $sql = "INSERT INTO `{$book}` (reference, chapter, verse_id, verse) VALUES ('" . sql_esc($db, $ref) . "',' " . sql_esc($db, $chapter) . "','" . sql_esc($db, $verse_id) . "','" . sql_esc($db, $verse) . "')";
    if (!mysqli_query($db, $sql)) {
        echo "SLQ ERROR: " . $db->error;
    }

    $i++;
    if ($i > 1000000) {
        exit("Wow, One million. Something went wrong.");
    }
}
fclose($file_pointer);
echo "{$i} lines read and inserted successfully.";
?>