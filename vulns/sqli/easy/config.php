<?php
// Database configuration for SQL Injection Easy Lab
// INTENTIONALLY VULNERABLE - DO NOT use in production

$db_host = 'localhost';
$db_user = 'cyberrange';
$db_pass = 'cr_lab_pass';
$db_name = 'cyberrange';

// Connect to database
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
