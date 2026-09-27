<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$email = $_SESSION['email'] ?? null;

$otp = $_SESSION['otp'] ?? null;
if (empty($otp)  || empty($_SESSION['otp_expire'])) {
$_SESSION['signupactive'] = true;
    set_flash("error", "no request send for otp");

    redirect("signup");
}
$check_stmt = $pdo->prepare("SELECT is_verified FROM users WHERE email = :email");
$check_stmt->execute(['email' => $email]);
$db_user = $check_stmt->fetch(PDO::FETCH_ASSOC);

if ($db_user && $db_user['is_verified'] == 1) {
    set_flash("success", "You Are Already Verified Please Login! Or try With Different email");

    unset($_SESSION['otp']);
    unset($_SESSION['email']);
    unset($_SESSION['otp_expire']);
$_SESSION['signupactive'] = false;
    redirect("signup");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $email) {
    verify_csrf();
    $inputotp = $_POST['otp'];

    if (time() > $_SESSION['otp_expire']) {

        set_flash('error', 'Otp Expired Resend it');
        redirect("verify-otp");
    }

    $stmt = $pdo->prepare("SELECT otp_code FROM users WHERE email = :email AND is_verified = :is_verified");

    $stmt->execute([

        'email' => $email,

        'is_verified' => 0

    ]);



    $user = $stmt->fetch(PDO::FETCH_ASSOC);



    if ($user) {



        if ($user['otp_code'] == $inputotp) {



            $stmt = $pdo->prepare("UPDATE users SET is_verified = :is_verified WHERE email = :email");

            $stmt->execute([

                'email' => $email,

                'is_verified' => 1

            ]);



            set_flash("success", "Verification Successful. Welcome!");

            $verified = selectData($pdo, "SELECT id, username, email, role FROM users WHERE email = ?", [$email]);
            if (!empty($verified)) {
                session_regenerate_id(true);
                $_SESSION['loggedIn'] = true;
                $_SESSION['role'] = $verified[0]['role'];
                $_SESSION['user_id'] = $verified[0]['id'];
                $_SESSION['username'] = $verified[0]['username'];
                $_SESSION['email'] = $verified[0]['email'];
            }
            $_SESSION['signupactive'] = false;
            unset($_SESSION['otp_expire']);
            unset($_SESSION['otp']);
            unset($_SESSION['email']);

            if (!empty($verified) && $verified[0]['role'] == 'customer') {
                redirect("customer-dashboard");
            } elseif (!empty($verified) && $verified[0]['role'] == 'farmer') {
                redirect("add-profile");
            }
            redirect("signup");
        } else {

            set_flash('error', 'Incorrect OTP code');

            redirect("verify-otp");
        }
    } else {

        set_flash('success', 'You are already verified or account does not exist');
$_SESSION['signupactive'] = false;
        unset($_SESSION['otp_expire']);
        unset($_SESSION['otp']);

        unset($_SESSION['email']);

        redirect("signup");
    }
}
?>
<?php $pageTitle = 'Verify Email - MarketLink'; ?>
<?php include __DIR__ . '/../../public/components/header.php'; ?>

<div class="ml-auth-wrap">
    <div class="ml-auth-card p-4 p-md-5" style="max-width:480px;">
        <?php echo get_flash(); ?>
        <div class="text-center mb-4">
            <span class="ml-brand-icon mb-3"><i class="bi bi-shield-lock-fill"></i></span>
            <h2 class="ml-auth-title">Verify Your Email</h2>
            <p class="text-muted small mb-0">We sent a 6-digit code to
                <strong><?php echo sanitize_output($email); ?></strong>. Enter it to activate your account.</p>
        </div>

        <form action="" method="post" class="d-grid gap-3">
            <?php echo csrf_field(); ?>
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input type="number" name="otp" maxlength="6" placeholder="Enter 6-digit OTP" required autofocus>
            </div>
            <button type="submit" name="verification" class="btn ml-btn-solid w-100">Verify Account</button>
        </form>

        <p id="timer-text" class="text-center small text-muted mt-3 mb-0">Resend OTP in <span id="timer" class="fw-semibold"></span></p>
        <div id="resend-box" style="display:none;" class="text-center mt-2">
            <a href="/techwiz7/private/backend-scripting/resend-otp.php" class="fw-semibold text-decoration-none" style="color:var(--ml-green);">Resend OTP</a>
        </div>
    </div>
</div>

<script>
    let expiryTime = <?php echo isset($_SESSION['otp_expire']) ? $_SESSION['otp_expire'] * 1000 : 0; ?>;
    let timerRef = document.getElementById("timer");
    let timerInterval = setInterval(function () {
        let now = new Date().getTime();
        let distance = expiryTime - now;
        let seconds = Math.floor(distance / 1000);
        if (seconds <= 0) {
            clearInterval(timerInterval);
            document.getElementById("timer-text").style.display = "none";
            document.getElementById("resend-box").style.display = "block";
        } else if (timerRef) {
            timerRef.innerHTML = seconds + " seconds";
        }
    }, 1000);
</script>

<?php include __DIR__ . '/../../public/components/footer.php'; ?>