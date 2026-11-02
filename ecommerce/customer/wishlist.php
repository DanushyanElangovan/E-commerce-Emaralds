<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

$pageTitle = "My Wishlist";

try {
    // Get customer ID - safer prepared statement
    $stmt = $pdo->prepare("SELECT CustomerID FROM customer WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $customer = $stmt->fetch();

    if (!$customer) {
        throw new Exception("Customer not found");
    }

    $customerId = $customer['CustomerID'];

    // Get wishlist items - using your actual table structure
    $stmt = $pdo->prepare("
        SELECT p.productID, p.productname, p.unitprice, p.imageURl, p.quantityINstock,
               w.wishlistID, w.datacreated as dateadded
        FROM wishlist w
        JOIN product p ON w.productID = p.productID
        WHERE w.CustomerID = ?
        ORDER BY w.datacreated DESC
    ");
    $stmt->execute([$customerId]);
    $wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $wishlist = [];
    echo "<div class='alert alert-danger'>Error loading wishlist. Please try again later.</div>";
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $wishlist = [];
    echo "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Wishlist</h2>
        <a href="http://localhost:8080/ecommerce/shop.php" class="btn btn-outline-danger">Continue Shopping</a>
    </div>

    <?php if (empty($wishlist)): ?>
        <div class="alert alert-info">Your wishlist is empty.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($wishlist as $item): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="../assets/images/products/<?php echo htmlspecialchars($item['imageURl']); ?>"
                            class="card-img-top"
                            alt="<?php echo htmlspecialchars($item['productname']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['productname']); ?></h5>
                            <p class="card-text">Rs. <?php echo number_format($item['unitprice'], 2); ?></p>

                            <div class="d-flex justify-content-between">
                                <a href="../product.php?id=<?php echo $item['productID']; ?>"
                                    class="btn btn-sm btn-danger">View</a>

                                <button class="btn btn-sm btn-danger remove-wishlist"
                                    data-wishlist-id="<?php echo $item['wishlistID']; ?>">
                                    Remove
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            Added <?php echo formatDate($item['dateadded'], 'M d, Y'); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Remove from wishlist
        document.querySelectorAll('.remove-wishlist').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Remove this item from your wishlist?')) {
                    const wishlistId = this.dataset.wishlistId;

                    fetch('../remove_from_wishlist.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                wishlistId
                            }),
                            credentials: 'include'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('success', data.message);
                                this.closest('.col-md-3').remove();

                                // If no items left, show empty message
                                if (document.querySelectorAll('.col-md-3').length === 0) {
                                    document.querySelector('.row').innerHTML = `
                                        <div class="alert alert-info">Your wishlist is empty.</div>
                                    `;
                                }
                            } else {
                                showToast('error', data.message || 'Error removing item');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('error', 'Failed to remove item');
                        });
                }
            });
        });

        // Toast notification function
        function showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `toast show align-items-center text-white bg-${type} border-0`;
            toast.style.position = 'fixed';
            toast.style.bottom = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>