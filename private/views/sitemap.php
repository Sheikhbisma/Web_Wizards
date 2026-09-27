<?php
$pageTitle = 'Sitemap - MarketLink';
include __DIR__ . '/../../public/components/header.php';

$groups = [
    'Browse' => [
        'Home' => ['', 'bi-house'],
        'Community Markets' => ['markets', 'bi-shop'],
        'Verified Farmers' => ['farmers', 'bi-people'],
        'Fresh Products' => ['products', 'bi-basket2'],
        'Search' => ['search', 'bi-search'],
    ],
    'Shopping' => [
        'My Basket' => ['cart', 'bi-basket'],
        'Checkout &amp; Pickup' => ['checkout', 'bi-bag-check'],
        'My Orders' => ['orders', 'bi-box-seam'],
        'Saved Favorites' => ['favorites', 'bi-heart'],
    ],
    'Account' => [
        'Login' => ['login', 'bi-box-arrow-in-right'],
        'Register' => ['signup', 'bi-person-plus'],
        'Forgot Password' => ['forgot-password', 'bi-shield-lock'],
        'My Profile' => ['profile', 'bi-person-gear'],
    ],
    'Help &amp; Info' => [
        'Smart Assist Assistant' => ['ai-chat', 'bi-robot'],
        'About Us' => ['about', 'bi-info-circle'],
        'Contact Support' => ['contact', 'bi-envelope'],
    ],
];
?>

<section class="ml-hero text-center">
    <div class="container">
        <span class="ml-hero-eyebrow"><i class="bi bi-diagram-3"></i> Site Structure</span>
        <h1 class="section-title mt-3" style="font-size:clamp(2rem,4.5vw,3rem);">Sitemap</h1>
        <p class="mx-auto text-muted" style="max-width:56ch;">Everything on MarketLink in one place - browse, shop, and find help in just a click.</p>
    </div>
</section>

<section class="py-5">
    <div class="container" style="max-width:1000px;">
        <div class="row g-4">
            <?php foreach ($groups as $title => $links): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="c-card h-100">
                        <div class="c-card-body">
                            <h5 class="fw-bold mb-3"><i class="bi bi-folder2-open me-2" style="color:var(--ml-green);"></i><?php echo $title; ?></h5>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($links as $label => $page): ?>
                                    <li class="mb-2">
                                        <a href="<?php echo ML_asset($page[0]); ?>" class="text-decoration-none d-flex align-items-center gap-2" style="color:#31422a;font-size:.92rem;">
                                            <i class="bi <?php echo $page[1]; ?>" style="color:var(--ml-leaf);width:18px;text-align:center;"></i>
                                            <?php echo $label; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($user['role'])): ?>
            <div class="mt-4">
                <div class="alert alert-success d-flex align-items-center gap-2 mb-0" role="alert" style="border-radius:14px;">
                    <i class="bi bi-person-check"></i>
                    You are logged in as a <strong>&nbsp;<?php echo sanitize_output(ucfirst($user['role'])); ?>&nbsp;</strong>. Your role dashboard is available
                    <?php if ($user['role'] === 'customer'): ?>
                        <a href="<?php echo ML_asset('customer-dashboard'); ?>">here</a>.
                    <?php elseif ($user['role'] === 'farmer'): ?>
                        <a href="<?php echo ML_asset('farmer-dashboard'); ?>">here</a>.
                    <?php else: ?>
                        <a href="<?php echo ML_asset('admin-dashboard'); ?>">here</a>.
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../../public/components/footer.php'; ?>