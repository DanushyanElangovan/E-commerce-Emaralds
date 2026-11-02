<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check admin access
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Inventory Management";

// Handle inventory actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_inventory'])) {
        $productId = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        $location = $_POST['location'];
        $batchNumber = $_POST['batch_number'] ?? null;
        $status = $_POST['status'] ?? 'active';
        $notes = $_POST['notes'] ?? 'Manual inventory adjustment';

        try {
            // Begin transaction
            $pdo->beginTransaction();

            // Get current quantity using prepared statement
            $stmt = $pdo->prepare("SELECT quantityINstock FROM product WHERE productID = ?");
            $stmt->execute([$productId]);
            $current = $stmt->fetch();

            if (!$current) {
                throw new Exception("Product not found");
            }

            // Update product stock
            $stmt = $pdo->prepare("UPDATE product SET quantityINstock = ? WHERE productID = ?");
            $stmt->execute([$quantity, $productId]);

            // Record transaction
            $stmt = $pdo->prepare("INSERT INTO inventorytransaction 
                                  (productID, transactiontype, quantity, transactiondate, 
                                   previousquantity, newquantity, notes, userID)
                                  VALUES (?, 'adjustment', ?, NOW(), ?, ?, ?, ?)");
            $stmt->execute([
                $productId, 
                $quantity - $current['quantityINstock'],
                $current['quantityINstock'], 
                $quantity,
                $notes,
                $_SESSION['user_id']
            ]);

            // Check if inventory record exists
            $stmt = $pdo->prepare("SELECT inventoryID FROM inventory WHERE productID = ?");
            $stmt->execute([$productId]);
            $inventoryExists = $stmt->fetch();

            if ($inventoryExists) {
                // Update existing inventory record
                $stmt = $pdo->prepare("UPDATE inventory 
                                      SET quantityINstock = ?, lastupdated = NOW(), 
                                          location = ?, batchnumber = ?, status = ?
                                      WHERE productID = ?");
                $stmt->execute([$quantity, $location, $batchNumber, $status, $productId]);
            } else {
                // Create new inventory record
                $stmt = $pdo->prepare("INSERT INTO inventory 
                                      (productID, quantityINstock, lastupdated, 
                                       location, batchnumber, status)
                                      VALUES (?, ?, NOW(), ?, ?, ?)");
                $stmt->execute([$productId, $quantity, $location, $batchNumber, $status]);
            }

            $pdo->commit();
            $_SESSION['message'] = "Inventory updated successfully";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error updating inventory: " . $e->getMessage();
        }
    }
}

// Get inventory with product info
$inventory = $pdo->query("
    SELECT p.productID, p.productname, p.imageURl, p.quantityINstock as product_stock,
           i.quantityINstock, i.location, i.lastupdated, i.batchnumber, i.status
    FROM product p
    LEFT JOIN inventory i ON p.productID = i.productID
    ORDER BY p.productname
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Inventory Management</h2>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Current Stock</th>
                            <th>Location</th>
                            <th>Batch</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['productname']); ?></td>
                                <td>
                                    <?php if ($item['imageURl']): ?>
                                        <img src="../assets/images/products/<?php echo htmlspecialchars($item['imageURl']); ?>"
                                            alt="Product Image"
                                            style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo ($item['product_stock'] ?? $item['product_stock']) > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $item['product_stock'] ?? $item['product_stock']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($item['location'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['batchnumber'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($item['status'] ?? 'N/A'); ?></td>
                                <td><?php echo $item['lastupdated'] ? formatDate($item['lastupdated']) : 'Never'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal"
                                        data-product-id="<?php echo $item['productID']; ?>"
                                        data-current-stock="<?php echo $item['quantityINstock'] ?? $item['product_stock']; ?>"
                                        data-location="<?php echo htmlspecialchars($item['location'] ?? ''); ?>"
                                        data-batch-number="<?php echo htmlspecialchars($item['batchnumber'] ?? ''); ?>"
                                        data-status="<?php echo htmlspecialchars($item['status'] ?? 'active'); ?>">
                                        Update
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Update Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="modal_product_id">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location">
                    </div>
                    <div class="mb-3">
                        <label for="batch_number" class="form-label">Batch Number</label>
                        <input type="text" class="form-control" id="batch_number" name="batch_number">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="expired">Expired</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_inventory" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var modal = this;
            
            modal.querySelector('#modal_product_id').value = button.getAttribute('data-product-id');
            modal.querySelector('#quantity').value = button.getAttribute('data-current-stock');
            modal.querySelector('#location').value = button.getAttribute('data-location');
            modal.querySelector('#batch_number').value = button.getAttribute('data-batch-number');
            modal.querySelector('#status').value = button.getAttribute('data-status');
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>