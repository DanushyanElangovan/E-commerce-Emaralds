<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$pageTitle = "Product Categories";
require_once 'includes/header.php';

// Get all categories with product counts
$categories = $pdo->query("
    SELECT c.*, COUNT(p.productID) as product_count
    FROM category c
    LEFT JOIN product p ON c.categoryID = p.categoryID AND p.quantityINstock > 0
    GROUP BY c.categoryID
    ORDER BY c.displayOrder, c.categoryname
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            text-decoration: none;
        }

        .card-title {
            font-size: 16px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="my-4">Shop by Category</h1>

        <?php if (empty($categories)): ?>
            <div class="alert alert-info">No categories found.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h2 class="card-title">
                                    <a href="./category.php?id=<?php echo $category['categoryID']; ?>" class="text-danger">
                                        <?php echo htmlspecialchars($category['categoryname']); ?>
                                    </a>
                                </h2>
                                <p class="card-text"><?php echo htmlspecialchars($category['Description']); ?></p>
                                <span class="badge bg-danger">
                                    <?php echo $category['product_count']; ?> products
                                </span>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="./category.php?id=<?php echo $category['categoryID']; ?>"
                                    class="btn btn-sm btn-outline-danger">
                                    View All
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once 'includes/footer.php'; ?>