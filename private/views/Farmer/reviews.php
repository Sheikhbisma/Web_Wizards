<?php
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];

$reviews = selectData($pdo,
    "SELECT r.*, p.name AS product_name, c.full_name, u.username, u.email
     FROM reviews r
     LEFT JOIN products p ON r.product_id = p.product_id
     LEFT JOIN customers c ON r.customer_id = c.customer_id
     LEFT JOIN users u ON c.user_id = u.id
     WHERE r.farmer_id = ?
        OR (r.farmer_id IS NULL AND r.product_id IN (SELECT product_id FROM products WHERE farmer_id = ?))
     ORDER BY r.review_date DESC", [$farmerId, $farmerId]);

$unanswered = 0;
foreach ($reviews as $rev) {
    if (empty($rev['farmer_response'])) {
        $unanswered++;
    }
}

function starRating($rating)
{
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= $i <= $rating
            ? '<i class="bi bi-star-fill" style="color:var(--f-amber);"></i>'
            : '<i class="bi bi-star" style="color:var(--f-grey);"></i>';
    }
    return $stars;
}

include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-star"></i> Reviews</h2>
            <p>See what customers say about your stall and respond.</p>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="f-stats">
        <div class="f-stat">
            <span class="f-stat-icon green"><i class="bi bi-star-fill"></i></span>
            <div>
                <h3><?php echo count($reviews); ?></h3>
                <p>Total Reviews</p>
            </div>
        </div>
        <div class="f-stat">
            <span class="f-stat-icon amber"><i class="bi bi-chat-square-text"></i></span>
            <div>
                <h3><?php echo $unanswered; ?></h3>
                <p>Awaiting Response</p>
            </div>
        </div>
        <div class="f-stat">
            <span class="f-stat-icon blue"><i class="bi bi-stars"></i></span>
            <div>
                <h3><?php echo !empty($farmer['avg_rating']) ? number_format((float)$farmer['avg_rating'], 1) . ' / 5' : 'N/A'; ?></h3>
                <p>Average Rating</p>
            </div>
        </div>
    </div>

    <?php if (empty($reviews)): ?>
        <div class="f-card">
            <div class="f-empty">
                <i class="bi bi-star"></i>
                No reviews yet. Keep up the good work!
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <div class="f-review">
                <div class="f-review-head">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <strong><?php echo sanitize_output($review['username'] ?? $review['full_name'] ?? 'Customer'); ?></strong>
                            <?php echo starRating((int)$review['rating']); ?>
                        </div>
                        <small class="f-muted"><?php echo date("d M Y, h:i A", strtotime($review['review_date'])); ?></small>
                    </div>
                    <?php if (!empty($review['product_name'])): ?>
                        <span class="f-badge grey"><?php echo sanitize_output($review['product_name']); ?></span>
                    <?php endif; ?>
                </div>

                <p class="mb-3 mt-2"><?php echo sanitize_output($review['comment'] ?? 'No comment'); ?></p>

                <?php if (!empty($review['farmer_response'])): ?>
                    <div class="f-review-reply">
                        <strong><i class="bi bi-reply"></i> Your Response</strong>
                        <p class="mb-0"><?php echo sanitize_output($review['farmer_response']); ?></p>
                    </div>
                <?php endif; ?>

                <form action="../private/backend-scripting/save-review-response.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="review_id" value="<?php echo (int)$review['review_id']; ?>">
                    <div class="f-field mb-2">
                        <textarea name="farmer_response" rows="2" class="form-control" placeholder="Write a public response..." required><?php echo sanitize_output($review['farmer_response'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="save_response" class="f-btn primary sm">
                        <i class="bi bi-send"></i>
                        <?php echo empty($review['farmer_response']) ? 'Post Response' : 'Update Response'; ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>