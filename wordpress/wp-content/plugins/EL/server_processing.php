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
    if ( isset( $user_filter ) ) {
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
    while ( $aRow = $rResult->fetch_assoc() ) {
        $row = array();
        for ( $i=0 ; $i<$iColumnCount ; $i++ ) {
        
            $col_name = $aColumns[$i];
            $field = $table_class->get_field( $col_name ); // Field class
    
            if ( $field->is_pk() ) { // don't format
                $row[] = $aRow[ $col_name ];
            } else { // format with filter
                $url = sprintf("%s&filter=%s,%s", $_SERVER['HTTP_REFERER'], $col_name, $aRow[ $col_name ]);
                $row[] = '<a href="' . $url . '">' . $aRow[ $col_name ] . '</a>';
            }
        }
        $output['aaData'][] = $row;
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


    // TODO we need to run a check when deleting a primary key that is being referenced as a foreign key

    // delete row
    $sql = sprintf("DELETE FROM %s WHERE `%s` = '%s'", $table_full_name, $pk, $id);
    if (exec_query($sql)) {
        $msg = sprintf("The item <code>%s</code> was properly archived.", $id);
        $status = true;
        $ret = array("msg" => $msg, "status" => $status);
    }


    // update history

    return json_encode($ret);
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
        $msg = validate_item_in_table($table, $dat);

        if ($msg !== true) {
            return json_encode(array("msg" => $msg, "status" => false));
        }

        // prepare statement to add item
        $cols = '`' . implode( '`, `', array_keys($dat) ) . '`';
        $vals = implode(', ', array_pad(array(), count($dat), '?'));
        $table_class = $db->get_table($table);
        $stmt = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table_class->get_full_name(), $cols, $vals);
        $prep = prepare_statement( $stmt, $dat, $table_class);
        if ( $prep ) {
            return json_encode(array("msg" => sprintf('Item <code>%s</code> successfully added to LIMS.', $dat[$pk]), "status" => true ));
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
- $_GET['dat'] : obj of form data (key: col name, val: value)

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
        foreach ($original_row as $field => $original) {
            $new = $dat[$field];
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

            // prepare statement to add item
            $stmt = sprintf('UPDATE %s SET %s WHERE %s', $table_class->get_full_name(), implode(', ', $col_eq), $pk_eq);
            $prep = prepare_statement( $stmt, $edits, $table_class );
            if ( $prep === true ) {
                return json_encode(array("msg" => sprintf('Item <code>%s</code> successfully edited.', $pk_val), "status" => true, 'log'=>array($stmt,$edits,$dat, $original_row, $prep)));
            } else {
                return json_encode(array("msg" => 'There was an error, please try again.', "status" => false, 'log'=>$prep));
            }
        } else {
            return json_encode(array("msg" => 'Values are not any different than current ones, nothing was edited.', "status" => true));
        }
    }
    return json_encode(array("msg" => 'There was an error, please try again.', "status" => false));

}

/* Put together and execute prepared statement

Parameters:
===========
- $stms : str
          statement with ? read to be bound
- $args : assoc array of col:value to parse
          array with values used for binding
- $table_class : Table class

Returns:
========
- true if successful, false otherwise


*/
// https://edorian.github.io/2011-05-12-References-suck-lets-fix-mysqli-prepared-statements/
function prepare_statement($stmt, $args, $table_class) {

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

        // check field type and enforce
        $types = '';
        foreach($args as $field => $field_val) {
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
        
        array_unshift($callArgs, $types );

        // Now bind the parameters
        call_user_func_array(array($statement, 'bind_param'), $callArgs);

        // Now we can execute the statement, finally
        if ( !$statement->execute() ) {
            return false;
        } else {
            //return var_dump($statement);
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
                if ( in_array( $check_val, $unique_vals ) ) {
                    return sprintf("The field <code>%s</code> is unique and already contains the value <code>%s</code>", $field, $check_val);
                }
            }

            // check not null (required) constraint
            if ( $field_class->is_required() ) {
                if ( !(array_key_exists($field, $dat) ) ) {
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
