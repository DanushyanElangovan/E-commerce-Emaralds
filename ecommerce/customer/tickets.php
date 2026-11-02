<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check if customer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['Role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

$stmt = $pdo->prepare("SELECT * FROM customer WHERE username = ?");
$stmt->execute([$username]);

// Fetch the customer record
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

$customerId = $customer['CustomerID'];

// Get customer's tickets
$tickets = $pdo->prepare("SELECT t.*, COUNT(r.response_id) as response_count
                         FROM support_tickets t
                         LEFT JOIN ticket_responses r ON t.ticket_id = r.ticket_id
                         WHERE t.customer_id = ?
                         GROUP BY t.ticket_id
                         ORDER BY t.created_at DESC");
$tickets->execute([$customerId]);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>My Support Tickets</h4>
                        <a href="customer_ticket.php" class="btn btn-light btn-sm">
                            <i class="bi bi-plus"></i> New Ticket
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Responses</th>
                                    <th>Created</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($ticket = $tickets->fetch()): ?>
                                    <tr>
                                        <td><?php echo $ticket['ticket_id']; ?></td>
                                        <td>
                                            <a href="view_ticket.php?id=<?php echo $ticket['ticket_id']; ?>">
                                                <?php echo htmlspecialchars($ticket['subject']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo ucfirst($ticket['ticket_type']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                switch($ticket['priority']) {
                                                    case 'urgent': echo 'danger'; break;
                                                    case 'high': echo 'warning'; break;
                                                    case 'medium': echo 'primary'; break;
                                                    case 'low': echo 'secondary'; break;
                                                    default: echo 'light';
                                                }
                                            ?>">
                                                <?php echo ucfirst($ticket['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                switch($ticket['status']) {
                                                    case 'open': echo 'warning'; break;
                                                    case 'in_progress': echo 'info'; break;
                                                    case 'resolved': echo 'success'; break;
                                                    case 'closed': echo 'secondary'; break;
                                                    default: echo 'light';
                                                }
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $ticket['response_count']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($ticket['updated_at'])); ?></td>
                                        <td>
                                            <a href="view_ticket.php?id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                
                                <?php if ($tickets->rowCount() === 0): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">You have no support tickets yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>