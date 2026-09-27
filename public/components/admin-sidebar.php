<?php
$currentPage = $_GET['page'] ?? '';
$username = $_SESSION['username'] ?? 'Stanton';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketLink — Admin Panel</title>
    <link rel="icon" type="image/svg+xml" href="Uploads/img/log.svg">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Fraunces:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body class="adm-body">

<div class="a-overlay" id="aOverlay"></div>

<aside class="a-sidebar" id="aSidebar">
    <div class="a-brand">
        <span class="a-brand-mark"><img src="Uploads/img/logo-nav.png" alt="MarketLink" width="40" height="27" decoding="async"></span>
        <span class="a-brand-name">MarketLink</span>
    </div>

    <ul class="a-menu">
        <li>
            <a href="index.php?page=admin-dashboard" class="<?php echo in_array($currentPage, ['admin-dashboard', '']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-table-cells-large"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=admin-reports" class="<?php echo $currentPage === 'admin-reports' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-simple"></i>
                <span>Analytics</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=product-listing" class="<?php echo in_array($currentPage, ['admin-products', 'product-listing']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-seedling"></i>
                <span>Products</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=manage-farmers" class="<?php echo in_array($currentPage, ['admin-farmers', 'manage-farmers']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-people-roof"></i>
                <span>Farmers</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=manage-markets" class="<?php echo in_array($currentPage, ['admin-markets', 'manage-markets', 'add-market']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-store"></i>
                <span>Markets</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=manage-customers" class="<?php echo in_array($currentPage, ['admin-customers', 'manage-customers']) ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i>
                <span>Customers</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=order-listing" class="<?php echo $currentPage === 'order-listing' ? 'active' : ''; ?>">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Orders</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=admin-reviews" class="<?php echo $currentPage === 'admin-reviews' ? 'active' : ''; ?>">
                <i class="fa-solid fa-star-half-stroke"></i>
                <span>Reviews</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=admin-categories" class="<?php echo $currentPage === 'admin-categories' ? 'active' : ''; ?>">
                <i class="fa-solid fa-tags"></i>
                <span>Categories</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=admin-announcements" class="<?php echo $currentPage === 'admin-announcements' ? 'active' : ''; ?>">
                <i class="fa-solid fa-bullhorn"></i>
                <span>Announcements</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=admin-settings" class="<?php echo $currentPage === 'admin-settings' ? 'active' : ''; ?>">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>

    <!-- Sidebar Bottom Promo Card (Matched to Reference Image) -->
    <div class="a-sidebar-bottom px-3 pb-3">
        <div class="a-promo-card">
            <div class="a-promo-img">
                <i class="fa-solid fa-seedling" aria-hidden="true"></i>
            </div>
            <a href="index.php?page=manage-farmers" class="a-promo-btn">
                <span>+ Add farm</span>
            </a>
        </div>
        <a href="index.php?page=logout" class="a-logout-link mt-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>LOG OUT</span>
        </a>
    </div>
</aside>

<header class="a-topbar">
    <button class="a-hamburger" id="aToggle" aria-label="Open menu">
        <i class="fa-solid fa-bars"></i>
    </button>
    
    <!-- Search Bar Pill (Matched to Reference Image) -->
    <div class="a-search-box">
        <i class="fa-solid fa-magnifying-glass a-search-icon"></i>
        <input type="search" placeholder="Search this page..." class="a-search-input" id="admLiveSearch" aria-label="Search the current admin page">
    </div>

    <div class="a-topbar-right">
        <button class="a-topbar-round-btn" title="Messages">
            <i class="fa-regular fa-comment-dots"></i>
        </button>
        <button class="a-topbar-round-btn" title="Notifications">
            <i class="fa-regular fa-bell"></i>
            <span class="a-badge-dot"></span>
        </button>
        <div class="a-topbar-user-pill">
            <div class="a-user-avatar-wrap">
                <span class="a-user-initials" aria-hidden="true"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                <span class="a-user-online-dot"></span>
            </div>
            <div class="a-user-info">
                <span class="a-user-name"><?php echo htmlspecialchars($username); ?></span>
                <span class="a-user-sub">MarketLink Admin</span>
            </div>
            <i class="fa-solid fa-chevron-down a-user-dropdown-icon"></i>
        </div>
    </div>
</header>

<main class="a-content">
