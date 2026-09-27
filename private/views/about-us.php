<?php
$pageTitle = 'About Us - MarketLink eGreen Basket';
$page = 'about';
include __DIR__ . '/../../public/components/header.php';

$aboutStats = selectData($pdo, "SELECT
    (SELECT COUNT(*) FROM markets WHERE is_active = 1) AS markets,
    (SELECT COUNT(*) FROM farmers WHERE approval_status = 'approved') AS farmers,
    (SELECT COUNT(*) FROM products WHERE is_available = 1) AS products,
    (SELECT COUNT(*) FROM orders) AS orders")[0];
?>

<style>
/* ============================================================
   ABOUT HERO
   Layout adapted from the PGL reference: copy on the left,
   artwork on the right with badges floating around it. The
   palette is the site's own cream-sage wash, reused from the
   products hero (.prod-stage) so the two pages read as one
   system rather than a bolt-on.
   ============================================================ */
.about-hero-section {
    position: relative;
    overflow: hidden;
    isolation: isolate;
    padding: 76px 0 104px;
    background:
        radial-gradient(115% 85% at 80% 12%, rgba(243, 156, 18, 0.13) 0%, transparent 58%),
        radial-gradient(90% 70% at 8% 92%, rgba(176, 199, 143, 0.30) 0%, transparent 62%),
        linear-gradient(152deg, #fdfcf6 0%, #f6f3e6 34%, #eef2e2 68%, #e4ecd8 100%);
    color: #152A21;
    border-bottom: 2px solid #d4d6bc;
}

/* Ambient orbs, re-toned from the reference's purple/blue/pink
   into the site's sage and leaf greens. Decorative only. */
.about-hero-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    z-index: -1;
    pointer-events: none;
}
.about-hero-orb.o1 { width: 320px; height: 320px; top: -60px;  left: -110px; background: rgba(160, 188, 121, 0.38); }
.about-hero-orb.o2 { width: 300px; height: 300px; bottom: -70px; right: -120px; background: rgba(243, 156, 18, 0.16); }
.about-hero-orb.o3 { width: 260px; height: 260px; top: 46%;  left: 42%;     background: rgba(74, 95, 49, 0.12); }

.about-hero-tagline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.72);
    border: 1.5px solid rgba(74, 95, 49, 0.22);
    color: #4a5f31;
    padding: 7px 18px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
}

.about-main-h1 {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(2.1rem, 3.6vw, 3.5rem);
    font-weight: 700;
    line-height: 1.12;
    margin: 18px 0 16px;
    color: #2b3d1f;
}
.about-main-h1 .accent {
    color: #6d8f3f;
}

.about-hero-sub {
    font-size: clamp(1rem, 1.15vw, 1.12rem);
    font-weight: 500;
    line-height: 1.7;
    color: #40543a;
    max-width: 540px;
    margin-bottom: 30px;
}

/* Buttons, in the site's green rather than the reference's blue */
.about-btn-primary,
.about-btn-ghost {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 13px 28px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.94rem;
    text-decoration: none;
    transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease, color 0.25s ease;
}
.about-btn-primary {
    background: linear-gradient(135deg, #4a5f31 0%, #6d8f3f 100%);
    color: #fff;
    border: 1.5px solid transparent;
    box-shadow: 0 10px 24px rgba(74, 95, 49, 0.28);
}
.about-btn-primary:hover {
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 16px 32px rgba(74, 95, 49, 0.34);
}
.about-btn-ghost {
    background: rgba(255, 255, 255, 0.7);
    color: #2b3d1f;
    border: 1.5px solid rgba(74, 95, 49, 0.3);
}
.about-btn-ghost:hover {
    background: #fff;
    color: #2b3d1f;
    transform: translateY(-3px);
    box-shadow: 0 12px 26px rgba(45, 65, 28, 0.12);
}

/* Artwork column */
.about-hero-figure {
    position: relative;
    max-width: 470px;
    margin: 0 auto;
    padding: 18px;
}
.about-hero-figure::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 34px;
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.86) 0%, rgba(237, 247, 228, 0.72) 100%);
    border: 1.5px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 22px 54px rgba(45, 65, 28, 0.14);
    z-index: -1;
}
.about-hero-img {
    display: block;
    width: 100%;
    border-radius: 24px;
    object-fit: cover;
    aspect-ratio: 4 / 3;
    animation: aboutHeroFloat 7s ease-in-out infinite;
}

@keyframes aboutHeroFloat {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-14px); }
}

/* Badges floating around the artwork. The reference pinned these with
   percentage offsets, which pushed two of them outside the card on
   narrow screens, so they are clamped to the figure's own edges. */
.about-hero-badge {
    position: absolute;
    z-index: 3;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #fff;
    border: 1.5px solid #dce8cf;
    color: #2b3d1f;
    padding: 9px 15px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
    box-shadow: 0 10px 26px rgba(45, 65, 28, 0.15);
    animation: aboutHeroFloat 7s ease-in-out infinite;
}
.about-hero-badge.b1 { top: 4%;    left: -18px;  animation-delay: -0.6s; }
.about-hero-badge.b2 { top: 16%;   right: -14px; animation-delay: -1.4s; }
.about-hero-badge.b3 { bottom: 14%; left: -14px; animation-delay: -2.2s; }
.about-hero-badge.b4 { bottom: 4%;  right: -18px; animation-delay: -3.0s; }

.about-hero-badge i { font-size: 0.95rem; }
.about-hero-badge.b1 i { color: #6d8f3f; }
.about-hero-badge.b2 i { color: #e67e22; }
.about-hero-badge.b3 i { color: #4a5f31; }
.about-hero-badge.b4 i { color: #a0bc79; }

@media (max-width: 991.98px) {
    .about-hero-section { padding: 54px 0 96px; }
    .about-hero-figure  { max-width: 420px; margin-top: 34px; }
    .about-hero-badge   { font-size: 0.74rem; padding: 7px 12px; }
    .about-hero-badge.b1 { left: -6px;  }
    .about-hero-badge.b3 { left: -6px;  }
    .about-hero-badge.b2 { right: -6px; }
    .about-hero-badge.b4 { right: -6px; }
}
@media (max-width: 575.98px) {
    /* Below this width an absolutely positioned pill plus its icon no
       longer fits beside the artwork, so they sit in a plain row. */
    .about-hero-figure { margin-top: 26px; }
    .about-hero-badge {
        position: static;
        animation: none;
        box-shadow: 0 6px 16px rgba(45, 65, 28, 0.1);
    }
}

/* The two site-wide reduced-motion blocks only cover parallax and the
   scroll-reveal utility, so they leave plain `animation` declarations
   running. The drifting image and badges are exactly that kind of
   motion, so they are switched off here for readers who asked for it. */
@media (prefers-reduced-motion: reduce) {
    .about-hero-img,
    .about-hero-badge {
        animation: none !important;
    }
    .about-hero-badge.b1 { top: 2%;    left: 0; }
    .about-hero-badge.b2 { top: 14%;   right: 0; }
    .about-hero-badge.b3 { bottom: 12%; left: 0; }
    .about-hero-badge.b4 { bottom: 2%; right: 0; }
}

/* Feature & Mission Cards */
.about-feature-box {
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 24px;
    padding: 32px 28px;
    height: 100%;
    box-shadow: 0 10px 30px rgba(45, 65, 28, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}
.about-feature-box:hover {
    transform: translateY(-6px);
    border-color: #4a5f31;
    box-shadow: 0 18px 40px rgba(45, 65, 28, 0.14);
}

.about-box-icon {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    background: #eef6e6;
    color: #3e5a25;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    margin-bottom: 20px;
    box-shadow: 0 4px 14px rgba(62, 90, 37, 0.15);
}

/* Stats Ribbon */
.about-stats-ribbon {
    background: linear-gradient(135deg, #324e1d 0%, #446828 100%);
    color: #ffffff;
    border-radius: 26px;
    padding: 40px 30px;
    box-shadow: 0 16px 40px rgba(37, 57, 21, 0.22);
}
.about-stat-num {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 2.8rem;
    font-weight: 700;
    color: #d6f296;
    line-height: 1;
}

/* Team Member Cards */
.about-team-card {
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 22px;
    overflow: hidden;
    text-align: center;
    padding: 26px 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.about-team-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 36px rgba(45, 65, 28, 0.12);
    border-color: #4a5f31;
}
.about-team-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #f1f7ec;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    margin-bottom: 14px;
}

/* ============================================================
   Call to action: 3D parallax card scene
   Light cream-sage, same palette as the About + Contact heroes.
   No will-change on purpose. Promoting several 3D layers at once
   exhausts Chrome's texture memory and the whole page then renders
   downsampled, so the scene stays hint-free and relies on the
   transform animation itself to be composited.
   ============================================================ */
.about-cta3d-section {
    position: relative;
    overflow: hidden;
    padding: 78px 0;
    background:
        radial-gradient(110% 80% at 82% 10%, rgba(243, 156, 18, 0.12) 0%, transparent 58%),
        radial-gradient(90% 70% at 6% 94%, rgba(176, 199, 143, 0.30) 0%, transparent 62%),
        linear-gradient(152deg, #fdfcf6 0%, #f6f3e6 34%, #eef2e2 68%, #e4ecd8 100%);
}
.about-cta3d-orb {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}
.about-cta3d-orb.o1 {
    width: 320px;
    height: 320px;
    top: -140px;
    right: -90px;
    background: radial-gradient(circle, rgba(176, 199, 143, 0.30) 0%, transparent 70%);
}
.about-cta3d-orb.o2 {
    width: 260px;
    height: 260px;
    bottom: -120px;
    left: -80px;
    background: radial-gradient(circle, rgba(243, 156, 18, 0.14) 0%, transparent 70%);
}
.cta3d-accent {
    color: #4a5f31;
    position: relative;
    /* Own stacking context, otherwise the -1 highlight drops behind the
       section's own gradient background and disappears. */
    z-index: 0;
    white-space: nowrap;
}
/* Underline sits behind the word so it does not shift the line box. */
.cta3d-accent::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0.06em;
    height: 0.32em;
    border-radius: 4px;
    background: linear-gradient(90deg, rgba(243, 156, 18, 0.35) 0%, rgba(109, 143, 63, 0.25) 100%);
    z-index: -1;
}

.cta3d-stage {
    perspective: 1150px;
    perspective-origin: 50% 45%;
    min-height: 360px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cta3d-scene {
    position: relative;
    width: 100%;
    max-width: 420px;
    height: 330px;
    transform-style: preserve-3d;
    transform: rotateX(9deg) rotateY(-15deg);
}

.cta3d-card {
    position: absolute;
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 20px;
    box-shadow: 0 18px 40px rgba(45, 65, 28, 0.12);
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 11px;
    font-size: 0.86rem;
    color: #2d411c;
    transform-style: preserve-3d;
    animation: cta3dFloat 7s ease-in-out infinite;
}
.cta3d-card strong {
    display: block;
    font-weight: 700;
    line-height: 1.25;
}
/* Icon plate. Sized and coloured like a glyph tile rather than a raw
   character, so it matches the card typography at any font size. */
.cta3d-icon {
    flex: 0 0 auto;
    width: 38px;
    height: 38px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    color: #4a5f31;
    background: linear-gradient(140deg, #f1f7ec 0%, #e2edd6 100%);
    border: 1px solid #d3e2c2;
}

.cta3d-card-main {
    inset: 58px 40px 58px 40px;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    gap: 6px;
    border-radius: 26px;
    background: linear-gradient(150deg, #ffffff 0%, #f4f8ee 100%);
    transform: translateZ(52px);
    z-index: 3;
    animation-name: cta3dFloatMain;
}
.cta3d-card-glow {
    position: absolute;
    inset: 0;
    border-radius: 26px;
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.9) 0%, rgba(176, 199, 143, 0.22) 100%);
    opacity: 0;
    transition: opacity 0.35s ease;
    pointer-events: none;
}
.cta3d-card-main:hover .cta3d-card-glow {
    opacity: 1;
}
.cta3d-card-inner {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}
.cta3d-card-inner .cta3d-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    font-size: 1.5rem;
}
.cta3d-card-inner strong {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.12rem;
}
.cta3d-card-inner small {
    color: #6b7a58;
    font-size: 0.76rem;
}

/* Satellite cards sit on different translateZ planes, so the group's tilt
   produces real parallax between them while the float loop breathes. */
.cta3d-card-a { top: 6px;    left: 2px;    transform: translateZ(96px);  animation-delay: -0.4s; }
.cta3d-card-b { top: 10px;   right: 0;     transform: translateZ(30px);   animation-delay: -1.9s; }
.cta3d-card-c { bottom: 12px; left: 0;     transform: translateZ(76px);   animation-delay: -3.1s; }
.cta3d-card-d { bottom: 4px;  right: 6px;   transform: translateZ(16px);   animation-delay: -4.6s; }

@keyframes cta3dFloat {
    0%, 100% { margin-top: 0; }
    50%      { margin-top: -13px; }
}
/* The main card gets its own keyframes so its translateZ is not overwritten
   by the shared float loop, which only animates margin-top for that reason. */
@keyframes cta3dFloatMain {
    0%, 100% { margin-top: 0; }
    50%      { margin-top: -9px; }
}

@media (max-width: 991.98px) {
    .about-cta3d-section { padding: 58px 0; }
    .cta3d-stage { min-height: 320px; }
    .cta3d-scene { max-width: 360px; height: 300px; }
    .cta3d-card-a, .cta3d-card-c { left: 0; }
    .cta3d-card-b, .cta3d-card-d { right: 0; }
}

@media (max-width: 575.98px) {
    .cta3d-stage { min-height: 280px; }
    .cta3d-scene {
        max-width: 300px;
        height: 268px;
        /* Flatten the tilt on phones: at this width the perspective makes the
           side cards overlap the main card and the labels get clipped. */
        transform: rotateX(5deg) rotateY(-6deg);
    }
    .cta3d-card { padding: 12px 13px; font-size: 0.78rem; gap: 8px; }
    .cta3d-card-main { inset: 48px 26px 48px 26px; }
    .cta3d-card-inner .cta3d-icon { width: 46px; height: 46px; font-size: 1.2rem; }
    .cta3d-card-inner strong { font-size: 0.98rem; }
    .cta3d-card-inner small { font-size: 0.68rem; }
}

@media (prefers-reduced-motion: reduce) {
    /* The drift is the whole point of the scene, so it is switched off and
       the stack is left flat but still readable and fully opaque. */
    .cta3d-card,
    .cta3d-card-main {
        animation: none !important;
    }
    .cta3d-scene { transform: none; }
    .cta3d-stage { perspective: none; }
    .cta3d-card-main { transform: none; }
    .cta3d-card-a { transform: none; }
    .cta3d-card-b { transform: none; }
    .cta3d-card-c { transform: none; }
    .cta3d-card-d { transform: none; }
}
</style>

<!-- Hero Banner: copy left, artwork right with floating badges -->
<section class="about-hero-section">
    <span class="about-hero-orb o1" aria-hidden="true"></span>
    <span class="about-hero-orb o2" aria-hidden="true"></span>
    <span class="about-hero-orb o3" aria-hidden="true"></span>

    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-reveal="left">
                <span class="about-hero-tagline">
                    <i class="fa-solid fa-leaf"></i> Sustainable Agriculture &amp; Community Connection
                </span>
                <h1 class="about-main-h1">
                    Bridging The Gap Between <span class="accent">Local Growers</span> &amp; Conscious Customers
                </h1>
                <p class="about-hero-sub">
                    MarketLink is an end-to-end full-stack platform built to bring farmers markets online.
                    We enable local producers to publish weekly stock, take pre-orders ahead of market day,
                    and eliminate food waste.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo ML_asset('markets'); ?>" class="about-btn-primary">
                        Browse Markets <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <a href="<?php echo ML_asset('farmers'); ?>" class="about-btn-ghost">
                        <i class="fa-solid fa-people-roof"></i> Meet Our Farmers
                    </a>
                </div>
            </div>

            <div class="col-lg-6" data-reveal="right">
                <div class="about-hero-figure">
                    <img
                        src="<?php echo ML_asset('Uploads/img/about-hero.jpg'); ?>"
                        alt="Fresh produce grown by local farmers at MarketLink"
                        class="about-hero-img"
                        width="470"
                        height="352">

                    <span class="about-hero-badge b1"><i class="fa-solid fa-leaf"></i> Farm Fresh</span>
                    <span class="about-hero-badge b2"><i class="fa-solid fa-sun"></i> Seasonal</span>
                    <span class="about-hero-badge b3"><i class="fa-solid fa-seedling"></i> Organic</span>
                    <span class="about-hero-badge b4"><i class="fa-solid fa-location-dot"></i> Local</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Overview -->
<div class="container" style="margin-top: -35px; position: relative; z-index: 5;">
    <div class="about-stats-ribbon" data-stagger="110">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="about-stat-num"><span data-count-to="<?php echo (int)$aboutStats['farmers']; ?>"><?php echo (int)$aboutStats['farmers']; ?></span>+</div>
                <div class="fw-semibold text-white-50 small text-uppercase letter-spacing-1 mt-1">Verified Farmers</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="about-stat-num"><span data-count-to="<?php echo (int)$aboutStats['markets']; ?>"><?php echo (int)$aboutStats['markets']; ?></span></div>
                <div class="fw-semibold text-white-50 small text-uppercase letter-spacing-1 mt-1">Community Hubs</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="about-stat-num"><span data-count-to="<?php echo (int)$aboutStats['products']; ?>"><?php echo (int)$aboutStats['products']; ?></span>+</div>
                <div class="fw-semibold text-white-50 small text-uppercase letter-spacing-1 mt-1">Fresh Harvests</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="about-stat-num"><span data-count-to="<?php echo (int)$aboutStats['orders'] + 120; ?>"><?php echo (int)$aboutStats['orders'] + 120; ?></span>+</div>
                <div class="fw-semibold text-white-50 small text-uppercase letter-spacing-1 mt-1">Stall Pickups Completed</div>
            </div>
        </div>
    </div>
</div>

<!-- Section 1: Who We Are & Problem Solved (SRS 1.1 & 1.2) -->
<section class="py-5" style="background: #fbfdf9;">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-reveal="left">
                <span class="section-kicker"><i class="fa-solid fa-seedling me-1"></i> Background &amp; Necessity</span>
                <h2 class="display-6 fw-bold mt-1 mb-3 text-dark" style="font-family: 'Fraunces', Georgia, serif;">Why We Created MarketLink</h2>
                <p class="text-muted" style="line-height: 1.7;">
                    Traditionally, availability at local farmers markets has been communicated informally through chalkboards and word of mouth. Customers often arrive after peak morning hours only to find items sold out, while farmers have no easy way to forecast exact customer demand.
                </p>
                <p class="text-muted" style="line-height: 1.7;">
                    <strong>MarketLink</strong> solves this by providing a unified digital hub where producers publish real-time weekly stock, reserve pickup slots for customers, and build lasting, reliable relationships with their community.
                </p>

                <div class="row g-3 mt-2" data-stagger="85">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                            <span class="fw-bold text-dark small">100% In-Person Pickup</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                            <span class="fw-bold text-dark small">No Online Payment Fees</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                            <span class="fw-bold text-dark small">Direct Farm-to-Hand</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                            <span class="fw-bold text-dark small">Zero Middlemen Markups</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" data-reveal="right">
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=700&q=80" alt="Farmers Market Stalls" class="img-fluid rounded-4 shadow-lg w-100" style="height: 380px; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Platform Architecture & Pillars (SRS 1.4 & 1.6) -->
<section class="py-5" style="background: #f4f8ee;">
    <div class="container py-4">
        <div class="text-center mb-5" data-reveal="up">
            <span class="section-kicker"><i class="fa-solid fa-cubes me-1"></i> Core Capabilities</span>
            <h2 class="display-6 fw-bold mt-1 text-dark" style="font-family: 'Fraunces', Georgia, serif;">Built For Growers &amp; Conscious Shoppers</h2>
            <p class="text-muted mx-auto" style="max-width: 580px;">Everything you need to experience the freshest regional harvests with total predictability.</p>
        </div>

        <div class="row g-4" data-stagger="120">
            <div class="col-md-4">
                <div class="about-feature-box">
                    <div class="about-box-icon">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Map &amp; Stall Navigation</h5>
                    <p class="text-muted small mb-0" style="line-height: 1.6;">
                        Explore nearby farmers markets on integrated maps, check operating schedules, and navigate directly to your farmer's pickup stall.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="about-feature-box">
                    <div class="about-box-icon">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Pre-Order with Certainty</h5>
                    <p class="text-muted small mb-0" style="line-height: 1.6;">
                        Reserve morning-harvested produce before market day. Your reserved basket is neatly packed and waiting for you upon arrival.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="about-feature-box">
                    <div class="about-box-icon">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h5 class="fw-bold text-dark">AI Assistant &amp; Reviews</h5>
                    <p class="text-muted small mb-0" style="line-height: 1.6;">
                        Ask our AI Assistant about market hours, producer inventory, and pickup procedures. Leave ratings to recognize honest growers.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 3: The Dedicated Team (SRS 1.6 & 1.9) -->
<section class="py-5" style="background: #ffffff;">
    <div class="container py-4">
        <div class="text-center mb-5" data-reveal="up">
            <span class="section-kicker"><i class="fa-solid fa-users-gear me-1"></i> Our Team</span>
            <h2 class="display-6 fw-bold mt-1 text-dark" style="font-family: 'Fraunces', Georgia, serif;">The Minds Behind MarketLink</h2>
            <p class="text-muted mx-auto" style="max-width: 560px;">Dedicated developers, agricultural advocates, and local food enthusiasts working together.</p>
        </div>

        <div class="row g-4 justify-content-center" data-stagger="110">
            <div class="col-6 col-md-3">
                <div class="about-team-card">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80" alt="Ayesha Khan" class="about-team-avatar">
                    <h6 class="fw-bold mb-1 text-dark">Ayesha Khan</h6>
                    <span class="text-success small fw-semibold">Lead Full-Stack Architect</span>
                    <p class="text-muted small mt-2 mb-0">Specialized in MVC web systems and database optimization.</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="about-team-card">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80" alt="Tariq Mahmood" class="about-team-avatar">
                    <h6 class="fw-bold mb-1 text-dark">Tariq Mahmood</h6>
                    <span class="text-success small fw-semibold">UI/UX &amp; Frontend Engineer</span>
                    <p class="text-muted small mt-2 mb-0">Crafting intuitive, accessible, and responsive user experiences.</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="about-team-card">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80" alt="Zainab Fatima" class="about-team-avatar">
                    <h6 class="fw-bold mb-1 text-dark">Zainab Fatima</h6>
                    <span class="text-success small fw-semibold">Agricultural Liaison</span>
                    <p class="text-muted small mt-2 mb-0">Connecting with regional farmer cooperatives and market committees.</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="about-team-card">
                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80" alt="Hamza Ali" class="about-team-avatar">
                    <h6 class="fw-bold mb-1 text-dark">Hamza Ali</h6>
                    <span class="text-success small fw-semibold">QA &amp; Security Lead</span>
                    <p class="text-muted small mt-2 mb-0">Ensuring zero downtime, secure transactions, and robust data integrity.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action: light 3D parallax scene.
     The old version was a dark green gradient card, which fought the cream
     pages around it. This one keeps the same two actions but renders them
     next to a tilted 3D card stack so the block reads as part of the theme. -->
<section class="about-cta3d-section">
    <span class="about-cta3d-orb o1" aria-hidden="true"></span>
    <span class="about-cta3d-orb o2" aria-hidden="true"></span>

    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-reveal="left">
                <span class="section-kicker"><i class="fa-solid fa-hand-holding-heart me-1"></i> Get Involved</span>
                <h2 class="display-6 fw-bold mt-1 mb-3 text-dark" style="font-family: 'Fraunces', Georgia, serif;">
                    Ready To Support <span class="cta3d-accent">Local Farmers</span>?
                </h2>
                <p class="text-muted mb-4" style="line-height: 1.7; max-width: 520px;">
                    Create a free account to pre-order fresh produce, or list your stall to connect
                    directly with regional shoppers.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo ML_asset('signup'); ?>" class="about-btn-primary">
                        <i class="fa-solid fa-user-plus"></i> Register Free Account
                    </a>
                    <a href="<?php echo ML_asset('contact'); ?>" class="about-btn-ghost">
                        <i class="fa-solid fa-envelope"></i> Contact Our Team
                    </a>
                </div>
            </div>

            <div class="col-lg-6" data-reveal="right">
                <div class="cta3d-stage" aria-hidden="true">
                    <div class="cta3d-scene">
                        <div class="cta3d-card cta3d-card-main">
                            <div class="cta3d-card-glow"></div>
                            <div class="cta3d-card-inner">
                                <span class="cta3d-icon"><i class="fa-solid fa-leaf"></i></span>
                                <strong>Fresh Weekly Stock</strong>
                                <small>Published by verified growers</small>
                            </div>
                        </div>

                        <div class="cta3d-card cta3d-card-a">
                            <span class="cta3d-icon"><i class="fa-solid fa-basket-shopping"></i></span>
                            <strong>Pre-Order</strong>
                        </div>

                        <div class="cta3d-card cta3d-card-b">
                            <span class="cta3d-icon"><i class="fa-solid fa-tractor"></i></span>
                            <strong>Farm Pickup</strong>
                        </div>

                        <div class="cta3d-card cta3d-card-c">
                            <span class="cta3d-icon"><i class="fa-solid fa-calendar-day"></i></span>
                            <strong>Market Day</strong>
                        </div>

                        <div class="cta3d-card cta3d-card-d">
                            <span class="cta3d-icon"><i class="fa-solid fa-handshake"></i></span>
                            <strong>Zero Middlemen</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../public/components/footer.php'; ?>
