<?php


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














?>
