<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Check admin access
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "Reports";

// Generate report
$reportData = [];
$reportType = '';
$startDate = '';
$endDate = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['report_type'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    
    // Add time to dates to include the entire end day
    $startDateWithTime = $startDate . ' 00:00:00';
    $endDateWithTime = $endDate . ' 23:59:59';
    
    // Debug output (remove in production)
    error_log("Report Type: $reportType, Start: $startDateWithTime, End: $endDateWithTime");

    try {
        switch ($reportType) {
            case 'sales':
                $stmt = $pdo->prepare("
                    SELECT DATE(o.orderDate) as date, 
                           COUNT(*) as order_count,
                           SUM(o.totalAmount) as total_sales,
                           AVG(o.totalAmount) as avg_order_value
                    FROM `orders` o
                    WHERE o.orderDate BETWEEN ? AND ?
                    GROUP BY DATE(o.orderDate)
                ");
                $stmt->execute([$startDateWithTime, $endDateWithTime]);
                $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'products':
                $stmt = $pdo->prepare("
                    SELECT p.productname, 
                           SUM(oi.quantity) as units_sold,
                           SUM(oi.subtotal) as revenue,
                           p.quantityINstock as current_stock
                    FROM orderitems oi
                    JOIN product p ON oi.productID = p.productID
                    JOIN `orders` o ON oi.orderID = o.orderID
                    WHERE o.orderDate BETWEEN ? AND ?
                    GROUP BY p.productID
                    ORDER BY revenue DESC
                ");
                $stmt->execute([$startDateWithTime, $endDateWithTime]);
                $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'customers':
                $stmt = $pdo->prepare("
                    SELECT c.firstname, c.lastname, c.Email,
                           COUNT(o.orderID) as order_count,
                           SUM(o.totalAmount) as total_spent
                    FROM customer c
                    JOIN `orders` o ON c.CustomerID = o.CustomerID
                    WHERE o.orderDate BETWEEN ? AND ?
                    GROUP BY c.CustomerID
                    ORDER BY total_spent DESC
                    LIMIT 50
                ");
                $stmt->execute([$startDateWithTime, $endDateWithTime]);
                $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        error_log($error);
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Reports</h2>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5>Generate Report</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select class="form-select" id="report_type" name="report_type" required>
                            <option value="sales" <?= $reportType === 'sales' ? 'selected' : '' ?>>Sales Summary</option>
                            <!--<option value="products" <?= $reportType === 'products' ? 'selected' : '' ?>>Product Performance</option>-->
                            <option value="customers" <?= $reportType === 'customers' ? 'selected' : '' ?>>Customer Spending</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?= htmlspecialchars($startDate) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?= htmlspecialchars($endDate) ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </form>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Report Results</h5>
                <div>
                    <?php if (!empty($reportData)): ?>
                        <span class="badge bg-primary">
                            <?= count($reportData) ?> records found
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($reportData)): ?>
                    <div class="alert alert-info">No records found for the selected criteria</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($reportData[0]) as $column): ?>
                                        <th><?= ucwords(str_replace('_', ' ', $column)) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td>
                                                <?php if (is_numeric($value)): ?>
                                                    <?php 
                                                    // Format as currency if the column name suggests it
                                                    if (strpos($column, 'total') !== false || 
                                                        strpos($column, 'revenue') !== false || 
                                                        strpos($column, 'amount') !== false ||
                                                        strpos($column, 'avg') !== false) {
                                                        echo 'Rs.' . number_format($value, 2);
                                                    } else {
                                                        echo number_format($value);
                                                    }
                                                    ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($value) ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-success" onclick="exportToExcel()">
                            <i class="bi bi-file-earmark-excel"></i> Export to Excel
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
            function exportToExcel() {
                let html = document.querySelector('.table').outerHTML;
                let blob = new Blob([html], {
                    type: 'application/vnd.ms-excel'
                });
                let url = URL.createObjectURL(blob);

                let a = document.createElement('a');
                a.href = url;
                a.download = 'report_<?= date('Y-m-d') ?>_<?= $reportType ?>.xls';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        </script>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>