<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
$pageTitle = "Home";

// Get featured products
try {
    $stmt = $pdo->query("SELECT * FROM product WHERE quantityINstock > 0 ORDER BY dataAdded DESC LIMIT 8");
    $featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}
?>

<div class="hero-section mb-5" style = "background:#F8F9FA;">
    <div class="row align-items-center">
        <div class="col-md-6">
            <span class="hero-eyebrow">New Collection</span>
            <h1>Timeless sarees, made for you</h1>
            <p class="lead">Discover handcrafted designs in silk, georgette and zari — curated for every celebration.</p>
            <a href="./shop.php" class="btn btn-danger btn-lg px-4">Shop Now →</a>
        </div>
        <div class="col-md-6">
            <img src="https://media.istockphoto.com/id/157774583/photo/ethnic-clothing.jpg?s=612x612&w=0&k=20&c=8YpdlGNBDxmm-vweuQOnIIv51XPEvNpfv0XVMDngb_8=" alt="Hero Image" class="img-fluid rounded">
        </div>
    </div>
</div>

<h2 class="mb-4">Featured Collection</h2>
<div class="row">
    <?php foreach ($featuredProducts as $product): ?>
        <div class="col-6 col-md-3 mb-4">
            <div class="card h-100">
                <img src="assets/images/products/<?php echo htmlspecialchars($product['imageURl']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['productname']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['productname']); ?></h5>
                    <p class="card-text">Rs.<?php echo number_format($product['unitprice'], 2); ?></p>
                    <a href="./product.php?id=<?php echo $product['productID']; ?>" class="btn btn-danger">View Details</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>