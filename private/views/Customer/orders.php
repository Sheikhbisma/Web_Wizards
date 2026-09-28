<?php
$pageTitle = 'My Orders - MarketLink';
$cust = customerGuard($pdo);
include __DIR__ . '/../../../public/components/header.php';
$cid = (int)$cust['customer_id'];

$filter = $_GET['filter'] ?? 'all';
$allowed = ['all', 'placed', 'accepted', 'ready', 'completed', 'cancelled', 'declined'];
if (!in_array($filter, $allowed, true)) $filter = 'all';

$where = "o.customer_id = ?";
$params = [$cid];
if ($filter !== 'all') { $where .= " AND o.order_status = ?"; $params[] = $filter; }

$orders = selectData($pdo, "SELECT o.*, f.stall_name, f.farmer_id, m.market_name, m.address AS market_address
    FROM orders AS o
    INNER JOIN farmers AS f ON f.farmer_id = o.farmer_id
    LEFT JOIN markets AS m ON m.market_id = o.market_id
    WHERE " . $where . " ORDER BY o.order_date DESC", $params);

/* Un-reviewed item counts, one query for the whole list. The Rate button is
   only rendered on the order detail page, so without a marker here a
   completed order looks identical to a fully reviewed one and the customer
   has no way to know a review is still owed. */
$pendingByOrder = [];
if (!empty($orders)) {
    $orderIds = array_map('intval', array_column($orders, 'order_id'));
    $pendingRows = selectData($pdo, "SELECT oi.order_id, COUNT(*) AS pending
        FROM order_items AS oi
        INNER JOIN orders AS o ON o.order_id = oi.order_id
        LEFT JOIN reviews AS r ON r.order_id = oi.order_id AND r.product_id = oi.product_id
        WHERE oi.order_id IN (" . implode(',', $orderIds) . ")
          AND o.order_status = 'completed'
          AND r.review_id IS NULL
        GROUP BY oi.order_id", []);
    foreach ($pendingRows as $pr) $pendingByOrder[(int)$pr['order_id']] = (int)$pr['pending'];
}
?>

<div class="c-page">
    <div class="c-wrap">
        <div class="c-page-head">
            <div>
                <div class="section-kicker">My Account</div>
                <h2 class="mt-1 mb-1">My Orders</h2>
                <p>Track, modify and complete your pre-orders.</p>
            </div>
            <a href="<?php echo ML_asset('products'); ?>" class="c-btn primary"><i class="bi bi-basket"></i> New Pre-Order</a>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <?php
            $tabs = ['all' => 'All', 'placed' => 'Pending', 'accepted' => 'Accepted', 'ready' => 'Ready', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'declined' => 'Declined'];
            foreach ($tabs as $k => $lbl): ?>
                <a href="?page=orders&filter=<?php echo $k; ?>" class="ml-chip <?php echo $filter === $k ? 'active' : ''; ?>"><?php echo $lbl; ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($orders)): ?>
            <div class="c-card">
                <div class="c-empty">
                    <i class="bi bi-inbox"></i>
                    <h6 class="fw-bold">No orders here</h6>
                    <p class="mb-3">When you pre-order fresh produce, it will show up here.</p>
                    <a href="<?php echo ML_asset('markets'); ?>" class="c-btn primary sm">Browse Markets</a>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($orders as $o):
                    $editable = canEditOrder($o);
                    $cut = cutoffInfo($o);
                ?>
                    <div class="c-card">
                        <div class="c-card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="ml-farmer-avatar sm"><i class="bi bi-person-fill"></i></span>
                                    <div>
                                        <div class="fw-bold"><?php echo orderRef($o['order_id']); ?> · <?php echo sanitize_output($o['stall_name']); ?></div>
                                        <span class="c-sub">Placed <?php echo timeAgo($o['order_date']); ?></span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <?php echo orderStatusBadge($o['order_status']); ?>
                                    <div class="c-order-total mt-1"><?php echo money($o['total_amount']); ?></div>
                                </div>
                            </div>
                            <div class="c-grid-3 mb-3">
                                <div>
                                    <span class="c-muted">Market</span>
                                    <div class="fw-semibold"><?php echo sanitize_output($o['market_name']); ?></div>
                                </div>
                                <div>
                                    <span class="c-muted">Pickup</span>
                                    <div class="fw-semibold"><?php echo date('D, M j, Y', strtotime($o['pickup_date'])); ?> · <?php echo sanitize_output($o['pickup_slot']); ?></div>
                                </div>
                                <div>
                                    <span class="c-muted">Modify window</span>
                                    <?php if ($editable): ?>
                                        <div class="fw-semibold" style="color:var(--c-green-dark);"><i class="bi bi-clock-history me-1"></i><?php echo $cut['label']; ?></div>
                                    <?php else: ?>
                                        <div class="c-muted"><?php echo $cut['label']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="c-actions">
                                <?php $pendingReview = $pendingByOrder[(int)$o['order_id']] ?? 0; ?>
                                <?php if ($o['order_status'] === 'completed' && $pendingReview > 0): ?>
                                    <a href="<?php echo ML_asset('order') . '?id=' . $o['order_id']; ?>#review" class="c-btn sm review-pending">
                                        <i class="bi bi-star-fill me-1"></i> Rate <?= $pendingReview ?> item<?= $pendingReview > 1 ? 's' : '' ?>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo ML_asset('order') . '?id=' . $o['order_id']; ?>" class="c-btn outline sm">View Details <i class="bi bi-arrow-right"></i></a>
                                <?php if ($editable): ?>
                                    <a href="<?php echo ML_asset('order') . '?id=' . $o['order_id'] . '#modify'; ?>" class="c-btn ghost sm"><i class="bi bi-pencil"></i> Modify / Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>
