<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check if customer is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Get customer's orders for dropdown
$stmt = $pdo->prepare("SELECT * FROM customer WHERE username = ?");
$stmt->execute([$username]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Now you can access the CustomerID
$customerID = $customer['CustomerID'];

$orders = $pdo->prepare("SELECT orderID, orderDate FROM orders 
                        WHERE CustomerID = ? ORDER BY orderDate DESC");
$orders->execute([$customerId]);

//echo $orders['orderID'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?: null;
    $ticketType = $_POST['ticket_type'];
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $priority = $_POST['priority'];

    try {
        $pdo->beginTransaction();
        
        // Create new ticket
        $stmt = $pdo->prepare("INSERT INTO support_tickets 
                             (customer_id, order_id, ticket_type, subject, description, priority)
                             VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $customerID,
            $orderId,
            $ticketType,
            $subject,
            $description,
            $priority
        ]);
        
        $ticketId = $pdo->lastInsertId();
        
        // Add initial response from customer
        $stmt = $pdo->prepare("INSERT INTO ticket_responses 
                             (ticket_id, user_id, user_type, message)
                             VALUES (?, ?, 'customer', ?)");
        $stmt->execute([
            $ticketId,
            $customerID,
            $description  // Using the same description as initial message
        ]);
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Ticket #$ticketId created successfully!";
        header("Location: view_ticket.php?id=$ticketId");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error creating ticket: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4>Create New Support Ticket</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="ticket_type" class="form-label">Ticket Type *</label>
                            <select class="form-select" id="ticket_type" name="ticket_type" required>
                                <option value="general">General Inquiry</option>
                                <option value="return">Return Request</option>
                                <option value="warranty">Warranty Claim</option>
                                <option value="account">Account Issue</option>
                                <option value="technical">Technical Support</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="order_id" class="form-label">Related Order (if applicable)</label>
                            <select class="form-select" id="order_id" name="order_id">
                                <option value="">-- Select Order --</option>
                                <?php while ($order = $orders->fetch()): ?>
                                    <option value="<?php echo $order['orderID']; ?>">
                                        Order #<?php echo $order['orderID']; ?> - <?php echo $order['orderDate']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="attachments" class="form-label">Attachments (if any)</label>
                            <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                            <small class="text-muted">Max file size: 5MB each (PDF, JPG, PNG)</small>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-danger">Submit Ticket</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>