<?php
$host = ".";
$myname = ".";
$password = "...";
$database = ".";

$connection = new mysqli($host, $myname, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
?>