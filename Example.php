<?php
require "DBAPI.class.php";
$db = new DBAPI("localhost", "root", "password", "database");

$res = $db->query("SELECT * FROM `table` WHERE 1");
foreach($res as $row)
{
    // $row is set for each dataset in the table `table`
}

$res = $db->query("SELECT * FROM `table` WHERE `this`=?", "s", "that");
       // The second argument is for "string" and the following argument is a string to be replaced with the question mark (?)
// $res is an array of all rows matching the query

$res = $db-query("SELECT * FROM `table` WHERE `string`=? AND `int`=?", "si", "string", "1");
       // In this case the second argument states 'the following two arguments are string (s) and int (i) respectively'
       // The string ("string") and int (1) are to be placed in the query where the question marks (?) have been set
       // This parsing of string and int excludes user data from any SQL execution and thereby blocks any injection attempts
// $res again is an array of all rows matching the query
