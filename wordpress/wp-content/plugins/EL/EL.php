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
        $table_class = $db->get_table($table);
    }


    // build filter for use with AJAX
    if ( isset( $_GET['filter'] ) ) {
        $filter = array();
        $filter_raw = explode( ',', $_GET['filter'] );
        $filter[ $filter_raw[0] ] = $filter_raw[1];
        echo "<small class='text-muted'>Table is currently only showing $filter_raw[0] = '$filter_raw[1]'</small>";
    }

    // generate table HTML
    if ( isset( $db ) && isset( $table ) && $table_class != null ) {
    
        $fields = $db->get_fields($table); 
        $hidden = $table_class->get_hidden_fields();
        ?>
        
        <table class="table table-bordered table-hover" id="datatable">
        <thead>
        <tr class="info">

        <?php foreach ( $fields as $field ) echo "<th>$field</th>"; ?>

        <th>Action</th>
        </tr>
        </thead>
        </table>

        <script type="text/javascript">
            // This will do the AJAX call, func defined in js/table.js
            var table = <?php echo json_encode( $table ); ?>;
            var columns = <?php echo json_encode( $fields ); ?>;
            var pk = <?php echo json_encode( $db->get_pk( $table ) ); ?>;
            var filter = <?php echo json_encode( $filter ); ?>;
            var hidden = <?php echo json_encode( $hidden ); ?>;
            getData(table, columns, pk, filter, hidden);
        </script>

    <?php } else {
        echo 'Table doesn\'t exist; list of available tables are: ' . implode(', ', $db->get_tables());
        var_dump($_SESSION['db']);
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

// edit item (row) to db
add_action( 'wp_ajax_editItem', 'editItem_callback' );
function editItem_callback() {

    echo edit_item_in_db(); // defined in server_processing.php

    wp_die(); // this is required to terminate immediately and return a proper response
}

// add table to db
add_action( 'wp_ajax_addTable', 'addTable_callback' );
function addTable_callback() {

    echo add_table_to_db(); // defined in server_processing.php

    wp_die(); // this is required to terminate immediately and return a proper response
}

// add table to db
add_action( 'wp_ajax_deleteTable', 'deleteTable_callback' );
function deleteTable_callback() {

    echo delete_table_from_db(); // defined in server_processing.php

    wp_die(); // this is required to terminate immediately and return a proper response
}

// add table to db
add_action( 'wp_ajax_editTable', 'editTable_callback' );
function editTable_callback() {

    echo edit_table(); // defined in server_processing.php

    wp_die(); // this is required to terminate immediately and return a proper response
}



/* Generates a dropbown with available values for a foreign key

A foreign key field must take on a value from the table and
column that it references.  This function will generate the
HTML for a select dropdown that is filled with those
available column values.

Parameters:
===========
- $field_class : Field class
                 the field (assumed to be an FK) for which to find
                 the available column values for

Returns:
========
- HTML for a select dropdown if the field is an FK; if field is not
  an FK or if the reference table/column doesn't exist, nothing
  is returned.

*/
function get_fks_as_select($field_class) {
        $fks = $field_class->get_fks(); // get the available values
        $name = $field_class->get_name();
        $ref_id = $field_class->get_fk_field(); // get the field the FK references

        if ( isset($fks) && isset($ref_id) ) {
            echo '<select class="form-control" id="' . $name . '" name="' . $name . '">';
            foreach ($fks as $fk) {
                echo sprintf("<option value='%s'>%s</option>", $fk, $fk);
            }
            echo '</select>';
        }
}









/* Build a form of inputs based on a table row

When either adding a new item or editing a table row item,
a modal appears that should be filled with inputs for each
table field.  This function will generate a form that has
proper inputs for each of these fields.  If the field is
an FK, a select will be generated, otherwise an input
box is shown.  For any field that is automatically populated
(e.g. datetime), the input will be disabled and a note will
be displayed.

Parameters:
===========
- $table : str
           table name for which to generate input fields


Return:
=======
- will generate (echo) all the proper HTML which should
  be placed within a form

*/
function get_form_table_row($table) {

    $db = get_db();
    $table_class = $db->get_table($table);
    $fields = $table_class->get_fields();

    forEach($fields as $field) {

        $field_class = $db->get_field($table, $field);
        $field_type = $field_class->get_type();
        $comment = $field_class->get_comment();
    
        if ($comment['column_format'] != 'hidden') {

            if ( preg_match('/float|int/', $field_type) ) {
                $type = 'number';
            } elseif ( $field_type == 'date') {
                $type = 'date';
            } elseif ( $field_type == 'datetime') {
                $type = 'datetime';
            } else {
                $type = 'text';
            }
            ?>

            <div class="form-group">

                <?php if ($field_class->is_required()) {
                    echo '<label class="col-sm-2 control-label">' . $field . '<span class="required">*</span></label>';
                } else {
                    echo '<label class="col-sm-2 control-label">' . $field . '</label>';
                } ?>

                <div class="col-sm-10">

                <?php if ( $field_class->is_fk() ) {  // if field is an fk, show a select dropdown with available values
                    get_fks_as_select($field_class);
                } else {
                    if ( in_array( $field_class->get_type(), array('datetime', 'date') ) && $field_class->get_default() ) {
                        echo "<input type='$type' id='$field' name='$field' class='form-control' disabled></input><small class='text-muted'>Field has been disabled since it populates automatically</small>";
                    } elseif ($field_class->is_required()) {
                        echo "<input type='$type' id='$field' name='$field' class='form-control' required>";
                    } else {
                        echo "<input type='$type' id='$field' name='$field' class='form-control'>";
                    }
                } ?>

                </div>
            </div>
    <?php    }
     } ?>

    <p class="text-right"><span class="required">*</span> field is required</p>
<?php }









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

    wp_enqueue_script('table', plugin_dir_url( __FILE__ ) . '/js/table.js', array('jquery'));
    wp_localize_script( 'table', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

    if (is_page('view')) {
        wp_enqueue_style('datatables', 'https://cdn.datatables.net/v/bs/dt-1.10.12/r-2.1.0/datatables.min.css');
        wp_enqueue_script('datatables', 'https://cdn.datatables.net/v/bs/dt-1.10.12/r-2.1.0/datatables.min.js');
    }

}
add_action('wp_print_scripts', 'EL_conditional_scripts'); // Add Conditional Page Scripts




// Load plugin styles (should be loaded after theme style sheet)
function EL_styles()
{
    wp_enqueue_style('EL_style', plugin_dir_url( __FILE__ ) . '/css/styles.css');
}
add_action('wp_enqueue_scripts', 'EL_styles'); // Add plugin stylesheet








