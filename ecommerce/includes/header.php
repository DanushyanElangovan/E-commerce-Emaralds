<?php
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'E-Commerce Site'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ecommerce/assets/css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="http://localhost:8080/ecommerce/">
            Emerald<span>s</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="http://localhost:8080/ecommerce/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="http://localhost:8080/ecommerce/categories.php">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="http://localhost:8080/ecommerce/shop.php">Shop</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        if ($_SESSION['user_id'] != 1) {
                            $cart_userId = $_SESSION['user_id'];

                            $u_cart_tmt = $pdo->prepare("SELECT username FROM user WHERE userID = ?");
                            $u_cart_tmt->execute([$cart_userId]);

                            $cart_users = $u_cart_tmt->fetch();
                            if (!$cart_users) {
                                throw new Exception("User not found");
                            }

                            $cart_customer_username = $cart_users['username'];

                            // Get customer information
                            $c_cart_tmt = $pdo->prepare("SELECT CustomerID FROM customer WHERE username = ?");
                            $c_cart_tmt->execute([$cart_customer_username]);

                            $cart_customers = $c_cart_tmt->fetch();
                            if (!$cart_customers) {
                                throw new Exception("Customer not found for username: " . htmlspecialchars($customer_username));
                            }

                            $cart_customer_id = $cart_customers['CustomerID'];
                            $u_cart_tmt = $pdo->prepare("SELECT COUNT(cartID) as cart_count FROM cart WHERE CustomerID = ?");
                            $u_cart_tmt->execute([$cart_customer_id]);

                            $cart_item_count = $u_cart_tmt->fetch();
                            if (!$cart_item_count) {
                                $cart_item_number = 0;
                            } else {
                                $cart_item_number = $cart_item_count["cart_count"];
                            }
                    ?>
                            <li class="nav-item">
                                <a class="nav-link" href="http://localhost:8080/ecommerce/customer/cart.php">
                                    Cart <span id="cart-count" class="badge bg-danger"><?php echo $cart_item_number; ?></span>
                                </a>
                            </li>
                    <?php
                        }
                    }
                    ?>

                    <?php
                    if (isset($_SESSION['user_id'])) {
                        if ($_SESSION['user_id'] == 1) {
                    ?>

                            <li class="nav-item">
                                <a class="nav-link" href="http://localhost:8080/ecommerce/admin/products.php">
                                    All Products
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="http://localhost:8080/ecommerce/admin/add_category.php">
                                    + New Category
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="http://localhost:8080/ecommerce/admin/users.php">
                                    Users
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="http://localhost:8080/ecommerce/admin/inventory.php">
                                    Inventory
                                </a>
                            </li>

        
                    <?php
                        }
                        elseif($_SESSION['Role'] == "CEO" || $_SESSION['Role'] == "inventory_manager"){
                    ?>
                            <li class="nav-item">
                                <a class="nav-link" href="http://localhost:8080/ecommerce/admin/reports.php">
                                    Reports
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="http://localhost:8080/ecommerce/admin/inventory.php">
                                    Inventory
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="http://localhost:8080/ecommerce/admin/products.php">
                                    Products
                                </a>
                            </li>
                    <?php
                        }
                    }
                    ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                My Account
                            </a>
                            <ul class="dropdown-menu">
                                <?php
                                if ($_SESSION['user_id'] == 1) {
                                    echo '
                                    <li>
                                        <a class="dropdown-item" href="http://localhost:8080/ecommerce/admin/products.php">
                                            All Product
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item" href="http://localhost:8080/ecommerce/admin/add_category.php">
                                            Add New Category
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="dropdown-item" href="http://localhost:8080/ecommerce/admin/users.php">
                                            Users
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    
                                     <li>
                                        <a class="dropdown-item" href="http://localhost:8080/ecommerce/admin/reports.php">
                                            All Reports
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item" href="http://localhost:8080/ecommerce/admin/inventory.php">
                                            Inventory
                                        </a>
                                    </li>

                                    <li><a class="dropdown-item" href="http://localhost:8080/ecommerce/admin/tickets.php">Tickets</a></li>
                                    
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <li><a class="dropdown-item" href="http://localhost:8080/ecommerce/logout.php">Logout</a></li>';
                                }
                                elseif($_SESSION['Role'] == "CEO" || $_SESSION['Role'] == "inventory_manager"){
                                    echo '
                                    <li> <a class="dropdown-item" href="http://localhost:8080/ecommerce/admin/reports.php">Reports</a></li>
                                    <li> <a class="dropdown-item" href="http://localhost:8080/ecommerce/admin/inventory.php">Inventory</a></li>
                                    <li> <a class="dropdown-item" href="http://localhost:8080/ecommerce/admin/products.php">Products</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="http://localhost:8080/ecommerce/logout.php">Logout</a></li>
                                    ';
                                }
                                else {
                                    echo '
                                    <li><a class="dropdown-item" href="http://localhost:8080/ecommerce/customer/account.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="http://localhost:8080/ecommerce/customer/orders.php">Orders</a></li>
                                    <li><a class="dropdown-item" href="http://localhost:8080/ecommerce/customer/wishlist.php">Wishlist</a></li>
                                    <li><a class="dropdown-item" href="http://localhost:8080/ecommerce/customer/tickets.php">support tickets</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="http://localhost:8080/ecommerce/logout.php">Logout</a></li>
                                    ';
                                }
                                ?>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="./login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="./register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
