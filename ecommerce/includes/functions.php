<?php
function getStatusBadge($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
        case 'delivered':
        case 'completed':
            return 'success';
        case 'cancelled':
        case 'rejected':
            return 'danger';
        default:
            return 'secondary';
    }
}

function formatDate($dateString, $format = 'M d, Y')
{
    return date($format, strtotime($dateString));
}

function isAdmin()
{
    return isset($_SESSION['Role']) && $_SESSION['Role'] === 'System_admin';
}

function redirectIfNotLoggedIn()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit();
    }
}

function redirectIfNotAdmin()
{
    redirectIfNotLoggedIn();
    if (!isAdmin()) {
        header("Location: /");
        exit();
    }
}

function addToCart($productId, $quantity, $customerId)
{
    global $pdo;

    // Check if customer has an active cart
    $cart = $pdo->query("SELECT * FROM CART WHERE CustomerID = $customerId ORDER BY lastupdated DESC LIMIT 1")->fetch();

    if (!$cart) {
        // Create new cart
        $pdo->prepare("INSERT INTO CART (CustomerID) VALUES (?)")->execute([$customerId]);
        $cartId = $pdo->lastInsertId();
    } else {
        $cartId = $cart['cartID'];
    }

    // Check if product already in cart
    $existingItem = $pdo->query("SELECT * FROM CARTITEM WHERE cartID = $cartId AND productID = $productId")->fetch();

    if ($existingItem) {
        // Update quantity
        $newQty = $existingItem['quantity'] + $quantity;
        $pdo->prepare("UPDATE CARTITEM SET quantity = ? WHERE cartitemID = ?")
            ->execute([$newQty, $existingItem['cartitemID']]);
    } else {
        // Add new item
        $pdo->prepare("INSERT INTO CARTITEM (cartID, productID, quantity) VALUES (?, ?, ?)")
            ->execute([$cartId, $productId, $quantity]);
    }

    // Update cart totals
    updateCartTotals($cartId);

    return true;
}

function updateCartTotals($cartId)
{
    global $pdo;

    // Calculate subtotal
    $subtotal = $pdo->query("
        SELECT SUM(p.unitprice * ci.quantity) as subtotal
        FROM CARTITEM ci
        JOIN PRODUCT p ON ci.productID = p.productID
        WHERE ci.cartID = $cartId
    ")->fetch()['subtotal'] ?? 0;

    // Update cart
    $pdo->prepare("UPDATE CART SET subtotal = ?, lastupdated = NOW() WHERE cartID = ?")
        ->execute([$subtotal, $cartId]);
}
