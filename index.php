<?php
require_once("src/init.php");

$query = $_GET["q"] ?? NULL;
$page = $_GET["p"] ?? 0;

if (!validate_page($page)) {
    redirect(SITE_URL);
}

$page = intval($page);
$search_results = search($db, $query, $page);

$db->close();

// render page
include_once(DIR_TEMPLATES . "main.php");
