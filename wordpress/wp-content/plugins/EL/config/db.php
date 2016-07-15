<?php
/**
 * Configuration for: Database Connection
 *
 * For more information about constants please @see http://php.net/manual/en/function.define.php
 * If you want to know why we use "define" instead of "const" @see http://stackoverflow.com/q/2447791/1114320
 *
 * DB_HOST: database host, usually it's "127.0.0.1" or "localhost", some servers also need port info
 * DB_NAME: name of the database. please note: database and database table are not the same thing
 * DB_USER: user for your database. the user needs to have rights for SELECT, UPDATE, DELETE and INSERT.
 * DB_PASS: the password of the above user
 */
define("DB_HOST_EL", "internal-db.s215537.gridserver.com");
define("DB_NAME_EL", "db215537_EL");
define("DB_USER_EL", "db215537_el");
define("DB_PASS_EL", "K3r7mS#SMxJvDgo@dyvxv\$LeooQXN8!$");


define("DEBUG", true); // if false, some erorr message won't be printed
define("HIDDEN",""); // comma separated list of general table fields that should be hidden from view
