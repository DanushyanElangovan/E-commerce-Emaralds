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
$userId = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['productId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$utmt = $pdo->prepare("SELECT username FROM user WHERE userID = ?");
$utmt->execute([$userId]);

$users = $utmt->fetch();
if (!$users) {
    throw new Exception("User not found");
}

$customer_username = $users['username'];

// Get customer information
$ctmt = $pdo->prepare("SELECT CustomerID FROM customer WHERE username = ?");
$ctmt->execute([$customer_username]);

$customers = $ctmt->fetch();
if (!$customers) {
    throw new Exception("Customer not found for username: " . htmlspecialchars($customer_username));
}

$customer_id = $customers['CustomerID'];

$productId = (int)$data['productId'];



try {
    $ctmt_already = $pdo->prepare("SELECT * FROM wishlist WHERE CustomerID = ? and productID = ?");
    $ctmt_already->execute([$customer_id, $productId]);
    $already = $ctmt_already->fetch();
    if ($already) {
        echo json_encode(['success' => true, 'message' => 'This product alreay added to wishlist']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO wishlist (CustomerID, productID) VALUES (?, ?)");
        $stmt->execute([$customer_id, $productId]);

        echo json_encode(['success' => true, 'message' => 'Product added to wishlist']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
