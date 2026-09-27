<?php
validateAdmin();

$totalOrders   = selectData($pdo, "SELECT COUNT(*) AS total FROM orders");
$totalRevenue  = selectData($pdo, "SELECT SUM(total_amount) AS total FROM orders WHERE order_status IN ('accepted','ready','completed')");
$completedCount = selectData($pdo, "SELECT COUNT(*) AS total FROM orders WHERE order_status = 'completed'");
$avgOrderValue = selectData($pdo, "SELECT AVG(total_amount) AS total FROM orders WHERE order_status IN ('accepted','ready','completed')");

// Market ke hisaab se revenue
$revenueByMarket = selectData($pdo,
    "SELECT m.market_name, COUNT(o.order_id) AS order_count, SUM(o.total_amount) AS revenue
     FROM orders o
     LEFT JOIN markets m ON o.market_id = m.market_id
     WHERE o.order_status IN ('accepted','ready','completed')
     GROUP BY m.market_id, m.market_name
     ORDER BY revenue DESC");

// Order status breakdown
$ordersByStatus = selectData($pdo,
    "SELECT order_status, COUNT(*) AS total FROM orders GROUP BY order_status ORDER BY total DESC");

// Most active farmers
$activeFarmers = selectData($pdo,
    "SELECT f.stall_name, u.username,
            COUNT(o.order_id) AS order_count,
            SUM(o.total_amount) AS revenue,
            COUNT(DISTINCT o.customer_id) AS customer_count
     FROM farmers f
     LEFT JOIN orders o ON o.farmer_id = f.farmer_id
     LEFT JOIN users u ON f.user_id = u.id
     GROUP BY f.farmer_id, f.stall_name, u.username
     ORDER BY order_count DESC, revenue DESC
     LIMIT 10");

// Top products across platform
$topProducts = selectData($pdo,
    "SELECT p.name, f.stall_name, SUM(oi.quantity) AS total_qty, SUM(oi.subtotal) AS revenue
     FROM order_items oi
     JOIN orders o ON oi.order_id = o.order_id
     JOIN products p ON oi.product_id = p.product_id
     LEFT JOIN farmers f ON o.farmer_id = f.farmer_id
     WHERE o.order_status IN ('accepted','ready','completed')
     GROUP BY p.product_id, p.name, f.stall_name
     ORDER BY total_qty DESC
     LIMIT 5");

// Month ke hisaab se revenue aur orders (last 12 months)
$monthlyRevenue = selectData($pdo,
    "SELECT DATE_FORMAT(order_date, '%Y-%m') AS month_key,
            SUM(total_amount) AS revenue
     FROM orders
     WHERE order_status IN ('accepted','ready','completed')
       AND order_date >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 11 MONTH), '%Y-%m-01')
     GROUP BY month_key
     ORDER BY month_key ASC");

$monthlyOrders = selectData($pdo,
    "SELECT DATE_FORMAT(order_date, '%Y-%m') AS month_key,
            COUNT(order_id) AS order_count
     FROM orders
     WHERE order_date >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 11 MONTH), '%Y-%m-01')
     GROUP BY month_key
     ORDER BY month_key ASC");

$monthMap = [];
foreach ($monthlyRevenue as $mr) {
    $monthMap[$mr['month_key']]['revenue'] = (float)$mr['revenue'];
}

$orderMap = [];
foreach ($monthlyOrders as $mo) {
    $orderMap[$mo['month_key']] = (int)$mo['order_count'];
}

$monthLabels = [];
$monthData = [];
$monthOrderData = [];
for ($i = 11; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("-$i months"));
    $monthLabels[] = date('M Y', strtotime($key . '-01'));
    $monthData[] = $monthMap[$key]['revenue'] ?? 0;
    $monthOrderData[] = $orderMap[$key] ?? 0;
}

include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap">
    <div class="a-page-head">
        <div>
            <h2><i class="bi bi-bar-chart-fill"></i> Reports &amp; Analytics</h2>
            <p>Revenue, order trends and platform performance.</p>
        </div>
        <div class="a-actions">
            <a href="order-listing" class="a-btn ghost sm"><i class="bi bi-cart-check"></i> Manage Orders</a>
            <a href="index.php?page=admin-dashboard" class="a-btn primary sm"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="a-stats">
        <div class="a-stat">
            <span class="a-stat-icon violet"><i class="bi bi-cart-check-fill"></i></span>
            <div>
                <h3><?php echo (int)($totalOrders[0]['total'] ?? 0); ?></h3>
                <p>Total Orders</p>
            </div>
        </div>
        <div class="a-stat">
            <span class="a-stat-icon green"><i class="bi bi-check-circle"></i></span>
            <div>
                <h3><?php echo (int)($completedCount[0]['total'] ?? 0); ?></h3>
                <p>Completed Orders</p>
            </div>
        </div>
        <div class="a-stat">
            <span class="a-stat-icon blue"><i class="bi bi-cash-stack"></i></span>
            <div>
                <h3>Rs <?php echo number_format((float)($totalRevenue[0]['total'] ?? 0), 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
        <div class="a-stat">
            <span class="a-stat-icon amber"><i class="bi bi-graph-up-arrow"></i></span>
            <div>
                <h3>Rs <?php echo number_format((float)($avgOrderValue[0]['total'] ?? 0), 2); ?></h3>
                <p>Avg Order Value</p>
            </div>
        </div>
    </div>

    <div class="a-grid-2">
        <div class="a-card">
            <div class="a-card-head">
                <h5><i class="bi bi-graph-up"></i> Monthly Revenue (Last 12 Months)</h5>
                <span class="a-chip"><i class="bi bi-cash"></i> Rs <?php echo number_format(array_sum($monthData), 2); ?></span>
            </div>
            <div class="a-card-body">
                <div class="a-chart-box">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="a-card">
            <div class="a-card-head">
                <h5><i class="bi bi-cart-check"></i> Monthly Orders (Last 12 Months)</h5>
                <span class="a-chip"><i class="bi bi-bag-check"></i> <?php echo array_sum($monthOrderData); ?> orders</span>
            </div>
            <div class="a-card-body">
                <div class="a-chart-box">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="a-grid-2">
        <div class="a-card">
            <div class="a-card-head">
                <h5><i class="bi bi-shop"></i> Revenue Across Markets</h5>
            </div>
            <div class="a-card-body">
                <?php if (empty($revenueByMarket)): ?>
                    <div class="a-empty"><i class="bi bi-shop"></i>No sales data yet.</div>
                <?php else: ?>
                    <?php
                    $maxRev = max(array_map(function ($r) { return (float)$r['revenue']; }, $revenueByMarket));
                    foreach ($revenueByMarket as $mkt):
                        $percent = $maxRev > 0 ? round(((float)$mkt['revenue'] / $maxRev) * 100) : 0;
                    ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo sanitize_output($mkt['market_name'] ?? 'Online / Unassigned'); ?></strong>
                                <small class="a-muted">Rs <?php echo number_format((float)$mkt['revenue'], 2); ?> (<?php echo (int)$mkt['order_count']; ?> orders)</small>
                            </div>
                            <div class="a-progress"><div style="width: <?php echo $percent; ?>%;"></div></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="a-card">
            <div class="a-card-head">
                <h5><i class="bi bi-pie-chart"></i> Orders by Status</h5>
            </div>
            <div class="a-card-body">
                <?php if (empty($ordersByStatus) || array_sum(array_column($ordersByStatus, 'total')) == 0): ?>
                    <div class="a-empty"><i class="bi bi-bag"></i>No orders yet.</div>
                <?php else: ?>
                    <?php $totalAll = array_sum(array_column($ordersByStatus, 'total')); ?>
                    <?php foreach ($ordersByStatus as $st): ?>
                        <?php $percent = $totalAll > 0 ? round((int)$st['total'] / $totalAll * 100) : 0; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span><?php echo ucfirst(sanitize_output($st['order_status'])); ?></span>
                                <small class="a-muted"><?php echo (int)$st['total']; ?> (<?php echo $percent; ?>%)</small>
                            </div>
                            <div class="a-progress"><div style="width: <?php echo $percent; ?>%;"></div></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="a-card">
        <div class="a-card-head">
            <h5><i class="bi bi-people-fill"></i> Most Active Farmers</h5>
        </div>
        <div class="a-card-body flush">
            <?php $farmerRows = array_filter($activeFarmers, function ($f) { return (int)$f['order_count'] > 0; }); ?>
            <?php if (empty($farmerRows)): ?>
                <div class="a-empty"><i class="bi bi-people"></i>No active farmers yet.</div>
            <?php else: ?>
                <div class="a-table-wrap">
                    <table class="a-table">
                        <thead>
                            <tr>
                                <th>Stall</th>
                                <th>User</th>
                                <th>Customers</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($farmerRows as $farmer): ?>
                                <tr>
                                    <td><strong><?php echo sanitize_output($farmer['stall_name']); ?></strong></td>
                                    <td><?php echo sanitize_output($farmer['username'] ?? '-'); ?></td>
                                    <td><?php echo (int)$farmer['customer_count']; ?></td>
                                    <td><?php echo (int)$farmer['order_count']; ?></td>
                                    <td>Rs <?php echo number_format((float)$farmer['revenue'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="a-card">
        <div class="a-card-head">
            <h5><i class="bi bi-trophy"></i> Top Products</h5>
        </div>
        <div class="a-card-body flush">
            <?php if (empty($topProducts)): ?>
                <div class="a-empty"><i class="bi bi-basket"></i>No product sales yet.</div>
            <?php else: ?>
                <div class="a-table-wrap">
                    <table class="a-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Farmer</th>
                                <th>Qty</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $product): ?>
                                <tr>
                                    <td><strong><?php echo sanitize_output($product['name']); ?></strong></td>
                                    <td><?php echo sanitize_output($product['stall_name'] ?? '-'); ?></td>
                                    <td><?php echo (int)$product['total_qty']; ?></td>
                                    <td>Rs <?php echo number_format((float)$product['revenue'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/admin-footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const revenueChartCtx = document.getElementById('revenueChart');
const ordersChartCtx = document.getElementById('ordersChart');
const monthLabels = <?php echo json_encode($monthLabels); ?>;
const monthData = <?php echo json_encode($monthData); ?>;
const monthOrderData = <?php echo json_encode($monthOrderData); ?>;
new Chart(revenueChartCtx, {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Revenue (Rs)',
            data: monthData,
            backgroundColor: 'rgba(23, 145, 91, 0.75)',
            borderColor: 'rgba(23, 145, 91, 1)',
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function (ctx) {
                        return 'Rs ' + Number(ctx.parsed.y).toLocaleString('en-US', { minimumFractionDigits: 2 });
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function (value) {
                        return 'Rs ' + Number(value).toLocaleString();
                    }
                }
            }
        }
    }
});
new Chart(ordersChartCtx, {
    type: 'line',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Orders',
            data: monthOrderData,
            backgroundColor: 'rgba(47, 127, 214, 0.14)',
            borderColor: 'rgba(47, 127, 214, 1)',
            borderWidth: 2,
            pointBackgroundColor: '#2f7fd6',
            pointRadius: 3,
            pointHoverRadius: 5,
            tension: 0.35,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function (ctx) {
                        return ctx.parsed.y + ' orders';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    callback: function (value) {
                        return Number(value).toLocaleString();
                    }
                }
            }
        }
    }
});
</script>