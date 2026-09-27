<?php
$pageTitle = 'Product - MarketLink';
$pid = (int)($_GET['id'] ?? 0);
$product = selectData($pdo, "SELECT p.*, f.*, f.stall_name, f.farmer_id AS pfid, c.category_name
    FROM products AS p
    INNER JOIN farmers AS f ON f.farmer_id = p.farmer_id AND f.approval_status = 'approved'
    INNER JOIN users AS u ON u.id = f.user_id AND u.status = 'active'
    LEFT JOIN categories AS c ON c.category_id = p.category_id
    WHERE p.product_id = ?", [$pid]);
if (empty($product)) {
    set_flash('error', 'Product not found.');
    redirect('products');
}
$p = $product[0];
$available = $p['is_available'] && !$p['is_sold_out'] && (int)$p['stock_quantity'] > 0;
include __DIR__ . '/../../../public/components/header.php';

$similar = selectData($pdo, "SELECT p.*, f.stall_name, c.category_name FROM products AS p
    INNER JOIN farmers AS f ON f.farmer_id = p.farmer_id
    LEFT JOIN categories AS c ON c.category_id = p.category_id
    WHERE p.product_id != ? AND p.is_available = 1 AND p.is_sold_out = 0 AND p.stock_quantity > 0
      AND (p.category_id = ? OR p.farmer_id = ?) ORDER BY p.avg_rating DESC, p.price ASC LIMIT 4",
    [$pid, $p['category_id'], $p['farmer_id']]);

$reviews = selectData($pdo, "SELECT r.rating, r.comment, r.review_date, c.full_name FROM reviews AS r
    INNER JOIN customers AS c ON c.customer_id = r.customer_id
    WHERE r.product_id = ? AND r.comment IS NOT NULL AND r.comment != '' ORDER BY r.review_date DESC LIMIT 8", [$pid]);

$isLoggedCustomer = !empty($_SESSION['loggedIn']) && ($_SESSION['role'] ?? '') == 'customer';
$isFav = false;
if ($isLoggedCustomer) {
    $crows = selectData($pdo, "SELECT customer_id FROM customers WHERE user_id = ?", [$_SESSION['user_id']]);
    if (!empty($crows)) $isFav = isFav($pdo, $crows[0]['customer_id'], null, $pid);
}
?>

<style>
    /* Palette pulled from the products listing page so a product and the
       grid it was clicked from read as the same page. The first six are
       the existing theme tokens; the rest are the cream-sage wash and
       accent stops that page uses, plus the orange from the hero. */
    :root {
        --theme-lightest: #f4f8ee;
        --theme-lighter: #e4eed4;
        --theme-light: #d4e4be;
        --theme-primary: #4a5f31;
        --theme-accent: #a0bc79;
        --theme-dark: #24362a;

        --ml-cream: #f3f0e0;
        --ml-cream-soft: #f6f3e6;
        --ml-sage: #b0c78f;
        --ml-sage-soft: #eef2e2;
        --ml-line: #e8ecdd;
        --ml-line-strong: #d4d6bc;
        --ml-ink: #2b3d1f;
        --ml-ink-soft: #40543a;
        --ml-orange: #f39c12;
    }

    /* The cream wash is scoped to the page wrapper rather than set on
       body, so the sticky header and the footer keep their own
       backgrounds instead of inheriting this tint. */
    .prod-page {
        background:
            radial-gradient(90% 60% at 85% 0%, rgba(243, 156, 18, 0.10) 0%, transparent 58%),
            radial-gradient(80% 55% at 5% 8%, rgba(176, 199, 143, 0.26) 0%, transparent 60%),
            linear-gradient(168deg, #fdfcf6 0%, var(--ml-cream) 38%, var(--ml-sage-soft) 100%);
        min-height: 40vh;
    }

    .prod-detail-wrapper {
        padding: 40px 0;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* The image column is deliberately narrower than the details column. At
       1fr 1fr the photo took half a 1140px container and pushed every
       product's copy below the fold, so the picture is capped in width and
       the spare room goes to the text. */
    .prod-grid {
        display: grid;
        grid-template-columns: minmax(0, 0.78fr) minmax(0, 1.22fr);
        gap: 56px;
        align-items: start;
        margin-bottom: 60px;
    }

    /* Softer than the old 45px/15px leaf split, so the hero image sits in
       the same rounded language as the grid cards it was opened from. */
    .prod-img-box {
        position: relative;
        width: 100%;
        max-width: 360px;
        margin: 0 auto;
        border-radius: 24px;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--ml-line);
        box-shadow: 0 12px 28px rgba(45, 65, 28, 0.08);
        aspect-ratio: 4/5;
    }

    .prod-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .prod-img-box:hover img {
        transform: scale(1.05);
    }

    .cat-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--theme-lighter);
        color: var(--theme-primary);
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .prod-title {
        font-family: 'Fraunces', Georgia, serif;
        font-size: clamp(2rem, 3vw, 2.8rem);
        font-weight: 700;
        color: var(--ml-ink);
        line-height: 1.1;
        margin-bottom: 12px;
    }

    .prod-farmer {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        color: var(--theme-dark);
        margin-bottom: 24px;
    }

    .prod-farmer i {
        color: var(--theme-accent);
    }

    .prod-farmer a {
        color: var(--theme-primary);
        font-weight: 600;
        text-decoration: none;
    }

    .prod-desc {
        font-size: 1.05rem;
        line-height: 1.7;
        color: #556b4d;
        margin-bottom: 30px;
    }

    .prod-price-box {
        background: #fff;
        border: 1px solid var(--ml-line);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 25px rgba(45, 65, 28, 0.06);
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .price-value {
        font-family: 'Fraunces', Georgia, serif;
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--ml-ink);
    }

    .price-unit {
        font-size: 1rem;
        color: #728267;
        font-weight: 500;
    }

    .stock-pill {
        display: inline-block;
        background: var(--theme-lighter);
        color: var(--theme-primary);
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .cart-actions {
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }

    .qty-selector {
        display: flex;
        align-items: center;
        background: #fff;
        border: 2px solid var(--theme-lighter);
        border-radius: 50px;
        overflow: hidden;
        height: 54px;
    }

    .qty-btn {
        background: transparent;
        border: none;
        color: var(--theme-primary);
        font-size: 1.2rem;
        width: 44px;
        height: 100%;
        cursor: pointer;
        transition: background 0.2s;
    }

    .qty-btn:hover {
        background: var(--theme-lighter);
    }

    .qty-input {
        width: 50px;
        text-align: center;
        border: none;
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--theme-dark);
        background: transparent;
        outline: none;
        pointer-events: none;
    }

    .add-btn {
        background: var(--theme-primary);
        color: #fff;
        border: none;
        border-radius: 50px;
        height: 54px;
        padding: 0 32px;
        font-size: 1.05rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 8px 20px rgba(74, 95, 49, 0.2);
        flex: 1;
        justify-content: center;
    }

    .add-btn:hover {
        background: #394a26;
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(74, 95, 49, 0.3);
    }

    .fav-btn {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        border: 2px solid var(--theme-lighter);
        background: #fff;
        color: var(--theme-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .fav-btn:hover, .fav-btn.active {
        background: var(--theme-lighter);
        border-color: var(--theme-accent);
    }

    .info-box {
        margin-top: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: var(--theme-lightest);
        border: 1px solid var(--theme-light);
        border-radius: 12px;
        font-size: 0.9rem;
        color: var(--theme-dark);
    }

    .info-box i {
        font-size: 1.4rem;
        color: var(--theme-accent);
    }

    /* Similar-product cards now use the exact treatment of the listing
       page's .ml-card (same radius, hairline border, lift distance and
       shadow) instead of the old asymmetric leaf shape, so moving
       between the grid and a product does not feel like two designs. */
    .similar-section {
        margin-top: 60px;
        padding-top: 60px;
        border-top: 1px solid var(--ml-line);
    }

    .similar-title {
        font-family: 'Fraunces', Georgia, serif;
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--ml-ink);
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ml-leaf-card {
        position: relative;
        background: #ffffff;
        border: 1px solid var(--ml-line);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 22px rgba(45, 65, 28, 0.07);
        transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        text-decoration: none;
        color: inherit;
    }

    .ml-leaf-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(45, 65, 28, 0.13);
        border-color: var(--ml-line-strong);
    }

    /* Keyboard users get the same affordance a mouse hover gives. */
    .ml-leaf-card:focus-within {
        outline: 2px solid var(--theme-primary);
        outline-offset: 3px;
    }

    .ml-leaf-img {
        aspect-ratio: 4/3;
        overflow: hidden;
    }

    .ml-leaf-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .ml-leaf-card:hover .ml-leaf-img img {
        transform: scale(1.08);
    }

    .ml-leaf-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .ml-leaf-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--theme-primary);
        margin-bottom: 4px;
    }

    .ml-leaf-stall {
        font-size: 0.85rem;
        color: #728267;
        margin-bottom: 14px;
    }

    .ml-leaf-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ml-leaf-price {
        font-weight: 700;
        color: var(--theme-primary);
        font-size: 1.1rem;
    }

    .ml-leaf-add {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--theme-lighter);
        color: var(--theme-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
    }

    .ml-leaf-card:hover .ml-leaf-add {
        background: var(--theme-primary);
        color: #fff;
    }

    @media (max-width: 991px) {
        .prod-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        /* Stacked, the photo is the full focus of the screen, but it still
           must not out-size the desktop box or the page jumps on resize. */
        .prod-img-box {
            max-width: 340px;
        }
    }

    /* The price row and the action row both sit side by side above this
       width, so they need to stack once there is not enough room. */
    @media (max-width: 575.98px) {
        .prod-detail-wrapper { padding: 26px 0; }
        .prod-img-box {
            max-width: 260px;
            aspect-ratio: 1/1;
        }
        .prod-title { font-size: clamp(1.7rem, 7vw, 2.1rem); }
        .prod-price-box {
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
            padding: 20px;
        }
        .price-value { font-size: 1.85rem; }
        .add-btn { padding: 0 20px; }
        .similar-section {
            margin-top: 40px;
            padding-top: 40px;
        }
        .similar-title { font-size: 1.45rem; }
    }
</style>

<div class="prod-page">
<div class="container-fluid px-4 py-3">
    <a href="<?php echo ML_asset('products'); ?>" class="btn btn-link text-decoration-none" style="color: var(--theme-primary); font-weight: 500;">
        <i class="bi bi-arrow-left me-2"></i>Back to Market
    </a>
</div>

<div class="container prod-detail-wrapper">
    <div class="prod-grid">
        <!-- Left Side: Image -->
        <div class="prod-img-wrapper">
            <div class="prod-img-box">
                <?php echo prodImg($p); ?>
            </div>
        </div>

        <!-- Right Side: Details -->
        <div class="prod-info-wrapper">
            <div class="cat-badge">
                <?php echo catIcon($p['category_name']); ?>
                <?php echo sanitize_output($p['category_name']); ?>
            </div>
            
            <h1 class="prod-title"><?php echo sanitize_output($p['name']); ?></h1>
            
            <div class="mb-3 d-flex align-items-center gap-3">
                <div style="color: #f1c40f; font-size: 1.1rem;">
                    <?php echo starsHtml($p['avg_rating'], 18); ?>
                </div>
            </div>

            <div class="prod-farmer">
                <i class="bi bi-shop"></i> Farm Stall: 
                <a href="<?php echo ML_asset('farmer') . '?id=' . $p['farmer_id']; ?>">
                    <?php echo sanitize_output($p['stall_name']); ?>
                </a>
            </div>

            <p class="prod-desc">
                <?php echo sanitize_output($p['description'] ?: 'Fresh, locally sourced produce harvested directly from a verified community farmer. Enjoy farm-to-table quality while supporting your local market network.'); ?>
            </p>

            <div class="prod-price-box">
                <div>
                    <div class="price-value"><?php echo money($p['price']); ?></div>
                    <div class="price-unit">per <?php echo sanitize_output($p['unit']); ?></div>
                </div>
                <div>
                    <?php if ($available): ?>
                        <span class="stock-pill"><i class="bi bi-check-circle-fill me-1"></i> In Stock (<?php echo (int)$p['stock_quantity']; ?>)</span>
                    <?php else: ?>
                        <span class="stock-pill" style="color:#d9534f; background:#f9e4e4;"><i class="bi bi-x-circle-fill me-1"></i> Out of Stock</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($available): ?>
                <form class="cart-form cart-actions">
                    <div class="qty-selector">
                        <button type="button" class="qty-btn" data-qty-btn="dec" data-add-decor="-">-</button>
                        <input type="number" class="qty-input" data-qty-input value="1" min="1" max="<?php echo (int)$p['stock_quantity']; ?>" readonly>
                        <button type="button" class="qty-btn" data-qty-btn="inc" data-add-decor="+">+</button>
                    </div>
                    
                    <button type="button" class="add-btn" data-add-cart="<?php echo $pid; ?>">
                        <i class="bi bi-cart-plus-fill"></i> Add to Basket
                    </button>

                    <?php if ($isLoggedCustomer): ?>
                        <button type="button" class="fav-btn <?php echo $isFav ? 'active' : ''; ?>" data-fav-btn data-fav-type="product" data-fav-id="<?php echo $pid; ?>" title="<?php echo $isFav ? 'Saved' : 'Save for later'; ?>">
                            <i class="bi <?php echo $isFav ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                        </button>
                    <?php endif; ?>
                </form>

                <div class="info-box">
                    <i class="bi bi-shield-check"></i>
                    <div>
                        <strong>Market Guarantee:</strong> Payment is settled securely when you pick up your order. Free cancellations up to 24 hours before pickup.
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" style="border-radius: 12px; border: none; background: #fff3cd; color: #856404;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> This product is currently unavailable. Check out similar items below.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Similar Products -->
    <?php if (!empty($similar)): ?>
        <div class="similar-section">
            <h3 class="similar-title"><i class="bi bi-stars"></i> You may also like</h3>
            <div class="row g-4 mt-2">
                <?php foreach ($similar as $s): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <a href="<?php echo ML_asset('product') . '?id=' . $s['product_id']; ?>" class="ml-leaf-card">
                            <div class="ml-leaf-img">
                                <?php echo prodImg($s); ?>
                            </div>
                            <div class="ml-leaf-body">
                                <div>
                                    <h4 class="ml-leaf-title"><?php echo sanitize_output($s['name']); ?></h4>
                                    <div class="ml-leaf-stall"><i class="bi bi-shop me-1"></i><?php echo sanitize_output($s['stall_name']); ?></div>
                                </div>
                                <div class="ml-leaf-foot mt-3">
                                    <div class="ml-leaf-price">
                                        <?php echo money($s['price']); ?> <span style="font-size:0.8rem; font-weight:normal; color:#728267;">/ <?php echo sanitize_output($s['unit']); ?></span>
                                    </div>
                                    <button type="button" class="ml-leaf-add" data-add-cart="<?php echo $s['product_id']; ?>" onclick="event.preventDefault(); event.stopPropagation();">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Reviews -->
    <?php if (!empty($reviews)): ?>
        <div class="similar-section" style="margin-top: 40px; padding-top: 40px;">
            <h3 class="similar-title"><i class="bi bi-chat-quote"></i> Customer Reviews</h3>
            <div class="row g-4 mt-2">
                <?php foreach ($reviews as $rv): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="p-4" style="background:#fff; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); height: 100%;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold" style="color:var(--theme-primary);"><i class="bi bi-person-circle me-2"></i><?php echo sanitize_output($rv['full_name']); ?></span>
                                <span style="font-size:0.8rem; color:#888;"><?php echo timeAgo($rv['review_date']); ?></span>
                            </div>
                            <div style="color: #f1c40f; font-size: 0.9rem; margin-bottom: 10px;">
                                <?php echo starsHtml($rv['rating'], 14); ?>
                            </div>
                            <p style="color: #555; font-size: 0.95rem; margin: 0; line-height: 1.6;">
                                "<?php echo sanitize_output($rv['comment']); ?>"
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</div><!-- /.prod-page -->

<script>
(function () {
    if (!window.mlRecent) return;
    window.mlRecent.add({
        id: <?php echo (int)$pid; ?>,
        name: <?php echo json_encode($p['name'], JSON_UNESCAPED_UNICODE); ?>,
        price: <?php echo (float)$p['price']; ?>,
        unit: <?php echo json_encode($p['unit'] ?? '', JSON_UNESCAPED_UNICODE); ?>,
        img: <?php echo json_encode(prodImgSrc($p)); ?>
    });
})();
</script>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>