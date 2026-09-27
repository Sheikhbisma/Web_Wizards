<?php
$pageTitle = 'Search - MarketLink';
include __DIR__ . '/../../../public/components/header.php';

$q = trim($_GET['q'] ?? '');
$tab = $_GET['tab'] ?? 'products';

$products = [];
$farmers = [];
$markets = [];

if ($q !== '') {
    $like = '%' . $q . '%';
    if ($tab === 'products') {
        $products = selectData($pdo, "SELECT p.*, f.stall_name, c.category_name FROM products AS p
            INNER JOIN farmers AS f ON f.farmer_id = p.farmer_id AND f.approval_status = 'approved'
            INNER JOIN users AS u ON u.id = f.user_id AND u.status = 'active'
            LEFT JOIN categories AS c ON c.category_id = p.category_id
            WHERE p.is_available = 1 AND p.is_sold_out = 0 AND p.stock_quantity > 0
              AND (p.name LIKE ? OR p.description LIKE ? OR f.stall_name LIKE ? OR c.category_name LIKE ?)
            ORDER BY p.name LIMIT 24", [$like, $like, $like, $like]);
        $farmers = selectData($pdo, "SELECT f.farmer_id, f.stall_name, f.description, f.avg_rating, f.total_orders,
            (SELECT COUNT(*) FROM products AS q WHERE q.farmer_id = f.farmer_id AND q.is_available = 1) AS products
            FROM farmers AS f WHERE f.approval_status = 'approved' AND f.stall_name LIKE ? ORDER BY f.stall_name LIMIT 8", [$like]);
        $markets = selectData($pdo, "SELECT * FROM markets WHERE is_active = 1 AND (market_name LIKE ? OR address LIKE ?) ORDER BY market_name LIMIT 6", [$like, $like]);
    } elseif ($tab === 'farmers') {
        $farmers = selectData($pdo, "SELECT f.farmer_id, f.stall_name, f.description, f.avg_rating, f.total_orders,
            (SELECT COUNT(*) FROM products AS q WHERE q.farmer_id = f.farmer_id AND q.is_available = 1) AS products
            FROM farmers AS f WHERE f.approval_status = 'approved' AND (f.stall_name LIKE ? OR f.description LIKE ?) ORDER BY f.stall_name LIMIT 20", [$like, $like]);
    } else {
        $markets = selectData($pdo, "SELECT * FROM markets WHERE is_active = 1 AND (market_name LIKE ? OR address LIKE ?) ORDER BY market_name LIMIT 20", [$like, $like]);
    }
}

$popular = [];
if ($q === '') {
    $popular = selectData($pdo, "SELECT p.*, f.stall_name, c.category_name FROM products AS p
        INNER JOIN farmers AS f ON f.farmer_id = p.farmer_id
        LEFT JOIN categories AS c ON c.category_id = p.category_id
        WHERE p.avg_rating > 0 ORDER BY p.avg_rating DESC LIMIT 8", []);
}
?>

<div class="c-page">
    <div class="c-wrap">
        <div class="text-center mb-4" style="max-width:620px;margin-left:auto;margin-right:auto;">
            <div class="section-kicker">Search MarketLink</div>
            <h2 class="mt-2 mb-3">Find Fresh Produce</h2>
            <form method="get" class="d-flex gap-2">
                <input type="hidden" name="page" value="search">
                <input type="text" name="tab" value="<?php echo sanitize_output($tab); ?>" hidden>
                <input type="search" name="q" value="<?php echo sanitize_output($q); ?>" class="form-control" placeholder="tomatoes, mangoes, Nida's stall..." autofocus data-live-search>
                <button class="c-btn primary" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                <a href="?page=search&tab=products&q=" class="ml-chip <?php echo $tab === 'products' ? 'active' : ''; ?>">Products</a>
                <a href="?page=search&tab=farmers&q=" class="ml-chip <?php echo $tab === 'farmers' ? 'active' : ''; ?>">Farmers</a>
                <a href="?page=search&tab=markets&q=" class="ml-chip <?php echo $tab === 'markets' ? 'active' : ''; ?>">Markets</a>
            </div>
        </div>

        <?php if ($q !== ''): ?>
            <p class="text-center c-muted mb-4">
                <?php echo count($products) + count($farmers) + count($markets); ?> results for "<b><?php echo sanitize_output($q); ?></b>"
            </p>
        <?php endif; ?>

        <?php if ($q === '' && empty($popular)): ?>
            <div class="c-card">
                <div class="c-empty">
                    <i class="bi bi-search"></i>
                    <h6 class="fw-bold">Start typing to search</h6>
                    <p class="mb-0">Search markets, farmers and products.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($products)): ?>
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2"><i class="bi bi-basket2" style="color:var(--c-green);"></i> Products</h5>
            <div class="row g-3 mb-4">
                <?php foreach ($products as $p): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="ml-card">
                            <a href="<?php echo ML_asset('product') . '?id=' . $p['product_id']; ?>" class="text-decoration-none text-reset">
                                <div class="ml-card-img"><?php echo prodImg($p); ?></div>
                            </a>
                            <div class="ml-card-body">
                                <a href="<?php echo ML_asset('product') . '?id=' . $p['product_id']; ?>" class="text-decoration-none text-reset"><h6 class="ml-card-title"><?php echo sanitize_output($p['name']); ?></h6></a>
                                <div class="c-sub mb-1"><?php echo sanitize_output($p['stall_name']); ?></div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-tag"><?php echo money($p['price']); ?> <span class="unit-tag">/ <?php echo sanitize_output($p['unit']); ?></span></span>
                                    <button type="button" class="btn btn-sm p-0 border-0" data-add-cart="<?php echo $p['product_id']; ?>"><i class="bi bi-plus-circle-fill fs-5" style="color:var(--c-green);"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($farmers)): ?>
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2"><i class="bi bi-people" style="color:var(--c-green);"></i> Farmers</h5>
            <div class="row g-3 mb-4">
                <?php foreach ($farmers as $f): ?>
                    <div class="col-md-6 col-lg-4">
                        <a href="<?php echo ML_asset('farmer') . '?id=' . $f['farmer_id']; ?>" class="text-decoration-none text-reset">
                            <div class="c-card h-100">
                                <div class="c-card-body d-flex align-items-center gap-3">
                                    <span class="ml-farmer-avatar"><i class="bi bi-person-fill"></i></span>
                                    <div>
                                        <div class="fw-bold"><?php echo sanitize_output($f['stall_name']); ?></div>
                                        <div class="c-sub"><?php echo sanitize_output(mb_strimwidth($f['description'] ?? '', 0, 70, '...')); ?></div>
                                    </div>
                                    <span class="ms-auto"><?php echo starsHtml($f['avg_rating'], 13); ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($markets)): ?>
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2"><i class="bi bi-shop" style="color:var(--c-green);"></i> Markets</h5>
            <div class="row g-3 mb-4">
                <?php foreach ($markets as $mk): ?>
                    <div class="col-md-6 col-lg-4">
                        <a href="<?php echo ML_asset('market') . '?id=' . $mk['market_id']; ?>" class="text-decoration-none text-reset">
                            <div class="c-card h-100">
                                <div class="c-card-body d-flex align-items-center gap-3">
                                    <span class="c-stat-icon green"><i class="bi bi-shop"></i></span>
                                    <div>
                                        <div class="fw-bold"><?php echo sanitize_output($mk['market_name']); ?></div>
                                        <div class="c-sub"><i class="bi bi-geo-alt me-1"></i><?php echo sanitize_output($mk['address']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($q !== '' && empty($products) && empty($farmers) && empty($markets)): ?>
            <div class="c-card">
                <div class="c-empty">
                    <i class="bi bi-emoji-frown"></i>
                    <h6 class="fw-bold">Nothing found</h6>
                    <p class="mb-1">No results for "<?php echo sanitize_output($q); ?>".</p>
                    <p class="c-muted mb-0">Try a different keyword like "milk", "mango" or "bakery".</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>