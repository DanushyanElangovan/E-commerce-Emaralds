<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$wishlistId = (int)$data['wishlistId'];



try {

    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE wishlistID = ?");
    $stmt->execute([$wishlistId]);

    echo json_encode(['success' => true, 'message' => 'Product removed from wishlist']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
