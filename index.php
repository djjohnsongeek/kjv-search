<?PHP
#### Gathers the user's search query and renders the results ####
require_once("util/init.php");

$query = $_GET["q"] ?? NULL;
$page = $_GET["p"] ?? 0;
if (!validate_page($page)) {
    redirect(SITE_URL);
}

$page = intval($page);
$search_results = search($db, $query, $page);
$count = $search_results["count"] ?? NULL;

// render page
include_once(DIR_TEMPLATES . "header.php");
?>
<div class="search-bar">
    <h1>Search the KJV</h2>
    <form action="/">
        <input type="text" name="q"/>
        <button type="submit">Search</button>
    </form>
</div>
<div id="results">
    <?PHP
        if($query && $count > 0 && !isset($search_results["display"])) {


            // paginaton links
            $prev_link = "<a href='http://biblesearch/?q=" . $query . "&p=" . strval($page - 1) . "'>Prev</a> | ";
            $next_link = "<a href='http://biblesearch/?q=" . $query . "&p=" . strval($page + 1) . "'>Next</a><br/>";

            $prev_link = $page >= 1 ? $prev_link : "<span class='disabled'>Prev</span> | ";
            $next_link = $count > 10 ? $next_link : "<span class='disabled'>Next</span><br/>";

            echo $prev_link . $next_link;

            // metrics
            echo "<span class='metrics'>" . $count . " results in " . $search_results["time"] . " seconds.</span><br/>";
        }

        // render block of verses
        if(isset($search_results["display"])) {
            //debug_print(array($search_results));
            $html = "<span class='metrics'>"
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
            echo $html;
            
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
                    echo "
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
            echo "<div class='no-results-message'><span>No Results Found</span></div>";
        }
    ?>
</div>
<?PHP include_once(DIR_TEMPLATES . "footer.php"); ?>

