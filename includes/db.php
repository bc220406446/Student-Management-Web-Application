<?php
// Database settings for the local XAMPP MySQL server.
$host = 'localhost';
$dbname = 'smwa';
$username = 'root';
$password = '';

try {
    // PDO creates a reusable connection between PHP and MySQL.
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Turn database errors into exceptions so they can be handled safely.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Return query results as arrays with readable column names.
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Stop the page and show a clear message if the connection fails.
    die("Database connection failed: " . $e->getMessage());
}
?>
