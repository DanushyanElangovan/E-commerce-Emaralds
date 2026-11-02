<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check admin access
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Product Management";

// Handle product actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $productId = $_GET['id'] ?? null;

    switch ($action) {
        case 'delete':
            if ($productId) {
                $pdo->prepare("DELETE FROM product WHERE productID = ?")->execute([$productId]);
                $_SESSION['message'] = "Product deleted successfully";
                header("Location: ./products.php");
                exit();
            }
            break;
    }
}

// Get all products with category and supplier info
$products = $pdo->query("
    SELECT p.*, c.categoryname, s.companyname as suppliername
    FROM product p
    LEFT JOIN category c ON p.categoryID = c.categoryID
    LEFT JOIN supplier s ON p.supplierid = s.supplierid
    ORDER BY p.dataAdded DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        @media (max-width: 991.98px) {

            /* Stack table rows vertically on mobile */
            .responsive-table {
                border: 0;
                width: 100%;
            }

            .responsive-table thead {
                display: none;
            }

            .responsive-table tr {
                display: block;
                margin-bottom: 1.5rem;
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            }

            .responsive-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem;
                border-bottom: 1px solid #eee;
                text-align: right;
                position: relative;
                padding-left: 50%;
            }

            .responsive-table td:before {
                content: attr(data-label);
                font-weight: bold;
                position: absolute;
                left: 0.75rem;
                width: 45%;
                padding-right: 1rem;
                text-align: left;
                white-space: nowrap;
            }

            .responsive-table td:last-child {
                border-bottom: 0;
            }

            /* Hide empty cells */
            .responsive-table td:empty {
                display: none;
            }

            .badge {
                font-size: 0.9em;
            }
        }

        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 0.25rem;
        }

        .action-btns .btn {
            margin-right: 0.3rem;
        }

        .action-btns .btn:last-child {
            margin-right: 0;
        }
    </style>
</head>

<body>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2><i class="bi bi-box-seam"></i> <?php echo htmlspecialchars($pageTitle); ?></h2>
            </div>
            <div class="col-md-6 text-end">
                <a href="../admin/add_product.php" class="btn btn-danger">
                    <i class="bi bi-plus-lg"></i> Add Product
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover responsive-table">
                        <thead class="d-none d-lg-table-header-group">
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Supplier</th>
                                <th>Added On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td data-label="ID"><?php echo $product['productID']; ?></td>

                                    <td data-label="Image">
                                        <?php if ($product['imageURl']): ?>
                                            <img src="../assets/images/products/<?php echo htmlspecialchars($product['imageURl']); ?>"
                                                alt="<?php echo htmlspecialchars($product['productname']); ?>"
                                                class="product-img">
                                        <?php else: ?>
                                            <div class="text-muted">No image</div>
                                        <?php endif; ?>
                                    </td>

                                    <td data-label="Product Name"><?php echo htmlspecialchars($product['productname']); ?></td>

                                    <td data-label="Category"><?php echo $product['categoryname'] ?? 'N/A'; ?></td>

                                    <td data-label="Price">Rs.<?php echo number_format($product['unitprice'], 2); ?></td>

                                    <td data-label="Stock">
                                        <span class="badge bg-<?php echo $product['quantityINstock'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $product['quantityINstock']; ?> in stock
                                        </span>
                                    </td>

                                    <td data-label="Supplier"><?php echo $product['suppliername'] ?? 'N/A'; ?></td>

                                    <td data-label="Added On"><?php echo date('M d, Y', strtotime($product['dataAdded'])); ?></td>

                                    <td data-label="Actions" class="action-btns">
                                        <div class="btn-group">
                                            <a href="products.php?action=delete&id=<?php echo $product['productID']; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete this product?')"
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirm before deleting
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this product?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>

<?php require_once '../includes/footer.php'; ?>