<!DOCTYPE html>
<html>

<head>
    <title>Bible Search (KVJ)</title>
    <link href="static/style.css" rel="stylesheet">
</head>

<body>
    <div class="search-bar">
        <h1>Search the KJV</h1>
        <form action="/">
            <div class="search">
                <div>
                    <input type="text" name="q" />
                </div>
                <div>
                    <button type="submit">Search</button>
                </div>
            </div>
        </form><br/>
    </div>
    <div id="results">
        <?php
        echo generate_search_results_html(
            htmlspecialchars($page),
            htmlspecialchars($query),
            $search_results
        );
        ?>
    </div>
</body>

</html>