<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['Role'] !== 'System_admin') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Order Management";

// Handle order actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $orderId = $_GET['id'] ?? null;

    if ($orderId) {
        switch ($action) {
            case 'update_status':
                $newStatus = $_POST['status'];
                $pdo->prepare("UPDATE `orders` SET status = ? WHERE orderID = ?")
                    ->execute([$newStatus, $orderId]);
                $_SESSION['message'] = "order status updated successfully";
                break;

            case 'view':
                // View handled in the page content
                break;
        }
    }
}

// Get all orders with customer info
$orders = $pdo->query("
    SELECT o.*, c.firstname, c.lastname, c.Email
    FROM `orders` o
    JOIN customer c ON o.CustomerID = c.CustomerID
    ORDER BY o.orderdate DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Order Management</h2>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message'];
                                            unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['orderID']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($order['firstname'] . ' ' . $order['lastname']); ?>
                                    <br><small><?php echo htmlspecialchars($order['Email']); ?></small>
                                </td>
                                <td><?php echo formatDate($order['orderDate']); ?></td>
                                <td>Rs.<?php echo number_format($order['totalAmount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadge($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="?action=view&id=<?php echo $order['orderID']; ?>"
                                            class="btn btn-sm btn-outline-danger">
                                            View
                                        </a>
                                        <button class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#statusModal"
                                            data-order-id="<?php echo $order['orderID']; ?>"
                                            data-current-status="<?php echo $order['status']; ?>">
                                            Update Status
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="?action=update_status&id=" id="statusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<?php if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])): ?>
    <?php
    $orderId = $_GET['id'];
    $order = $pdo->query("
        SELECT o.*, c.firstname, c.lastname, c.Email, c.phonenumber, c.shippingaddress
        FROM `orders` o
        JOIN customer c ON o.CustomerID = c.CustomerID
        WHERE o.orderID = $orderId
    ")->fetch();

    $orderItems = $pdo->query("
        SELECT oi.*, p.productname, p.imageURl
        FROM orderitem oi
        JOIN product p ON oi.productID = p.productID
        WHERE oi.orderID = $orderId
    ")->fetchAll(PDO::FETCH_ASSOC);

    $payment = $pdo->query("SELECT * FROM payment WHERE orderID = $orderId")->fetch();
    ?>

    <div class="modal fade show" id="orderDetailModal" tabindex="-1" aria-modal="true" role="dialog" style="display: block; padding-right: 17px;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order #<?php echo $order['orderID']; ?></h5>
                    <a href="?" class="btn-close"></a>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p>
                                <?php echo htmlspecialchars($order['firstname'] . ' ' . $order['lastname']); ?><br>
                                <?php echo htmlspecialchars($order['Email']); ?><br>
                                <?php echo htmlspecialchars($order['phonenumber']); ?>
                            </p>

                            <h6 class="mt-4">Shipping Address</h6>
                            <p><?php echo nl2br(htmlspecialchars($order['shippingAddress'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Order Details</h6>
                            <p>
                                <strong>Date:</strong> <?php echo formatDate($order['orderDate']); ?><br>
                                <strong>Status:</strong>
                                <span class="badge bg-<?php echo getStatusBadge($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span><br>
                                <strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['paymentMethod'])); ?>
                            </p>

                            <?php if ($payment): ?>
                                <h6 class="mt-4">Payment Information</h6>
                                <p>
                                    <strong>Amount:</strong> $<?php echo number_format($payment['amount'], 2); ?><br>
                                    <strong>Date:</strong> <?php echo formatDate($payment['paymentDate']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['imageURl']): ?>
                                                    <img src="<?php echo htmlspecialchars($item['imageURl']); ?>"
                                                        style="width: 50px; height: 50px; object-fit: cover;"
                                                        class="me-3">
                                                <?php endif; ?>
                                                <div><?php echo htmlspecialchars($item['productname']); ?></div>
                                            </div>
                                        </td>
                                        <td>Rs. <?php echo number_format($item['unitprice'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Rs. <?php echo number_format($item['subtotal'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Subtotal:</th>
                                    <td>Rs. <?php echo number_format($order['totalAmount'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <td>Rs. <?php echo number_format($order['totalAmount'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="?" class="btn btn-secondary">Close</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var statusModal = document.getElementById('statusModal');
        statusModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var orderId = button.getAttribute('data-order-id');
            var currentStatus = button.getAttribute('data-current-status');

            document.getElementById('statusForm').action = "?action=update_status&id=" + orderId;
            document.getElementById('status').value = currentStatus;
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>