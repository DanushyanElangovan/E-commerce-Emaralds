<?php
// Start output buffering at the very beginning
ob_start();

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check admin access
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Add New Product";

// Get categories and suppliers for dropdowns
$categories = $pdo->query("SELECT * FROM category ORDER BY categoryname")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY companyname")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $unitPrice = floatval($_POST['unit_price']);
    $quantity = intval($_POST['quantity']);
    $categoryId = $_POST['category_id'] ?: null;
    $supplierId = $_POST['supplier_id'] ?: null;
    $location = trim($_POST['location']);
    $addedBy = $_SESSION['user_id'];

    // Initialize image URLs
    $imageUrl = $imageUrl2 = $imageUrl3 = null;
    
    // Function to handle image upload
    function uploadImage($file, $uploadDir, $allowedTypes) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $uploadFile = $uploadDir . basename($file['name']);
            $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

            if (in_array($imageFileType, $allowedTypes)) {
                if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                    return basename($file['name']);
                }
            }
        }
        return null;
    }

    // Create directory if it doesn't exist
    $uploadDir = '../assets/images/products/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Allowed image types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    // Handle main image upload
    if (isset($_FILES['product_image'])) {
        $imageUrl = uploadImage($_FILES['product_image'], $uploadDir, $allowedTypes);
    }

    // Handle second image upload
    if (isset($_FILES['product_image2'])) {
        $imageUrl2 = uploadImage($_FILES['product_image2'], $uploadDir, $allowedTypes);
    }

    // Handle third image upload
    if (isset($_FILES['product_image3'])) {
        $imageUrl3 = uploadImage($_FILES['product_image3'], $uploadDir, $allowedTypes);
    }

    try {
        $pdo->beginTransaction();

        // Insert product with all three image fields
        $stmt = $pdo->prepare("INSERT INTO product 
                             (productname, Description, unitprice, quantityINstock, categoryID, supplierid, 
                             dataAdded, addedBY, imageURl, imageURl2, imageURl3, location)
                             VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
        $stmt->execute([
            $productName,
            $description,
            $unitPrice,
            $quantity,
            $categoryId,
            $supplierId,
            $addedBy,
            $imageUrl,
            $imageUrl2,
            $imageUrl3,
            $location
        ]);

        $productId = $pdo->lastInsertId();

        // Add to inventory
        $pdo->prepare("INSERT INTO inventory 
                      (productID, quantityINstock, lastupdated, location)
                      VALUES (?, ?, NOW(), ?)")
            ->execute([$productId, $quantity, $location]);

        // Record inventory transaction
        $pdo->prepare("INSERT INTO inventorytransaction 
                      (productID, transactiontype, quantity, previousquantity, newquantity, userID)
                      VALUES (?, 'purchase', ?, 0, ?, ?)")
            ->execute([$productId, $quantity, $quantity, $addedBy]);

        $pdo->commit();

        // Clear output buffer and redirect
        ob_end_clean();
        $_SESSION['success_message'] = "Product added successfully!";
        header("Location: products.php");
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error adding product: " . $e->getMessage();
        error_log("Product Add Error: " . $e->getMessage());
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
        error_log("Product Add Error: " . $e->getMessage());
    }
}

// If we get here, either it's not a POST request or there was an error
// Flush the buffer if not already cleaned
if (ob_get_length()) {
    ob_end_flush();
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Add New Product</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="../admin/products.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit_price" class="form-label">Unit Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rs. </span>
                                        <input type="number" class="form-control" id="unit_price" name="unit_price"
                                            step="0.01" min="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Initial Stock *</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity"
                                        min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="product_image" class="form-label">Main Product Image *</label>
                            <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*" required>
                            <small class="text-muted">Max size: 2MB (JPEG, PNG, GIF)</small>
                            <div id="imagePreview" class="mt-2 border p-2 text-center" style="display: none;">
                                <img id="previewImg" src="#" alt="Preview" style="max-height: 200px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="product_image2" class="form-label">Additional Image 1</label>
                            <input type="file" class="form-control" id="product_image2" name="product_image2" accept="image/*">
                            <div id="imagePreview2" class="mt-2 border p-2 text-center" style="display: none;">
                                <img id="previewImg2" src="#" alt="Preview" style="max-height: 200px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="product_image3" class="form-label">Additional Image 2</label>
                            <input type="file" class="form-control" id="product_image3" name="product_image3" accept="image/*">
                            <div id="imagePreview3" class="mt-2 border p-2 text-center" style="display: none;">
                                <img id="previewImg3" src="#" alt="Preview" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['categoryID']; ?>">
                                        <?php echo htmlspecialchars($category['categoryname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="location" class="form-label">Location in Warehouse</label>
                            <input type="text" class="form-control" id="location" name="location">
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-danger">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Image preview functionality for all three images
    function setupImagePreview(inputId, previewId, imgId) {
        document.getElementById(inputId).addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).style.display = 'block';
                    document.getElementById(imgId).src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                document.getElementById(previewId).style.display = 'none';
            }
        });
    }

    // Set up previews for all three image inputs
    setupImagePreview('product_image', 'imagePreview', 'previewImg');
    setupImagePreview('product_image2', 'imagePreview2', 'previewImg2');
    setupImagePreview('product_image3', 'imagePreview3', 'previewImg3');

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const price = parseFloat(document.getElementById('unit_price').value);
        const quantity = parseInt(document.getElementById('quantity').value);

        if (price < 0) {
            alert('Price cannot be negative');
            e.preventDefault();
        }

        if (quantity < 0) {
            alert('Quantity cannot be negative');
            e.preventDefault();
        }

        // Check if main image is uploaded
        if (!document.getElementById('product_image').files[0]) {
            alert('Main product image is required');
            e.preventDefault();
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>