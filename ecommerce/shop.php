<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$pageTitle = "Shop All Products";

// Pagination
$perPage = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Get total product count
$totalProducts = $pdo->query("SELECT COUNT(*) FROM product WHERE quantityINstock > 0")->fetchColumn();

// Search functionality
$searchCondition = '';
if (isset($_GET["search_product"]) && !empty($_GET["search_product"])) {
    $searchTerm = '%' . $_GET["search_product"] . '%';
    $searchCondition = " AND productname LIKE :search_term";
}

// Get products with pagination
$sql = "SELECT * FROM product WHERE quantityINstock > 0 $searchCondition ORDER BY dataAdded DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

if (!empty($searchCondition)) {
    $stmt->bindValue(':search_term', $searchTerm, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>All Products</h2>
        </div>
        <div class="col-md-6 text-end">
            <form class="d-flex" action="" method="GET">
                <input type="text" name="search_product" class="form-control me-2"
                    placeholder="Search products..."
                    value="<?php echo isset($_GET['search_product']) ? htmlspecialchars($_GET['search_product']) : ''; ?>">
                <button class="btn btn-outline-danger" type="submit">Search</button>
            </form>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">No products found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['imageURl']); ?>"
                            class="card-img-top p-3"
                            alt="<?php echo htmlspecialchars($product['productname']); ?>"
                            style="height: 200px; object-fit: contain;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['productname']); ?></h5>
                            <p class="card-text">Rs.<?php echo number_format($product['unitprice'], 2); ?></p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="./product.php?id=<?php echo $product['productID']; ?>"
                                        class="btn btn-sm btn-outline-danger">Details</a>

                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <small class="text-muted">
                                <?php if ($product['quantityINstock'] > 0): ?>
                                    <span class="text-success">In Stock</span>
                                <?php else: ?>
                                    <span class="text-danger">Out of Stock</span>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['search_product']) ? '&search_product=' . urlencode($_GET['search_product']) : ''; ?>"
                            aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= ceil($totalProducts / $perPage); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $i; ?><?php echo isset($_GET['search_product']) ? '&search_product=' . urlencode($_GET['search_product']) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < ceil($totalProducts / $perPage)): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['search_product']) ? '&search_product=' . urlencode($_GET['search_product']) : ''; ?>"
                            aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Event delegation for dynamic elements
        document.addEventListener('click', function(e) {
            // Add to cart handler
            if (e.target.closest('.add-to-cart')) {
                const btn = e.target.closest('.add-to-cart');
                const productId = btn.dataset.productId;
                addToCart(productId);
            }

            // Add to wishlist handler
            if (e.target.closest('.add-to-wishlist')) {
                const btn = e.target.closest('.add-to-wishlist');
                const productId = btn.dataset.productId;
                addToWishlist(productId);
            }
        });

        // Add to cart function
        async function addToCart(productId, quantity = 1) {
            try {
                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `productId=${productId}&quantity=${quantity}`
                });

                const data = await response.json();

                if (data.success) {
                    showToast('success', 'Product added to cart!');
                    if (typeof updateCartCount === 'function') {
                        updateCartCount();
                    }
                } else {
                    showToast('error', data.message || 'Error adding to cart');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('error', 'An error occurred while adding to cart');
            }
        }

        // Add to wishlist function
        async function addToWishlist(productId) {
            try {
                const response = await fetch('add_to_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `productId=${productId}`
                });

                const data = await response.json();

                if (data.success) {
                    showToast('success', 'Product added to wishlist!');
                } else {
                    showToast('error', data.message || 'Error adding to wishlist');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('error', 'An error occurred while adding to wishlist');
            }
        }

        // Toast notification function
        function showToast(type, message) {
            // Create container if it doesn't exist
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.style.position = 'fixed';
                toastContainer.style.bottom = '20px';
                toastContainer.style.right = '20px';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }

            // Create toast
            const toast = document.createElement('div');
            toast.className = `toast show align-items-center text-white bg-${type} border-0 mb-2`;
            toast.style.maxWidth = '100%';
            toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
            toastContainer.appendChild(toast);

            // Auto-remove after 3 seconds
            setTimeout(() => {
                toast.remove();
                // Remove container if no more toasts
                if (toastContainer.children.length === 0) {
                    toastContainer.remove();
                }
            }, 3000);
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>