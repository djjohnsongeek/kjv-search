<?PHP

function validate_page($page) {
    return intval($page) < 0 ? False : True;
}