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

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['productId']) || !isset($data['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$productId = (int)$data['productId'];
$quantity = (int)$data['quantity'];
$userId = (int)$_SESSION['user_id'];

try {
    // Begin transaction
    $pdo->beginTransaction();

    // 1. Verify product exists and has sufficient stock
    $stmt = $pdo->prepare("SELECT quantityINstock FROM product WHERE productID = ? FOR UPDATE");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception("Product not found");
    }

    if ($product['quantityINstock'] < $quantity) {
        throw new Exception("Not enough stock available");
    }

    // 2. Get customer ID from user ID
    $stmt = $pdo->prepare("SELECT c.CustomerID 
                          FROM customer c
                          JOIN user u ON c.username = u.username
                          WHERE u.userID = ?");
    $stmt->execute([$userId]);
    $customer = $stmt->fetch();

    if (!$customer) {
        throw new Exception("Customer not found for this user");
    }
    $customerId = $customer['CustomerID'];

    // 3. Check for existing cart item
    $stmt = $pdo->prepare("SELECT cartID, quantity FROM cart WHERE CustomerID = ? AND productID = ?");
    $stmt->execute([$customerId, $productId]);
    $existingItem = $stmt->fetch();

    if ($existingItem) {
        // Update existing item
        $newQuantity = $existingItem['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, lastupdated = NOW() WHERE cartID = ?");
        $stmt->execute([$newQuantity, $existingItem['cartID']]);
    } else {
        // Add new item
        $stmt = $pdo->prepare("INSERT INTO cart (CustomerID, productID, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$customerId, $productId, $quantity]);
    }


    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Product added to cart',
        'newQuantity' => $existingItem ? $newQuantity : $quantity
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database error in add_to_cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error in add_to_cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}