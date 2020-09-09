<?PHP
# Basic Utilities stuff

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function debug_print($items) {
    echo "<pre>";
    foreach ($items as $item) {
        print_r($item);
        echo "<br/>";
    }
    exit();
}

function validate_page($page) {
    return intval($page) < 0 ? False : True;
}