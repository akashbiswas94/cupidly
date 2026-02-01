<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "valentine";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed");
}
