<?php
$page_title = 'POS - Billing';
require_once 'header.php';

$message = '';
$messageType = '';

// Handle sale submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_sale'])) {
    $customer_id = intval($_POST['customer_id']);
    $items = json_decode($_POST['items'], true);

    if (empty($items)) {
        $message = 'Please add items to the cart';
        $messageType = 'danger';
    } else {
        $conn->begin_transaction();

        try {
            // Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $discount = $subtotal * 0.05; // 5% discount
            $total = $subtotal - $discount;

            // Insert sale
            $stmt = $conn->prepare("INSERT INTO sales (customer_id, subtotal, discount, total, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("idddi", $customer_id, $subtotal, $discount, $total, $_SESSION['user_id']);
            $stmt->execute();
            $sale_id = $stmt->insert_id;
            $stmt->close();

            // Insert sale items and update stock
            foreach ($items as $item) {
                $item_total = $item['price'] * $item['quantity'];
                $stmt = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, product_name, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisidd", $sale_id, $item['id'], $item['name'], $item['quantity'], $item['price'], $item_total);
                $stmt->execute();
                $stmt->close();

                // Update stock
                $conn->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['id']}");

                // Log stock change
                $conn->query("INSERT INTO stock_log (product_id, type, quantity, note, created_by) 
                              VALUES ({$item['id']}, 'sale', {$item['quantity']}, 'Sale #$sale_id', {$_SESSION['user_id']})");
            }

            $conn->commit();
            logActivity($conn, $_SESSION['user_id'], 'Completed sale', "Sale #$sale_id, Total: ৳$total");

            // Redirect to invoice
            redirect("invoice.php?id=$sale_id");
        } catch (Exception $e) {
            $conn->rollback();
            $message = 'Error completing sale: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Get all customers for dropdown
$customers = $conn->query("SELECT * FROM customers ORDER BY name");
?>

<style>
    .pos-container {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 20px;
        height: calc(100vh - 150px);
    }

    .products-panel {
        overflow-y: auto;
    }

    .cart-panel {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        padding: 20px;
        display: flex;
        flex-direction: column;
    }

    .cart-items {
        flex: 1;
        overflow-y: auto;
        margin: 15px 0;
    }

    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid var(--bs-border-color);
        border-radius: 5px;
    }

    .product-card {
        cursor: pointer;
        transition: all 0.3s;
        height: 100%;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .barcode-input {
        font-size: 1.2rem;
        padding: 15px;
    }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cart3"></i> Point of Sale</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="pos-container">
    <!-- Products Panel -->
    <div class="products-panel">
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                <input type="text" class="form-control barcode-input" id="barcodeInput"
                    placeholder="Scan barcode or search product..." autofocus>
            </div>
        </div>

        <div class="row g-3" id="productsGrid">
            <?php
            $products = $conn->query("SELECT * FROM products WHERE stock > 0 ORDER BY name");
            while ($product = $products->fetch_assoc()):
            ?>
                <div class="col-md-3">
                    <div class="card product-card" onclick='addToCart(<?php echo json_encode($product); ?>)'>
                        <div class="card-body text-center">
                            <i class="bi bi-box-seam display-4 text-primary"></i>
                            <h6 class="mt-2"><?php echo $product['name']; ?></h6>
                            <p class="text-muted small mb-1"><?php echo $product['barcode']; ?></p>
                            <h5 class="text-success">৳<?php echo number_format($product['selling_price'], 2); ?></h5>
                            <small class="text-muted">Stock: <?php echo $product['stock']; ?></small>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="cart-panel">
        <h4 class="mb-3"><i class="bi bi-cart-check"></i> Shopping Cart</h4>

        <div class="mb-3">
            <label class="form-label">Select Customer</label>
            <select class="form-select" id="customerId" required>
                <option value="">-- Select Customer --</option>
                <?php
                $customers->data_seek(0);
                while ($customer = $customers->fetch_assoc()):
                ?>
                    <option value="<?php echo $customer['id']; ?>">
                        <?php echo $customer['name']; ?> (<?php echo $customer['beetech_id']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="text-center text-muted py-5">
                <i class="bi bi-cart-x display-1"></i>
                <p>Cart is empty</p>
            </div>
        </div>

        <div class="cart-summary">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <strong id="subtotal">৳0.00</strong>
            </div>
            <div class="d-flex justify-content-between mb-2 text-success">
                <span>Discount (5%):</span>
                <strong id="discount">৳0.00</strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-3">
                <h5>Total:</h5>
                <h5 id="total">৳0.00</h5>
            </div>

            <button class="btn btn-primary btn-lg w-100" onclick="completeSale()">
                <i class="bi bi-check-circle"></i> Complete Sale
            </button>
            <button class="btn btn-outline-danger w-100 mt-2" onclick="clearCart()">
                <i class="bi bi-trash"></i> Clear Cart
            </button>
        </div>
    </div>
</div>

<form id="saleForm" method="POST" style="display:none;">
    <input type="hidden" name="customer_id" id="formCustomerId">
    <input type="hidden" name="items" id="formItems">
    <input type="hidden" name="complete_sale" value="1">
</form>

<script>
    let cart = [];
    let allProducts = <?php
                        $products->data_seek(0);
                        $productsArray = [];
                        while ($p = $products->fetch_assoc()) {
                            $productsArray[] = $p;
                        }
                        echo json_encode($productsArray);
                        ?>;

    // Barcode scanner support
    document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const barcode = this.value.trim();
            const product = allProducts.find(p => p.barcode === barcode);
            if (product) {
                addToCart(product);
                this.value = '';
            } else {
                alert('Product not found!');
            }
        }
    });

    // Search functionality
    document.getElementById('barcodeInput').addEventListener('input', function(e) {
        const search = this.value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.closest('.col-md-3').style.display = text.includes(search) ? '' : 'none';
        });
    });

    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);

        if (existingItem) {
            if (existingItem.quantity < parseInt(product.stock)) {
                existingItem.quantity++;
            } else {
                alert('Not enough stock!');
                return;
            }
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.selling_price),
                quantity: 1,
                stock: parseInt(product.stock)
            });
        }

        updateCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        updateCart();
    }

    function updateQuantity(index, change) {
        const item = cart[index];
        const newQuantity = item.quantity + change;

        if (newQuantity <= 0) {
            removeFromCart(index);
        } else if (newQuantity <= item.stock) {
            item.quantity = newQuantity;
            updateCart();
        } else {
            alert('Not enough stock!');
        }
    }

    function updateCart() {
        const cartItemsDiv = document.getElementById('cartItems');

        if (cart.length === 0) {
            cartItemsDiv.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-cart-x display-1"></i>
                <p>Cart is empty</p>
            </div>
        `;
            document.getElementById('subtotal').textContent = '৳0.00';
            document.getElementById('discount').textContent = '৳0.00';
            document.getElementById('total').textContent = '৳0.00';
            return;
        }

        let html = '';
        let subtotal = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            html += `
            <div class="cart-item">
                <div>
                    <strong>${item.name}</strong><br>
                    <small class="text-muted">৳${item.price.toFixed(2)} × ${item.quantity}</small>
                </div>
                <div class="d-flex align-items-center">
                    <div class="btn-group btn-group-sm me-2">
                        <button class="btn btn-outline-secondary" onclick="updateQuantity(${index}, -1)">-</button>
                        <button class="btn btn-outline-secondary" disabled>${item.quantity}</button>
                        <button class="btn btn-outline-secondary" onclick="updateQuantity(${index}, 1)">+</button>
                    </div>
                    <strong>৳${itemTotal.toFixed(2)}</strong>
                    <button class="btn btn-sm btn-danger ms-2" onclick="removeFromCart(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        `;
        });

        cartItemsDiv.innerHTML = html;

        const discount = subtotal * 0.05;
        const total = subtotal - discount;

        document.getElementById('subtotal').textContent = '৳' + subtotal.toFixed(2);
        document.getElementById('discount').textContent = '৳' + discount.toFixed(2);
        document.getElementById('total').textContent = '৳' + total.toFixed(2);
    }

    function clearCart() {
        if (confirm('Clear all items from cart?')) {
            cart = [];
            updateCart();
        }
    }

    function completeSale() {
        const customerId = document.getElementById('customerId').value;

        if (!customerId) {
            alert('Please select a customer!');
            return;
        }

        if (cart.length === 0) {
            alert('Cart is empty!');
            return;
        }

        document.getElementById('formCustomerId').value = customerId;
        document.getElementById('formItems').value = JSON.stringify(cart);
        document.getElementById('saleForm').submit();
    }
</script>

<?php require_once 'footer.php'; ?>