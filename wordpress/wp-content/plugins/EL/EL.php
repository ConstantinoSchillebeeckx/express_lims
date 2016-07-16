<?php
/**
* Plugin Name: Express Lims
* Description: TODO
* Version: 1.0
* Author: Constantino Schilebeeckx
* Author URI: https://photoscs.wordpress.com/
 */

/*

- TODO

*/

require_once("config/db.php");
require_once("classes.php");


/* Load the DB structure

This should be the first step for
when a user logs in.  It will load
the overal structure of the database
for the company associated with the
user and store it in $GLOBALS['db'].

This function is hooked into wp_login:
will automatically be called once 
logged in.

Parameters:
- none

Returns:
- will return true and set $GLOBALS['db']
  if user is logged in and has an associated
  company with their profile; otherwise
  returns false.

*/

function init_db() {

    $comp = get_company();
    if ( isset( $comp ) ) {
    
        $GLOBALS['db'] = new Database( $comp );        
        return true;

    }
    return false;
}
add_action('wp_login', 'init_db');






/* returns company of currently logged in user

Returns:
- 'company' meta for logged in user, otherwise false

*/
function get_company() {

    global $current_user;
    if ( isset( $current_user ) ) {
        $comp = get_user_meta( $current_user->ID, 'company', true );
    }

    if ( isset( $comp ) ) {
        return $comp;
    } else {
        return false;
    }

}




/* Build table HTML for view

*/
function build_table() {

    // ensure we have our data
    if ( !isset( $GLOBALS['db'] ) || $GLOBALS['db'] == NULL ) {
        init_db();
    }

    $table = $_GET['table'];
    $db = $GLOBALS['db'];

    if ( isset( $db ) && isset( $table ) && $db->get_table( $table ) ) {
    
        $fields = $db->get_fields($table);

        $html = '<table class="table table-bordered table-hover" id="datatable">';
        $html .= '<thead>';
        $html .= '<tr class="info">';

        foreach ( $fields as $field ) {
            $html .= sprintf('<th>%s</th>', $field); 
        }

        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '</table>';

        echo $html;

        ?>
        <script type="text/javascript">
            // This will do the AJAX call, func defined in js/table.js
            var table = <?php echo json_encode($table); ?>;
            var columns = <?php echo json_encode($fields); ?>;
            getData(table, columns);
        </script>
        <?php

    } else {
        echo 'Table doesnt exist';
    }


}


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





/* Process data from AJAX call

Function is called every time an AJAX call is 
made for data for viewing the DB.  This is done
when the function build_table() is called [in
EL.php]

Function assumes that the following are passed:
- $_GET['table']
- $_GET['columns']

*/
add_action( 'wp_ajax_viewTable', 'viewTable_callback' );
function viewTable_callback() {

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

    $aColumns = $_GET['columns'];
      
    // Indexed column (used for fast and accurate table cardinality)
    $sIndexColumn = 'id';

      
    // Database connection information
    $gaSql['user']     = DB_USER_EL;
    $gaSql['password'] = DB_PASS_EL;
    $gaSql['db']       = DB_NAME_EL;
    $gaSql['server']   = DB_HOST_EL;

    // Input method (use $_GET, $_POST or $_REQUEST)
    $input =& $_GET;
     
    /** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP server-side, there is
     * no need to edit below this line
     */
     
    /**
     * Character set to use for the MySQL connection.
     * MySQL will return all strings in this charset to PHP (if the data is stored correctly in the database).
     */
    $gaSql['charset']  = 'utf8';
     
    /**
     * MySQL connection
     */
    $db = new mysqli($gaSql['server'], $gaSql['user'], $gaSql['password'], $gaSql['db']);
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
        SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", $aQueryColumns)."`
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
      
    echo json_encode( $output );

    wp_die(); // this is required to terminate immediately and return a proper response
}




















/*------------------------------------*\
	Custom User Roles
\*------------------------------------*/

/*
	One new role is created in order
	to reduce ambiguity regarding user
	roles.  A user will be able both to
	post a task as well as apply to them.
	This prevents people from creating
	multiple users.
	- user: same roles as Author

	The following roles are removed
	to reduce confusion:
	- Subscriber
	- Contributor
	- Author
    - Editor


*/
function yti_add_roles_on_plugin_activation() {
	add_role( 'user', 'User', array(
		'read' => true,
		'delete_posts' => true,
		'delete_published_posts' => true,
		'edit_posts' => true,
		'edit_published_posts' => true,
		'publish_posts' => true,
		'upload_files' => false,
	) );
	remove_role( 'subscriber' );
	remove_role( 'author' );
	remove_role( 'editor' );
	remove_role( 'contributor' );
	update_option('default_role', 'user');
}
register_activation_hook( __FILE__, 'yti_add_roles_on_plugin_activation' );












// Load conditional scripts
function EL_conditional_scripts() {

    if (is_page('view')) {
        wp_enqueue_style('datatables', 'https://cdn.datatables.net/v/bs/dt-1.10.12/r-2.1.0/datatables.min.css');
        wp_enqueue_script('datatables', 'https://cdn.datatables.net/v/bs/dt-1.10.12/r-2.1.0/datatables.min.js');
        wp_enqueue_script('table', plugin_dir_url( __FILE__ ) . '/js/table.js', array('jquery'));
        wp_localize_script( 'table', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }

}
add_action('wp_print_scripts', 'EL_conditional_scripts'); // Add Conditional Page Scripts














