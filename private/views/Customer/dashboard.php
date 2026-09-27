<?php
$pageTitle = 'Customer Dashboard - MarketLink';
$cust = customerGuard($pdo);
include __DIR__ . '/../../../public/components/header.php';
$cid = (int)$cust['customer_id'];
$uid = (int)$cust['user_id'];

$stats = selectData($pdo, "SELECT
    (SELECT COUNT(*) FROM orders WHERE customer_id = ? AND order_status IN ('placed','accepted','ready')) AS active_orders,
    (SELECT COUNT(*) FROM orders WHERE customer_id = ? AND order_status = 'completed') AS completed_orders,
    (SELECT COUNT(*) FROM favorites WHERE customer_id = ?) AS favorites,
    (SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0) AS unread",
    [$cid, $cid, $cid, $uid])[0];

$activeOrders = selectData($pdo, "SELECT o.*, f.stall_name, m.market_name, m.address AS market_address
    FROM orders AS o
    INNER JOIN farmers AS f ON f.farmer_id = o.farmer_id
    LEFT JOIN markets AS m ON m.market_id = o.market_id
    WHERE o.customer_id = ? AND o.order_status IN ('placed','accepted','ready')
    ORDER BY o.pickup_date ASC", [$cid]);

$freshPicks = selectData($pdo, "SELECT p.*, f.stall_name, c.category_name
    FROM products AS p
    INNER JOIN farmers AS f ON f.farmer_id = p.farmer_id
    LEFT JOIN categories AS c ON c.category_id = p.category_id
    WHERE p.is_available = 1 AND p.is_sold_out = 0 AND p.stock_quantity > 0
    ORDER BY p.created_at DESC LIMIT 8");

/* Announcements are no longer queried here. They are shown on the customer's
   notification page instead, so the bell badge is the single place a customer
   has to look for admin-pushed news. */
$near = selectData($pdo, "SELECT * FROM markets WHERE is_active = 1 LIMIT 4");

$todayStr = date('Y-m-d');
$upcoming = null;
$todayPickups = 0;
foreach ($activeOrders as $o) {
    if ($o['pickup_date'] < $todayStr) continue;
    if ($o['pickup_date'] === $todayStr) $todayPickups++;
    if ($upcoming === null || ($o['pickup_date'] . ' ' . $o['pickup_slot']) < ($upcoming['pickup_date'] . ' ' . $upcoming['pickup_slot'])) {
        $upcoming = $o;
    }
}
$pickupAt = $upcoming ? strtotime($upcoming['pickup_date'] . ' ' . slotEndTime($upcoming['pickup_slot'])) : 0;
$pickupLabel = '';
if ($upcoming && $pickupAt) {
    $diffMin = (int)round(($pickupAt - time()) / 60);
    if ($diffMin <= 0) {
        $pickupLabel = 'Pickup time has arrived — head to the stall.';
    } elseif ($diffMin < 120) {
        $pickupLabel = 'In about ' . max(1, ceil($diffMin / 60)) . ' hour(s).';
    } elseif ($diffMin < 2880) {
        $pickupLabel = 'In about ' . ceil($diffMin / 60) . ' hour(s).';
    } else {
        $pickupLabel = 'In ' . ceil($diffMin / 1440) . ' day(s).';
    }
}
?>

<div class="c-page">
    <div class="c-wrap">
        <div class="c-page-head">
            <div>
                <div class="section-kicker">Customer Area</div>
                <h2 class="mt-1 mb-1">Salam, <?php echo sanitize_output($cust['full_name'] ?: $cust['username']); ?> <i class="bi bi-hand-thumbs-up-fill"></i></h2>
                <p>Pre-order farm fresh produce and pick it up at your favourite market.</p>
            </div>
            <a href="<?php echo ML_asset('products'); ?>" class="c-btn primary"><i class="bi bi-basket"></i> Start Pre-Ordering</a>
        </div>

        <style>
            .c-pickup-banner {
                display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
                background: linear-gradient(135deg, #ffffff 0%, #f3f8ed 100%);
                border: 1.5px solid #d3e5c4; border-left: 5px solid var(--c-green, #2e7d32);
                border-radius: 18px; padding: 16px 20px; margin-bottom: 20px;
                box-shadow: 0 6px 18px rgba(45, 65, 28, 0.08);
            }
            .c-pickup-banner-ico {
                width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
                display: flex; align-items: center; justify-content: center;
                background: var(--c-green, #2e7d32); color: #fff; font-size: 1.4rem;
            }
            .c-pickup-kicker {
                font-size: .74rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;
                color: var(--c-green, #2e7d32); margin-bottom: 2px;
            }
            .c-pickup-banner-body { flex: 1 1 300px; min-width: 260px; }
        </style>

        <?php if ($upcoming || $todayPickups > 0): ?>
            <div class="c-pickup-banner">
                <div class="c-pickup-banner-ico"><i class="bi bi-box-seam-fill"></i></div>
                <div class="c-pickup-banner-body">
                    <div class="c-pickup-kicker">
                        <?php echo $upcoming && $upcoming['pickup_date'] === $todayStr ? 'Pickup Today!' : ($todayPickups > 0 ? ($todayPickups > 1 ? $todayPickups . ' pickups today' : '1 pickup today') : 'Next Pickup'); ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?php echo orderRef($upcoming['order_id']); ?> &middot; <?php echo sanitize_output($upcoming['stall_name']); ?></h5>
                    <p class="mb-2">
                        <i class="bi bi-calendar3 me-1"></i><?php echo date('l, M j, Y', strtotime($upcoming['pickup_date'])); ?>
                        &middot; <i class="bi bi-clock me-1"></i><?php echo sanitize_output($upcoming['pickup_slot']); ?>
                        &middot; <i class="bi bi-geo-alt me-1"></i><?php echo sanitize_output($upcoming['market_name']); ?>
                    </p>
                    <p class="mb-0 c-muted"><i class="bi bi-wallet2 me-1"></i>Pay in person at the stall. Total: <b><?php echo money($upcoming['total_amount']); ?></b> <?php if ($pickupLabel): ?>&middot; <i class="bi bi-hourglass-split me-1"></i><?php echo $pickupLabel; ?><?php endif; ?></p>
                </div>
                <a href="<?php echo ML_asset('order') . '?id=' . $upcoming['order_id']; ?>" class="c-btn primary sm">View Details</a>
            </div>
        <?php endif; ?>

        <div class="c-stats">
            <div class="c-stat">
                <span class="c-stat-icon green"><i class="bi bi-basket2"></i></span>
                <div>
                    <h3><?php echo (int)$stats['active_orders']; ?></h3>
                    <p>Active Pre-Orders</p>
                </div>
            </div>
            <div class="c-stat">
                <span class="c-stat-icon blue"><i class="bi bi-check2-circle"></i></span>
                <div>
                    <h3><?php echo (int)$stats['completed_orders']; ?></h3>
                    <p>Completed Orders</p>
                </div>
            </div>
            <div class="c-stat">
                <span class="c-stat-icon amber"><i class="bi bi-stars"></i></span>
                <div>
                    <h3><?php echo (int)$cust['loyalty_points']; ?></h3>
                    <p>Loyalty Points</p>
                </div>
            </div>
            <div class="c-stat">
                <span class="c-stat-icon violet"><i class="bi bi-bell"></i></span>
                <div>
                    <h3><?php echo (int)$stats['unread']; ?></h3>
                    <p>Unread Notifications</p>
                </div>
            </div>
        </div>

        <div class="c-card">
            <div class="c-card-head">
                <h5><i class="bi bi-box-seam"></i> Your Active Pre-Orders</h5>
                <a href="<?php echo ML_asset('orders'); ?>" class="c-btn ghost sm">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <?php if (empty($activeOrders)): ?>
                <div class="c-empty">
                    <i class="bi bi-inbox"></i>
                    <h6 class="fw-bold">No active pre-orders</h6>
                    <p class="mb-3">Browse fresh produce from verified farmers and pre-order for your next pickup.</p>
                    <a href="<?php echo ML_asset('markets'); ?>" class="c-btn primary sm">Explore Markets</a>
                </div>
            <?php else: ?>
                <div class="c-card-body">
                    <div class="c-grid-3">
                        <?php foreach ($activeOrders as $o): ?>
                            <div class="c-tile">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0"><?php echo orderRef($o['order_id']); ?></h6>
                                        <span class="c-sub"><?php echo sanitize_output($o['stall_name']); ?></span>
                                    </div>
                                    <?php echo orderStatusBadge($o['order_status']); ?>
                                </div>
                                <div class="c-summary-row">
                                    <span class="c-muted"><i class="bi bi-calendar3 me-1"></i><?php echo date('D, M j', strtotime($o['pickup_date'])); ?></span>
                                    <span class="fw-semibold"><?php echo sanitize_output($o['pickup_slot']); ?></span>
                                </div>
                                <div class="c-summary-row">
                                    <span class="c-muted"><i class="bi bi-geo-alt me-1"></i><?php echo sanitize_output($o['market_name']); ?></span>
                                    <span class="fw-semibold"><?php echo money($o['total_amount']); ?></span>
                                </div>
                                <a href="<?php echo ML_asset('order') . '?id=' . $o['order_id']; ?>" class="c-btn outline sm block mt-3">Track Order</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3 mt-2">
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2"><i class="bi bi-basket2-fill" style="color:var(--c-green);"></i> Fresh From the Farm</h5>
            <a href="<?php echo ML_asset('products'); ?>" class="c-btn ghost sm">See All <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-3 mb-4">
            <?php foreach ($freshPicks as $p): ?>
                <div class="col-6 col-md-3">
                    <div class="ml-card">
                        <div class="ml-card-img"><?php echo prodImg($p); ?></div>
                        <div class="ml-card-body">
                            <div class="d-flex justify-content-between align-items-start gap-1">
                                <h6 class="ml-card-title"><?php echo sanitize_output($p['name']); ?></h6>
                                <button type="button" class="btn btn-sm p-0 border-0" data-add-cart="<?php echo $p['product_id']; ?>" title="Add to cart"><i class="bi bi-plus-circle-fill fs-5" style="color:var(--c-green);"></i></button>
                            </div>
                            <div class="c-sub mb-1"><?php echo sanitize_output($p['stall_name']); ?></div>
                            <span class="price-tag"><?php echo money($p['price']); ?> <span class="unit-tag">/ <?php echo sanitize_output($p['unit']); ?></span></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($near)): ?>
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2"><i class="bi bi-shop" style="color:var(--c-green);"></i> Nearest Markets</h5>
            <div class="row g-3">
                <?php foreach ($near as $mk): ?>
                    <div class="col-md-6 col-lg-3">
                        <a href="<?php echo ML_asset('market') . '?id=' . $mk['market_id']; ?>" class="text-decoration-none text-reset">
                            <div class="c-tile h-100">
                                <i class="bi bi-shop-window"></i>
                                <h6 class="fw-bold"><?php echo sanitize_output($mk['market_name']); ?></h6>
                                <p><i class="bi bi-geo-alt me-1"></i><?php echo sanitize_output($mk['address']); ?></p>
                                <span class="c-badge green"><i class="bi bi-calendar-week"></i> <?php echo marketPickups($mk); ?></span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>
