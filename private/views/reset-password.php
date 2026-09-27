<?php
$pageTitle = 'Choose a New Password - MarketLink';

/* This page is what the emailed link opens. There is no code to copy:
   the token in the URL is the authorisation, and the reader sets the
   new password and its confirmation right here. */

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$valid = ml_reset_token_is_valid($pdo, $token);

// Anything below is only reachable with a live token.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    verify_csrf();

    $pass   = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if (mb_strlen($pass) < 6) {
        set_flash('error', 'New password must be at least 6 characters.');
    } elseif ($pass !== $confirm) {
        set_flash('error', 'The two passwords do not match.');
    } elseif (!preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) {
        set_flash('error', 'Use at least one letter and one number.');
    } else {
        $hash = hash('sha256', $token);
        $stmt = $pdo->prepare("UPDATE users
            SET password = ?, reset_token = NULL, reset_token_expiry = NULL
            WHERE reset_token = ?");
        $stmt->execute([password_hash($pass, PASSWORD_DEFAULT), $hash]);

        // The link is single-use, so this also retires it.
        set_flash('success', 'Password updated. You can now log in with your new password.');
        redirect('login');
    }
    // Re-render with the flash message rather than bouncing, so the
    // reader keeps the token and can correct just the fields.
    $valid = ml_reset_token_is_valid($pdo, $token);
}

include __DIR__ . '/../../public/components/header.php';
?>

<section class="ml-hero text-center">
    <div class="container">
        <span class="ml-hero-eyebrow"><i class="bi bi-shield-lock"></i> Account Recovery</span>
        <h1 class="section-title mt-3" style="font-size:clamp(2rem,4.5vw,3rem);">Choose a New Password</h1>
        <p class="mx-auto text-muted" style="max-width:52ch;">
            <?php echo $valid
                ? 'Pick something you have not used before. You will be signed out nowhere &mdash; just log in again with it.'
                : 'This reset link is no longer valid.'; ?>
        </p>
    </div>
</section>

<section class="py-5">
    <div class="container" style="max-width:520px;">
        <div class="c-card">
            <div class="c-card-body p-4">

                <?php if (!$valid): ?>
                    <!-- Expired, already used, or a hand-edited URL. -->
                    <div class="text-center py-2">
                        <i class="bi bi-link-45deg" style="font-size:2.6rem;color:#c0392b;"></i>
                        <h5 class="fw-bold mt-2">This link has expired or already been used</h5>
                        <p class="text-muted small">
                            Reset links work once and last 30 minutes. Request a fresh one and it will
                            arrive in your inbox within a moment.
                        </p>
                        <a href="<?php echo ML_asset('forgot-password'); ?>" class="c-btn primary w-100 mt-2">
                            <i class="bi bi-envelope-check"></i> Send me a new link
                        </a>
                    </div>
                <?php else: ?>
                    <form method="post" action="<?php echo ML_asset('reset-password'); ?>?token=<?php echo urlencode($token); ?>" id="resetForm" novalidate>
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="token" value="<?php echo sanitize_output($token); ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="pw1">New password</label>
                            <div class="position-relative">
                                <input type="password" name="password" id="pw1" class="form-control" required
                                       minlength="6" placeholder="At least 6 characters" autocomplete="new-password">
                                <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-1 pw-toggle" data-target="pw1" aria-label="Show password" tabindex="-1">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <!-- Live strength meter: the rule is stated up front
                                 so a rejected password is never a surprise. -->
                            <div class="pw-meter mt-2" id="pwMeter" aria-live="polite">
                                <div class="pw-meter-bar"><i></i></div>
                                <span class="pw-meter-label">Use at least 6 characters, with a letter and a number</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="pw2">Confirm new password</label>
                            <div class="position-relative">
                                <input type="password" name="password_confirm" id="pw2" class="form-control" required
                                       minlength="6" placeholder="Type it once more" autocomplete="new-password">
                                <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-1 pw-toggle" data-target="pw2" aria-label="Show password" tabindex="-1">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <div class="pw-match mt-2" id="pwMatch" aria-live="polite"></div>
                        </div>

                        <button type="submit" class="c-btn primary w-100" id="resetSubmit">
                            <i class="bi bi-check2-circle"></i> Update Password
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($valid): ?>
                    <hr class="my-4">
                    <p class="c-muted text-center mb-0 small">Changed your mind?
                        <a href="<?php echo ML_asset('login'); ?>" class="fw-semibold text-decoration-none" style="color:var(--ml-green);">Back to login</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Scoped to this page. The strength meter and the match line are the
   only new pieces of UI; both are pure feedback and carry no state of
   their own, so the form still works if this script never runs. */
.pw-toggle {
    border: 0;
    background: transparent;
    color: #6b7763;
    padding: .25rem .5rem;
}
.pw-toggle:hover { color: #40543a; }

.pw-meter-bar {
    height: 5px;
    border-radius: 99px;
    background: #eef1e6;
    overflow: hidden;
}
.pw-meter-bar > i {
    display: block;
    height: 100%;
    width: 0;
    border-radius: 99px;
    background: #c0392b;
    transition: width .3s ease, background .3s ease;
}
.pw-meter-label {
    display: block;
    margin-top: 5px;
    font-size: .74rem;
    color: #7a8a68;
}
.pw-meter.s2 .pw-meter-bar > i { background: #e59819; }
.pw-meter.s3 .pw-meter-bar > i { background: #7fa53f; }
.pw-meter.s4 .pw-meter-bar > i { background: #3f7d2e; }

.pw-match { font-size: .8rem; font-weight: 600; }
.pw-match.ok { color: #3f7d2e; }
.pw-match.no { color: #c0392b; }
</style>

<script>
(function () {
    var form = document.getElementById('resetForm');
    if (!form) return;

    var pw1 = document.getElementById('pw1');
    var pw2 = document.getElementById('pw2');
    var meter = document.getElementById('pwMeter');
    var bar = meter ? meter.querySelector('.pw-meter-bar > i') : null;
    var match = document.getElementById('pwMatch');
    var submit = document.getElementById('resetSubmit');

    // Show / hide each field.
    form.querySelectorAll('.pw-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-target'));
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });

    function score(v) {
        if (!v) return 0;
        var s = 0;
        if (v.length >= 6) s++;
        if (v.length >= 10) s++;
        if (/[A-Za-z]/.test(v) && /\d/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        return s;
    }

    if (pw1 && meter && bar) {
        pw1.addEventListener('input', function () {
            var s = score(pw1.value);
            bar.style.width = (s / 4 * 100) + '%';
            meter.className = 'pw-meter mt-2' + (s >= 2 ? ' s' + Math.min(s, 4) : '');
        });
    }

    // Only judge the match once the reader has actually typed something,
    // otherwise an untouched field is reported as a mismatch.
    if (pw1 && pw2 && match) {
        function check() {
            if (!pw2.value) { match.textContent = ''; match.className = 'pw-match mt-2'; return; }
            var same = pw1.value === pw2.value;
            match.textContent = same ? 'Passwords match' : 'Passwords do not match';
            match.className = 'pw-match mt-2 ' + (same ? 'ok' : 'no');
            // Hold the submit only while they differ; the server re-checks
            // everything regardless.
            if (submit) submit.disabled = !same;
        }
        pw1.addEventListener('input', check);
        pw2.addEventListener('input', check);
    }
})();
</script>

<?php include __DIR__ . '/../../public/components/footer.php'; ?>
