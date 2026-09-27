<?php
require_once "../private/config/dbconnect.php";
require_once "../private/config/functions.php";
require_once "../private/config/customer.php";

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

if ($page == 'home' || $page == '') {
    include '../private/views/home.php';
} elseif ($page == 'about') {
    include '../private/views/about-us.php';
} elseif ($page == 'contact') {
    include '../private/views/contact-us.php';
} elseif ($page == 'login') {
    include '../private/views/signup.php';
} elseif ($page == 'signup') {
    include '../private/views/signup.php';
} elseif ($page == 'verify-otp') {
    include __DIR__ . '/../private/backend-scripting/verify-otp.php';
} elseif ($page == 'logout') {
    include __DIR__ . '/../private/backend-scripting/logout.php';
} elseif ($page == 'customer-dashboard') {
    include __DIR__ . '/../private/views/Customer/dashboard.php';
} elseif ($page == 'farmer-dashboard') {
    include __DIR__ . '/../private/views/Farmer/dashboard.php';
} elseif ($page == 'admin-dashboard') {
    include __DIR__ . '/../private/views/Admin/dashboard.php';
} elseif ($page == 'add-profile') {
    include __DIR__ . '/../private/views/Farmer/add-profile.php';
} elseif ($page == 'farmer-products') {
    include __DIR__ . '/../private/views/Farmer/products.php';
} elseif ($page == 'farmer-product-form') {
    include __DIR__ . '/../private/views/Farmer/product-form.php';
} elseif ($page == 'farmer-stock') {
    include __DIR__ . '/../private/views/Farmer/weekly-stock.php';
} elseif ($page == 'farmer-orders') {
    include __DIR__ . '/../private/views/Farmer/orders.php';
} elseif ($page == 'farmer-history') {
    include __DIR__ . '/../private/views/Farmer/order-history.php';
} elseif ($page == 'farmer-markets') {
    include __DIR__ . '/../private/views/Farmer/markets.php';
} elseif ($page == 'farmer-reviews') {
    include __DIR__ . '/../private/views/Farmer/reviews.php';
} elseif ($page == 'farmer-reports') {
    include __DIR__ . '/../private/views/Farmer/reports.php';
} elseif ($page == 'add-market') {
    include __DIR__ . '/../private/views/Admin/manage-markets.php';
} elseif ($page == 'manage-markets') {
    include __DIR__ . '/../private/views/Admin/manage-markets.php';
}elseif ($page == 'manage-farmers') {
    include __DIR__ . '/../private/views/Admin/manage-farmers.php';
}elseif ($page == 'manage-customers') {
    include __DIR__ . '/../private/views/Admin/manage-customers.php';
}elseif ($page == 'product-listing') {
    include __DIR__ . '/../private/views/Admin/product-listing.php';
}elseif ($page == 'order-listing') {
    include __DIR__ . '/../private/views/Admin/order-listing.php';
} elseif ($page == 'admin-reviews') {
    include __DIR__ . '/../private/views/Admin/manage-reviews.php';
} elseif ($page == 'admin-reports') {
    include __DIR__ . '/../private/views/Admin/reports.php';
} elseif ($page == 'admin-categories') {
    include __DIR__ . '/../private/views/Admin/categories.php';
} elseif ($page == 'admin-announcements') {
    include __DIR__ . '/../private/views/Admin/announcements.php';
} elseif ($page == 'admin-settings') {
    include __DIR__ . '/../private/views/Admin/settings.php';
} elseif ($page == 'add-farmer-market') {
    include __DIR__ . '/../private/views/Farmer/farmer-market-form.php';
}elseif ($page == 'add-farmer-market') {
    include __DIR__ . '/../private/views/Farmer/farmer-market-form.php';
} elseif ($page == 'markets') {
    include __DIR__ . '/../private/views/Customer/markets.php';
} elseif ($page == 'market') {
    include __DIR__ . '/../private/views/Customer/market.php';
} elseif ($page == 'farmers') {
    include __DIR__ . '/../private/views/Customer/farmers.php';
} elseif ($page == 'farmer') {
    include __DIR__ . '/../private/views/Customer/farmer.php';
} elseif ($page == 'products') {
    include __DIR__ . '/../private/views/Customer/products.php';
} elseif ($page == 'product') {
    include __DIR__ . '/../private/views/Customer/product.php';
} elseif ($page == 'search') {
    include __DIR__ . '/../private/views/Customer/search.php';
} elseif ($page == 'cart') {
    include __DIR__ . '/../private/views/Customer/cart.php';
} elseif ($page == 'checkout') {
    include __DIR__ . '/../private/views/Customer/checkout.php';
} elseif ($page == 'orders') {
    include __DIR__ . '/../private/views/Customer/orders.php';
} elseif ($page == 'order') {
    include __DIR__ . '/../private/views/Customer/order-detail.php';
} elseif ($page == 'favorites') {
    include __DIR__ . '/../private/views/Customer/favorites.php';
} elseif ($page == 'notifications') {
    include __DIR__ . '/../private/views/Customer/notifications.php';
} elseif ($page == 'profile') {
    include __DIR__ . '/../private/views/Customer/profile.php';
} elseif ($page == 'forgot-password') {
    include __DIR__ . '/../private/views/forgot-password.php';
} elseif ($page == 'reset-password') {
    include __DIR__ . '/../private/views/reset-password.php';
} elseif ($page == 'ai-chat') {
    include __DIR__ . '/../private/views/Customer/ai-chat.php';
} elseif ($page == 'sitemap') {
    include __DIR__ . '/../private/views/sitemap.php';
}else {
    echo "<h2>404 Page Not Found</h2>";
}