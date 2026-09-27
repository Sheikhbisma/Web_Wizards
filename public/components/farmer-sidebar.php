<?php
$currentPage = $_GET['page'] ?? '';
$username = $_SESSION['username'] ?? 'Farmer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketLink — Farmer Panel</title>
    <link rel="icon" type="image/svg+xml" href="Uploads/img/log.svg">
    <link rel="stylesheet" href="css/farmer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="farm-body">

<div class="f-overlay" id="fOverlay"></div>

<aside class="f-sidebar" id="fSidebar">
    <div class="f-brand">
        <span class="f-brand-mark"><img src="Uploads/img/logo-nav.png" alt="MarketLink" width="44" height="29" decoding="async"></span>
        <span>
            <span class="f-brand-name">MarketLink</span>
            <small class="f-brand-role">Farmer Panel</small>
        </span>
    </div>
    <ul class="f-menu">
        <li>
            <a href="index.php?page=farmer-dashboard" class="<?php echo $currentPage === 'farmer-dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=add-profile" class="<?php echo $currentPage === 'add-profile' ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i>
                <span>My Profile</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=farmer-markets" class="<?php echo $currentPage === 'farmer-markets' ? 'active' : ''; ?>">
                <i class="bi bi-shop"></i>
                <span>My Markets</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=farmer-products" class="<?php echo in_array($currentPage, ['farmer-products', 'farmer-product-form']) ? 'active' : ''; ?>">
                <i class="bi bi-basket"></i>
                <span>My Products</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=farmer-stock" class="<?php echo $currentPage === 'farmer-stock' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-week"></i>
                <span>Weekly Stock</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=farmer-orders" class="<?php echo $currentPage === 'farmer-orders' ? 'active' : ''; ?>">
                <i class="bi bi-cart-check"></i>
                <span>Pre-Orders</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=farmer-history" class="<?php echo $currentPage === 'farmer-history' ? 'active' : ''; ?>">
                <i class="bi bi-clock-history"></i>
                <span>Order History</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=farmer-reviews" class="<?php echo $currentPage === 'farmer-reviews' ? 'active' : ''; ?>">
                <i class="bi bi-star"></i>
                <span>Reviews</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=farmer-reports" class="<?php echo $currentPage === 'farmer-reports' ? 'active' : ''; ?>">
                <i class="bi bi-bar-chart"></i>
                <span>Insights</span>
            </a>
        </li>
        <li class="f-divider" role="presentation"></li>
        <li>
            <a href="index.php?page=logout" class="f-logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>

<header class="f-topbar">
    <button class="f-hamburger" id="fToggle" aria-label="Open menu" aria-controls="fSidebar" aria-expanded="false">
        <i class="bi bi-list"></i>
    </button>
    <h1>
        <?php
        $pageTitles = [
            'farmer-dashboard' => 'Dashboard',
            'add-profile' => 'My Profile',
            'farmer-markets' => 'My Markets',
            'farmer-products' => 'My Products',
            'farmer-product-form' => 'Product Form',
            'farmer-stock' => 'Weekly Stock',
            'farmer-orders' => 'Pre-Orders',
            'farmer-history' => 'Order History',
            'farmer-reviews' => 'Reviews',
            'farmer-reports' => 'Insights'
        ];
        echo $pageTitles[$currentPage] ?? 'Farmer Panel';
        ?>
    </h1>
    <div class="f-topbar-user">
        <i class="bi bi-person-circle"></i>
        <span><?php echo htmlspecialchars($username); ?></span>
    </div>
</header>

<main class="f-content">