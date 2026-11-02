<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

$pageTitle = "Checkout";
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$customerId = null;
$cartItems = [];
$subtotal = 0;
$tax = 0;
$total = 0;
$shippingEstimate = 0;
$discountAmount = 0;
$customerData = [];
$error = '';

try {
    // Get customer information
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE username = ?");
    $stmt->execute([$username]);
    $customer = $stmt->fetch();

    if (!$customer) {
        throw new Exception("Customer not found");
    }

    $customerId = $customer['CustomerID'];
    $customerData = $customer;

    // Get the most recent active cart
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE CustomerID = ? ORDER BY lastupdated DESC LIMIT 1");
    $stmt->execute([$customerId]);
    $cart = $stmt->fetch();

    if (!$cart) {
        throw new Exception("Your cart is empty");
    }

    $shippingEstimate = $cart['shippingestimate'];
    $discountAmount = $cart['discountamount'];

    // Get cart items
    $stmt = $pdo->prepare("
        SELECT ci.cartID, ci.productID, ci.quantity, 
               p.productname, p.unitprice, p.imageURL, p.quantityInStock 
        FROM cart ci
        JOIN product p ON ci.productID = p.productID
        WHERE ci.CustomerID = ?
    ");
    $stmt->execute([$customerId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        throw new Exception("Your cart is empty");
    }

    // Calculate totals
    foreach ($cartItems as $item) {
        $itemTotal = $item['unitprice'] * $item['quantity'];
        $subtotal += $itemTotal;
    }

    $tax = !empty($cart['taxamount']) ? $cart['taxamount'] : $subtotal * 0.08;
    $total = $subtotal + $tax + $shippingEstimate - $discountAmount;

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate and process payment
        $paymentMethod = $_POST['payment_method'] ?? '';
        $shippingAddress = $_POST['shipping_address'] ?? '';
        $billingAddress = $_POST['billing_address'] ?? $shippingAddress;
        $notes = $_POST['notes'] ?? '';

        if (empty($paymentMethod)) {
            throw new Exception("Please select a payment method");
        }

        if (empty($shippingAddress)) {
            throw new Exception("Shipping address is required");
        }

        // Start transaction
        $pdo->beginTransaction();

        try {
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders 
                (CustomerID, orderDate, status, totalAmount, taxAmount, 
                 shippingAmount, discountAmount, paymentMethod, shippingAddress, billingAddress, notes)
                VALUES (?, NOW(), 'Processing', ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $customerId,
                $total,
                $tax,
                $shippingEstimate,
                $discountAmount,
                $paymentMethod,
                $shippingAddress,
                $billingAddress,
                $notes
            ]);
            $orderId = $pdo->lastInsertId();

            // Add order items
            foreach ($cartItems as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items 
                    (orderID, productID, quantity, unitPrice)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $item['productID'],
                    $item['quantity'],
                    $item['unitprice']
                ]);

                // Update product stock
                $stmt = $pdo->prepare("
                    UPDATE product 
                    SET quantityInStock = quantityInStock - ? 
                    WHERE productID = ?
                ");
                $stmt->execute([
                    $item['quantity'],
                    $item['productID']
                ]);
            }

            // Clear the cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE CustomerID = ?");
            $stmt->execute([$customerId]);

            $stmt = $pdo->prepare("DELETE FROM cart WHERE CustomerID = ?");
            $stmt->execute([$customerId]);

            // Commit transaction
            $pdo->commit();

            // Redirect to order confirmation
            header("Location: order_confirmation.php?id=" . $orderId);
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h4>Checkout</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" id="checkoutForm">
                        <h5 class="mb-3">Shipping Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName"
                                    value="<?php echo htmlspecialchars($customerData['firstname'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName"
                                    value="<?php echo htmlspecialchars($customerData['lastname'] ?? ''); ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email"
                                    value="<?php echo htmlspecialchars($customerData['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone"
                                    value="<?php echo htmlspecialchars($customerData['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="shipping_address" class="form-label">Shipping Address</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?php
                                                                                                                                echo htmlspecialchars($customerData['address'] ?? '');
                                                                                                                                ?></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sameBilling">
                                    <label class="form-check-label" for="sameBilling">
                                        Billing address same as shipping
                                    </label>
                                </div>
                            </div>
                            <div class="col-12" id="billingAddressGroup">
                                <label for="billing_address" class="form-label">Billing Address</label>
                                <textarea class="form-control" id="billing_address" name="billing_address" rows="3"><?php
                                                                                                                    echo htmlspecialchars($customerData['address'] ?? '');
                                                                                                                    ?></textarea>
                            </div>
                        </div>

                        <h5 class="mb-3">Payment Method</h5>
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="creditCard" value="Credit Card" checked required>
                                <label class="form-check-label" for="creditCard">
                                    Bank Transfer
                                </label>
                                <small class="text-muted"><br>86xxxxxx<br>T.M.N.T.Thennakoon<br>Bank of ceylon</small>
                            </div>
                            <div class="form-check mt-4">
                                <input class="form-check-input " type="radio" name="payment_method" id="cashOnDelivery" value="Cash on Delivery">
                                <label class="form-check-label" for="cashOnDelivery">
                                    Cash on Delivery
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Order Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>

                        <input type="hidden" name="cart_id" value="<?php echo $cartId; ?>">
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4>Order Summary</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Products</h6>
                        <div class="border-bottom pb-2 mb-2">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <?php echo htmlspecialchars($item['productname']); ?>
                                        <small class="text-muted">x<?php echo $item['quantity']; ?></small>
                                    </div>
                                    <div>Rs. <?php echo number_format($item['unitprice'] * $item['quantity'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <table class="table">
                        <tr>
                            <th>Subtotal</th>
                            <td class="text-end">Rs. <?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php if ($discountAmount > 0): ?>
                            <tr>
                                <th>Discount</th>
                                <td class="text-end text-danger">- Rs. <?php echo number_format($discountAmount, 2); ?></td>
                            </tr>
                        <?php endif; ?>
                        <!-- <tr>
                            <th>Tax (8%)</th>
                            <td class="text-end">Rs. <?php echo number_format($tax, 2); ?></td>
                        </tr> -->
                        <tr>
                            <th>Shipping</th>
                            <td class="text-end">Rs. <?php echo number_format($shippingEstimate, 2); ?></td>
                        </tr>
                        <tr class="fw-bold">
                            <th>Total</th>
                            <td class="text-end">Rs. <?php echo number_format($total, 2); ?></td>
                        </tr>
                    </table>

                    <button type="submit" form="checkoutForm" class="btn btn-danger w-100">Place Order</button>
                    <a href="../shop.php" class="btn btn-outline-secondary w-100 mt-2">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle billing address visibility
        const sameBillingCheckbox = document.getElementById('sameBilling');
        const billingAddressGroup = document.getElementById('billingAddressGroup');

        sameBillingCheckbox.addEventListener('change', function() {
            billingAddressGroup.style.display = this.checked ? 'none' : 'block';
        });

        // Initialize state
        billingAddressGroup.style.display = sameBillingCheckbox.checked ? 'none' : 'block';
    });
</script>

<?php require_once '../includes/footer.php'; ?>