<?php
# Main search logic

function search($db, $query, $page) {
    $search_results = array();

    if (!$query) {
        $search_results["count"] = 0;
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


# Helper Function for Search Logic

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