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
user and store it in $_SESSION['db'].

This function is hooked into wp_login:
will automatically be called once 
logged in.

Parameters:
- none

Returns:
- will return true and set $_SESSION['db']
  if user is logged in and has an associated
  company with their profile; otherwise
  returns false.

*/

function init_db() {

    $comp = get_company();
    if ( isset( $comp ) ) {
    
        $_SESSION['db'] = new Database( $comp );        
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

Main function for building table that
will display results of DB query.  Will
do all the HTML rendering as well as
do the AJAX call to the server.

Function expects GET['table'] to be set
with the safe name of the table (e.g.
table name without the prepended company
name).

*/
function build_table() {

    include_once("modals.php");

    // ensure we have our data
    if ( !isset( $_SESSION['db'] ) || $_SESSION['db'] == NULL ) {
        init_db();
    }

    $db = $_SESSION['db'];
    if ( isset( $_GET['table'] ) ) {
        $table = $db->get_company() . "_" . $_GET['table']; // GET should pass safe name of table
    }

    if ( isset( $db ) && isset( $table ) && $db->get_table( $table ) ) {
    
        $fields = $db->get_fields($table);

        $html = '<table class="table table-bordered table-hover" id="datatable">';
        $html .= '<thead>';
        $html .= '<tr class="info">';

        foreach ( $fields as $field ) {
            $html .= sprintf('<th>%s</th>', $field); 
        }

        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '</table>';

        echo $html;
        ?>
        <script type="text/javascript">
            // This will do the AJAX call, func defined in js/table.js
            var table = <?php echo json_encode($table); ?>;
            var columns = <?php echo json_encode($fields); ?>;
            var pk = <?php echo json_encode($db->get_pk($table)); ?>;
            getData(table, columns, pk);
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



function exec_query($sql, $conn=null) {

    if (!$conn) {
        $conn = connect_db();
    }

    if ($conn) {
        $res = $conn->query($sql);
        if (!$res) {
            err_msg("Error running query: " . $sql, DEBUG);
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
include_once("server_processing.php");
add_action( 'wp_ajax_viewTable', 'viewTable_callback' );
function viewTable_callback() {

    echo get_data_from_db(); // defined in server_processing.php

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














