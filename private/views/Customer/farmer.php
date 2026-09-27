<?php
$pageTitle = 'Farmer Stall - MarketLink';

// Fallback logic to ensure page is NEVER empty if ?id= is missing or invalid
$fid = (int)($_GET['id'] ?? 0);
$farmer = [];
if ($fid > 0) {
    $farmer = selectData($pdo, "SELECT f.*, u.email, u.contact FROM farmers AS f INNER JOIN users AS u ON u.id = f.user_id WHERE f.farmer_id = ? AND f.approval_status = 'approved' AND u.status = 'active'", [$fid]);
}

if (empty($farmer)) {
    // Fallback to first available approved farmer
    $farmer = selectData($pdo, "SELECT f.*, u.email, u.contact FROM farmers AS f INNER JOIN users AS u ON u.id = f.user_id WHERE f.approval_status = 'approved' AND u.status = 'active' ORDER BY f.farmer_id ASC LIMIT 1");
    if (!empty($farmer)) {
        $fid = (int)$farmer[0]['farmer_id'];
    }
}

if (empty($farmer)) {
    set_flash('error', 'No active farmer stall found.');
    redirect('farmers');
}
$f = $farmer[0];

include __DIR__ . '/../../../public/components/header.php';

$products = selectData($pdo, "SELECT p.*, c.category_name FROM products AS p LEFT JOIN categories AS c ON c.category_id = p.category_id WHERE p.farmer_id = ? AND p.is_available = 1 AND p.is_sold_out = 0 AND p.stock_quantity > 0 ORDER BY c.category_name, p.name", [$fid]);
$stalls = selectData($pdo, "SELECT m.*, mf.stall_number, mf.day_of_week FROM market_farmer AS mf INNER JOIN markets AS m ON m.market_id = mf.market_id WHERE mf.farmer_id = ? AND m.is_active = 1", [$fid]);
$reviews = selectData($pdo, "SELECT r.comment, r.rating, r.review_date, c.full_name FROM reviews AS r INNER JOIN customers AS c ON c.customer_id = r.customer_id WHERE r.farmer_id = ? AND r.comment IS NOT NULL AND r.comment != '' ORDER BY r.review_date DESC LIMIT 6", [$fid]);

$maps = [];
if (!empty($f['latitude']) && !empty($f['longitude'])) {
    $maps[] = ['lat' => $f['latitude'], 'lng' => $f['longitude'], 'label' => $f['stall_name'], 'kind' => 'Farm stall'];
}
foreach ($stalls as $s) {
    if (!empty($s['latitude']) && !empty($s['longitude'])) {
        $maps[] = ['lat' => $s['latitude'], 'lng' => $s['longitude'], 'label' => $s['market_name'], 'kind' => 'Pickup market'];
    }
}

$mapFrameUrl = '';
$mapPrimary = null;
if (!empty($maps)) {
    $lats = array_map('floatval', array_column($maps, 'lat'));
    $lngs = array_map('floatval', array_column($maps, 'lng'));
    $padLat = max(0.006, (max($lats) - min($lats)) * 0.25);
    $padLng = max(0.008, (max($lngs) - min($lngs)) * 0.25);
    $mapFrameUrl = 'https://www.openstreetmap.org/export/embed.html?bbox='
        . (min($lngs) - $padLng) . '%2C' . (min($lats) - $padLat) . '%2C'
        . (max($lngs) + $padLng) . '%2C' . (max($lats) + $padLat)
        . '&layer=mapnik&marker=' . $lats[0] . '%2C' . $lngs[0];
    $mapPrimary = $maps[0];
}

$isLoggedCustomer = !empty($_SESSION['loggedIn']) && ($_SESSION['role'] ?? '') == 'customer';
$isFav = false;
if ($isLoggedCustomer) {
    $crows = selectData($pdo, "SELECT customer_id FROM customers WHERE user_id = ?", [$_SESSION['user_id']]);
    if (!empty($crows)) $isFav = isFav($pdo, $crows[0]['customer_id'], $fid);
}
?>

<!-- ============================================================
     FARMER PAGE STYLES — Light Green Theme & Portrait Video Layout
     ============================================================ -->
<style>
/* Light Soft Green Hero Section */
.farmer-hero-section {
    position: relative;
    background: linear-gradient(135deg, #f4f8ee 0%, #e4eed4 100%);
    padding: 70px 0 90px;
    color: #1a2412;
    overflow: hidden;
    border-bottom: 1px solid #d8e6c7;
}

.farmer-hero-grid {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    position: relative;
    z-index: 2;
}

.farmer-hero-content {
    flex: 1 1 52%;
    max-width: 620px;
}

.farmer-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #a0bc79;
    color: #4a5f31;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 6px 18px;
    border-radius: 50px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(160, 188, 121, 0.15);
}
.farmer-hero-badge i {
    color: #a0bc79;
}

.farmer-hero-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(2.2rem, 4.5vw, 3.4rem);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.5px;
    color: #1a2412;
    margin-bottom: 18px;
}

.farmer-hero-subtitle {
    font-size: clamp(0.98rem, 1.6vw, 1.15rem);
    color: #445437;
    line-height: 1.6;
    margin-bottom: 28px;
}

.farmer-hero-buttons {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;
}

.farmer-hero-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: #a0bc79;
    color: #1a261a;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 12px 30px;
    border-radius: 50px;
    text-decoration: none;
    box-shadow: 0 8px 22px rgba(160, 188, 121, 0.35);
    transition: all 0.3s ease;
}
.farmer-hero-btn-primary:hover {
    background: #8eab66;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(160, 188, 121, 0.45);
}

.farmer-btn-arrow {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ffffff;
    color: #1a261a;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.farmer-hero-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    color: #4a5f31;
    border: 2px solid #a0bc79;
    font-weight: 600;
    font-size: 0.95rem;
    padding: 10px 26px;
    border-radius: 50px;
    text-decoration: none;
    transition: all 0.3s ease;
}
.farmer-hero-btn-secondary:hover {
    background: #a0bc79;
    color: #ffffff;
    border-color: #a0bc79;
    transform: translateY(-2px);
}

/* Portrait Video Card — Matches Uploaded Reference Layout */
.farmer-portrait-video-wrapper {
    flex: 0 0 360px;
    max-width: 380px;
    width: 100%;
    aspect-ratio: 3 / 4;
    border-radius: 28px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 18px 40px rgba(74, 95, 49, 0.16);
    border: 6px solid #ffffff;
    background: #e4eed4;
}

.farmer-portrait-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* 3 Overlapping Highlight Cards */
.farmer-cards-overlap-container {
    margin-top: -45px;
    position: relative;
    z-index: 5;
}

.farmer-top-highlight-card {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(74, 95, 49, 0.12);
    background: #ffffff;
    border: 4px solid #ffffff;
    transition: transform 0.35s ease, box-shadow 0.35s ease;
}
.farmer-top-highlight-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 18px 38px rgba(74, 95, 49, 0.22);
}

.farmer-top-card-img-wrap {
    height: 220px;
    width: 100%;
    overflow: hidden;
    position: relative;
}
.farmer-top-card-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.45s ease;
}
.farmer-top-highlight-card:hover .farmer-top-card-img-wrap img {
    transform: scale(1.08);
}

.farmer-top-card-pill {
    position: absolute;
    bottom: 14px;
    left: 50%;
    transform: translateX(-50%);
    width: 88%;
    background: #ffffff;
    padding: 10px 18px;
    border-radius: 50px;
    text-align: center;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
}
.farmer-top-card-pill span {
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 0.9rem;
    font-weight: 700;
    color: #1a2412;
    display: block;
}

/* Story & Widgets Split Section */
.farmer-story-wrap {
    padding-right: 15px;
}

.farmer-kicker {
    font-size: 0.88rem;
    font-weight: 700;
    color: #4a5f31;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 8px;
}

.farmer-story-heading {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(1.8rem, 3.2vw, 2.5rem);
    font-weight: 700;
    color: #1a2412;
    line-height: 1.2;
    margin-bottom: 16px;
}

.farmer-story-text {
    font-size: 0.98rem;
    color: #55624c;
    line-height: 1.65;
    margin-bottom: 24px;
}

.farmer-pill-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: #a0bc79;
    color: #1a261a;
    font-weight: 700;
    font-size: 0.92rem;
    padding: 12px 28px;
    border-radius: 50px;
    text-decoration: none;
    box-shadow: 0 8px 20px rgba(160, 188, 121, 0.3);
    transition: all 0.3s ease;
}
.farmer-pill-cta-btn:hover {
    background: #8eab66;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 12px 26px rgba(160, 188, 121, 0.4);
}

.farmer-cta-circle {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ffffff;
    color: #1a261a;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.farmer-story-image-card {
    border-radius: 24px;
    overflow: hidden;
    height: 260px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    margin-top: 20px;
}
.farmer-story-image-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Widgets Stack */
.farmer-widgets-stack {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.farmer-gold-widget-card {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border-radius: 24px;
    padding: 28px 26px;
    color: #ffffff;
    box-shadow: 0 12px 28px rgba(230, 126, 34, 0.25);
}

.farmer-gold-widget-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 12px;
}

.farmer-gold-kicker {
    font-size: 0.85rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9);
    display: block;
    margin-bottom: 4px;
}

.farmer-gold-stat {
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: clamp(2.2rem, 3.5vw, 2.8rem);
    font-weight: 800;
    line-height: 1;
    margin: 0;
    color: #ffffff;
}

.farmer-gold-icon-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.22);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.farmer-gold-desc {
    font-size: 0.88rem;
    color: rgba(255, 255, 255, 0.92);
    margin: 0;
    line-height: 1.5;
}

/* Light Soft Green Feature Box (Replaced Darkest Green) */
.farmer-forest-widget-card {
    background: #e4eed4;
    border: 1px solid #d4e4be;
    border-radius: 24px;
    padding: 28px 26px;
    color: #1a2412;
    box-shadow: 0 12px 30px rgba(160, 188, 121, 0.18);
}

.farmer-forest-check-list {
    list-style: none;
    padding: 0;
    margin: 0 0 22px;
}
.farmer-forest-check-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.95rem;
    font-weight: 700;
    color: #24362a;
    margin-bottom: 12px;
}
.farmer-forest-check-list li i {
    color: #4a5f31;
    font-size: 1.1rem;
}

.farmer-forest-icon-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 16px;
    background: #ffffff;
    border: 1px solid #d4e4be;
    border-radius: 16px;
    margin-bottom: 12px;
}
.farmer-forest-icon-item i {
    font-size: 1.5rem;
    color: #4a5f31;
}
.farmer-forest-icon-item span {
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #1a2412;
}

/* Theme Product Card */
.farmer-prod-card {
    background: linear-gradient(180deg, #ffffff 0%, #f4f8ee 100%);
    border: 1px solid #d4e4be;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.farmer-prod-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 30px rgba(74, 95, 49, 0.18);
    border-color: #a0bc79;
}

.farmer-prod-img-wrap {
    height: 180px;
    width: 100%;
    overflow: hidden;
    position: relative;
    background: #ffffff;
}
.farmer-prod-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.farmer-prod-card:hover .farmer-prod-img-wrap img {
    transform: scale(1.06);
}

.farmer-prod-body {
    padding: 18px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.farmer-prod-cat {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #4a5f31;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.farmer-prod-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1a2412;
    margin-bottom: 8px;
}

.farmer-prod-price {
    font-size: 1.15rem;
    font-weight: 800;
    color: #24362a;
    margin-top: auto;
    margin-bottom: 12px;
}

@media (max-width: 991px) {
    .farmer-hero-grid {
        flex-direction: column;
        text-align: center;
    }
    .farmer-hero-content {
        max-width: 100%;
    }
    .farmer-hero-buttons {
        justify-content: center;
    }
    .farmer-portrait-video-wrapper {
        max-width: 320px;
        margin: 0 auto;
    }
    .farmer-cards-overlap-container {
        margin-top: 30px;
    }
}
</style>

<!-- ============================================================
     HERO SECTION — Light Green Theme & Portrait Video Layout
     ============================================================ -->
<section class="farmer-hero-section">
    <div class="container">
        <div class="farmer-hero-grid">
            
            <!-- Left Side: Content -->
            <div class="farmer-hero-content">
                <span class="farmer-hero-badge">
                    <i class="fas fa-leaf"></i> Better Agriculture for Better Future
                </span>
                
                <h1 class="farmer-hero-title">
                    EVERY CROP COUNTS, EVERY FARMER MATTERS.
                </h1>
                
                <p class="farmer-hero-subtitle">
                    Welcome to <strong><?= htmlspecialchars($f['stall_name']) ?></strong>! <?= htmlspecialchars(!empty($f['description']) ? $f['description'] : 'Delivering fresh, organic, sustainable farm produce directly from our harvest to your table.') ?>
                </p>

                <div class="farmer-hero-buttons">
                    <a href="#farmer-products" class="farmer-hero-btn-primary">
                        <span>Explore Stall Products</span>
                        <span class="farmer-btn-arrow"><i class="fas fa-arrow-right"></i></span>
                    </a>

                    <?php if ($isLoggedCustomer): ?>
                        <form method="POST" action="<?= ML_asset('api/favorites.php') ?>" style="display:inline;">
                            <input type="hidden" name="farmer_id" value="<?= $fid ?>">
                            <input type="hidden" name="action" value="toggle">
                            <button type="submit" class="farmer-hero-btn-secondary">
                                <i class="<?= $isFav ? 'fas fa-heart text-danger' : 'far fa-heart' ?>"></i>
                                <?= $isFav ? 'Favorited' : 'Favorite Stall' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Side: Portrait Video Box (No Overlay) -->
            <div class="farmer-portrait-video-wrapper">
                <video src="<?= ML_asset('Uploads/farmer-video.mp4') ?>" autoplay loop muted playsinline class="farmer-portrait-video"></video>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     3 OVERLAPPING HIGHLIGHT CARDS
     ============================================================ -->
<div class="container farmer-cards-overlap-container">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="farmer-top-highlight-card">
                <div class="farmer-top-card-img-wrap">
                    <img src="<?= ML_asset('Uploads/img/farmers.png') ?>" alt="We Use Natural Tech">
                    <div class="farmer-top-card-pill">
                        <span>We Use Natural Tech</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="farmer-top-highlight-card">
                <div class="farmer-top-card-img-wrap">
                    <img src="<?= ML_asset('Uploads/img/crop.webp') ?>" alt="Making Healthy Foods">
                    <div class="farmer-top-card-pill">
                        <span>Making Healthy Foods</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="farmer-top-highlight-card">
                <div class="farmer-top-card-img-wrap">
                    <img src="<?= ML_asset('Uploads/img/crops.webp') ?>" alt="Reforming The System">
                    <div class="farmer-top-card-pill">
                        <span>Reforming The System</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     STORY & WIDGETS SPLIT SECTION
     ============================================================ -->
<section class="py-5 mt-4">
    <div class="container">
        <div class="row g-5 align-items-center">
            
            <!-- Left Side: Story & Farm Image -->
            <div class="col-lg-6">
                <div class="farmer-story-wrap">
                    <span class="farmer-kicker">We have 20+ years of agriculture & farming experience</span>
                    <h2 class="farmer-story-heading">Delivering Farm Fresh Produce Directly To You</h2>
                    <p class="farmer-story-text">
                        <?= htmlspecialchars(!empty($f['description']) ? $f['description'] : 'Our farm is dedicated to sustainability by providing fresh, high quality agricultural produce with efficient, eco-friendly farming techniques. We connect consumers directly to the source of their food.') ?>
                    </p>
                    <a href="#farmer-products" class="farmer-pill-cta-btn">
                        <span>View Available Harvest</span>
                        <span class="farmer-cta-circle"><i class="fas fa-arrow-right"></i></span>
                    </a>

                    <div class="farmer-story-image-card">
                        <img src="<?= ML_asset('Uploads/img/farmers.png') ?>" alt="Farm field">
                    </div>
                </div>
            </div>

            <!-- Right Side: Gold Stat Card + Light Soft Green Feature Box -->
            <div class="col-lg-6">
                <div class="farmer-widgets-stack">
                    
                    <!-- Gold Stat Card -->
                    <div class="farmer-gold-widget-card">
                        <div class="farmer-gold-widget-header">
                            <div>
                                <span class="farmer-gold-kicker">Trusted By Customers</span>
                                <h3 class="farmer-gold-stat">12,980+</h3>
                            </div>
                            <div class="farmer-gold-icon-btn">
                                <i class="fas fa-seedling"></i>
                            </div>
                        </div>
                        <p class="farmer-gold-desc">
                            Delivering organic farm products to thousands of happy families every week.
                        </p>
                    </div>

                    <!-- Light Soft Green Feature Widget -->
                    <div class="farmer-forest-widget-card">
                        <ul class="farmer-forest-check-list">
                            <li><i class="fas fa-check-circle"></i> Modern Agriculture Equipment</li>
                            <li><i class="fas fa-check-circle"></i> Awesome Harvest of Produce</li>
                            <li><i class="fas fa-check-circle"></i> Fresh Fruits & Organic Vegetables</li>
                        </ul>

                        <div class="farmer-forest-icon-item">
                            <i class="fas fa-user-check"></i>
                            <span>HIGHLY QUALIFIED & SPECIALIZED FARMERS</span>
                        </div>

                        <div class="farmer-forest-icon-item">
                            <i class="fas fa-apple-alt"></i>
                            <span>FRUITS, VEGETABLES & ORGANIC PRODUCE</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     PRODUCTS SECTION
     ============================================================ -->
<section id="farmer-products" class="py-5" style="background: #f8faf6;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="farmer-kicker">Fresh Harvest</span>
            <h2 class="farmer-story-heading">Available Products from <?= htmlspecialchars($f['stall_name']) ?></h2>
        </div>

        <?php if (empty($products)): ?>
            <div class="alert alert-info text-center rounded-4 py-4">
                <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                No active products currently listed by this stall. Please check back soon!
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $p): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="farmer-prod-card">
                            <div class="farmer-prod-img-wrap">
                                <img src="<?= prodImgSrc($p) ?: ML_asset('Uploads/img/straw.png') ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                            </div>
                            <div class="farmer-prod-body">
                                <span class="farmer-prod-cat"><?= htmlspecialchars($p['category_name'] ?? 'Fresh') ?></span>
                                <h4 class="farmer-prod-title"><?= htmlspecialchars($p['name']) ?></h4>
                                <div class="farmer-prod-price">$<?= number_format((float)$p['price'], 2) ?> <small class="text-muted fw-normal">/ <?= htmlspecialchars($p['unit'] ?? 'unit') ?></small></div>
                                
                                <form method="POST" action="<?= ML_asset('cart_actions.php') ?>" class="mt-2">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm w-100 rounded-pill fw-bold" style="background: #a0bc79; color: #1a261a;">
                                        <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================================
     LOCATION & MARKETS SECTION (OpenStreetMap Embed)
     ============================================================ -->
<?php if (!empty($mapFrameUrl)): ?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <span class="farmer-kicker">Find Our Stall</span>
            <h2 class="farmer-story-heading">Location & Pickup Markets</h2>
        </div>
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="rounded-4 overflow-hidden shadow-sm border">
                    <iframe width="100%" height="380" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?= $mapFrameUrl ?>"></iframe>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="farmer-forest-widget-card">
                    <h4 class="fw-bold mb-3" style="color: #1a2412;"><i class="fas fa-map-marker-alt me-2" style="color: #4a5f31;"></i> Stalls & Schedules</h4>
                    <p class="small mb-3" style="color: #55624c;">Visit our stall at the following local markets:</p>
                    
                    <?php if (!empty($stalls)): ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($stalls as $st): ?>
                                <li class="border-bottom pb-2 mb-2" style="border-color: #d4e4be !important;">
                                    <strong class="d-block" style="color: #1a2412;"><?= htmlspecialchars($st['market_name']) ?></strong>
                                    <small style="color: #55624c;">Stall #<?= htmlspecialchars($st['stall_number'] ?? 'N/A') ?> — <?= htmlspecialchars($st['day_of_week'] ?? 'Everyday') ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="mb-0" style="color: #55624c;">Farm location: <?= htmlspecialchars($f['city'] ?? 'Local Region') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     REVIEWS SECTION
     ============================================================ -->
<?php if (!empty($reviews)): ?>
<section class="py-5" style="background: #f4f8ee;">
    <div class="container">
        <div class="text-center mb-4">
            <span class="farmer-kicker">Customer Feedback</span>
            <h2 class="farmer-story-heading">What Customers Say</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($reviews as $rev): ?>
                <div class="col-md-4">
                    <div class="bg-white p-4 rounded-4 shadow-sm h-100 border">
                        <div class="text-warning mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="<?= $i <= (int)$rev['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-dark small fst-italic mb-3">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                        <strong class="d-block text-success small">— <?= htmlspecialchars($rev['full_name']) ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>