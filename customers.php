<?php
$page_title = 'Customers';
require_once 'header.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_customer'])) {
        $name = clean($_POST['name']);
        $mobile = clean($_POST['mobile']);
        $address = clean($_POST['address']);
        $beetech_id = clean($_POST['beetech_id']);

        $stmt = $conn->prepare("INSERT INTO customers (name, mobile, address, beetech_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $mobile, $address, $beetech_id);

        if ($stmt->execute()) {
            logActivity($conn, $_SESSION['user_id'], 'Added customer', $name);
            $message = 'Customer added successfully';
            $messageType = 'success';
        } else {
            $message = 'Error adding customer: ' . $conn->error;
            $messageType = 'danger';
        }
        $stmt->close();
    } elseif (isset($_POST['update_customer'])) {
        $id = intval($_POST['id']);
        $name = clean($_POST['name']);
        $mobile = clean($_POST['mobile']);
        $address = clean($_POST['address']);
        $beetech_id = clean($_POST['beetech_id']);

        $stmt = $conn->prepare("UPDATE customers SET name=?, mobile=?, address=?, beetech_id=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $mobile, $address, $beetech_id, $id);

        if ($stmt->execute()) {
            logActivity($conn, $_SESSION['user_id'], 'Updated customer', $name);
            $message = 'Customer updated successfully';
            $messageType = 'success';
        } else {
            $message = 'Error updating customer';
            $messageType = 'danger';
        }
        $stmt->close();
    } elseif (isset($_POST['delete_customer'])) {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM customers WHERE id = $id");
        logActivity($conn, $_SESSION['user_id'], 'Deleted customer', "Customer ID: $id");
        $message = 'Customer deleted successfully';
        $messageType = 'success';
    }
}

// Export to CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="customers_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Name', 'Mobile', 'Address', 'Beetech ID', 'Joined Date'));

    $result = $conn->query("SELECT * FROM customers");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? clean($_GET['search']) : '';
$searchCondition = '';
if (!empty($search)) {
    $searchCondition = "WHERE mobile LIKE '%$search%' OR name LIKE '%$search%' OR beetech_id LIKE '%$search%'";
}

// Get all customers
$customers = $conn->query("SELECT * FROM customers $searchCondition ORDER BY name");
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-people"></i> Customers</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="bi bi-plus-circle"></i> Add Customer
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

<!-- Search Box -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="search"
                        placeholder="Search by Mobile Number, Name, or Beetech ID..."
                        value="<?php echo htmlspecialchars($search); ?>" autofocus>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            <?php if (!empty($search)): ?>
                <div class="col-12">
                    <a href="customers.php" class="btn btn-sm btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear Search
                    </a>
                    <span class="ms-2 text-muted">
                        Found <?php echo $customers->num_rows; ?> result(s) for "<?php echo htmlspecialchars($search); ?>"
                    </span>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Address</th>
                        <th>Beetech ID</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($customers->num_rows > 0): ?>
                        <?php while ($customer = $customers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $customer['id']; ?></td>
                                <td><?php echo $customer['name']; ?></td>
                                <td>
                                    <i class="bi bi-phone"></i> <?php echo $customer['mobile']; ?>
                                </td>
                                <td><?php echo $customer['address']; ?></td>
                                <td><span class="badge bg-info"><?php echo $customer['beetech_id']; ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                <td>
                                    <a href="customer_profile.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-warning" onclick="editCustomer(<?php echo htmlspecialchars(json_encode($customer)); ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this customer?');">
                                        <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
                                        <button type="submit" name="delete_customer" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                <?php if (!empty($search)): ?>
                                    No customers found matching "<?php echo htmlspecialchars($search); ?>"
                                <?php else: ?>
                                    No customers found
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" name="mobile" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Beetech ID</label>
                        <input type="text" class="form-control" name="beetech_id" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_customer" class="btn btn-primary">Add Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" name="mobile" id="edit_mobile" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="edit_address" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Beetech ID</label>
                        <input type="text" class="form-control" name="beetech_id" id="edit_beetech_id" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_customer" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editCustomer(customer) {
        document.getElementById('edit_id').value = customer.id;
        document.getElementById('edit_name').value = customer.name;
        document.getElementById('edit_mobile').value = customer.mobile;
        document.getElementById('edit_address').value = customer.address;
        document.getElementById('edit_beetech_id').value = customer.beetech_id;
        new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
    }
</script>

<?php require_once 'footer.php'; ?>