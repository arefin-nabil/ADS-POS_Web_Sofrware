<?php
$page_title = 'Sales History';
require_once 'header.php';

// Export to CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Invoice', 'Date', 'Customer', 'Subtotal', 'Discount', 'Total', 'Cashier'));

    $result = $conn->query("SELECT s.*, c.name as customer_name, u.fullname as cashier 
                            FROM sales s 
                            JOIN customers c ON s.customer_id = c.id 
                            JOIN users u ON s.created_by = u.id 
                            ORDER BY s.created_at DESC");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, array(
            str_pad($row['id'], 5, '0', STR_PAD_LEFT),
            date('Y-m-d H:i:s', strtotime($row['created_at'])),
            $row['customer_name'],
            $row['subtotal'],
            $row['discount'],
            $row['total'],
            $row['cashier']
        ));
    }
    fclose($output);
    exit();
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filters
$where = "1=1";
if (isset($_GET['date']) && $_GET['date']) {
    $date = clean($_GET['date']);
    $where .= " AND DATE(s.created_at) = '$date'";
}
if (isset($_GET['customer']) && $_GET['customer']) {
    $customer = intval($_GET['customer']);
    $where .= " AND s.customer_id = $customer";
}

// Get total count
$total = $conn->query("SELECT COUNT(*) as count FROM sales s WHERE $where")->fetch_assoc()['count'];
$totalPages = ceil($total / $limit);

// Get sales
$sales = $conn->query("SELECT s.*, c.name as customer_name, u.fullname as cashier 
                       FROM sales s 
                       JOIN customers c ON s.customer_id = c.id 
                       JOIN users u ON s.created_by = u.id 
                       WHERE $where
                       ORDER BY s.created_at DESC 
                       LIMIT $limit OFFSET $offset");

// Get customers for filter
$customers = $conn->query("SELECT * FROM customers ORDER BY name");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-receipt"></i> Sales History</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="?export=1<?php echo isset($_GET['date']) ? '&date=' . $_GET['date'] : ''; ?><?php echo isset($_GET['customer']) ? '&customer=' . $_GET['customer'] : ''; ?>" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="date" value="<?php echo $_GET['date'] ?? ''; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Customer</label>
                <select class="form-select" name="customer">
                    <option value="">All Customers</option>
                    <?php while ($customer = $customers->fetch_assoc()): ?>
                        <option value="<?php echo $customer['id']; ?>" <?php echo (isset($_GET['customer']) && $_GET['customer'] == $customer['id']) ? 'selected' : ''; ?>>
                            <?php echo $customer['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="sales.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Subtotal</th>
                        <th>Discount</th>
                        <th>Total</th>
                        <th>Cashier</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($sale = $sales->fetch_assoc()):
                        $itemCount = $conn->query("SELECT COUNT(*) as count FROM sale_items WHERE sale_id = {$sale['id']}")->fetch_assoc()['count'];
                    ?>
                        <tr>
                            <td><strong>#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($sale['created_at'])); ?></td>
                            <td><?php echo $sale['customer_name']; ?></td>
                            <td><?php echo $itemCount; ?> items</td>
                            <td>৳<?php echo number_format($sale['subtotal'], 2); ?></td>
                            <td class="text-success">৳<?php echo number_format($sale['discount'], 2); ?></td>
                            <td><strong>৳<?php echo number_format($sale['total'], 2); ?></strong></td>
                            <td><?php echo $sale['cashier']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo $sale['id']; ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="invoice.php?id=<?php echo $sale['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['date']) ? '&date=' . $_GET['date'] : ''; ?><?php echo isset($_GET['customer']) ? '&customer=' . $_GET['customer'] : ''; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['date']) ? '&date=' . $_GET['date'] : ''; ?><?php echo isset($_GET['customer']) ? '&customer=' . $_GET['customer'] : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['date']) ? '&date=' . $_GET['date'] : ''; ?><?php echo isset($_GET['customer']) ? '&customer=' . $_GET['customer'] : ''; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Sale Details Modal -->
<div class="modal fade" id="saleDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sale Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="saleDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function viewDetails(saleId) {
        const modal = new bootstrap.Modal(document.getElementById('saleDetailsModal'));
        modal.show();

        fetch('get_sale_details.php?id=' + saleId)
            .then(response => response.text())
            .then(html => {
                document.getElementById('saleDetailsContent').innerHTML = html;
            });
    }
</script>

<?php require_once 'footer.php'; ?>