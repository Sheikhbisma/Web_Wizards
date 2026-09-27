<?php
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];

$totalOrders = selectData($pdo, "SELECT COUNT(*) AS total FROM orders WHERE farmer_id = ?", [$farmerId]);
$pendingOrders = selectData($pdo, "SELECT COUNT(*) AS total FROM orders WHERE farmer_id = ? AND order_status = 'placed'", [$farmerId]);
$revenue = selectData($pdo, "SELECT SUM(total_amount) AS total FROM orders WHERE farmer_id = ? AND order_status IN ('accepted','ready','completed')", [$farmerId]);
$productsCount = selectData($pdo, "SELECT COUNT(*) AS total FROM products WHERE farmer_id = ?", [$farmerId]);
$reviewsCount = selectData($pdo, "SELECT COUNT(*) AS total FROM reviews WHERE farmer_id = ?", [$farmerId]);

include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-sun"></i> Welcome back, <?php echo sanitize_output($farmer['contact_person'] ?? 'Farmer'); ?></h2>
            <p>Here is what is happening with your stall today.</p>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="f-stats">
        <div class="f-stat">
            <span class="f-stat-icon green"><i class="bi bi-bag-check-fill"></i></span>
            <div>
                <h3><?php echo (int)($totalOrders[0]['total'] ?? 0); ?></h3>
                <p>Total Orders</p>
            </div>
        </div>
        <div class="f-stat">
            <span class="f-stat-icon amber"><i class="bi bi-hourglass-split"></i></span>
            <div>
                <h3><?php echo (int)($pendingOrders[0]['total'] ?? 0); ?></h3>
                <p>Pending Orders</p>
            </div>
        </div>
        <div class="f-stat">
            <span class="f-stat-icon blue"><i class="bi bi-cash-stack"></i></span>
            <div>
                <h3>Rs <?php echo number_format((float)($revenue[0]['total'] ?? 0), 2); ?></h3>
                <p>Revenue Summary</p>
            </div>
        </div>
        <div class="f-stat">
            <span class="f-stat-icon red"><i class="bi bi-box-seam"></i></span>
            <div>
                <h3><?php echo (int)($productsCount[0]['total'] ?? 0); ?></h3>
                <p>My Products</p>
            </div>
        </div>
    </div>

    <div class="f-grid-3">
        <div class="f-card">
            <div class="f-card-head">
                <h5><i class="bi bi-person-badge"></i> Stall Summary</h5>
            </div>
            <div class="f-card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="f-stat-icon green"><i class="bi bi-shop"></i></span>
                    <div>
                        <h4 class="mb-0 fw-bold"><?php echo sanitize_output($farmer['stall_name']); ?></h4>
                        <small class="f-muted">Contact: <?php echo sanitize_output($farmer['contact_person']); ?></small>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="f-chip"><i class="bi bi-clock"></i> Pickup: <?php echo sanitize_output(($farmer['pickup_start'] ?? '-') . ' - ' . ($farmer['pickup_end'] ?? '-')); ?></span>
                </div>
                <div class="mb-3">
                    <span class="f-chip"><i class="bi bi-hourglass-bottom"></i> Cut-off: <?php echo sanitize_output($farmer['cutoff_time'] ?? 'Not set'); ?></span>
                </div>
                <div class="mb-3">
                    <span class="f-chip"><i class="bi bi-star-fill" style="color:var(--f-amber);"></i> Rating: <?php echo !empty($farmer['avg_rating']) ? number_format((float)$farmer['avg_rating'], 1) . ' / 5' : 'New'; ?></span>
                </div>
                <div class="mb-4">
                    <span class="f-chip"><i class="bi bi-chat-quote"></i> Reviews: <?php echo (int)($reviewsCount[0]['total'] ?? 0); ?></span>
                </div>
                <div class="f-actions">
                    <a href="index.php?page=add-profile" class="f-btn outline sm"><i class="bi bi-pencil"></i> Edit Profile</a>
                </div>
            </div>
        </div>

        <div class="f-col-right">
            <div class="f-card">
                <div class="f-card-head">
                    <h5><i class="bi bi-lightning-charge"></i> Quick Actions</h5>
                </div>
                <div class="f-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <a href="index.php?page=farmer-orders" class="f-btn primary block text-center">
                                <i class="bi bi-cart-check"></i> Pre-Orders
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="index.php?page=farmer-product-form" class="f-btn success-soft block text-center">
                                <i class="bi bi-plus-circle"></i> Add Product
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="index.php?page=farmer-stock" class="f-btn outline block text-center">
                                <i class="bi bi-calendar-week"></i> Weekly Stock
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="index.php?page=farmer-history" class="f-btn ghost block text-center">
                                <i class="bi bi-clock-history"></i> Order History
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="f-card">
                <div class="f-card-head">
                    <h5><i class="bi bi-graph-up"></i> Insights</h5>
                    <a href="index.php?page=farmer-reports" class="f-btn ghost sm">View Reports</a>
                </div>
                <div class="f-card-body pad-sm">
                    <p class="mb-0 f-muted">
                        Track your best selling products, top customers and recent sales in one place.
                        <i class="bi bi-arrow-right ms-1"></i>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>