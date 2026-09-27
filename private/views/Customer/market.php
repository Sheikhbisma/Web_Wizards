<?php
$pageTitle = 'Market Details - MarketLink';
$page = 'markets';
$marketId = (int)($_GET['id'] ?? 0);
$market = selectData($pdo, "SELECT * FROM markets WHERE market_id = ? AND is_active = 1", [$marketId]);
if (empty($market)) {
    set_flash('error', 'Market not found.');
    redirect('markets');
}
$market = $market[0];
include __DIR__ . '/../../../public/components/header.php';

$farmers = selectData($pdo, "SELECT f.*, mf.stall_number, mf.pickup_start, mf.pickup_end, mf.day_of_week, u.username
    FROM market_farmer AS mf
    INNER JOIN farmers AS f ON f.farmer_id = mf.farmer_id AND f.approval_status = 'approved'
    INNER JOIN users AS u ON u.id = f.user_id AND u.status = 'active'
    WHERE mf.market_id = ? ORDER BY f.stall_name ASC", [$marketId]);

$products = selectData($pdo, "SELECT p.*, f.stall_name, c.category_name
    FROM market_farmer AS mf
    INNER JOIN products AS p ON p.farmer_id = mf.farmer_id AND p.is_available = 1 AND p.is_sold_out = 0 AND p.stock_quantity > 0
    INNER JOIN farmers AS f ON f.farmer_id = p.farmer_id
    LEFT JOIN categories AS c ON c.category_id = p.category_id
    WHERE mf.market_id = ? ORDER BY c.category_name, p.name ASC", [$marketId]);

$allMarkets = selectData($pdo, "SELECT market_id, market_name FROM markets WHERE is_active = 1 ORDER BY market_name");

$mapUrl = 'https://www.openstreetmap.org/export/embed.html?bbox=' . ($market['longitude'] - 0.01) . '%2C' . ($market['latitude'] - 0.008) . '%2C' . ($market['longitude'] + 0.01) . '%2C' . ($market['latitude'] + 0.008) . '&layer=mapnik&marker=' . $market['latitude'] . '%2C' . $market['longitude'];
$dirUrl = 'https://www.openstreetmap.org/directions?from=&to=' . $market['latitude'] . '%2C' . $market['longitude'];

$fruitCards = [
    [
        'title' => 'Peaches',
        'desc' => 'Lush organic orchard fruits picked at peak morning sweetness.',
        'bg' => 'linear-gradient(135deg, #f77f00 0%, #d62828 100%)',
        'img' => ML_asset('Uploads/img/peaches_3d.png'),
        'blend' => false
    ],
    [
        'title' => 'Oranges',
        'desc' => 'Naturally ripened, bursting with Vitamin C and fresh orchard dew.',
        'bg' => 'linear-gradient(135deg, #f48c06 0%, #e85d04 100%)',
        'img' => ML_asset('Uploads/img/oranges_3d.png'),
        'blend' => false
    ],
    [
        'title' => 'Peas',
        'desc' => 'Crisp sweet pods and garden greens harvested fresh before market.',
        'bg' => 'linear-gradient(135deg, #8cb369 0%, #43aa8b 100%)',
        'img' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=400&q=80',
        'blend' => true
    ],
    [
        'title' => 'Beet',
        'desc' => 'Nutrient-dense organic root vegetables from mineral-rich soils.',
        'bg' => 'linear-gradient(135deg, #9d0208 0%, #6a040f 100%)',
        'img' => 'https://images.unsplash.com/photo-1593105544559-ecb03bf76f82?auto=format&fit=crop&w=400&q=80',
        'blend' => true
    ]
];
?>

<style>
/* Prominent High-Clarity Video Hero with Scroll Parallax Animation */
.market-detail-hero {
    position: relative;
    overflow: hidden;
    min-height: 460px;
    display: flex;
    align-items: center;
    background: #0e180c;
    color: #ffffff;
    padding: 65px 0 55px;
}
.market-hero-video-wrap {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none;
    z-index: 1;
}
.market-hero-video {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%;
    width: 100%;
    height: 100%;
    transform: translate(-50%, -50%);
    object-fit: cover;
    filter: brightness(0.88) contrast(1.12);
    transition: transform 0.1s linear;
}
.market-hero-video-overlay {
    position: absolute;
    inset: 0;
    z-index: 2;
    background: linear-gradient(135deg, rgba(14, 24, 10, 0.65) 0%, rgba(28, 46, 18, 0.45) 50%, rgba(10, 18, 7, 0.72) 100%);
}

.market-back-link {
    color: #e5f4d8;
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 14px;
    transition: color 0.2s ease;
}
.market-back-link:hover {
    color: #ffffff;
}

/* 4 3D Produce Cards (No Box Borders) */
.market-fruit-card {
    border-radius: 0;
    padding: 36px 28px 24px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    height: 290px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.35s ease, box-shadow 0.35s ease;
}
.market-fruit-card:hover {
    transform: translateY(-4px);
    z-index: 2;
    box-shadow: 0 14px 34px rgba(0,0,0,0.25);
}
.fruit-card-title {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 2.2rem;
    font-weight: 600;
    margin-bottom: 6px;
}
.fruit-card-desc {
    font-size: 0.82rem;
    font-style: italic;
    line-height: 1.45;
    opacity: 0.95;
    margin-bottom: 12px;
    max-width: 170px;
}
.fruit-card-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(255, 255, 255, 0.28);
    border: 1px solid rgba(255, 255, 255, 0.6);
    color: #ffffff !important;
    font-size: 0.76rem;
    font-weight: 700;
    padding: 5px 16px;
    border-radius: 50px;
    text-decoration: none;
    width: fit-content;
    transition: all 0.2s ease;
}
.fruit-card-btn:hover {
    background: #ffffff;
    color: #17240f !important;
}

.market-fruit-thumb {
    position: absolute;
    bottom: -8px;
    right: -8px;
    width: 190px;
    height: 190px;
    pointer-events: none;
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
}
.market-fruit-thumb img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 16px 28px rgba(0,0,0,0.45));
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.market-fruit-thumb.blend-mode img {
    mix-blend-mode: multiply;
    border-radius: 50%;
}
.market-fruit-card:hover .market-fruit-thumb img {
    transform: scale(1.12) rotate(3deg);
}

/* "We Grow Best Food" Spotlight */
.best-food-section {
    background: #ffffff;
    padding: 75px 0;
}
.best-food-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(2.2rem, 3.6vw, 3rem);
    font-weight: 700;
    color: #648430;
    margin-bottom: 8px;
}
.best-food-sub {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    color: #7f9964;
    font-size: 1.05rem;
    max-width: 680px;
    margin: 0 auto 55px;
}

.feature-item-col {
    display: flex;
    flex-direction: column;
    gap: 34px;
}
.feature-bubble {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.feature-bubble.reverse {
    flex-direction: row-reverse;
    text-align: right;
}
.feature-bubble-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 50%;
    background: #edf7e4;
    color: #4a6a26;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    box-shadow: 0 4px 14px rgba(74, 106, 38, 0.12);
}
.feature-bubble-title {
    font-weight: 700;
    font-size: 1.15rem;
    color: #17240f;
    margin-bottom: 3px;
}
.feature-bubble-desc {
    font-size: 0.84rem;
    color: #6b7d63;
    line-height: 1.48;
    margin-bottom: 0;
}

.center-basket-wrap {
    position: relative;
    text-align: center;
}
.center-basket-tag {
    background: #9ab84a;
    color: #ffffff;
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 1.15rem;
    font-weight: 600;
    padding: 18px 24px 80px;
    border-radius: 20px 20px 0 0;
    display: inline-block;
    width: 84%;
    margin-bottom: -70px;
    box-shadow: 0 10px 28px rgba(74, 95, 49, 0.18);
}
.center-basket-img {
    position: relative;
    z-index: 2;
    max-width: 100%;
    height: auto;
    filter: drop-shadow(0 20px 40px rgba(36, 54, 25, 0.32));
    border-radius: 24px;
}

/* "Our Grown Up Plants" Products Grid */
.grown-plants-section {
    background: #f7faf4;
    padding: 70px 0;
}
.plant-product-card {
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 20px;
    padding: 24px 18px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.plant-product-card:hover {
    transform: translateY(-6px);
    border-color: #4a5f31;
    box-shadow: 0 16px 36px rgba(45, 65, 28, 0.12);
}
.plant-product-img {
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
}
.plant-product-img img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.plant-product-card:hover .plant-product-img img {
    transform: scale(1.08);
}
.plant-product-title {
    font-weight: 700;
    font-size: 1.05rem;
    color: #17240f;
    margin-bottom: 4px;
}
.plant-product-price {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.25rem;
    font-weight: 700;
    color: #3e5a25;
    margin-bottom: 12px;
}
.plant-add-btn {
    background: #d4f28f;
    color: #15220d !important;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 7px 18px;
    border-radius: 50px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: none;
    width: 100%;
    transition: all 0.2s ease;
}
.plant-add-btn:hover {
    background: #15220d;
    color: #ffffff !important;
}

/* Farmers Present at Market Animated Cards */
.stall-animated-card {
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 8px 24px rgba(45, 65, 28, 0.06);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease, border-color 0.35s ease;
}
.stall-animated-card:hover {
    transform: translateY(-5px) scale(1.01);
    border-color: #4a5f31;
    box-shadow: 0 16px 36px rgba(45, 65, 28, 0.15);
}

.map-card-wrapper {
    background: #f4f8ee;
    border: 1.5px solid #dce8cf;
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 10px 30px rgba(45, 65, 28, 0.08);
}
</style>

<!-- ============================================================
     HERO SECTION: MARKET PROMINENT VIDEO HERO
     ============================================================ -->
<section class="market-detail-hero">
    <div class="market-hero-video-wrap">
        <video autoplay muted loop playsinline class="market-hero-video" id="marketHeroVideo">
            <source src="<?php echo ML_asset('Uploads/market.mp4'); ?>" type="video/mp4">
        </video>
        <div class="market-hero-video-overlay"></div>
    </div>

    <div class="container position-relative py-4" style="z-index: 3;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <a href="<?php echo ML_asset('markets'); ?>" class="market-back-link">
                <i class="fa-solid fa-arrow-left"></i> All Farmers Markets
            </a>

            <!-- Market Quick Switch Filter Form (Styled like farmers.php) -->
            <form method="get" action="<?php echo ML_asset('market'); ?>" class="d-flex align-items-center gap-2">
                <select name="id" class="form-select rounded-pill px-3 py-2 shadow-sm border-success-subtle fw-semibold" style="max-width: 260px;" onchange="this.form.submit()">
                    <option value="" disabled>Switch Market Hub</option>
                    <?php foreach ($allMarkets as $am): ?>
                        <option value="<?php echo $am['market_id']; ?>" <?php echo (int)$am['market_id'] === $marketId ? 'selected' : ''; ?>>
                            <?php echo sanitize_output($am['market_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mt-2">
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold mb-2">
                    <i class="fa-solid fa-store me-1"></i> Verified Community Pickup Hub
                </span>
                <h1 class="display-5 fw-bold mb-2 text-white" style="font-family: 'Fraunces', Georgia, serif;"><?php echo sanitize_output($market['market_name']); ?></h1>
                <p class="mb-0 text-white-50 fs-6">
                    <i class="fa-solid fa-location-dot text-danger me-1"></i> <?php echo sanitize_output($market['address']); ?>
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-light text-dark px-3 py-2 rounded-pill fw-semibold shadow-sm">
                    <i class="fa-regular fa-calendar-days text-success me-1"></i> <?php echo marketPickups($market); ?>
                </span>
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">
                    <i class="fa-regular fa-clock me-1"></i> <?php echo date('g:i A', strtotime($market['opening_time'])); ?> &ndash; <?php echo date('g:i A', strtotime($market['closing_time'])); ?>
                </span>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 1: 4 COLORED 3D FRUIT CARDS
     ============================================================ -->
<div class="container-fluid p-0">
    <div class="row g-0">
        <?php foreach ($fruitCards as $fc): ?>
            <div class="col-6 col-md-3">
                <div class="market-fruit-card" style="background: <?php echo $fc['bg']; ?>;">
                    <div>
                        <h3 class="fruit-card-title"><?php echo $fc['title']; ?></h3>
                        <p class="fruit-card-desc"><?php echo $fc['desc']; ?></p>
                        <a href="#marketProducts" class="fruit-card-btn">
                            <span>Read More</span> <i class="fa-solid fa-angle-right"></i>
                        </a>
                    </div>
                    
                    <div class="market-fruit-thumb <?= $fc['blend'] ? 'blend-mode' : '' ?>">
                        <img src="<?php echo $fc['img']; ?>" alt="<?php echo $fc['title']; ?>">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============================================================
     SECTION 2: "WE GROW BEST FOOD" SPOTLIGHT (Woven Organic Harvest Basket)
     ============================================================ -->
<section class="best-food-section">
    <div class="container">
        <div class="text-center">
            <h2 class="best-food-title">We Grow Best Food <i class="fa-solid fa-leaf text-success" style="font-size: 1.8rem;"></i></h2>
            <p class="best-food-sub">
                Every stall at <?php echo sanitize_output($market['market_name']); ?> delivers naturally grown harvest directly from certified regional agriculture.
            </p>
        </div>

        <div class="row align-items-center g-4">
            
            <!-- Left 3 Features -->
            <div class="col-lg-3 col-md-6 order-2 order-lg-1 feature-item-col">
                <div class="feature-bubble">
                    <div class="feature-bubble-icon"><i class="fa-solid fa-leaf"></i></div>
                    <div>
                        <h5 class="feature-bubble-title">Fresh</h5>
                        <p class="feature-bubble-desc">Harvested at early sunrise and delivered straight to market stalls without cold-storage delays.</p>
                    </div>
                </div>

                <div class="feature-bubble">
                    <div class="feature-bubble-icon"><i class="fa-solid fa-heart"></i></div>
                    <div>
                        <h5 class="feature-bubble-title">Healthy</h5>
                        <p class="feature-bubble-desc">100% natural, nutrient-dense fruits and vegetables rich in essential vitamins and antioxidants.</p>
                    </div>
                </div>

                <div class="feature-bubble">
                    <div class="feature-bubble-icon"><i class="fa-solid fa-wheat-awn"></i></div>
                    <div>
                        <h5 class="feature-bubble-title">Eco</h5>
                        <p class="feature-bubble-desc">Sustainable ecological farming practices preserving soil biodiversity and saving water.</p>
                    </div>
                </div>
            </div>

            <!-- Center Basket Spotlight -->
            <div class="col-lg-6 col-md-12 order-1 order-lg-2 text-center my-4 my-lg-0">
                <div class="center-basket-wrap mx-auto" style="max-width: 500px;">
                    <div class="center-basket-tag">
                        Personal recommendation of Regional Organic Growers
                        <div class="center-spotlight-sign" style="font-family: 'Caveat', cursive; font-size: 1.6rem; color: #fff; margin-top: 4px;">V. Mark &#10004;</div>
                    </div>
                    <img src="https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=700&q=85" alt="Fresh Harvest Basket" class="center-basket-img">
                </div>
            </div>

            <!-- Right 3 Features -->
            <div class="col-lg-3 col-md-6 order-3 order-lg-3 feature-item-col">
                <div class="feature-bubble">
                    <div class="feature-bubble-icon"><i class="fa-solid fa-thumbs-up"></i></div>
                    <div>
                        <h5 class="feature-bubble-title">Tasty</h5>
                        <p class="feature-bubble-desc">Naturally ripened on the vine for deep, authentic flavor that supermarket produce lacks.</p>
                    </div>
                </div>

                <div class="feature-bubble">
                    <div class="feature-bubble-icon"><i class="fa-solid fa-apple-whole"></i></div>
                    <div>
                        <h5 class="feature-bubble-title">Yummy</h5>
                        <p class="feature-bubble-desc">Delicious seasonal fruits and heirloom veggies hand-picked daily from family farm stalls.</p>
                    </div>
                </div>

                <div class="feature-bubble">
                    <div class="feature-bubble-icon"><i class="fa-solid fa-award"></i></div>
                    <div>
                        <h5 class="feature-bubble-title">Premium</h5>
                        <p class="feature-bubble-desc">Hand-graded quality directly from certified growers with verified producer standards.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 3: "OUR GROWN UP PLANTS" PRODUCTS GRID
     ============================================================ -->
<section class="grown-plants-section" id="marketProducts">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="best-food-title">Our Grown Up Plants <i class="fa-solid fa-leaf text-success" style="font-size: 1.8rem;"></i></h2>
            <p class="best-food-sub">We grow only the best fruits and vegetables for every family!</p>
        </div>

        <?php if (empty($products)): ?>
            <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
                <i class="fa-solid fa-basket-shopping text-muted fs-1 mb-3"></i>
                <h5 class="fw-bold">No products currently listed for this market</h5>
                <p class="text-muted mb-0">Please check our other community market hubs or check back soon.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $p): 
                    $prodImgUrl = prodImgSrc($p);
if ($prodImgUrl === '') $prodImgUrl = 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=400&q=80';
                ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="plant-product-card">
                            <div>
                                <a href="<?php echo ML_asset('product') . '?id=' . $p['product_id']; ?>" class="text-decoration-none">
                                    <div class="plant-product-img position-relative">
                                        <img src="<?php echo $prodImgUrl; ?>" alt="<?php echo sanitize_output($p['name']); ?>">
                                        <?php if ((int)$p['stock_quantity'] <= 10): ?>
                                            <span class="position-absolute badge bg-warning text-dark top-0 start-0 m-2 shadow-sm">Low Stock &middot; <?php echo (int)$p['stock_quantity']; ?> left</span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="plant-product-title"><?php echo sanitize_output($p['name']); ?></h5>
                                </a>
                                
                                <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small mb-2">
                                    <i class="fa-solid fa-store me-1"></i><?php echo sanitize_output($p['stall_name']); ?>
                                </span>

                                <div class="text-warning small mb-2">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star-half-stroke"></i>
                                </div>

                                <div class="plant-product-price">
                                    <?php echo money($p['price']); ?> <span class="text-muted fs-6 fw-normal">/ <?php echo sanitize_output($p['unit']); ?></span>
                                </div>
                            </div>

                            <button type="button" class="plant-add-btn" data-add-cart="<?php echo $p['product_id']; ?>">
                                <i class="fa-solid fa-cart-plus"></i> Add To Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- CTA BANNER -->
        <div class="market-cta-landscape" style="background: linear-gradient(135deg, rgba(26, 42, 17, 0.88) 0%, rgba(53, 80, 35, 0.82) 100%), url('<?php echo ML_asset('Uploads/img/crop.webp'); ?>') center/cover; padding: 70px 20px; border-radius: 28px; margin: 40px auto; box-shadow: 0 16px 40px rgba(35, 55, 23, 0.2);">
            <div class="market-cta-floating-card" style="background: #ffffff; border-radius: 20px; padding: 36px 30px; max-width: 580px; margin: 0 auto; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.25);">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold mb-2">
                    <i class="fa-solid fa-seedling me-1"></i> Fresh From Our Fields
                </span>
                <h3 class="fw-bold text-dark mb-2" style="font-family: 'Fraunces', Georgia, serif;">We Grow Best Food</h3>
                <p class="text-muted small mb-4">
                    Farm, Garden and Agriculture &mdash; Reserve your fresh weekly basket online and collect directly at the market stall.
                </p>
                <a href="#marketStalls" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow">
                    <i class="fa-solid fa-store me-1"></i> View Market Stalls
                </a>
            </div>
        </div>

    </div>
</section>

<!-- ============================================================
     SECTION 4: FARMERS PRESENT AT MARKET (ANIMATED) & LOCATION MAP
     ============================================================ -->
<div class="py-5" id="marketStalls" style="background: #ffffff;">
    <div class="container py-2">
        <div class="row g-5 align-items-stretch">
            
            <!-- Left: Farmers List with Animated Cards -->
            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="section-kicker"><i class="fa-solid fa-people-roof me-1 text-leaf"></i> Verified Stalls</span>
                        <h3 class="fw-bold text-dark mt-1 mb-0" style="font-family: 'Fraunces', Georgia, serif;">Farmers Present At Market</h3>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                        <?php echo count($farmers); ?> Approved Stalls
                    </span>
                </div>

                <?php if (empty($farmers)): ?>
                    <div class="card border-0 rounded-4 shadow-sm p-4 text-center bg-light">
                        <i class="fa-solid fa-users-slash text-muted fs-2 mb-2"></i>
                        <h6 class="fw-bold">No active farmers listed yet for this hub</h6>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($farmers as $f): 
                            $fUrl = ML_asset('farmer') . '?id=' . $f['farmer_id'];
                        ?>
                            <div class="stall-animated-card">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 54px; height: 54px; background: linear-gradient(135deg, #4a5f31, #a0bc79); font-size: 1.4rem;">
                                            <i class="fa-solid fa-tractor"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1 text-dark">
                                                <a href="<?php echo $fUrl; ?>" class="text-decoration-none text-dark">
                                                    <?php echo sanitize_output($f['stall_name']); ?>
                                                </a>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-1 small">
                                                    Stall #<?php echo sanitize_output($f['stall_number'] ?? '1'); ?>
                                                </span>
                                            </h5>
                                            <span class="text-muted small d-block"><?php echo sanitize_output($f['description'] ?? 'Verified local producer offering fresh morning harvests.'); ?></span>
                                            
                                            <!-- Animated Rating Badge & Orders -->
                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                <div class="text-warning small" style="letter-spacing: 1px;">
                                                    <?php echo starsHtml($f['avg_rating'], 14); ?>
                                                </div>
                                                <span class="badge bg-light text-dark border rounded-pill px-2 py-0 small">
                                                    <i class="fa-solid fa-bag-shopping text-success me-1"></i><?php echo (int)$f['total_orders']; ?> orders completed
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <a href="<?php echo $fUrl; ?>" class="btn btn-sm btn-success rounded-pill px-4 py-2 fw-bold text-white shadow-sm" style="background:#4a5f31; border: none;">
                                            <span>View Stall</span> <i class="fa-solid fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Interactive Location Map -->
            <div class="col-lg-5">
                <div class="map-card-wrapper h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark mb-2" style="font-family: 'Fraunces', Georgia, serif;">
                            <i class="fa-solid fa-location-dot text-danger me-2"></i> Stall Location Map
                        </h4>
                        <p class="text-muted small mb-3">Collect your fresh order directly at the market stall during scheduled hours.</p>
                        
                        <iframe class="map-frame mb-3" src="<?php echo $mapUrl; ?>" loading="lazy" style="height: 340px; border-radius: 20px; width: 100%; border: 1.5px solid #dce8cf;"></iframe>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top border-success-subtle">
                        <span class="text-muted small">
                            <i class="fa-solid fa-hand-holding-dollar text-success me-1"></i> Cash paid at stall pickup
                        </span>
                        <a href="<?php echo $dirUrl; ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-2 fw-bold">
                            <i class="fa-solid fa-diamond-turn-right me-1"></i> Open in Maps
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Parallax Scroll Effect for Video Hero -->
<script>
(function() {
    window.addEventListener('scroll', function() {
        var sc = window.scrollY;
        var videoEl = document.getElementById('marketHeroVideo');
        if (videoEl && sc < 600) {
            videoEl.style.transform = 'translate(-50%, calc(-50% + ' + (sc * 0.3) + 'px))';
        }
    }, { passive: true });
})();
</script>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>