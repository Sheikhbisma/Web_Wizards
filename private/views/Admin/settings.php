<?php
validateAdmin();
include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap">
    <div class="a-page-head">
        <div>
            <h2><i class="bi bi-gear"></i> Settings</h2>
            <p>Master data and platform communication controls.</p>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="a-card">
        <div class="a-card-head">
            <h5><i class="bi bi-sliders"></i> System Configuration</h5>
        </div>
        <div class="a-card-body">
            <p class="a-muted mb-3">Manage your platform master data and communication from here:</p>
            <div class="a-grid-3">
                <div class="a-tile">
                    <i class="bi bi-tags-fill"></i>
                    <h6>Product Categories</h6>
                    <p>Add, edit, activate or deactivate product categories.</p>
                    <a href="index.php?page=admin-categories" class="a-btn primary sm">Manage Categories</a>
                </div>
                <div class="a-tile">
                    <i class="bi bi-megaphone-fill"></i>
                    <h6>Announcements</h6>
                    <p>Publish platform-wide announcements to all users.</p>
                    <a href="index.php?page=admin-announcements" class="a-btn primary sm">Manage Announcements</a>
                </div>
                <div class="a-tile">
                    <i class="bi bi-shop-window"></i>
                    <h6>Markets</h6>
                    <p>Add, edit or remove farmer markets with map locations.</p>
                    <a href="index.php?page=manage-markets" class="a-btn primary sm">Manage Markets</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/admin-footer.php'; ?>