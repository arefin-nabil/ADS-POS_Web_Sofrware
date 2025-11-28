<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Get theme preference
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo $theme; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            padding-top: 56px;
        }

        /* Desktop Sidebar */
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: inherit;
            padding: 0.75rem 1rem;
        }

        .sidebar .nav-link.active {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            border-left: 3px solid var(--bs-primary);
        }

        .sidebar .nav-link:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
        }

        main {
            margin-left: 240px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 56px;
                left: -100%;
                width: 250px;
                height: calc(100vh - 56px);
                transition: left 0.3s ease-in-out;
                z-index: 1040;
                background: var(--bs-body-bg);
                border-right: 1px solid var(--bs-border-color);
                padding-top: 0;
            }

            .sidebar.show {
                left: 0;
            }

            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 56px;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1030;
            }

            .sidebar-backdrop.show {
                display: block;
            }

            main {
                margin-left: 0;
                padding-left: 15px;
                padding-right: 15px;
            }

            .mobile-menu-btn {
                display: inline-block !important;
            }

            /* Table responsiveness */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Card adjustments */
            .card {
                margin-bottom: 1rem;
            }

            /* Form adjustments */
            .btn-toolbar {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-toolbar .btn {
                width: 100%;
            }
        }

        .mobile-menu-btn {
            display: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }

        /* POS Mobile Optimization */
        @media (max-width: 991.98px) {
            .pos-container {
                grid-template-columns: 1fr !important;
            }

            .cart-panel {
                position: sticky;
                bottom: 0;
                max-height: 50vh;
                overflow-y: auto;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top no-print">
        <div class="container-fluid">
            <button class="btn btn-link text-white mobile-menu-btn me-2" onclick="toggleSidebar()" type="button">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-shop-window"></i> Al Madina Discount Shop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button class="btn btn-link nav-link" onclick="toggleTheme()">
                            <i class="bi bi-moon-stars" id="themeIcon"></i>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['fullname']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar Backdrop (Mobile) -->
    <div class="sidebar-backdrop no-print" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <div class="container-fluid no-print">
        <div class="row">
            <nav class="col-md-2 d-md-block bg-body-secondary sidebar" id="sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pos.php' ? 'active' : ''; ?>" href="pos.php">
                                <i class="bi bi-cart3"></i> POS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
                                <i class="bi bi-box-seam"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                                <i class="bi bi-people"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>" href="sales.php">
                                <i class="bi bi-receipt"></i> Sales
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                                    <i class="bi bi-person-gear"></i> Users
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                <?php // Content will be inserted here 
                ?>