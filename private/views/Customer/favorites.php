<?php
$pageTitle = 'Favorites - MarketLink';
$cust = customerGuard($pdo);
include __DIR__ . '/../../../public/components/header.php';
$cid = (int)$cust['customer_id'];

$tab = $_GET['tab'] ?? 'products';

$favProducts = [];
$favFarmers = [];
$favMarkets = [];
if ($tab === 'products') {
    $favProducts = selectData($pdo, "SELECT p.*, f.stall_name, c.category_name, fa.created_at AS fav_at
        FROM favorites AS fa
        INNER JOIN products AS p ON p.product_id = fa.product_id
        INNER JOIN farmers AS f ON f.farmer_id = p.farmer_id
        LEFT JOIN categories AS c ON c.category_id = p.category_id
        WHERE fa.customer_id = ? ORDER BY fa.created_at DESC", [$cid]);
} elseif ($tab === 'farmers') {
    $favFarmers = selectData($pdo, "SELECT f.*, u.email, fa.created_at AS fav_at,
        (SELECT COUNT(*) FROM products AS q WHERE q.farmer_id = f.farmer_id AND q.is_available = 1) AS products
        FROM favorites AS fa
        INNER JOIN farmers AS f ON f.farmer_id = fa.farmer_id
        INNER JOIN users AS u ON u.id = f.user_id
        WHERE fa.customer_id = ? ORDER BY fa.created_at DESC", [$cid]);
} else {
    $favMarkets = selectData($pdo, "SELECT m.*, fa.created_at AS fav_at,
        (SELECT COUNT(DISTINCT mf2.farmer_id) FROM market_farmer AS mf2 WHERE mf2.market_id = m.market_id) AS stalls
        FROM favorites AS fa
        INNER JOIN markets AS m ON m.market_id = fa.market_id
        WHERE fa.customer_id = ? AND m.is_active = 1 ORDER BY fa.created_at DESC", [$cid]);
}

$allFav = count($favProducts) + count($favFarmers) + count($favMarkets);
?>

<div class="c-page">
    <div class="c-wrap">
        <div class="c-page-head">
            <div>
                <div class="section-kicker">Saved for Later</div>
                <h2 class="mt-1 mb-1">Favorites</h2>
                <p><?php echo $allFav; ?> saved item(s) in your list.</p>
            </div>
            <a href="<?php echo ML_asset('products'); ?>" class="c-btn primary"><i class="bi bi-basket"></i> Browse Products</a>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="?page=favorites&tab=products" class="ml-chip <?php echo $tab === 'products' ? 'active' : ''; ?>">Products (<?php echo count($favProducts); ?>)</a>
            <a href="?page=favorites&tab=farmers" class="ml-chip <?php echo $tab === 'farmers' ? 'active' : ''; ?>">Farmers (<?php echo count($favFarmers); ?>)</a>
            <a href="?page=favorites&tab=markets" class="ml-chip <?php echo $tab === 'markets' ? 'active' : ''; ?>">Markets (<?php echo count($favMarkets); ?>)</a>
        </div>

        <?php if ($tab === 'products'): ?>
            <?php if (empty($favProducts)): ?>
                <div class="c-card">
                    <div class="c-empty">
                        <i class="bi bi-heart"></i>
                        <h6 class="fw-bold">No favorite products yet</h6>
                        <p class="mb-0">Tap the heart on any product to save it here.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($favProducts as $p): ?>
                        <div class="col-6 col-md-4 col-lg-3" data-fav-item>
                            <div class="ml-card">
                                <a href="<?php echo ML_asset('product') . '?id=' . $p['product_id']; ?>" class="text-decoration-none text-reset">
                                    <div class="ml-card-img"><?php echo prodImg($p); ?></div>
                                </a>
                                <div class="ml-card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-1">
                                        <a href="<?php echo ML_asset('product') . '?id=' . $p['product_id']; ?>" class="text-decoration-none text-reset"><h6 class="ml-card-title"><?php echo sanitize_output($p['name']); ?></h6></a>
                                        <button type="button" class="btn btn-sm p-0 border-0 is-fav" data-fav-btn data-fav-type="product" data-fav-id="<?php echo $p['product_id']; ?>" data-fav-silent title="Remove"><i class="bi bi-heart-fill fs-5" style="color:var(--c-red);"></i></button>
                                    </div>
                                    <div class="c-sub mb-1"><?php echo sanitize_output($p['stall_name']); ?></div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price-tag"><?php echo money($p['price']); ?> <span class="unit-tag">/ <?php echo sanitize_output($p['unit']); ?></span></span>
                                        <button type="button" class="btn btn-sm p-0 border-0" data-add-cart="<?php echo $p['product_id']; ?>" title="Add to cart"><i class="bi bi-cart-plus-fill fs-5" style="color:var(--c-green);"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php elseif ($tab === 'farmers'): ?>
            <?php if (empty($favFarmers)): ?>
                <div class="c-card">
                    <div class="c-empty">
                        <i class="bi bi-heart"></i>
                        <h6 class="fw-bold">No favorite farmers yet</h6>
                        <p class="mb-0">Save farmers you love for quick access.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($favFarmers as $f): ?>
                        <div class="col-md-6 col-lg-4" data-fav-item>
                            <div class="c-card h-100">
                                <div class="c-card-body">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="ml-farmer-avatar"><i class="bi bi-person-fill"></i></span>
                                            <div>
                                                <h6 class="fw-bold mb-0"><?php echo sanitize_output($f['stall_name']); ?></h6>
                                                <span class="c-sub"><?php echo (int)$f['products']; ?> products</span>
                                                <?php echo starsHtml($f['avg_rating'], 13); ?>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm p-0 border-0 is-fav" data-fav-btn data-fav-type="farmer" data-fav-id="<?php echo $f['farmer_id']; ?>" data-fav-silent title="Remove"><i class="bi bi-heart-fill fs-5" style="color:var(--c-red);"></i></button>
                                    </div>
                                    <p class="c-muted my-3"><?php echo sanitize_output(mb_strimwidth($f['description'] ?? '', 0, 90, '...')); ?></p>
                                    <a href="<?php echo ML_asset('farmer') . '?id=' . $f['farmer_id']; ?>" class="c-btn outline sm block">Visit Stall</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if (empty($favMarkets)): ?>
                <div class="c-card">
                    <div class="c-empty">
                        <i class="bi bi-geo-alt"></i>
                        <h6 class="fw-bold">No favorite markets yet</h6>
                        <p class="mb-0">Tap the heart on any market to get quick access to its pickup days.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($favMarkets as $m): ?>
                        <div class="col-md-6 col-lg-4" data-fav-item>
                            <div class="c-card h-100">
                                <div class="c-card-body">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="ml-farmer-avatar"><i class="bi bi-shop-window"></i></span>
                                            <div>
                                                <h6 class="fw-bold mb-0"><?php echo sanitize_output($m['market_name']); ?></h6>
                                                <span class="c-sub"><?php echo (int)$m['stalls']; ?> stalls</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm p-0 border-0 is-fav" data-fav-btn data-fav-type="market" data-fav-id="<?php echo $m['market_id']; ?>" data-fav-silent title="Remove"><i class="bi bi-heart-fill fs-5" style="color:var(--c-red);"></i></button>
                                    </div>
                                    <p class="c-muted my-3 d-flex align-items-center gap-2"><i class="bi bi-geo-alt"></i> <?php echo sanitize_output($m['address']); ?></p>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge text-bg-light border"><i class="bi bi-calendar3 me-1"></i><?php echo marketPickups($m); ?></span>
                                        <span class="badge text-bg-light border"><i class="bi bi-clock me-1"></i><?php echo date('g:i A', strtotime($m['opening_time'])); ?> &ndash; <?php echo date('g:i A', strtotime($m['closing_time'])); ?></span>
                                    </div>
                                    <a href="<?php echo ML_asset('market') . '?id=' . $m['market_id']; ?>" class="c-btn outline sm block">View Market</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>
