<?php


/*------------------------------------*\
	LIMS database class
\*------------------------------------*/

/* Database class for loading structure of LIMS database

Once a user logs in, the general structure of the database
associated with the company of that user is loaded.

Class properties:
- tables : array of tables associated with user's company
- struct : associative array where each table is a key and
           the value is a class Table
- name : name of database e.g. db215537_EL
- db : same as name but defined as static
- company : company associated with logged in user

TODO
*/
class Database {

    protected $tables = array(); // array of tables associated with user's company
    protected $struct = array(); // associative array where each table is a key and the value is a class table()
    protected $name = null; // DB name e.g. db215537_EL
    protected static $db = null; // DB name e.g. db215537_EL
    protected static $company = null; // company associated with logged in user

    public function __construct($comp=null) {

        if ($comp) {

            self::$company = $comp;
            self::$db = DB_NAME_EL;
            $this->name = self::$db;

            // get list of tables
            $sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME_EL . "' AND TABLE_NAME like '" . self::$company . "%'";
            $results = exec_query($sql);
            while($row = $results->fetch_assoc()) {
                $this->tables[] = $row["TABLE_NAME"];
            }

            // check FKs for table
            $sql = sprintf("select concat(table_schema, '.', table_name, '.', column_name) as 'foreign key',  
            concat(referenced_table_schema, '.', referenced_table_name, '.', referenced_column_name) as 'references'
            from
                information_schema.key_column_usage
            where
                referenced_table_name is not null
                and table_schema = '%s' 
                and table_name like '%s_%%'
            ", Database::get_db(), Database::get_company());
            $results = exec_query($sql);
            $fks = array();
            while($row = $results->fetch_assoc()) {
                $fks[$row["foreign key"]] = $row["references"];
            }

            // generate DB structure
            foreach ($this->tables as $table) {
                $this->struct[$table] = new Table($table, $fks);
            }
        }
    }

    // return array of full table names
    public function get_tables() {
        return $this->tables;
    }
    
    // return assoc array of table struct
    public function get_struct() {
        return $this->struct;
    }

    // return name of company for user
    public function get_company() {
        return self::$company;
    }

    // return name of DB
    public function get_db() {
        return self::$db;
    }

    // same as get_db()
    public function get_name() {
        return get_db();
    }

    // given a table (name) return its Table class in struct
    public function get_table($table) {
        return $this->struct[$table];
    }

    // given a table (name) return fields in table as array
    public function get_fields($table) {
        return $this->struct[$table]->get_fields();
    }

    // pretty print
    public function show() {
        echo '<pre style="font-size:8px;">';
        print_r($this);
        echo '</pre>';
    }
}

/* Table class defines properties of a given database table

Class roperties:
- struct : associative array where each field is a key and
           the value is a class Field
- name : name of table with prepended company (e.g. matatu_samples)
- table : same as name but as a static type
- fields : array of fields contained in table

*/
class Table {

    protected $fields = array(); // list of fields in table
    protected static $table = null; // table name

    public function __construct($name, $fks) {
        $this->name = $name;
        self::$table = $this->name;

        // get list of fields
        $sql = sprintf("DESCRIBE %s", $this->name);
        $results = exec_query($sql);
        while($row = $results->fetch_assoc()) {
            $this->fields[] = $row["Field"];
        }

        // get details of each field
        foreach ($this->fields as $field) {
            $this->struct[$field] = new Field($field, $fks);
        }
     }

    // return full table name
    public function get_table() {
        return self::$table;
    }

    // same as get_table()
    public function get_name() {
        return get_table();
    }

    // return safe name (without company or DB)
    public function get_safe_name() {
        return explode("_", $this->get_table())[1];
    }

    public function get_full_name() {
        return $this->get_db() . '.' . $this-get_table();
    }


    // return array of fields in table
    public function get_fields() {
        return $this->fields;
    }

    // return databse table belongs to
    public function get_db() {
        return Database::get_db();
    }

    // pretty print
    public function show() {
        echo '<pre style="font-size:8px;">';
        print_r($this);
        echo '</pre>';
    }
}

/* Field class defined properties of a given column in a table

Class properties:
- name : name of field (e.g. sampleType)
- is_fk : bool for if a field is a foreign key
- fk_ref : if a field is a foreign key, it references this field (full_name)
- hidden : bool for whether field should be hidden from front-end view
- is_ref : bool if field is referenced by a foreign key (this makes the field a primary key)
- ref : if field is referenced by a foreign key, this is the field that references it (full_name)
*/
class Field {

    protected $is_fk; // if field is a foreign key
    protected $fk_ref; // if field is a foreign key, it references this field (full name)
    protected $hidden; // if field should be hidden from front end view
    protected $is_ref; // if field is referenced by a foreign key
    protected $ref; // if field is referenced by a foreign key, this is the field that references it (full name)

    public function __construct($name, $fks) {
        $this->name = $name;

        // check if field is fk
        if (array_key_exists($this->name, $fks)) {
            $this->is_fk = true;
            $this->fk_ref = $fks[$this->name];
        } else {
            $this->is_fk = false;
            $this->fk_ref = false;
        }

        // check if field is referenced by fk
        $tmp = array_search($this->name, $fks);
        if ($tmp) {
            $this->is_ref = true;
            $this->ref = $tmp;
        } else {
            $this->is_ref = false;
            $this->ref = false;
        }

        // check if hidden field
        if (in_array($this->name, explode(",",HIDDEN))) {
            $this->hidden = true;
        } else {
            $this->hidden = false;
        }

    }

    // return name of field (e.g. sample)
    public function get_name() {
        return self::$name;
    }

    // return full name of field (db123.matatu_samples.sample)
    public function get_full_name() {
        return Table::get_full_name() . '.' . $this->get_name();
    }    


    // pretty print
    public function show() {
        echo '<pre style="font-size:8px;">';
        print_r($this);
        echo '</pre>';
    }
}



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
