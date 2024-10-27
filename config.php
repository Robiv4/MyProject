<?php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tip');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$page = $_SERVER['PHP_SELF'];
$conn->query("INSERT INTO page_visits (page, visits) VALUES ('$page', 1) ON DUPLICATE KEY UPDATE visits = visits + 1");