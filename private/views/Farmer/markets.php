<?php
validateFarmer($pdo);
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT farmer_id FROM farmers WHERE user_id = ?");
$stmt->execute([$userId]);
$farmer = $stmt->fetch();

if (!$farmer) {
    set_flash("error", "Please complete your farmer profile first.");
    $_SESSION['old_input'] = $_POST;
    redirect("add-profile");
}

$f_id = $farmer['farmer_id'];

$query = "SELECT m.market_name, m.address, m.latitude, m.longitude, m.operating_days, m.opening_time, m.closing_time, m.map_provider
          FROM market_farmer AS mf
          INNER JOIN markets AS m ON mf.market_id = m.market_id
          WHERE mf.farmer_id = ?";

$selectMarket = selectData($pdo, $query, [$f_id]);

include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-shop"></i> My Markets</h2>
            <p>Markets where your stall is currently active.</p>
        </div>
        <a href="add-farmer-market" class="f-btn primary">
            <i class="bi bi-plus-circle"></i> Add Market
        </a>
    </div>

    <?php echo get_flash(); ?>

    <?php if (empty($selectMarket)): ?>
        <div class="f-card">
            <div class="f-empty">
                <i class="bi bi-shop"></i>
                No markets assigned to you yet. Join a market to start selling.
            </div>
        </div>
    <?php else: ?>
        <div class="f-card">
            <div class="f-card-body flush">
                <div class="f-entity-grid">
                    <?php foreach ($selectMarket as $market): ?>
                        <?php
                        $opening = !empty($market['opening_time']) ? date("h:i A", strtotime($market['opening_time'])) : '';
                        $closing = !empty($market['closing_time']) ? date("h:i A", strtotime($market['closing_time'])) : '';
                        ?>
                        <article class="f-entity-card f-market-card">
                            <div class="f-entity-heading">
                                <span class="f-entity-icon"><i class="bi bi-shop-window"></i></span>
                                <div>
                                    <h5><?php echo sanitize_output($market['market_name']); ?></h5>
                                    <p><?php echo sanitize_output($market['address']); ?></p>
                                </div>
                            </div>
                            <div class="f-market-details">
                                <div class="wide"><span>Operating days</span><strong><?php echo sanitize_output($market['operating_days'] ?? 'Not set'); ?></strong></div>
                                <div><span>Hours</span><strong><?php echo ($opening && $closing) ? "$opening – $closing" : 'Not set'; ?></strong></div>
                            </div>
                            <?php if (!empty($market['latitude']) && !empty($market['longitude'])): ?>
                                <a href="https://www.openstreetmap.org/?mlat=<?php echo urlencode($market['latitude']); ?>&mlon=<?php echo urlencode($market['longitude']); ?>#map=15/<?php echo urlencode($market['latitude']); ?>/<?php echo urlencode($market['longitude']); ?>" target="_blank" rel="noopener" class="f-btn outline sm"><i class="bi bi-geo-alt-fill"></i> View map</a>
                            <?php else: ?>
                                <span class="f-muted">Map location not available</span>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>