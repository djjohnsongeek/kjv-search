<?php
# HTML Rendering Logic

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
        $html .= "<div class='no-results-message'>"
              .  "<span>No Results Found for \" "
              .  $query
              .  " \"</span></div>";
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

function emphasize_result($query, $verse_text) {

    $pattern = "/" . $query . "/i";
    $match_count = preg_match_all($pattern, $verse_text, $matches, PREG_PATTERN_ORDER);

    if($match_count > 0) {
        $replacement = "<strong><i>" . $matches[0][0] . "</i></strong>";
        $verse_text = preg_replace($pattern, $replacement, $verse_text);
    }

    return $verse_text;
}