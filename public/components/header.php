<?php
$siteTitle =$pageTitle ?? 'MarketLink - eGreen Basket';
$user = currentUser($pdo);
$isCustomer = isset($user['role']) && $user['role'] === 'customer';$cust = [];
if ($isCustomer) {
    $cust = currentCustomer($pdo);
}
$cartCountHeader = cartCount();
$favCountHeader =$isCustomer ? favCount($pdo, ($cust['customer_id'] ?? 0)) : 0;
$notifCountHeader = $isCustomer ? notifCount($pdo, ($user['id'] ?? 0)) : 0;
$activePage =$page ?? 'home';
$navItems = [
    'home' => ['Home', '', 'fa-house'],
    'markets' => ['Markets', 'markets', 'fa-store'],
    'farmers' => ['Farmers', 'farmers', 'fa-people-roof'],
    'products' => ['Products', 'products', 'fa-leaf'],
    'about' => ['About', 'about', 'fa-circle-info'],
    'contact' => ['Contact', 'contact', 'fa-envelope'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize_output($siteTitle); ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo ML_asset('Uploads/img/log.svg'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Fraunces:wght@600;700&family=Caveat:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/customer.css">
    
    <!-- Navbar flashlight styles live in public/css/customer.css under
         "Navbar Flashlight" - they used to be duplicated inline here,
         which overrode the stylesheet and pointed at a crop-bg.jpg
         that does not exist, so the beam revealed nothing. -->
</head>
<body class="ml-body">
<div class="ml-toast-root"></div>
<script>
    window.ML = { base: '/techwiz7/public', csrf: '<?php echo csrf_token(); ?>' };
</script>
<header class="ml-topbar d-none d-md-flex">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-4">
            <span><i class="fa-solid fa-clock me-1"></i> 7:00 AM - 2:00 PM, Market Days</span>
            <span><i class="fa-solid fa-location-dot me-1"></i> Pre-order, Pick up in person</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="<?php echo ML_asset('about'); ?>" class="ml-topbar-link"><i class="fa-solid fa-basket-shopping me-1"></i>Farm Fresh, Market Public</a>
        </div>
    </div>
</header>

<nav class="navbar navbar-expand-lg ml-navbar sticky-top" id="mlHeaderNav">
    <div class="ml-navbar-reveal-layer" aria-hidden="true">
        <div class="ml-navbar-reveal-bg"></div>
        <div class="ml-navbar-reveal-overlay"></div>
    </div>
    <div class="ml-scroll-progress" aria-hidden="true"><i></i></div>
    <div class="container position-relative" style="z-index: 3;">
        <a class="navbar-brand ml-brand ml-brand-solo d-flex align-items-center" href="<?php echo ML_asset(''); ?>">
            <span class="ml-brand-icon ml-brand-logo">
                <img src="<?php echo ML_asset('Uploads/img/logo-nav.png'); ?>"
                     srcset="<?php echo ML_asset('Uploads/img/logo-nav@2x.png'); ?> 2x"
                     alt="MarketLink" width="82" height="54" decoding="async">
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mlMainNav" aria-controls="mlMainNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="mlMainNav">
            <ul class="navbar-nav mx-auto my-3 my-lg-0 gap-lg-1">
                <?php foreach ($navItems as $key =>$item): ?>
                    <li class="nav-item">
                        <a class="nav-link ml-nav <?php echo $activePage ===$key ? 'active' : ''; ?>" href="<?php echo ML_asset($item[1]); ?>">
                            <i class="fa-solid <?php echo $item[2]; ?> me-1 opacity-75"></i><?php echo $item[0]; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?php echo ML_asset('search'); ?>" class="ml-icon-btn" title="Search"><i class="fa-solid fa-magnifying-glass"></i></a>
                <a href="<?php echo ML_asset('cart'); ?>" class="ml-icon-btn position-relative" title="Cart">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="ml-badge <?php echo $cartCountHeader > 0 ? 'show' : ''; ?>" data-cart-count><?php echo $cartCountHeader; ?></span>
                </a>
                <?php if ($isCustomer): ?>
                    <a href="<?php echo ML_asset('favorites'); ?>" class="ml-icon-btn position-relative" title="Favorites">
                        <i class="fa-solid fa-heart"></i>
                        <span class="ml-badge <?php echo $favCountHeader > 0 ? 'show' : ''; ?>"><?php echo $favCountHeader; ?></span>
                    </a>
                    <a href="<?php echo ML_asset('notifications'); ?>" class="ml-icon-btn position-relative" title="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        <span class="ml-badge <?php echo $notifCountHeader > 0 ? 'show' : ''; ?>"><?php echo $notifCountHeader; ?></span>
                    </a>
                    <div class="dropdown">
                        <a href="#" class="ml-icon-btn" data-bs-toggle="dropdown" title="Account">
                            <i class="fa-solid fa-circle-user fs-5"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li><span class="dropdown-item-text fw-semibold small"><?php echo sanitize_output($cust['full_name'] ?? $user['username']); ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo ML_asset('customer-dashboard'); ?>"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo ML_asset('orders'); ?>"><i class="fa-solid fa-box-archive me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item" href="<?php echo ML_asset('favorites'); ?>"><i class="fa-solid fa-heart me-2"></i>Favorites</a></li>
                            <li><a class="dropdown-item" href="<?php echo ML_asset('profile'); ?>"><i class="fa-solid fa-user-gear me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo ML_asset('logout'); ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo ML_asset('login'); ?>" class="btn ml-btn-ghost btn-sm"><i class="fa-solid fa-right-to-bracket me-1"></i>Login</a>
                    <a href="<?php echo ML_asset('signup'); ?>" class="btn ml-btn-solid btn-sm"><i class="fa-solid fa-user-plus me-1"></i>Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php
/* get_flash() has a side effect (it consumes the session key), so it is
   called once and the result checked. Rendering the wrapper
   unconditionally left an empty .mt-3 div under the navbar on every page
   without a flash message, which showed up as a dead gap between the
   navbar and whatever section came next. */
$mlFlashHtml = get_flash();
if ($mlFlashHtml !== '') : ?>
<div class="ml-flash container mt-3">
    <?php echo $mlFlashHtml; ?>
</div>
<?php endif; ?>
<div class="ml-page-content">

<script>
document.addEventListener("DOMContentLoaded", () => {
    const navbar = document.getElementById("mlHeaderNav");
    if (!navbar) return;

    const revealBg = navbar.querySelector(".ml-navbar-reveal-bg");
    
    // Spring physics coordinates for fluid water wave motion
    let mouse = { x: -500, y: -500 };
    let currentPos = { x: -500, y: -500 };
    const speed = 0.1; // Fluid lag factor for water wave feel

    navbar.addEventListener("mousemove", (e) => {
        const rect = navbar.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
        revealBg.style.opacity = "1";
        navbar.classList.add("is-illuminated");

        // Dynamic element proximity check to switch text color over the revealed light/image
        const interactiveElements = navbar.querySelectorAll('.nav-link, .navbar-brand, .ml-icon-btn');
        interactiveElements.forEach(el => {
            const elRect = el.getBoundingClientRect();
            const elCenterX = elRect.left + elRect.width / 2 - rect.left;
            const elCenterY = elRect.top + elRect.height / 2 - rect.top;
            
            const distance = Math.hypot(mouse.x - elCenterX, mouse.y - elCenterY);
            
            if (distance < 85) {
                el.classList.add("illuminated-text");
            } else {
                el.classList.remove("illuminated-text");
            }
        });
    });

    navbar.addEventListener("mouseleave", () => {
        mouse.x = -500;
        mouse.y = -500;
        revealBg.style.opacity = "0";
        navbar.classList.remove("is-illuminated");
        
        navbar.querySelectorAll('.illuminated-text').forEach(el => {
            el.classList.remove("illuminated-text");
        });
    });

    // Fluid spring loop
    function animate() {
        currentPos.x += (mouse.x - currentPos.x) * speed;
        currentPos.y += (mouse.y - currentPos.y) * speed;

        navbar.style.setProperty("--mouse-x", `${currentPos.x}px`);
        navbar.style.setProperty("--mouse-y", `${currentPos.y}px`);

        requestAnimationFrame(animate);
    }
    animate();
});
</script>