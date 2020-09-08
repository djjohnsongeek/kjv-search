<?PHP
# A big file of functions
require_once("config.php");

function search($db, $query, $page) {
    $search_results = array();

    if (!$query) {
        return $search_results;
    }

    // by keyword first, then reference
    $search_results = keyword_search($db, $query, $page);
    if ($search_results["count"] < 1) {
        $search_results = reference_search($db, $query);
    }

    return $search_results;
}

function keyword_search($db, $query, $page) {
    $sql = $db->generate_search_sql($query, $page);
    $search_results = $db->execute($sql);
    $search_results["count"] = count($search_results);

    return $search_results;
}

function reference_search($db, $query) {
    $ref = parse_as_ref($query);
    $sql = $db->generate_ref_sql($ref);

    if ($sql) {
        $search_results = $db->execute($sql);
        $search_results["gen-referance"] = generate_pretty_ref($search_results);
        $search_results["count"] = count($search_results) - 1;
        $search_results["display"] = "chunk";
    }
    else {
        $search_results = array();
        $search_results["count"] = 0;
    }

    return $search_results;
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
        $chapter = $ref["chapter"] === "" ? "1" : $ref["chapter"];
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

    // get book name
    $book = get_book($query);
    $chapter = get_chapter($query);
    $verse = get_verse_number($query);
    if (!$book) {
        return $book;
    }

    // package reference
    $full_ref["book"] = $book;
    $full_ref["chapter"] = $chapter == "" ? "1" : $chapter;
    $full_ref["verse"] = $verse;

    return $full_ref;
}

function get_book($query) {
    $query = strtolower($query);
    $book_key = substr($query, 0, 3);

    // special cases
    if ($book_key === "phi") {
        $book_key = substr($query, 0, 5);
    }
    if ($book_key === "jud") {
        $book_key = substr($query, 0, 4);
    }
    return BOOKS[$book_key] ?? False;
}

function debug_print($items) {
    echo "<pre>";
    foreach ($items as $item) {
        print_r($item);
        echo "<br/>";
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

    return $chapter;
}

function get_verse_number($ref) {
    $chars = str_split($ref);
    $verse_num = "";

    $length = count($chars);
    $parse_flag = False;
    for ($i = 0; $i < $length; $i++) {
        if ($parse_flag && (is_numeric($chars[$i]) || $chars[$i] === "-")) {
            $verse_num .= $chars[$i];
        }

        if ($chars[$i] === ":") {
            $parse_flag = True;
        }
    }

    return $verse_num;
}

function generate_pretty_ref($search_results){
    $count = count($search_results);
    $reference = "";

    if($count >= 1) {
        $reference = $search_results[0]["reference"];
    }

    if ($count > 1) {
        $end_verse = end($search_results)["verse_id"];
        $reference .= "-" . $end_verse;
    }

    return $reference;
}

function generate_search_results_html($page, $query, $search_results) {
    $html = "";

    if($query && $search_results["count"] > 0 && !isset($search_results["display"])) {
        $html .= generate_pagination_links($query, $search_results, $page);
    }

    if(isset($search_results["display"]) && $search_results["count"] > 0) {
        $html .= generate_verse_block($search_results);
    }
    else {
        $html .= generate_verse_rows($query, $search_results);
    }

    // No results message
    if ($search_results["count"] === 0) {
        $html .= "<div class='no-results-message'><span>No Results Found for '"
              . $query . "'</span></div>";
    }

    return $html;
}

function generate_pagination_links($query, $search_results,  $page) {
    $html = "";
    $prev_link = "<a href='http://biblesearch/?q=" . $query . "&p=" . strval($page - 1) . "'>Prev</a> | ";
    $next_link = "<a href='http://biblesearch/?q=" . $query . "&p=" . strval($page + 1) . "'>Next</a><br/>";

    $prev_link = $page >= 1 ? $prev_link : "<span class='disabled'>Prev</span> | ";
    $next_link = $search_results["count"] > 10 ? $next_link : "<span class='disabled'>Next</span><br/>";

    $html .= $prev_link . $next_link;

    return $html;
}

function generate_verse_block($search_results) {
    $html = "<div class='verse-container'>";
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
    
    return $html;
}

function generate_verse_rows($query, $search_results) {
    $html = "";
    $render_count = 0;

    foreach($search_results as $result) {
        // don't render the 11th result
        if ($render_count === 10) {break;}

        // filter out meta data
        if (isset($result["reference"])) {
            $verse = emphasize_result($query, $result["verse"]);
    
            $html .= "
            <div class='verse-container'>
                <div class='referance'><strong >" . $result["reference"] . "</strong></div>
                <div>" . $verse . "</div>
            </div>";
            $render_count++;
        }
    }

    return $html;
}