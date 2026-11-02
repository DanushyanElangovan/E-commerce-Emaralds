<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage your cart']);
    exit();
}

// Validate input
if (!isset($_GET['cartid'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$cartid = $_GET['cartid'];

$stmt = $pdo->prepare("DELETE FROM cart WHERE cartID = ?");
$stmt->execute([$cartid]);
header("Location:../shop.php");
