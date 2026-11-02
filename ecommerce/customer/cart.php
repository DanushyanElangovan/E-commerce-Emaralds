<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Shopping Cart";
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];
$customerId = null;
$cartItems = [];
$subtotal = 0;
$tax = 0;
$total = 0;
$shippingEstimate = 0;
$discountAmount = 0;

try {
    // Get customer ID using prepared statement
    $stmt = $pdo->prepare("SELECT CustomerID FROM customer WHERE username = ?");
    $stmt->execute([$username]);
    $customer = $stmt->fetch();

    if (!$customer) {
        throw new Exception("Customer not found");
    }

    $customerId = $customer['CustomerID'];

    // Get the most recent active cart for this customer
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE CustomerID = ? ORDER BY lastupdated DESC LIMIT 1");
    $stmt->execute([$customerId]);
    $cart = $stmt->fetch();

    if ($cart) {
        $cartId = $cart['cartID'];
        $shippingEstimate = $cart['shippingestimate'];
        $discountAmount = $cart['discountamount'];

        // Get cart items with product details
        $stmt = $pdo->prepare("SELECT ci.cartID, ci.productID, ci.quantity, 
                              p.productname, p.unitprice, p.imageURL, p.quantityInStock 
                              FROM cart ci
                              JOIN product p ON ci.productID = p.productID
                              WHERE ci.CustomerID = ?");
        $stmt->execute([$customerId]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate totals
        foreach ($cartItems as $item) {
            $itemTotal = $item['unitprice'] * $item['quantity'];
            $subtotal += $itemTotal;
        }

        // Use cart's tax amount if exists, otherwise calculate
        $tax = !empty($cart['taxamount']) ? $cart['taxamount'] : $subtotal * 0.08;

        // Calculate final total
        $total = $subtotal + $tax + $shippingEstimate - $discountAmount;
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h4>Your Cart</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php elseif (empty($cartItems)): ?>
                    <div class="alert alert-info">Your cart is empty</div>
                    <a href="../shop.php" class="btn btn-danger">Continue Shopping</a>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
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
                                                    <small>Stock: <?php echo $item['quantityInStock']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Rs. <?php echo number_format($item['unitprice'], 2); ?></td>
                                        <td>
                                            <div class="input-group" style="width: 120px;">
                                                <!-- <button class="btn btn-outline-secondary update-qty"
                                                    data-action="decrease"
                                                    data-cartid="<?php echo $cartId; ?>"
                                                    data-productid="<?php echo $item['productID']; ?>">-</button> -->
                                                <input type="number" class="form-control text-center qty-input"
                                                    value="<?php echo $item['quantity']; ?>"
                                                    min="1"
                                                    max="<?php echo $item['quantityInStock']; ?>" readonly>
                                                <!-- <button class="btn btn-outline-secondary update-qty"
                                                    data-action="increase"
                                                    data-cartid="<?php echo $cartId; ?>"
                                                    data-productid="<?php echo $item['productID']; ?>">+</button> -->
                                            </div>
                                        </td>
                                        <td>Rs. <?php echo number_format($item['unitprice'] * $item['quantity'], 2); ?></td>
                                        <td>
                                            <a href="./remove_cart_item.php?cartid=<?php echo $cartId; ?>" class=" btn btn-danger btn-sm remove-item">
                                                <i class="bi bi-trash"></i> Remove
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($cartItems)): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Order Summary</h4>
                </div>
                <div class="card-body">
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
                    <a href="checkout.php" class="btn btn-danger w-100 mt-3">Proceed to Checkout</a>
                    <a href="../shop.php" class="btn btn-outline-secondary w-100 mt-2">Continue Shopping</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>


<?php require_once '../includes/footer.php'; ?>