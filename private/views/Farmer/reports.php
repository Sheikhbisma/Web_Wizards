<?php
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];

$totalOrders  = selectData($pdo, "SELECT COUNT(*) AS total FROM orders WHERE farmer_id = ?", [$farmerId]);
$pendingCount = selectData($pdo, "SELECT COUNT(*) AS total FROM orders WHERE farmer_id = ? AND order_status = 'placed'", [$farmerId]);
$completedCount = selectData($pdo, "SELECT COUNT(*) AS total FROM orders WHERE farmer_id = ? AND order_status = 'completed'", [$farmerId]);
$revenue      = selectData($pdo, "SELECT SUM(total_amount) AS total FROM orders WHERE farmer_id = ? AND order_status IN ('accepted','ready','completed')", [$farmerId]);
$uniqueCustomers = selectData($pdo, "SELECT COUNT(DISTINCT customer_id) AS total FROM orders WHERE farmer_id = ?", [$farmerId]);

$bestSellers = selectData($pdo,
    "SELECT p.name, SUM(oi.quantity) AS total_qty, SUM(oi.subtotal) AS total_revenue
     FROM order_items oi
     JOIN orders o ON oi.order_id = o.order_id
     JOIN products p ON oi.product_id = p.product_id
     WHERE o.farmer_id = ? AND o.order_status IN ('accepted','ready','completed')
     GROUP BY p.name
     ORDER BY total_qty DESC
     LIMIT 5", [$farmerId]);

$topCustomers = selectData($pdo,
    "SELECT u.username, c.full_name, COUNT(o.order_id) AS order_count, SUM(o.total_amount) AS total_spent
     FROM orders o
     JOIN customers c ON o.customer_id = c.customer_id
     LEFT JOIN users u ON c.user_id = u.id
     WHERE o.farmer_id = ? AND o.order_status IN ('accepted','ready','completed')
     GROUP BY o.customer_id, u.username, c.full_name
     ORDER BY total_spent DESC
     LIMIT 5", [$farmerId]);

$recentSales = selectData($pdo,
    "SELECT order_id, total_amount, order_status, order_date
     FROM orders
     WHERE farmer_id = ? AND order_status IN ('accepted','ready','completed')
     ORDER BY order_date DESC
     LIMIT 7", [$farmerId]);

$totalSalesValue = (float)($revenue[0]['total'] ?? 0);
$maxSellerQty = $bestSellers[0]['total_qty'] ?? 0;

include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-bar-chart"></i> Insights</h2>
            <p>Performance overview for your farm stall.</p>
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
                <h3><?php echo (int)($pendingCount[0]['total'] ?? 0); ?></h3>
                <p>Pending Orders</p>
            </div>
        </div>
        <div class="f-stat">
            <span class="f-stat-icon blue"><i class="bi bi-check-circle"></i></span>
            <div>
                <h3><?php echo (int)($completedCount[0]['total'] ?? 0); ?></h3>
                <p>Completed Orders</p>
            </div>
        </div>
        <div class="f-stat">
            <span class="f-stat-icon red"><i class="bi bi-cash-stack"></i></span>
            <div>
                <h3>Rs <?php echo number_format($totalSalesValue, 2); ?></h3>
                <p>Revenue Summary</p>
            </div>
        </div>
        <div class="f-stat">
            <span class="f-stat-icon grey"><i class="bi bi-people"></i></span>
            <div>
                <h3><?php echo (int)($uniqueCustomers[0]['total'] ?? 0); ?></h3>
                <p>Unique Customers</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="f-card h-100">
                <div class="f-card-head">
                    <h5><i class="bi bi-trophy"></i> Best Selling Products</h5>
                </div>
                <div class="f-card-body">
                    <?php if (empty($bestSellers)): ?>
                        <p class="f-muted mb-0">No sales yet. Once orders come in, your best sellers will appear here.</p>
                    <?php else: ?>
                        <?php foreach ($bestSellers as $best): ?>
                            <?php $barPercent = $maxSellerQty > 0 ? round(($best['total_qty'] / $maxSellerQty) * 100) : 0; ?>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="f-muted"><?php echo sanitize_output($best['name']); ?></strong>
                                    <span class="f-badge green"><?php echo (int)$best['total_qty']; ?> sold</span>
                                </div>
                                <div class="f-progress">
                                    <div style="width: <?php echo $barPercent; ?>%;"></div>
                                </div>
                                <small class="f-muted">Rs <?php echo number_format((float)$best['total_revenue'], 2); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="f-card h-100">
                <div class="f-card-head">
                    <h5><i class="bi bi-people-fill"></i> Top Customers</h5>
                </div>
                <div class="f-card-body">
                    <?php if (empty($topCustomers)): ?>
                        <p class="f-muted mb-0">No sales yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="f-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Orders</th>
                                        <th>Total Spent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topCustomers as $cust): ?>
                                        <tr>
                                            <td><?php echo sanitize_output($cust['username'] ?? $cust['full_name'] ?? 'Customer'); ?></td>
                                            <td><?php echo (int)$cust['order_count']; ?></td>
                                            <td class="f-revenue">Rs <?php echo number_format((float)$cust['total_spent'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="f-card">
        <div class="f-card-head">
            <h5><i class="bi bi-arrow-repeat"></i> Recent Sales</h5>
        </div>
        <div class="f-card-body flush">
            <?php if (empty($recentSales)): ?>
                <div class="f-empty">
                    <i class="bi bi-arrow-repeat"></i>
                    No recent sales.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="f-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSales as $sale): ?>
                                <tr>
                                    <td><span class="f-order-id">#<?php echo (int)$sale['order_id']; ?></span></td>
                                    <td><?php echo date("d M Y, h:i A", strtotime($sale['order_date'])); ?></td>
                                    <td><span class="f-badge green"><?php echo ucfirst(sanitize_output($sale['order_status'])); ?></span></td>
                                    <td class="f-revenue">Rs <?php echo number_format((float)$sale['total_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>