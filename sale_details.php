<?php
require_once 'config.php';

$sale_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get sale details with customer info
$sale = $conn->query("
    SELECT s.*, 
           c.name AS customer_name, 
           c.mobile, 
           c.beetech_id AS customer_beetech, 
           u.fullname AS cashier
    FROM sales s 
    JOIN customers c ON s.customer_id = c.id 
    JOIN users u ON s.created_by = u.id 
    WHERE s.id = $sale_id
")->fetch_assoc();

if (!$sale) {
    echo '<div class="alert alert-danger">Sale not found</div>';
    exit;
}

// Safe fallbacks
$customerName = $sale['customer_name'] ?? 'Unknown';
$mobile       = $sale['mobile'] ?? 'N/A';
$beetechID    = $sale['customer_beetech'] ?? 'N/A';

// Get sale items
$items = $conn->query("SELECT * FROM sale_items WHERE sale_id = $sale_id");
?>

<div class="row">
    <div class="col-md-6">
        <h6>Sale Information</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Invoice #:</strong></td>
                <td>#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?></td>
            </tr>
            <tr>
                <td><strong>Date:</strong></td>
                <td><?php echo date('M d, Y h:i A', strtotime($sale['created_at'])); ?></td>
            </tr>
            <tr>
                <td><strong>Cashier:</strong></td>
                <td><?php echo $sale['cashier'] ?? 'N/A'; ?></td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h6>Customer Information</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Name:</strong></td>
                <td><?php echo $customerName; ?></td>
            </tr>
            <tr>
                <td><strong>Mobile:</strong></td>
                <td><?php echo $mobile; ?></td>
            </tr>
            <tr>
                <td><strong>Beetech ID:</strong></td>
                <td><span class="badge bg-info"><?php echo $beetechID; ?></span></td>
            </tr>
        </table>
    </div>
</div>

<h6 class="mt-3">Items Purchased</h6>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($item = $items->fetch_assoc()): ?>
            <tr>
                <td><?php echo $item['product_name'] ?? 'Unknown'; ?></td>
                <td>৳<?php echo number_format($item['price'] ?? 0, 2); ?></td>
                <td><?php echo $item['quantity'] ?? 0; ?></td>
                <td>৳<?php echo number_format($item['total'] ?? 0, 2); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div class="row mt-3">
    <div class="col-md-6 offset-md-6">
        <table class="table table-sm">
            <tr class="text-success">
                <td><strong>Beetech Balance: (5%)</strong></td>
                <td class="text-end">৳<?php echo number_format($sale['discount'] ?? 0, 2); ?></td>
            </tr>
            <tr class="table-primary">
                <td><strong>Total Amount:</strong></td>
                <td class="text-end"><strong>৳<?php echo number_format($sale['subtotal'] ?? 0, 2); ?></strong></td>
            </tr>
        </table>
    </div>
</div>