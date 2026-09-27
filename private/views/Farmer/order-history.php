<?php
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];

$totalOrders  = selectData($pdo, "SELECT COUNT(*) AS total FROM orders WHERE farmer_id = ?", [$farmerId]);
$pendingCount = selectData($pdo, "SELECT COUNT(*) AS total FROM orders WHERE farmer_id = ? AND order_status = 'placed'", [$farmerId]);
$completedCount = selectData($pdo, "SELECT COUNT(*) AS total FROM orders WHERE farmer_id = ? AND order_status = 'completed'", [$farmerId]);
$revenue      = selectData($pdo, "SELECT SUM(total_amount) AS total FROM orders WHERE farmer_id = ? AND order_status IN ('accepted','ready','completed')", [$farmerId]);

$orders = selectData($pdo,
    "SELECT o.*, c.full_name, u.username, u.contact, m.market_name
     FROM orders o
     JOIN customers c ON o.customer_id = c.customer_id
     LEFT JOIN users u ON c.user_id = u.id
     LEFT JOIN markets m ON o.market_id = m.market_id
     WHERE o.farmer_id = ?
     ORDER BY o.order_date DESC, o.order_id DESC", [$farmerId]);

$bestSellers = selectData($pdo,
    "SELECT p.name, SUM(oi.quantity) AS total_qty, SUM(oi.subtotal) AS total_revenue
     FROM order_items oi
     JOIN orders o ON oi.order_id = o.order_id
     JOIN products p ON oi.product_id = p.product_id
     WHERE o.farmer_id = ? AND o.order_status IN ('accepted','ready','completed')
     GROUP BY p.name
     ORDER BY total_qty DESC
     LIMIT 5", [$farmerId]);

function historyStatusBadge($status)
{
    $map = [
        'placed'    => 'amber',
        'accepted'  => 'blue',
        'ready'     => 'blue',
        'completed' => 'green',
        'declined'  => 'red',
        'cancelled' => 'grey'
    ];
    $class = $map[$status] ?? 'grey';
    return '<span class="f-badge ' . $class . '">' . ucfirst($status) . '</span>';
}

include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-clock-history"></i> Order History</h2>
            <p>Complete record of all orders placed with your stall.</p>
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
                <h3>Rs <?php echo number_format((float)($revenue[0]['total'] ?? 0), 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
    </div>

    <div class="f-grid-2 reverse">
        <div class="f-card">
            <div class="f-card-head">
                <h5><i class="bi bi-clock-history"></i> All Orders</h5>
            </div>
            <div class="f-card-body flush">
                <?php if (empty($orders)): ?>
                    <div class="f-empty">
                        <i class="bi bi-inbox"></i>
                        No orders yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="f-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Pickup</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <?php
                                    $items = selectData($pdo,
                                        "SELECT oi.quantity, oi.subtotal, p.name
                                         FROM order_items oi
                                         LEFT JOIN products p ON oi.product_id = p.product_id
                                         WHERE oi.order_id = ?", [$order['order_id']]);
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="f-order-id">#<?php echo (int)$order['order_id']; ?></span>
                                            <span class="f-sub"><?php echo date("d M Y, h:i A", strtotime($order['order_date'])); ?></span>
                                        </td>
                                        <td>
                                            <?php echo sanitize_output($order['username'] ?? $order['full_name'] ?? 'Customer'); ?>
                                            <span class="f-sub"><?php echo sanitize_output($order['contact'] ?? ''); ?></span>
                                        </td>
                                        <td>
                                            <?php echo !empty($order['pickup_date']) ? date("d M Y", strtotime($order['pickup_date'])) : '-'; ?>
                                            <?php if (!empty($order['pickup_slot'])): ?>
                                                <span class="f-sub"><?php echo sanitize_output($order['pickup_slot']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($items)): ?>
                                                <?php foreach ($items as $item): ?>
                                                    <span class="f-sub"><?php echo sanitize_output($item['name'] ?? 'Product'); ?> &times; <?php echo (int)$item['quantity']; ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="f-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="f-revenue">Rs <?php echo number_format((float)$order['total_amount'], 2); ?></td>
                                        <td><?php echo historyStatusBadge($order['order_status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="f-col-right">
            <div class="f-card">
                <div class="f-card-head">
                    <h5><i class="bi bi-trophy"></i> Best Sellers</h5>
                </div>
                <div class="f-card-body">
                    <?php if (empty($bestSellers)): ?>
                        <p class="f-muted mb-0">No sales yet to show best sellers.</p>
                    <?php else: ?>
                        <?php
                        $maxQty = $bestSellers[0]['total_qty'] > 0 ? $bestSellers[0]['total_qty'] : 1;
                        foreach ($bestSellers as $best):
                            $percent = round(($best['total_qty'] / $maxQty) * 100);
                        ?>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="f-muted"><?php echo sanitize_output($best['name']); ?></strong>
                                    <span class="f-badge green"><?php echo (int)$best['total_qty']; ?> sold</span>
                                </div>
                                <div class="f-progress">
                                    <div style="width: <?php echo $percent; ?>%;"></div>
                                </div>
                                <small class="f-muted">Rs <?php echo number_format((float)$best['total_revenue'], 2); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="f-card">
                <div class="f-card-head">
                    <h5><i class="bi bi-graph-up"></i> Insights</h5>
                </div>
                <div class="f-card-body">
                    <div class="f-actions">
                        <a href="index.php?page=farmer-reports" class="f-btn outline block text-center">View Detailed Insights &raquo;</a>
                        <a href="index.php?page=farmer-orders" class="f-btn ghost block text-center">Back to Pre-Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>