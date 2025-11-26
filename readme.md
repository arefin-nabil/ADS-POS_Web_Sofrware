# POS System - Installation & Setup Guide

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser (Chrome, Firefox, Edge)

## 🚀 Installation Steps

### 1. Extract Files

Extract all files to your web server directory (e.g., `htdocs`, `www`, or `public_html`)

```
pos_system/
├── config.php
├── login.php
├── header.php
├── footer.php
├── dashboard.php
├── pos.php
├── products.php
├── customers.php
├── customer_profile.php
├── sales.php
├── get_sale_details.php
├── users.php
├── invoice.php
├── logout.php
└── database.sql
```

### 2. Create Database

1. Open phpMyAdmin or MySQL command line
2. Run the SQL script from `database.sql` file
3. This will create:
   - Database: `pos_system`
   - All required tables
   - Default admin user
   - Sample products and customers

### 3. Configure Database Connection

Edit `config.php` and update these lines if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Your MySQL username
define('DB_PASS', '');              // Your MySQL password
define('DB_NAME', 'pos_system');
```

### 4. Set Permissions

Ensure the web server has read/write permissions:

```bash
chmod 755 /path/to/pos_system
chmod 644 /path/to/pos_system/*.php
```

### 5. Access the Application

Open your web browser and navigate to:
```
http://localhost/pos_system/login.php
```

## 🔐 Default Login Credentials

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Important:** Change the default password after first login!

## 📱 Features Overview

### 1. **Dashboard**
- Today's sales summary
- Total customers count
- Low stock alerts
- Recent sales list

### 2. **POS (Point of Sale)**
- Barcode scanner support
- Product search
- Real-time cart management
- Automatic 5% discount for all customers
- Invoice generation

### 3. **Product Management**
- Add/Edit/Delete products
- Stock management
- Stock history log
- Low stock notifications
- CSV export

### 4. **Customer Management**
- Customer profiles
- Purchase history
- Beetech ID system
- Customer statistics
- CSV export

### 5. **Sales History**
- Complete sales records
- Filter by date and customer
- Detailed sale view
- Invoice reprinting
- CSV export

### 6. **User Management** (Admin Only)
- Create managers
- Role-based access
- User activity logging

### 7. **Additional Features**
- Dark/Light theme toggle
- A5 invoice printing
- Activity logging
- Responsive design
- Bootstrap 5 UI

## 🎯 User Roles & Permissions

### Admin
✅ Full system access
✅ Create/edit/delete users
✅ View all sales and reports
✅ Manage products and stock
✅ Manage customers
✅ Process sales

### Manager
✅ Add/edit products
✅ Manage stock
✅ Create customers
✅ Process sales
✅ View own sales
❌ Cannot manage users
❌ Cannot view other users' activities

## 🛒 How to Use POS

1. **Login** with your credentials
2. Navigate to **POS** from sidebar
3. **Select a customer** from dropdown
4. **Add products** by:
   - Scanning barcode (enter in barcode field)
   - Clicking product cards
   - Searching products
5. **Adjust quantities** using +/- buttons
6. Review totals (automatic 5% discount applied)
7. Click **Complete Sale**
8. Invoice will automatically open for printing

## 🖨️ Invoice Printing

- A5 size format (148mm × 210mm)
- Professional layout
- Customer details
- Itemized list
- Discount information
- Printable directly from browser

## 🔧 Barcode Scanner Setup

The system supports standard USB barcode scanners:

1. Plug in your barcode scanner
2. Focus on the barcode input field in POS
3. Scan product barcode
4. Press Enter or scan next item
5. Product automatically adds to cart

**Barcode Format:** Any format (EAN-13, UPC, Code 128, etc.)

## 📊 CSV Export Features

Export data for Excel/Google Sheets:
- **Products:** All product details with stock
- **Customers:** Customer information
- **Sales:** Complete sales history with filters

## 🎨 Theme Toggle

Switch between light and dark modes:
- Click moon/sun icon in navbar
- Preference saved in browser cookie
- Applies across all pages

## 🔒 Security Features

- Password hashing (bcrypt)
- SQL injection protection (prepared statements)
- XSS protection (input sanitization)
- Session management
- Role-based access control
- Activity logging

## 🐛 Troubleshooting

### Database Connection Error
- Check MySQL service is running
- Verify credentials in `config.php`
- Ensure database exists

### Products Not Showing in POS
- Check product stock > 0
- Verify products table has data
- Clear browser cache

### Invoice Not Printing
- Allow pop-ups for the site
- Check printer settings (A5 paper size)
- Try different browser

### Theme Not Saving
- Enable cookies in browser
- Check browser cookie settings
- Clear site data and try again

## 📝 Database Backup

Regular backups recommended:

```bash
mysqldump -u root -p pos_system > backup_$(date +%Y%m%d).sql
```

Restore from backup:

```bash
mysql -u root -p pos_system < backup_20241126.sql
```

## 🔄 Updates & Maintenance

- Regularly backup database
- Monitor stock levels
- Review activity logs
- Update user passwords periodically
- Clear old sales data if needed

## 📞 Support

For issues or questions:
1. Check this README
2. Review error messages
3. Check PHP error logs
4. Verify database structure

## 📄 License

This POS system is provided as-is for educational and commercial use.

## 🎉 Quick Start Checklist

- [ ] Database created and imported
- [ ] config.php updated with correct credentials
- [ ] Logged in with admin/admin123
- [ ] Changed default admin password
- [ ] Added real products
- [ ] Added real customers
- [ ] Tested a sale transaction
- [ ] Printed test invoice
- [ ] Created manager users (if needed)
- [ ] Configured barcode scanner (if available)

---

**System Ready!** Start processing sales and managing your business efficiently! 🚀