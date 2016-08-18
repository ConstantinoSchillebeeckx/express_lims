<?php

/*------------------------------------*\
	    SQL server-side functions
\*------------------------------------*/

/* Generate a select with list of DB tables

Parameters:
- $tables : array
            list of tables

Returns:
- echos HTML of select

*/
function get_tables_as_select($tables) {

    $sel = '<select>';
    foreach ( $tables as $table ) {
        $sel .= sprintf("<option value='%s'>%s</option>", $table, $table);
    }
    $sel .= '</select>';

    echo $sel;

}




/* Connect to database

Create a connection to the database, show
error if connection cannot be made.

Parameters:
===========
- none

Return:
=======
- returns a mysqli connection object if
  successful connection, echo error
  otherwise and return false.

*/
function connect_db() {

    mb_internal_encoding('UTF-8');

    require_once("config/db.php");
    $conn = new mysqli(DB_HOST_EL, DB_USER_EL, DB_PASS_EL, DB_NAME_EL);

    if ($conn->connect_errno) {
        err_msg("Could not connect to database!");
        return false;
    }

    if (!$conn->set_charset('utf8')) {
        err_msg("Error loading character set utf8");
        return false;
    }

    return $conn;
}




/* Execute SQL query

Will execute a given SQL query

Parameters:
===========
- sql : sql string
- conn : MYSQLi connection object (optional)
         if no connection is passed, one will
         be created and closed when finished,
         if a connection is passed, it will be
         kept open.

Returns:
========
- false if no connection could be made to DB,
  otherwise returns the results of the query
  as a mysqli_result class

*/
function exec_query($sql, $conn=null) {

    $keep_alive = false;
    if (!$conn) {
        $conn = connect_db();
    } else {
        $keep_alive = true;
    }

    if ($conn) {
        $res = $conn->query($sql);
        if (!$res) {
            err_msg("Error running query: " . $sql . '; the error was: ' . $conn->error);
            $conn->close;
            return false;
        }
    } else {
        return false;
    }

    if (!$keep_alive && $conn) {
        $conn->close;
    }

    if ($res->num_rows > 0) {
        return $res;
    } else {
        return true;
    }
}



/* Process data from AJAX call

Function is called every time an AJAX call is 
made for data for viewing the DB.  This is done
when the function build_table() is called [in
EL.php]

Function assumes that the following are passed:
- $_GET['table']
- $_GET['columns']
- $_GET['pk']
- $_GET['filter'] (optional) format assoc array {col name: val}

*/
function get_data_from_db() {

    /**
     * Script:    DataTables server-side script for PHP 5.2+ and MySQL 4.1+
     * Notes:     Based on a script by Allan Jardine that used the old PHP mysql_* functions.
     *            Rewritten to use the newer object oriented mysqli extension.
     * Copyright: 2010 - Allan Jardine (original script)
     *            2012 - Kari Söderholm, aka Haprog (updates)
     * License:   GPL v2 or BSD (3-point)
     */
     
    // DB table to use
    $sTable = $_GET['table'];

    /**
     * Array of database columns which should be read and sent back to DataTables. Use a space where
     * you want to insert a non-database field (for example a counter or static image)
     */
    $aColumns = $_GET['cols'];
      
    // Indexed column (used for fast and accurate table cardinality)
    $sIndexColumn = $_GET['pk'];

    // user filter (optional)
    $user_filter = $_GET['filter'];

    // Input method (use $_GET, $_POST or $_REQUEST)
    $input =& $_GET;
     
    /**
     * MySQL connection
     */
    $conn = connect_db();

    // DB class
    $db = get_db();
    $table_class = $db->get_table($sTable);

      
    /**
     * Paging
     */
    $sLimit = "";
    if ( isset( $input['iDisplayStart'] ) && $input['iDisplayLength'] != '-1' ) {
        $sLimit = " LIMIT ".intval( $input['iDisplayStart'] ).", ".intval( $input['iDisplayLength'] );
    }
      
      
    /**
     * Ordering
     */
    $aOrderingRules = array();
    if ( isset( $input['iSortCol_0'] ) ) {
        $iSortingCols = intval( $input['iSortingCols'] );
        for ( $i=0 ; $i<$iSortingCols ; $i++ ) {
            if ( $input[ 'bSortable_'.intval($input['iSortCol_'.$i]) ] == 'true' ) {
                $aOrderingRules[] =
                    "`".$aColumns[ intval( $input['iSortCol_'.$i] ) ]."` "
                    .($input['sSortDir_'.$i]==='asc' ? 'asc' : 'desc');
            }
        }
    }
     
    if (!empty($aOrderingRules)) {
        $sOrder = " ORDER BY ".implode(", ", $aOrderingRules);
    } else {
        $sOrder = "";
    }

     
    /**
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */
    $iColumnCount = count($aColumns);
     
    if ( isset($input['sSearch']) && $input['sSearch'] != "" ) {
        $aFilteringRules = array();
        for ( $i=0 ; $i<$iColumnCount ; $i++ ) {
            if ( isset($input['bSearchable_'.$i]) && $input['bSearchable_'.$i] == 'true' ) {
                $aFilteringRules[] = "`".$aColumns[$i]."` LIKE '%".$conn->real_escape_string( $input['sSearch'] )."%'";
            }
        }
        if (!empty($aFilteringRules)) {
            $aFilteringRules = array('('.implode(" OR ", $aFilteringRules).')');
        }
    }
      
    // Individual column filtering
    for ( $i=0 ; $i<$iColumnCount ; $i++ ) {
        if ( isset($input['bSearchable_'.$i]) && $input['bSearchable_'.$i] == 'true' && $input['sSearch_'.$i] != '' ) {
            $aFilteringRules[] = "`".$aColumns[$i]."` LIKE '%".$conn->real_escape_string($input['sSearch_'.$i])."%'";
        }
    }


    // User filter
    if ( $user_filter !== '' ) {
        $filter_val = $conn->real_escape_string( reset( $user_filter ) );
        $filter_key = $conn->real_escape_string( key ( $user_filter ) );
        if ( empty( $aFilteringRules ) ) {
            $aFilteringRules = array( sprintf(" `%s` = '%s' ", $filter_key, $filter_val ) );
        } else {
            array_push( $aFilteringRules, sprintf(" `%s` = '%s' ", $filter_key, $filter_val ) );
        }
    }
     
    if (!empty($aFilteringRules)) {
        $sWhere = " WHERE ".implode(" AND ", $aFilteringRules);
    } else {
        $sWhere = "";
    }

      
      
    /**
     * SQL queries
     * Get data to display
     */
    $aQueryColumns = array();
    foreach ($aColumns as $col) {
        if ($col != ' ') {
            $aQueryColumns[] = $col;
        }
    }


     
    $sQuery = "
        SELECT SQL_CALC_FOUND_ROWS `" . implode("`, `", $aQueryColumns) . "`
        FROM `".$sTable."`".$sWhere.$sOrder.$sLimit;
    $rResult = exec_query($sQuery, $conn);
    //return $rResult;
      
    // Data set length after filtering
    $sQuery = "SELECT FOUND_ROWS()";
    $rResultFilterTotal = exec_query($sQuery, $conn); 
    list($iFilteredTotal) = $rResultFilterTotal->fetch_row();
     
    // Total data set length
    $sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM `".$sTable."`";
    $rResultTotal = exec_query($sQuery, $conn); 
    list($iTotal) = $rResultTotal->fetch_row();
      
      
    /**
     * Output
     */
    $output = array(
        "sEcho"                => intval($input['sEcho']),
        "iTotalRecords"        => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData"               => array(),
    );


    // fetch results and do any type of formatting      
    // such as adding hyper links (used in the front-end for filtering)
    // or adjusting time zones
    if ($rResult->num_rows) {
        while ( $aRow = $rResult->fetch_assoc() ) {
            $row = array();
            for ( $i=0 ; $i<$iColumnCount ; $i++ ) {
            
                $col_name = $aColumns[$i];
                $field_class = $table_class->get_field( $col_name ); // Field class
                $comment = $field_class->get_comment();

                $val = $aRow[ $col_name ];


                // reformat value if needed
                if ( $comment && array_key_exists('column_format', $comment) ) {
                    if ( $comment['column_format'] == 'date') {
                        $val = date('Y-m-d', strtotime($val));
                    }
                }
        
                if ( $field_class->is_unique() ) { // don't add filter to unique items since it doesnt make sense to filter them
                    $row[] = $val;
                } else { // format with filter
                    $url = sprintf("%s&filter=%s,%s", $_SERVER['HTTP_REFERER'], $col_name, $val );
                    $row[] = '<a href="' . $url . '">' . $val . '</a>';
                }
            }
            $output['aaData'][] = $row;
        }
    }

    return json_encode( $output );
}







/* Delete item from DB

Function is called by AJAX when user clicks delete
button.  Will delete item from table and will
update history.  AJAX call made by deleteItem() in
js/table.js

Parameters:
- $_GET['id'] : name of the item being deleted
- $_GET['table'] : name of table in which item is located
- $_GET['pk'] : column in which item exists


*/
function delete_item_from_db() {

    // get some vars
    $db = get_db();
    $id = $_GET['id'];
    $table = $_GET['table'];
    $pk = $_GET['pk'];
    $table_full_name = $db->get_name() . '.' . $table;
    $table_class = $db->get_table($table);

    // check if, when deleting a row, it's referenced by an FK
    // if it is, then the item can't be deleted unless the 
    // reference table ID doesn't have any values
    $refs = $table_class->get_ref();
    if ($refs) {
        foreach($refs as $ref) {

            $tmp = explode('.',$ref);
            $ref_table = $tmp[0];
            $ref_table_safe = explode('_',$ref_table)[1];
            $ref_field = $tmp[1];

            // check if ref table has a ref field equal to $id
            if (table_has_value($ref_table, $ref_field, $id)) {
                $msg = sprintf("The item <code>$id</code> that you are trying to delete is referenced as a foreign key in the table <code><a href='%s?table=$ref_table_safe'>$ref_table_safe</a></code>; you must remove all the entries in that table first, before deleting this entry.", VIEW_TABLE_URL_PATH);
                $ret = array("msg" => $msg, "status" => false);
                return json_encode($ret);
            }
        }
    }

    // update history
    // TODO

    // delete row
    $sql = sprintf("DELETE FROM %s WHERE `%s` = '%s'", $table_full_name, $pk, $id);
    $ret = exec_query($sql);
    if ($ret) {
        $msg = sprintf("The item <code>%s</code> was properly archived.", $id);
        $ret = array("msg" => $msg, "status" => true);
        return json_encode($ret);
    } else {
        return json_encode(array("msg"=>"There was an error, please try again", "status"=>false, "log"=>$ret));
    }


}

/* Check if table has value

Function useful for checking if a table has a certain value;
used when trying to remove a field that is referenced by
a FK.

Parameters:
===========
- $ref_table : str
               table in which to check for results
- $ref_field : str
               field to query
- $id : str
        value in $ref_field to query


*/
function table_has_value($ref_table, $ref_field, $id) {

    $db = get_db();
    $db_name = $db->get_name();
    $sql = sprintf("SELECT %s FROM %s.%s WHERE %s = '%s'", $ref_field, $db_name, $ref_table, $ref_field, $id);
    $result = exec_query($sql);

    if ($result->num_rows ) {
        return true;
    } else {
        return false;
    }
}






/* Function called by AJAX when user adds item with modal button

Parameters:
===========
- $_GET['table']
- $_GET['pk']
- $_GET['dat'] : obj of form data (key: col name, val: value)

*/
function add_item_to_db() {

    $db = get_db();
    $table = $_GET['table'];
    $dat = $_GET['dat'];
    $pk = $_GET['pk'];

    // ERROR CHECK ITEM
    if ( isset( $table ) && isset( $dat ) ) {
        $table_class = $db->get_table($table);
        $msg = validate_item_in_table($table, $dat);

        if ($msg !== true) {
            return json_encode(array("msg" => $msg, "status" => false));
        }

        // check field type and enforce
        $types = '';
        foreach($dat as $field => $field_val) {
            $field_class = $table_class->get_field($field);
            $type = $field_class->get_type();
            
            if (strpos($type, 'float') !== false) {
                $types .= 'd';
            } else if (strpos($type, 'int') !== false) {
                $types .= 'i';
            } else {
                $types .= 's';
            }
        }

        // prepare statement to add item
        $cols = '`' . implode( '`, `', array_keys($dat) ) . '`';
        $vals = implode(', ', array_pad(array(), count($dat), '?'));
        $stmt = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table_class->get_full_name(), $cols, $vals);
        $prep = prepare_statement( $stmt, $dat, $types);
        if ( $prep ) {
            if ($dat[$pk]) {
                return json_encode(array("msg" => sprintf('Item <code>%s</code> successfully added to LIMS.', $dat[$pk]), "status" => true, "log"=>$stmt ));
            } else {
                return json_encode(array("msg" => 'Item successfully added to LIMS.', "status" => true, "log"=>$stmt ));
            }
        } else {
            return json_encode(array("msg" => 'There was an error, please try again.', "status" => false, "log" => $prep ));
        }
    }
    return json_encode(array("msg" => 'There was an error, please try again.', "status" => false));

}



/* Function called by AJAX when user edits item in modal button

Edit any items that have been changed

Parameters:
===========
- $_GET['table']
- $_GET['pk'] : primary key column
- $_GET['original_row'] : obj of original row values (key: col name, val: value)
- $_GET['dat'] : obj of form data from modal (key: col name, val: value)

*/
function edit_item_in_db() {

    $db = get_db();
    $table = $_GET['table'];
    $dat = $_GET['dat'];
    $pk = $_GET['pk'];
    $original_row = $_GET['original_row'];
    $pk_val = $original_row[$pk];

    if ( isset( $table ) && isset( $dat ) ) {

        // FIND ITEMS THAT WERE CHANGED
        $edits = array();
        foreach ($dat as $field => $new) {
            $new == '' ? $new = null : null;
            $original = $original_row[$field];
            if ($original != $new) {
                $edits[$field] = $new;
            }
        }
    
        if ( count($edits) > 0 ) {
            // ERROR CHECK ITEM
            $msg = validate_item_in_table($table, $edits);

            if ($msg !== true) {
                return json_encode(array("msg" => $msg, "status" => false, 'log'=>array($edits,$dat,$original_row)));
            }

            $table_class = $db->get_table($table);
            $pk_eq = sprintf("`%s` = '%s'", $pk, $pk_val);
            $col_eq = array();
            foreach ($edits as $col => $val) {
                $col_eq[] .= "`" . $col . "`= ?";
            }

            // check field type and enforce
            $types = '';
            foreach($edits as $field => $field_val) {
                $field_class = $table_class->get_field($field);
                $type = $field_class->get_type();
                
                if (strpos($type, 'float') !== false) {
                    $types .= 'd';
                } else if (strpos($type, 'int') !== false) {
                    $types .= 'i';
                } else {
                    $types .= 's';
                }
            }

            // prepare statement to add item
            $stmt = sprintf('UPDATE %s SET %s WHERE %s', $table_class->get_full_name(), implode(', ', $col_eq), $pk_eq);
            $prep = prepare_statement( $stmt, $edits, $types );
            if ( $prep === true ) {
                return json_encode(array("msg" => 'Item successfully edited.', "status" => true, 'log'=>array($stmt,$edits,$dat, $original_row, $prep))); // TODO cleanup log when finished (for safety)
            } else {
                return json_encode(array("msg" => 'There was an error, please try again.', "status" => false, 'log'=>$prep)); // clean up log when finished (for safety)
            }
        } else {
            return json_encode(array("msg" => 'Values are not any different than current ones, nothing was edited.', "status" => true));
        }
    }
    return json_encode(array("msg" => 'There was an error, please try again.', "status" => false));

}






/* Function called by AJAX when user attempts to delete table

Will delete the specified table, both the standard and the
history counterpart.


Parameters:
===========
- $_GET['dat'] : obj of form data (key: col name, val: value)
                 at a minimum will have the following keys:
                 - table_name (safe name)
*/
function delete_table_from_db() {

    $data = $_GET['dat'];

    if ( isset($data['table_name']) ) {

        $db = get_db();

        $table = $db->get_name() . '.' . $db->get_company() . '_' . $data['table_name'];
        $table_history = $db->get_name() . '_history.' . $db->get_company() . '_' . $data['table_name'];

        $sql = "DROP TABLE " . $table;
        $sql2 = "DROP TABLE " . $table_history;

        $res = exec_query($sql);
        $res2 = exec_query($sql2);

        if ($res && $res2) {
            $msg = sprintf("The table <code>%s</code> was properly deleted.", $data['table_name']);
            $ret = array("msg" => $msg, "status" => true, "log"=>$sql);
            init_db(); // refreh
            return json_encode($ret);
        } else {
            return json_encode(array("msg"=>"There was an error, please try again.", "status"=>false, "log"=>$sql));
        }

    } else {
            return json_encode(array("msg"=>"There was an error, please try again.", "status"=>false, "log"=>$sql));
    }


}









/* Function called by AJAX when user attempts to add table


Parameters:
===========
- $_GET['dat'] : obj of form data (key: col name, val: value)
                 at a minimum will have the following keys:
                 - table_name (safe name)
                 - name-1 (field name)
                 - type-1 (field type)
- $_GET['field_num'] : number of fields being added

*/
function add_table_to_db() {

    $db = get_db();

    $data = $_GET['dat'];
    $field_num = $_GET['field_num'];

    $uid_field = ' UID int NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT \'{"column_format": "hidden"}\''; // unique field for table if needed

    // ensure table name is only letters
    if (preg_match('/^[a-z0-9-_]+$/i', $data['table_name'])) {
        $table_name = $db->get_name() . '.' . $db->get_company() . '_' . $data['table_name'];
        $table_name_history = $db->get_name() . '_history.' . $db->get_company() . '_' . $data['table_name'];
    } else {
        return json_encode(array("msg" => 'Only letters, numbers, underscores and dashes are allowed in the table name.', "status" => false)); 
    }

    $binds = array();

    // construct SQL for table by checking each field
    $has_uid = false; // if table has unique ID
    $fields = array(); // list of fields for table
    $history_fields = array(); // list of fields for history table counterpart
    for ($i = 1; $i <= $field_num; $i++) {
        $tmp_sql = '';
        $field_name = $data['name-' . $i];
        $field_default = isset($data['default-' . $i]) ? $data['default-' . $i] : false;
        $field_current = isset($data['currentDate-' . $i]) ? $data['currentDate-' . $i] : false;
        $field_required = isset($data['required-' . $i]) ? $data['required-' . $i] : false;
        $field_unique = isset($data['unique-' . $i]) ? $data['unique-' . $i] : false;

        $field_current ? $field_default = true : null;

        // ensure field name is only alphanumeric
        if (!preg_match('/^[a-z0-9 .\-_]+$/i', $field_name)) {
            return json_encode(array("msg" => "Only letters, numbers, spaces, underscores and dashes are allowed in the field name; please adjust the field <code>$field_name</code>.", "status" => false)); 
        }

        // ensure default field is only alphanumeric
        if ($field_default && !preg_match('/^[a-z0-9 .\-_]+$/i', $field_default)) {
            return json_encode(array("msg" => "Only letters, numbers, spaces, underscores and dashes are allowed as a default value; please adjust the default value <code>$field_default</code>.", "status" => false));
        }

        $field_type = $data['type-' . $i];

        // date field type cannot have default current_date,
        // so we change the type to timestamp
        // and leave a note in the comment field
        $comment = false;
        if ($field_current && $field_type == 'date') {
            $field_type = 'timestamp';
            $comment .=' COMMENT \'{"column_format": "date"}\'';
        } elseif ($field_type == 'fk') { // foreign key cannot have a default value
            $field_default = false;
        }

        if ($field_type == 'int') {
            $tmp_sql .= " `$field_name` int(32)";
        } else if ($field_type == 'varchar') {
            if ($field_unique) { // a unique field will create an index which is limited to 767 bytes (191 * 4)
                $tmp_sql .= " `$field_name` varchar(191)";
            } else {
                $tmp_sql .= " `$field_name` varchar(4096)";
            }
        } else {
            $tmp_sql .= " `$field_name` $field_type";
        }

        if ($comment) {
            $tmp_sql .= $comment;
        }

        $field_required ? $tmp_sql .= " NOT NULL" : null;

        if ($field_default) {
            $field_type == 'timestamp' ? $tmp_sql .= " DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP" : $tmp_sql .= " DEFAULT '$field_default'";
        }

        $field_unique ? $tmp_sql .= " UNIQUE" : null;
  
        array_push($fields, $tmp_sql); 
        array_push($history_fields, str_replace(' UNIQUE','', $tmp_sql) ); // only the manually added UID field can be unique
 
        $field_unique && $field_required ? $has_uid = true : null; // set flag if unique field found
        if ($i == $field_num) {

            array_push($history_fields, $uid_field);  // add a UID field to the history table manually
            array_push($history_fields, " User varchar(56) NOT NULL"); // add a field for user

            if (!$has_uid) { // if no field has been set as unique, create one
                array_push($fields, $uid_field);
            }
        }

    
    } 
    $sql = "CREATE TABLE $table_name ( " . implode(',', $fields) . " )";
    $sql2 = "CREATE TABLE $table_name_history ( " . implode(',', $history_fields) . " )";

    $res = exec_query($sql);
    $res2 = exec_query($sql2);

    if ($res && $res2) {
        $msg = sprintf("The table <code>%s</code> was properly created.", $data['table_name']);
        $ret = array("msg" => $msg, "status" => true, "log"=>$sql);
        init_db(); // refresh so that table will show up in menu
        return json_encode($ret);
    } else {
        return json_encode(array("msg"=>"There was an error, please try again.", "status"=>false, "log"=>$sql));
    }


}



/* Put together and execute prepared statement

Parameters:
===========
- $stms : str
          statement with ? vars to be bound
- $args : assoc array of col:value in which
          vals will be bound to '?'
- $types : str 
           string with length equal to number
           of variables to bind, representing
           variable type.  e.g. if 3 strings,
           then 'sss'

Returns:
========
- true if successful query execute, returns error string otherwise

*/
// https://edorian.github.io/2011-05-12-References-suck-lets-fix-mysqli-prepared-statements/
function prepare_statement($stmt, $args, $types) {

    $conn = connect_db();
    $field_vals = array_values($args);

    if ( !( $statement = $conn->prepare($stmt) ) ) {
        return "Prepare failed: (" . $conn->errno . ") " . $conn->error . '; ' . $stmt;
    }

    // Skipped error handling for readability
    $argumentCount = count($field_vals);

    if($statement->param_count == $argumentCount) {

        // Now we need to call 'bind_param'
        // 'bind_param' is a procedure and the only way to call a procedure with a variable number of field_vals is call_user_func_array
        // BUT WE NEED TO CALL IT WITH REFERENCES!
        $callArgs = array();
        foreach($field_vals as $index => $arg) {
            $callArgs[$index] = &$field_vals[$index]; // :(
        }
        
        array_unshift($callArgs, $types );

        // Now bind the parameters
        call_user_func_array(array($statement, 'bind_param'), $callArgs);

        // Now we can execute the statement, finally
        if ( !$statement->execute() ) {
            return "SQL error: " . $statement->error . '; ' . $stmt;
        } else {
            return true;
        }

    } else {
        return false;
    }

}




/* Ensure item can be added to DB

This function will run all the necessarry error
checks on an item within a table.  It will check
any unique constraint, as well as not null and
foreign keys

Parameters:
===========
- $table : str
           table name
- $dat : assoc array of fields to validate
         key - column name, value - value

Returns:
========
- true if item has no errors, error str otherwise

*/
function validate_item_in_table($table, $dat) {

    $db = get_db();
    $table_class = $db->get_table($table);
    $fields = $table_class->get_fields();
    foreach($fields as $field) {
        if (array_key_exists($field, $dat) ) { // only check errors on provided fields in $dat

            $field_class = $table_class->get_field($field);

            // check unique constraint
            if ( $field_class->is_unique() ) {
                $unique_vals = $field_class->get_unique_vals();
                $check_val = $dat[$field];
                if ( $unique_vals && in_array( $check_val, $unique_vals ) ) {
                    return sprintf("The field <code>%s</code> is unique and already contains the value <code>%s</code>", $field, $check_val);
                }
            }

            // TODO validate types (date)


            // check not null (required) constraint
            if ( $field_class->is_required() ) {
                if ( !$dat[$field] ) {
                    return sprintf("The field <code>%s</code> is required, please specify a value.", $field);
                }
            }
        
            // check foreign key constraint
            // XXX probably don't need to do this since the form was populated with a dropdown
            if ( $field_class->is_fk() ) {
                $fk_vals = $field_class->get_fks();
                if ( !( in_array( $dat[$field], $fk_vals ) ) ) {
                    return sprintf("The field <code>%s</code> is a foreign key and must be one of the following: %s.", $field, implode(', ', $fk_vals) );
                }
            }
        }
    }

    return true;
}






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

function err_msg($msg, $debug=DEBUG) {

    if ($debug) {
        echo $msg;
    }

}










?>
