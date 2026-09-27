<?php
$pageTitle = 'Fresh Harvest Products - MarketLink';
$page = 'products';
include __DIR__ . '/../../../public/components/header.php';

$category = (int)($_GET['category'] ?? 0);
$market = (int)($_GET['market'] ?? 0);
$sort = $_GET['sort'] ?? 'new';
$q = trim($_GET['q'] ?? '');

$categories = selectData($pdo, "SELECT * FROM categories WHERE is_active = 1 ORDER BY category_name");
$markets = selectData($pdo, "SELECT market_id, market_name FROM markets WHERE is_active = 1 ORDER BY market_name");

$where = ["p.is_available = 1", "p.is_sold_out = 0", "p.stock_quantity > 0"];$params = [];
if ($category) { $where[] = "p.category_id = ?"; $params[] = $category; }
if ($market) { $where[] = "p.farmer_id IN (SELECT farmer_id FROM market_farmer WHERE market_id = ?)"; $params[] = $market; }
if ($q !== '') { 
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR f.stall_name LIKE ? OR c.category_name LIKE ?)"; 
    $like = '%' . $q . '%'; 
    array_push($params, $like, $like, $like, $like); 
}

$orderBy = 'p.created_at DESC';
if ($sort === 'price_asc') $orderBy = 'p.price ASC';
elseif ($sort === 'price_desc') $orderBy = 'p.price DESC';
elseif ($sort === 'rating') $orderBy = 'p.avg_rating DESC';
elseif ($sort === 'name') $orderBy = 'p.name ASC';

$products = selectData($pdo, "SELECT p.*, f.stall_name, c.category_name FROM products AS p
    INNER JOIN farmers AS f ON f.farmer_id = p.farmer_id AND f.approval_status = 'approved'
    INNER JOIN users AS u ON u.id = f.user_id AND u.status = 'active'
    LEFT JOIN categories AS c ON c.category_id = p.category_id
    WHERE " . implode(' AND ', $where) . " ORDER BY " . $orderBy, $params);

// Counts for the closing banner. Deliberately unfiltered: this is a
// "here is the whole marketplace" invitation, so the numbers must describe
// the platform, not whatever the shopper happens to have filtered down to
// above. Same single aggregate home.php runs, so it costs one round trip.
$harvestStats = selectData($pdo, "SELECT
    (SELECT COUNT(*) FROM markets WHERE is_active = 1) AS markets,
    (SELECT COUNT(*) FROM farmers WHERE approval_status = 'approved') AS farmers,
    (SELECT COUNT(*) FROM products WHERE is_available = 1 AND is_sold_out = 0) AS products")[0];

function mlq($k, $v, $keep = []) {
    $p = array_merge($_GET, $keep);
    if ($v === '' || $v === 0) unset($p[$k]); else $p[$k] = $v;
    return '?' . http_build_query($p);
}


// 4 Featured Produce Cards with 3D Cutouts (No Square Box Borders!)
$featureProduceCards = [
    [
        'title' => 'Oranges',
        'desc' => 'Naturally ripened, bursting with Vitamin C and fresh orchard dew.',
        'bg' => 'linear-gradient(135deg, #f48c06 0%, #e85d04 100%)',
        'img' => ML_asset('Uploads/img/orange-3d.png'),
        'query' => 'orange',
        'blend' => false
    ],
    [
        'title' => 'Peaches',
        'desc' => 'Lush organic orchard fruits picked at peak morning sweetness.',
        'bg' => 'linear-gradient(135deg, #f77f00 0%, #d62828 100%)',
        'img' => ML_asset('Uploads/img/peavh3d.png'),
        'query' => 'peach',
        'blend' => false
    ],
    [
        'title' => 'Spinach',
        'desc' => 'Iron-rich leafy greens hand-picked before sunrise for peak nutrition.',
        'bg' => 'linear-gradient(135deg, #2d6a4f 0%, #40916c 100%)',
        'img' => ML_asset('Uploads/img/spinash-3d.png'),
        'query' => 'spinach',
        'blend' => false
    ],
    [
        'title' => 'Carrots',
        'desc' => 'Crisp beta-carotene rich root vegetables straight from organic soil.',
        'bg' => 'linear-gradient(135deg, #e76f51 0%, #c1440e 100%)',
        'img' => ML_asset('Uploads/img/carrot-3d.png'),
        'query' => 'carrot',
        'blend' => false
    ]
];


function getLocalOrCustomImg($p) {
    $custom = prodImgSrc($p);
    if ($custom !== '') {
        return $custom;
    }
    $n = strtolower($p['name'] ?? '');
    $cat = strtolower($p['category_name'] ?? '');

    if (strpos($n, 'strawberr') !== false) return ML_asset('Uploads/img/straw.png');
    if (strpos($n, 'peach') !== false) return ML_asset('Uploads/img/peaches_3d.png');
    if (strpos($n, 'orange') !== false) return ML_asset('Uploads/img/oranges_3d.png');
    if (strpos($n, 'banana') !== false) return 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?auto=format&fit=crop&w=400&q=80';
    if (strpos($n, 'mango') !== false) return 'https://images.unsplash.com/photo-1553279768-865429fa0078?auto=format&fit=crop&w=400&q=80';
    if (strpos($cat, 'fruit') !== false) return ML_asset('Uploads/hero/fruits.JPG');

    if (strpos($n, 'tomat') !== false) return 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=400&q=80';
    if (strpos($n, 'potat') !== false) return 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=400&q=80';
    if (strpos($n, 'onion') !== false) return 'https://images.unsplash.com/photo-1618512496248-a07fe83aa8cb?auto=format&fit=crop&w=400&q=80';
    if (strpos($n, 'cucumber') !== false) return 'https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?auto=format&fit=crop&w=400&q=80';
    if (strpos($cat, 'veg') !== false) return ML_asset('Uploads/hero/vegetables.jpg');

    if (strpos($cat, 'dairy') !== false) return ML_asset('Uploads/hero/dairy.jpg');
    if (strpos($cat, 'bakery') !== false) return ML_asset('Uploads/hero/bakery.webp');
    if (strpos($cat, 'herb') !== false) return ML_asset('Uploads/hero/herbs.webp');
    
    return ML_asset('Uploads/img/crop.webp');
}
?>

<style>
/* ============================================================
/* ============================================================
/* ============================================================
   PRODUCTS HERO - VIDEO PORTAL
   ============================================================
   The footage used to be stretched edge to edge behind the text, which
   is the plainest way to use a background video and read as a template.
   Here the video is cut into a large circle on the right, ringed by a
   slowly turning dashed orbit and crossed by a travelling light sweep,
   so the frame is a scene rather than a backdrop.

   Product cards used to float around the circle. They made the
   composition noisy and were taken out again, which also let the ground
   go from near-black to a soft cream-to-sage gradient. The copy colours
   are the home hero's dark greens rather than white, because white text
   does not survive on a pale ground.

   The video path is corrected: this used to ask for
   Uploads/video/products-hero.mp4, which does not exist on disk and
   returned 404, so the footage had never actually played. It has always
   been at Uploads/products-hero.mp4.
   ============================================================ */
.prod-stage {
    position: relative;
    overflow: hidden;
    /* Soft cream-to-sage wash. The stops are pulled from the home hero's
       #f3f0e0 and #b0c78f so this still belongs to the same palette. */
    background:
        radial-gradient(115% 85% at 80% 12%, rgba(243, 156, 18, 0.14) 0%, transparent 58%),
        radial-gradient(90% 70% at 8% 92%, rgba(176, 199, 143, 0.28) 0%, transparent 62%),
        linear-gradient(152deg, #fdfcf6 0%, #f6f3e6 34%, #eef2e2 68%, #e4ecd8 100%);
    color: #40543a;
    isolation: isolate;
    border-bottom: 2px solid #d4d6bc;
}
.prod-stage-grid {
    position: relative;
    z-index: 4;
    max-width: 1280px;
    margin: 0 auto;
    min-height: 600px;
    padding: clamp(34px, 5vw, 62px) clamp(24px, 5.5vw, 84px) clamp(40px, 5.5vw, 66px);
    display: grid;
    grid-template-columns: 1fr 1.08fr;
    gap: clamp(24px, 4vw, 56px);
    align-items: center;
}

/* ---------- left: copy ---------- */
.prod-stage-copy { max-width: 500px; }
.prod-stage-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: fit-content;
    color: #5b6a4e;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    margin-bottom: 16px;
}
.prod-stage-kicker i { color: #e67e22; }
.prod-stage-title {
    font-family: 'Poppins', system-ui, sans-serif;
    font-weight: 700;
    letter-spacing: -0.6px;
    font-size: clamp(2.3rem, 4.6vw, 3.9rem);
    line-height: 1.08;
    color: #40543a;
    max-width: 15ch;
    margin-bottom: 20px;
}
.prod-stage-lead {
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: clamp(0.95rem, 1.15vw, 1.05rem);
    line-height: 1.68;
    color: #4a5f31;
    max-width: 44ch;
    margin-bottom: 28px;
}
.prod-stage-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 26px;
}
.prod-stage-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    color: #ffffff;
    border: none;
    border-radius: 50px;
    padding: 14px 40px;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    text-decoration: none;
    box-shadow: 0 12px 28px rgba(230, 126, 34, 0.42);
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
}
.prod-stage-btn:hover {
    background: linear-gradient(135deg, #d35400 0%, #e67e22 100%);
    color: #ffffff;
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 18px 36px rgba(230, 126, 34, 0.55);
}
.prod-stage-btn.ghost {
    background: rgba(255, 255, 255, 0.65);
    color: #40543a;
    box-shadow: none;
    padding: 14px 32px;
    border: 2px solid #4a5f31;
    backdrop-filter: blur(4px);
}
.prod-stage-btn.ghost:hover {
    background: #4a5f31;
    color: #ffffff;
    border-color: #4a5f31;
}

/* Search pill sitting on the dark ground. */
.prod-stage-search {
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(10px);
    border-radius: 18px;
    padding: 7px 7px 7px 20px;
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    max-width: 470px;
    width: 100%;
    height: 62px;
    box-shadow: 0 20px 44px rgba(0, 0, 0, 0.34);
}
.prod-stage-search input {
    border: none;
    outline: none;
    background: transparent;
    flex: 1 1 auto;
    width: 100%;
    color: #40543a;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 0.95rem;
    font-weight: 500;
    padding: 0 14px;
}
.prod-stage-search input::placeholder { color: #728267; font-weight: 400; }
.prod-stage-search button {
    background: #4a5f31;
    color: #ffffff;
    border: none;
    border-radius: 12px;
    font-family: 'Poppins', system-ui, sans-serif;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0 28px;
    height: 46px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    flex-shrink: 0;
    transition: all 0.25s ease;
}
.prod-stage-search button:hover { background: #5c7047; transform: translateY(-1px); }

/* ---------- right: the video portal ---------- */
.prod-portal {
    position: relative;
    width: min(100%, 540px);
    aspect-ratio: 1;
    margin-left: auto;
    perspective: 1400px;
}
/* Warm light spilling out from behind the frame. Kept translucent, because
   a glow that strong reads as a lamp on a dark stage and would fight the
   soft ground. */
.prod-portal-glow {
    position: absolute;
    inset: -14%;
    z-index: 0;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(230, 126, 34, 0.20) 0%, rgba(160, 188, 121, 0.22) 44%, transparent 70%);
    filter: blur(20px);
    animation: portalGlow 6.5s ease-in-out infinite;
}
@keyframes portalGlow {
    0%, 100% { opacity: 0.7; transform: scale(1); }
    50%      { opacity: 1;  transform: scale(1.06); }
}
/* The cut itself. Just a hairline rim and a soft drop shadow, since the
   ground behind it is now pale. */
.prod-portal-frame {
    position: absolute;
    inset: 0;
    z-index: 2;
    border-radius: 50%;
    overflow: hidden;
    box-shadow:
        0 26px 60px rgba(64, 84, 58, 0.24),
        0 0 0 3px rgba(255, 255, 255, 0.9),
        0 0 0 9px rgba(255, 255, 255, 0.45);
}
/* Only a whisper of shade at the very bottom now, to stop the circle
   looking flat where the footage ends. */
.prod-portal-frame::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 62%, rgba(23, 36, 15, 0.22) 100%);
    pointer-events: none;
}
.prod-portal-frame video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    /* Slow drift so the circle never looks like a paused thumbnail. */
    animation: portalDrift 22s ease-in-out infinite alternate;
    will-change: transform;
}
@keyframes portalDrift {
    from { transform: scale(1.02) translate(0, 0); }
    to   { transform: scale(1.16) translate(-2.5%, -1.5%); }
}
/* Light sweep travelling across the glass. */
.prod-portal-shine {
    position: absolute;
    inset: 0;
    z-index: 3;
    border-radius: 50%;
    overflow: hidden;
    pointer-events: none;
}
.prod-portal-shine::before {
    content: '';
    position: absolute;
    top: -60%;
    left: -120%;
    width: 60%;
    height: 220%;
    background: linear-gradient(100deg, transparent, rgba(255, 255, 255, 0.26), transparent);
    transform: rotate(18deg);
    animation: portalShine 7s ease-in-out infinite;
}
@keyframes portalShine {
    0%       { left: -120%; }
    55%, 100% { left: 165%; }
}
/* Dashed ring turning slowly around the frame. */
.prod-portal-orbit {
    position: absolute;
    inset: -7%;
    z-index: 1;
    border-radius: 50%;
    border: 2px dashed rgba(140, 168, 106, 0.5);
    animation: portalOrbitSpin 46s linear infinite;
    pointer-events: none;
}
@keyframes portalOrbitSpin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
/* Three dots riding the ring, so the rotation is actually visible. */
.prod-portal-dot {
    position: absolute;
    width: 11px;
    height: 11px;
    border-radius: 50%;
    background: #f39c12;
    box-shadow: 0 0 14px rgba(243, 156, 18, 0.8);
}
.prod-portal-dot.d1 { top: -6px; left: 50%; margin-left: -5px; }
.prod-portal-dot.d2 { bottom: 12%; right: -5px; }
.prod-portal-dot.d3 { top: 46%; left: -6px; background: #a0bc79; box-shadow: 0 0 14px rgba(160, 188, 121, 0.8); }

/* Live badge, top-right of the section. */
.prod-stage-live {
    position: absolute;
    top: clamp(18px, 2.6vw, 30px);
    right: clamp(20px, 3vw, 34px);
    z-index: 7;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid #d4d6bc;
    color: #40543a;
    box-shadow: 0 8px 20px rgba(74, 95, 49, 0.12);
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.4px;
    padding: 8px 16px 8px 10px;
    border-radius: 50px;
    backdrop-filter: blur(6px);
}
.prod-stage-live .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ff4d4d;
    animation: heroLivePulse 1.8s infinite;
}
@keyframes heroLivePulse {
    0%   { box-shadow: 0 0 0 0 rgba(255, 77, 77, 0.6); }
    70%  { box-shadow: 0 0 0 9px rgba(255, 77, 77, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 77, 77, 0); }
}

@media (prefers-reduced-motion: reduce) {
    .prod-portal-frame video,
    .prod-portal-glow,
    .prod-portal-orbit,
    .prod-portal-shine::before { animation: none !important; }
}

@media (max-width: 991px) {
    .prod-stage-grid {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 34px;
        padding-bottom: 70px;
    }
    .prod-stage-copy { max-width: 100%; margin: 0 auto; }
    .prod-stage-title, .prod-stage-lead { max-width: 100%; }
    .prod-stage-lead { margin-left: auto; margin-right: auto; }
    .prod-stage-kicker { margin-left: auto; margin-right: auto; }
    .prod-stage-actions { justify-content: center; }
    .prod-stage-search { margin: 0 auto; }
    .prod-portal { margin: 0 auto; width: min(100%, 440px); }
}

@media (max-width: 576px) {
    .prod-stage-grid { padding: 22px 16px 56px; }
    .prod-stage-btn { padding: 12px 24px; font-size: 0.85rem; letter-spacing: 0.8px; }
    .prod-stage-btn.ghost { padding: 12px 20px; }
    .prod-stage-search { height: 56px; padding: 6px 6px 6px 14px; }
    .prod-stage-search button { height: 42px; padding: 0 18px; }
    .prod-portal { width: min(100%, 320px); }
    .prod-portal-orbit { inset: -5%; }
}

/* ============================================================
   SCROLL-LINKED FLYING TOMATO
   Invisible 1x1 waypoints (one glued to the hero chip, one glued to
   the centerpiece further down) that the scroll script measures, so
   the tomato always has a real start/end however the layout reflows.
   ============================================================ */
.tomato-anchor {
    position: absolute;
    width: 1px;
    height: 1px;
    pointer-events: none;
}

/* The tomato itself: fixed so it can travel over every section between
   the hero and the spotlight, driven entirely by transform/opacity set
   from JS on scroll. Sits above absolutely everything on the page. */
#scrollTomato {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 260px;
    z-index: 2147483647;
    pointer-events: none;
    opacity: 0;
    will-change: transform, opacity;
    transform: translate3d(-9999px, -9999px, 0);
    filter: drop-shadow(0 30px 40px rgba(20, 30, 10, 0.45));
}
@media (max-width: 767px) {
    #scrollTomato { width: 150px; }
}
@media (prefers-reduced-motion: reduce) {
    #scrollTomato { display: none; }
}

/* Flying fruit images from the feature cards - same technique as tomato */
.pfc-fly-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 16px 28px rgba(0,0,0,0.45));
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.pfc-fly-img.is-flying {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 160px;
    z-index: 2147483640;
    pointer-events: none;
    opacity: 0;
    will-change: transform, opacity;
    transform: translate3d(-9999px, -9999px, 0);
    filter: drop-shadow(0 20px 35px rgba(20, 30, 10, 0.4));
}
@media (max-width: 767px) {
    .pfc-fly-img.is-flying { width: 90px; }
}
@media (prefers-reduced-motion: reduce) {
    .pfc-fly-img.is-flying { display: none; }
}

/* Little "it landed" pulse on the centerpiece the instant the flying
   tomato's travel hits 100%. */
.center-spotlight-box.tomato-landed .center-spotlight-img {
    animation: tomatoLandPulse 0.7s ease;
}
@keyframes tomatoLandPulse {
    0%   { filter: drop-shadow(0 20px 40px rgba(36, 54, 25, 0.32)) brightness(1); }
    40%  { filter: drop-shadow(0 20px 55px rgba(214, 40, 40, 0.35)) brightness(1.08); }
    100% { filter: drop-shadow(0 20px 40px rgba(36, 54, 25, 0.32)) brightness(1); }
}

/* ---------- Center spotlight, base styles ----------
   Only the .tomato-landed pulse above ever existed for these, so the
   box was rendering as a bare line of text with an unstyled photo slot
   in it. Colours follow the surrounding section (.best-food-spotlight
   uses #edf7e4 panels and #4a6a26 icon fills) so the centerpiece is
   not a different design pasted into a white section. */
.center-spotlight-box {
    position: relative;
    background: #edf7e4;
    border: 1px solid rgba(74, 106, 38, 0.18);
    border-radius: 26px;
    padding: 32px 28px 30px;
    box-shadow: 0 20px 46px rgba(74, 106, 38, 0.14);
}
.center-spotlight-tag {
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    line-height: 1.5;
    color: #4a6a26;
}
.center-spotlight-sign {
    margin-top: 8px;
    font-family: 'Caveat', cursive, sans-serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: #648430;
}
.center-spotlight-figure {
    position: relative;
    margin-top: 14px;
}
/* Overrides the generic .tomato-anchor rule, which leaves the waypoint
   at its static position in the flow - that would have put it at the
   left edge of the figure and above the image, so the tomato would
   have landed beside the picture instead of on it. */
.center-spotlight-figure .tomato-anchor {
    top: 50%;
    left: 50%;
    right: auto;
    bottom: auto;
    transform: translate(-50%, -50%);
}
.center-spotlight-img {
    display: block;
    width: 100%;
    max-width: 290px;
    height: auto;
    margin: 0 auto;
    /* Has to match the 0%/100% keyframe above exactly. Any difference
       here is visible as a one-frame jump the moment the animation
       takes over. */
    filter: drop-shadow(0 20px 40px rgba(36, 54, 25, 0.32));
}
@media (max-width: 576px) {
    .center-spotlight-box { padding: 24px 18px 22px; }
    .center-spotlight-img { max-width: 210px; }
}

/* ============================================================
   4 3D PRODUCE CARDS (NO SQUARE BOX BORDERS!)
   ============================================================ */
.prod-feature-card {
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
.prod-feature-card:hover {
    transform: translateY(-4px);
    z-index: 2;
    box-shadow: 0 14px 34px rgba(0,0,0,0.25);
}
.pfc-title {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 2.2rem;
    font-weight: 600;
    margin-bottom: 6px;
    letter-spacing: -0.5px;
}
.pfc-desc {
    font-size: 0.82rem;
    font-style: italic;
    line-height: 1.45;
    opacity: 0.95;
    margin-bottom: 12px;
    max-width: 175px;
    color: rgba(255, 255, 255, 0.92);
}
.pfc-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
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
.pfc-btn:hover {
    background: #ffffff;
    color: #17240f !important;
}

/* 3D Floating Produce Element (Seamless & Natural) */
.pfc-thumb {
    position: absolute;
    bottom: -20px;
    right: -20px;
    width: 270px;
    height: 270px;
    pointer-events: none;
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
    z-index: 2;
}
.pfc-thumb img,
.pfc-fly-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 20px 32px rgba(0,0,0,0.5));
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.pfc-thumb.blend-mode img {
    mix-blend-mode: multiply;
    border-radius: 50%;
}
.prod-feature-card:hover .pfc-thumb img {
    transform: scale(1.1) rotate(4deg) translateY(-8px);
}

/* Flying fruit keep big size during flight */
.pfc-fly-img.is-flying {
    width: 220px !important;
    height: 220px !important;
}

/* ============================================================
   "WE GROW BEST FOOD" SPOTLIGHT (Tomato Centerpiece)
   ============================================================ */
.best-food-spotlight {
    background: #ffffff;
    padding: 75px 0;
}
.bf-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(2.2rem, 3.6vw, 3rem);
    font-weight: 700;
    color: #648430;
    margin-bottom: 8px;
}
.bf-sub {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    color: #7f9964;
    font-size: 1.05rem;
    max-width: 680px;
    margin: 0 auto 55px;
    line-height: 1.5;
}

.bf-feature-group {
    display: flex;
    flex-direction: column;
    gap: 34px;
}
.bf-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.bf-icon {
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
.bf-item-title {
    font-weight: 700;
    font-size: 1.15rem;
    color: #17240f;
    margin-bottom: 3px;
}
.bf-item-desc {
    font-size: 0.84rem;
    color: #6b7d63;
    line-height: 1.48;
    margin-bottom: 0;
}

/* 3D Center Basket Spotlight */
.center-spotlight-box {
    position: relative;
    text-align: center;
}
.center-spotlight-tag {
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
.center-spotlight-sign {
    font-family: 'Caveat', cursive, sans-serif;
    font-size: 1.6rem;
    color: #ffffff;
    margin-top: 4px;
}
/* The old block that used to live here styled this as a photo (max-width
   100%, border-radius 24px). That was a leftover from when the centerpiece
   was a basket photograph. The hero now seats the flying tomato here
   instead, and a cut-out PNG needs none of it, so the rules up at the top
   of this file are the only ones that apply. */

/* ============================================================
   PRODUCTS SECTION
   ============================================================
   A calm, scannable grid. An earlier pass turned this into a
   draggable horizontal shelf with a cursor spotlight and 3D tilt; the
   cursor work fought the page, dragging made the cards hard to hit, and
   a single long strip hid the fact that there were twenty-odd products
   rather than a manageable set. This is a plain grid instead.

   Two things carry over from that pass, because both were correcting
   real data problems rather than decorating:

   1. Ratings are real. The old card printed four and a half stars for
      every product on the site regardless of what was stored. It now
      renders the actual rating through the existing starsHtml() helper
      and shows a "New" tag when a product has no reviews yet.
   2. Stock is a bar, not a flat badge. A long bar reassures, a short
      red one creates the right urgency, and the exact count only
      appears when it is scarce.

   The add-to-cart button keeps data-add-cart, because the cart script
   in customer.js is written against that selector.
   ============================================================ */
.plants-grid-section {
    background: #f7faf4;
    padding: 74px 0 68px;
}

/* ---- heading row: result count + show more ---- */
.ml-grid-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.ml-grid-count { font-size: 0.86rem; color: #728267; font-weight: 600; }
.ml-grid-count b { color: #40543a; }
.ml-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    background: transparent;
    border: 1.5px solid #4a5f31;
    color: #4a5f31;
    border-radius: 99px;
    padding: 10px 22px;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 0.84rem;
    font-weight: 600;
    transition: background 0.22s ease, color 0.22s ease;
}
.ml-more-btn:hover { background: #4a5f31; color: #ffffff; }
.ml-more-btn[hidden] { display: none; }
.ml-more-btn i { font-size: 0.72rem; transition: transform 0.25s ease; }
.ml-grid.is-open .ml-more-btn i { transform: rotate(180deg); }

/* ---- the grid ---- */
.ml-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
}
@media (max-width: 1199px) { .ml-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 767px)  { .ml-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; } }
@media (max-width: 400px)  { .ml-grid { grid-template-columns: 1fr; } }

/* Past the first page, held back until "Show all" is pressed. */
.ml-card.is-beyond { display: none; }
.ml-grid.is-open .ml-card.is-beyond { display: flex; }

/* ---- the card ---- */
.ml-card {
    position: relative;
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border: 1px solid #e8ecdd;
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
}
.ml-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(45, 65, 28, 0.13);
    border-color: #d4d6bc;
}
/* Keyboard users get the same affordance a mouse hover gives. */
.ml-card:focus-within {
    outline: 2px solid #4a5f31;
    outline-offset: 3px;
}

.ml-card-link {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
}
.ml-card-media {
    position: relative;
    height: 182px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background:
        radial-gradient(90% 70% at 50% 22%, rgba(176, 199, 143, 0.32), transparent 68%),
        linear-gradient(180deg, #f4f6ec 0%, #e9efdd 100%);
}
.ml-card-media img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
    transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}
.ml-card:hover .ml-card-media img { transform: scale(1.07); }
.ml-flag {
    position: absolute;
    top: 11px;
    left: 11px;
    z-index: 2;
    background: #e74c3c;
    color: #ffffff;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    padding: 4px 10px;
    border-radius: 99px;
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
}

.ml-card-body { padding: 15px 16px 12px; display: flex; flex-direction: column; gap: 8px; flex: 1 1 auto; }
.ml-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.ml-card-name {
    font-family: 'Poppins', system-ui, sans-serif;
    font-weight: 600;
    font-size: 0.98rem;
    line-height: 1.3;
    color: #40543a;
    margin: 0;
    /* Two lines maximum, so one long product name cannot leave the row
       of cards ragged. */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.ml-card-new {
    flex-shrink: 0;
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    color: #4a5f31;
    background: #eaf1dd;
    border-radius: 99px;
    padding: 3px 8px;
}
/* starsHtml() emits five icons plus a number in one span. Without this it
   is the flex item that gives way when the name runs long, and the
   rating collapses to two and a half stars. */
.ml-card-top .ml-stars { flex-shrink: 0; white-space: nowrap; }

.ml-card-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    color: #728267;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ml-stock {
    height: 5px;
    border-radius: 99px;
    background: #eef1e6;
    overflow: hidden;
    margin-top: 2px;
}
.ml-stock i {
    display: block;
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #a0bc79, #4a5f31);
    transition: width 0.5s ease;
}
.ml-stock.low i { background: linear-gradient(90deg, #f39c12, #e74c3c); }

.ml-card-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: auto; }
.ml-card-price {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.2rem;
    font-weight: 700;
    color: #3e5a25;
}
.ml-card-price small { font-family: 'Poppins', system-ui, sans-serif; font-size: 0.7rem; font-weight: 500; color: #8b977f; }
/* The card opens the full product page, so it says so rather than
   leaving the shopper to guess at the whole tile. */
.ml-card-go {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.74rem;
    font-weight: 700;
    color: #6a8a45;
    white-space: nowrap;
    transition: gap 0.22s ease;
}
.ml-card:hover .ml-card-go { gap: 10px; }

.ml-card-add {
    margin: 0 16px 16px;
    background: #f2f5ea;
    color: #4a5f31;
    border: 1.5px solid #dfe4cf;
    border-radius: 99px;
    padding: 10px 16px;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.22s ease;
}
.ml-card-add:hover { background: #4a5f31; border-color: #4a5f31; color: #ffffff; }

@media (max-width: 767px) {
    .plants-grid-section { padding: 52px 0 46px; }
    .ml-card-media { height: 150px; }
    .ml-grid-head { justify-content: center; text-align: center; }
    .ml-card-add { margin: 0 12px 12px; padding: 9px 10px; font-size: 0.78rem; }
    .ml-card-body { padding: 13px 12px 10px; }
    .ml-card-go { display: none; }
}

@media (prefers-reduced-motion: reduce) {
    .ml-card:hover { transform: none; }
    .ml-card-media img, .ml-card:hover .ml-card-media img { transition: none; }
    .ml-card:hover .ml-card-media img { transform: none; }
}

/* Category Filter Chips Bar */
.prod-chip-bar {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin-bottom: 35px;
}
.prod-nav-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #ffffff;
    color: #3b4e26;
    border: 1.5px solid #d4e4be;
    font-size: 0.9rem;
    font-weight: 600;
    padding: 8px 22px;
    border-radius: 50px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.prod-nav-chip:hover {
    background: #eef6e6;
    color: #1a2412;
    border-color: #a0bc79;
}
.prod-nav-chip.active {
    background: #4a5f31;
    color: #ffffff;
    border-color: #4a5f31;
    box-shadow: 0 4px 14px rgba(74, 95, 49, 0.25);
}

/* ============================================================
   HARVEST CTA
   ============================================================
   This was a dark photograph with a white card floating in the middle of
   it, and it fought the page. Everything around it is a light cream and
   sage wash, so an 88%-black slab with one small centred card read as a
   hole punched through the design rather than a closing invitation. It
   also repeated the "We Grow Best Food" headline already used by the
   spotlight directly above, so the page said the same thing twice in a
   row, and its 797 KB background was the heaviest asset on the page
   behind a decorative gradient.

   It is now built from the same palette as the rest of the section:
   a light wash, one real photograph in a tall arched frame rather than a
   full-bleed wash, its own headline, and a row of live counts. The counts
   come from the database, because a banner that states how many growers
   and markets are actually trading carries more weight than another
   paragraph of adjectives.
   ============================================================ */
.prod-cta {
    position: relative;
    display: grid;
    grid-template-columns: 1.25fr 0.75fr;
    align-items: center;
    gap: 34px;
    margin: 46px 0 8px;
    padding: 40px 44px;
    border-radius: 26px;
    background:
        radial-gradient(70% 120% at 88% 0%, rgba(243, 156, 18, 0.14), transparent 62%),
        radial-gradient(80% 130% at 4% 100%, rgba(160, 188, 121, 0.30), transparent 66%),
        linear-gradient(120deg, #fdfcf6 0%, #f6f3e6 46%, #eaf0dd 100%);
    border: 1.5px solid #dfe3cd;
    overflow: hidden;
}
.prod-cta::after {
    /* A single hairline accent, so the band reads as a panel rather than
       another card. */
    content: '';
    position: absolute;
    left: 0;
    top: 26px;
    bottom: 26px;
    width: 4px;
    background: linear-gradient(180deg, #e67e22, #a0bc79);
    border-radius: 0 4px 4px 0;
}
.prod-cta-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1.1px;
    text-transform: uppercase;
    color: #4a5f31;
    background: #ffffff;
    border: 1px solid #dfe3cd;
    border-radius: 99px;
    padding: 6px 14px;
    margin-bottom: 14px;
}
.prod-cta-title {
    font-family: 'Fraunces', Georgia, serif;
    font-weight: 700;
    font-size: clamp(1.55rem, 2.6vw, 2.1rem);
    line-height: 1.18;
    color: #3e5a25;
    margin: 0 0 10px;
}
.prod-cta-copy {
    font-size: 0.93rem;
    line-height: 1.62;
    color: #55643f;
    margin: 0 0 22px;
    max-width: 46ch;
}
.prod-cta-stats {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.prod-cta-stat b {
    display: block;
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.7rem;
    font-weight: 700;
    line-height: 1;
    color: #3e5a25;
}
.prod-cta-stat span {
    display: block;
    margin-top: 5px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.7px;
    text-transform: uppercase;
    color: #7a8a68;
}
.prod-cta-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.prod-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    border-radius: 99px;
    padding: 12px 26px;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 0.86rem;
    font-weight: 600;
    text-decoration: none;
    border: 1.5px solid transparent;
    transition: all 0.22s ease;
}
.prod-cta-btn-primary { background: #4a5f31; color: #ffffff; }
.prod-cta-btn-primary:hover { background: #3a4b26; color: #ffffff; transform: translateY(-2px); }
.prod-cta-btn-ghost { background: #ffffff; color: #4a5f31; border-color: #c9d3b0; }
.prod-cta-btn-ghost:hover { border-color: #4a5f31; color: #3e5a25; transform: translateY(-2px); }

/* The photo sits in a tall arched frame. The arch echoes the shape used
   in the hero, which ties the page together without repeating the dark
   overlay treatment this band used to have. */
.prod-cta-figure {
    position: relative;
    margin: 0;
    border-radius: 150px 150px 20px 20px;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(45, 65, 28, 0.18);
    aspect-ratio: 3 / 4;
}
.prod-cta-figure img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.prod-cta-figure figcaption {
    position: absolute;
    left: 12px;
    right: 12px;
    bottom: 12px;
    background: rgba(255, 255, 255, 0.93);
    border-radius: 14px;
    padding: 9px 13px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #40543a;
}

@media (max-width: 991px) {
    .prod-cta { grid-template-columns: 1fr; gap: 26px; padding: 32px 26px; text-align: left; }
    .prod-cta-figure { max-width: 300px; justify-self: center; }
}
@media (max-width: 575px) {
    .prod-cta { padding: 26px 20px; border-radius: 20px; }
    .prod-cta-stats { gap: 22px; }
    .prod-cta-stat b { font-size: 1.4rem; }
    .prod-cta-actions .prod-cta-btn { flex: 1 1 100%; justify-content: center; }
}
@media (prefers-reduced-motion: reduce) {
    .prod-cta-btn:hover { transform: none; }
}
</style>

<!-- ============================================================
     HERO SECTION: 3D PRODUCT SCENE
     ============================================================ -->
<!-- ============================================================
<!-- ============================================================
<!-- ============================================================
     HERO SECTION: VIDEO PORTAL WITH FLOATING PRODUCE
     ============================================================ -->
<section class="prod-stage">

    <span class="prod-stage-live"><span class="dot"></span> Live from the field</span>

    <div class="prod-stage-grid">

        <div class="prod-stage-copy">
            <span class="prod-stage-kicker">
                <i class="fa-solid fa-leaf"></i> Fresh off the stall
            </span>

            <h1 class="prod-stage-title">
                <?php
                if ($q !== '') echo 'Search results for "' . htmlspecialchars($q) . '"';
                elseif ($category) {
                    $cName = 'Category Harvests';
                    foreach ($categories as $cat) {
                        if ((int)$cat['category_id'] === $category) { $cName = $cat['category_name']; break; }
                    }
                    echo htmlspecialchars($cName);
                } else {
                    echo "Today's produce, reserved before you arrive.";
                }
                ?>
            </h1>

            <p class="prod-stage-lead">
                Browse live stock from every farmer at your local market and pre-order for pickup &mdash; no waiting, no sold-out surprises.
            </p>

            <div class="prod-stage-actions">
                <a href="<?= mlq('q', '', ['category' => $category, 'market' => $market]) ?>" class="prod-stage-btn">
                    <i class="fa-solid fa-basket-shopping"></i> Browse products
                </a>
                <a href="<?= ML_asset('markets') ?>" class="prod-stage-btn ghost">
                    <i class="fa-solid fa-store"></i> View today's markets
                </a>
            </div>

            <form method="GET" action="<?= ML_asset('products') ?>" class="prod-stage-search">
                <?php if ($category): ?><input type="hidden" name="category" value="<?= $category ?>"><?php endif; ?>
                <?php if ($market): ?><input type="hidden" name="market" value="<?= $market ?>"><?php endif; ?>
                <i class="fa-solid fa-magnifying-glass ms-1 me-2" style="color: #4a5f31;"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search fruits, vegetables, stall names, or categories..." data-live-search>
                <button type="submit">
                    <span>Search</span> <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </div>

        <!-- The footage, cut into a circle instead of run edge to edge. -->
        <div class="prod-portal">

            <div class="prod-portal-glow" aria-hidden="true"></div>

            <div class="prod-portal-orbit" aria-hidden="true">
                <span class="prod-portal-dot d1"></span>
                <span class="prod-portal-dot d2"></span>
                <span class="prod-portal-dot d3"></span>
            </div>

            <div class="prod-portal-frame">
                <!-- Corrected path. This used to ask for
                     Uploads/video/products-hero.mp4, which does not exist on
                     disk and 404s; the footage has always been at
                     Uploads/products-hero.mp4. -->
                <video src="<?php echo ML_asset('Uploads/products-hero.mp4'); ?>" autoplay muted loop playsinline></video>
            </div>

            <div class="prod-portal-shine" aria-hidden="true"></div>

            <!-- Waypoint #1: the flying tomato's launch pad. With the cards
                 gone it sits in the middle of the circle, so the tomato
                 looks like it rises out of the footage itself. -->
            <span id="tomatoStartAnchor" class="tomato-anchor" style="top: 50%; left: 50%; transform: translate(-50%, -50%);"></span>
        </div>

    </div>
</section>

<!-- ============================================================
     SECTION 1: 4 COLORED FEATURE CARDS (3D Cutouts - No Square Borders)
     ============================================================ -->
    <div class="container-fluid p-0">
    <div class="row g-0">
        <?php foreach ($featureProduceCards as $idx => $fpc): 
            $fpcUrl = ML_asset('products') . '?q=' . urlencode($fpc['query']);
        ?>
            <div class="col-6 col-md-3">
                <div class="prod-feature-card" style="background: <?php echo $fpc['bg']; ?>;">
                    <div>
                        <h3 class="pfc-title"><?php echo $fpc['title']; ?></h3>
                        <p class="pfc-desc"><?php echo $fpc['desc']; ?></p>
                        <a href="<?php echo $fpcUrl; ?>" class="pfc-btn">
                            <span>Read More</span> <i class="fa-solid fa-angle-right"></i>
                        </a>
                    </div>
                    
                    <!-- 3D Produce Cutout Without Box Borders -->
                    <div class="pfc-thumb <?= $fpc['blend'] ? 'blend-mode' : '' ?>">
                        <!-- Waypoint anchor so JS knows the launch point for each fruit -->
                        <span class="pfc-fly-anchor tomato-anchor" id="pfcAnchor<?= $idx ?>" style="position:absolute;top:40%;left:50%;transform:translate(-50%,-50%);"></span>
                        <img
                            src="<?php echo $fpc['img']; ?>"
                            alt="<?php echo $fpc['title']; ?>"
                            id="pfcFlyer<?= $idx ?>"
                            class="pfc-fly-img"
                            data-fpc-idx="<?= $idx ?>"
                        >
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============================================================
     SECTION 2: "WE GROW BEST FOOD" SPOTLIGHT (Woven Organic Harvest Basket)
     ============================================================ -->
<section class="best-food-spotlight">
    <div class="container">
        <div class="text-center">
            <h2 class="bf-title">We Grow Best Food <i class="fa-solid fa-leaf text-success" style="font-size: 1.8rem;"></i></h2>
            <p class="bf-sub">
                Dedicated to authentic organic agriculture that delivers pure, morning-harvested goodness directly to community tables.
            </p>
        </div>

        <div class="row align-items-center g-4">
            
            <!-- Left 3 Features -->
            <div class="col-lg-3 col-md-6 order-2 order-lg-1 bf-feature-group">
                <div class="bf-item">
                    <div class="bf-icon"><i class="fa-solid fa-leaf"></i></div>
                    <div>
                        <h5 class="bf-item-title">Fresh</h5>
                        <p class="bf-item-desc">Harvested at early sunrise and delivered straight to market stalls without cold-storage delays.</p>
                    </div>
                </div>

                <div class="bf-item">
                    <div class="bf-icon"><i class="fa-solid fa-heart"></i></div>
                    <div>
                        <h5 class="bf-item-title">Healthy</h5>
                        <p class="bf-item-desc">100% natural, nutrient-dense harvest rich in essential vitamins, minerals, and enzymes.</p>
                    </div>
                </div>

                <div class="bf-item">
                    <div class="bf-icon"><i class="fa-solid fa-wheat-awn"></i></div>
                    <div>
                        <h5 class="bf-item-title">Eco</h5>
                        <p class="bf-item-desc">Sustainable ecological agriculture preserving fertile soil biology and saving water.</p>
                    </div>
                </div>
            </div>

            <!-- Center Spotlight: the flying tomato's resting place -->
            <div class="col-lg-6 col-md-12 order-1 order-lg-2 text-center my-4 my-lg-0">
                <div id="tomatoLandingBox" class="center-spotlight-box mx-auto" style="max-width: 500px;">
                    <div class="center-spotlight-tag">
                        Personal recommendation of Regional Organic Growers
                        <div class="center-spotlight-sign">V. Mark &#10004;</div>
                    </div>

                    <!-- The tomato that flies down the page on scroll lands
                         here and stays. Same asset as #scrollTomato, so it is
                         already in the browser cache and this costs no second
                         download. The local file replaces the Unsplash basket
                         photo that used to be hard-coded here: an external URL
                         in the centerpiece breaks whenever the CDN does, and
                         it had no relation to the tomato the script flies. -->
                    <div class="center-spotlight-figure">
                        <!-- Waypoint #2. Centred on the figure rather than left
                             in the normal flow, because the flight script aims
                             the tomato's midpoint at this point, not its edge. -->
                        <span id="tomatoEndAnchor" class="tomato-anchor"></span>
                        <!-- No <img> tag on purpose. The tomato that flies down out
                             of the hero IS the centerpiece: the script at the bottom
                             of this file moves that same node into this slot when it
                             lands, and takes it back out when the reader scrolls up.
                             A hard-coded image here would be a second tomato sitting
                             underneath the one that actually flew in. -->
                    </div>
                </div>
            </div>

            <!-- Right 3 Features -->
            <div class="col-lg-3 col-md-6 order-3 order-lg-3 bf-feature-group">
                <div class="bf-item">
                    <div class="bf-icon"><i class="fa-solid fa-thumbs-up"></i></div>
                    <div>
                        <h5 class="bf-item-title">Tasty</h5>
                        <p class="bf-item-desc">Naturally ripened on the vine for deep authentic flavor that commercial retail lacks.</p>
                    </div>
                </div>

                <div class="bf-item">
                    <div class="bf-icon"><i class="fa-solid fa-apple-whole"></i></div>
                    <div>
                        <h5 class="bf-item-title">Yummy</h5>
                        <p class="bf-item-desc">Delicious seasonal fruits, vegetables, dairy &amp; bakery goods hand-picked daily from family stalls.</p>
                    </div>
                </div>

                <div class="bf-item">
                    <div class="bf-icon"><i class="fa-solid fa-award"></i></div>
                    <div>
                        <h5 class="bf-item-title">Premium</h5>
                        <p class="bf-item-desc">Hand-graded quality standard directly from certified growers with verified producer standards.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 3: "OUR GROWN UP PLANTS" PRODUCTS GRID (With DB Filters & Sorting)
     ============================================================ -->
<section class="plants-grid-section" id="productsSection">
    <div class="container" style="max-width: 1240px;">
        <div class="text-center mb-4">
            <h2 class="bf-title">Our Grown Up Plants <i class="fa-solid fa-leaf text-success" style="font-size: 1.8rem;"></i></h2>
            <p class="bf-sub mb-4">We grow only the best fruits and vegetables to every family!</p>
        </div>

        <!-- Category Filter Pills Bar -->
        <div class="prod-chip-bar">
            <a href="<?= mlq('category', 0, ['category' => '', 'market' => $market, 'q' => $q]) ?>" class="prod-nav-chip <?= !$category ? 'active' : '' ?>">
                <i class="fa-solid fa-border-all"></i> All Categories
            </a>
            <?php foreach ($categories as $c): ?>
                <a href="<?= mlq('category', $c['category_id'], ['market' => $market, 'q' => $q]) ?>" class="prod-nav-chip <?= $category === (int)$c['category_id'] ? 'active' : '' ?>">
                    <?= catIcon($c['category_name']) ?>
                    <span><?= sanitize_output($c['category_name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Wide Filter / Sort Controls Bar -->
        <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white" style="border: 1.5px solid #dce8cf !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="small text-muted fw-semibold">
                    <i class="fa-solid fa-boxes-stacked text-success me-1"></i> Showing <strong class="text-dark"><?= count($products) ?></strong> fresh items
                    <?php if ($category || $market || $q !== ''): ?>
                        <a href="<?= ML_asset('products') ?>" class="ms-2 text-danger text-decoration-none small"><i class="fa-solid fa-rotate-left"></i> Reset Filters</a>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <!-- Market Select -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-bold text-muted mb-0"><i class="fa-solid fa-store text-success"></i> Market:</label>
                        <select class="form-select form-select-sm rounded-pill border-success-subtle shadow-none px-3 py-2" style="min-width: 210px;" onchange="location=this.value;">
                            <option value="<?= mlq('market', 0, ['market' => '', 'category' => $category, 'q' => $q]) ?>">All Community Markets</option>
                            <?php foreach ($markets as $mk): ?>
                                <option value="<?= mlq('market', $mk['market_id'], ['category' => $category, 'q' => $q]) ?>" <?= $market === (int)$mk['market_id'] ? 'selected' : '' ?>>
                                    <?= sanitize_output($mk['market_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sort Select -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-bold text-muted mb-0"><i class="fa-solid fa-arrow-down-wide-short text-success"></i> Sort:</label>
                        <select class="form-select form-select-sm rounded-pill border-success-subtle shadow-none px-3 py-2" style="min-width: 190px;" onchange="location=this.value;">
                            <option value="<?= mlq('sort', '', ['sort' => '']) ?>" <?= $sort === 'new' ? 'selected' : '' ?>>Newest Arrivals</option>
                            <option value="<?= mlq('sort', 'price_asc') ?>" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="<?= mlq('sort', 'price_desc') ?>" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="<?= mlq('sort', 'rating') ?>" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                            <option value="<?= mlq('sort', 'name') ?>" <?= $sort === 'name' ? 'selected' : '' ?>>Name: A-Z</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recently Viewed (device-local strip) -->
        <style>
            .ml-recent-item {
                display: flex; gap: 10px; align-items: center; min-width: 228px; max-width: 262px; flex: 0 0 auto;
                background: #ffffff; border: 1.5px solid #dce8cf; border-radius: 14px; padding: 8px 10px;
                text-decoration: none; color: #17240f; transition: border-color .2s ease, box-shadow .2s ease;
            }
            .ml-recent-item:hover { border-color: #4a5f31; box-shadow: 0 6px 16px rgba(45,65,28,.12); }
            .ml-recent-item img { width: 44px; height: 44px; border-radius: 10px; object-fit: cover; }
            .ml-recent-ph { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: #eef2e6; color: #4a5f31; }
            .ml-recent-meta { display: flex; flex-direction: column; line-height: 1.25; overflow: hidden; }
            .ml-recent-name { font-size: .82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
            .ml-recent-price { font-size: .78rem; color: #4a5f31; font-weight: 700; }
        </style>
        <div data-ml-recent-host class="mb-4" style="display:none;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left text-success me-2"></i>Recently Viewed</h6>
                <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none p-0" data-ml-recent-clear><i class="fa-solid fa-eraser me-1"></i>Clear</button>
            </div>
            <div class="d-flex gap-2 overflow-auto pb-2" id="mlRecentList"></div>
        </div>

        <!-- Products: a plain, calm grid. Click any card for its details. -->
        <?php if (empty($products)): ?>
            <div class="card border-0 rounded-4 shadow-sm p-5 text-center bg-white">
                <i class="fa-solid fa-basket-shopping text-muted fs-1 mb-3"></i>
                <h5 class="fw-bold">No products found matching your search</h5>
                <p class="text-muted mb-3">Try searching for other fruits, vegetables, or resetting your active filters.</p>
                <a href="<?= ML_asset('products') ?>" class="btn btn-outline-success rounded-pill px-4 mx-auto" style="width: fit-content;">View All Products</a>
            </div>
        <?php else: ?>
            <?php
            // Only the first eight render up front. The remainder stay in the
            // DOM behind the "Show all" control, so revealing them costs
            // nothing and the count still reflects the real result set.
            $mlTotal = count($products);
            $mlLimit = 8;
            $mlHeld = max(0, $mlTotal - $mlLimit);
            ?>
            <div class="ml-grid-head">
                <span class="ml-grid-count">
                    Showing <b><?= min($mlLimit, $mlTotal) ?></b> of <b><?= $mlTotal ?></b> products
                </span>
                <?php if ($mlHeld > 0): ?>
                    <button type="button" class="ml-more-btn" data-ml-more aria-expanded="false" aria-controls="mlProductsGrid">
                        <span data-ml-more-label>Show all <?= $mlHeld ?> more</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                <?php endif; ?>
            </div>

            <div class="ml-grid" id="mlProductsGrid" data-ml-grid>
                <?php foreach (array_values($products) as $mlIdx => $p):
                    $pImg = getLocalOrCustomImg($p);
                    $pUrl = ML_asset('product') . '?id=' . $p['product_id'];
                    $stock = (int)($p['stock_quantity'] ?? 0);
                    $isLow = $stock > 0 && $stock <= 10;
                    // Bar length reads against a 30-unit ceiling. Past that
                    // every bar looks identical, and the exact figure is only
                    // worth surfacing when stock is scarce.
                    $pct = $stock <= 0 ? 0 : max(7, min(100, (int)round($stock / 30 * 100)));
                    $rating = (float)($p['avg_rating'] ?? 0);
                ?>
                    <article class="ml-card<?= $mlIdx >= $mlLimit ? ' is-beyond' : '' ?>">
                        <a href="<?= $pUrl ?>" class="ml-card-link">
                            <div class="ml-card-media">
                                <?php if ($isLow): ?>
                                    <span class="ml-flag">Only <?= $stock ?> left</span>
                                <?php endif; ?>
                                <img src="<?= $pImg ?>" alt="<?= sanitize_output($p['name']) ?>" loading="lazy">
                            </div>
                            <div class="ml-card-body">
                                <div class="ml-card-top">
                                    <h3 class="ml-card-name"><?= sanitize_output($p['name']) ?></h3>
                                    <?php if ($rating > 0): ?>
                                        <?= starsHtml($rating, 12) ?>
                                    <?php else: ?>
                                        <span class="ml-card-new">New</span>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-card-meta">
                                    <i class="fa-solid fa-store"></i><?= sanitize_output($p['stall_name']) ?>
                                </div>
                                <div class="ml-stock <?= $isLow ? 'low' : '' ?>" title="<?= $stock ?> in stock">
                                    <i style="width: <?= $pct ?>%"></i>
                                </div>
                                <div class="ml-card-foot">
                                    <span class="ml-card-price">
                                        <?= money($p['price']) ?>
                                        <small>/ <?= sanitize_output($p['unit'] ?? 'unit') ?></small>
                                    </span>
                                    <span class="ml-card-go">Details <i class="fa-solid fa-arrow-right"></i></span>
                                </div>
                            </div>
                        </a>
                        <button type="button" class="ml-card-add" data-add-cart="<?= $p['product_id'] ?>">
                            <i class="fa-solid fa-cart-plus"></i> Add to cart
                        </button>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ============================================================
             SECTION 4: HARVEST CTA
             ============================================================ -->
        <section class="prod-cta">
            <div>
                <span class="prod-cta-kicker"><i class="fa-solid fa-seedling"></i> Fresh From Our Fields</span>
                <h2 class="prod-cta-title">Reserve your weekly basket, pick it up at the stall</h2>
                <p class="prod-cta-copy">
                    Order from verified growers near you and collect your harvest at the market stall
                    the same morning it is picked. No cold-storage wait in between.
                </p>

                <div class="prod-cta-stats">
                    <div class="prod-cta-stat">
                        <b><?= (int)($harvestStats['products'] ?? 0) ?></b>
                        <span>Items listed</span>
                    </div>
                    <div class="prod-cta-stat">
                        <b><?= (int)($harvestStats['markets'] ?? 0) ?></b>
                        <span>Live markets</span>
                    </div>
                    <div class="prod-cta-stat">
                        <b><?= (int)($harvestStats['farmers'] ?? 0) ?></b>
                        <span>Verified growers</span>
                    </div>
                </div>

                <div class="prod-cta-actions">
                    <a href="<?= ML_asset('markets') ?>" class="prod-cta-btn prod-cta-btn-primary">
                        <i class="fa-solid fa-store"></i> View Active Markets
                    </a>
                    <a href="<?= ML_asset('farmers') ?>" class="prod-cta-btn prod-cta-btn-ghost">
                        <i class="fa-solid fa-people-group"></i> Meet the Growers
                    </a>
                </div>
            </div>

            <figure class="prod-cta-figure">
                <img src="<?= ML_asset('Uploads/img/harvest_basket.jpg') ?>" alt="A woven basket filled with this morning's harvest" loading="lazy" width="800" height="1210">
                <figcaption>Picked at sunrise, on your stall by noon</figcaption>
            </figure>
        </section>

    </div>
</section>

<script>
(function () {
    if (!window.mlRecent) return;
    var host = document.querySelector('[data-ml-recent-host]');
    window.mlRecent.render(host);
    var clear = document.querySelector('[data-ml-recent-clear]');
    if (clear) clear.addEventListener('click', function () {
        try { localStorage.removeItem(window.mlRecent.KEY); } catch (e) {}
        window.mlRecent.render(host);
    });
})();
</script>

<!-- The flying tomato lives here, as a direct sibling of the page
     sections (not nested inside anything with transform/perspective),
     because position:fixed only travels relative to the viewport when
     none of its ancestors set one of those properties. -->
<img id="scrollTomato" src="<?php echo ML_asset('Uploads/img/tomato-3d.png'); ?>" alt="" aria-hidden="true">

<script>
(function () {
    var flyer = document.getElementById('scrollTomato');
    var startEl = document.getElementById('tomatoStartAnchor');
    var endEl = document.getElementById('tomatoEndAnchor');
    var landingBox = document.getElementById('tomatoLandingBox');
    var figure = document.querySelector('.center-spotlight-figure');
    if (!flyer || !startEl || !endEl || !figure) return;

    // Remembered so the node can be put back exactly where the browser
    // first put it when the reader scrolls back up.
    var flyerHome = flyer.parentNode;
    var flyerNext = flyer.nextSibling;
    var seated = false;

    function seat() {
        if (seated) return;
        seated = true;
        // The id is dropped while seated because #scrollTomato is an id
        // selector, and an id beats the .center-spotlight-img class that
        // now needs to control size and position. The script keeps its
        // own reference, so losing the id costs nothing.
        flyer.removeAttribute('id');
        flyer.removeAttribute('style');
        flyer.className = 'center-spotlight-img';
        flyer.setAttribute('alt', 'Sun-ripened tomato grown by a verified local farmer');
        figure.appendChild(flyer);
        if (landingBox) landingBox.classList.add('tomato-landed');
    }

    function release() {
        if (!seated) return;
        seated = false;
        flyer.className = '';
        flyer.removeAttribute('alt');
        flyer.id = 'scrollTomato';
        flyerHome.insertBefore(flyer, flyerNext);
        if (landingBox) landingBox.classList.remove('tomato-landed');
    }

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // No flight for these readers, but the centerpiece still gets its
    // tomato, otherwise the slot would sit empty forever.
    if (reduceMotion) { seat(); return; }

    var start, end, ticking = false;

    // Re-measure the two waypoints in document coordinates every time the
    // layout can change (load, resize, fonts/images settling in), so the
    // flight path stays correct at every breakpoint instead of being
    // computed once against a layout that hasn't finished settling.
    function measure() {
        var s = startEl.getBoundingClientRect();
        var e = endEl.getBoundingClientRect();
        start = { x: s.left + window.scrollX, y: s.top + window.scrollY };
        end = { x: e.left + window.scrollX, y: e.top + window.scrollY };
    }

    function lerp(a, b, t) { return a + (b - a) * t; }
    function easeInOutQuad(t) { return t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2; }
    function clamp01(v) { return Math.max(0, Math.min(1, v)); }

    function update() {
        ticking = false;
        var scrollY = window.scrollY;
        var vh = window.innerHeight;

        // The flight begins once the hero has scrolled roughly half a
        // viewport up, and finishes just before the centerpiece reaches
        // its resting spot - not the instant either element enters view.
        var travelStart = start.y - vh * 0.6;
        var travelEnd = end.y - vh * 0.45;
        var span = travelEnd - travelStart;
        var progress = span > 0 ? clamp01((scrollY - travelStart) / span) : 0;

        // Once it has arrived the tomato belongs to the centerpiece, so
        // the flight maths has to stop writing to it completely. Handing
        // it over and taking it back before any positioning happens is
        // what stops the two states from fighting over the same node.
        if (progress >= 0.97) { seat(); return; }
        if (seated) release();

        if (scrollY < travelStart - 300 || scrollY > travelEnd + 400) {
            flyer.style.opacity = 0;
        } else {
            var t = easeInOutQuad(progress);
            var x = lerp(start.x, end.x, t);
            var y = lerp(start.y, end.y, t) - scrollY - flyer.offsetHeight * 0.5;
            // Grows big and 3D through the middle of the scroll (the
            // "growth" moment), then settles large on the basket photo
            // instead of shrinking away to nothing.
            var scale = lerp(0.75, 1.05, t) + Math.sin(progress * Math.PI) * 0.45;
            var rot = lerp(-12, 10, t) + Math.sin(progress * Math.PI) * -8;
            var fadeIn = progress < 0.06 ? progress / 0.06 : 1;
            var fadeOut = progress > 0.92 ? (1 - progress) / 0.08 : 1;

            flyer.style.transform =
                'translate3d(' + (x - flyer.offsetWidth / 2) + 'px,' + y + 'px, 0) ' +
                'scale(' + scale + ') rotate(' + rot + 'deg)';
            flyer.style.opacity = clamp01(fadeIn * fadeOut);
        }
    }

    function onScroll() {
        if (!ticking) { requestAnimationFrame(update); ticking = true; }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', function () { measure(); update(); });
    window.addEventListener('load', function () { measure(); update(); });
    measure();
    update();
})();
</script>

<!-- ============================================================
<!-- ============================================================
     FLYING PRODUCE: CLONES fly, originals stay on cards.
     Clones land AROUND the tomato like the reference image.
     ============================================================ -->
<script>
(function () {
    var endEl      = document.getElementById('tomatoEndAnchor');
    var landingBox = document.getElementById('tomatoLandingBox');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!endEl || !landingBox || reduceMotion) return;

    /* Corner positions are fractions of the landing box, not pixels.
       They used to be hard-coded offsets (-120, -90 and friends), which
       only ever looked right when the box happened to be its 500px
       desktop width. The box is max-width 500px and shrinks further on
       phones, so a fixed -120px spread pushed the outer clones off the
       edge of the card and on top of each other. Fractions keep the same
       visual arrangement at every width. */
    var corners = [
        { fx: -0.60, fy: -0.56, rot: -15 },
        { fx:  0.58, fy: -0.56, rot:  12 },
        { fx: -0.58, fy:  0.54, rot:  10 },
        { fx:  0.56, fy:  0.58, rot:  -8 }
    ];

    /* Sizing comes from the measured box as well, clamped at both ends:
       large enough to stay legible on a phone, never so large that the
       four clones swamp the 290px tomato in the middle. */
    var CLONE_MIN = 74;
    var CLONE_MAX = 168;
    function cloneSizeFor(boxW) {
        return Math.round(Math.max(CLONE_MIN, Math.min(CLONE_MAX, boxW * 0.32)));
    }

    var flyers = [];
    var count  = <?php echo count($featureProduceCards); ?>;

    for (var i = 0; i < count; i++) {
        var orig   = document.getElementById('pfcFlyer' + i);
        var anchor = document.getElementById('pfcAnchor' + i);
        if (!orig || !anchor) continue;
        var clone = new Image();
        clone.src = orig.src;
        clone.alt = orig.alt;
        clone.style.cssText = 'position:fixed;top:0;left:0;width:220px;height:220px;object-fit:contain;pointer-events:none;opacity:0;z-index:2147483640;will-change:transform,opacity;transform:translate3d(-9999px,-9999px,0);filter:drop-shadow(0 22px 36px rgba(0,0,0,0.45))';
        document.body.appendChild(clone);
        flyers.push({ clone: clone, anchor: anchor, stagger: i * 0.05, corner: corners[i] || { fx: 0, fy: 0, rot: 0 }, seated: false, size: 0 });
    }

    if (!flyers.length) return;
    landingBox.style.position = 'relative';
    landingBox.style.overflow = 'visible';

    var end = { x: 0, y: 0 };
    var boxW = 0, boxH = 0, boxX = 0, boxY = 0;

    /* Writes the resting geometry for one clone. Kept separate from
       seat() so a resize can re-run it on clones that are already
       sitting in the box -- seat() used to bake the numbers in once and
       they were never corrected afterwards. */
    function layoutSeated(f, boxW, boxH) {
        var s  = f.size;
        var cx = f.corner.fx * boxW;
        var cy = f.corner.fy * boxH;
        f.clone.style.width  = s + 'px';
        f.clone.style.height = s + 'px';
        f.clone.style.marginLeft = (cx - s / 2) + 'px';
        f.clone.style.marginTop  = (cy - s / 2) + 'px';
        f.clone.style.setProperty('--land-rot', f.corner.rot + 'deg');
    }

    function measure() {
        var e = endEl.getBoundingClientRect();
        end.x = e.left + window.scrollX;
        end.y = e.top  + window.scrollY;

        var boxRect = landingBox.getBoundingClientRect();
        boxW = boxRect.width;
        boxH = boxRect.height;
        boxX = boxRect.left + window.scrollX;
        boxY = boxRect.top  + window.scrollY;
        flyers.forEach(function (f) {
            var s = f.anchor.getBoundingClientRect();
            f.start = { x: s.left + window.scrollX, y: s.top + window.scrollY };
            f.size = cloneSizeFor(boxW);
        });
    }

    function seat(f) {
        if (f.seated) return;
        f.seated = true;
        if (f.clone.parentNode) f.clone.parentNode.removeChild(f.clone);
        f.clone.style.cssText = 'position:absolute;object-fit:contain;pointer-events:none;filter:drop-shadow(0 16px 30px rgba(0,0,0,0.5));z-index:5;left:50%;top:50%;animation:fruitLand 0.55s cubic-bezier(0.34,1.56,0.64,1) both';
        landingBox.appendChild(f.clone);
        layoutSeated(f, boxW, boxH);
    }

    function release(f) {
        if (!f.seated) return;
        f.seated = false;
        if (f.clone.parentNode) f.clone.parentNode.removeChild(f.clone);
        f.clone.style.cssText = 'position:fixed;top:0;left:0;width:220px;height:220px;object-fit:contain;pointer-events:none;opacity:0;z-index:2147483640;will-change:transform,opacity;transform:translate3d(-9999px,-9999px,0);filter:drop-shadow(0 22px 36px rgba(0,0,0,0.45))';
        document.body.appendChild(f.clone);
    }

    function lerp(a,b,t){ return a+(b-a)*t; }
    function easeInOutQuad(t){ return t<0.5?2*t*t:1-Math.pow(-2*t+2,2)/2; }
    function clamp01(v){ return Math.max(0,Math.min(1,v)); }
    var ticking = false;

    function update() {
        ticking = false;
        var scrollY = window.scrollY;
        var vh = window.innerHeight;
        flyers.forEach(function (f, i) {
            var travelStart = f.start.y - vh * 0.55;
            var travelEnd   = end.y - vh * 0.42;
            var span        = travelEnd - travelStart;
            var rawP        = span > 0 ? clamp01((scrollY - travelStart) / span) : 0;
            var progress    = clamp01((rawP - f.stagger) / (1 - f.stagger));
            if (scrollY < travelStart - 300) {
                if (f.seated) release(f);
                f.clone.style.opacity   = '0';
                f.clone.style.transform = 'translate3d(-9999px,-9999px,0)';
                return;
            }
            if (progress >= 0.97) { seat(f); return; }
            if (f.seated) release(f);
            /* Aim at the resting spot derived from the same box geometry
               layoutSeated() uses, instead of the old corner.offsetX/Y.
               Those keys no longer exist, so this used to interpolate
               towards undefined and the clones all converged on the
               centre of the box, piling onto the tomato. Deriving the
               target means the flight ends exactly where the clone is
               about to come to rest. */
            var destX = boxX + boxW / 2 + f.corner.fx * boxW;
            var destY = boxY + boxH / 2 + f.corner.fy * boxH;
            var t   = easeInOutQuad(progress);
            var x   = lerp(f.start.x, destX, t);
            var y   = lerp(f.start.y, destY, t) - scrollY - 110;
            var sc  = lerp(1.0, 0.85, t) + Math.sin(progress * Math.PI) * 0.18;
            var rot = lerp(i % 2 === 0 ? -14 : 14, f.corner.rot, t);
            var fi  = progress < 0.07 ? progress / 0.07 : 1;
            var fo  = progress > 0.92 ? (1 - progress) / 0.08 : 1;
            f.clone.style.transform = 'translate3d('+(x-110)+'px,'+y+'px,0) scale('+sc+') rotate('+rot+'deg)';
            f.clone.style.opacity   = clamp01(fi * fo);
        });
    }

    function onScroll() { if (!ticking) { requestAnimationFrame(update); ticking = true; } }
    window.addEventListener('scroll', onScroll, { passive: true });

    function onResize() {
        measure();
        // Clones already sitting in the box keep their old pixel geometry
        // otherwise, so rotating a phone or resizing a window leaves them
        // clustered around the old centre instead of the new one.
        flyers.forEach(function (f) { if (f.seated) layoutSeated(f, boxW, boxH); });
        update();
    }
    window.addEventListener('resize', onResize);
    window.addEventListener('load',   function(){ onResize(); });
    onResize();
})();
</script>
<style>
/* The landing keyframes rotate through --land-rot instead of a literal
   angle. seat() gives each clone its own tilt inline, but the animation
   runs with fill-mode "both", so the final keyframe stays applied after
   the animation ends and used to overwrite that inline rotation with
   rotate(0deg) -- every clone landed dead upright, ignoring the tilt it
   was given. Reading the angle from a custom property keeps the per-clone
   value intact through the 100% frame. */
@keyframes fruitLand {
    0%   { transform: scale(1.6) rotate(var(--land-rot, 0deg)); opacity: 0.4; }
    55%  { transform: scale(0.88) rotate(var(--land-rot, 0deg)); opacity: 1; }
    100% { transform: scale(1) rotate(var(--land-rot, 0deg)); opacity: 1; }
}
</style>

<!-- ============================================================
     PRODUCTS: SHOW MORE / SHOW LESS
     The section opens with the first eight products so the page stays
     scannable instead of showing an endless wall, and this reveals the
     rest in place. No request is involved, because the extra cards are
     already in the DOM, just held back.
     ============================================================ -->
<script>
(function () {
    var grid = document.querySelector('[data-ml-grid]');
    var btn = document.querySelector('[data-ml-more]');
    if (!grid || !btn) return;

    var beyond = grid.querySelectorAll('.ml-card.is-beyond').length;
    // Nothing held back: the control would be a dead end, so drop it.
    if (!beyond) { btn.hidden = true; return; }

    var label = btn.querySelector('[data-ml-more-label]');
    var more = label ? label.textContent : '';

    function paint() {
        var open = grid.classList.toggle('is-open');
        if (label) label.textContent = open ? 'Show less' : more;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    btn.addEventListener('click', paint);
})();
</script>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>