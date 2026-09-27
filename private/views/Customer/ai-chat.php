<?php
$pageTitle = 'Smart Assist - MarketLink';
include __DIR__ . '/../../../public/components/header.php';

$faqs = [
    ['How does pre-ordering work?', 'Add fresh produce to your cart, go to checkout and pick a market day and time slot. The farmer packs your order and it is waiting for you at the stall. You pay in person at pickup.'],
    ['When can I pick up my order?', 'Pickup runs 7:00 AM to 2:00 PM on the market operating days shown on each market page. You choose the exact date and time window during checkout.'],
    ['Is online payment required?', 'No. MarketLink never takes online payment for produce. You settle the bill with the farmer when you collect your basket.'],
    ['Can I change or cancel an order?', 'Yes — until the cutoff, which is 24 hours before pickup. From My Orders open the order and use Modify or Cancel. Cancellation is free.'],
    ['What if a product is sold out?', 'Sold out items are marked clearly and removed from buying. You can add similar items from the same farmer or save it to favorites and pre-order next week.'],
    ['How do ratings work?', 'After pickup and completion of an order you can rate each product and the stall. Ratings are averaged and shown on product and farmer pages.'],
    ['How do I reset my password?', 'On the login screen tap "Forgot password", enter your email, and use the code we email you to set a new password.'],
    ['How do I join as a farmer?', 'Register with the Farmer option, complete your stall profile, and our team approves it before your first market day.'],
];
?>

<section class="ml-hero text-center">
    <div class="container">
        <span class="ml-hero-eyebrow"><i class="bi bi-robot"></i> MarketLink Assistant</span>
        <h1 class="section-title mt-3" style="font-size:clamp(2rem,4.5vw,3rem);">Smart Assist</h1>
        <p class="mx-auto text-muted" style="max-width:56ch;">Quick answers to the most common questions. For live help, open the chat bubble at the bottom-right of any page.</p>
    </div>
</section>

<section class="py-5">
    <div class="container" style="max-width:860px;">
        <div class="c-card">
            <div class="c-card-body text-center">
                <span class="c-stat-icon green mx-auto mb-3" style="width:58px;height:58px;font-size:1.5rem;"><i class="bi bi-chat-dots-fill"></i></span>
                <h4 class="fw-bold mb-1">Ask the Assistant</h4>
                <p class="c-muted mb-3">Try it right now — the chat bubble replies instantly, 24/7.</p>
                <button class="c-btn primary" id="mlChatToggleGoto"><i class="bi bi-chat-dots"></i> Open Assistant Chat</button>
            </div>
        </div>

        <div class="c-card mt-3">
            <div class="c-card-body">
                <div class="accordion" id="faqAccordion">
                    <?php foreach ($faqs as $i => $faq): ?>
                        <div class="accordion-item border-0 mb-2" style="border-radius:12px;background:var(--c-green-softer);overflow:hidden;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?php echo $i; ?>">
                                    <?php echo $faq[0]; ?>
                                </button>
                            </h2>
                            <div id="faq<?php echo $i; ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body c-muted" style="background:#fff;"><?php echo $faq[1]; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="c-muted mb-2">Still stuck? A human will help.</p>
            <a href="<?php echo ML_asset('contact'); ?>" class="c-btn outline">Contact Support</a>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var goto = document.getElementById('mlChatToggleGoto');
    if (goto) goto.addEventListener('click', function () {
        var t = document.getElementById('mlChatToggle');
        if (t) t.click();
    });
});
</script>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>