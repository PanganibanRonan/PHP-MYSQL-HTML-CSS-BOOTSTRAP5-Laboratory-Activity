
<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "php_db";

$conn = new mysqli("localhost", "root", "", "php_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
