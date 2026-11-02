<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['Role'];
$ticketId = $_GET['id'] ?? 0;

// Get ticket details
$stmt = $pdo->prepare("SELECT t.*, c.*,
                      o.orderID, o.orderDate
                      FROM support_tickets t
                      JOIN customer c ON t.customer_id = c.CustomerID
                      LEFT JOIN orders o ON t.order_id = o.orderID
                      WHERE t.ticket_id = ?");
$stmt->execute([$ticketId]);
$ticket = $stmt->fetch();

// Verify access
if (!$ticket) {
    header("Location: tickets.php");
    exit();
}

if ($userRole === 'Customer' && $ticket['customer_id'] != $userId) {
    header("Location: tickets.php");
    exit();
}

// Get ticket responses
// Replace the existing responses query with this:
$responses = $pdo->prepare("SELECT r.*, 
                           CASE 
                               WHEN r.user_type = 'staff' THEN s.staff_name
                               WHEN r.user_type = 'customer' THEN CONCAT(c.firstname, ' ', c.lastname)
                               ELSE 'System'
                           END as username,
                           CASE 
                               WHEN r.user_type = 'staff' THEN 'Staff'
                               WHEN r.user_type = 'customer' THEN 'Customer'
                               ELSE 'System'
                           END as user_role
                           FROM ticket_responses r
                           LEFT JOIN staff s ON r.user_type = 'staff' AND r.user_id = s.staff_id
                           LEFT JOIN customer c ON r.user_type = 'customer' AND r.user_id = c.CustomerID
                           WHERE r.ticket_id = ?
                           ORDER BY r.created_at");
$responses->execute([$ticketId]);

// Handle new response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    try {
        $pdo->beginTransaction();
        
        // Add response
        $stmt = $pdo->prepare("INSERT INTO ticket_responses 
                             (ticket_id, user_id, user_type, message)
                             VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $ticketId,
            $userId,
            strtolower($userRole) === 'customer' ? 'customer' : 'staff',
            $message
        ]);
        
        // Update ticket status
        $newStatus = $userRole === 'Customer' ? 'open' : 'in_progress';
        $stmt = $pdo->prepare("UPDATE support_tickets SET status = ?, updated_at = NOW()
                              WHERE ticket_id = ?");
        $stmt->execute([$newStatus, $ticketId]);
        
        $pdo->commit();
        
        // Handle file uploads
        if (!empty($_FILES['attachments']['name'][0])) {
            $responseId = $pdo->lastInsertId();
            handleAttachments($responseId);
        }
        
        $_SESSION['success_message'] = "Response added successfully!";
        header("Location: view_ticket.php?id=$ticketId");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error adding response: " . $e->getMessage();
    }
}

// Function to handle file attachments
function handleAttachments($responseId) {
    global $pdo;
    
    $uploadDir = '../uploads/ticket_attachments/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    foreach ($_FILES['attachments']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['attachments']['name'][$key]);
            $filePath = $uploadDir . uniqid() . '_' . $fileName;
            $fileType = $_FILES['attachments']['type'][$key];
            $fileSize = $_FILES['attachments']['size'][$key];
            
            if (move_uploaded_file($tmpName, $filePath)) {
                $stmt = $pdo->prepare("INSERT INTO ticket_attachments 
                                     (response_id, file_name, file_path, file_type, file_size)
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $responseId,
                    $fileName,
                    $filePath,
                    $fileType,
                    $fileSize
                ]);
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Ticket #<?php echo $ticket['ticket_id']; ?>: <?php echo htmlspecialchars($ticket['subject']); ?></h4>
                    <span class="badge bg-<?php 
                        switch($ticket['status']) {
                            case 'open': echo 'warning'; break;
                            case 'in_progress': echo 'info'; break;
                            case 'resolved': echo 'success'; break;
                            case 'closed': echo 'secondary'; break;
                            default: echo 'primary';
                        }
                    ?>">
                        <?php echo ucfirst($ticket['status']); ?>
                    </span>
                </div>
                
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                    <?php endif; ?>
                    
                    <div class="ticket-meta mb-4">
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($ticket['firstname'] . ' ' . $ticket['lastname']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($ticket['Email']); ?></p>
                        <?php if ($ticket['order_id']): ?>
                            <p><strong>Related Order:</strong> #<?php echo $ticket['order_id']; ?> (<?php echo $ticket['order_date']; ?>)</p>
                        <?php endif; ?>
                        <p><strong>Type:</strong> <?php echo ucfirst($ticket['ticket_type']); ?></p>
                        <p><strong>Priority:</strong> <?php echo ucfirst($ticket['priority']); ?></p>
                        <p><strong>Created:</strong> <?php echo $ticket['created_at']; ?></p>
                        <p><strong>Last Updated:</strong> <?php echo $ticket['updated_at']; ?></p>
                    </div>
                    
                    <hr>
                    
                    <div class="ticket-conversation">
                        <?php while ($response = $responses->fetch()): ?>
                            <div class="card mb-3 <?php echo $response['user_type'] === 'staff' ? 'border-primary' : 'border-secondary'; ?>">
                                <div class="card-header <?php echo $response['user_type'] === 'staff' ? 'bg-primary text-white' : 'bg-light'; ?>">
                                    <strong><?php echo htmlspecialchars($response['username']); ?></strong>
                                    <small class="float-end"><?php echo $response['created_at']; ?></small>
                                </div>
                                <div class="card-body">
                                    <p><?php echo nl2br(htmlspecialchars($response['message'])); ?></p>
                                    
                                    <?php 
                                    // Get attachments for this response
                                    $attachments = $pdo->prepare("SELECT * FROM ticket_attachments 
                                                                WHERE response_id = ?");
                                    $attachments->execute([$response['response_id']]);
                                    
                                    if ($attachments->rowCount() > 0): ?>
                                        <div class="attachments mt-3">
                                            <h6>Attachments:</h6>
                                            <ul class="list-unstyled">
                                                <?php while ($attachment = $attachments->fetch()): ?>
                                                    <li>
                                                        <a href="<?php echo $attachment['file_path']; ?>" target="_blank">
                                                            <?php echo htmlspecialchars($attachment['file_name']); ?>
                                                        </a>
                                                        (<?php echo formatFileSize($attachment['file_size']); ?>)
                                                    </li>
                                                <?php endwhile; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="ticket-response mt-4">
                        <h5>Add Response</h5>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="attachments" class="form-label">Attachments (if any)</label>
                                <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                                <small class="text-muted">Max file size: 5MB each (PDF, JPG, PNG)</small>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Submit Response</button>
                                
                                <?php if ($userRole !== 'Customer'): ?>
                                    <?php if ($ticket['status'] !== 'resolved'): ?>
                                        <button type="button" class="btn btn-success" onclick="updateStatus('resolved')">
                                            Mark as Resolved
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($ticket['status'] !== 'closed'): ?>
                                        <button type="button" class="btn btn-secondary" onclick="updateStatus('closed')">
                                            Close Ticket
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="tickets.php" class="btn btn-outline-secondary">
                    Back to Tickets
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(newStatus) {
    if (confirm('Are you sure you want to mark this ticket as ' + newStatus + '?')) {
        fetch('update_ticket_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'ticket_id=<?php echo $ticketId; ?>&status=' + newStatus
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>