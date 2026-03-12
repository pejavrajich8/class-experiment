<?php
// db.php - Database connection configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'appuser');       // Change to your MySQL username
define('DB_PASS', 'AppPass123!');   // Change to your MySQL password
define('DB_NAME', 'student_feedback');

function getConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . htmlspecialchars($conn->connect_error));
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
