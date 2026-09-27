<?php
$pageTitle = 'Forgot Password - MarketLink';

/* One step only. The reader types their email, we mail them a link, and
   that link opens reset-password.php where the new password and its
   confirmation are entered. Nothing has to be copied out of the email
   by hand, and nothing is remembered in the session, so the flow also
   works on a different device from the one the request was made on. */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Please enter a valid email address.');
        redirect('forgot-password');
    }

    $user = selectData($pdo, "SELECT id, email FROM users WHERE email = ? AND is_verified = 1 AND status = 'active'", [$email]);

    if (!empty($user)) {
        $raw = ml_issue_reset_token($pdo, $user[0]['id']);
        ml_send_reset_link($email, $raw);
    }

    // The same message either way. Saying "no account for that address"
    // would let anyone confirm which emails are registered.
    set_flash('success', 'If an account exists for that email, a reset link is on its way. It is valid for 30 minutes.');
    redirect('forgot-password');
}

include __DIR__ . '/../../public/components/header.php';
?>

<section class="ml-hero text-center">
    <div class="container">
        <span class="ml-hero-eyebrow"><i class="bi bi-shield-lock"></i> Account Recovery</span>
        <h1 class="section-title mt-3" style="font-size:clamp(2rem,4.5vw,3rem);">Forgot Password</h1>
        <p class="mx-auto text-muted" style="max-width:52ch;">
            Enter your email and we will send you a link. Open it and you can type your new
            password straight there &mdash; no code to copy.
        </p>
    </div>
</section>

<section class="py-5">
    <div class="container" style="max-width:520px;">
        <div class="c-card">
            <div class="c-card-body p-4">
                <form method="post" action="<?php echo ML_asset('forgot-password'); ?>">
                    <?php echo csrf_field(); ?>
                    <label class="form-label fw-semibold" for="fpEmail">Email address</label>
                    <input type="email" name="email" id="fpEmail" class="form-control mb-3" required
                           placeholder="you@example.com" autocomplete="email" autofocus>
                    <button type="submit" class="c-btn primary w-100">
                        <i class="bi bi-envelope-check"></i> Email me a reset link
                    </button>
                </form>

                <div class="d-flex align-items-start gap-2 mt-3 p-3 rounded-3" style="background:#f4f6ec;">
                    <i class="fa-solid fa-circle-info mt-1" style="color:#4a5f31;"></i>
                    <p class="c-muted mb-0" style="font-size:.82rem;line-height:1.6;">
                        The link opens a page with the new-password and confirm-password boxes,
                        and it works once for 30 minutes.
                    </p>
                </div>

                <hr class="my-4">
                <p class="c-muted text-center mb-0">Remembered it? <a href="<?php echo ML_asset('login'); ?>" class="fw-semibold text-decoration-none" style="color:var(--ml-green);">Back to login</a></p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../public/components/footer.php'; ?>
