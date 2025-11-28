<?php
require_once 'config.php';

$sale_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get sale details
$sale = $conn->query("SELECT s.*, c.name as customer_name, c.mobile, c.address, c.beetech_id, u.fullname as cashier
                      FROM sales s 
                      JOIN customers c ON s.customer_id = c.id 
                      JOIN users u ON s.created_by = u.id 
                      WHERE s.id = $sale_id")->fetch_assoc();

if (!$sale) {
    die('Invoice not found');
}

// Get sale items
$items = $conn->query("SELECT * FROM sale_items WHERE sale_id = $sale_id");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo str_pad($sale_id, 5, '0', STR_PAD_LEFT); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        @page {
            size: A5;
            margin: 10mm;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 20px;
            max-width: 148mm;
            margin: 0 auto;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }

        .invoice-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .invoice-details {
            margin-bottom: 20px;
        }

        .invoice-details table {
            width: 100%;
            font-size: 11px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .items-table th {
            background: #f0f0f0;
            padding: 8px 5px;
            text-align: left;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        .items-table td {
            padding: 6px 5px;
            border-bottom: 1px dashed #ccc;
        }

        .totals {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .totals table {
            width: 100%;
            font-size: 13px;
        }

        .totals .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 8px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #000;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        /* Mobile Responsiveness */
        @media (max-width: 576px) {
            body {
                padding: 10px;
                font-size: 11px;
            }

            .invoice-header h2 {
                font-size: 18px;
            }

            .invoice-header p {
                font-size: 10px;
            }

            .invoice-details table {
                font-size: 10px;
            }

            .items-table {
                font-size: 10px;
            }

            .items-table th,
            .items-table td {
                padding: 4px 2px;
            }

            .totals table {
                font-size: 11px;
            }

            .totals .grand-total {
                font-size: 14px;
            }

            .footer {
                font-size: 9px;
            }

            .no-print .btn {
                font-size: 12px;
                padding: 8px 12px;
                margin: 5px 2px;
            }
        }

        @media (max-width: 400px) {
            .no-print {
                text-align: center;
            }

            .no-print .btn {
                display: block;
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Invoice
        </button>
        <a href="pos.php" class="btn btn-secondary">New Sale</a>
        <a href="sales.php" class="btn btn-info">View Sales</a>
    </div>

    <div class="invoice-header">
        <h2>Al-Madina Discount Shop</h2>
        <p style="margin: 5px 0;">Barmi Bazar, Barmi, Sreepur, Gazipur</p>
        <p style="margin: 5px 0;">Phone: +880 1881 196156 | Email: almadinads@gmail.com</p>
    </div>

    <div class="invoice-details">
        <table>
            <tr>
                <td><strong>Invoice #:</strong> <?php echo str_pad($sale_id, 5, '0', STR_PAD_LEFT); ?></td>
                <td class="text-right"><strong>Date:</strong> <?php echo date('d/m/Y h:i A', strtotime($sale['created_at'])); ?></td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 10px;"><strong>Customer Details:</strong></td>
            </tr>
            <tr>
                <td><strong>Name:</strong> <?php echo $sale['customer_name']; ?></td>
                <td class="text-right"><strong>Beetech ID: <?php echo $sale['beetech_id']; ?></strong></td>
            </tr>
            <tr>
                <td><strong>Mobile:</strong> <?php echo $sale['mobile']; ?></td>
                <td class="text-right"><strong>Cashier:</strong> <?php echo $sale['cashier']; ?></td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 50%;">Item</th>
                <th style="width: 15%;" class="text-right">Price</th>
                <th style="width: 15%;" class="text-right">Qty</th>
                <th style="width: 15%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $counter = 1;
            while ($item = $items->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo $item['product_name']; ?></td>
                    <td class="text-right">৳<?php echo number_format($item['price'], 2); ?></td>
                    <td class="text-right"><?php echo $item['quantity']; ?></td>
                    <td class="text-right">৳<?php echo number_format($item['total'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Beetech point Earned:</td>
                <td class="text-right" style="color: green;">৳<?php echo number_format($sale['discount'] / 6, 2); ?></td>
            </tr>
            <tr class="grand-total">
                <td>GRAND TOTAL PAYABLE:</td>
                <td class="text-right">৳<?php echo number_format($sale['subtotal'], 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>Thank you for shopping with us!</strong></p>
        <p>All sales are final. No returns or exchanges without receipt.</p>
        <p style="margin-top: 15px;">--- This is a computer generated invoice ---</p>
    </div>
</body>

</html>