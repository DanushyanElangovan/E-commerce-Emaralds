<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['Role'] !== 'System_admin') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Add New Category";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['category_name']);
    $description = trim($_POST['description']);
    $displayOrder = intval($_POST['display_order']);
    $addedBy = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO category 
                             (Description, displayOrder, categoryname)
                             VALUES (?, ?, ?)");
        $stmt->execute([
            $description,
            $displayOrder,
            $categoryName
        ]);

        $_SESSION['success_message'] = "Category added successfully!";
        header("Location: ../categories.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error adding category: " . $e->getMessage();
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Add New Category</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="../categories.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Categories
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-danger">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const categoryName = document.getElementById('category_name').value.trim();

        if (!categoryName) {
            e.preventDefault();
            alert('Category name is required');
            document.getElementById('category_name').focus();
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>