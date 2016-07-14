<?php

require_once("sql_functions.php");

/*------------------------------------*\
	LIMS database class
\*------------------------------------*/



class Database {

    public $tables = array(); // array of tables associated with user's company
    public $struct = array(); // associative array where each table is a key and the value is a class table()
    public $name = null; // DB name e.g. db215537_EL
    public $company = null; // company associated with logged in user

    public function __construct() {

        $this->name = DB_NAME_EL;
        $this->company = COMPANY;

        // get list of tables
        $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME_EL . "' AND TABLE_NAME like '" . COMPANY . "%'";
        $results = exec_query($sql);
        while($row = $results->fetch_assoc()) {
            $this->tables[] = $row["TABLE_NAME"];
        }

        // generate DB structure
        foreach ($this->tables as $table) {
            $this->struct[$table] = new Table($table);
        }
    }

    public function get_tables() {
        return $this->tables;
    }
    
    public function get_struct() {
        return $this->struct;
    }

    public function get_company() {
        return $this->company;
    }

    public function get_name() {
        return $this->name;
    }

}


class Table extends Database {

    public $name = null;
    public $fields = array(); // list of fields in table
    public $struct = array(); // associative array detailing fields, key is field name, value is Field class

    function __construct($name) {
        $this->name = $name;

        // get list of fields
        $sql = sprintf("DESCRIBE %s.%s", $this->company, $name);
        $results = exec_query($sql);
        while($row = $results->fetch_assoc()) {
            $this->fields[] = $row["Field"];
        }

        // get details of each field
        foreach ($this->fields as $field) {
            $this->struct[$field] = new Field($field);
        }
     }

}

class Field extends Table {

    public $name = null;

/*

                    "type": "int",
                    "null": false,
                    "key": "PRI",
                    "extra": "auto_increment",
                    "name": "PK",
                    "table": "BioRepo.BacterialCharacterizations",
                    "default": null,
                    "is_pk": false,
                    "viewable": false,
                    "input": false,
                    "req": false,
                    "ref_table": false,
                    "is_fk": false,
                    "ref_by": false

*/


    function __construct($name) {
        $this->name = $name;
    }
}


$db = new Database();

/*------------------------------------*\
	LIMS functions
\*------------------------------------*/





/* Echo boostrap alert message

Parameters:
===========
- msg : str
        message (as HTML) to display to user,
        will place div alert around msg
- debug : bool (optional)
          if true, message will be printed, if
          false, message will not be printed.
          this allows for setting a global
          DEBUG
          
*/

function err_msg($msg, $debug=true) {

    if ($debug) {
        echo '<div class="alert alert-danger col-sm-3" role="alert">' . $msg . '</div>';
    }

}



?>
