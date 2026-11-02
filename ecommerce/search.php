<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$searchQuery = $_GET['q'] ?? '';
$pageTitle = "Search Results for: " . htmlspecialchars($searchQuery);

// Pagination
$perPage = 12;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Search products
$stmt = $pdo->prepare("SELECT * FROM product 
                      WHERE productname LIKE ? OR Description LIKE ?
                      LIMIT ? OFFSET ?");
$stmt->execute(["%$searchQuery%", "%$searchQuery%", $perPage, $offset]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM product 
                           WHERE productname LIKE ? OR Description LIKE ?");
$totalStmt->execute(["%$searchQuery%", "%$searchQuery%"]);
$totalProducts = $totalStmt->fetchColumn();
?>

<div class="container">
    <h2 class="my-4">Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">No products found matching your search.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($product['imageURl']); ?>"
                            class="card-img-top"
                            alt="<?php echo htmlspecialchars($product['productname']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['productname']); ?></h5>
                            <p class="card-text">$<?php echo number_format($product['unitprice'], 2); ?></p>
                            <a href="/product.php?id=<?php echo $product['productID']; ?>"
                                class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= ceil($totalProducts / $perPage); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < ceil($totalProducts / $perPage)): ?>
                    <li class="page-item">
                        <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>