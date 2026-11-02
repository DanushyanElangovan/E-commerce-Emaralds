<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Redirect if not logged in or no order ID provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ./login.php");
    exit();
}

$pageTitle = "Order Confirmation";
$orderId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$orderData = [];
$orderItems = [];
$customerData = [];
$error = '';

try {
    // Verify the order belongs to the current user
    $stmt = $pdo->prepare("
        SELECT o.*, c.firstname, c.lastname, c.email, c.phonenumber 
        FROM orders o
        JOIN customer c ON o.CustomerID = c.CustomerID
        WHERE o.orderID = ? AND c.username = ?
    ");
    $stmt->execute([$orderId, $username]);
    $orderData = $stmt->fetch();

    if (!$orderData) {
        throw new Exception("Order not found or doesn't belong to you");
    }

    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.productname, p.imageURL 
        FROM order_items oi
        JOIN product p ON oi.productID = p.productID
        WHERE oi.orderID = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get customer data
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE username = ?");
    $stmt->execute([$username]);
    $customerData = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-check-circle-fill"></i> Order Confirmation</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php else: ?>
                        <div class="text-center mb-4">
                            <div class="display-1 text-success mb-3">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <h2>Thank You for Your Order!</h2>
                            <p class="lead">Your order #<?php echo $orderId; ?> has been placed successfully.</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5>Order Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Order Number:</strong> #<?php echo $orderId; ?></p>
                                        <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($orderData['orderDate'])); ?></p>
                                        <p><strong>Status:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($orderData['status']); ?></span></p>
                                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($orderData['paymentMethod']); ?></p>
                                        <p><strong>Total Amount:</strong> Rs. <?php echo number_format($orderData['totalAmount'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5>Customer Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>
                                            <strong>Name:</strong>
                                            <?php echo htmlspecialchars($customerData['firstname'] . ' ' . $customerData['lastname']); ?>
                                        </p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($customerData['Email']); ?></p>
                                        <p><strong>Phone:</strong>
                                        <?php
                                            if($orderData['phonenumber'] != NULL){
                                                echo nl2br(htmlspecialchars($customerData['phonenumber']));
                                            }
                                            else{
                                                echo " ";
                                            }
                                             ?>
                                        <p>
                                            <strong>Shipping Address:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($orderData['shippingAddress'])); ?>
                                        </p>

                                        <p>
                                            <strong>Billing Address:</strong><br>
                                            <?php
                                            if($orderData['billingAddress'] != NULL){
                                                echo nl2br(htmlspecialchars($orderData['billingAddress']));
                                            }
                                            else{
                                                echo nl2br(htmlspecialchars($orderData['shippingAddress']));
                                            }
                                             ?>
                                        </p>
                                        <!-- <?php if ($orderData['shippingAddress'] !== $orderData['billingAddress']): ?>
                                            <p>
                                                <strong>Billing Address:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($orderData['billingAddress'])); ?>
                                            </p>
                                        <?php endif; ?> -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Order Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orderItems as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!empty($item['imageURL'])): ?>
                                                                <img src="../assets/images/products/<?php echo htmlspecialchars($item['imageURL']); ?>"
                                                                    alt="<?php echo htmlspecialchars($item['productname']); ?>"
                                                                    style="width: 50px; height: 50px; object-fit: cover;"
                                                                    class="me-3">
                                                            <?php endif; ?>
                                                            <div>
                                                                <h6><?php echo htmlspecialchars($item['productname']); ?></h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>Rs. <?php echo number_format($item['unitPrice'], 2); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td>Rs. <?php echo number_format($item['unitPrice'] * $item['quantity'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5>Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <th>Subtotal</th>
                                        <td class="text-end">
                                            Rs. <?php echo number_format($orderData['totalAmount'] - $orderData['taxAmount'] - $orderData['shippingAmount'] + $orderData['discountAmount'], 2); ?>
                                        </td>
                                    </tr>
                                    <?php if ($orderData['discountAmount'] > 0): ?>
                                        <tr>
                                            <th>Discount</th>
                                            <td class="text-end text-danger">
                                                - Rs. <?php echo number_format($orderData['discountAmount'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th>Tax</th>
                                        <td class="text-end">
                                            Rs. <?php echo number_format($orderData['taxAmount'], 2); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Shipping</th>
                                        <td class="text-end">
                                            Rs. <?php echo number_format($orderData['shippingAmount'], 2); ?>
                                        </td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <th>Total</th>
                                        <td class="text-end">
                                            Rs. <?php echo number_format($orderData['totalAmount'], 2); ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="../shop.php" class="btn btn-danger">Continue Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>