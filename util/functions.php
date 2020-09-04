<?PHP
# A big file of functions
require_once("config.php");

function search($db, $query, $page) {
    $search_results = array();

    if (!$query) {
        return $search_results;
    }

    $start = microtime(True);
    $sql = generate_search_sql($query, $page);

    // query database
    $result = mysqli_query($db, $sql);
    $search_results = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);

    // look up by ref
    if (empty($search_results)) {
        $ref = parse_as_ref($query);
        $sql = generate_ref_sql($ref);

        $result = mysqli_query($db, $sql);
        $search_results = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $search_results["display"] = "chunk";
        $search_results["gen-referance"] = generate_pretty_ref($ref);
        mysqli_free_result($result);
    }
    
    // add additional metrics
    $search_results["count"] = count($search_results);
    $end = microtime(True);
    $search_results["time"] = $end - $start;

    return $search_results;
}

function generate_search_sql($query, $page) {
    $sql = "";
    foreach (array_values(BOOKS) as $book) {
        $sql .= "(SELECT reference, verse FROM `". $book . "` WHERE verse LIKE '%" . $query . "%')";
        if ($book !== "revelation") {$sql .= " UNION ";}
    }
    $sql .= " LIMIT " . RESULT_LIMIT;
    $sql .= " OFFSET " . OFFSET_LIMIT * $page . ";";

    return $sql;
}

function generate_ref_sql($ref) {
    if(!$ref) {
        return false;
    }

    $sql = "SELECT reference, chapter, verse_id, verse FROM `" . $ref["book"] . "` WHERE ";
    $sql_where = generate_ref_sql_where($ref);

    return $sql .= $sql_where;
}

function generate_ref_sql_where($ref) {
    // if chapter, and verse is present
    if ($ref["chapter"] && $ref["verse"]) {
        // multiple verses?
        $verse_range = explode("-", $ref["verse"]);
        if (count($verse_range) > 1) {
            $sql_where = "chapter = '" . $ref["chapter"]
                       . "' AND verse_id >= '" . $verse_range[0]
                       . "' AND verse_id <= '" . $verse_range[1] . "';";
        }
        else {
            $sql_where = "chapter = '" . $ref["chapter"]
            . "' AND verse_id = '" . $ref["verse"] . "';";
        }
    }
    // if just chapter and book
    else {
        $chapter = $ref["chapter"] ?? "1";
        $sql_where = "chapter = '" . $chapter . "';";
    }

    return $sql_where;
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
        `chapter` INT(3) NOT NULL,
        `verse_id` INT(3) NOT NULL,
        `verse` text NOT NULL,
    PRIMARY KEY (`row_id`),
    UNIQUE KEY `reference` (`reference`));";
    

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

function emphasize_result($query, $verse_text) {
    // emphasize search query
    $pattern = "/" . $query . "/i";
    $match_count = preg_match_all($pattern, $verse_text, $matches, PREG_PATTERN_ORDER);

    if($match_count > 0) {
        $replacement = "<strong><i>" . $matches[0][0] . "</i></strong>";
        $verse_text = preg_replace($pattern, $replacement, $verse_text);
    }

    return $verse_text;
}

function parse_as_ref($query) {
    // remove whitespace
    $query = str_replace(" ", "", $query);

    // get book name from the query
    $book = get_reference($query);
    if (!$book) {
        return $book;
    }

    // get chapter and verse
    $pattern = "/[1-9]/";
    preg_match($pattern, $query, $matches, PREG_OFFSET_CAPTURE);
    $chapter_pos = $matches[0][1];
    $chapter_verse = explode(":", substr($query, $chapter_pos), 2);

    // package reference
    $full_ref["chapter"] = $chapter_verse[0] ?? null;
    $full_ref["verse"] = $chapter_verse[1] ?? null;
    $full_ref["book"] = $book;

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

function get_chapter($ref) {
    $chars = str_split($ref);
    $chapter = "";

    $length = count($chars);
    for ($i = 0; $i < $length; $i++) {
        if ($chars[$i] === ":") {
            break;
        }

        if (is_numeric($chars[$i]) && $i > 0) {
            $chapter .= $chars[$i];
        }
    }

    return intval($chapter);
}

function get_verse_number($ref) {
    $chars = str_split($ref);
    $verse_num = "";

    $length = count($chars);
    $parse_flag = False;
    for ($i = 0; $i < $length; $i++) {
        if ($parse_flag && is_numeric($chars[$i])) {
            $verse_num .= $chars[$i];
        }

        if ($chars[$i] === ":") {
            $parse_flag = True;
        }
    }

    return intval($verse_num);
}

function generate_pretty_ref($ref){
    $referance = ucfirst($ref["book"])
               . " "
               . $ref["chapter"];

    if ($ref["verse"]) {
        $referance .= ":" . $ref["verse"];
    }
               
    return $referance;
}

function generate_search_results_html($page, $query, $count, $search_results) {
    $html = "";
    if($query && $count > 0 && !isset($search_results["display"])) {


        // paginaton links
        $prev_link = "<a href='http://biblesearch/?q=" . $query . "&p=" . strval($page - 1) . "'>Prev</a> | ";
        $next_link = "<a href='http://biblesearch/?q=" . $query . "&p=" . strval($page + 1) . "'>Next</a><br/>";

        $prev_link = $page >= 1 ? $prev_link : "<span class='disabled'>Prev</span> | ";
        $next_link = $count > 10 ? $next_link : "<span class='disabled'>Next</span><br/>";

        $html .= $prev_link . $next_link;

        // metrics
        $html .= "<span class='metrics'>" . $count . " results in " . $search_results["time"] . " seconds.</span><br/>";
    }

    // render block of verses
    if(isset($search_results["display"])) {
        //debug_print(array($search_results));
        $html .= "<span class='metrics'>"
                . "Result found in "
                . $search_results["time"]
                . " seconds.</span><br/>";

        $html .= "<div class='verse-container'>";
        $html .= "<div class='referance'><strong>" . $search_results["gen-referance"] . "</strong></div>";
        $html .= "<div>";

        foreach($search_results as $result) {
            if (isset($result["reference"])) {
                $verse_html = "<span class='verse-number'>"
                            . $result["verse_id"]
                            . "</span>"
                            . " " . $result["verse"] . " ";
                $html .= $verse_html;
            }
        }

        $html .= "</div></div>";        
    }
    // render rows of verses
    else {
        $render_count = 0;
        foreach($search_results as $result) {
            // don't render the 11th result
            if ($render_count === 10) {break;}

            // only render results with a "reference"
            if (isset($result["reference"])) {
                $verse = emphasize_result($query, $result["verse"]);
        
                // render result
                $html .= "
                <div class='verse-container'>
                    <div class='referance'><strong >" . $result["reference"] . "</strong></div>
                    <div>" . $verse . "</div>
                </div>";
                $render_count++;
            }
        }
    }

    // No results message
    if ($count === 0) {
        $html .= "<div class='no-results-message'><span>No Results Found</span></div>";
    }

    return $html;
}