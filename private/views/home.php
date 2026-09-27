<?php
$pageTitle = 'MarketLink - eGreen Basket | Home';
$page = 'home';
include __DIR__ . '/../../public/components/header.php';

$homeStats = selectData($pdo, "SELECT
    (SELECT COUNT(*) FROM markets WHERE is_active = 1) AS markets,
    (SELECT COUNT(*) FROM farmers WHERE approval_status = 'approved') AS farmers,
    (SELECT COUNT(*) FROM products WHERE is_available = 1 AND is_sold_out = 0) AS products,
    (SELECT COUNT(*) FROM orders) AS orders")[0];

$featuredProducts = selectData($pdo, "SELECT p.*, f.stall_name, c.category_name FROM products AS p
    INNER JOIN farmers AS f ON f.farmer_id = p.farmer_id AND f.approval_status = 'approved'
    LEFT JOIN categories AS c ON c.category_id = p.category_id
    WHERE p.is_available = 1 AND p.is_sold_out = 0 AND p.stock_quantity > 0
    ORDER BY p.avg_rating DESC, p.product_id DESC LIMIT 3");

$featuredFarmers = selectData($pdo, "SELECT f.farmer_id, f.stall_name, f.contact_person, f.description, f.profile_image, f.address, f.avg_rating, f.total_orders,
    u.email, u.contact AS phone,
    (SELECT COUNT(*) FROM products AS q WHERE q.farmer_id = f.farmer_id AND q.is_available = 1 AND q.is_sold_out = 0) AS products,
    (SELECT m.market_name FROM market_farmer AS mf INNER JOIN markets AS m ON m.market_id = mf.market_id WHERE mf.farmer_id = f.farmer_id AND m.is_active = 1 ORDER BY mf.day_of_week LIMIT 1) AS market_name
    FROM farmers AS f 
    LEFT JOIN users AS u ON u.id = f.user_id
    WHERE f.approval_status = 'approved' AND f.stall_name != 'ali'
    ORDER BY f.farmer_id ASC LIMIT 10");

$homeMarkets = selectData($pdo, "SELECT m.*,
    (SELECT COUNT(DISTINCT mf.farmer_id) FROM market_farmer AS mf WHERE mf.market_id = m.market_id) AS farmers,
    (SELECT COUNT(DISTINCT p.product_id) FROM market_farmer AS mf INNER JOIN products AS p ON p.farmer_id = mf.farmer_id WHERE mf.market_id = m.market_id AND p.is_available = 1 AND p.is_sold_out = 0) AS products
    FROM markets AS m WHERE m.is_active = 1 ORDER BY m.market_name ASC LIMIT 3");

/* SRS: customers must be able to see market and farmer locations on one map
   and get directions to the pickup point, so both are collected here. */
$mapMarkets = selectData($pdo, "SELECT m.market_id, m.market_name, m.address, m.latitude, m.longitude, m.opening_time, m.closing_time
    FROM markets AS m
    WHERE m.is_active = 1 AND m.latitude IS NOT NULL AND m.longitude IS NOT NULL
    ORDER BY m.market_name ASC");

$mapFarmers = selectData($pdo, "SELECT f.farmer_id, f.stall_name, f.address, f.latitude, f.longitude
    FROM farmers AS f
    WHERE f.approval_status = 'approved' AND f.latitude IS NOT NULL AND f.longitude IS NOT NULL
    ORDER BY f.avg_rating DESC, f.total_orders DESC LIMIT 12");

$homeCategories = selectData($pdo, "SELECT c.category_id, c.category_name,
    (SELECT COUNT(*) FROM products AS p WHERE p.category_id = c.category_id AND p.is_available = 1 AND p.is_sold_out = 0) AS product_count
    FROM categories AS c ORDER BY c.category_name ASC");

?>

<?php include __DIR__ . '/../../public/components/gsap-scroll-hero.php'; ?>


<section class="farm-categories-section ml-reveal">
    <div class="farm-categories-banner">
        <div class="container">
            <div class="farm-categories-banner-content">
                <div class="farm-categories-kicker">100% Organic &amp; Farm Fresh</div>
                <h2 class="farm-categories-heading">Our 5 Natural Categories</h2>
                <p class="farm-categories-sub">Hand-picked daily, grown sustainably by verified local farmers ready for your market day.</p>
            </div>
        </div>
    </div>

    <div class="container farm-categories-row">
        <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 justify-content-center" data-stagger="80">
            
            <!-- Category 1: Vegetables -->
            <div class="col">
                <div class="farm-cat-card">
                    <div class="farm-cat-circle-wrap">
                        <img src="<?php echo ML_asset('Uploads/hero/vegetables.jpg'); ?>" alt="Vegetables" class="farm-cat-img">
                    </div>
                    <h4 class="farm-cat-title">Vegetables</h4>
                    <p class="farm-cat-desc">Crisp leafy greens, roots &amp; seasonal field harvest.</p>
                    <a href="<?php echo ML_asset('products') . '?q=vegetable'; ?>" class="farm-cat-btn">Read More</a>
                </div>
            </div>

            <!-- Category 2: Fruits -->
            <div class="col">
                <div class="farm-cat-card">
                    <div class="farm-cat-circle-wrap">
                        <img src="<?php echo ML_asset('Uploads/hero/fruits.JPG'); ?>" alt="Fruits" class="farm-cat-img">
                    </div>
                    <h4 class="farm-cat-title">Fruits</h4>
                    <p class="farm-cat-desc">Sweet orchard apples, berries, citrus &amp; melons.</p>
                    <a href="<?php echo ML_asset('products') . '?q=fruit'; ?>" class="farm-cat-btn">Read More</a>
                </div>
            </div>

            <!-- Category 3: Bakery -->
            <div class="col">
                <div class="farm-cat-card">
                    <div class="farm-cat-circle-wrap">
                        <img src="<?php echo ML_asset('Uploads/hero/bakery.webp'); ?>" alt="Bakery" class="farm-cat-img">
                    </div>
                    <h4 class="farm-cat-title">Bakery</h4>
                    <p class="farm-cat-desc">Rustic sourdoughs, multigrain loaves &amp; pastries.</p>
                    <a href="<?php echo ML_asset('products') . '?q=bakery'; ?>" class="farm-cat-btn">Read More</a>
                </div>
            </div>

            <!-- Category 4: Dairy -->
            <div class="col">
                <div class="farm-cat-card">
                    <div class="farm-cat-circle-wrap">
                        <img src="<?php echo ML_asset('Uploads/hero/dairy.jpg'); ?>" alt="Dairy" class="farm-cat-img">
                    </div>
                    <h4 class="farm-cat-title">Dairy</h4>
                    <p class="farm-cat-desc">Pasture-raised milk, artisanal cheese &amp; butter.</p>
                    <a href="<?php echo ML_asset('products') . '?q=dairy'; ?>" class="farm-cat-btn">Read More</a>
                </div>
            </div>

            <!-- Category 5: Herbs -->
            <div class="col">
                <div class="farm-cat-card">
                    <div class="farm-cat-circle-wrap">
                        <img src="<?php echo ML_asset('Uploads/hero/herbs.webp'); ?>" alt="Herbs" class="farm-cat-img">
                    </div>
                    <h4 class="farm-cat-title">Herbs</h4>
                    <p class="farm-cat-desc">Wild rosemary, fragrant basil, mint &amp; fresh thyme.</p>
                    <a href="<?php echo ML_asset('products') . '?q=herb'; ?>" class="farm-cat-btn">Read More</a>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="ml-impact-full-section ml-reveal">
    <div class="container-fluid px-3 px-md-5 py-4 py-md-4">
        <div class="row align-items-center g-3 g-lg-4">
            <!-- Left Content Column -->
            <div class="col-12 col-lg-4 col-xl-3">
                <div class="ml-impact-header">
                    <span class="ml-impact-kicker"><i class="bi bi-patch-check-fill text-leaf"></i> Why MarketLink</span>
                    <h2 class="ml-impact-title">Real Harvest.<br>Real Impact.</h2>
                    <p class="ml-impact-desc">
                        Direct connection with verified growers for pure organic produce, fair farmer prices, and zero food waste.
                    </p>
                    <div class="d-none d-lg-block">
                        <a href="<?php echo ML_asset('products'); ?>" class="ml-impact-btn">
                            <span>View All Harvest</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right 4 Metric/Feature Boxes with Images & Headings -->
            <div class="col-12 col-lg-8 col-xl-9">
                <div class="row g-2 g-md-3 row-cols-2 row-cols-xl-4">
                    <!-- Card 1: Fresh Produce -->
                    <div class="col">
                        <div class="ml-stat-card-compact">
                            <div class="ml-stat-card-bg" style="background-image: url('https://images.unsplash.com/photo-1610348725531-843dff563e2c?auto=format&fit=crop&w=600&q=80');"></div>
                            <div class="ml-stat-card-overlay"></div>
                            <div class="ml-stat-card-content">
                                <div class="ml-stat-val">100%</div>
                                <h5 class="ml-stat-title">Fresh Produce</h5>
                                <div class="ml-stat-divider"></div>
                                <span class="ml-stat-sub">Picked Same Morning</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Local Farmers -->
                    <div class="col">
                        <div class="ml-stat-card-compact">
                            <div class="ml-stat-card-bg" style="background-image: url('https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&w=600&q=80');"></div>
                            <div class="ml-stat-card-overlay"></div>
                            <div class="ml-stat-card-content">
                                <div class="ml-stat-val"><?php echo max(50, (int)($homeStats['farmers'] ?? 0)); ?>+</div>
                                <h5 class="ml-stat-title">Local Farmers</h5>
                                <div class="ml-stat-divider"></div>
                                <span class="ml-stat-sub">Verified &amp; Certified</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Nearby Markets -->
                    <div class="col">
                        <div class="ml-stat-card-compact">
                            <div class="ml-stat-card-bg" style="background-image: url('https://images.unsplash.com/photo-1488459716781-31db52582fe9?auto=format&fit=crop&w=600&q=80');"></div>
                            <div class="ml-stat-card-overlay"></div>
                            <div class="ml-stat-card-content">
                                <div class="ml-stat-val"><?php echo max(15, (int)($homeStats['markets'] ?? 0)); ?>+</div>
                                <h5 class="ml-stat-title">Nearby Markets</h5>
                                <div class="ml-stat-divider"></div>
                                <span class="ml-stat-sub">Community Stalls</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Seasonal Harvest -->
                    <div class="col">
                        <div class="ml-stat-card-compact">
                            <div class="ml-stat-card-bg" style="background-image: url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=600&q=80');"></div>
                            <div class="ml-stat-card-overlay"></div>
                            <div class="ml-stat-card-content">
                                <div class="ml-stat-val">0%</div>
                                <h5 class="ml-stat-title">Seasonal Produce</h5>
                                <div class="ml-stat-divider"></div>
                                <span class="ml-stat-sub">Zero Waste Model</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile CTA Button -->
                <div class="text-center d-lg-none mt-3">
                    <a href="<?php echo ML_asset('products'); ?>" class="ml-impact-btn">
                        <span>View All Harvest</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($featuredProducts)): ?>
<section class="mlp-section ml-reveal">
    <div class="container">
        
        <!-- Image 2 Header: "Our Product - GOOD PRODUCT FOR THE BLUE PLANET" + "READ MORE ↗" -->
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
            <div>
                <div class="mlp-kicker text-uppercase fw-bold" style="color: #5c7047; letter-spacing: 1.5px; font-size: 0.85rem;">Our Product</div>
                <h2 class="mlp-title mb-0" style="font-family: 'Poppins', system-ui, sans-serif; font-weight: 800; letter-spacing: -0.5px; font-size: clamp(1.8rem, 3.2vw, 2.6rem);">
                    GOOD PRODUCT<br><span style="color: #7ca83c;">FOR THE BLUE PLANET</span>
                </h2>
            </div>
            <a href="<?php echo ML_asset('products'); ?>" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2" style="font-size: 0.88rem; border-color: #9fb383; color: #40543a;">
                READ MORE <i class="bi bi-arrow-up-right"></i>
            </a>
        </div>

        <!-- Featured Products Grid (3 Cards Matched to Image 3) -->
        <div class="fp-card-grid" id="fpCardGrid">
            <?php foreach ($featuredProducts as $p): 
                $catLower = strtolower($p['category_name'] ?? '');
                $fallbackHero = 'vegetables.jpg';
                if (strpos($catLower, 'fruit') !== false) $fallbackHero = 'fruits.JPG';
                elseif (strpos($catLower, 'bake') !== false) $fallbackHero = 'bakery.webp';
                elseif (strpos($catLower, 'dairy') !== false) $fallbackHero = 'dairy.jpg';
                elseif (strpos($catLower, 'herb') !== false) $fallbackHero = 'herbs.webp';
                $pImgSrc = prodImgSrc($p);
                if ($pImgSrc === '') $pImgSrc = ML_asset('Uploads/hero/' . $fallbackHero);
            ?>
                <a href="<?php echo ML_asset('product') . '?id=' . $p['product_id']; ?>" 
                   class="fp-product-card" 
                   data-category="<?php echo htmlspecialchars($catLower); ?>" 
                   data-name="<?php echo htmlspecialchars(strtolower($p['name'])); ?>">
                    
                    <h4 class="fp-card-title"><?php echo sanitize_output($p['name']); ?></h4>
                    
                    <div class="fp-card-middle-row">
                        <div class="fp-card-info-col">
                            <div class="fp-card-stall">
                                <i class="bi bi-shop"></i>
                                <span><?php echo sanitize_output($p['stall_name']); ?></span>
                            </div>
                            <div class="fp-card-price">
                                <?php echo money($p['price']); ?> <span class="unit-tag">/ <?php echo sanitize_output($p['unit']); ?></span>
                            </div>
                            <div class="fp-card-rating">
                                <span class="stars-gold"><?php echo starsHtml($p['avg_rating'] > 0 ? $p['avg_rating'] : 5, 13); ?></span>
                                <span class="rating-val"><?php echo number_format($p['avg_rating'] > 0 ? $p['avg_rating'] : 5.0, 1); ?></span>
                            </div>
                        </div>

                        <div class="fp-card-img-wrap">
                            <img src="<?php echo $pImgSrc; ?>" alt="<?php echo sanitize_output($p['name']); ?>" loading="lazy">
                        </div>
                    </div>

                    <!-- Bottom row: Read More Text and Green Circular Arrow Button -->
                    <div class="fp-card-bottom-row">
                        <span class="fp-read-more-text">Read More</span>
                        <span class="fp-arrow-circle"><i class="bi bi-arrow-up-right"></i></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo ML_asset('products'); ?>" class="mlp-btn-ghost">Browse all products <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Hero Filter Toolbar Dropdown Interactivity Script -->
<script>
(function () {
    var heroFilterBtn = document.getElementById("heroFilterTypeBtn");
    var heroFilterMenu = document.getElementById("heroFilterTypeMenu");
    var heroFilterChevron = document.getElementById("heroFilterChevron");
    if (heroFilterBtn && heroFilterMenu) {
        heroFilterBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            var isOpen = heroFilterMenu.classList.toggle("show");
            if (heroFilterChevron) {
                heroFilterChevron.className = isOpen ? "bi bi-chevron-up" : "bi bi-chevron-down";
            }
        });
        document.addEventListener("click", function(e) {
            if (!heroFilterMenu.contains(e.target) && e.target !== heroFilterBtn) {
                heroFilterMenu.classList.remove("show");
                if (heroFilterChevron) heroFilterChevron.className = "bi bi-chevron-down";
            }
        });
    }

    // Connect filter form checkboxes to search
    var heroFilterForm = document.getElementById("heroFilterForm");
    if (heroFilterForm) {
        heroFilterForm.addEventListener("submit", function(e) {
            var checked = document.querySelectorAll(".hero-type-checkbox:checked");
            if (checked.length > 0) {
                var types = [];
                checked.forEach(function(c) { types.push(c.value); });
                var input = document.getElementById("heroFilterInput");
                if (input && !input.value.trim()) {
                    input.value = types.join(" ");
                }
            }
        });
    }
})();
</script>

<!-- ============================================================
     Harvest Section with Video Background (public/uploads/home-page-section.mp4)
     ============================================================ -->
<section class="ml-video-harvest-section ml-reveal">
    <div class="ml-video-harvest-bg-wrap" aria-hidden="true">
        <video class="ml-video-harvest-bg" autoplay muted loop playsinline poster="<?php echo ML_asset('Uploads/img/crop.webp'); ?>">
            <source src="<?php echo ML_asset('uploads/home-page-section.mp4'); ?>" type="video/mp4">
        </video>
        <div class="ml-video-harvest-overlay"></div>
    </div>
    <div class="ml-parallax-content position-relative" style="z-index: 3;">
        <div class="container text-center text-white">
            <span class="parallax-kicker"><i class="bi bi-play-circle-fill text-leaf me-1"></i> 100% Sustainable &amp; Direct</span>
            <h2 class="parallax-title">Fresh From Our Fields To Your Hands</h2>
            <p class="parallax-sub">Experience the authentic taste of harvest picked just hours ago. Connecting conscious food lovers directly with verified regional growers and traditional community markets.</p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="<?php echo ML_asset('products'); ?>" class="parallax-btn-theme">Explore Fresh Harvest <i class="bi bi-arrow-right ms-1"></i></a>
                <a href="<?php echo ML_asset('markets'); ?>" class="parallax-btn-outline">Find A Market Near You</a>
            </div>
        </div>
    </div>
</section>

<?php
// Prepare Farmers Data for the Arc Slider using authentic farmer records
$displayFarmers = [];
if (!empty($featuredFarmers)) {
    foreach ($featuredFarmers as $f) {
        $displayFarmers[] = [
            'farmer_id' => $f['farmer_id'],
            'stall_name' => !empty($f['stall_name']) ? $f['stall_name'] : 'Organic Harvest',
            'contact_person' => !empty($f['contact_person']) ? $f['contact_person'] : 'Verified Producer',
            'description' => (!empty($f['description']) && strlen(trim($f['description'])) > 5) 
                ? $f['description'] 
                : 'Cultivating fresh pesticide-free vegetables and seasonal produce with sustainable farming methods.',
            'profile_image' => $f['profile_image'] ?? '',
            'address' => $f['address'] ?? 'Karachi, Pakistan',
            'market_name' => $f['market_name'] ?? 'Local Farmers Market',
            'phone' => $f['phone'] ?? '0333-1234567',
            'email' => $f['email'] ?? '',
            'rating' => !empty($f['avg_rating']) ? (float)$f['avg_rating'] : 4.9
        ];
    }
}

// Authentic seeded farmers fallback list
$seedFarmers = [
    [
        'farmer_id' => 2,
        'stall_name' => 'Ayesha Farms',
        'contact_person' => 'Ali Raza',
        'description' => 'Ayesha Farms produce organic vegetables grown in Tando Adam, harvested before sunrise and at your gate by noon.',
        'address' => 'Plot 8, Farm Colony Tando Adam',
        'phone' => '0333-1234567',
        'email' => 'ali.raza@gmail.com',
        'rating' => 4.9
    ],
    [
        'farmer_id' => 3,
        'stall_name' => 'Nida Orchard',
        'contact_person' => 'Nida Bano',
        'description' => 'Family-run mango and citrus orchard. Seasonal fruits only, picked ripe and packed the same morning.',
        'address' => 'Orchard Road, Mirpurkhas',
        'phone' => '0345-1234567',
        'email' => 'nida.orchard@gmail.com',
        'rating' => 4.8
    ],
    [
        'farmer_id' => 4,
        'stall_name' => 'GreenGate Dairy',
        'contact_person' => 'Usman Khan',
        'description' => 'Pure desi dairy — raw milk, fresh butter and clotted cream collected every single morning.',
        'address' => 'Green Gate Lane, Latifabad, Hyderabad',
        'phone' => '0321-1234567',
        'email' => 'greengate.dairy@gmail.com',
        'rating' => 5.0
    ],
    [
        'farmer_id' => 5,
        'stall_name' => 'Sunrise Bakery',
        'contact_person' => 'Farhan Sheikh',
        'description' => 'Wood-fired whole-wheat bread, sourdough and flatbread baked daily at dawn.',
        'address' => 'Shop 21, Shahrah-e-Quaideen, Saddar',
        'phone' => '0300-1234567',
        'email' => 'sunrise.bake@gmail.com',
        'rating' => 4.9
    ],
    [
        'farmer_id' => 6,
        'stall_name' => 'Herb Haven',
        'contact_person' => 'Sana Abbasi',
        'description' => 'Hydroponic herbs and microgreens grown in Karachi — basil, mint, coriander and greens year round.',
        'address' => 'Zone C, Scheme 33, Karachi',
        'phone' => '0315-1234567',
        'email' => 'herbhaven.pk@gmail.com',
        'rating' => 4.9
    ],
    [
        'farmer_id' => 7,
        'stall_name' => 'Farm2Basket',
        'contact_person' => 'Imran Shah',
        'description' => 'A collective of small holder farmers delivering mixed seasonal baskets of fruit and vegetables.',
        'address' => 'Main PAF Road, Malir, Karachi',
        'phone' => '0316-1234567',
        'email' => 'farm2basket@gmail.com',
        'rating' => 4.8
    ]
];

$finalFarmers = $displayFarmers;
$seedIdx = 0;
while (count($finalFarmers) < 6) {
    $finalFarmers[] = $seedFarmers[$seedIdx % count($seedFarmers)];
    $seedIdx++;
}

$themeClasses = [
    'arc-theme-sage-1',
    'arc-theme-sage-2',
    'arc-theme-sage-3',
    'arc-theme-sage-4',
    'arc-theme-sage-5',
    'arc-theme-sage-6'
];
?>
<section class="mlp-section ml-arc-farmers-section ml-reveal">
    <div class="container">
        <div class="mlp-head text-center mb-4">
            <div class="mlp-kicker">Meet Your Farmers</div>
            <h2 class="mlp-title">Verified Local Farmers</h2>
            <p class="mlp-sub">Approved stalls with real ratings from customers who collected their orders.</p>
        </div>

        <div class="ml-arc-slider-wrapper">
            <div class="ml-arc-stage" id="mlArcStage">
                <?php foreach ($finalFarmers as $idx => $f): 
                    $themeClass = $themeClasses[$idx % count($themeClasses)];
                ?>
                    <div class="ml-arc-card <?php echo $themeClass; ?>" data-index="<?php echo $idx; ?>">
                        <div class="arc-card-header">
                            <!-- Starburst icon in theme circular badge -->
                            <div class="arc-icon-badge">
                                <svg class="arc-star-icon" viewBox="0 0 40 40" fill="none">
                                    <path d="M20 4V36M4 20H36M8.69 8.69L31.31 31.31M8.69 31.31L31.31 8.69" stroke="#4a5f31" stroke-width="2.6" stroke-linecap="round"/>
                                    <circle cx="20" cy="20" r="3.5" fill="#4a5f31"/>
                                </svg>
                            </div>
                            <h3 class="arc-card-stall-title" title="<?php echo sanitize_output($f['stall_name']); ?>">
                                <?php echo sanitize_output($f['stall_name']); ?>
                            </h3>
                            <div class="arc-farmer-badge">
                                <span><i class="bi bi-person-fill"></i> <?php echo sanitize_output($f['contact_person']); ?></span>
                                <?php if (!empty($f['address'])): 
                                    $addrParts = explode(',', $f['address']);
                                    $shortLoc = trim(end($addrParts));
                                    if (empty($shortLoc) || strlen($shortLoc) > 18) $shortLoc = trim($addrParts[0]);
                                ?>
                                    <span class="arc-loc-dot">·</span>
                                    <span><i class="bi bi-geo-alt-fill"></i> <?php echo sanitize_output($shortLoc); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <p class="arc-card-desc">
                            <?php echo sanitize_output($f['description']); ?>
                        </p>

                        <div class="arc-card-footer">
                            <?php if (!empty($f['phone'])): ?>
                                <div class="arc-contact-pill">
                                    <i class="bi bi-telephone-fill"></i> <?php echo sanitize_output($f['phone']); ?>
                                </div>
                            <?php endif; ?>
                            <a href="<?php echo ML_asset('farmer') . '?id=' . (int)$f['farmer_id']; ?>" class="arc-card-btn">
                                VIEW STALL <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Arc Slider Navigation Controls -->
            <div class="ml-arc-controls">
                <button type="button" class="ml-arc-btn" id="mlArcPrev" aria-label="Previous Farmer">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div class="ml-arc-dots" id="mlArcDots"></div>
                <button type="button" class="ml-arc-btn" id="mlArcNext" aria-label="Next Farmer">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="<?php echo ML_asset('farmers'); ?>" class="mlp-btn-ghost">
                Meet all farmers <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<script>
(function() {
    function initArcCarousel() {
        var stage = document.getElementById('mlArcStage');
        if (!stage) return;
        var cards = Array.from(stage.querySelectorAll('.ml-arc-card'));
        if (!cards.length) return;

        var prevBtn = document.getElementById('mlArcPrev');
        var nextBtn = document.getElementById('mlArcNext');
        var dotsContainer = document.getElementById('mlArcDots');
        var currentIndex = Math.floor(cards.length / 2);
        var autoTimer = null;
        var isDragging = false;
        var startX = 0;
        var currentX = 0;

        // Generate Dots
        if (dotsContainer) {
            dotsContainer.innerHTML = '';
            cards.forEach(function(_, idx) {
                var dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'ml-arc-dot' + (idx === currentIndex ? ' active' : '');
                dot.setAttribute('aria-label', 'Go to farmer slide ' + (idx + 1));
                dot.addEventListener('click', function() {
                    currentIndex = idx;
                    updateSlider();
                    resetAuto();
                });
                dotsContainer.appendChild(dot);
            });
        }

        function updateSlider() {
            var total = cards.length;
            var isMobile = window.innerWidth < 768;
            var isTablet = window.innerWidth >= 768 && window.innerWidth < 992;

            var spacingX = isMobile ? 120 : (isTablet ? 175 : 215);
            var dropY = isMobile ? 22 : 30;
            var rotateAngle = isMobile ? 6.5 : 8.5;

            cards.forEach(function(card, idx) {
                var diff = idx - currentIndex;
                while (diff > total / 2) diff -= total;
                while (diff < -total / 2) diff += total;

                var absDiff = Math.abs(diff);

                if (absDiff > 2 && total > 5) {
                    card.style.opacity = '0';
                    card.style.pointerEvents = 'none';
                    card.style.transform = 'translate3d(' + (diff * spacingX * 1.3) + 'px, 100px, -150px) scale(0.7) rotate(' + (diff * rotateAngle) + 'deg)';
                    card.style.zIndex = '0';
                    card.classList.remove('is-active');
                } else if (absDiff > (isMobile ? 1 : 2)) {
                    card.style.opacity = '0';
                    card.style.pointerEvents = 'none';
                    card.style.transform = 'translate3d(' + (diff * spacingX * 1.2) + 'px, 85px, -100px) scale(0.75) rotate(' + (diff * rotateAngle) + 'deg)';
                    card.style.zIndex = '1';
                    card.classList.remove('is-active');
                } else {
                    var tx = diff * spacingX;
                    var ty = Math.pow(absDiff, 1.75) * dropY;
                    var rot = diff * rotateAngle;
                    var scale = 1 - (absDiff * (isMobile ? 0.08 : 0.055));
                    var opacity = 1 - (absDiff * (isMobile ? 0.4 : 0.16));
                    var zIndex = 10 - absDiff;

                    card.style.opacity = opacity;
                    card.style.pointerEvents = 'auto';
                    card.style.transform = 'translate3d(' + tx + 'px, ' + ty + 'px, ' + (-absDiff * 25) + 'px) scale(' + scale + ') rotate(' + rot + 'deg)';
                    card.style.zIndex = zIndex;

                    if (diff === 0) {
                        card.classList.add('is-active');
                        card.style.boxShadow = '0 24px 50px rgba(0, 0, 0, 0.22)';
                    } else {
                        card.classList.remove('is-active');
                        card.style.boxShadow = '0 12px 28px rgba(0, 0, 0, 0.1)';
                    }
                }
            });

            if (dotsContainer) {
                var dots = dotsContainer.querySelectorAll('.ml-arc-dot');
                dots.forEach(function(dot, idx) {
                    if (idx === currentIndex) dot.classList.add('active');
                    else dot.classList.remove('active');
                });
            }
        }

        function goNext() {
            currentIndex = (currentIndex + 1) % cards.length;
            updateSlider();
        }

        function goPrev() {
            currentIndex = (currentIndex - 1 + cards.length) % cards.length;
            updateSlider();
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                goNext();
                resetAuto();
            });
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                goPrev();
                resetAuto();
            });
        }

        // Clicking side card brings it to center focus
        cards.forEach(function(card, idx) {
            card.addEventListener('click', function(e) {
                if (idx !== currentIndex) {
                    var isBtn = e.target.closest('.arc-card-btn');
                    if (!isBtn) {
                        e.preventDefault();
                        currentIndex = idx;
                        updateSlider();
                        resetAuto();
                    }
                }
            });
        });

        // Drag & Touch Gestures
        stage.addEventListener('touchstart', function(e) {
            isDragging = true;
            startX = e.touches[0].clientX;
            currentX = startX;
            stopAuto();
        }, { passive: true });

        stage.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            currentX = e.touches[0].clientX;
        }, { passive: true });

        stage.addEventListener('touchend', function() {
            if (!isDragging) return;
            isDragging = false;
            var diffX = currentX - startX;
            if (diffX > 35) {
                goPrev();
            } else if (diffX < -35) {
                goNext();
            }
            startAuto();
        });

        // Mouse Drag Support
        var isMouseDown = false;
        stage.addEventListener('mousedown', function(e) {
            if (e.target.closest('.arc-card-btn') || e.target.closest('.ml-arc-btn')) return;
            isMouseDown = true;
            startX = e.clientX;
            currentX = startX;
            stopAuto();
        });

        window.addEventListener('mousemove', function(e) {
            if (!isMouseDown) return;
            currentX = e.clientX;
        });

        window.addEventListener('mouseup', function() {
            if (!isMouseDown) return;
            isMouseDown = false;
            var diffX = currentX - startX;
            if (diffX > 45) {
                goPrev();
            } else if (diffX < -45) {
                goNext();
            }
            startAuto();
        });

        function startAuto() {
            stopAuto();
            autoTimer = setInterval(goNext, 4500);
        }
        function stopAuto() {
            if (autoTimer) {
                clearInterval(autoTimer);
                autoTimer = null;
            }
        }
        function resetAuto() {
            stopAuto();
            startAuto();
        }

        stage.addEventListener('mouseenter', stopAuto);
        stage.addEventListener('mouseleave', startAuto);
        window.addEventListener('resize', updateSlider);

        updateSlider();
        startAuto();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initArcCarousel);
    } else {
        initArcCarousel();
    }
})();
</script>

<?php if (!empty($homeMarkets)): ?>
<section class="ml-markets-section-redesign ml-reveal">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <div class="mlp-kicker"><i class="bi bi-geo-alt-fill text-leaf me-1"></i> Plan Your Pickup</div>
                <h2 class="mlp-title mb-1">Explore Community Markets</h2>
                <p class="mlp-sub mb-0">Choose a local market hub, reserve fresh morning harvests, and pick up directly from verified growers.</p>
            </div>
            <div>
                <a href="<?php echo ML_asset('markets'); ?>" class="ml-btn-market-all">
                    <span>View All Markets</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="row g-4 justify-content-center" data-stagger="100">
            <?php 
            $marketBgs = [
                'https://images.unsplash.com/photo-1488459716781-31db52582fe9?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1519999482648-25049ddd37b1?auto=format&fit=crop&w=700&q=80',
                'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=700&q=80',
            ];
            foreach ($homeMarkets as $mIdx => $mk): 
                $bgImg = !empty($mk['image_url']) ? ML_asset(htmlspecialchars($mk['image_url'])) : $marketBgs[$mIdx % count($marketBgs)];
                $marketUrl = ML_asset('market') . '?id=' . $mk['market_id'];
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="ml-market-premium-card">
                        <div class="m-card-media">
                            <img src="<?php echo $bgImg; ?>" alt="<?php echo sanitize_output($mk['market_name']); ?>" class="m-card-media-img" loading="lazy">
                            <div class="m-card-media-overlay"></div>
                            <div class="m-status-pill">
                                <span class="m-pulse-dot"></span>
                                <span>Active Market Hub</span>
                            </div>
                            <div class="m-days-pill">
                                <i class="bi bi-calendar-week me-1"></i>
                                <span><?php echo marketPickups($mk); ?></span>
                            </div>
                        </div>

                        <div class="m-card-body">
                            <h5 class="m-card-title"><?php echo sanitize_output($mk['market_name']); ?></h5>
                            <div class="m-card-addr">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span><?php echo sanitize_output($mk['address']); ?></span>
                            </div>

                            <div class="m-chips-grid">
                                <div class="m-chip">
                                    <i class="bi bi-clock"></i>
                                    <div>
                                        <span class="m-chip-label">Pickup Hours</span>
                                        <strong><?php echo !empty($mk['opening_time']) ? date('g:i A', strtotime($mk['opening_time'])) . ' - ' . date('g:i A', strtotime($mk['closing_time'])) : 'Morning'; ?></strong>
                                    </div>
                                </div>
                                <div class="m-chip">
                                    <i class="bi bi-people-fill"></i>
                                    <div>
                                        <span class="m-chip-label">Farmers</span>
                                        <strong><?php echo (int)($mk['farmers'] ?? 0); ?> Active</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="m-card-footer">
                                <a href="<?php echo $marketUrl; ?>" class="m-action-btn-primary">
                                    <span>Browse Stalls</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                                <a href="#mlpMapSection" class="btn btn-sm btn-outline-success rounded-pill px-3 py-2 fw-semibold" title="View on Map" style="border-color: #bdd4a4; color: #354722;">
                                    <i class="bi bi-map me-1"></i> Map
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($mapMarkets) || !empty($mapFarmers)): ?>
<!-- SRS feature: view market and farmer locations on an integrated map,
     then get directions to the pickup point. -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<section class="mlp-section ml-reveal" id="mlpMapSection">
    <div class="container">
        <div class="mlp-head">
            <div class="mlp-kicker">Find Your Pickup Point</div>
            <h2 class="mlp-title">Markets &amp; Farmers On The Map</h2>
            <p class="mlp-sub">Every market and stall that shares a location appears here. Pick the closest one, get directions, then collect your pre-order.</p>
        </div>

        <div class="mlp-map-grid">
            <div class="mlp-map-shell">
                <div id="mlpHomeMap" role="region" aria-label="Map of MarketLink markets and farmers"></div>
                <div class="mlp-map-legend">
                    <span><i class="bi bi-square-fill mlp-dot-market"></i> Market / pickup point</span>
                    <span><i class="bi bi-circle-fill mlp-dot-farmer"></i> Farmer stall</span>
                </div>
            </div>

            <div class="mlp-map-side">
                <h3 class="mlp-side-title">Get Directions</h3>
                <p class="mlp-side-sub">Open a pickup point in your map app, or read the farmer page for stall details.</p>
                <div class="mlp-map-list" data-stagger="70">
                    <?php foreach ($mapMarkets as $mm): ?>
                        <div class="mlp-map-row">
                            <span class="mlp-map-ico market"><i class="bi bi-shop"></i></span>
                            <div class="mlp-map-meta">
                                <a href="<?php echo ML_asset('market') . '?id=' . $mm['market_id']; ?>" class="mlp-map-name"><?php echo sanitize_output($mm['market_name']); ?></a>
                                <span class="mlp-map-addr"><?php echo sanitize_output($mm['address']); ?></span>
                            </div>
                            <a class="mlp-map-dir" target="_blank" rel="noopener"
                               href="https://www.openstreetmap.org/directions?to=<?php echo $mm['latitude']; ?>%2C<?php echo $mm['longitude']; ?>"
                               title="Directions to <?php echo sanitize_output($mm['market_name']); ?>"><i class="bi bi-signpost-split"></i></a>
                        </div>
                    <?php endforeach; ?>
                    <?php foreach ($mapFarmers as $mfp): ?>
                        <div class="mlp-map-row">
                            <span class="mlp-map-ico farmer"><i class="bi bi-person-fill"></i></span>
                            <div class="mlp-map-meta">
                                <a href="<?php echo ML_asset('farmer') . '?id=' . $mfp['farmer_id']; ?>" class="mlp-map-name"><?php echo sanitize_output($mfp['stall_name']); ?></a>
                                <span class="mlp-map-addr"><?php echo sanitize_output($mfp['address']); ?></span>
                            </div>
                            <a class="mlp-map-dir" target="_blank" rel="noopener"
                               href="https://www.openstreetmap.org/directions?to=<?php echo $mfp['latitude']; ?>%2C<?php echo $mfp['longitude']; ?>"
                               title="Directions to <?php echo sanitize_output($mfp['stall_name']); ?>"><i class="bi bi-signpost-split"></i></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('mlpHomeMap');
    if (!el || typeof L === 'undefined') { return; }

    var points = <?php echo json_encode(array_merge(
        array_map(function ($m) {
            return ['lat' => (float)$m['latitude'], 'lng' => (float)$m['longitude'],
                    'name' => $m['market_name'], 'kind' => 'market',
                    'url' => ML_asset('market') . '?id=' . $m['market_id']];
        }, $mapMarkets),
        array_map(function ($f) {
            return ['lat' => (float)$f['latitude'], 'lng' => (float)$f['longitude'],
                    'name' => $f['stall_name'], 'kind' => 'farmer',
                    'url' => ML_asset('farmer') . '?id=' . $f['farmer_id']];
        }, $mapFarmers)
    )); ?>;

    var marketIcon = L.divIcon({
        className: '',
        html: '<span class="mlp-pin mlp-pin-market"><i class="bi bi-shop"></i></span>',
        iconSize: [34, 34], iconAnchor: [17, 34], popupAnchor: [0, -32]
    });
    var farmerIcon = L.divIcon({
        className: '',
        html: '<span class="mlp-pin mlp-pin-farmer"><i class="bi bi-person-fill"></i></span>',
        iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -26]
    });

    var marketBounds = [];
    var anyPoint = false;
    var map = L.map(el, { scrollWheelZoom: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    points.forEach(function (p) {
        if (!isFinite(p.lat) || !isFinite(p.lng)) { return; }
        anyPoint = true;
        /* The view is framed on markets only, because a single mis-typed
           farm coordinate would otherwise zoom the whole map out to nowhere. */
        if (p.kind === 'market') { marketBounds.push([p.lat, p.lng]); }
        L.marker([p.lat, p.lng], { icon: p.kind === 'market' ? marketIcon : farmerIcon })
            .addTo(map)
            .bindPopup(
                '<div class="mlp-popup">' +
                '<span class="mlp-popup-kind">' + (p.kind === 'market' ? 'Market / pickup point' : 'Farmer stall') + '</span>' +
                '<strong>' + p.name.replace(/[<>&]/g, '') + '</strong>' +
                '<a class="mlp-popup-btn" href="' + p.url + '">View details</a>' +
                '<a class="mlp-popup-btn ghost" target="_blank" rel="noopener" href="https://www.openstreetmap.org/directions?to=' +
                p.lat + '%2C' + p.lng + '">Directions</a>' +
                '</div>'
            );
    });

    if (marketBounds.length) {
        map.fitBounds(marketBounds, { padding: [45, 45], maxZoom: 14 });
    } else if (anyPoint) {
        map.setView(points[0], 11);
    } else {
        map.setView([24.8607, 67.0011], 12);
    }

    /* Re-measure once the panel settles, otherwise Leaflet keeps a stale size. */
    setTimeout(function () { map.invalidateSize(); }, 260);
    window.addEventListener('resize', function () { map.invalidateSize(); });
});
</script>
<?php endif; ?>

<section class="mlp-section alt">
    <div class="container">
        <div class="mlp-split">
            <div class="mlp-split-media" data-reveal="left">
                <img src="<?php echo ML_asset('Uploads/img/h1-banner.png'); ?>" alt="Built for both sides - MarketLink">
                <span class="mlp-float-card">
                    <i class="bi bi-people-fill"></i>
                    <div><strong><span class="ml-count" data-count-to="<?php echo (int)$homeStats['farmers']; ?>" data-count-suffix=" farmers"><?php echo (int)$homeStats['farmers']; ?> farmers</span></strong><span>ready for the market</span></div>
                </span>
            </div>
            <div class="mlp-split-body" data-reveal="right">
                <div class="mlp-kicker">Built for Both Sides</div>
                <h2 class="mlp-title">One platform, two sides, a market day that works for everybody</h2>
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="mlp-mini"><i class="bi bi-person-vcard"></i> For Farmers</h6>
                        <ul class="mlp-tick">
                            <li>Free stall listing, no commission on direct sales</li>
                            <li>Weekly stock and sold-out control from your dashboard</li>
                            <li>Order notifications the moment a pre-order is placed</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mlp-mini"><i class="bi bi-bag-heart"></i> For Customers</h6>
                        <ul class="mlp-tick">
                            <li>One basket across multiple farmers and stalls</li>
                            <li>Modify or cancel free until the 24-hour cutoff</li>
                            <li>Rate the produce and the farmer after every pickup</li>
                        </ul>
                    </div>
                </div>
                <a href="<?php echo ML_asset('signup'); ?>" class="mlp-btn-solid mt-4"><i class="bi bi-person-plus"></i> Join MarketLink Free</a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     Draggable Card Deck Reviews Section (Matched to Image 3)
     Wide width + Stacked card deck + Drag & Swipe interaction
     ============================================================ -->
<section class="deck-reviews-section ml-reveal">
    <div class="deck-reviews-container">
        
        <!-- Header: "Review" on Left, < > on Right -->
        <div class="deck-reviews-header">
            <h3 class="deck-reviews-title">Review</h3>
            <div class="deck-nav-btns">
                <button type="button" class="deck-nav-btn" id="deckPrevBtn" aria-label="Previous review" title="Previous review">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="deck-nav-btn" id="deckNextBtn" aria-label="Next review" title="Next review">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- 3D Stacked Card Deck Stage -->
        <div class="deck-reviews-stage" id="deckStage">
            
            <!-- Background Layered Card (Bottom Layer, Tilted Left) -->
            <div class="deck-bg-card-bottom" id="deckBgCard2"></div>

            <!-- Background Layered Card (Middle Layer, Tilted Right) -->
            <div class="deck-bg-card-mid" id="deckBgCard1"></div>

            <!-- Front Draggable Card (Active Review) -->
            <div class="deck-front-card" id="deckFrontCard">
                <p class="deck-quote-text" id="deckQuoteText">
                    The entire setup with LeanLeaf was painless and straightforward. They go the extra mile to reach out to you and make sure you have everything you need
                </p>

                <div class="deck-user-meta">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=150&q=80" 
                         id="deckUserAvatar" 
                         class="deck-user-avatar" 
                         alt="Stacy">
                    <div class="deck-user-details">
                        <h6 class="deck-user-name" id="deckUserName">Stacy, 32 y.o.</h6>
                        <span class="deck-user-badge" id="deckUserBadge">Positive effect in 2 months</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Pagination Dots -->
        <div class="deck-dots" id="deckDots"></div>

    </div>
</section>

<!-- Drag & Deck Controller Script -->
<script>
(function () {
    var reviews = [
        {
            quote: "The entire setup with LeanLeaf was painless and straightforward. They go the extra mile to reach out to you and make sure you have everything you need",
            name: "Stacy, 32 y.o.",
            badge: "Positive effect in 2 months",
            avatar: "https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=150&q=80"
        },
        {
            quote: "I used to guess how much to harvest each weekend. With pre-orders on MarketLink, my leftovers dropped to almost nothing. When the pre-orders come in, I know exactly what to harvest and what to price.",
            name: "Zubair Ahmed, 45 y.o.",
            badge: "Approved Local Farmer",
            avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80"
        },
        {
            quote: "The slot system means no queue at the stall. I pre-order on Sunday, walk in on Tuesday morning, pay the farmer and collect produce packed that same morning. The whole pickup takes five minutes.",
            name: "Ayesha Malik, 29 y.o.",
            badge: "Weekend Market Shopper",
            avatar: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80"
        },
        {
            quote: "The freshness is incomparable to supermarket produce. You can taste the soil and sun in every tomato and strawberry. It feels great supporting real local families directly.",
            name: "Marcus Chen, 38 y.o.",
            badge: "Healthy Kitchen Chef",
            avatar: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80"
        },
        {
            quote: "Artisan sourdough and pure pasture cheeses right at our neighborhood pickup point. Transparent pricing, wonderful farmers, and a super smooth order experience every week.",
            name: "Elena Rostova, 34 y.o.",
            badge: "18 Orders Completed",
            avatar: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80"
        }
    ];

    var currentIdx = 0;
    var isDragging = false;
    var startX = 0;
    var currentX = 0;
    var autoTimer = null;

    var frontCard = document.getElementById("deckFrontCard");
    var quoteEl = document.getElementById("deckQuoteText");
    var nameEl = document.getElementById("deckUserName");
    var badgeEl = document.getElementById("deckUserBadge");
    var avatarEl = document.getElementById("deckUserAvatar");
    var bgCard1 = document.getElementById("deckBgCard1");
    var bgCard2 = document.getElementById("deckBgCard2");
    var dotsContainer = document.getElementById("deckDots");
    var prevBtn = document.getElementById("deckPrevBtn");
    var nextBtn = document.getElementById("deckNextBtn");

    // Render pagination dots
    if (dotsContainer) {
        dotsContainer.innerHTML = "";
        reviews.forEach(function (_, i) {
            var dot = document.createElement("button");
            dot.type = "button";
            dot.className = "deck-dot" + (i === 0 ? " active" : "");
            dot.setAttribute("aria-label", "Go to review " + (i + 1));
            dot.addEventListener("click", function () {
                goTo(i, i > currentIdx ? "left" : "right");
                restartAuto();
            });
            dotsContainer.appendChild(dot);
        });
    }

    function updateDots() {
        if (!dotsContainer) return;
        var dots = dotsContainer.querySelectorAll(".deck-dot");
        dots.forEach(function (d, i) {
            d.classList.toggle("active", i === currentIdx);
        });
    }

    function renderContent(idx) {
        var r = reviews[idx];
        quoteEl.textContent = r.quote;
        nameEl.textContent = r.name;
        badgeEl.textContent = r.badge;
        avatarEl.src = r.avatar;
        avatarEl.alt = r.name;
        updateDots();
    }

    function goTo(idx, direction) {
        if (isDragging) return;
        direction = direction || "left";
        var swipeClass = direction === "left" ? "swipe-left" : "swipe-right";
        frontCard.classList.add(swipeClass);

        setTimeout(function () {
            currentIdx = ((idx % reviews.length) + reviews.length) % reviews.length;
            renderContent(currentIdx);

            frontCard.style.transition = "none";
            frontCard.style.transform = (direction === "left" ? "translateX(60px) rotate(6deg)" : "translateX(-60px) rotate(-6deg)");
            frontCard.classList.remove(swipeClass);

            // Reflow
            void frontCard.offsetWidth;

            frontCard.style.transition = "transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.3s ease";
            frontCard.style.transform = "translateX(0) rotate(0deg)";
            frontCard.style.opacity = "1";
        }, 220);
    }

    function nextReview() { goTo(currentIdx + 1, "left"); }
    function prevReview() { goTo(currentIdx - 1, "right"); }

    function startAuto() {
        stopAuto();
        autoTimer = setInterval(nextReview, 7000);
    }
    function stopAuto() {
        if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
    }
    function restartAuto() {
        stopAuto();
        startAuto();
    }

    if (prevBtn) prevBtn.addEventListener("click", function () { prevReview(); restartAuto(); });
    if (nextBtn) nextBtn.addEventListener("click", function () { nextReview(); restartAuto(); });

    // Drag / Touch Engine
    function onPointerDown(e) {
        if (e.button !== undefined && e.button !== 0) return;
        isDragging = true;
        startX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
        currentX = startX;
        frontCard.classList.add("is-dragging");
        stopAuto();
    }

    function onPointerMove(e) {
        if (!isDragging) return;
        currentX = e.clientX || (e.touches && e.touches[0].clientX) || currentX;
        var diffX = currentX - startX;

        var rot = diffX * 0.045;
        frontCard.style.transform = "translateX(" + diffX + "px) rotate(" + rot + "deg)";

        // Responsive background card reaction
        if (bgCard1) {
            bgCard1.style.transform = "rotate(" + (2.8 - diffX * 0.015) + "deg) translateY(-4px) scale(0.985)";
        }
        if (bgCard2) {
            bgCard2.style.transform = "rotate(" + (-3.5 + diffX * 0.015) + "deg) translateY(-8px) scale(0.97)";
        }
    }

    function onPointerUp() {
        if (!isDragging) return;
        isDragging = false;
        frontCard.classList.remove("is-dragging");

        var diffX = currentX - startX;
        var threshold = 65;

        if (diffX < -threshold) {
            goTo(currentIdx + 1, "left");
        } else if (diffX > threshold) {
            goTo(currentIdx - 1, "right");
        } else {
            frontCard.style.transition = "transform 0.35s cubic-bezier(0.2, 0.8, 0.2, 1)";
            frontCard.style.transform = "translateX(0) rotate(0deg)";
        }

        if (bgCard1) bgCard1.style.transform = "rotate(2.8deg) translateY(-4px) scale(0.985)";
        if (bgCard2) bgCard2.style.transform = "rotate(-3.5deg) translateY(-8px) scale(0.97)";

        restartAuto();
    }

    frontCard.addEventListener("pointerdown", onPointerDown);
    window.addEventListener("pointermove", onPointerMove);
    window.addEventListener("pointerup", onPointerUp);
    window.addEventListener("pointercancel", onPointerUp);

    // Initial load
    renderContent(0);
    startAuto();
})();
</script>

<section class="ml-cta-full-section ml-reveal">
    <div class="ml-cta-full-inner">
        <div class="ml-cta-full-overlay"></div>
        <div class="container position-relative" style="z-index: 2;">
            <div class="text-center mx-auto" style="max-width: 820px;">
                <span class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-25 text-white small fw-semibold">
                    <i class="fa-solid fa-basket-shopping" style="color: #d5eab8;"></i> Fresh Organic Pre-Orders
                </span>
                <h2 class="display-5 fw-bold text-white mb-3" style="font-family: 'Fraunces', Georgia, serif; letter-spacing: -0.5px;">Ready to pre-order your next market day?</h2>
                <p class="lead text-white text-opacity-90 mb-4" style="font-size: 1.08rem; line-height: 1.6;">
                    Join MarketLink today — connect with verified local farmers, reserve your basket and collect it fresh at the stall.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?php echo ML_asset('signup'); ?>" class="ml-cta-btn-main">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Create Free Account</span>
                    </a>
                    <a href="<?php echo ML_asset('markets'); ?>" class="ml-cta-btn-outline">
                        <i class="fa-solid fa-store"></i>
                        <span>Browse Markets</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Native Zero-Load Scroll Reveal Animation Controller -->
<script>
(function () {
    function reveal(el) {
        /* Deliberately no will-change. Promoting every section and every
           staggered card at the same time exhausts Chrome's texture memory,
           and Chrome then downsamples the layers so the whole page renders
           blurry. A CSS transition is already composited without the hint. */
        el.classList.add("is-revealed");
    }

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    reveal(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: "0px 0px -20px 0px" });

        document.querySelectorAll(".ml-reveal").forEach(function (el) {
            observer.observe(el);
        });
    } else {
        document.querySelectorAll(".ml-reveal").forEach(reveal);
    }
})();
</script>

<?php include __DIR__ . '/../../public/components/footer.php'; ?>
