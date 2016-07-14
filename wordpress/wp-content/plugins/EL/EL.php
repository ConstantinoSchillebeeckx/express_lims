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


