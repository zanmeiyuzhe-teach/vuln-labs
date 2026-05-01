<?php
// Database configuration for CSRF Labs
$host = 'localhost';
$dbname = 'cyberrange';
$username = 'cyberrange';
$password = 'cr_lab_pass';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
