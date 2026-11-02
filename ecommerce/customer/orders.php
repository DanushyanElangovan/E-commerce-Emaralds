<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

$customer = $pdo->query("SELECT CustomerID FROM customer WHERE username = '{$_SESSION['username']}'")->fetch();
$customerId = $customer['CustomerID'];

$pageTitle = "My Orders";

// Get customer orders
$orders = $pdo->query("
    SELECT * FROM `orders` 
    WHERE CustomerID = $customerId 
    ORDER BY orderdate DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2 class="my-4">My Orders</h2>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">You haven't placed any orders yet.</div>
        <a href="http://localhost:8080/ecommerce/shop.php" class="btn btn-danger">Start Shopping</a>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['orderID']; ?></td>
                            <td><?php echo formatDate($order['orderDate']); ?></td>
                            <td>
                                <?php
                                $itemCount = $pdo->query("SELECT COUNT(*) FROM orderitem WHERE orderID = {$order['orderID']}")->fetchColumn();
                                echo $itemCount;
                                ?>
                            </td>
                            <td>Rs. <?php echo number_format($order['totalAmount'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo getStatusBadge($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>