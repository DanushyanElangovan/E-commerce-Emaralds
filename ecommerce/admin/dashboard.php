<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['Role'] !== 'System_admin') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Admin Dashboard";

// Get stats for dashboard
$orderStats = $pdo->query("SELECT COUNT(*) as total, 
                          SUM(CASE WHEN Status = 'completed' THEN 1 ELSE 0 END) as completed,
                          SUM(CASE WHEN Status = 'pending' THEN 1 ELSE 0 END) as pending,
                          SUM(CASE WHEN Status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                          FROM `order`")->fetch(PDO::FETCH_ASSOC);

$productStats = $pdo->query("SELECT COUNT(*) as total, 
                            SUM(CASE WHEN quantityINstock > 0 THEN 1 ELSE 0 END) as in_stock,
                            SUM(CASE WHEN quantityINstock = 0 THEN 1 ELSE 0 END) as out_of_stock
                            FROM product")->fetch(PDO::FETCH_ASSOC);

$userStats = $pdo->query("SELECT COUNT(*) as total,
                         SUM(CASE WHEN Role = 'customer' THEN 1 ELSE 0 END) as customers,
                         SUM(CASE WHEN Role != 'customer' THEN 1 ELSE 0 END) as staff
                         FROM user")->fetch(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <p class="card-text display-4"><?php echo $orderStats['total']; ?></p>
                <div class="d-flex justify-content-between">
                    <small>Completed: <?php echo $orderStats['completed']; ?></small>
                    <small>Pending: <?php echo $orderStats['pending']; ?></small>
                    <small>Cancelled: <?php echo $orderStats['cancelled']; ?></small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Products</h5>
                <p class="card-text display-4"><?php echo $productStats['total']; ?></p>
                <div class="d-flex justify-content-between">
                    <small>In Stock: <?php echo $productStats['in_stock']; ?></small>
                    <small>Out of Stock: <?php echo $productStats['out_of_stock']; ?></small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <p class="card-text display-4"><?php echo $userStats['total']; ?></p>
                <div class="d-flex justify-content-between">
                    <small>Customers: <?php echo $userStats['customers']; ?></small>
                    <small>Staff: <?php echo $userStats['staff']; ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Orders</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM `orders` ORDER BY orderdate DESC LIMIT 5");
                        while ($order = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                            <tr>
                                <td><a href="../admin/orders.php?action=view&id=<?php echo $order['orderID']; ?>">#<?php echo $order['orderID']; ?></a></td>
                                <td><?php echo date('M d, Y', strtotime($order['orderDate'])); ?></td>
                                <td><span class="badge bg-<?php echo getStatusBadge($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                                <td>Rs. <?php echo number_format($order['totalAmount'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="./orders.php" class="btn btn-sm btn-outline-danger">View All Orders</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Low Stock Products</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM product WHERE quantityINstock < 10 AND quantityINstock > 0 ORDER BY quantityINstock ASC LIMIT 5");
                        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                            <tr>
                                <td><a href="../product.php?id=<?php echo $product['productID']; ?>"><?php echo $product['productname']; ?></a></td>
                                <td><?php echo $product['quantityINstock']; ?></td>
                                <td>Rs. <?php echo number_format($product['unitprice'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="./products.php" class="btn btn-sm btn-outline-danger">View All Products</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>