<?php
$pageTitle = 'My Profile - MarketLink';
$cust = customerGuard($pdo);
include __DIR__ . '/../../../public/components/header.php';
$cid = (int)$cust['customer_id'];

$markets = selectData($pdo, "SELECT market_id, market_name FROM markets WHERE is_active = 1 ORDER BY market_name");

$recent = selectData($pdo, "SELECT order_id, total_amount, order_status, pickup_date, order_date FROM orders WHERE customer_id = ? ORDER BY order_date DESC LIMIT 5", [$cid]);
?>

<div class="c-page">
    <div class="c-wrap">
        <div class="c-page-head">
            <div>
                <div class="section-kicker">My Account</div>
                <h2 class="mt-1 mb-1">My Profile</h2>
                <p>Manage your details, photo and password.</p>
            </div>
        </div>

        <div class="c-panel">
            <aside class="c-side-nav">
                <div class="c-side-title">Account</div>
                <a href="<?php echo ML_asset('customer-dashboard'); ?>"><i class="bi bi-speedometer2"></i>Dashboard</a>
                <a href="<?php echo ML_asset('orders'); ?>"><i class="bi bi-box-seam"></i>Orders</a>
                <a href="<?php echo ML_asset('favorites'); ?>"><i class="bi bi-heart"></i>Favorites</a>
                <a href="<?php echo ML_asset('notifications'); ?>"><i class="bi bi-bell"></i>Notifications</a>
                <a href="<?php echo ML_asset('profile'); ?>" class="active"><i class="bi bi-person-gear"></i>My Profile</a>
                <a href="<?php echo ML_asset('cart'); ?>"><i class="bi bi-cart3"></i>My Cart</a>
                <div class="c-side-title">Session</div>
                <a href="<?php echo ML_asset('logout'); ?>" style="color:var(--c-red);"><i class="bi bi-box-arrow-right" style="color:var(--c-red);"></i>Logout</a>
            </aside>

            <div>
                <div class="c-card">
                    <div class="c-card-body d-flex align-items-center gap-3 flex-wrap">
                        <span class="ml-farmer-avatar" style="width:74px;height:74px;font-size:2rem;">
                            <?php if (!empty($cust['profile_image'])): ?>
                                <img src="<?php echo ML_asset('Uploads/img/' . $cust['profile_image']); ?>" alt="" class="w-100 h-100" style="object-fit:cover;border-radius:20px;">
                            <?php else: ?>
                                <i class="bi bi-person-fill"></i>
                            <?php endif; ?>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-1"><?php echo sanitize_output($cust['full_name'] ?: $cust['username']); ?></h5>
                            <div class="c-muted"><i class="bi bi-envelope me-1"></i><?php echo sanitize_output($cust['email']); ?> · <i class="bi bi-phone me-1"></i><?php echo sanitize_output($cust['contact']); ?></div>
                            <div class="mt-2"><span class="c-badge amber"><i class="bi bi-star-fill"></i> <?php echo (int)$cust['loyalty_points']; ?> points</span></div>
                        </div>
                    </div>
                </div>

                <div class="c-card">
                    <div class="c-card-head"><h5><i class="bi bi-person-lines-fill"></i> Personal Information</h5></div>
                    <div class="c-card-body">
                        <form data-profile-form>
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="info">
                            <div class="row g-3">
                                <div class="col-md-6 c-field mb-0">
                                    <label for="pfFullName">Full name</label>
                                    <input type="text" id="pfFullName" name="full_name" class="form-control" value="<?php echo sanitize_output($cust['full_name']); ?>" required>
                                </div>
                                <div class="col-md-6 c-field mb-0">
                                    <label for="pfContact">Phone</label>
                                    <input type="tel" id="pfContact" name="contact" class="form-control" value="<?php echo sanitize_output($cust['contact']); ?>" required>
                                </div>
                                <div class="col-md-6 c-field mb-0">
                                    <label for="pfEmail">Email</label>
                                    <input type="email" id="pfEmail" class="form-control" value="<?php echo sanitize_output($cust['email']); ?>" disabled>
                                </div>
                                <div class="col-md-6 c-field mb-0">
                                    <label for="pfMarket">Preferred market</label>
                                    <select id="pfMarket" name="preferred_market_id" class="form-select">
                                        <option value="">Not selected</option>
                                        <?php foreach ($markets as $mk): ?>
                                            <option value="<?php echo $mk['market_id']; ?>" <?php echo (int)$cust['preferred_market_id'] === (int)$mk['market_id'] ? 'selected' : ''; ?>><?php echo sanitize_output($mk['market_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 c-field mb-0">
                                    <label for="pfCity">City</label>
                                    <input type="text" id="pfCity" name="city" class="form-control" value="<?php echo sanitize_output($cust['city']); ?>">
                                </div>
                                <div class="col-md-6 c-field mb-0">
                                    <label for="pfAddress">Pickup area address</label>
                                    <textarea id="pfAddress" name="address" class="form-control" rows="2"><?php echo sanitize_output($cust['address']); ?></textarea>
                                </div>
                            </div>
                            <button class="c-btn primary mt-3"><i class="bi bi-check2"></i> Save Profile</button>
                        </form>
                    </div>
                </div>

                <div class="c-card">
                    <div class="c-card-head"><h5><i class="bi bi-image"></i> Profile Photo</h5></div>
                    <div class="c-card-body">
                        <form data-profile-form enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="photo">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <input type="file" name="photo" class="form-control" style="max-width:320px;" accept="image/*">
                                <button class="c-btn outline"><i class="bi bi-upload"></i> Upload Photo</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="c-card">
                    <div class="c-card-head"><h5><i class="bi bi-shield-lock"></i> Change Password</h5></div>
                    <div class="c-card-body">
                        <form data-profile-form>
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="password">
                            <div class="row g-3">
                                <div class="col-md-4 c-field mb-0">
                                    <label for="pfCurPass">Current password</label>
                                    <input type="password" id="pfCurPass" name="current_password" class="form-control" required>
                                </div>
                                <div class="col-md-4 c-field mb-0">
                                    <label for="pfNewPass">New password</label>
                                    <input type="password" id="pfNewPass" name="new_password" class="form-control" minlength="8" required>
                                    <span class="c-hint">Minimum 8 characters.</span>
                                </div>
                                <div class="col-md-4 c-field mb-0">
                                    <label for="pfConPass">Confirm new password</label>
                                    <input type="password" id="pfConPass" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            <button class="c-btn primary mt-3"><i class="bi bi-key"></i> Update Password</button>
                        </form>
                    </div>
                </div>

                <div class="c-card">
                    <div class="c-card-head"><h5><i class="bi bi-clock-history"></i> Recent Activity</h5></div>
                    <?php if (empty($recent)): ?>
                        <div class="c-empty">
                            <i class="bi bi-receipt"></i>
                            <h6 class="fw-bold">No orders yet</h6>
                            <p class="mb-3">Place your first pre-order to see activity here.</p>
                            <a href="<?php echo ML_asset('products'); ?>" class="c-btn primary sm">Start Pre-Ordering</a>
                        </div>
                    <?php else: ?>
                        <div class="c-card-body flush">
                            <div class="c-table-wrap">
                                <table class="c-table">
                                    <thead><tr><th>Order</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($recent as $r): ?>
                                            <tr>
                                                <td><a href="<?php echo ML_asset('order') . '?id=' . $r['order_id']; ?>" class="fw-semibold text-decoration-none" style="color:var(--c-green-dark);"><?php echo orderRef($r['order_id']); ?></a></td>
                                                <td class="c-muted"><?php echo date('M j, Y', strtotime($r['order_date'])); ?></td>
                                                <td><?php echo money($r['total_amount']); ?></td>
                                                <td><?php echo orderStatusBadge($r['order_status']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('[data-profile-form]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var btn = form.querySelector('button[type="submit"]');
            var hasFile = !!form.querySelector('input[type="file"]');
            var body = new FormData(form);
            body.set('csrf_token', window.ML.csrf);
            if (hasFile) body.set('action', 'photo');
            if (btn) btn.disabled = true;

            fetch('../private/backend-scripting/customer-profile.php', { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (btn) btn.disabled = false;
                    if (res.ok) {
                        toast(res.msg || 'Saved');
                        setTimeout(function () { if (res.reload) window.location.reload(); }, 800);
                    } else {
                        toast(res.error || 'Could not save', false);
                    }
                }).catch(function () { if (btn) btn.disabled = false; toast('Network error', false); });
        });
    });
})();
</script>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>
