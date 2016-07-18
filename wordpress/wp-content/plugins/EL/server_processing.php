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
    require_once("config/db.php");
    $conn = new mysqli(DB_HOST_EL, DB_USER_EL, DB_PASS_EL, DB_NAME_EL);

    if (!$conn->connect_errno) {
        return $conn;
    } else {
        err_msg("Could not connect to database!");
        return false;
    }

}




/* Execute SQL query

Will execute a given SQL query

Parameters:
===========
- sql : sql string
- conn : MYSQLi connection object (optional)

Returns:
========
- false if no connection could be made to DB,
  otherwise returns the results of the query

*/
function exec_query($sql, $conn=null) {

    if (!$conn) {
        $conn = connect_db();
    }

    if ($conn) {
        $res = $conn->query($sql);
        if (!$res) {
            err_msg("Error running query: " . $sql, DEBUG);
            return false;
        } else {
            return $res;
        }
    } else {
        return false;
    }
    $conn.close();
}



/* Process data from AJAX call

Function is called every time an AJAX call is 
made for data for viewing the DB.  This is done
when the function build_table() is called [in
EL.php]

Function assumes that the following are passed:
- $_GET['table']
- $_GET['columns']

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
    mb_internal_encoding('UTF-8');
     
    // DB table to use
    $sTable = $_GET['table'];

    /**
     * Array of database columns which should be read and sent back to DataTables. Use a space where
     * you want to insert a non-database field (for example a counter or static image)
     */
    $aColumns = $_GET['cols'];
      
    // Indexed column (used for fast and accurate table cardinality)
    $sIndexColumn = $_GET['pk'];

    // Input method (use $_GET, $_POST or $_REQUEST)
    $input =& $_GET;
     
    /**
     * Character set to use for the MySQL connection.
     * MySQL will return all strings in this charset to PHP (if the data is stored correctly in the database).
     */
    $gaSql['charset']  = 'utf8';
     
    /**
     * MySQL connection
     */
    $db = connect_db();
    if (mysqli_connect_error()) {
        die( 'Error connecting to MySQL server (' . mysqli_connect_errno() .') '. mysqli_connect_error() );
    }
     
    if (!$db->set_charset($gaSql['charset'])) {
        die( 'Error loading character set "'.$gaSql['charset'].'": '.$db->error );
    }
      
      
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
                $aFilteringRules[] = "`".$aColumns[$i]."` LIKE '%".$db->real_escape_string( $input['sSearch'] )."%'";
            }
        }
        if (!empty($aFilteringRules)) {
            $aFilteringRules = array('('.implode(" OR ", $aFilteringRules).')');
        }
    }
      
    // Individual column filtering
    for ( $i=0 ; $i<$iColumnCount ; $i++ ) {
        if ( isset($input['bSearchable_'.$i]) && $input['bSearchable_'.$i] == 'true' && $input['sSearch_'.$i] != '' ) {
            $aFilteringRules[] = "`".$aColumns[$i]."` LIKE '%".$db->real_escape_string($input['sSearch_'.$i])."%'";
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
     
    $rResult = $db->query( $sQuery ) or die($db->error);
      
    // Data set length after filtering
    $sQuery = "SELECT FOUND_ROWS()";
    $rResultFilterTotal = $db->query( $sQuery ) or die($db->error);
    list($iFilteredTotal) = $rResultFilterTotal->fetch_row();
     
    // Total data set length
    $sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM `".$sTable."`";
    $rResultTotal = $db->query( $sQuery ) or die($db->error);
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
      
    while ( $aRow = $rResult->fetch_assoc() ) {
        $row = array();
        for ( $i=0 ; $i<$iColumnCount ; $i++ ) {
            if ( $aColumns[$i] == 'version' ) {
                // Special output formatting for 'version' column
                $row[] = ($aRow[ $aColumns[$i] ]=='0') ? '-' : $aRow[ $aColumns[$i] ];
            } elseif ( $aColumns[$i] != ' ' ) {
                // General output
                $row[] = $aRow[ $aColumns[$i] ];
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
- $_POST['id'] : name of the item being deleted
- $_POST['table'] : name of table in which item is located
- $_POST['pk'] : column in which item exists


*/
function delete_item_from_db() {

    // get some vars
    $db = get_db();
    $id = $_POST['id'];
    $table = $_POST['table'];
    $pk = $_POST['pk'];
    $table_full_name = $db->get_name() . '.' . $table;

    // delete row
    $sql = sprintf("DELETE FROM %s WHERE `%s` = '%s'", $table_full_name, $pk, $id);
    //if (exec_query($sql)) {
        $msg = sprintf("The item %s was properly archived.", $id);
        $ret = array("msg" => $msg, "status" => true);
    //}


    // update history

    return json_encode($ret);
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
- size : int (optional)
         boostrap col-sm-X width
*/

function err_msg($msg, $debug=true, $size=12) {

    if ($debug) {
        echo '<div class="alert alert-danger col-sm-'. $size .'" role="alert">' . $msg . '</div>';
    }

}












?>
