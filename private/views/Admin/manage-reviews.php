<?php
validateAdmin();

if (isset($_POST['delete_review'])) {
    verify_csrf();
    $review_id = (int)($_POST['review_id'] ?? 0);

    if (!empty($review_id)) {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = ?");
        $stmt->execute([$review_id]);

        if ($stmt->rowCount() > 0) {
            set_flash("success", "Review removed successfully.");
        } else {
            set_flash("error", "Review not found.");
        }
    } else {
        set_flash("error", "Invalid review ID.");
    }
    redirect("admin-reviews");
}

$reviews = selectData($pdo,
    "SELECT r.*, p.name AS product_name, f.stall_name, c.full_name, u.username, u.email
     FROM reviews r
     LEFT JOIN products p ON r.product_id = p.product_id
     LEFT JOIN farmers f ON r.farmer_id = f.farmer_id
     LEFT JOIN customers c ON r.customer_id = c.customer_id
     LEFT JOIN users u ON c.user_id = u.id
     ORDER BY r.review_date DESC");

function starRating($rating)
{
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= $i <= $rating
            ? '<i class="bi bi-star-fill" style="color:var(--a-amber);"></i>'
            : '<i class="bi bi-star" style="color:var(--a-grey-soft);"></i>';
    }
    return $stars;
}

include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap">
    <div class="a-page-head">
        <div>
            <h2><i class="bi bi-star-half"></i> Review Moderation</h2>
            <p>Keep the marketplace clean by reviewing customer feedback.</p>
        </div>
        <div class="a-actions">
            <span class="a-chip"><i class="bi bi-chat-quote"></i> <?php echo count($reviews); ?> reviews</span>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="a-card">
        <div class="a-card-head">
            <h5><i class="bi bi-list-ul"></i> All Reviews</h5>
            <a href="index.php?page=admin-dashboard" class="a-btn ghost sm"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </div>
        <div class="a-card-body flush">
            <?php if (empty($reviews)): ?>
                <div class="a-empty"><i class="bi bi-star"></i>No reviews found.</div>
            <?php else: ?>
                <div class="a-table-wrap">
                    <table class="a-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Product / Farmer</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Farmer Response</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo sanitize_output($review['username'] ?? $review['full_name'] ?? 'Customer'); ?></strong>
                                        <span class="a-sub"><?php echo sanitize_output($review['email'] ?? ''); ?></span>
                                    </td>
                                    <td>
                                        <?php echo sanitize_output($review['product_name'] ?? 'General'); ?>
                                        <span class="a-sub"><?php echo sanitize_output($review['stall_name'] ?? 'Farmer'); ?></span>
                                    </td>
                                    <td><?php echo starRating((int)$review['rating']); ?></td>
                                    <td><?php echo sanitize_output($review['comment'] ?? 'No comment'); ?></td>
                                    <td>
                                        <?php echo !empty($review['farmer_response'])
                                            ? sanitize_output($review['farmer_response'])
                                            : '<span class="a-badge grey">No Response</span>'; ?>
                                    </td>
                                    <td><?php echo date("d M Y", strtotime($review['review_date'])); ?></td>
                                    <td>
                                        <form action="" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="review_id" value="<?php echo (int)$review['review_id']; ?>">
                                            <button type="submit" name="delete_review" class="a-btn danger-soft sm" onclick="return confirm('Remove this review?');">Remove</button>
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
</div>

<?php include __DIR__ . '/../../../public/components/admin-footer.php'; ?>