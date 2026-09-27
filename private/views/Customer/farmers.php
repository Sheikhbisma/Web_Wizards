<?php
$pageTitle = 'Verified Farmers - MarketLink';
include __DIR__ . '/../../../public/components/header.php';

$marketFilter = (int)($_GET['market'] ?? 0);

$farms = selectData($pdo, "SELECT f.*, u.email, u.contact,
    (SELECT COUNT(*) FROM market_farmer AS mf WHERE mf.farmer_id = f.farmer_id) AS markets,
    (SELECT COUNT(*) FROM products AS q WHERE q.farmer_id = f.farmer_id AND q.is_available = 1 AND q.is_sold_out = 0 AND q.stock_quantity > 0) AS products
    FROM farmers AS f
    INNER JOIN users AS u ON u.id = f.user_id AND u.status = 'active'
    WHERE f.approval_status = 'approved'" . ($marketFilter ? " AND f.farmer_id IN (SELECT farmer_id FROM market_farmer WHERE market_id = ?)" : "") . "
    ORDER BY f.avg_rating DESC, f.stall_name ASC", $marketFilter ? [$marketFilter] : []);

$markets = selectData($pdo, "SELECT market_id, market_name FROM markets WHERE is_active = 1 ORDER BY market_name");

$favoriteFarmerIds = [];
if (!empty($_SESSION['loggedIn']) && ($_SESSION['role'] ?? '') === 'customer') {
    $customerRows = selectData($pdo, "SELECT customer_id FROM customers WHERE user_id = ?", [$_SESSION['user_id']]);
    if (!empty($customerRows)) {
        $favoriteRows = selectData($pdo, "SELECT farmer_id FROM favorites WHERE customer_id = ? AND farmer_id IS NOT NULL AND product_id IS NULL", [(int)$customerRows[0]['customer_id']]);
        $favoriteFarmerIds = array_map('intval', array_column($favoriteRows, 'farmer_id'));
    }
}

$farmerAvatars = [
    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80',
    'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80',
    'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80',
    'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=300&q=80',
    'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80',
];
?>

<style>
/* Video Hero with Parallax Animation */
.farmers-video-hero {
    position: relative;
    overflow: hidden;
    min-height: 480px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #142211;
    color: #ffffff;
}
.farmers-video-wrap {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none;
    z-index: 1;
}
.farmers-hero-video {
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
.farmers-video-overlay {
    position: absolute;
    inset: 0;
    z-index: 2;
    background: linear-gradient(135deg, rgba(14, 24, 10, 0.65) 0%, rgba(28, 46, 18, 0.45) 50%, rgba(10, 18, 7, 0.72) 100%);
}

.farmers-hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.35);
    color: #e5f4d8;
    padding: 5px 18px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
}

/* ============================================================
   FARMER ID BADGE CARD DESIGN (Matched to User Reference Image)
   ============================================================ */
.farmer-id-card-wrapper {
    position: relative;
    background: linear-gradient(180deg, #142311 0%, #1c3316 60%, #12200f 100%);
    border: 1.5px solid #3d5a27;
    border-radius: 26px;
    overflow: hidden;
    box-shadow: 0 16px 40px rgba(18, 32, 14, 0.25);
    padding: 30px 24px 26px;
    text-align: center;
    color: #ffffff;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
}
.farmer-id-card-wrapper:hover {
    transform: translateY(-6px);
    border-color: #a0bc79;
    box-shadow: 0 22px 50px rgba(18, 32, 14, 0.38);
}

/* Top Geometric Canopy Mesh */
.farmer-id-canopy {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 140px;
    background: radial-gradient(circle at 50% -10%, rgba(160, 188, 121, 0.35) 0%, rgba(74, 95, 49, 0.12) 50%, transparent 80%);
    pointer-events: none;
    z-index: 1;
}
.farmer-id-canopy::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100%;
    background-image: radial-gradient(rgba(160, 188, 121, 0.22) 1.5px, transparent 1.5px);
    background-size: 16px 16px;
    opacity: 0.6;
}

/* Avatar Center with Glowing Border Ring */
.farmer-id-avatar-wrap {
    position: relative;
    z-index: 2;
    width: 110px;
    height: 110px;
    margin: 10px auto 16px;
    border-radius: 50%;
    padding: 4px;
    background: linear-gradient(135deg, #a0bc79, #4a5f31);
    box-shadow: 0 0 0 4px rgba(160, 188, 121, 0.2), 0 10px 25px rgba(0, 0, 0, 0.4);
}
.farmer-id-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #142311;
}

.farmer-id-name {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.45rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 2px;
    position: relative;
    z-index: 2;
}
.farmer-id-role {
    font-size: 0.82rem;
    color: #a0bc79;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    display: block;
    margin-bottom: 18px;
    position: relative;
    z-index: 2;
}

/* ID Badge Data Rows */
.farmer-id-info-table {
    background: rgba(0, 0, 0, 0.22);
    border: 1px solid rgba(160, 188, 121, 0.2);
    border-radius: 16px;
    padding: 14px 16px;
    margin-bottom: 18px;
    text-align: left;
    font-size: 0.82rem;
    position: relative;
    z-index: 2;
}
.farmer-id-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.08);
}
.farmer-id-row:last-child {
    border-bottom: none;
}
.farmer-id-label {
    color: #c7dfb4;
    font-weight: 600;
}
.farmer-id-val {
    color: #ffffff;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
}

/* Stats Chips Row */
.farmer-id-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 20px;
    position: relative;
    z-index: 2;
}
.farmer-id-stat-box {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 8px 4px;
}
.farmer-id-stat-num {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.15rem;
    font-weight: 700;
    color: #d4f28f;
    line-height: 1;
}
.farmer-id-stat-txt {
    font-size: 0.68rem;
    color: #b0c9a0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-top: 3px;
}

/* Bottom Verification Signature Line */
.farmer-id-sign-line {
    position: relative;
    z-index: 2;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    padding-top: 8px;
    margin-bottom: 16px;
    font-family: 'Caveat', cursive, sans-serif;
    font-size: 1rem;
    color: #a0bc79;
    letter-spacing: 1px;
}

/* Action Button */
.farmer-id-btn {
    background: #d4f28f;
    color: #142311 !important;
    font-weight: 700;
    font-size: 0.88rem;
    padding: 10px 20px;
    border-radius: 50px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 6px 18px rgba(160, 188, 121, 0.25);
    transition: all 0.25s ease;
    position: relative;
    z-index: 2;
    border: none;
    width: 100%;
}
.farmer-id-btn:hover {
    background: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.35);
}
</style>

<!-- ============================================================
     SECTION 1: HERO SECTION WITH VIDEO & PARALLAX SCROLL
     ============================================================ -->
<section class="food-hero-section full-width-hero">
    
    <!-- Outer Presentation Frame (Full Width) -->
    <div class="hero-device-frame">
        
        <!-- Top-Left Corner Decorative Image.
             Starts on slide 2's photo, which is what renderSlide(0) expects,
             so there is no flash of an unrelated image on first paint. -->
        <div class="hero-top-left-decor">
            <img src="<?php echo ML_asset('Uploads/img/farmers-slide2.jpg'); ?>" id="heroTopLeftImg" alt="Right Time, Better Quality" class="hero-corner-img">
        </div>

        <!-- Green Backdrop (Circular from left, solid green on right) -->
        <div class="hero-backdrop-curve-green" aria-hidden="true"></div>

        <!-- 2-Column Hero Stage Grid -->
        <div class="hero-main-grid">
            
            <!-- Left Category Info Column -->
            <div class="hero-dish-info">
                <span class="farmers-hero-kicker" id="heroKicker">Harvesting</span>
                <h1 class="hero-dish-title" id="heroDishTitle">Fresh From The Farm</h1>
                <p class="hero-dish-desc" id="heroDishDesc">
                    Our harvesting process begins with carefully selecting fresh and healthy crops at the right stage of maturity. We make sure every product reaches the market with its natural freshness and quality.
                </p>

                <!-- Orange Pill CTA Button -->
                <div class="hero-dish-actions">
                    <a href="<?php echo ML_asset('products'); ?>" class="hero-btn-order" id="heroOrderBtn">
                        ORDER NOW
                    </a>
                </div>
            </div>

            <!-- Right Showcase: Orbit Arc + 5 Category Dishes + Center Featured Plate + Tags + Next Arrow -->
            <div class="hero-showcase" id="heroShowcase">

                <!-- Dashed Circular Orbit Curve SVG -->
                <svg class="hero-orbit-svg" viewBox="0 0 440 440" fill="none">
                    <circle cx="220" cy="220" r="185" stroke="#8ca86a" stroke-width="2" stroke-dasharray="6 6" opacity="0.55" />
                </svg>

                <!-- 5 Orbit Dishes Along the Arc: one per harvesting slide -->
                <div class="hero-orbit-nodes" id="heroOrbitNodes">
                    <!-- Node 0: Fresh From The Farm -->
                    <div class="orbit-node active" data-index="0" style="left: 20%; top: 12%;" title="Fresh From The Farm">
                        <div class="node-plate">
                            <img src="<?php echo ML_asset('Uploads/img/farmers-slide1.jpg'); ?>" alt="Fresh From The Farm">
                        </div>
                    </div>

                    <!-- Node 1: Right Time, Better Quality -->
                    <div class="orbit-node" data-index="1" style="left: 8%; top: 38%;" title="Right Time, Better Quality">
                        <div class="node-plate">
                            <img src="<?php echo ML_asset('Uploads/img/farmers-slide2.jpg'); ?>" alt="Right Time, Better Quality">
                        </div>
                    </div>

                    <!-- Node 2: Carefully Picked, Carefully Handled -->
                    <div class="orbit-node" data-index="2" style="left: 6%; top: 68%;" title="Carefully Picked, Carefully Handled">
                        <div class="node-plate">
                            <img src="<?php echo ML_asset('Uploads/img/farmers-slide3.jpg'); ?>" alt="Carefully Picked, Carefully Handled">
                        </div>
                    </div>

                    <!-- Node 3: From Harvest To Market -->
                    <div class="orbit-node" data-index="3" style="left: 22%; top: 88%;" title="From Harvest To Market">
                        <div class="node-plate">
                            <img src="<?php echo ML_asset('Uploads/img/farmers-slide4.jpg'); ?>" alt="From Harvest To Market">
                        </div>
                    </div>

                    <!-- Node 4: Freshness You Can Trust -->
                    <div class="orbit-node" data-index="4" style="left: 48%; top: 96%;" title="Freshness You Can Trust">
                        <div class="node-plate">
                            <img src="<?php echo ML_asset('Uploads/img/farmers-slide5.webp'); ?>" alt="Freshness You Can Trust">
                        </div>
                    </div>
                </div>

                <!-- Center Main Featured Plate (Large Dish) -->
                <div class="hero-main-plate" id="heroMainPlate">
                    <img src="<?php echo ML_asset('Uploads/img/farmers-slide1.jpg'); ?>"
                         id="heroMainPlateImg"
                         alt="Fresh From The Farm"
                         class="main-plate-img">
                </div>

                <!-- Right Vertical Tag Attributes List (Matched per slide) -->
                <ul class="hero-right-tags" id="heroRightTags">
                    <li data-tag-idx="0">Fresh Quality</li>
                    <li data-tag-idx="1" class="active-tag">Farm Fresh</li>
                    <li data-tag-idx="2">Natural Goodness</li>
                    <li data-tag-idx="3">Carefully Picked</li>
                </ul>

                <button type="button" class="hero-arrow-next" id="heroArrowNext" aria-label="Next slide" title="Next slide">
                    <i class="bi bi-arrow-right"></i>
                </button>

            </div>

        </div>

       

    </div>
</section>

<script>
(function () {
    /* The five harvesting stages. `kicker` is the shared "Harvesting"
       eyebrow, `tags` are the four short badges shown down the right-hand
       side, and `mainImg` is the large centre plate. `cornerImg` points at
       the NEXT slide rather than the current one, so the small top-left
       accent never shows the same photo twice on screen; it wraps back to
       the first image on the last slide. */
    var slides = [
        {
            kicker: "Harvesting",
            title: "Fresh From The Farm",
            desc: "Our harvesting process begins with carefully selecting fresh and healthy crops at the right stage of maturity. We make sure every product reaches the market with its natural freshness and quality.",
            tags: ["Fresh Quality", "Farm Fresh", "Natural Goodness", "Carefully Picked"],
            activeTagIdx: 1,
            mainImg: "<?php echo ML_asset('Uploads/img/farmers-slide1.jpg'); ?>",
            cornerImg: "<?php echo ML_asset('Uploads/img/farmers-slide2.jpg'); ?>",
            link: "<?php echo ML_asset('products'); ?>"
        },
        {
            kicker: "Harvesting",
            title: "Right Time, Better Quality",
            desc: "Crops are harvested at the right time to maintain their freshness, taste, texture, and nutritional value. Our careful approach helps preserve the natural quality of every product.",
            tags: ["Timely Harvest", "Fresh Produce", "Rich Nutrition", "Natural Taste"],
            activeTagIdx: 0,
            mainImg: "<?php echo ML_asset('Uploads/img/farmers-slide2.jpg'); ?>",
            cornerImg: "<?php echo ML_asset('Uploads/img/farmers-slide3.jpg'); ?>",
            link: "<?php echo ML_asset('products'); ?>"
        },
        {
            kicker: "Harvesting",
            title: "Carefully Picked, Carefully Handled",
            desc: "After harvesting, fresh produce is handled with care to reduce damage and maintain its quality. Each product goes through a careful selection process before reaching the market.",
            tags: ["Quality Checked", "Fresh & Clean", "Careful Handling", "Premium Produce"],
            activeTagIdx: 2,
            mainImg: "<?php echo ML_asset('Uploads/img/farmers-slide3.jpg'); ?>",
            cornerImg: "<?php echo ML_asset('Uploads/img/farmers-slide4.jpg'); ?>",
            link: "<?php echo ML_asset('products'); ?>"
        },
        {
            kicker: "Harvesting",
            title: "From Harvest To Market",
            desc: "Our harvested products are sorted, cleaned, and prepared for delivery. This process helps ensure that customers receive fresh and quality produce directly from the farming community.",
            tags: ["Farm To Market", "Clean Produce", "Safe Packaging", "Fresh Delivery"],
            activeTagIdx: 0,
            mainImg: "<?php echo ML_asset('Uploads/img/farmers-slide4.jpg'); ?>",
            cornerImg: "<?php echo ML_asset('Uploads/img/farmers-slide5.webp'); ?>",
            link: "<?php echo ML_asset('products'); ?>"
        },
        {
            kicker: "Harvesting",
            title: "Freshness You Can Trust",
            desc: "From the moment crops are harvested to the time they reach our customers, we focus on freshness, quality, and careful handling.",
            tags: ["100% Fresh", "Quality Assured", "Naturally Good", "Customer Ready"],
            activeTagIdx: 3,
            mainImg: "<?php echo ML_asset('Uploads/img/farmers-slide5.webp'); ?>",
            cornerImg: "<?php echo ML_asset('Uploads/img/farmers-slide1.jpg'); ?>",
            link: "<?php echo ML_asset('products'); ?>"
        }
    ];

    var currentIndex = 0;
    var currentOrbitAngle = 0;
    var timer = null;

    var kickerEl = document.getElementById("heroKicker");
    var titleEl = document.getElementById("heroDishTitle");
    var descEl = document.getElementById("heroDishDesc");
    var orderBtn = document.getElementById("heroOrderBtn");
    var mainImg = document.getElementById("heroMainPlateImg");
    var cornerImgEl = document.getElementById("heroTopLeftImg");
    var showcase = document.getElementById("heroShowcase");
    var orbitNodesEl = document.getElementById("heroOrbitNodes");
    var orbitSvg = document.querySelector(".hero-orbit-svg");
    var nodes = document.querySelectorAll(".orbit-node");
    var tagItems = document.querySelectorAll("#heroRightTags li");
    var arrowNext = document.getElementById("heroArrowNext");

    function renderSlide(idx, animate) {
        var targetIndex = ((idx % slides.length) + slides.length) % slides.length;
        
        // Bounded half-circle arc rotation (oscillates smoothly between -28deg and +28deg)
        currentOrbitAngle = (targetIndex - 2) * 14;
        currentIndex = targetIndex;
        var s = slides[currentIndex];

        // Physical rotation of orbit nodes strictly along the half-circle trajectory
        if (orbitNodesEl) {
            orbitNodesEl.style.transform = "rotate(" + currentOrbitAngle + "deg)";
            var nodePlates = orbitNodesEl.querySelectorAll(".node-plate");
            nodePlates.forEach(function (plate) {
                // Counter-rotate plate dishes so food remains perfectly upright
                plate.style.transform = "rotate(" + (-currentOrbitAngle) + "deg)";
            });
        }
        if (orbitSvg) {
            orbitSvg.style.transform = "translate(-50%, -50%) rotate(" + (currentOrbitAngle * 0.4) + "deg)";
        }

        // Orbit nodes active class
        nodes.forEach(function (node, nIdx) {
            node.classList.toggle("active", nIdx === currentIndex);
        });

        // Right tags highlight
        tagItems.forEach(function (tEl, tIdx) {
            if (s.tags[tIdx]) {
                tEl.textContent = s.tags[tIdx];
            }
            tEl.classList.toggle("active-tag", tIdx === s.activeTagIdx);
        });

        if (animate) {
            if (cornerImgEl) {
                cornerImgEl.style.opacity = "0";
                cornerImgEl.style.transform = "scale(0.9) translateY(-6px)";
            }
            mainImg.style.opacity = "0";
            mainImg.style.transform = "scale(0.88) rotate(-15deg)";
            titleEl.style.opacity = "0";
            titleEl.style.transform = "translateY(8px)";
            descEl.style.opacity = "0";

            setTimeout(function () {
                if (cornerImgEl && s.cornerImg) {
                    cornerImgEl.src = s.cornerImg;
                    cornerImgEl.alt = s.title;
                    cornerImgEl.style.opacity = "1";
                    cornerImgEl.style.transform = "scale(1) translateY(0)";
                }
                if (kickerEl) kickerEl.textContent = s.kicker;
                titleEl.textContent = s.title;
                descEl.textContent = s.desc;
                if (orderBtn) orderBtn.href = s.link;
                mainImg.src = s.mainImg;
                mainImg.alt = s.title;

                mainImg.style.opacity = "1";
                mainImg.style.transform = "scale(1) rotate(0deg)";
                titleEl.style.opacity = "1";
                titleEl.style.transform = "translateY(0)";
                descEl.style.opacity = "1";
            }, 250);
        } else {
            if (cornerImgEl && s.cornerImg) {
                cornerImgEl.src = s.cornerImg;
                cornerImgEl.alt = s.title;
            }
            if (kickerEl) kickerEl.textContent = s.kicker;
            titleEl.textContent = s.title;
            descEl.textContent = s.desc;
            if (orderBtn) orderBtn.href = s.link;
            mainImg.src = s.mainImg;
            mainImg.alt = s.title;
        }
    }

    function nextSlide() { renderSlide(currentIndex + 1, true); }
    function prevSlide() { renderSlide(currentIndex - 1, true); }

    function startAuto() {
        stopAuto();
        timer = setInterval(nextSlide, 5500);
    }
    function stopAuto() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    // Node plate clicks
    nodes.forEach(function (node) {
        node.addEventListener("click", function () {
            var i = parseInt(node.getAttribute("data-index"), 10);
            renderSlide(i, true);
            startAuto();
        });
    });

    // Arrow button click
    if (arrowNext) {
        arrowNext.addEventListener("click", function () {
            nextSlide();
            startAuto();
        });
    }

    // Pause auto on hover
    if (showcase) {
        showcase.addEventListener("mouseenter", stopAuto);
        showcase.addEventListener("mouseleave", startAuto);
    }

    // Keyboard support: the showcase is focusable so the arrow can be
    // reached without a pointer, and left/right cycle the stages.
    if (showcase) {
        showcase.setAttribute("tabindex", "0");
        showcase.addEventListener("keydown", function (e) {
            if (e.key === "ArrowRight") { nextSlide(); startAuto(); }
            else if (e.key === "ArrowLeft") { prevSlide(); startAuto(); }
        });
    }

    // Stop the carousel entirely once the tab is hidden, so a background
    // tab is not left running a 5.5s timer that fires into a dead page.
    document.addEventListener("visibilitychange", function () {
        if (document.hidden) { stopAuto(); }
        else { startAuto(); }
    });

    // Initial render
    renderSlide(0, false);
    startAuto();
})();
</script>

<!-- ============================================================
     SECTION 2: WORK PROCESS — HOW TO USE GROW BASE
     4 Circular Images on ONE middle row + Blue Wavy Ribbon Weave + Alternating Content
     ============================================================ -->
<section class="work-process-section py-5">
    <div class="container text-center py-4">
        <span class="process-kicker-badge"><i class="fa-solid fa-arrows-rotate me-1"></i> Work Process</span>
        <h2 class="process-main-title mt-2 mb-5">How To Use Grow Base</h2>

        <div class="process-wavy-container position-relative">
            <!-- SVG Wavy Connecting Ribbon that weaves around the 4 circular images in the middle row -->
            <svg class="process-wavy-svg d-none d-lg-block" viewBox="0 0 1200 220" fill="none" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="processGreenGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#4a5f31"/>
                        <stop offset="50%" stop-color="#a0bc79"/>
                        <stop offset="100%" stop-color="#4a5f31"/>
                    </linearGradient>
                </defs>
                <path d="M 0,110 C 60,110 80,185 150,185 C 220,185 380,35 450,35 C 520,35 680,185 750,185 C 820,185 980,35 1050,35 C 1120,35 1140,110 1200,110" stroke="url(#processGreenGrad)" stroke-width="8" stroke-linecap="round"/>
            </svg>

            <div class="row g-4 justify-content-between position-relative" style="z-index: 2;">
                
                <!-- Node 1: Content BELOW Image (Bottom Content) -->
                <div class="col-12 col-md-6 col-lg-3 process-col">
                    <div class="process-node-card process-type-bottom">
                        <!-- Top spacer slot (empty on desktop to keep middle row aligned) -->
                        <div class="process-slot-top d-none d-lg-flex"></div>

                        <!-- Circular Image in Center Row (Cradled by bottom wave) -->
                        <div class="process-slot-middle">
                            <div class="process-img-circle ring-bottom">
                                <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=400&q=80" alt="Plant Establishment">
                            </div>
                        </div>

                        <!-- Content below image -->
                        <div class="process-slot-bottom">
                            <span class="process-step-num">1</span>
                            <h5 class="process-step-title">Plant Establishment</h5>
                            <p class="process-step-desc">Trees, flowers, vegetables, fruit, herbs — Incorporate 1/2-1 inch of GrowBase to depth of 6-12 inches of existing soil.</p>
                            <a href="#farmersDirectory" class="process-learn-btn">Learn More ↗</a>
                        </div>
                    </div>
                </div>

                <!-- Node 2: Content ABOVE Image (Top Content) -->
                <div class="col-12 col-md-6 col-lg-3 process-col">
                    <div class="process-node-card process-type-top">
                        <!-- Content above image -->
                        <div class="process-slot-top">
                            <span class="process-step-num">2</span>
                            <h5 class="process-step-title">Turf Maintenance</h5>
                            <p class="process-step-desc">Trees, flowers, vegetables, fruit, herbs — Incorporate 1/2-1 inch of GrowBase to depth of 6-12 inches of existing soil.</p>
                            <a href="#farmersDirectory" class="process-learn-btn">Learn More ↗</a>
                        </div>

                        <!-- Circular Image in Center Row (Cradled by top wave) -->
                        <div class="process-slot-middle">
                            <div class="process-img-circle ring-top">
                                <img src="https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&w=400&q=80" alt="Turf Maintenance">
                            </div>
                        </div>

                        <!-- Bottom spacer slot (empty on desktop to keep middle row aligned) -->
                        <div class="process-slot-bottom d-none d-lg-flex"></div>
                    </div>
                </div>

                <!-- Node 3: Content BELOW Image (Bottom Content) -->
                <div class="col-12 col-md-6 col-lg-3 process-col">
                    <div class="process-node-card process-type-bottom">
                        <!-- Top spacer slot (empty on desktop to keep middle row aligned) -->
                        <div class="process-slot-top d-none d-lg-flex"></div>

                        <!-- Circular Image in Center Row (Cradled by bottom wave) -->
                        <div class="process-slot-middle">
                            <div class="process-img-circle ring-bottom">
                                <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=400&q=80" alt="Potting Soil Blends">
                            </div>
                        </div>

                        <!-- Content below image -->
                        <div class="process-slot-bottom">
                            <span class="process-step-num">3</span>
                            <h5 class="process-step-title">Potting Soil Blends</h5>
                            <p class="process-step-desc">Uniformly blend GrowBase into a standard growing media. Place in pot and plant, then water.</p>
                            <a href="#farmersDirectory" class="process-learn-btn">Learn More ↗</a>
                        </div>
                    </div>
                </div>

                <!-- Node 4: Content ABOVE Image (Top Content) -->
                <div class="col-12 col-md-6 col-lg-3 process-col">
                    <div class="process-node-card process-type-top">
                        <!-- Content above image -->
                        <div class="process-slot-top">
                            <span class="process-step-num">4</span>
                            <h5 class="process-step-title">Top Dressing</h5>
                            <p class="process-step-desc">Spread 1/4" Grow Base evenly on your lawn to improve soil quality without disturbing turf growth.</p>
                            <a href="#farmersDirectory" class="process-learn-btn">Learn More ↗</a>
                        </div>

                        <!-- Circular Image in Center Row (Cradled by top wave) -->
                        <div class="process-slot-middle">
                            <div class="process-img-circle ring-top">
                                <img src="https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=400&q=80" alt="Top Dressing">
                            </div>
                        </div>

                        <!-- Bottom spacer slot (empty on desktop to keep middle row aligned) -->
                        <div class="process-slot-bottom d-none d-lg-flex"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<style>
/* Farmer directory: clear filter rail and compact searchable cards */
.farmer-directory-section{background:#f6f8f5;padding:clamp(36px,5vw,68px) 0}
.farmer-directory-heading{margin-bottom:24px}.farmer-directory-heading .section-kicker{color:#178a53;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-size:.78rem}
.farmer-directory-heading h2{margin:8px 0;color:#14281b;font:700 clamp(1.7rem,3vw,2.35rem)/1.15 Poppins,sans-serif}.farmer-directory-heading p{max-width:62ch;margin:0;color:#5f7a67}
.farmer-directory-layout{display:grid;grid-template-columns:minmax(220px,270px) minmax(0,1fr);gap:22px;align-items:start}
.farmer-filter-panel,.farmer-directory-list{background:#fff;border:1px solid #e6ede6;border-radius:18px;box-shadow:0 12px 30px rgba(23,47,31,.07)}
.farmer-filter-panel{padding:20px;position:sticky;top:104px}.farmer-filter-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;color:#14281b;font-weight:700}
.farmer-filter-panel label{display:block;color:#334b3a;font-size:.84rem;font-weight:600;margin:12px 0 6px}.farmer-filter-panel select,.farmer-search-input{width:100%;min-height:44px;border:1px solid #dce7dd;border-radius:10px;background:#fff;padding:9px 12px;color:#14281b;outline:none}.farmer-filter-panel select:focus,.farmer-search-input:focus{border-color:#178a53;box-shadow:0 0 0 3px rgba(23,138,83,.12)}
.farmer-filter-actions{display:flex;gap:9px;margin-top:16px}.farmer-filter-apply,.farmer-filter-reset{min-height:42px;border-radius:10px;padding:9px 14px;font-weight:700;text-decoration:none;text-align:center}.farmer-filter-apply{flex:1;background:#178a53;color:#fff;border:0}.farmer-filter-apply:hover{background:#0f4d30;color:#fff}.farmer-filter-reset{border:1px solid #dce7dd;color:#49624f}
.farmer-directory-list{padding:16px}.farmer-directory-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:0 0 14px}.farmer-directory-count{color:#5f7a67;font-size:.9rem}.farmer-search-input{max-width:280px}
.farmer-result-list{display:grid;gap:12px}.farmer-result-card{display:grid;grid-template-columns:112px minmax(0,1fr) auto;gap:16px;align-items:center;padding:14px;border:1px solid #e6ede6;border-radius:15px;background:#fff;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}.farmer-result-card[hidden]{display:none}.farmer-result-card:hover{transform:translateY(-2px);border-color:#c8dfcb;box-shadow:0 10px 22px rgba(20,40,27,.08)}
.farmer-result-avatar{width:112px;height:104px;object-fit:cover;border-radius:12px;background:#eef2ef}.farmer-result-main{min-width:0}.farmer-result-title{margin:0 0 4px;color:#14281b;font-size:1.02rem;font-weight:700}.farmer-result-title a{color:inherit;text-decoration:none}.farmer-result-title a:hover{color:#178a53}.farmer-result-description{margin:0 0 9px;color:#5f7a67;font-size:.88rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.farmer-result-meta{display:flex;flex-wrap:wrap;gap:8px 14px;color:#5f7a67;font-size:.8rem}.farmer-result-meta span{display:inline-flex;align-items:center;gap:5px}.farmer-result-rating{color:#b66a12!important}.farmer-result-actions{display:flex;align-items:center;gap:8px}.farmer-result-view{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:8px 13px;border:1px solid #178a53;border-radius:10px;color:#0f4d30;font-size:.84rem;font-weight:700;text-decoration:none;white-space:nowrap}.farmer-result-view:hover{background:#178a53;color:#fff}.farmer-result-favorite{width:38px;height:38px;border:1px solid #e6ede6;border-radius:10px;background:#fff;color:#5f7a67}.farmer-result-favorite:hover,.farmer-result-favorite.is-favorite{color:#178a53;border-color:#b6d6bd}
.farmer-directory-empty{padding:42px 20px;text-align:center;color:#5f7a67}.farmer-directory-empty i{display:block;margin-bottom:10px;color:#178a53;font-size:2rem}.farmer-directory-pagination{display:flex;justify-content:center;align-items:center;gap:7px;padding:18px 4px 2px}.farmer-page-button{min-width:36px;height:36px;border:1px solid #e6ede6;border-radius:9px;background:#fff;color:#334b3a;font-weight:700}.farmer-page-button:hover,.farmer-page-button.active{background:#178a53;border-color:#178a53;color:#fff}.farmer-page-button:disabled{opacity:.45;cursor:not-allowed}
@media(max-width:767.98px){.farmer-directory-layout{grid-template-columns:1fr}.farmer-filter-panel{position:static}.farmer-result-card{grid-template-columns:82px minmax(0,1fr);gap:12px;padding:11px}.farmer-result-avatar{width:82px;height:82px}.farmer-result-actions{grid-column:2;justify-content:flex-start}.farmer-directory-toolbar{align-items:stretch;flex-direction:column}.farmer-search-input{max-width:none}}
@media(max-width:420px){.farmer-result-meta{gap:6px 10px}.farmer-result-description{-webkit-line-clamp:3}.farmer-directory-list{padding:11px}}
</style>

<section class="farmer-directory-section" id="farmersDirectory">
    <div class="container">
        <div class="farmer-directory-heading">
            <span class="section-kicker">Meet the growers</span>
            <h2>Find your local farmers</h2>
            <p>Discover trusted local farms, explore their stalls and find fresh produce from growers near you.</p>
        </div>
        <div class="farmer-directory-layout">
            <aside class="farmer-filter-panel" aria-label="Farmer filters">
                <div class="farmer-filter-head"><span>Filter farmers</span><i class="bi bi-sliders2" aria-hidden="true"></i></div>
                <form method="get" action="<?= ML_asset('farmers') ?>">
                    <label for="farmerMarketFilter">Pickup market</label>
                    <select id="farmerMarketFilter" name="market">
                        <option value="0">All markets</option>
                        <?php foreach ($markets as $market): ?>
                            <option value="<?= (int)$market['market_id'] ?>" <?= $marketFilter === (int)$market['market_id'] ? 'selected' : '' ?>><?= htmlspecialchars($market['market_name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="farmer-filter-actions">
                        <button class="farmer-filter-apply" type="submit">Apply filter</button>
                        <a class="farmer-filter-reset" href="<?= ML_asset('farmers') ?>">Reset</a>
                    </div>
                </form>
            </aside>
            <div class="farmer-directory-list">
                <div class="farmer-directory-toolbar">
                    <span class="farmer-directory-count"><strong id="farmerVisibleCount"><?= count($farms) ?></strong> verified farms</span>
                    <input class="farmer-search-input" id="farmerSearch" type="search" placeholder="Search farmers..." aria-label="Search farmers">
                </div>
                <div class="farmer-result-list" id="farmerResultList">
                    <?php foreach ($farms as $index => $farm): ?>
                        <?php
                            $farmerId = (int)$farm['farmer_id'];
                            $farmerName = (string)($farm['stall_name'] ?? 'Local farm');
                            $farmerDescription = trim((string)($farm['description'] ?? ''));
                            $farmerImage = $farmerAvatars[$index % count($farmerAvatars)];
                            $farmerIsFavorite = in_array($farmerId, $favoriteFarmerIds, true);
                        ?>
                        <article class="farmer-result-card" data-farmer-search="<?= htmlspecialchars(mb_strtolower($farmerName . ' ' . $farmerDescription), ENT_QUOTES, 'UTF-8') ?>">
                            <img class="farmer-result-avatar" src="<?= htmlspecialchars($farmerImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($farmerName, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <div class="farmer-result-main">
                                <h3 class="farmer-result-title"><a href="<?= ML_asset('farmer') . '?id=' . $farmerId ?>"><?= htmlspecialchars($farmerName, ENT_QUOTES, 'UTF-8') ?></a></h3>
                                <p class="farmer-result-description"><?= htmlspecialchars($farmerDescription !== '' ? $farmerDescription : 'Fresh, carefully grown produce from a verified local farmer.', ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="farmer-result-meta">
                                    <span class="farmer-result-rating"><i class="bi bi-star-fill" aria-hidden="true"></i> <?= number_format((float)($farm['avg_rating'] ?? 0), 1) ?> <span class="visually-hidden">rating</span></span>
                                    <span><i class="bi bi-shop" aria-hidden="true"></i> <?= (int)($farm['markets'] ?? 0) ?> markets</span>
                                    <span><i class="bi bi-basket2" aria-hidden="true"></i> <?= (int)($farm['products'] ?? 0) ?> products</span>
                                </div>
                            </div>
                            <div class="farmer-result-actions">
                                <a class="farmer-result-view" href="<?= ML_asset('farmer') . '?id=' . $farmerId ?>">View farm <i class="bi bi-arrow-up-right ms-1" aria-hidden="true"></i></a>
                                <?php if (!empty($_SESSION['loggedIn']) && ($_SESSION['role'] ?? '') === 'customer'): ?>
                                    <form method="post" action="<?= ML_asset('api/favorites.php') ?>">
                                        <input type="hidden" name="farmer_id" value="<?= $farmerId ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <button class="farmer-result-favorite <?= $farmerIsFavorite ? 'is-favorite' : '' ?>" type="submit" aria-label="<?= $farmerIsFavorite ? 'Remove from favorites' : 'Add to favorites' ?>"><i class="bi <?= $farmerIsFavorite ? 'bi-heart-fill' : 'bi-heart' ?>" aria-hidden="true"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php if (empty($farms)): ?>
                    <div class="farmer-directory-empty"><i class="bi bi-flower1" aria-hidden="true"></i><strong>No verified farmers found</strong><p class="mb-0 mt-2">Try selecting another market or check back soon.</p></div>
                <?php endif; ?>
                <nav class="farmer-directory-pagination" id="farmerPagination" aria-label="Farmer pages"></nav>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var list = document.getElementById('farmerResultList');
    if (!list) return;
    var cards = Array.prototype.slice.call(list.querySelectorAll('.farmer-result-card'));
    var search = document.getElementById('farmerSearch');
    var count = document.getElementById('farmerVisibleCount');
    var pagination = document.getElementById('farmerPagination');
    var pageSize = 6;
    var page = 1;
    var filtered = cards.slice();
    function draw() {
        var pages = Math.max(1, Math.ceil(filtered.length / pageSize));
        page = Math.min(page, pages);
        cards.forEach(function (card) { card.hidden = true; });
        filtered.slice((page - 1) * pageSize, page * pageSize).forEach(function (card) { card.hidden = false; });
        if (count) count.textContent = String(filtered.length);
        if (!pagination) return;
        pagination.innerHTML = '';
        if (pages <= 1) return;
        function button(label, next, disabled, active, aria) {
            var el = document.createElement('button');
            el.type = 'button'; el.className = 'farmer-page-button' + (active ? ' active' : '');
            el.textContent = label; el.disabled = disabled;
            if (aria) el.setAttribute('aria-label', aria);
            if (active) el.setAttribute('aria-current', 'page');
            el.addEventListener('click', function () { page = next; draw(); });
            pagination.appendChild(el);
        }
        button('‹', page - 1, page === 1, false, 'Previous page');
        for (var i = 1; i <= pages; i++) button(String(i), i, false, i === page, 'Page ' + i);
        button('›', page + 1, page === pages, false, 'Next page');
    }
    if (search) search.addEventListener('input', function () {
        var query = search.value.trim().toLocaleLowerCase();
        filtered = cards.filter(function (card) { return (card.getAttribute('data-farmer-search') || '').indexOf(query) !== -1; });
        page = 1; draw();
    });
    draw();
})();
</script>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>