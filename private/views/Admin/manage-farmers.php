<?php
validateAdmin();
$showFarmers = "SELECT u.contact,u.email, f.* FROM farmers AS f INNER JOIN users AS u ON u.id = f.user_id WHERE u.role = ? ";
$farmers = selectData($pdo, $showFarmers , ["farmer"]);
include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap">
    <div class="a-page-head">
        <div>
            <h2><i class="bi bi-people-fill"></i> Manage Farmers</h2>
            <p>Approve, suspend or review every farmer stall on the platform.</p>
        </div>
        <div class="a-actions">
            <span class="a-chip"><i class="bi bi-shop"></i> <?php echo count($farmers); ?> stalls</span>
            <span class="a-chip"><i class="bi bi-hourglass-split"></i> <?php echo count(array_filter($farmers, function ($f) { return $f['approval_status'] === 'pending'; })); ?> pending</span>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="a-card">
        <div class="a-card-head">
            <h5><i class="bi bi-list-ul"></i> All Farmers</h5>
            <a href="index.php?page=admin-dashboard" class="a-btn ghost sm"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </div>
        <div class="a-card-body flush">
            <?php if (empty($farmers)): ?>
                <div class="a-empty"><i class="bi bi-people"></i>No farmers found.</div>
            <?php else: ?>
                <div class="a-entity-grid">
                    <?php foreach ($farmers as $farmer): ?>
                        <article class="a-entity-card">
                            <div class="a-entity-card-head">
                                <div class="a-entity-title">
                                    <span class="a-entity-icon"><i class="bi bi-shop"></i></span>
                                    <div>
                                        <h6><?php echo sanitize_output($farmer['stall_name']); ?></h6>
                                        <p><?php echo sanitize_output($farmer['contact_person']); ?></p>
                                    </div>
                                </div>
                                <?php echo adminApprovalBadge($farmer['approval_status']); ?>
                            </div>
                            <div class="a-entity-details">
                                <div><span>Contact</span><strong><?php echo sanitize_output($farmer['contact'] ?: 'Not provided'); ?></strong></div>
                                <div><span>Email</span><strong><?php echo sanitize_output($farmer['email'] ?: 'Not provided'); ?></strong></div>
                                <div class="wide"><span>Address</span><strong><?php echo sanitize_output($farmer['address'] ?: 'Not provided'); ?></strong></div>
                            </div>
                            <form action="../private/backend-scripting/update-farmer-status.php" method="POST" class="a-entity-actions">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="farmer_id" value="<?php echo (int)$farmer['farmer_id']; ?>">
                                <label class="visually-hidden" for="farmer-status-<?php echo (int)$farmer['farmer_id']; ?>">Status for <?php echo sanitize_output($farmer['stall_name']); ?></label>
                                <select id="farmer-status-<?php echo (int)$farmer['farmer_id']; ?>" name="approval_status" class="form-select form-select-sm" required>
                                    <option value="pending" <?php if ($farmer['approval_status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="approved" <?php if ($farmer['approval_status'] == 'approved') echo 'selected'; ?>>Approved</option>
                                    <option value="suspended" <?php if ($farmer['approval_status'] == 'suspended') echo 'selected'; ?>>Suspended</option>
                                </select>
                                <button type="submit" name="update_status" class="a-btn primary sm">Update status</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/admin-footer.php'; ?>