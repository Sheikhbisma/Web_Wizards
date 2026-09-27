</div>
<footer class="ml-footer-redesign">
  <div class="ml-footer-reveal-layer" aria-hidden="true">
    <div class="ml-footer-reveal-bg"></div>
    <div class="ml-footer-reveal-overlay"></div>
  </div>
  <div class="ml-footer-top-wave" aria-hidden="true">
    <svg viewBox="0 0 1440 60" fill="none" preserveAspectRatio="none">
      <path d="M0,0 C360,45 1080,45 1440,0 L1440,60 L0,60 Z" fill="currentColor"></path>
    </svg>
  </div>
  
  <div class="container py-5">
    <div class="row g-4 g-lg-5">
      <!-- Brand & Mission Column -->
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="ml-brand-icon ml-brand-logo">
            <img src="<?php echo ML_asset('Uploads/img/logo-nav.png'); ?>"
                 srcset="<?php echo ML_asset('Uploads/img/logo-nav@2x.png'); ?> 2x"
                 alt="MarketLink" width="57" height="38" loading="lazy" decoding="async">
          </span>
        </div>
        <p class="ml-footer-text">
          Direct digital bridge connecting verified local growers and conscious customers. Pre-order honest farm harvests, pick them up fresh at community market stalls.
        </p>
        <div class="ml-footer-badges d-flex flex-wrap gap-2 mt-3">
          <span class="ml-fbadge"><i class="fa-solid fa-certificate text-leaf me-1"></i> 100% Verified Farmers</span>
          <span class="ml-fbadge"><i class="fa-solid fa-sun text-warning me-1"></i> Harvested Same Dawn</span>
        </div>
        <div class="mt-4 d-flex gap-2">
          <a href="<?php echo ML_asset('about'); ?>" class="ml-social-btn" title="About Us"><i class="fa-solid fa-circle-info"></i></a>
          <a href="<?php echo ML_asset('contact'); ?>" class="ml-social-btn" title="Contact Us"><i class="fa-solid fa-envelope"></i></a>
          <a href="<?php echo ML_asset('markets'); ?>" class="ml-social-btn" title="Our Markets"><i class="fa-solid fa-store"></i></a>
          <a href="<?php echo ML_asset('farmers'); ?>" class="ml-social-btn" title="Meet Farmers"><i class="fa-solid fa-users"></i></a>
        </div>
      </div>

      <!-- Quick Navigation -->
      <div class="col-6 col-md-3 col-lg-2 offset-lg-1">
        <h6 class="ml-footer-heading">Quick Links</h6>
        <ul class="ml-footer-links-list list-unstyled">
          <li><a href="<?php echo ML_asset(''); ?>" class="ml-footer-link-item"><i class="fa-solid fa-house me-2 opacity-75 small"></i>Home</a></li>
          <li><a href="<?php echo ML_asset('markets'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-store me-2 opacity-75 small"></i>Community Markets</a></li>
          <li><a href="<?php echo ML_asset('farmers'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-people-roof me-2 opacity-75 small"></i>Verified Farmers</a></li>
          <li><a href="<?php echo ML_asset('products'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-leaf me-2 opacity-75 small"></i>Fresh Products</a></li>
          <li><a href="<?php echo ML_asset('about'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-circle-info me-2 opacity-75 small"></i>Our Mission</a></li>
          <li><a href="<?php echo ML_asset('contact'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-envelope me-2 opacity-75 small"></i>Help &amp; Support</a></li>
          <li><a href="<?php echo ML_asset('ai-chat'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-robot me-2 opacity-75 small"></i>Smart Assist</a></li>
          <li><a href="<?php echo ML_asset('sitemap'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-sitemap me-2 opacity-75 small"></i>Sitemap</a></li>
        </ul>
      </div>

      <!-- Customer Account & Services -->
      <div class="col-6 col-md-3 col-lg-2">
        <h6 class="ml-footer-heading">Account &amp; Orders</h6>
        <ul class="ml-footer-links-list list-unstyled">
          <?php if (isset($user['role']) && $user['role'] === 'customer'): ?>
            <li><a href="<?php echo ML_asset('customer-dashboard'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-gauge-high me-2 opacity-75 small"></i>Customer Portal</a></li>
            <li><a href="<?php echo ML_asset('orders'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-box-archive me-2 opacity-75 small"></i>Active Orders</a></li>
            <li><a href="<?php echo ML_asset('favorites'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-heart me-2 opacity-75 small"></i>Saved Stalls</a></li>
            <li><a href="<?php echo ML_asset('profile'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-user-gear me-2 opacity-75 small"></i>My Profile</a></li>
          <?php else: ?>
            <li><a href="<?php echo ML_asset('login'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-right-to-bracket me-2 opacity-75 small"></i>Customer Login</a></li>
            <li><a href="<?php echo ML_asset('signup'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-user-plus me-2 opacity-75 small"></i>Register Account</a></li>
            <li><a href="<?php echo ML_asset('cart'); ?>" class="ml-footer-link-item"><i class="fa-solid fa-basket-shopping me-2 opacity-75 small"></i>View Basket</a></li>
            <li><a href="<?php echo ML_asset('signup'); ?>?role=farmer" class="ml-footer-link-item text-leaf fw-semibold"><i class="fa-solid fa-tractor me-2 text-leaf small"></i>Join As Farmer</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Schedule & Operating Card -->
      <div class="col-md-6 col-lg-3">
        <div class="ml-footer-hours-box">
          <h6 class="ml-footer-heading mb-3"><i class="fa-solid fa-clock text-leaf me-2"></i>Market Schedule</h6>
          <div class="d-flex align-items-center gap-2 mb-2 text-white-50 small">
            <i class="fa-solid fa-calendar-days text-leaf"></i>
            <span>Scheduled Market Days</span>
          </div>
          <div class="text-white fw-bold mb-3 fs-6">7:00 AM — 2:00 PM</div>
          <div class="ml-hours-note">
            <i class="fa-solid fa-shield-halved text-leaf me-1"></i> Pre-order online, collect &amp; pay directly at the farmer stall.
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="ml-footer-bottom py-3">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
      <span class="text-white-50 small">© <?php echo date('Y'); ?> MarketLink - eGreen Basket. All Rights Reserved.</span>
      <span class="text-white-50 small d-flex align-items-center gap-2">
        <span>Hand-crafted for Local Agriculture</span>
        <span>•</span>
        <span class="text-leaf">Zero Food Waste</span>
      </span>
    </div>
  </div>
</footer>

<!-- MarketLink Assistant Chat Widget -->
<div class="ml-chat-widget" id="mlChatWidget">
    <div class="ml-chat-panel" id="mlChatPanel">
        <div class="ml-chat-head d-flex align-items-center gap-2 justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-robot fs-5"></i>
                <div>
                    <div class="fw-bold" style="font-size:.9rem;">MarketLink Assistant</div>
                    <div class="small opacity-75">Online &middot; replies instantly</div>
                </div>
            </div>
            <i class="bi bi-x-lg" id="mlChatClose" style="cursor:pointer;" role="button" aria-label="Close chat"></i>
        </div>
        <div class="ml-chat-body" id="mlChatBody">
            <div class="ml-msg ml-msg-bot">Salam! I can help with markets, farmers, products, orders, pickup times and payment. What would you like to know?</div>
        </div>
        <form class="ml-chat-input" id="mlChatForm">
            <input type="text" class="form-control" id="mlChatText" placeholder="Ask about markets, orders..." autocomplete="off" aria-label="Chat message">
            <button type="submit" class="btn" style="background:linear-gradient(135deg,var(--ml-green),var(--ml-leaf));color:#fff;" aria-label="Send"><i class="bi bi-send-fill"></i></button>
        </form>
    </div>
    <button type="button" class="ml-chat-toggle" id="mlChatToggle" aria-label="Open chat"><i class="bi bi-chat-dots-fill"></i></button>
</div>

<!-- Sticky Header Script -->
<script>
(function () {
    // Sticky header sync
    var nav = document.querySelector('.ml-navbar');
    if (nav) {
        var stuck = false;
        var sync = function () {
            var next = window.scrollY > 12;
            if (next !== stuck) {
                stuck = next;
                nav.classList.toggle('is-stuck', stuck);
            }
        };
        window.addEventListener('scroll', sync, { passive: true });
        sync();
    }
    // The navbar and footer cursor flashlight both used to be duplicated
    // here without any lerp, driving --nav-x/--nav-y which the stylesheet
    // never read. They now share one engine in customer.js.
})();
</script>
<script src="<?php echo ML_asset('js/main.js'); ?>"></script>
<script src="<?php echo ML_asset('js/customer.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script>
// GSAP scroll reveals - skipped automatically when the user prefers reduced motion
// or when the CDN is unreachable, so the site never depends on it.
(function () {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce || !window.gsap || !window.ScrollTrigger) return;
    gsap.registerPlugin(ScrollTrigger);

    gsap.utils.toArray('.ml-hero-eyebrow, .ml-hero .section-title, .ml-hero p').forEach(function (el, i) {
        gsap.from(el, { duration: .8, y: 26, autoAlpha: 0, ease: 'power2.out', delay: i * .12 });
    });

    gsap.utils.toArray('.ml-card, .c-card, .ml-product-card').forEach(function (card) {
        gsap.from(card, {
            scrollTrigger: { trigger: card, start: 'top 88%' },
            duration: .6,
            y: 28,
            autoAlpha: 0,
            ease: 'power2.out'
        });
    });
})();
</script>
</body>
</html>
