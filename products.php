<?php
$page_title = 'Products';
require_once 'header.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = clean($_POST['name']);
        $category = clean($_POST['category']);
        $barcode = clean($_POST['barcode']);
        $buying_price = floatval($_POST['buying_price']);
        $selling_price = floatval($_POST['selling_price']);
        $stock = intval($_POST['stock']);

        $stmt = $conn->prepare("INSERT INTO products (name, category, barcode, buying_price, selling_price, stock) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddi", $name, $category, $barcode, $buying_price, $selling_price, $stock);

        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
            if ($stock > 0) {
                $conn->query("INSERT INTO stock_log (product_id, type, quantity, note, created_by) 
                              VALUES ($product_id, 'in', $stock, 'Initial stock', {$_SESSION['user_id']})");
            }
            logActivity($conn, $_SESSION['user_id'], 'Added product', $name);
            $message = 'Product added successfully';
            $messageType = 'success';
        } else {
            $message = 'Error adding product: ' . $conn->error;
            $messageType = 'danger';
        }
        $stmt->close();
    } elseif (isset($_POST['update_product'])) {
        $id = intval($_POST['id']);
        $name = clean($_POST['name']);
        $category = clean($_POST['category']);
        $barcode = clean($_POST['barcode']);
        $buying_price = floatval($_POST['buying_price']);
        $selling_price = floatval($_POST['selling_price']);

        $stmt = $conn->prepare("UPDATE products SET name=?, category=?, barcode=?, buying_price=?, selling_price=? WHERE id=?");
        $stmt->bind_param("sssddi", $name, $category, $barcode, $buying_price, $selling_price, $id);

        if ($stmt->execute()) {
            logActivity($conn, $_SESSION['user_id'], 'Updated product', $name);
            $message = 'Product updated successfully';
            $messageType = 'success';
        } else {
            $message = 'Error updating product';
            $messageType = 'danger';
        }
        $stmt->close();
    } elseif (isset($_POST['add_stock'])) {
        $id = intval($_POST['id']);
        $quantity = intval($_POST['quantity']);
        $note = clean($_POST['note']);

        $conn->query("UPDATE products SET stock = stock + $quantity WHERE id = $id");
        $conn->query("INSERT INTO stock_log (product_id, type, quantity, note, created_by) 
                      VALUES ($id, 'in', $quantity, '$note', {$_SESSION['user_id']})");

        logActivity($conn, $_SESSION['user_id'], 'Added stock', "Product ID: $id, Qty: $quantity");
        $message = 'Stock added successfully';
        $messageType = 'success';
    } elseif (isset($_POST['delete_product'])) {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM products WHERE id = $id");
        logActivity($conn, $_SESSION['user_id'], 'Deleted product', "Product ID: $id");
        $message = 'Product deleted successfully';
        $messageType = 'success';
    }
}

// Export to CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="products_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Name', 'Category', 'Barcode', 'Buying Price', 'Selling Price', 'Stock'));

    $result = $conn->query("SELECT * FROM products");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY name");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-box-seam"></i> Products</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-circle"></i> Add Product
        </button>
        <a href="?export=1" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="productsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Barcode</th>
                        <th>Buying Price</th>
                        <th>Selling Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo $product['category']; ?></td>
                            <td><?php echo $product['barcode']; ?></td>
                            <td>৳<?php echo number_format($product['buying_price'], 2); ?></td>
                            <td>৳<?php echo number_format($product['selling_price'], 2); ?></td>
                            <td>
                                <span class="badge <?php echo $product['stock'] < 20 ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="addStock(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                    <i class="bi bi-plus"></i> Stock
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" name="category" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" class="form-control" name="barcode" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Buying Price</label>
                            <input type="number" step="0.01" class="form-control" name="buying_price" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Selling Price</label>
                            <input type="number" step="0.01" class="form-control" name="selling_price" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Stock</label>
                        <input type="number" class="form-control" name="stock" value="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" name="category" id="edit_category" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Barcode</label>
                        <input type="text" class="form-control" name="barcode" id="edit_barcode" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Buying Price</label>
                            <input type="number" step="0.01" class="form-control" name="buying_price" id="edit_buying_price" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Selling Price</label>
                            <input type="number" step="0.01" class="form-control" name="selling_price" id="edit_selling_price" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="stock_id">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" id="stock_product_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" id="stock_current" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Add</label>
                        <input type="number" class="form-control" name="quantity" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="note" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_stock" class="btn btn-primary">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editProduct(product) {
        document.getElementById('edit_id').value = product.id;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_category').value = product.category;
        document.getElementById('edit_barcode').value = product.barcode;
        document.getElementById('edit_buying_price').value = product.buying_price;
        document.getElementById('edit_selling_price').value = product.selling_price;
        new bootstrap.Modal(document.getElementById('editProductModal')).show();
    }

    function addStock(product) {
        document.getElementById('stock_id').value = product.id;
        document.getElementById('stock_product_name').value = product.name;
        document.getElementById('stock_current').value = product.stock;
        new bootstrap.Modal(document.getElementById('addStockModal')).show();
    }
</script>

<?php require_once 'footer.php'; ?>