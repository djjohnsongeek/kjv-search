<?php
require_once("config.php");

class Database
{
    public $db;

    function __construct() {
        $db = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($db->connect_errno) {
            exit("SQL Connection Error - {$db->connect_errorno}: {$db->connect_error}");
        }
        $db->set_charset('utf8');

        $this->db = $db;
    }

    function close() {
        mysqli_close($this->db);
        $this->db = null;
    }

    public function execute($sql) {
        // debug_print(array($sql));
        $result = mysqli_query($this->db, $sql);
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        echo "<br/>";
        if ($result === False) {
            $search_results = array();
        }
        else {
            $search_results = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
        }

        return $search_results;
    }

    public function generate_search_sql($query, $page) {
        $sql = "";
        foreach (array_values(BOOKS) as $book) {
            $sql .= "(SELECT reference, verse FROM `". $book . "` WHERE verse LIKE '%" . $query . "%')";
            if ($book !== "revelation") {$sql .= " UNION ";}
        }
        $sql .= " LIMIT " . RESULT_LIMIT;
        $sql .= " OFFSET " . OFFSET_LIMIT * $page . ";";
    
        return $sql;
    }
    
    public function generate_ref_sql($ref) {
        if(!$ref) {
            return false;
        }
    
        $sql = "SELECT reference, chapter, verse_id, verse FROM `" . $ref["book"] . "` WHERE ";
        $sql_where = generate_ref_sql_where($ref);
    
        return $sql .= $sql_where;
    }

}