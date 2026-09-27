<?php
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];

$settings = $farmer;

$slots = selectData($pdo, "SELECT * FROM pickup_slots WHERE farmer_id = ? ORDER BY day_of_week, slot_start", [$farmerId]);

$orders = selectData($pdo,
    "SELECT o.*, c.full_name, u.username, u.contact, m.market_name
     FROM orders o
     JOIN customers c ON o.customer_id = c.customer_id
     LEFT JOIN users u ON c.user_id = u.id
     LEFT JOIN markets m ON o.market_id = m.market_id
     WHERE o.farmer_id = ?
     ORDER BY o.order_date DESC, o.order_id DESC", [$farmerId]);

$pendingOrders = [];
$acceptedOrders = [];
foreach ($orders as $order) {
    if ($order['order_status'] === 'placed') {
        $pendingOrders[] = $order;
    } elseif ($order['order_status'] === 'accepted' || $order['order_status'] === 'ready') {
        $acceptedOrders[] = $order;
    }
}

$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-cart-check"></i> Pre-Orders</h2>
            <p>Review incoming orders and manage your pickup slots.</p>
        </div>
        <div class="f-actions">
            <span class="f-chip"><i class="bi bi-hourglass-split"></i> <?php echo count($pendingOrders); ?> pending</span>
            <span class="f-chip"><i class="bi bi-box-seam"></i> <?php echo count($acceptedOrders); ?> active</span>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="f-grid-2">
        <div class="f-card">
            <div class="f-card-head">
                <h5><i class="bi bi-gear"></i> Order Settings</h5>
            </div>
            <div class="f-card-body">
                <form action="../private/backend-scripting/save-order-settings.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="f-field">
                        <label for="f-cutoff">Order Cut-off Time</label>
                        <input type="time" id="f-cutoff" name="cutoff_time" class="form-control" value="<?php echo sanitize_output($settings['cutoff_time'] ?? ''); ?>">
                        <small class="f-hint">Orders placed after this time move to the next day.</small>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="f-field">
                                <label for="f-start">Pickup Start</label>
                                <input type="time" id="f-start" name="pickup_start" class="form-control" value="<?php echo sanitize_output($settings['pickup_start'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="f-field">
                                <label for="f-end">Pickup End</label>
                                <input type="time" id="f-end" name="pickup_end" class="form-control" value="<?php echo sanitize_output($settings['pickup_end'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="save_cutoff" class="f-btn primary block">
                        <i class="bi bi-check2"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>

        <div class="f-card">
            <div class="f-card-head">
                <h5><i class="bi bi-calendar-plus"></i> Pickup Slots</h5>
            </div>
            <div class="f-card-body">
                <form action="../private/backend-scripting/save-order-settings.php" method="POST" class="mb-3">
                    <?php echo csrf_field(); ?>
                    <div class="row g-2">
                        <div class="col-12 mb-1">
                            <select name="slot_day" class="form-select" required>
                                <option value="">-- Day --</option>
                                <?php foreach ($daysOfWeek as $day): ?>
                                    <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-5">
                            <input type="time" name="slot_start" class="form-control" required>
                        </div>
                        <div class="col-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-arrow-right f-muted"></i>
                        </div>
                        <div class="col-5">
                            <input type="time" name="slot_end" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="add_slot" class="f-btn outline block mt-3">
                        <i class="bi bi-plus-circle"></i> Add Slot
                    </button>
                </form>

                <?php if (empty($slots)): ?>
                    <p class="f-muted mb-0">No pickup slots yet. Add your first slot above.</p>
                <?php else: ?>
                    <?php foreach ($slots as $slot): ?>
                        <div class="f-slot-row">
                            <div class="f-slot-info">
                                <strong><?php echo sanitize_output($slot['day_of_week']); ?></strong>
                                <small><?php echo date("h:i A", strtotime($slot['slot_start'])) . ' - ' . date("h:i A", strtotime($slot['slot_end'])); ?></small>
                            </div>
                            <div class="f-actions">
                                <?php if ($slot['is_active']): ?>
                                    <span class="f-badge green">Active</span>
                                <?php else: ?>
                                    <span class="f-badge grey">Off</span>
                                <?php endif; ?>
                                <form action="../private/backend-scripting/save-order-settings.php" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="slot_id" value="<?php echo (int)$slot['slot_id']; ?>">
                                    <button type="submit" name="toggle_slot" class="f-btn ghost sm" title="On / Off"><i class="bi bi-power"></i></button>
                                    <button type="submit" name="delete_slot" class="f-btn danger sm" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="f-card">
        <div class="f-card-head">
            <h5><i class="bi bi-inbox"></i> Incoming Pre-Orders</h5>
            <span class="f-badge amber"><?php echo count($pendingOrders); ?> pending</span>
        </div>
        <div class="f-card-body flush">
            <?php if (empty($pendingOrders)): ?>
                <div class="f-empty">
                    <i class="bi bi-inbox"></i>
                    No pending pre-orders right now.
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingOrders as $order): ?>
                                <?php
                                $items = selectData($pdo,
                                    "SELECT oi.quantity, oi.unit_price, oi.subtotal, p.name
                                     FROM order_items oi
                                     LEFT JOIN products p ON oi.product_id = p.product_id
                                     WHERE oi.order_id = ?", [$order['order_id']]);
                                ?>
                                <tr>
                                    <td>
                                        <span class="f-order-id">#<?php echo (int)$order['order_id']; ?></span>
                                        <?php echo orderStatusBadge($order['order_status']); ?>
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
                                    <td>
                                        <div class="f-actions">
                                            <form action="../private/backend-scripting/update-order-status.php" method="POST" class="d-flex gap-2 w-100">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="order_id" value="<?php echo (int)$order['order_id']; ?>">
                                                <button type="submit" name="accept_order" class="f-btn success-soft sm flex-fill">
                                                    <i class="bi bi-check2"></i> Accept
                                                </button>
                                                <button type="submit" name="decline_order" class="f-btn danger sm" onclick="return confirm('Decline this order?');">
                                                    <i class="bi bi-x-lg"></i> Decline
                                                </button>
                                            </form>
                                        </div>
                                        <?php if (!empty($order['special_instructions'])): ?>
                                            <small class="f-muted d-block mt-2" title="Special Instructions">
                                                <i class="bi bi-info-circle"></i> <?php echo sanitize_output($order['special_instructions']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="f-card">
        <div class="f-card-head">
            <h5><i class="bi bi-box-seam"></i> Accepted / Ready Orders</h5>
            <span class="f-badge blue"><?php echo count($acceptedOrders); ?> active</span>
        </div>
        <div class="f-card-body flush">
            <?php if (empty($acceptedOrders)): ?>
                <div class="f-empty">
                    <i class="bi bi-box-seam"></i>
                    No accepted orders yet.
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($acceptedOrders as $order): ?>
                                <?php
                                $items = selectData($pdo,
                                    "SELECT oi.quantity, oi.unit_price, oi.subtotal, p.name
                                     FROM order_items oi
                                     LEFT JOIN products p ON oi.product_id = p.product_id
                                     WHERE oi.order_id = ?", [$order['order_id']]);
                                ?>
                                <tr>
                                    <td>
                                        <span class="f-order-id">#<?php echo (int)$order['order_id']; ?></span>
                                        <?php echo orderStatusBadge($order['order_status']); ?>
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
                                    <td>
                                        <form action="../private/backend-scripting/update-order-status.php" method="POST" class="d-flex gap-2">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="order_id" value="<?php echo (int)$order['order_id']; ?>">
                                            <?php if ($order['order_status'] === 'accepted'): ?>
                                                <button type="submit" name="mark_ready" class="f-btn outline sm">
                                                    <i class="bi bi-hand-index-thumb"></i> Mark Ready
                                                </button>
                                            <?php endif; ?>
                                            <button type="submit" name="mark_completed" class="f-btn success-soft sm">
                                                <i class="bi bi-check2-circle"></i> Complete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <p class="f-muted mt-1">
        <a href="index.php?page=farmer-history" style="color:var(--f-green);font-weight:600;text-decoration:none;">View full order history &raquo;</a>
    </p>
</div>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>