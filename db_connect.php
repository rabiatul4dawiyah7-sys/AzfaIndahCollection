<?php

$host = "fdb1030.awardspace.net";
$username = "4782428_azfaindah";
$password = "9Q6iP7#;AU@-,!";
$database = "4782428_azfaindah";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>