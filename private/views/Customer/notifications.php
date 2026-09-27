<?php
$pageTitle = 'Notifications - MarketLink';
$cust = customerGuard($pdo);
include __DIR__ . '/../../../public/components/header.php';
$uid = (int)$cust['user_id'];

$notifs = selectData($pdo, "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 40", [$uid]);
$unreadCount = 0;
foreach ($notifs as $n) {
    if (!$n['is_read']) $unreadCount++;
}

// Platform announcements live in their own table, not in notifications.
// They are read straight from announcements WHERE is_active = 1 so that the
// admin's activate/deactivate and delete buttons actually reach the customer:
// save-announcement.php used to fan each publish out into notifications, but
// nothing stopped those copies from surviving a later deactivate or delete,
// and a customer who received the copy still saw it after the admin withdrew
// the announcement. One table, one place, admin control that holds.
$announcements = selectData($pdo, "SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC, announcement_id DESC");
?>

<div class="c-page">
    <div class="c-wrap">
        <div class="c-page-head">
            <div>
                <div class="section-kicker">Updates</div>
                <h2 class="mt-1 mb-1">Notifications</h2>
                <p>Platform announcements, order updates and more.</p>
            </div>
            <?php if (!empty($notifs)): ?>
                <button class="c-btn outline" data-mark-all><i class="bi bi-check2-all"></i> Mark all as read</button>
            <?php endif; ?>
        </div>

        <?php /* Announcements lead the page. They are the one thing the admin
                 pushes to every customer, and burying them under a list of
                 per-order notices meant the sale banner was easy to miss. */ ?>
        <div class="c-card mb-3">
            <div class="c-card-head">
                <h5><i class="bi bi-megaphone"></i> Announcements</h5>
                <span class="c-badge <?php echo !empty($announcements) ? 'green' : 'grey'; ?>"><?php echo count($announcements); ?> active</span>
            </div>
            <div class="c-card-body">
                <?php if (empty($announcements)): ?>
                    <div class="c-empty">
                        <i class="bi bi-megaphone"></i>
                        <h6 class="fw-bold">No announcements right now</h6>
                        <p class="mb-0">Sales, holidays and market-wide notices will show up here.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($announcements as $a): ?>
                            <div class="c-list-item" style="background:var(--c-green-softer);border-radius:12px;">
                                <div class="d-flex gap-3 align-items-start">
                                    <span class="c-stat-icon green" style="width:42px;height:42px;font-size:1.1rem;"><i class="bi bi-megaphone-fill"></i></span>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <span class="fw-bold small"><?php echo sanitize_output($a['title']); ?></span>
                                            <span class="c-muted"><?php echo timeAgo($a['created_at']); ?></span>
                                        </div>
                                        <p class="c-muted mb-0 mt-1"><?php echo sanitize_output($a['content']); ?></p>
                                    </div>
                                    <span class="c-badge green">New</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($notifs)): ?>
            <div class="c-card">
                <div class="c-empty">
                    <i class="bi bi-bell-slash"></i>
                    <h6 class="fw-bold">No notifications yet</h6>
                    <p class="mb-0">Order and market updates will appear here.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="c-card">
                <div class="c-card-head">
                    <h5><i class="bi bi-bell"></i> Activity</h5>
                    <span class="c-badge <?php echo $unreadCount > 0 ? 'green' : 'grey'; ?>"><?php echo $unreadCount; ?> unread</span>
                </div>
                <div class="c-card-body">
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($notifs as $n):
                            $icon = 'bi-bell';
                            if ($n['type'] === 'order') $icon = 'bi-box-seam';
                            elseif ($n['type'] === 'restock') $icon = 'bi-arrow-repeat';
                            elseif ($n['type'] === 'review') $icon = 'bi-star';
                            else $icon = 'bi-megaphone';
                        ?>
                            <div class="c-list-item <?php echo !$n['is_read'] ? '' : ''; ?>" style="<?php echo !$n['is_read'] ? 'background:var(--c-green-softer);border-radius:12px;' : ''; ?>" data-notif-id="<?php echo $n['notification_id']; ?>">
                                <div class="d-flex gap-3 align-items-start">
                                    <span class="c-stat-icon green" style="width:42px;height:42px;font-size:1.1rem;"><i class="bi <?php echo $icon; ?>"></i></span>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <span class="fw-bold small"><?php echo sanitize_output($n['title']); ?></span>
                                            <span class="c-muted"><?php echo timeAgo($n['created_at']); ?></span>
                                        </div>
                                        <p class="c-muted mb-0 mt-1"><?php echo sanitize_output($n['message']); ?></p>
                                    </div>
                                    <?php if (!$n['is_read']): ?><span class="c-badge green">New</span><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>
