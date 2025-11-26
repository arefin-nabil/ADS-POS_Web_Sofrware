<?php
$page_title = 'Dashboard';
require_once 'header.php';

// Get today's sales
$today = date('Y-m-d');
$todaySales = $conn->query("SELECT SUM(total) as total FROM sales WHERE DATE(created_at) = '$today'")->fetch_assoc()['total'] ?? 0;

// Get total customers
$totalCustomers = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'];

// Get low stock products (stock < 20)
$lowStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock < 20")->fetch_assoc()['count'];

// Get total products
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];

// Get recent sales
$recentSales = $conn->query("SELECT s.*, c.name as customer_name, u.fullname as created_by_name 
                             FROM sales s 
                             JOIN customers c ON s.customer_id = c.id 
                             JOIN users u ON s.created_by = u.id 
                             ORDER BY s.created_at DESC LIMIT 10");

// Get low stock products list
$lowStockProducts = $conn->query("SELECT * FROM products WHERE stock < 20 ORDER BY stock ASC LIMIT 10");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar"></i> <?php echo date('F d, Y'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Today's Sales</h6>
                        <h2 class="mt-2 mb-0">৳<?php echo number_format($todaySales, 2); ?></h2>
                    </div>
                    <div class="fs-1 fw-bold">
                        ৳
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Customers</h6>
                        <h2 class="mt-2 mb-0"><?php echo $totalCustomers; ?></h2>
                    </div>
                    <div class="fs-1">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Low Stock Items</h6>
                        <h2 class="mt-2 mb-0"><?php echo $lowStock; ?></h2>
                    </div>
                    <div class="fs-1">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Products</h6>
                        <h2 class="mt-2 mb-0"><?php echo $totalProducts; ?></h2>
                    </div>
                    <div class="fs-1">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Sales -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> Recent Sales</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($sale = $recentSales->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo $sale['customer_name']; ?></td>
                                    <td>৳<?php echo number_format($sale['total'], 2); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($sale['created_at'])); ?></td>
                                    <td>
                                        <a href="invoice.php?id=<?php echo $sale['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="bi bi-printer"></i>
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

    <!-- Low Stock Alert -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Low Stock Alert</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php while ($product = $lowStockProducts->fetch_assoc()): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo $product['name']; ?></strong>
                                <br>
                                <small class="text-muted"><?php echo $product['barcode']; ?></small>
                            </div>
                            <span class="badge bg-danger"><?php echo $product['stock']; ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>