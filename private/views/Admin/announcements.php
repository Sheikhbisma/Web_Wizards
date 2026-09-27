<?php
validateAdmin();

$announcements = selectData($pdo,
    "SELECT a.*, u.username AS created_by
     FROM announcements a
     LEFT JOIN users u ON a.admin_id = u.id
     ORDER BY a.created_at DESC, a.announcement_id DESC");

include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap">
    <div class="a-page-head">
        <div>
            <h2><i class="bi bi-megaphone"></i> Announcements</h2>
            <p>Broadcast platform-wide notices to every user.</p>
        </div>
        <div class="a-actions">
            <span class="a-chip"><i class="bi bi-megaphone"></i> <?php echo count($announcements); ?> published</span>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="a-form-side narrow">
        <div class="a-card">
            <div class="a-card-head">
                <h5><i class="bi bi-send"></i> Publish Announcement</h5>
            </div>
            <div class="a-card-body">
                <form action="../private/backend-scripting/save-announcement.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="a-field">
                        <label for="announcement_title">Title</label>
                        <input type="text" id="announcement_title" name="title" class="form-control" required placeholder="e.g. Holiday Notice">
                    </div>
                    <div class="a-field">
                        <label for="announcement_content">Message</label>
                        <textarea id="announcement_content" name="content" class="form-control" rows="4" required placeholder="Write the announcement content..."></textarea>
                    </div>
                    <button type="submit" name="publish_announcement" class="a-btn primary block">
                        <i class="bi bi-send"></i> Publish to All Users
                    </button>
                </form>
            </div>
        </div>

        <div class="a-card">
            <div class="a-card-head">
                <h5><i class="bi bi-list-ul"></i> All Announcements</h5>
            </div>
            <div class="a-card-body flush">
                <?php if (empty($announcements)): ?>
                    <div class="a-empty"><i class="bi bi-megaphone"></i>No announcements yet.</div>
                <?php else: ?>
                    <?php foreach ($announcements as $ann): ?>
                        <div class="a-list-item">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h6><?php echo sanitize_output($ann['title']); ?></h6>
                                    <?php echo $ann['is_active']
                                        ? '<span class="a-badge green ms-1">Active</span>'
                                        : '<span class="a-badge grey ms-1">Inactive</span>'; ?>
                                    <p><?php echo sanitize_output($ann['content']); ?></p>
                                    <span class="a-muted">
                                        By <?php echo sanitize_output($ann['created_by'] ?? 'Admin'); ?> &middot; <?php echo date("d M Y, h:i A", strtotime($ann['created_at'])); ?>
                                    </span>
                                </div>
                                <form action="../private/backend-scripting/save-announcement.php" method="POST" class="a-actions">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="announcement_id" value="<?php echo (int)$ann['announcement_id']; ?>">
                                    <button type="submit" name="toggle_announcement" class="a-btn ghost sm">
                                        <?php echo $ann['is_active'] ? 'Hide' : 'Show'; ?>
                                    </button>
                                    <button type="submit" name="delete_announcement" class="a-btn danger-soft sm" onclick="return confirm('Delete this announcement?');">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/admin-footer.php'; ?>