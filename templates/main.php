<!DOCTYPE html>
<html>

<head>
    <title>Bible Search (KVJ)</title>
    <style>
        html {
            font-family: Arial;
            color: #2a314a;
            font-size: 18px;
        }

        .verse-container {
            text-align: left;
            margin: 20px 0px;
            padding: 5px;
        }

        .disabled {
            color: lightgrey;
        }

        #results,
        .search-bar {
            margin: auto;
            width: 75%;
            text-align: center;
        }

        .no-results-message {
            font-weight: bold;
            margin-top: 20px;
        }

        .metrics {
            font-size: 12px;
            color: lightgrey;
        }

        .verse-number {
            font-size: .5rem;
            line-height: 0;
            vertical-align: super;
            font-weight: 800;
        }

        .referance {
            font-size: 1.25rem;
        }
    </style>
</head>

<body>
    <div class="search-bar">
        <h1>Search the KJV</h1>
        <form action="/">
            <input type="text" name="q" />
            <button type="submit">Search</button>
        </form>
    </div>
    <div id="results">
        <?php
        echo generate_search_results_html(
            $page,
            $query,
            $count,
            $search_results
        );
        ?>
    </div>
</body>

</html>