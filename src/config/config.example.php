<?php

// SITE
define("SITE_URL", "");

// PROJET DIRECTORIES
define("DIR_ROOT", realpath($_SERVER["DOCUMENT_ROOT"]) . DIRECTORY_SEPARATOR);
define("DIR_SRC", DIR_ROOT . "src" . DIRECTORY_SEPARATOR);
define("DIR_CLASSES", DIR_SRC . "classes" . DIRECTORY_SEPARATOR);
define("DIR_TEMPLATES", DIR_SRC . "templates" . DIRECTORY_SEPARATOR);
define("DIR_INSTANCE", DIR_ROOT . "instance" . DIRECTORY_SEPARATOR);

// MYSQL
define("DB_HOST", "");
define("DB_USERNAME", "");
define("DB_PASSWORD", "");
define("DB_NAME", "");
define("DB_PORT", "");

// QUERY CONSTANTS
define("RESULT_LIMIT", 11);
define("OFFSET_LIMIT", 10);
define(
        "BOOKS",
        [
            "gen" => "genesis",
            "exo" => "exodus",
            "lev" => "leviticus",
            "num" => "numbers",
            "deu" => "deuteronomy",
            "jos" => "joshua",
            "judg" => "judges",
            "rut" => "ruth",
            "1sa" => "1samuel",
            "2sa" => "2samuel",
            "1ki" => "1kings",
            "2ki" => "2kings",
            "1ch" => "1chronicles",
            "2ch" => "2chronicles",
            "ezr" => "ezra",
            "nem" => "nehemiah",
            "est" => "esther",
            "job" => "job",
            "psa" => "psalms",
            "pro" => "proverbs",
            "ecc" => "ecclesiastes",
            "son" => "songofsolomon",
            "isa" => "isaiah",
            "jer" => "jeremiah",
            "lam" => "lamentations",
            "eze" => "ezekiel",
            "dan" => "daniel",
            "hos" => "hosea",
            "joe" => "joel",
            "amo" => "amos",
            "oba" => "obadiah",
            "jon" => "jonah",
            "mic" => "micah",
            "nah" => "nahum",
            "hab" => "habakkuk",
            "zep" => "zephaniah",
            "hag" => "haggai",
            "zec" => "zechariah",
            "mal" => "malachi",
            "mat" => "matthew",
            "mar" => "mark",
            "luk" => "luke",
            "joh" => "john",
            "act" => "acts",
            "rom" => "romans",
            "1co" => "1corinthians",
            "2co" => "2corinthians",
            "gal" => "galatians",
            "eph" => "ephesians",
            "phili" => "philippians",
            "col" => "colossians",
            "1th" => "1thessalonians",
            "2th" => "2thessalonians",
            "1ti" => "1timothy",
            "2ti" => "2timothy",
            "tit" => "titus",
            "phile" => "philemon",
            "heb" => "hebrews",
            "jam" => "james",
            "1pe" => "1peter",
            "2pe" => "2peter",
            "1jo" => "1john",
            "2jo" => "2john",
            "2jo" => "3john",
            "jude" => "jude",
            "rev" => "revelation",
        ]
    );