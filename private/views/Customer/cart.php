<?php
$pageTitle = 'Your Basket - MarketLink';
$page = 'cart';
include __DIR__ . '/../../../public/components/header.php';

$items = cartItems($pdo);
$total = cartTotal($pdo);
$loggedIn = !empty($_SESSION['loggedIn']);
?>

<style>
/* Basket Page Theme Redesign */
/* Cream-sage wash, same recipe as the About + Contact heroes, so the basket
   reads as part of the light MarketLink theme instead of a dark photo band. */
.cart-theme-hero {
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(115% 85% at 80% 12%, rgba(243, 156, 18, 0.13) 0%, transparent 58%),
        radial-gradient(90% 70% at 8% 92%, rgba(176, 199, 143, 0.30) 0%, transparent 62%),
        linear-gradient(152deg, #fdfcf6 0%, #f6f3e6 34%, #eef2e2 68%, #e4ecd8 100%);
    color: #2d411c;
    padding: 55px 0 45px;
    border-bottom: 1px solid #dce8cf;
}
.cart-theme-hero::after {
    content: "";
    position: absolute;
    width: 340px;
    height: 340px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(176, 199, 143, 0.28) 0%, transparent 68%);
    bottom: -150px;
    right: -70px;
    pointer-events: none;
}
.cart-theme-hero > * {
    position: relative;
    z-index: 1;
}
.cart-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.72);
    border: 1px solid #c7dfb4;
    color: #4a5f31;
    padding: 5px 18px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
}

.cart-main-section {
    background: #f8faf6;
    min-height: 550px;
    padding: 55px 0 75px;
}

.cart-items-card {
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 10px 30px rgba(45, 65, 28, 0.06);
}

.cart-item-row {
    padding: 18px 0;
    border-bottom: 1px dashed #dce8cf;
    transition: background-color 0.2s ease;
}
.cart-item-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.cart-item-row:first-child {
    padding-top: 0;
}

.cart-prod-thumb {
    width: 72px;
    height: 72px;
    min-width: 72px;
    border-radius: 16px;
    overflow: hidden;
    background: #f3f7ee;
    border: 1.5px solid #e1edd8;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cart-prod-thumb img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.cart-qty-pill {
    display: inline-flex;
    align-items: center;
    background: #f4f8ee;
    border: 1.5px solid #c7dfb4;
    border-radius: 50px;
    padding: 3px 6px;
}
.cart-qty-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: #ffffff;
    color: #243815;
    font-weight: 700;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: all 0.2s ease;
    cursor: pointer;
}
.cart-qty-btn:hover {
    background: #4a5f31;
    color: #ffffff;
}
.cart-qty-val {
    width: 44px;
    text-align: center;
    font-weight: 700;
    font-size: 0.95rem;
    color: #17240f;
    border: none;
    background: transparent;
    outline: none;
}

.cart-summary-card {
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 24px;
    padding: 30px;
    box-shadow: 0 12px 35px rgba(45, 65, 28, 0.08);
}
.cart-summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px dashed #e1edd8;
    font-size: 0.95rem;
}
.cart-summary-row:last-of-type {
    border-bottom: none;
}
.cart-summary-total {
    border-top: 2px solid #dce8cf;
    padding-top: 16px;
    margin-top: 10px;
}

.cart-checkout-btn {
    background: #4a5f31;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 1.05rem;
    padding: 14px 28px;
    border-radius: 50px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    width: 100%;
    box-shadow: 0 8px 24px rgba(74, 95, 49, 0.28);
    transition: all 0.25s ease;
}
.cart-checkout-btn:hover {
    background: #2b3d1b;
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(74, 95, 49, 0.4);
}

.cart-empty-box {
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 24px;
    padding: 65px 30px;
    text-align: center;
    max-width: 620px;
    margin: 0 auto;
    box-shadow: 0 12px 35px rgba(45, 65, 28, 0.06);
}
.cart-empty-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #edf7e4;
    color: #4a6a26;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    margin-bottom: 20px;
}
</style>

<!-- Hero Section -->
<section class="cart-theme-hero">
    <div class="container text-center">
        <span class="cart-kicker"><i class="fa-solid fa-basket-shopping text-warning me-1"></i> Fresh Pre-Order Basket</span>
        <h1 class="display-5 fw-bold mt-2 mb-2" style="font-family: 'Fraunces', Georgia, serif;">Review Your Farm Harvests</h1>
        <p class="lead mx-auto mb-0" style="max-width: 620px; color: #5a6b47; font-size: 1.05rem;">
            Order honest produce directly from verified local growers. Collect and pay cash in person on market morning.
        </p>
    </div>
</section>

<div class="cart-main-section">
    <div class="container">
        
        <?php if (empty($items)): ?>
            <div class="cart-empty-box">
                <div class="cart-empty-icon">
                    <i class="fa-solid fa-cart-arrow-down"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2" style="font-family: 'Fraunces', Georgia, serif;">Your Basket is Empty</h3>
                <p class="text-muted small mb-4">You haven't reserved any farm fresh items yet. Explore active community stalls to add produce to your weekly basket.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?php echo ML_asset('products'); ?>" class="btn btn-success rounded-pill px-4 py-2 fw-bold text-white shadow-sm" style="background:#4a5f31; border:none;">
                        <i class="fa-solid fa-leaf me-1"></i> Browse Products
                    </a>
                    <a href="<?php echo ML_asset('markets'); ?>" class="btn btn-outline-success rounded-pill px-4 py-2 fw-bold">
                        <i class="fa-solid fa-store me-1"></i> View Markets
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                
                <!-- Left: Cart Items List -->
                <div class="col-lg-8">
                    <div class="cart-items-card">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-success-subtle">
                            <div>
                                <h4 class="fw-bold text-dark mb-0" style="font-family: 'Fraunces', Georgia, serif;">
                                    <i class="fa-solid fa-boxes-packing text-success me-2"></i> Reserved Produce Items
                                </h4>
                                <span class="text-muted small">Showing <strong class="text-dark"><?php echo count($items); ?></strong> produce item(s) in basket</span>
                            </div>
                            <a href="<?php echo ML_asset('products'); ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                                <i class="fa-solid fa-plus me-1"></i> Add More Items
                            </a>
                        </div>

                        <div class="d-flex flex-column">
                            <?php foreach ($items as $p): 
                                $pImg = prodImgSrc($p);
if ($pImg === '') $pImg = 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=200&q=80';
                            ?>
                                <div class="cart-item-row" data-cart-row="<?php echo $p['product_id']; ?>">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                        
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="cart-prod-thumb">
                                                <img src="<?php echo $pImg; ?>" alt="<?php echo sanitize_output($p['name']); ?>">
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-1 fs-6"><?php echo sanitize_output($p['name']); ?></h6>
                                                <span class="badge bg-light text-muted border rounded-pill px-2 py-0 small mb-1">
                                                    <i class="fa-solid fa-store text-success me-1"></i><?php echo sanitize_output($p['stall_name']); ?>
                                                </span>
                                                <div class="text-success fw-bold small">
                                                    <?php echo money($p['price']); ?> <span class="text-muted fw-normal">/ <?php echo sanitize_output($p['unit'] ?? 'unit'); ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quantity Controls & Row Subtotal -->
                                        <div class="d-flex align-items-center gap-3 ms-auto">
                                            <div class="cart-qty-pill">
                                                <button type="button" class="cart-qty-btn" data-qty-btn="dec">-</button>
                                                <input data-qty-input value="<?php echo (int)$p['qty']; ?>" max="<?php echo (int)$p['stock_quantity']; ?>" readonly class="cart-qty-val">
                                                <button type="button" class="cart-qty-btn" data-qty-btn="inc">+</button>
                                            </div>

                                            <div class="text-end" style="min-width: 85px;">
                                                <div class="fw-bold text-dark fs-6" data-row-total="<?php echo $p['product_id']; ?>">
                                                    <?php echo money($p['price'] * $p['qty']); ?>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" data-remove-cart="<?php echo $p['product_id']; ?>" title="Remove item">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary Card -->
                <div class="col-lg-4">
                    <div class="cart-summary-card sticky-lg-top" style="top: 100px;">
                        <h4 class="fw-bold text-dark mb-3" style="font-family: 'Fraunces', Georgia, serif;">
                            <i class="fa-solid fa-receipt text-success me-2"></i> Basket Summary
                        </h4>

                        <div class="cart-summary-row">
                            <span class="text-muted">Total Reserved Items</span>
                            <span class="fw-bold text-dark" data-cart-items-count><?php echo count($items); ?></span>
                        </div>

                        <div class="cart-summary-row">
                            <span class="text-muted">Direct Market Pickup</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Free</span>
                        </div>

                        <div class="cart-summary-row">
                            <span class="text-muted">Payment Method</span>
                            <span class="fw-semibold text-dark"><i class="fa-solid fa-money-bill-wave text-success me-1"></i> Cash at Stall</span>
                        </div>

                        <div class="cart-summary-row cart-summary-total">
                            <span class="fw-bold text-dark fs-5">Estimated Total</span>
                            <span class="fw-bold fs-4 text-success" data-cart-total><?php echo money($total); ?></span>
                        </div>

                        <div class="mt-4">
                            <?php if ($loggedIn): ?>
                                <a href="<?php echo ML_asset('checkout'); ?>" class="cart-checkout-btn">
                                    <span>Proceed to Pre-Order</span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                                <p class="text-muted text-center small mt-3 mb-0">
                                    <i class="fa-solid fa-shield-check text-success me-1"></i> Choose your pickup market slot in the next step.
                                </p>
                            <?php else: ?>
                                <a href="<?php echo ML_asset('signup'); ?>" class="cart-checkout-btn">
                                    <i class="fa-solid fa-right-to-bracket me-1"></i> Login to Pre-Order
                                </a>
                                <p class="text-muted text-center small mt-3 mb-0">
                                    Don't have an account? <a href="<?php echo ML_asset('signup'); ?>" class="text-success fw-bold">Register free</a> to reserve items.
                                </p>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>