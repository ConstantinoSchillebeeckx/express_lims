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
include_once("server_processing.php");







/*------------------------------------*\
	    Wordpress side functions
\*------------------------------------*/

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


    $db = get_db();

    if ( isset( $_GET['table'] ) ) {
        $table = $db->get_company() . "_" . $_GET['table']; // GET should pass safe name of table
    }

    // build filter for use with AJAX
    $filter = array();
    if ( isset( $_GET['filter'] ) ) {
        $filter_raw = explode( ',', $_GET['filter'] );
        $filter[ $filter_raw[0] ] = $filter_raw[1];
    }

    // generate table HTML
    if ( isset( $db ) && isset( $table ) && $db->get_table( $table ) ) {
    
        $fields = $db->get_fields($table); ?>
        
        <div class="row">
            <div class="col-sm-12">
                <button class="btn btn-info btn-xs" onclick="addItemModal()">New item</button>
            </div>   
        </div>   

        <table class="table table-bordered table-hover" id="datatable">
        <thead>
        <tr class="info">

        <?php foreach ( $fields as $field ) {
            echo sprintf('<th>%s</th>', $field); 
        } ?>

        <th>Action</th>
        </tr>
        </thead>
        </table>

        <script type="text/javascript">
            // This will do the AJAX call, func defined in js/table.js
            var table = <?php echo json_encode($table); ?>;
            var columns = <?php echo json_encode($fields); ?>;
            var pk = <?php echo json_encode($db->get_pk($table)); ?>;
            var filter = <?php echo json_encode($filter); ?>;
            getData(table, columns, pk, filter);
        </script>

    <?php } else {
        echo 'Table doesnt exist';
    }

    // must be included after table vars are defined
    include_once("modals.php");
}


/* Helper function for getting DB structure

When called, function will check if DB structure
has been loaded into $_SESSION; if not it will do
so.

Returns:
========
- Database class object

*/
function get_db() {

    // ensure we have our data
    if ( !isset( $_SESSION['db'] ) || $_SESSION['db'] == NULL ) {
        init_db();
    }

    return $_SESSION['db'];
}



/* Setup PHP functions called by AJAX on the font end

Since using wordpress, we have to register each PHP
function that will be handling an AJAX request.

All of these functions are defined in server_processing.php

*/

// main function to query DB for viewing data
add_action( 'wp_ajax_viewTable', 'viewTable_callback' );
function viewTable_callback() {

    echo get_data_from_db(); // defined in server_processing.php

    wp_die(); // this is required to terminate immediately and return a proper response
}

// delete item (row) from db
add_action( 'wp_ajax_deleteItem', 'deleteItem_callback' );
function deleteItem_callback() {

    echo delete_item_from_db(); // defined in server_processing.php

    wp_die(); // this is required to terminate immediately and return a proper response
}

// add item (row) to db
add_action( 'wp_ajax_addItem', 'addItem_callback' );
function addItem_callback() {

    echo add_item_to_db(); // defined in server_processing.php

    wp_die(); // this is required to terminate immediately and return a proper response
}



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




// Load plugin styles (should be loaded after theme style sheet)
function EL_styles()
{
    wp_enqueue_style('EL_style', plugin_dir_url( __FILE__ ) . '/css/styles.css');
}
add_action('wp_enqueue_scripts', 'EL_styles'); // Add plugin stylesheet








