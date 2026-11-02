<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$categoryId = $_GET['id'] ?? 0;
$category = $pdo->query("SELECT * FROM category WHERE categoryID = $categoryId")->fetch();

if (!$category) {
    header("Location: /categories.php");
    exit();
}

$pageTitle = $category['categoryname'];

// Pagination
$perPage = 12;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Get products in this category
$stmt = $pdo->prepare("
    SELECT * FROM product 
    WHERE categoryID = ? AND quantityINstock > 0
    ORDER BY dataAdded DESC
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
$stmt->bindValue(2, $perPage, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM product WHERE categoryID = ? AND quantityINstock > 0");
$totalStmt->execute([$categoryId]);
$totalProducts = $totalStmt->fetchColumn();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo htmlspecialchars($category['categoryname']); ?></h1>
        <a href="./categories.php" class="btn btn-outline-secondary">All Categories</a>
    </div>

    <?php if (!empty($category['Description'])): ?>
        <p class="lead"><?php echo htmlspecialchars($category['Description']); ?></p>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">No products found in this category.</div>
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
                            <p class="card-text">Rs. <?php echo number_format($product['unitprice'], 2); ?></p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="./product.php?id=<?php echo $product['productID']; ?>"
                                        class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </div>
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
                        <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $page - 1; ?>">
                            Previous
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= ceil($totalProducts / $perPage); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < ceil($totalProducts / $perPage)): ?>
                    <li class="page-item">
                        <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $page + 1; ?>">
                            Next
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;

            fetch('/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        productId: productId,
                        quantity: 1
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast
                        const toast = document.createElement('div');
                        toast.className = 'position-fixed bottom-0 end-0 p-3';
                        toast.innerHTML = `
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Success</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            Product added to cart!
                        </div>
                    </div>
                `;
                        document.body.appendChild(toast);

                        // Remove toast after 3 seconds
                        setTimeout(() => {
                            toast.remove();
                        }, 3000);

                        // Update cart count if function exists
                        if (typeof updateCartCount === 'function') {
                            updateCartCount();
                        }
                    } else {
                        alert(data.message || 'Error adding to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding to cart');
                });
        });
    });

    // Add to wishlist functionality
    document.querySelectorAll('.add-to-wishlist').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;

            fetch('/add_to_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        productId: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast
                        const toast = document.createElement('div');
                        toast.className = 'position-fixed bottom-0 end-0 p-3';
                        toast.innerHTML = `
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Success</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            Product added to wishlist!
                        </div>
                    </div>
                `;
                        document.body.appendChild(toast);

                        // Remove toast after 3 seconds
                        setTimeout(() => {
                            toast.remove();
                        }, 3000);
                    } else {
                        alert(data.message || 'Error adding to wishlist');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding to wishlist');
                });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?><?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$categoryId = $_GET['id'] ?? 0;
$category = $pdo->query("SELECT * FROM category WHERE categoryID = $categoryId")->fetch();

if (!$category) {
    header("Location: /categories.php");
    exit();
}

$pageTitle = $category['categoryname'];

// Pagination
$perPage = 12;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Get products in this category
$stmt = $pdo->prepare("
    SELECT * FROM product 
    WHERE categoryID = ? AND quantityINstock > 0
    ORDER BY dataAdded DESC
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
$stmt->bindValue(2, $perPage, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM product WHERE categoryID = ? AND quantityINstock > 0");
$totalStmt->execute([$categoryId]);
$totalProducts = $totalStmt->fetchColumn();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo htmlspecialchars($category['categoryname']); ?></h1>
        <a href="./categories.php" class="btn btn-outline-secondary">All Categories</a>
    </div>

    <?php if (!empty($category['Description'])): ?>
        <p class="lead"><?php echo htmlspecialchars($category['Description']); ?></p>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">No products found in this category.</div>
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
                            <p class="card-text">$<?php echo number_format($product['unitprice'], 2); ?></p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="./product.php?id=<?php echo $product['productID']; ?>"
                                        class="btn btn-sm btn-outline-primary">View</a>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-success add-to-cart"
                                            data-product-id="<?php echo $product['productID']; ?>">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary add-to-wishlist"
                                            data-product-id="<?php echo $product['productID']; ?>">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
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
                        <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $page - 1; ?>">
                            Previous
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= ceil($totalProducts / $perPage); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < ceil($totalProducts / $perPage)): ?>
                    <li class="page-item">
                        <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $page + 1; ?>">
                            Next
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;

            fetch('/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        productId: productId,
                        quantity: 1
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast
                        const toast = document.createElement('div');
                        toast.className = 'position-fixed bottom-0 end-0 p-3';
                        toast.innerHTML = `
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Success</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            Product added to cart!
                        </div>
                    </div>
                `;
                        document.body.appendChild(toast);

                        // Remove toast after 3 seconds
                        setTimeout(() => {
                            toast.remove();
                        }, 3000);

                        // Update cart count if function exists
                        if (typeof updateCartCount === 'function') {
                            updateCartCount();
                        }
                    } else {
                        alert(data.message || 'Error adding to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding to cart');
                });
        });
    });

    // Add to wishlist functionality
    document.querySelectorAll('.add-to-wishlist').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;

            fetch('/add_to_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        productId: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast
                        const toast = document.createElement('div');
                        toast.className = 'position-fixed bottom-0 end-0 p-3';
                        toast.innerHTML = `
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Success</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            Product added to wishlist!
                        </div>
                    </div>
                `;
                        document.body.appendChild(toast);

                        // Remove toast after 3 seconds
                        setTimeout(() => {
                            toast.remove();
                        }, 3000);
                    } else {
                        alert(data.message || 'Error adding to wishlist');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding to wishlist');
                });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?><?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$categoryId = $_GET['id'] ?? 0;
$category = $pdo->query("SELECT * FROM category WHERE categoryID = $categoryId")->fetch();

if (!$category) {
    header("Location: /categories.php");
    exit();
}

$pageTitle = $category['categoryname'];

// Pagination
$perPage = 12;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Get products in this category
$stmt = $pdo->prepare("
    SELECT * FROM product 
    WHERE categoryID = ? AND quantityINstock > 0
    ORDER BY dataAdded DESC
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
$stmt->bindValue(2, $perPage, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM product WHERE categoryID = ? AND quantityINstock > 0");
$totalStmt->execute([$categoryId]);
$totalProducts = $totalStmt->fetchColumn();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo htmlspecialchars($category['categoryname']); ?></h1>
        <a href="./categories.php" class="btn btn-outline-secondary">All Categories</a>
    </div>

    <?php if (!empty($category['Description'])): ?>
        <p class="lead"><?php echo htmlspecialchars($category['Description']); ?></p>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">No products found in this category.</div>
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
                            <p class="card-text">$<?php echo number_format($product['unitprice'], 2); ?></p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="./product.php?id=<?php echo $product['productID']; ?>"
                                        class="btn btn-sm btn-outline-primary">View</a>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-success add-to-cart"
                                            data-product-id="<?php echo $product['productID']; ?>">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary add-to-wishlist"
                                            data-product-id="<?php echo $product['productID']; ?>">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
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
                        <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $page - 1; ?>">
                            Previous
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= ceil($totalProducts / $perPage); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < ceil($totalProducts / $perPage)): ?>
                    <li class="page-item">
                        <a class="page-link" href="?id=<?php echo $categoryId; ?>&page=<?php echo $page + 1; ?>">
                            Next
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;

            fetch('/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        productId: productId,
                        quantity: 1
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast
                        const toast = document.createElement('div');
                        toast.className = 'position-fixed bottom-0 end-0 p-3';
                        toast.innerHTML = `
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Success</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            Product added to cart!
                        </div>
                    </div>
                `;
                        document.body.appendChild(toast);

                        // Remove toast after 3 seconds
                        setTimeout(() => {
                            toast.remove();
                        }, 3000);

                        // Update cart count if function exists
                        if (typeof updateCartCount === 'function') {
                            updateCartCount();
                        }
                    } else {
                        alert(data.message || 'Error adding to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding to cart');
                });
        });
    });

    // Add to wishlist functionality
    document.querySelectorAll('.add-to-wishlist').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;

            fetch('/add_to_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        productId: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast
                        const toast = document.createElement('div');
                        toast.className = 'position-fixed bottom-0 end-0 p-3';
                        toast.innerHTML = `
                    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header bg-success text-white">
                            <strong class="me-auto">Success</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            Product added to wishlist!
                        </div>
                    </div>
                `;
                        document.body.appendChild(toast);

                        // Remove toast after 3 seconds
                        setTimeout(() => {
                            toast.remove();
                        }, 3000);
                    } else {
                        alert(data.message || 'Error adding to wishlist');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding to wishlist');
                });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>