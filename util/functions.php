<?PHP
require_once("config.php");

function search($db, $query, $page) {
    $search_results = array();

    if (!$query) {
        return $search_results;
    }

    $ref = parse_as_ref($query);
    debug_print(array($ref));

    $start = microtime(True);
    $sql = generate_sql($query, $page);

    // query database
    $result = mysqli_query($db, $sql);
    $search_results = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    
    // add additional metrics
    $search_results["count"] = count($search_results);
    $end = microtime(True);
    $search_results["time"] = $end - $start;

    return $search_results;
}

function generate_sql($query, $page) {
    $sql = "";
    foreach (array_values(BOOKS) as $book) {
        $sql .= "(SELECT reference, verse FROM `". $book . "` WHERE verse LIKE '%" . $query . "%')";
        if ($book !== "revelation") {$sql .= " UNION ";}
    }
    $sql .= " LIMIT " . RESULT_LIMIT;
    $sql .= " OFFSET " . OFFSET_LIMIT * $page . ";";

    return $sql;
}

function sql_esc($db, $string) {
    return mysqli_real_escape_string($db, $string);
}

function create_table($db, $book_name) {
    $return_bool = True;
    $sql = "
    DROP TABLE IF EXISTS `{$book_name}`;
    CREATE TABLE IF NOT EXISTS `{$book_name}` (
        `row_id` int(11) NOT NULL AUTO_INCREMENT,
        `reference` char(64) NOT NULL,
        `verse` text NOT NULL,
    PRIMARY KEY (`row_id`),
    UNIQUE KEY `reference` (`reference`));";
    // $db->store_result();
    $result = mysqli_multi_query($db, $sql);
    if (!$result){
        echo $db->error;
        $return_bool = False;
        mysqli_free_result($result);
    }

    // flush multi_queries
    while ($db->next_result()) 
    {
        if (!$db->more_results()) break;
    }

    return $return_bool;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function emphasize_result($query, $result_verse) {
    // emphasize search query
    $pattern = "/" . $query . "/i";
    preg_match_all($pattern, $result_verse, $matches, PREG_PATTERN_ORDER);
    $replacement = "<strong><i>" . $matches[0][0] . "</i></strong>";
    $verse = preg_replace($pattern, $replacement, $result_verse);

    return $verse;
}

function parse_as_ref($query) {
    // remove whitespace
    $query = str_replace(" ", "", $query);

    // get book
    if (!$book = get_reference($query)) {
        return $book;
    }

    // get chapter and verse
    $pattern = "/[1-9]/";
    preg_match($pattern, $query, $matches, PREG_OFFSET_CAPTURE);
    $chapter_pos = $matches[0][1];
    $chapter_verse = explode(":", substr($query, $chapter_pos), 2);

    // package reference
    $full_ref["chapter"] = $chapter_verse[0];
    $full_ref["verse"] = $chapter_verse[1] ?? '';
    $full_ref["ref"] = $book;

    return $full_ref;
}

function get_reference($query) {
    $query = strtolower($query);
    $book_key = substr($query, 0, 3);
    if ($book_key === "phi") {
        $book_key = substr($query, 0, 5);
    }
    return BOOKS[$book_key] ?? False;
}

function debug_print($items) {
    echo "<pre>";
    foreach ($items as $item) {
        print_r($item);
    }
    exit();
}


// print_r(parse_as_ref("exo10"));