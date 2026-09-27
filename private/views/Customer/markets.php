<?php
$pageTitle = 'Community Farmers Markets - MarketLink';
$page = 'markets';
include __DIR__ . '/../../../public/components/header.php';

$search = trim($_GET['search'] ?? '');
$dayFilter = trim($_GET['day'] ?? '');

$sql = "SELECT m.*,
    (SELECT COUNT(DISTINCT mf.farmer_id) FROM market_farmer AS mf WHERE mf.market_id = m.market_id) AS farmers,
    (SELECT COUNT(DISTINCT p.product_id) FROM market_farmer AS mf INNER JOIN products AS p ON p.farmer_id = mf.farmer_id WHERE mf.market_id = m.market_id AND p.is_available = 1 AND p.is_sold_out = 0) AS products
    FROM markets AS m WHERE m.is_active = 1";
$params = [];

if ($search !== '') {
    $sql .= " AND (m.market_name LIKE ? OR m.address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY m.market_name ASC";
$markets = selectData($pdo, $sql, $params);

// Filter by day in PHP if specified
if ($dayFilter !== '') {
    $markets = array_filter($markets, function($mk) use ($dayFilter) {
        $days = explode(',', strtolower($mk['operating_days'] ?? ''));
        foreach ($days as $d) {
            if (trim($d) === strtolower($dayFilter)) return true;
        }
        return false;
    });
}

$marketImages = [
    'https://images.unsplash.com/photo-1488459716781-31db52582fe9?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1533900298318-6b8da08a523e?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1516253593875-bd7ba052fbc5?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1526399232581-2ab5608b6336?auto=format&fit=crop&w=700&q=80',
];
?>

<style>
/* Markets Video Hero Header */
.markets-hero {
    position: relative;
    overflow: hidden;
    min-height: 480px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #0e180c;
    color: #ffffff;
    padding: 65px 0 55px;
}
.markets-video-wrap {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none;
    z-index: 1;
}
.markets-hero-video {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%;
    width: 100%;
    height: 100%;
    transform: translate(-50%, -50%);
    object-fit: cover;
    filter: brightness(0.92) contrast(1.08);
    transition: transform 0.1s linear;
}
.markets-video-overlay {
    position: absolute;
    inset: 0;
    z-index: 2;
    background: linear-gradient(135deg, rgba(14, 24, 10, 0.55) 0%, rgba(28, 46, 18, 0.35) 50%, rgba(10, 18, 7, 0.62) 100%);
}

.markets-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.38);
    color: #f1f8eb;
    padding: 6px 20px;
    border-radius: 50px;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
}

/* WIDE SINGLE-LINE HORIZONTAL FILTER FORM */
.market-search-filter-box {
    background: #ffffff !important;
    border-radius: 60px !important;
    padding: 6px 8px 6px 22px !important;
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    justify-content: space-between !important;
    max-width: 840px !important;
    width: 100% !important;
    height: 62px !important;
    margin: 28px auto 10px !important;
    box-shadow: 0 16px 42px rgba(0, 0, 0, 0.3) !important;
    border: 2px solid #a0bc79 !important;
    position: relative;
    z-index: 5;
}
.market-search-input-wrap {
    display: flex !important;
    align-items: center !important;
    flex: 1 1 auto !important;
}
.market-search-input {
    border: none !important;
    outline: none !important;
    background: transparent !important;
    width: 100% !important;
    color: #17240f !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    padding: 0 12px !important;
    font-family: 'Poppins', sans-serif !important;
}
.market-search-input::placeholder {
    color: #7d9271;
    font-weight: 400;
}
.market-day-select {
    border: none !important;
    outline: none !important;
    background: #f4f8ee !important;
    color: #243815 !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    padding: 8px 16px !important;
    border-radius: 50px !important;
    max-width: 160px !important;
    margin: 0 8px !important;
    cursor: pointer !important;
    box-shadow: none !important;
}
.market-search-submit-btn {
    background: #4a5f31 !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 50px !important;
    font-weight: 700 !important;
    font-size: 0.95rem !important;
    padding: 0 32px !important;
    height: 50px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    white-space: nowrap !important;
    transition: all 0.25s ease !important;
    flex-shrink: 0 !important;
    box-shadow: 0 4px 14px rgba(74, 95, 49, 0.35) !important;
    font-family: 'Poppins', sans-serif !important;
}
.market-search-submit-btn:hover {
    background: #2b3d1b !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3) !important;
}

/* Floating Market Cards */
.market-float-card {
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(45, 65, 28, 0.08);
    transition: transform 0.35s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.35s ease, border-color 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}
.market-float-card:hover {
    transform: translateY(-8px);
    border-color: #4a5f31;
    box-shadow: 0 20px 45px rgba(45, 65, 28, 0.18);
}
.market-card-img-wrap {
    height: 190px;
    position: relative;
    overflow: hidden;
    background: #eef2e6;
}
.market-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.market-float-card:hover .market-card-img {
    transform: scale(1.08);
}
.market-badge-chip {
    position: absolute;
    top: 14px;
    left: 14px;
    background: rgba(20, 36, 12, 0.88);
    backdrop-filter: blur(8px);
    color: #ffffff;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.market-status-chip {
    position: absolute;
    top: 14px;
    right: 14px;
    background: #4a5f31;
    color: #ffffff;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 50px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.market-fav-btn {
    position: absolute;
    top: 58px;
    right: 14px;
    background: #ffffff;
    border: 1px solid rgba(20, 36, 12, 0.12);
    color: #d8483f;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
    transition: transform 0.2s ease, background 0.2s ease;
    z-index: 2;
}
.market-fav-btn:hover {
    transform: scale(1.1);
}
.market-fav-btn.is-fav {
    background: #d8483f;
    color: #ffffff;
}

.market-card-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.market-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.35rem;
    font-weight: 700;
    color: #17240f;
    margin-bottom: 6px;
    transition: color 0.2s ease;
}
.market-float-card:hover .market-title {
    color: #3e5a25;
}
.market-location {
    font-size: 0.86rem;
    color: #63775b;
    margin-bottom: 14px;
    display: flex;
    align-items: flex-start;
    gap: 6px;
}

.market-info-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f4f8ee;
    border: 1px solid #d3e5c4;
    color: #3b5226;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 50px;
}

.market-stats-row {
    margin-top: auto;
    padding-top: 16px;
    border-top: 1px solid #eef3e8;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.market-stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.84rem;
    font-weight: 600;
    color: #2b3d1f;
}
.market-stat-item i {
    color: #4a5f31;
    font-size: 1rem;
}

.market-card-btn {
    background: #4a5f31;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 8px 18px;
    border-radius: 50px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    border: none;
}
.market-card-btn:hover {
    background: #253915;
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(37, 57, 21, 0.25);
}
</style>

<!-- ============================================================
     HERO SECTION: MARKETS PROMINENT VIDEO HERO
     ============================================================ -->
<section class="markets-hero">
    <div class="markets-video-wrap">
        <video autoplay muted loop playsinline class="markets-hero-video" id="marketsHeroVideo">
            <source src="<?php echo ML_asset('Uploads/market.mp4'); ?>" type="video/mp4">
        </video>
        <div class="markets-video-overlay"></div>
    </div>

    <div class="container position-relative py-4 text-center" style="z-index: 3;">
        <span class="markets-hero-badge"><i class="fa-solid fa-store"></i> Verified Pickup Hubs</span>
        <h1 class="display-5 fw-bold mt-2 mb-2 text-white" style="font-family: 'Fraunces', Georgia, serif;">Community Farmers Markets</h1>
        <p class="lead mb-4" style="max-width: 640px; margin: 0 auto; color: #e1edd8; font-size: 1.05rem;">
            Discover regional producers at local market stalls. Pre-order honest harvests and pick them up fresh with zero middlemen.
        </p>

        <!-- Wide Single-Line Filter Form -->
        <form method="GET" action="<?php echo ML_asset('markets'); ?>" class="market-search-filter-box">
            <div class="market-search-input-wrap">
                <i class="fa-solid fa-magnifying-glass text-success fs-5 ms-1 me-2"></i>
                <input type="text" name="search" class="market-search-input" placeholder="Search by market name or area..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <select name="day" class="market-day-select">
                <option value="">All Days</option>
                <option value="Sunday" <?php echo $dayFilter === 'Sunday' ? 'selected' : ''; ?>>Sunday</option>
                <option value="Monday" <?php echo $dayFilter === 'Monday' ? 'selected' : ''; ?>>Monday</option>
                <option value="Tuesday" <?php echo $dayFilter === 'Tuesday' ? 'selected' : ''; ?>>Tuesday</option>
                <option value="Wednesday" <?php echo $dayFilter === 'Wednesday' ? 'selected' : ''; ?>>Wednesday</option>
                <option value="Thursday" <?php echo $dayFilter === 'Thursday' ? 'selected' : ''; ?>>Thursday</option>
                <option value="Friday" <?php echo $dayFilter === 'Friday' ? 'selected' : ''; ?>>Friday</option>
                <option value="Saturday" <?php echo $dayFilter === 'Saturday' ? 'selected' : ''; ?>>Saturday</option>
            </select>

            <button type="submit" class="market-search-submit-btn">
                <span>Filter</span> <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>
</section>

<!-- ============================================================
     MARKETS LISTING GRID
     ============================================================ -->
<div class="py-5" style="background: #f8faf6;">
    <div class="container py-3">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h3 class="fw-bold text-dark mb-0" style="font-family: 'Fraunces', Georgia, serif;">Active Pickup Locations</h3>
                <span class="text-muted small">Showing <strong class="text-dark"><?php echo count($markets); ?></strong> community market hubs</span>
            </div>

            <?php if ($search !== '' || $dayFilter !== ''): ?>
                <a href="<?php echo ML_asset('markets'); ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-rotate-left me-1"></i> Reset Filters
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($markets)): ?>
            <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white" style="border: 1.5px solid #dce8cf !important;">
                <i class="fa-solid fa-store-slash text-muted fs-1 mb-3"></i>
                <h5 class="fw-bold text-dark">No Farmers Markets Found</h5>
                <p class="text-muted mb-3">Try clearing your search query or selecting a different market day.</p>
                <a href="<?php echo ML_asset('markets'); ?>" class="btn btn-success rounded-pill px-4 mx-auto fw-bold" style="background:#4a5f31; border:none; width: fit-content;">
                    View All Markets
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php 
                foreach ($markets as $idx => $m): 
                    $mImg = $marketImages[$idx % count($marketImages)];
                    $mUrl = ML_asset('market') . '?id=' . $m['market_id'];
                    $mkFav = false;
                    if (!empty($_SESSION['loggedIn']) && ($_SESSION['role'] ?? '') == 'customer') {
                        $crows = selectData($pdo, "SELECT customer_id FROM customers WHERE user_id = ?", [$_SESSION['user_id']]);
                        if (!empty($crows)) $mkFav = isFav($pdo, $crows[0]['customer_id'], null, null, $m['market_id']);
                    }
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="market-float-card">
                            <div class="market-card-img-wrap">
                                <img src="<?php echo $mImg; ?>" alt="<?php echo sanitize_output($m['market_name']); ?>" class="market-card-img">
                                <span class="market-badge-chip">
                                    <i class="fa-solid fa-certificate text-leaf"></i> Verified Hub
                                </span>
                                <span class="market-status-chip">Open Scheduled</span>
                                <button type="button" class="market-fav-btn <?php echo $mkFav ? 'is-fav' : ''; ?>" data-fav-btn data-fav-type="market" data-fav-id="<?php echo $m['market_id']; ?>" title="<?php echo $mkFav ? 'Remove from favorites' : 'Save to favorites'; ?>"><i class="bi <?php echo $mkFav ? 'bi-heart-fill' : 'bi-heart'; ?>"></i></button>
                            </div>

                            <div class="market-card-body">
                                <a href="<?php echo $mUrl; ?>" class="text-decoration-none">
                                    <h4 class="market-title"><?php echo sanitize_output($m['market_name']); ?></h4>
                                </a>

                                <div class="market-location">
                                    <i class="fa-solid fa-location-dot text-success mt-1"></i>
                                    <span><?php echo sanitize_output($m['address']); ?></span>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="market-info-pill">
                                        <i class="fa-regular fa-calendar-days text-success"></i> <?php echo marketPickups($m); ?>
                                    </span>
                                    <span class="market-info-pill">
                                        <i class="fa-regular fa-clock text-success"></i> <?php echo date('g:i A', strtotime($m['opening_time'])); ?> &ndash; <?php echo date('g:i A', strtotime($m['closing_time'])); ?>
                                    </span>
                                </div>

                                <div class="market-stats-row">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="market-stat-item" title="Approved Stalls">
                                            <i class="fa-solid fa-tractor"></i>
                                            <span><?php echo (int)$m['farmers']; ?> Stalls</span>
                                        </div>
                                        <div class="market-stat-item" title="Available Products">
                                            <i class="fa-solid fa-leaf"></i>
                                            <span><?php echo (int)$m['products']; ?> Items</span>
                                        </div>
                                    </div>

                                    <a href="<?php echo $mUrl; ?>" class="market-card-btn">
                                        <span>View Hub</span> <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Parallax Scroll Effect for Video Hero -->
<script>
(function() {
    window.addEventListener('scroll', function() {
        var sc = window.scrollY;
        var videoEl = document.getElementById('marketsHeroVideo');
        if (videoEl && sc < 600) {
            videoEl.style.transform = 'translate(-50%, calc(-50% + ' + (sc * 0.3) + 'px))';
        }
    }, { passive: true });
})();
</script>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>