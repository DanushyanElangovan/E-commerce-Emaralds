<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM user WHERE userID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$_SESSION['cust_username'] = $cust_username = htmlspecialchars($user['username']);
$ctmt = $pdo->prepare("SELECT * FROM customer WHERE username = ?");
$ctmt->execute([$cust_username]);
$customer = $ctmt->fetch(PDO::FETCH_ASSOC);


$pageTitle = "My Account";
require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <a href="http://localhost:8080/ecommerce//customer/account.php" class="list-group-item list-group-item-action active">Profile</a>
            <a href="http://localhost:8080/ecommerce//customer/orders.php" class="list-group-item list-group-item-action">My Orders</a>
            <a href="http://localhost:8080/ecommerce//customer/wishlist.php" class="list-group-item list-group-item-action">Wishlist</a>
            <a href="http://localhost:8080/ecommerce//customer/cart.php" class="list-group-item list-group-item-action">Shopping Cart</a>
            <a href="http://localhost:8080/ecommerce//logout.php" class="list-group-item list-group-item-action">Logout</a>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h3>My Profile</h3>
            </div>
            <div class="card-body">
                <form action="./update_profile.php" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['Firstname']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['Lastname']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phonenumber" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phonenumber" name="phonenumber" value="<?php echo htmlspecialchars($user['Phonenumber']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="shippingaddress" class="form-label">Shipping Address</label>
                        <?php
                        if (htmlspecialchars($customer['shippingaddress']) != NULL) {
                        ?>
                            <textarea class="form-control" id="shippingaddress" name="shippingaddress" rows="3"><?php echo htmlspecialchars($customer['shippingaddress']); ?></textarea>
                        <?php
                        } else {
                        ?>
                            <textarea class="form-control" id="shippingaddress" name="shippingaddress" rows="3"></textarea>

                        <?php
                        }
                        ?>
                    </div>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password (leave blank to keep unchanged)</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password (leave blank to keep unchanged)</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    <button type="submit" class="btn btn-danger">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>