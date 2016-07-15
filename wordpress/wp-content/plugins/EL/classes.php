<?php

require_once("sql_functions.php");

/*------------------------------------*\
	LIMS database class
\*------------------------------------*/



class Database {

    protected $tables = array(); // array of tables associated with user's company
    protected $struct = array(); // associative array where each table is a key and the value is a class table()
    protected $name = null; // DB name e.g. db215537_EL
    protected static $db = null; // DB name e.g. db215537_EL
    protected static $company = null; // company associated with logged in user

    public function __construct() {

        self::$company = COMPANY;
        self::$db = DB_NAME_EL;
        $this->name = self::$db;

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

    public function get_db() {
        return self::$db;
    }

    public function get_name() {
        return $this->name;
    }

    public function show() {
        echo '<pre style="font-size:8px;">';
        print_r($this);
        echo '</pre>';
    }
}


class Table extends Database {

    protected $fields = array(); // list of fields in table
    protected static $table = null; // table name

    public function __construct($name) {
        $this->name = $name;
        self::$table = $this->name;

        // get list of fields
        $sql = sprintf("DESCRIBE %s.%s", $this->company, $name);
        $results = exec_query($sql);
        while($row = $results->fetch_assoc()) {
            $this->fields[] = $row["Field"];
        }

        // check FKs for table
        $sql = sprintf("select concat(table_name, '.', column_name) as 'foreign key',  
        concat(referenced_table_name, '.', referenced_column_name) as 'references'
        from
            information_schema.key_column_usage
        where
            referenced_table_name is not null
            and table_schema = '%s' 
            and table_name = '%s'
        ", $this->get_db(), $name);

        // get details of each field
        foreach ($this->fields as $field) {
            $this->struct[$field] = new Field($field);
        }
     }

    public function get_table() {
        return self::$table;
    }

}

class Field extends Table {


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


    public function __construct($name) {
        $this->name = $name;
    }
}


$db = new Database();
$db->show();

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
