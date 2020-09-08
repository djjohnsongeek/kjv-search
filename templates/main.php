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

        input {
            box-sizing: border-box;
            width: 250px;
            font-size: 18px;
        }

        button {
            font-size: 18px;
            box-sizing: border-box;
            border: 1px solid grey;
            padding: 2px;
            border-left: 0px;
        }

        .verse-container {
            text-align: left;
            margin: 20px 0px;
            padding: 5px;
        }

        .disabled {
            color: lightgrey;
        }

        #results, .search-bar {
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
            <div style="display: flex; flex-direction: row; flex-wrap: wrap;">
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