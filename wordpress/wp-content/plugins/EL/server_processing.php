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
        return false;
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
        $msg = sprintf("The item %s was properly archived.", $id);
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

    $table = $_GET['table'];
    $dat = $_GET['dat'];

    // ERROR CHECK ITEM
    if ( isset( $table ) && isset( $dat ) ) {
        $msg = validate_item_in_table($table, $dat);

        if ($msg === true) {
            return json_encode(array("msg" => "Item successfully added to LIMS", "status" => true));
        } else { // error
            return json_encode(array("msg" => $msg, "status" => false));
        }
    } else {
        return json_encode(array("msg" => 'There ws an error, please try again.', "status" => false));
    }

}


/* Function called by AJAX when user edits item in modal button

Parameters:
===========
- $_POST[

*/
function edit_item_to_db() {

    // TODO

    return;

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
- $dat : assoc array
         key - column name, value - value

Returns:
========
- true if item has no errors, error str otherwise

*/
function validate_item_in_table($table, $dat) {

    $db = get_db();
    $table_class = $db->get_table($table);


    // check unique constraint
    $unique_fields = $table_class->get_unique();
    foreach($unique_fields as $field) {
        $field_class = $table_class->get_field($field);
        $unique_vals = $field_class->get_unique_vals();
        $check_val = $dat[$field];
        if ( in_array( $check_val, $unique_vals ) ) {
            return sprintf("The field <code>%s</code> is unique and already contains the value <code>%s</code>", $field, $check_val);
        }
    }

    // check not null (required) constraint



    // check foreign key constraint
    // XXX probably don't need to do this since the form was populated with a dropdown



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
