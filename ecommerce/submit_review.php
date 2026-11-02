<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['productID']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    $customerId = $_SESSION['user_id'];

    // Validate input
    $errors = [];

    if ($rating < 1 || $rating > 5) {
        $errors[] = "Please select a valid rating (1-5 stars)";
    }

    if (empty($comment)) {
        $errors[] = "Please write your review comment";
    }

    if (empty($errors)) {
        try {
            // Check if customer already reviewed this product
            $stmt = $pdo->prepare("SELECT reviewID FROM productreview WHERE productID = ? AND CustomerID = ?");
            $stmt->execute([$productId, $customerId]);

            if ($stmt->fetch()) {
                $_SESSION['review_error'] = "You've already reviewed this product";
                header("Location: ./product.php?id=$productId");
                exit();
            }

            // Insert new review
           $stmt = $pdo->prepare("INSERT INTO productreview 
                      (productID, CustomerID, rating, comment)
                      VALUES (?, ?, ?, ?)");
            $stmt->execute([$productId, $customerId, $rating, $comment]);

            $_SESSION['review_success'] = "Thank you for your review! It will be visible after approval.";
            header("Location: ./product.php?id=$productId");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    // If we got here, there were errors
    $_SESSION['review_errors'] = $errors;
    $_SESSION['review_form_data'] = [
        'rating' => $rating,
        'comment' => $comment
    ];
    header("Location: ./product.php?id=$productId");
    exit();
}

// If someone tries to access this page directly
header("Location: ./");
exit();
