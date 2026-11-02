<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: /");
    exit();
}

$productId = $_GET['id'];
$stmt = $pdo->prepare("SELECT p.*, c.categoryname 
                      FROM product p 
                      LEFT JOIN category c ON p.categoryID = c.categoryID 
                      WHERE p.productID = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: /");
    exit();
}

$pageTitle = $product['productname'];
require_once 'includes/header.php';

// Get reviews
$stmt = $pdo->prepare("SELECT * FROM productreview WHERE productID = ?");
$stmt->execute([$productId]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ad_img1 = $product['imageURl2'];
$ad_img2 = $product['imageURl3'];
?>

<div class="row">
    <div class="col-md-6">
        <img src="assets/images/products/<?php echo htmlspecialchars($product['imageURl']); ?>"
            class="img-fluid rounded"
            alt="<?php echo htmlspecialchars($product['productname']); ?>">

            <div class="row additional-images">
            <?php if (!empty($ad_img1)): ?>
                <div class="col-6 mb-3">
                    <img src="assets/images/products/<?php echo $ad_img1; ?>"
                        class="img-fluid rounded border thumbnail-image"
                        alt="<?php echo htmlspecialchars($product['productname']); ?> - Additional image 1"
                        data-full-image="<?php echo $ad_img1; ?>">
                </div>
            <?php endif; ?>
            
            <?php if (!empty($ad_img2)): ?>
                <div class="col-6 mb-3">
                    <img src="assets/images/products/<?php echo $ad_img2; ?>"
                        class="img-fluid rounded border thumbnail-image"
                        alt="<?php echo htmlspecialchars($product['productname']); ?> - Additional image 2"
                        data-full-image="<?php echo $ad_img2; ?>">
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <h1><?php echo htmlspecialchars($product['productname']); ?></h1>
        <p class="text-muted">Category: <?php echo htmlspecialchars($product['categoryname'] ?? 'Uncategorized'); ?></p>
        <p class="h3">Rs.<?php echo number_format($product['unitprice'], 2); ?></p>

        <?php if ($product['quantityINstock'] > 0): ?>
            <p class="text-success">In Stock (<?php echo $product['quantityINstock']; ?> available)</p>
        <?php else: ?>
            <p class="text-danger">Out of Stock</p>
        <?php endif; ?>

        <p><?php echo htmlspecialchars($product['Description']); ?></p>

        <form class="add-to-cart-form" data-product-id="<?php echo $product['productID']; ?>">
            <div class="input-group mb-3" style="max-width: 200px;">
                <!--<button class="btn btn-outline-secondary minus-btn" type="button">-</button>-->
                <input type="number" class="form-control text-center quantity-input"
                    value="1" min="1" max="<?php echo $product['quantityINstock']; ?>">
                <!--<button class="btn btn-outline-secondary plus-btn" type="button">+</button>-->
            </div>
            <button type="submit" class="btn btn-danger btn-lg"
                <?php echo $product['quantityINstock'] <= 0 ? 'disabled' : ''; ?>>
                <?php echo $product['quantityINstock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-lg ms-2 add-to-wishlist"
                data-product-id="<?php echo $product['productID']; ?>">
                Add to Wishlist
            </button>
        </form>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <h3>Product Reviews</h3>
        <?php if (empty($reviews)): ?>
            <div class="alert alert-info">No reviews yet. Be the first to review!</div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title">
                                <?php echo str_repeat('★', $review['rating']); ?><?php echo str_repeat('☆', 5 - $review['rating']); ?>
                            </h5>
                            
                        </div>
                        <p class="card-text"><?php echo htmlspecialchars($review['comment']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Write a Review</h5>
                    <form action="./submit_review.php" method="POST">
                        <input type="hidden" name="productID" value="<?php echo $product['productID']; ?>">
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <select class="form-select" id="rating" name="rating" required>
                                <option value="">Select rating...</option>
                                <option value="5">★★★★★ Excellent</option>
                                <option value="4">★★★★☆ Very Good</option>
                                <option value="3">★★★☆☆ Good</option>
                                <option value="2">★★☆☆☆ Fair</option>
                                <option value="1">★☆☆☆☆ Poor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Your Review</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">Submit Review</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">
                Please <a href="./login.php" class="alert-link">login</a> to write a review.
            </div>
        <?php endif; ?>
    </div>
</div>


<script>
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

    // Add to cart function
    async function addToCart(productId, quantity) {
        try {
            const response = await fetch('./add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    productId: productId,
                    quantity: quantity
                }),
                credentials: 'include' // Important for session
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Network response was not ok');
            }

            if (data.success) {
                showToast('success', data.message);
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
                // Update the UI to reflect the new quantity
                const quantityInput = document.querySelector(`.add-to-cart-form[data-product-id="${productId}"] .quantity-input`);
                if (quantityInput) {
                    quantityInput.value = 1; // Reset to 1 after successful add
                }
            } else {
                showToast('error', data.message || 'Error adding to cart');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', error.message || 'An error occurred while adding to cart');
        }
    }

    // Add to wishlist function
    async function addToWishlist(productId, buttonElement) {
        try {
            console.log('Adding to wishlist:', {
                productId
            }); // Debug log

            const response = await fetch('./add_to_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    productId: productId
                }),
                credentials: 'include' // Important for sessions
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Wishlist response:', data); // Debug log

            if (data.success) {
                showToast('success', data.message);

                // Update button appearance if buttonElement was provided
                if (buttonElement) {
                    if (data.action === 'added') {
                        buttonElement.innerHTML = '❤️ In Wishlist';
                        buttonElement.classList.add('active');
                    } else {
                        buttonElement.innerHTML = '♡ Add to Wishlist';
                        buttonElement.classList.remove('active');
                    }
                }

                // Optional: Refresh wishlist count if you have one
                if (typeof updateWishlistCount === 'function') {
                    updateWishlistCount();
                }
            } else {
                showToast('error', data.message || 'Error updating wishlist');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'An error occurred while updating wishlist');
        }
    }

    // Image thumbnail click handler
    function setupImageThumbnails() {
        const thumbnails = document.querySelectorAll('.thumbnail-image');
        const mainImage = document.getElementById('mainProductImage');
        
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                const fullImage = this.getAttribute('data-full-image');
                mainImage.src = `assets/images/products/${fullImage}`;
                
                // Add active class to clicked thumbnail
                thumbnails.forEach(t => t.classList.remove('border-primary'));
                this.classList.add('border-primary');
            });
        });
    }

    // Event delegation for form submission and buttons
    document.addEventListener('DOMContentLoaded', function() {
        setupQuantityControls();
        setupImageThumbnails();

        // Handle add to cart form submission
        document.addEventListener('submit', function(e) {
            if (e.target.classList.contains('add-to-cart-form')) {
                e.preventDefault();
                const productId = e.target.dataset.productId;
                const quantity = e.target.querySelector('.quantity-input').value;
                addToCart(productId, quantity);
            }
        });

        // Handle add to wishlist button clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-to-wishlist')) {
                e.preventDefault();
                const productId = e.target.dataset.productId;
                addToWishlist(productId);
            }
        });
    });
</script>


<?php
// Display review submission messages
if (isset($_SESSION['review_success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['review_success'] . '</div>';
    unset($_SESSION['review_success']);
}

if (isset($_SESSION['review_error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['review_error'] . '</div>';
    unset($_SESSION['review_error']);
}

if (isset($_SESSION['review_errors'])) {
    echo '<div class="alert alert-danger"><ul>';
    foreach ($_SESSION['review_errors'] as $error) {
        echo '<li>' . $error . '</li>';
    }
    echo '</ul></div>';
    unset($_SESSION['review_errors']);
}
?>
<?php require_once 'includes/footer.php'; ?>