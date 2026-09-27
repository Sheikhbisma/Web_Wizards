<?php
validateAdmin();

if (isset($_POST['delete_order'])) {
    verify_csrf();
    $order_id = (int)($_POST['order_id'] ?? 0);

    if (!empty($order_id)) {
        $stmt = $pdo->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);

        if ($stmt->rowCount() > 0) {
            set_flash("success", "Order deleted successfully.");
        } else {
            set_flash("error", "Order not found.");
        }
    } else {
        set_flash("error", "Invalid order ID.");
    }
    redirect("order-listing");
}

$sql = "SELECT o.order_id, o.total_amount, o.order_status, o.order_date, o.pickup_date, o.pickup_slot,
               c.full_name, f.stall_name, m.market_name,
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) AS item_count
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.customer_id
        LEFT JOIN farmers f ON o.farmer_id = f.farmer_id
        LEFT JOIN markets m ON o.market_id = m.market_id
        ORDER BY o.order_id DESC";
$orders = selectData($pdo, $sql);

include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap">
    <div class="a-page-head">
        <div>
            <h2><i class="bi bi-cart-check"></i> Manage Orders</h2>
            <p>Track every pre-order, update its status and export invoices.</p>
        </div>
        <div class="a-actions">
            <span class="a-chip"><i class="bi bi-hourglass-split"></i> <?php echo count(array_filter($orders, function ($o) { return $o['order_status'] === 'placed'; })); ?> pending</span>
            <span class="a-chip"><i class="bi bi-check2-circle"></i> <?php echo count(array_filter($orders, function ($o) { return $o['order_status'] === 'completed'; })); ?> completed</span>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="a-card">
        <div class="a-card-head">
            <h5><i class="bi bi-list-ul"></i> All Orders</h5>
            <a href="index.php?page=admin-reports" class="a-btn ghost sm"><i class="bi bi-bar-chart"></i> Reports</a>
        </div>
        <div class="a-card-body flush">
            <?php if (empty($orders)): ?>
                <div class="a-empty"><i class="bi bi-bag"></i>No orders found.</div>
            <?php else: ?>
                <div class="a-table-wrap">
                    <table class="a-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Farmer</th>
                                <th>Market</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $row): ?>
                                <tr>
                                    <td><strong>#<?php echo (int)$row['order_id']; ?></strong></td>
                                    <td>
                                        <?php echo sanitize_output($row['full_name'] ?? 'Customer'); ?>
                                        <?php if (!empty($row['pickup_slot'])): ?>
                                            <span class="a-sub"><?php echo sanitize_output($row['pickup_slot']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo sanitize_output($row['stall_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo sanitize_output($row['market_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo (int)$row['item_count']; ?></td>
                                    <td>Rs <?php echo number_format((float)$row['total_amount'], 2); ?></td>
                                    <td><?php echo adminStatusBadge($row['order_status']); ?></td>
                                    <td><?php echo date("d M Y, h:i A", strtotime($row['order_date'])); ?></td>
                                    <td>
                                        <div class="a-actions">
                                            <?php if (in_array($row['order_status'], ['placed', 'accepted', 'ready'])): ?>
                                                <form action="../private/backend-scripting/admin-update-order-status.php" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="order_id" value="<?php echo (int)$row['order_id']; ?>">
                                                    <?php if ($row['order_status'] === 'placed'): ?>
                                                        <button type="submit" name="accept_order" class="a-btn success-soft sm">Accept</button>
                                                        <button type="submit" name="decline_order" class="a-btn danger-soft sm" onclick="return confirm('Decline this order?');">Decline</button>
                                                    <?php elseif ($row['order_status'] === 'accepted'): ?>
                                                        <button type="submit" name="mark_ready" class="a-btn info sm">Ready</button>
                                                        <button type="submit" name="mark_completed" class="a-btn primary sm">Complete</button>
                                                    <?php elseif ($row['order_status'] === 'ready'): ?>
                                                        <button type="submit" name="mark_completed" class="a-btn primary sm">Complete</button>
                                                    <?php endif; ?>
                                                </form>
                                            <?php elseif ($row['order_status'] === 'completed'): ?>
                                                <a href="../private/backend-scripting/export-order-invoice.php?order_id=<?php echo (int)$row['order_id']; ?>" class="a-btn success-soft sm">
                                                    <i class="bi bi-file-earmark-excel"></i> Invoice
                                                </a>
                                            <?php endif; ?>
                                            <form action="" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="order_id" value="<?php echo (int)$row['order_id']; ?>">
                                                <button type="submit" name="delete_order" class="a-btn danger-soft sm" onclick="return confirm('Delete this order?');">Delete</button>
                                            </form>
                                        </div>
                                    </td>
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