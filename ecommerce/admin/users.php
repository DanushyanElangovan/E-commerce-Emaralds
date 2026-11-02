<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['Role'] !== 'System_admin') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "User Management";

// Handle user actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $userId = $_GET['id'] ?? null;

    if ($userId) {
        switch ($action) {
            case 'update_role':
                $newRole = $_POST['role'];
                $pdo->prepare("UPDATE user SET Role = ? WHERE userID = ?")
                    ->execute([$newRole, $userId]);
                $_SESSION['message'] = "User role updated successfully";
                break;

            case 'update_status':
                $newStatus = $_POST['status'];
                $pdo->prepare("UPDATE user SET Status = ? WHERE userID = ?")
                    ->execute([$newStatus, $userId]);
                $_SESSION['message'] = "User status updated successfully";
                break;

            case 'delete':
                $pdo->prepare("DELETE FROM user WHERE userID = ?")->execute([$userId]);
                $_SESSION['message'] = "User deleted successfully";
                break;
        }
    }
}

// Get all users
$users = $pdo->query("SELECT * FROM user ORDER BY accountCreationDate DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>User Management</h2>
        </div>

        <div class="col-md-6 text-end">
                <a href="../admin/add_new_user.php" class="btn btn-danger">
                    <i class="bi bi-plus-lg"></i> Add New User
                </a>
            </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message'];
                                            unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['userID']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['Firstname'] . ' ' . $user['Lastname']); ?></td>
                                <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $user['Role']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['Status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo $user['Status']; ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($user['accountCreationDate']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#roleModal"
                                            data-user-id="<?php echo $user['userID']; ?>"
                                            data-current-role="<?php echo $user['Role']; ?>">
                                            Change Role
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#statusModal"
                                            data-user-id="<?php echo $user['userID']; ?>"
                                            data-current-status="<?php echo $user['Status']; ?>">
                                            Change Status
                                        </button>
                                        <a href="?action=delete&id=<?php echo $user['userID']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this user?')">
                                            Delete
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

<!-- Role Update Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalLabel">Update User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="?action=update_role&id=" id="roleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="sales_manager">Sales Manager</option>
                            <option value="inventory_manager">Inventory Manager</option>
                            <option value="warehouse_staff">Warehouse Staff</option>
                            <option value="CEO">CEO</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update User Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="?action=update_status&id=" id="statusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Role modal setup
        var roleModal = document.getElementById('roleModal');
        roleModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            var currentRole = button.getAttribute('data-current-role');

            document.getElementById('roleForm').action = "?action=update_role&id=" + userId;
            document.getElementById('role').value = currentRole;
        });

        // Status modal setup
        var statusModal = document.getElementById('statusModal');
        statusModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            var currentStatus = button.getAttribute('data-current-status');

            document.getElementById('statusForm').action = "?action=update_status&id=" + userId;
            document.getElementById('status').value = currentStatus;
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>