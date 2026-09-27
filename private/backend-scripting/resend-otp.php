<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";
$email = $_SESSION['email'] ?? null;

if (!$email || empty($_SESSION['otp_expire'])) {
    set_flash("error", "Session expired. Please register again.");
    redirect("signup");
}
?>
<?php
$otp = random_int(100000, 999999);
$expire = time() + 60;
$stmt = $pdo->prepare("UPDATE users SET otp_code = :otp, otp_expiry = :exp WHERE email = :email AND is_verified = 0");
$stmt->execute(['otp' => $otp, 'exp' => $expire, 'email' => $email]);

if ($stmt->rowCount() > 0 && sentOtp($email, $otp)) {
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expire'] = $expire;
    set_flash("success", "A new OTP has been sent to your email.");
} else {
    set_flash("error", "Could not resend OTP. Please register again.");
    unset($_SESSION['otp'], $_SESSION['otp_expire'], $_SESSION['email']);
    redirect("signup");
}
redirect("verify-otp");