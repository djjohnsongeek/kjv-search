<?PHP
# script to reset database

require_once("../init.php");
// db connection set by init.php

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
        if (!$db->create_table($book)) {
            exit("table " . $book . " was not created.");
        }
    }
    
    // insert data into database
    $sql = "INSERT INTO `{$book}` (reference, chapter, verse_id, verse) VALUES ('" . $db->sql_esc($ref) . "',' " . $db->sql_esc($chapter) . "','" . $db->sql_esc($verse_id) . "','" . $db->sql_esc($verse) . "')";
    if (!$db->execute($sql)) {
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