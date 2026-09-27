<?php
$pageTitle = 'Contact Us - MarketLink';
$page = 'contact';
include __DIR__ . '/../../public/components/header.php';
?>

<style>
/* ============================================================
   CONTACT HERO
   Layout taken from the PGL reference (tagline / title /
   subtitle / CTA on the left, a visual panel on the right), but
   built in MarketLink's own cream-sage palette and without the
   reference's illustration, which is not part of this project.
   The right column is a stack of real contact channels instead
   of a decorative image, so it carries information rather than
   just filling space.
   ============================================================ */
.contact-hero-section {
    position: relative;
    overflow: hidden;
    isolation: isolate;
    padding: 76px 0 88px;
    background:
        radial-gradient(115% 85% at 80% 12%, rgba(243, 156, 18, 0.13) 0%, transparent 58%),
        radial-gradient(90% 70% at 8% 92%, rgba(176, 199, 143, 0.30) 0%, transparent 62%),
        linear-gradient(152deg, #fdfcf6 0%, #f6f3e6 34%, #eef2e2 68%, #e4ecd8 100%);
    color: #152A21;
    border-bottom: 2px solid #d4d6bc;
}

/* Ambient orbs, matching the About hero */
.contact-hero-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    z-index: -1;
    pointer-events: none;
}
.contact-hero-orb.o1 { width: 320px; height: 320px; top: -60px;  left: -110px; background: rgba(160, 188, 121, 0.38); }
.contact-hero-orb.o2 { width: 300px; height: 300px; bottom: -70px; right: -120px; background: rgba(243, 156, 18, 0.16); }
.contact-hero-orb.o3 { width: 260px; height: 260px; top: 46%;  left: 42%;     background: rgba(74, 95, 49, 0.12); }

.contact-hero-tagline {
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

.contact-hero-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(2.1rem, 3.6vw, 3.5rem);
    font-weight: 700;
    line-height: 1.12;
    margin: 18px 0 16px;
    color: #2b3d1f;
}
.contact-hero-title .accent {
    color: #6d8f3f;
}

.contact-hero-subtitle {
    font-size: clamp(1rem, 1.15vw, 1.12rem);
    font-weight: 500;
    line-height: 1.7;
    color: #40543a;
    max-width: 540px;
    margin-bottom: 30px;
}

.contact-hero-btn {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 13px 28px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.94rem;
    text-decoration: none;
    background: linear-gradient(135deg, #4a5f31 0%, #6d8f3f 100%);
    color: #fff;
    border: 1.5px solid transparent;
    box-shadow: 0 10px 24px rgba(74, 95, 49, 0.28);
    transition: transform 0.25s ease, box-shadow 0.25s ease, color 0.25s ease;
}
.contact-hero-btn:hover {
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 16px 32px rgba(74, 95, 49, 0.34);
}

/* Right column: quick channel list, standing in for the reference's
   illustration. Each row is a pill that also drifts, so the column
   keeps the same sense of movement the artwork had. */
.contact-hero-panel {
    max-width: 430px;
    margin: 0 auto;
    padding: 22px;
    border-radius: 30px;
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.88) 0%, rgba(237, 247, 228, 0.74) 100%);
    border: 1.5px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 22px 54px rgba(45, 65, 28, 0.14);
}

.contact-hero-row {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fff;
    border: 1.5px solid #dce8cf;
    border-radius: 18px;
    padding: 14px 18px;
    margin-bottom: 12px;
    box-shadow: 0 8px 20px rgba(45, 65, 28, 0.08);
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}
.contact-hero-row:last-child { margin-bottom: 0; }
.contact-hero-row:hover {
    transform: translateX(5px);
    border-color: #6d8f3f;
    box-shadow: 0 12px 26px rgba(45, 65, 28, 0.14);
}

.contact-hero-row-icon {
    flex: 0 0 auto;
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    color: #4a5f31;
    background: linear-gradient(140deg, #eef4e4 0%, #dce8cf 100%);
}
.contact-hero-row-icon.phone { color: #e67e22; background: linear-gradient(140deg, #fdf1e0 0%, #f8e0c2 100%); }
.contact-hero-row-icon.clock { color: #6d8f3f; background: linear-gradient(140deg, #f0f5e8 0%, #dfeac9 100%); }

.contact-hero-row-text { min-width: 0; }
.contact-hero-row-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.7px;
    text-transform: uppercase;
    color: #7a8a68;
}
.contact-hero-row-value {
    display: block;
    font-size: 0.95rem;
    font-weight: 700;
    color: #2b3d1f;
    overflow-wrap: anywhere;
}

@media (max-width: 991.98px) {
    .contact-hero-section { padding: 54px 0 74px; }
    .contact-hero-subtitle { margin-left: auto; margin-right: auto; }
    .contact-hero-panel  { max-width: 480px; margin-top: 34px; }
}
@media (max-width: 575.98px) {
    .contact-hero-section { padding: 42px 0 62px; }
    .contact-hero-panel  { padding: 16px; border-radius: 24px; }
    .contact-hero-row    { padding: 12px 14px; gap: 11px; }
    .contact-hero-row-icon { width: 38px; height: 38px; font-size: 0.95rem; }
    /* The sideways nudge is too strong once the rows go near full width. */
    .contact-hero-row:hover { transform: translateY(-2px); }
}

/* Nothing on this hero moves on its own, but the project already has
   two reduced-motion blocks and this hero is static, so nothing to
   disable is needed here. */

/* Contact Channel Cards */
.contact-card-box {
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 22px;
    padding: 26px 22px;
    height: 100%;
    box-shadow: 0 8px 24px rgba(45, 65, 28, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    text-align: center;
}
.contact-card-box:hover {
    transform: translateY(-6px);
    border-color: #4a5f31;
    box-shadow: 0 16px 36px rgba(45, 65, 28, 0.14);
}
.contact-card-icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: #eef6e6;
    color: #3e5a25;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 16px;
}

/* Contact Form Card */
.contact-form-card {
    background: #ffffff;
    border: 1.5px solid #dce8cf;
    border-radius: 26px;
    padding: 36px 32px;
    box-shadow: 0 12px 35px rgba(45, 65, 28, 0.08);
}

.c-field-wrap {
    margin-bottom: 18px;
}
.c-field-label {
    display: block;
    font-size: 0.84rem;
    font-weight: 700;
    color: #243815;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.c-input-box {
    width: 100%;
    background: #ffffff;
    border: 1.5px solid #a4c489;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 0.92rem;
    color: #17240f;
    outline: none;
    transition: all 0.2s ease;
}
.c-input-box:focus {
    border-color: #3e5a25;
    box-shadow: 0 0 0 3.5px rgba(62, 90, 37, 0.16);
}

/* Map Frame */
.contact-map-frame {
    width: 100%;
    height: 380px;
    border: none;
    border-radius: 22px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

/* FAQ accordion: Bootstrap snaps the panel open instantly, so the height gets
   animated here instead. The icon rotates with it to read as one motion. */
#contactFaqAccordion .accordion-collapse {
    transition: height 0.38s cubic-bezier(0.16, 1, 0.3, 1);
}
#contactFaqAccordion .accordion-button {
    transition: background-color 0.25s ease, color 0.25s ease, box-shadow 0.25s ease;
}
#contactFaqAccordion .accordion-button:not(.collapsed) {
    background: #f4f8ee;
    color: #2d411c;
    box-shadow: none;
}
#contactFaqAccordion .accordion-button:focus {
    box-shadow: 0 0 0 3px rgba(74, 95, 49, 0.18);
    border-color: transparent;
}
#contactFaqAccordion .accordion-button::after {
    transition: transform 0.38s cubic-bezier(0.16, 1, 0.3, 1);
}
#contactFaqAccordion .accordion-item {
    transition: background-color 0.25s ease;
}
#contactFaqAccordion .accordion-item:hover {
    background: #fbfdf9;
}

@media (prefers-reduced-motion: reduce) {
    #contactFaqAccordion .accordion-collapse,
    #contactFaqAccordion .accordion-button,
    #contactFaqAccordion .accordion-button::after {
        transition: none !important;
    }
}
</style>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="contact-hero-section">
    <span class="contact-hero-orb o1" aria-hidden="true"></span>
    <span class="contact-hero-orb o2" aria-hidden="true"></span>
    <span class="contact-hero-orb o3" aria-hidden="true"></span>

    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-reveal="left">
                <span class="contact-hero-tagline">
                    <i class="fa-solid fa-comments"></i> Contact Us
                </span>
                <h1 class="contact-hero-title">
                    Let's Start a <span class="accent">Conversation</span>
                </h1>
                <p class="contact-hero-subtitle">
                    Have a question, need support, or want to learn more about your local market?
                    We're here to help. Reach out to us and we'll get back to you as soon as possible.
                </p>
                <a href="#contactForm" class="contact-hero-btn">
                    Send Message <i class="fa-solid fa-paper-plane"></i>
                </a>
            </div>

            <div class="col-lg-6" data-reveal="right">
                <div class="contact-hero-panel" data-stagger="120">
                    <div class="contact-hero-row">
                        <span class="contact-hero-row-icon"><i class="fa-solid fa-envelope"></i></span>
                        <span class="contact-hero-row-text">
                            <span class="contact-hero-row-label">Email</span>
                            <span class="contact-hero-row-value">support@marketlink.pk</span>
                        </span>
                    </div>

                    <div class="contact-hero-row">
                        <span class="contact-hero-row-icon phone"><i class="fa-solid fa-phone"></i></span>
                        <span class="contact-hero-row-text">
                            <span class="contact-hero-row-label">Call / WhatsApp</span>
                            <span class="contact-hero-row-value">+92 300 000 0000</span>
                        </span>
                    </div>

                    <div class="contact-hero-row">
                        <span class="contact-hero-row-icon clock"><i class="fa-solid fa-clock"></i></span>
                        <span class="contact-hero-row-text">
                            <span class="contact-hero-row-label">Market Days</span>
                            <span class="contact-hero-row-value">7:00 AM &ndash; 2:00 PM</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Direct Channels Grid -->
<div class="py-5" style="background: #f8faf6;">
    <div class="container py-2">
        <div class="row g-4" data-stagger="100">
            
            <!-- Email Support -->
            <div class="col-sm-6 col-lg-3">
                <div class="contact-card-box">
                    <div class="contact-card-icon">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Email Us</h5>
                    <p class="text-muted small mb-2">For general support &amp; inquiries</p>
                    <a href="mailto:support@marketlink.pk" class="text-success fw-bold text-decoration-none small">support@marketlink.pk</a>
                </div>
            </div>

            <!-- Phone / WhatsApp -->
            <div class="col-sm-6 col-lg-3">
                <div class="contact-card-box">
                    <div class="contact-card-icon">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Call / WhatsApp</h5>
                    <p class="text-muted small mb-2">Market hours: 7 AM &ndash; 2 PM</p>
                    <a href="tel:+923000000000" class="text-success fw-bold text-decoration-none small">+92 300 000 0000</a>
                </div>
            </div>

            <!-- Market Hubs -->
            <div class="col-sm-6 col-lg-3">
                <div class="contact-card-box">
                    <div class="contact-card-icon">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Visit a Market</h5>
                    <p class="text-muted small mb-2">Pickup at local market stalls</p>
                    <a href="<?php echo ML_asset('markets'); ?>" class="text-success fw-bold text-decoration-none small">Find Nearest Hub &rarr;</a>
                </div>
            </div>

            <!-- Farmer Stall Inquiries -->
            <div class="col-sm-6 col-lg-3">
                <div class="contact-card-box">
                    <div class="contact-card-icon">
                        <i class="fa-solid fa-tractor"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Become a Farmer</h5>
                    <p class="text-muted small mb-2">List your produce stall free</p>
                    <a href="<?php echo ML_asset('signup'); ?>" class="text-success fw-bold text-decoration-none small">Register Stall &rarr;</a>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Main Contact Form & Location Map Section.
     The id is the target for the hero's "Send Message" button. -->
<section class="py-5" id="contactForm" style="background: #ffffff; scroll-margin-top: 90px;">
    <div class="container py-3">
        <div class="row g-5 align-items-stretch">
            
            <!-- Left: Contact Form -->
            <div class="col-lg-7" data-reveal="left">
                <div class="contact-form-card">
                    <span class="section-kicker"><i class="fa-solid fa-paper-plane me-1"></i> Send a Message</span>
                    <h2 class="fw-bold text-dark mt-1 mb-2" style="font-family: 'Fraunces', Georgia, serif;">How Can We Help You?</h2>
                    <p class="text-muted small mb-4">Please fill out the form below. Our support coordinator will respond promptly.</p>

                    <form method="POST" action="../private/backend-scripting/contact-handler.php">
                        <?php echo csrf_field(); ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="c-field-wrap">
                                    <label class="c-field-label">Your Name *</label>
                                    <input type="text" name="name" class="c-input-box" placeholder="e.g. Ayesha Khan" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="c-field-wrap">
                                    <label class="c-field-label">Email Address *</label>
                                    <input type="email" name="email" class="c-input-box" placeholder="e.g. ayesha@example.com" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="c-field-wrap">
                                    <label class="c-field-label">Contact Number (Optional)</label>
                                    <input type="tel" name="phone" class="c-input-box" placeholder="e.g. 03209087654">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="c-field-wrap">
                                    <label class="c-field-label">Inquiry Subject *</label>
                                    <select name="subject" class="c-input-box" required>
                                        <option value="" disabled selected>Select a Topic</option>
                                        <option>Order &amp; Stall Pickup Support</option>
                                        <option>Farmer Registration &amp; Stall Approval</option>
                                        <option>Market Schedules &amp; Locations</option>
                                        <option>Produce Quality &amp; Feedback</option>
                                        <option>General Question</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="c-field-wrap">
                                    <label class="c-field-label">Your Message *</label>
                                    <textarea name="message" rows="5" class="c-input-box" placeholder="Please describe how we can assist you..." required></textarea>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-success rounded-pill px-5 py-3 fw-bold text-white shadow" style="background: #3e5a25; border: none;">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Send Message
                                </button>
                                <span class="text-muted small ms-3"><i class="fa-solid fa-shield-halved text-success me-1"></i> Protected by CSRF Verification</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Static Contact Info + Google Maps Location -->
            <div class="col-lg-5" data-reveal="right">
                <div class="card border-0 rounded-4 p-4 shadow-sm h-100" style="background: #f4f8ee; border: 1.5px solid #dce8cf;">
                    <h4 class="fw-bold text-dark mb-3" style="font-family: 'Fraunces', Georgia, serif;">
                        <i class="fa-solid fa-location-dot text-danger me-2"></i> Market Coordination Office
                    </h4>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-start gap-3 mb-2">
                            <i class="fa-solid fa-building text-success mt-1"></i>
                            <div>
                                <strong class="text-dark d-block">MarketLink Headquarters</strong>
                                <span class="text-muted small">Plot 42-B, Commercial Agricultural Hub, Block 6, PECHS, Karachi, Pakistan</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 mb-2">
                            <i class="fa-solid fa-clock text-success"></i>
                            <div>
                                <strong class="text-dark d-block">Operating Hours</strong>
                                <span class="text-muted small">Monday &ndash; Sunday: 7:00 AM &ndash; 2:00 PM (Market Days)</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-hand-holding-dollar text-success"></i>
                            <div>
                                <strong class="text-dark d-block">Payment Policy</strong>
                                <span class="text-muted small">All payments settled in person at market stall pickup</span>
                            </div>
                        </div>
                    </div>

                    <!-- Embedded Map -->
                    <div class="mt-auto">
                        <span class="fw-bold text-dark small d-block mb-2"><i class="fa-solid fa-map me-1 text-success"></i> Live Office &amp; Market Location Map</span>
                        <iframe 
                            class="contact-map-frame"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d115822.45731388658!2d67.01438902891157!3d24.8607343!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3eb33e06651ee9eb%3A0x9482d8c363ea534!2sKarachi%2C%20Pakistan!5e0!3m2!1sen!2s!4v1700000000000!5m2!1sen!2s" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- FAQ Accordion -->
<section class="py-5" style="background: #f8faf6;">
    <div class="container py-2" style="max-width: 800px;">
        <div class="text-center mb-4" data-reveal="up">
            <span class="section-kicker"><i class="fa-solid fa-circle-question me-1"></i> Frequently Asked Questions</span>
            <h3 class="fw-bold text-dark mt-1" style="font-family: 'Fraunces', Georgia, serif;">Quick Answers For Customers &amp; Farmers</h3>
        </div>

        <div class="accordion border-0 shadow-sm rounded-4 overflow-hidden" id="contactFaqAccordion" data-stagger="95">
            <div class="accordion-item border-0 border-bottom">
                <h2 class="accordion-header">
                    <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        How does pre-order and pickup work?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#contactFaqAccordion">
                    <div class="accordion-body text-muted small">
                        Browse verified farmers markets and stalls, add items to your cart, and choose an available pickup window. The farmer packs your fresh produce, and you collect and pay cash directly at their stall on market day.
                    </div>
                </div>
            </div>

            <div class="accordion-item border-0 border-bottom">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Are there any online payment processing or delivery fees?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                    <div class="accordion-body text-muted small">
                        No. Per our SRS design, MarketLink does not process online payments or courier deliveries. Everything is settled directly with the farmer at pickup, ensuring maximum freshness and zero extra charges.
                    </div>
                </div>
            </div>

            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        How can I register my farm and list products?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                    <div class="accordion-body text-muted small">
                        Click on "Create Account", select the "Farmer" role, enter your stall details and address. Once approved by our administration, you can start listing weekly stock and accepting pre-orders immediately.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../public/components/footer.php'; ?>