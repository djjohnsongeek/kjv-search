<?php

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
        $result = mysqli_query($this->db, $sql);
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

    public function sql_esc($string) {
        return mysqli_real_escape_string($this->db, $string);
    }
    
    public function create_table($book_name) {
        $return_bool = True;
    
        $sql = "
        DROP TABLE IF EXISTS `{$book_name}`;
        CREATE TABLE IF NOT EXISTS `{$book_name}` (
            `row_id` int(11) NOT NULL AUTO_INCREMENT,
            `reference` char(64) NOT NULL,
            `chapter` INT(3) NOT NULL,
            `verse_id` INT(3) NOT NULL,
            `verse` text NOT NULL,
        PRIMARY KEY (`row_id`),
        UNIQUE KEY `reference` (`reference`));";
        
    
        $result = mysqli_multi_query($this->db, $sql);
    
        if (!$result){
            echo $this->db->error;
            $return_bool = False;
            mysqli_free_result($result);
        }
    
        // flush multi_queries
        while ($this->db->next_result()) 
        {
            if (!$this->db->more_results()) break;
        }
    
        return $return_bool;
    }

}