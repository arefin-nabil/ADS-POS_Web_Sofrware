<?php
$page_title = 'Customer Profile';
require_once 'header.php';

$customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get customer details
$customer = $conn->query("SELECT * FROM customers WHERE id = $customer_id")->fetch_assoc();

if (!$customer) {
    redirect('customers.php');
}

// Get customer purchase history
$purchases = $conn->query("SELECT s.*, u.fullname as created_by_name 
                           FROM sales s 
                           JOIN users u ON s.created_by = u.id 
                           WHERE s.customer_id = $customer_id 
                           ORDER BY s.created_at DESC");

// Get total stats
$stats = $conn->query("SELECT 
                       COUNT(*) as total_purchases,
                       SUM(total) as total_spent,
                       SUM(discount) as total_saved
                       FROM sales WHERE customer_id = $customer_id")->fetch_assoc();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-person-circle"></i> Customer Profile</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="customers.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Customers
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-person-circle display-1 text-primary"></i>
                </div>
                <h3><?php echo $customer['name']; ?></h3>
                <p class="text-muted mb-2">
                    <i class="bi bi-telephone"></i> <?php echo $customer['mobile']; ?>
                </p>
                <p class="text-muted mb-2">
                    <i class="bi bi-geo-alt"></i> <?php echo $customer['address']; ?>
                </p>
                <p class="mb-3">
                    <span class="badge bg-info"><?php echo $customer['beetech_id']; ?></span>
                </p>
                <p class="text-muted small">
                    Member since: <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Purchase Statistics</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Total Purchases</label>
                    <h4><?php echo $stats['total_purchases']; ?></h4>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Total Spent</label>
                    <h4>৳<?php echo number_format($stats['total_spent'], 2); ?></h4>
                </div>
                <div class="mb-0">
                    <label class="text-muted small">Total Saved (5% Discount)</label>
                    <h4 class="text-success">৳<?php echo number_format($stats['total_saved'], 2); ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Purchase History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Subtotal</th>
                                <th>Discount</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($sale = $purchases->fetch_assoc()):
                                $items = $conn->query("SELECT COUNT(*) as count FROM sale_items WHERE sale_id = {$sale['id']}")->fetch_assoc()['count'];
                            ?>
                                <tr>
                                    <td>#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($sale['created_at'])); ?></td>
                                    <td><?php echo $items; ?> items</td>
                                    <td>৳<?php echo number_format($sale['subtotal'], 2); ?></td>
                                    <td class="text-success">-৳<?php echo number_format($sale['discount'], 2); ?></td>
                                    <td><strong>৳<?php echo number_format($sale['total'], 2); ?></strong></td>
                                    <td>
                                        <a href="invoice.php?id=<?php echo $sale['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="bi bi-printer"></i> Invoice
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>