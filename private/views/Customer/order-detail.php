<?php
$pageTitle = 'Order Details - MarketLink';
$cust = customerGuard($pdo);
$cid = (int)$cust['customer_id'];

$oid = (int)($_GET['id'] ?? 0);
$order = selectData($pdo, "SELECT o.*, f.stall_name, f.farmer_id AS ofid, f.contact_person, uf.email AS farmer_email, uf.contact AS farmer_contact,
    m.market_name, m.address AS market_address, m.operating_days, m.opening_time, m.closing_time
    FROM orders AS o
    INNER JOIN farmers AS f ON f.farmer_id = o.farmer_id
    INNER JOIN users AS uf ON uf.id = f.user_id
    LEFT JOIN markets AS m ON m.market_id = o.market_id
    WHERE o.order_id = ? AND o.customer_id = ?", [$oid, $cid]);
if (empty($order)) {
    set_flash('error', 'Order not found.');
    redirect('orders');
}
$o = $order[0];
include __DIR__ . '/../../../public/components/header.php';
$editable = canEditOrder($o);
$cut = cutoffInfo($o);

$items = selectData($pdo, "SELECT oi.*, p.name, p.unit, p.farmer_id FROM order_items AS oi
    INNER JOIN products AS p ON p.product_id = oi.product_id WHERE oi.order_id = ?", [$oid]);

$timeline = [];
$steps = ['placed' => 'Pre-Ordered', 'accepted' => 'Accepted', 'ready' => 'Ready', 'completed' => 'Completed'];
$stepOrder = array_keys($steps);
$currentIndex = array_search($o['order_status'], $stepOrder, true);

// Cancelled / declined order: sirf pehla step (placed) done dikhao, baqi dim.
$isFailedOrder = in_array($o['order_status'], ['cancelled', 'declined'], true);
if ($isFailedOrder) {
    $currentIndex = false;
}

foreach ($stepOrder as $i => $step) {
    $isCurrent = !$isFailedOrder && ($i === $currentIndex);
    $isDone    = $isCurrent || ($isFailedOrder && $i === 0) || ($i < $currentIndex);
    $timeline[] = ['label' => $steps[$step], 'done' => $isDone, 'current' => $isCurrent];
}

$reviewedIds = getReviewedIds($pdo, $cid, $oid);
$canReview = $o['order_status'] === 'completed';
$marketForEdit = selectData($pdo, "SELECT m.* FROM market_farmer AS mf INNER JOIN markets AS m ON m.market_id = mf.market_id WHERE mf.farmer_id = ? AND m.is_active = 1", [$o['farmer_id']]);
?>

<div class="c-page">
    <div class="c-wrap">
        <a href="<?php echo ML_asset('orders'); ?>" class="c-btn ghost sm mb-3"><i class="bi bi-arrow-left"></i> My Orders</a>

        <div class="c-card">
            <div class="c-card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="fw-bold mb-1" style="font-size:1.35rem;"><?php echo orderRef($o['order_id']); ?></h2>
                        <span class="c-muted">Placed <?php echo date('M j, Y · g:i A', strtotime($o['order_date'])); ?></span>
                    </div>
                    <div class="text-end">
                        <?php echo orderStatusBadge($o['order_status']); ?>
                        <div class="c-order-total mt-1"><?php echo money($o['total_amount']); ?></div>
                        <div class="c-muted">Pay at pickup</div>
                    </div>
                </div>

                <div class="ml-timeline mt-4">
                    <?php foreach ($timeline as $tl): ?>
                        <div class="ml-tl-item <?php echo $tl['done'] ? ($tl['current'] ? 'current' : 'done') : ''; ?>">
                            <span class="fw-semibold <?php echo $tl['done'] ? '' : 'c-muted'; ?>"><?php echo $tl['label']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="c-card">
                    <div class="c-card-head"><h5><i class="bi bi-basket2"></i> Items</h5></div>
                    <div class="c-table-wrap">
                        <table class="c-table">
                            <thead><tr><th>Product</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                                <?php foreach ($items as $it): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <a href="<?php echo ML_asset('product') . '?id=' . $it['product_id']; ?>" class="fw-semibold text-decoration-none" style="color:var(--c-ink);"><?php echo sanitize_output($it['name']); ?></a>
                                                <span class="c-muted">/ <?php echo sanitize_output($it['unit']); ?></span>
                                                <?php if ($canReview): ?>
                                                    <?php if (in_array((int)$it['product_id'], $reviewedIds, true)): ?>
                                                        <span class="c-badge green ms-1"><i class="bi bi-check-lg"></i> Reviewed</span>
                                                    <?php else: ?>
                                                        <form class="d-inline" data-review-form data-order="<?php echo $oid; ?>" data-product="<?php echo $it['product_id']; ?>" data-farmer="<?php echo $o['ofid']; ?>">
                                                            <button type="button" class="c-btn outline sm btn-open-review ms-1">Rate this</button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center"><?php echo (int)$it['quantity']; ?></td>
                                        <td class="text-end"><?php echo money($it['unit_price']); ?></td>
                                        <td class="text-end fw-semibold"><?php echo money($it['subtotal']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot><tr><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold" style="color:var(--c-green-dark);"><?php echo money($o['total_amount']); ?></td></tr></tfoot>
                        </table>
                    </div>
                </div>

                <?php if (!empty($o['special_instructions'])): ?>
                    <div class="c-card">
                        <div class="c-card-body">
                            <h6 class="fw-bold mb-2 d-flex align-items-center gap-2"><i class="bi bi-sticky" style="color:var(--c-green);"></i> Special instructions</h6>
                            <p class="c-muted mb-0"><?php echo sanitize_output($o['special_instructions']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <div class="c-card sticky-lg-top" style="top:96px;">
                    <div class="c-card-head"><h5><i class="bi bi-geo-alt"></i> Pickup Details</h5></div>
                    <div class="c-card-body">
                        <span class="c-muted">Market</span>
                        <div class="fw-semibold mb-3"><?php echo sanitize_output($o['market_name']); ?><br><span class="c-muted fw-normal small"><?php echo sanitize_output($o['market_address']); ?></span></div>
                        <span class="c-muted">Date &amp; Slot</span>
                        <div class="fw-semibold mb-3"><?php echo date('D, M j, Y', strtotime($o['pickup_date'])); ?> · <?php echo sanitize_output($o['pickup_slot']); ?></div>
                        <span class="c-muted">Seller</span>
                        <div class="fw-semibold mb-2"><?php echo sanitize_output($o['stall_name']); ?> <span class="c-muted fw-normal small">(<?php echo sanitize_output($o['contact_person']); ?>)</span></div>
                        <div class="mb-3 small"><i class="bi bi-telephone me-1" style="color:var(--c-green);"></i><?php echo sanitize_output($o['farmer_contact']); ?></div>
                        <div class="c-tile py-3 mb-3">
                            <div class="d-flex justify-content-between small"><span class="c-muted">Modify window</span>
                                <span class="fw-semibold" style="color:var(--c-green-dark);"><?php echo $cut['label']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mt-1"><span class="c-muted">Payment</span><span class="fw-semibold">In person at pickup</span></div>
                            <?php if ($editable): ?>
                                <div class="d-flex justify-content-between small mt-1"><span class="c-muted">Cancellation</span><span class="fw-semibold" style="color:var(--c-green-dark);">Free before cutoff</span></div>
                            <?php endif; ?>
                        </div>

                        <?php if ($editable): ?>
                            <div id="modify">
                                <h6 class="fw-bold mb-2">Modify or Cancel</h6>
                                <form class="mb-3" data-modify-form>
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="modify">
                                    <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                    <div class="c-field">
                                        <label>Pickup date</label>
                                        <select name="pickup_date" class="form-select" required>
                                            <?php foreach (pickupDates(['operating_days' => $o['operating_days']]) as $d): ?>
                                                <option value="<?php echo $d['date']; ?>" <?php echo $d['date'] === $o['pickup_date'] ? 'selected' : ''; ?>><?php echo $d['label']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="c-field">
                                        <label>Time slot</label>
                                        <select name="pickup_slot" class="form-select" required>
                                            <?php foreach (pickupSlots($marketForEdit[0] ?? $o) as $slot): ?>
                                                <option value="<?php echo $slot; ?>" <?php echo $slot === $o['pickup_slot'] ? 'selected' : ''; ?>><?php echo $slot; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="c-field">
                                        <label>Instructions</label>
                                        <textarea name="instructions" class="form-control" rows="2"><?php echo sanitize_output($o['special_instructions']); ?></textarea>
                                    </div>
                                    <button type="submit" class="c-btn primary block"><i class="bi bi-check2"></i> Save Changes</button>
                                </form>
                                <button type="button" class="c-btn danger-soft block" data-cancel-order="<?php echo $oid; ?>" data-confirm="1"><i class="bi bi-x-circle"></i> Cancel Pre-Order</button>
                            </div>
                        <?php endif; ?>

                        <?php if (!$editable && in_array($o['order_status'], ['placed', 'accepted'], true)): ?>
                            <div class="c-muted"><i class="bi bi-info-circle me-1"></i>The 24-hour cutoff has passed; this order can no longer be modified.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:20px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Rate your purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="c-muted" id="reviewProductName"></p>
                    <div class="ml-review-input d-flex justify-content-center gap-1 mb-3" id="ratingPicker">
                        <input type="radio" name="rating" value="5" id="r5"><label for="r5"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" name="rating" value="4" id="r4"><label for="r4"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" name="rating" value="3" id="r3"><label for="r3"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" name="rating" value="2" id="r2"><label for="r2"><i class="bi bi-star-fill"></i></label>
                        <input type="radio" name="rating" value="1" id="r1" checked><label for="r1"><i class="bi bi-star-fill"></i></label>
                    </div>
                    <textarea id="reviewComment" class="form-control" rows="3" placeholder="How was the produce and pickup?"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="c-btn primary" id="submitReview">Submit Review</button>
                </div>
            </div>
    </div>
</div>

<script>
(function () {
    var current = null;
    var modal = document.getElementById('reviewModal');
    document.querySelectorAll('.btn-open-review').forEach(function (b) {
        b.addEventListener('click', function () {
            var form = b.closest('[data-review-form]');
            current = form;
            document.getElementById('reviewProductName').textContent = b.closest('td').querySelector('a').textContent.trim();
            var m = new bootstrap.Modal(modal);
            m.show();
        });
    });
    document.getElementById('submitReview').addEventListener('click', function () {
        if (!current) return;
        var form = current;
        var rating = document.querySelector('#ratingPicker input[name="rating"]:checked').value;
        var comment = document.getElementById('reviewComment').value.trim();
        var body = new URLSearchParams({
            action: 'add',
            order_id: form.getAttribute('data-order'),
            product_id: form.getAttribute('data-product'),
            farmer_id: form.getAttribute('data-farmer'),
            rating: rating,
            comment: comment,
            csrf_token: window.ML.csrf
        });
        fetch('../private/backend-scripting/customer-review.php', { method: 'POST', body: body })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.ok) {
                    window.toast('Review submitted. Thank you!');
                    var m = bootstrap.Modal.getInstance(modal);
                    if (m) m.hide();
                    current.outerHTML = '<span class="c-badge green ms-1"><i class="bi bi-check-lg"></i> Reviewed</span>';
                } else {
                    window.toast(res.error || 'Could not submit review', false);
                }
            }).catch(function () { window.toast('Network error', false); });
    });

    var cancelBtn = document.querySelector('[data-cancel-order]');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            if (!confirm('Are you sure you want to cancel this pre-order?')) return;
            var oid = cancelBtn.getAttribute('data-cancel-order');
            var body = new URLSearchParams({ action: 'cancel', order_id: oid, csrf_token: window.ML.csrf });
            fetch('../private/backend-scripting/customer-order.php', { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.ok) { window.toast('Pre-order cancelled'); setTimeout(function () { window.location.reload(); }, 900); }
                    else window.toast(res.error || 'Could not cancel', false);
                }).catch(function () { window.toast('Network error', false); });
        });
    }

    document.querySelectorAll('[data-modify-form]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var body = new URLSearchParams(new FormData(form));
            body.set('csrf_token', window.ML.csrf);
            fetch('../private/backend-scripting/customer-order.php', { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.ok) { window.toast('Order updated'); setTimeout(function () { window.location.reload(); }, 900); }
                    else window.toast(res.error || 'Could not update', false);
                }).catch(function () { window.toast('Network error', false); });
        });
    });
})();
</script>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>