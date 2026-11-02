<?php
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = 'ecommerce_db';
$username = 'root';
$password = '';

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Set the PDO error mode to exception (corrected constant)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Prevent emulated prepared statements
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Set character encoding
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
