<?php
include __DIR__ . '/../emailCredentials/credentials.php';


function sanitize($conn, $input)
{
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($input)));
}

function sanitize_output($input)
{
    return htmlspecialchars($input ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf()
{
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        set_flash("error", "Invalid or Expired CSRF Token. Please try again.");
        $_SESSION['old_input'] = $_POST; 
        
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
function redirect($page)
{
    // Ab yeh direct clean URL par bhej dega
    header("Location: /techwiz7/public/$page");
    exit();
}
function set_flash($type, $message)
{
   return $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        
        $class = ($flash['type'] == 'success') ? 'alert-success' : 'alert-danger';
        
        return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                    ' . $flash['message'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }
    return '';
}
function insertData($pdo, $table, $values)
{
    $columns = implode(", ", array_keys($values));

    $placeholders = ":" . implode(", :", array_keys($values));

    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        return true;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

function selectData($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
    }
}

use   PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../backend-scripting/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../backend-scripting/PHPMailer/src/SMTP.php';
require __DIR__ . '/../backend-scripting/PHPMailer/src/Exception.php';

function sentOtp($email, $otp)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_ADDRESS;
        $mail->Password   = PASS_KEY;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // RECEIVER & SENDER
        $mail->setFrom(EMAIL_ADDRESS, 'Techwiz');
        $mail->addAddress($email);

        // EMAIL CONTENT
        $mail->isHTML(true);
        $mail->Subject = "OTP For Verification";
        $mail->Body    = "<h3>Your Verification Code is: <b>{$otp}</b></h3>";

        // Agar yahan error aaya toh seedha catch block mein chala jayega
        $mail->send();
        set_flash("success", "An Email Has Been Sent");
        return true;

    } catch (Exception $e) {
        // Yahan error capture ho raha hai aur session mein save ho raha hai
        set_flash("error", "Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}


function sendEmail($to, $subject, $html)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_ADDRESS;
        $mail->Password   = PASS_KEY;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom(EMAIL_ADDRESS, 'MarketLink');
        $mail->addAddress($to);
        $mail->addReplyTo(EMAIL_ADDRESS, 'MarketLink');
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>', '</tr>'], ["\n", "\n", "\n"], $html));
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error ({$subject}): " . $mail->ErrorInfo);
        return false;
    }
}

/* ============================================================
   PASSWORD RESET
   The reset token is a 64-character random string. Only its SHA-256
   hash is stored, so a leaked database dump cannot be replayed as a
   working reset link, and the raw token exists nowhere on the server
   after the mail is sent.

   The link opens a page that asks for the new password and its
   confirmation, so the reader chooses the password on the spot instead
   of typing a code back into a form.
   ============================================================ */

/** Absolute URL to a page on this install, for use inside an email. */
function ml_base_url()
{
    // The app is served from a subfolder, so the scheme+host have to be
    // rebuilt from the request. Outside a web request (CLI, cron) there is
    // no host to read, so fall back to the known install path.
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($script === '' || !isset($_SERVER['HTTP_HOST'])) {
        return 'http://localhost/techwiz7/public';
    }

    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $dir    = rtrim(str_replace('\\', '/', dirname($script)), '/');

    return $scheme . '://' . $host . $dir;
}

/** Creates a fresh token, stores its hash, and returns the raw value. */
function ml_issue_reset_token($pdo, $userId)
{
    $raw = bin2hex(random_bytes(32));          // 64 hex chars
    $hash = hash('sha256', $raw);

    // Written in UTC on purpose. The MySQL server and PHP do not share a
    // timezone on this machine (MySQL is three hours ahead), so a local
    // date() written here would be read back by any SQL that compares
    // against NOW() as if it were hours in the past or future. UTC on both
    // sides removes the ambiguity entirely.
    $expiry = gmdate('Y-m-d H:i:s', time() + 1800); // 30 minutes

    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
    $stmt->execute([$hash, $expiry, $userId]);

    return $raw;
}

/** True when the supplied raw token matches a live, unexpired hash. */
function ml_reset_token_is_valid($pdo, $raw)
{
    if (!is_string($raw) || $raw === '') return false;
    $row = selectData($pdo, "SELECT reset_token, reset_token_expiry FROM users WHERE reset_token = ?", [hash('sha256', $raw)]);
    if (empty($row)) return false;
    if (empty($row[0]['reset_token_expiry'])) return false;

    // Parsed as UTC to match how ml_issue_reset_token wrote it.
    return strtotime($row[0]['reset_token_expiry'] . ' UTC') > time();
}

/** Sends the "set your new password" link. */
function ml_send_reset_link($email, $rawToken)
{
    $link = ml_base_url() . '/reset-password?token=' . urlencode($rawToken);
    $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

    $html = '
    <div style="font-family:Poppins,Arial,Helvetica,sans-serif;background:#f4f6ec;padding:28px 12px;">
      <div style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #e2e7d4;">
        <div style="background:#4a5f31;padding:22px 28px;">
          <span style="color:#a0bc79;font-size:13px;letter-spacing:2px;text-transform:uppercase;">MarketLink</span>
          <h1 style="color:#ffffff;font-size:20px;margin:6px 0 0;font-weight:600;">Reset your password</h1>
        </div>
        <div style="padding:28px;">
          <p style="color:#40543a;font-size:15px;line-height:1.65;margin:0 0 18px;">
            Hello, we received a request to reset the password on your MarketLink account.
            Choose a new one using the button below &mdash; you will not need any code.
          </p>
          <a href="' . $safeLink . '" style="display:inline-block;background:#4a5f31;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:13px 30px;border-radius:99px;">
            Set a new password
          </a>
          <p style="color:#7a8a68;font-size:13px;line-height:1.6;margin:22px 0 0;">
            This link works once and expires in <b>30 minutes</b>. If you did not ask for this,
            you can safely ignore this email &mdash; your password will not change.
          </p>
          <p style="color:#9aa691;font-size:11px;line-height:1.6;margin:16px 0 0;word-break:break-all;">
            If the button does not work, paste this into your browser:<br>' . $safeLink . '
          </p>
        </div>
      </div>
    </div>';

    return sendEmail($email, 'Reset your MarketLink password', $html);
}

function notifyOrderStatus($pdo, $orderId, $newStatus, $source = 'farmer')
{
    $actorLabel = $source === 'admin' ? 'the MarketLink team' : 'the farmer';
    $actorShort = $source === 'admin' ? 'by the admin' : 'by the farmer';

    $orderRow = selectData($pdo,
        "SELECT o.*, c.full_name, u.email, u.id AS account_id, m.market_name
         FROM orders o
         JOIN customers c ON o.customer_id = c.customer_id
         JOIN users u ON c.user_id = u.id
         LEFT JOIN markets m ON o.market_id = m.market_id
         WHERE o.order_id = ?", [$orderId]);

    if (empty($orderRow) || !filter_var($orderRow[0]['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        return;
    }

    $orderInfo = $orderRow[0];

    $items = selectData($pdo,
        "SELECT p.name, oi.quantity, oi.unit_price, oi.subtotal
         FROM order_items oi
         LEFT JOIN products p ON oi.product_id = p.product_id
         WHERE oi.order_id = ?", [$orderId]);

    $emailMeta = [
        'accepted'   => ['Your Order Is Confirmed', 'Good news! Your pre-order has been accepted by ' . $actorLabel . '. Please plan your pickup accordingly.'],
        'declined'   => ['Order Declined', 'We are sorry, your pre-order was declined by ' . $actorLabel . '. Please place a new order or contact support for assistance.'],
        'ready'      => ['Order Ready for Pickup', 'Great! Your pre-order is now ready for pickup. Head over to the stall at your selected time.'],
        'completed'  => ['Order Completed', 'Your pre-order has been marked as completed. Thank you for ordering through MarketLink!']
    ];

    $subject = 'MarketLink - ' . $emailMeta[$newStatus][0];
    $headline = $emailMeta[$newStatus][0];
    $bodyText = $emailMeta[$newStatus][1];

    $itemRows = '';
    foreach ($items as $item) {
        $itemRows .= '<tr style="background:#ffffff;">'
            . '<td style="padding:10px 14px;border-bottom:1px solid #eaf1ec;font-size:14px;color:#17271e;">' . htmlspecialchars($item['name'] ?? 'Product') . '</td>'
            . '<td align="center" style="padding:10px 14px;border-bottom:1px solid #eaf1ec;font-size:14px;color:#17271e;">' . (int)$item['quantity'] . '</td>'
            . '<td align="right" style="padding:10px 14px;border-bottom:1px solid #eaf1ec;font-size:14px;font-weight:600;color:#17271e;">Rs ' . number_format((float)$item['subtotal'], 2) . '</td>'
            . '</tr>';
    }

    $statusBadge = [
        'accepted'   => '#17915b',
        'declined'   => '#d8483f',
        'ready'      => '#e8a13c',
        'completed'  => '#17915b'
    ];
    $badgeColor = $statusBadge[$newStatus];

    $pickupInfo = !empty($orderInfo['pickup_date'])
        ? date("d M Y", strtotime($orderInfo['pickup_date'])) . (!empty($orderInfo['pickup_slot']) ? ' at ' . htmlspecialchars($orderInfo['pickup_slot']) : '')
        : 'As per merchant schedule';

    $html = '<div style="margin:0;padding:0;background:#f2f6f3;font-family:Poppins,Segoe UI,Arial,sans-serif;">'
        . '<div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e3ece6;">'
        . '<div style="background:linear-gradient(135deg,#08271d,#0b3528);padding:26px 30px;">'
        . '<div style="display:flex;align-items:center;gap:10px;">'
        . '<span style="width:38px;height:38px;border-radius:12px;background:#22b573;display:inline-block;text-align:center;line-height:38px;color:#ffffff;font-weight:800;font-size:18px;">M</span>'
        . '<span style="color:#ffffff;font-size:18px;font-weight:700;">MarketLink</span>'
        . '</div>'
        . '<p style="color:#8fd6b4;font-size:12px;margin:8px 0 0;letter-spacing:1.5px;text-transform:uppercase;">Order Update</p>'
        . '</div>'
        . '<div style="padding:28px 30px;">'
        . '<div style="display:inline-block;background:' . $badgeColor . '22;color:' . $badgeColor . ';font-size:13px;font-weight:700;padding:7px 14px;border-radius:999px;">' . htmlspecialchars($headline) . '</div>'
        . '<h1 style="font-size:22px;color:#17271e;margin:16px 0 8px;">Hi ' . htmlspecialchars($orderInfo['full_name'] ?? 'there') . ',</h1>'
        . '<p style="font-size:14px;line-height:1.7;color:#4b5c50;margin:0 0 22px;">' . htmlspecialchars($bodyText) . '</p>'
        . '<div style="background:#f7faf8;border:1px solid #e3ece6;border-radius:14px;padding:16px 18px;margin-bottom:22px;">'
        . '<table cellpadding="0" cellspacing="0" style="width:100%;font-size:13px;">'
        . '<tbody>'
        . '<tr><td style="padding:6px 0;color:#7b8a80;">Order Number</td><td style="padding:6px 0;text-align:right;font-weight:700;color:#17271e;">#' . (int)$orderInfo['order_id'] . '</td></tr>'
        . '<tr><td style="padding:6px 0;color:#7b8a80;">Market</td><td style="padding:6px 0;text-align:right;color:#17271e;">' . htmlspecialchars($orderInfo['market_name'] ?? 'Farmers Market') . '</td></tr>'
        . '<tr><td style="padding:6px 0;color:#7b8a80;">Pickup</td><td style="padding:6px 0;text-align:right;color:#17271e;">' . $pickupInfo . '</td></tr>'
        . '<tr><td style="padding:6px 0;color:#7b8a80;">Total Amount</td><td style="padding:6px 0;text-align:right;font-weight:800;color:#103f2d;">Rs ' . number_format((float)$orderInfo['total_amount'], 2) . '</td></tr>'
        . '</tbody>'
        . '</table>'
        . '</div>'
        . '<p style="font-size:12px;font-weight:700;color:#7b8a80;text-transform:uppercase;letter-spacing:1px;margin:0 0 8px;">Items</p>'
        . '<table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;">'
        . '<thead><tr style="background:#e6f4ec;">'
        . '<th style="padding:9px 14px;font-size:12px;color:#106b44;text-align:left;">Product</th>'
        . '<th style="padding:9px 14px;font-size:12px;color:#106b44;">Qty</th>'
        . '<th style="padding:9px 14px;font-size:12px;color:#106b44;text-align:right;">Subtotal</th>'
        . '</tr></thead>'
        . '<tbody>' . $itemRows . '</tbody>'
        . '</table>'
        . '<a href="http://localhost/techwiz7/public/orders" style="display:block;text-align:center;background:#17915b;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;padding:13px 20px;border-radius:12px;margin:24px 0 6px;">View My Orders</a>'
        . '<p style="font-size:12px;color:#9aa79e;text-align:center;margin:0;line-height:1.6;">Need help? Reply to this email or contact MarketLink support.<br>MarketLink - Fresh produce, direct from the farmer.</p>'
        . '</div>'
        . '</div>'
        . '</div>';

    sendEmail($orderInfo['email'], $subject, $html);

    $notifyText = [
        'accepted'   => "Order #$orderId has been accepted $actorShort.",
        'declined'   => "Order #$orderId was declined $actorShort.",
        'ready'      => "Order #$orderId is now ready for pickup.",
        'completed'  => "Order #$orderId has been completed."
    ];

    $notifyStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'order')");
    $notifyStmt->execute([(int)$orderInfo['account_id'], $emailMeta[$newStatus][0], $notifyText[$newStatus]]);
}

function adminStatusBadge($status)
{
    $map = [
        'placed'    => ['amber', 'Placed'],
        'accepted'  => ['blue', 'Accepted'],
        'ready'     => ['violet', 'Ready for Pickup'],
        'completed' => ['green', 'Completed'],
        'declined'  => ['red', 'Declined'],
        'cancelled' => ['grey', 'Cancelled']
    ];
    $badge = $map[$status] ?? ['grey', ucfirst($status)];
    return '<span class="a-badge ' . $badge[0] . '">' . $badge[1] . '</span>';
}

function adminApprovalBadge($status)
{
    $map = [
        'approved' => ['green', 'Approved'],
        'pending'  => ['amber', 'Pending'],
        'suspended' => ['red', 'Suspended']
    ];
    $badge = $map[$status] ?? ['grey', ucfirst($status)];
    return '<span class="a-badge ' . $badge[0] . '">' . $badge[1] . '</span>';
}

function adminUserBadge($status)
{
    $status = $status ?: 'active';
    $class = $status === 'active' ? 'green' : 'red';
    return '<span class="a-badge ' . $class . '">' . ucfirst($status) . '</span>';
}

function farmerSessionCheck()
{
    if (!(isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] == 1 && isset($_SESSION['role']) && $_SESSION['role'] == "farmer")) {
        set_flash("error", "Please login as a farmer first");
        $_SESSION['signupactive'] = false;
        redirect("signup");
    }
}

function currentFarmer($pdo)
{
    farmerSessionCheck();
    $rows = selectData($pdo, "SELECT f.*, u.username, u.email, u.contact FROM farmers AS f INNER JOIN users AS u ON f.user_id = u.id WHERE f.user_id = ?", [$_SESSION['user_id']]);
    if (empty($rows)) {
        return [];
    }
    return $rows[0];
}

function validateFarmer($pdo)
{
    $profileData = currentFarmer($pdo);

    if (empty($profileData)) {
        set_flash("error", "First you have to set your profile");
        redirect("add-profile");
    }

    if ($profileData['approval_status'] !== 'approved') {
        set_flash("error", "Your Profile Is Not Approved by Admin");
        redirect("add-profile");
    }

    return $profileData;
}

function adminSessionCheck()
{
    if (!(isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] == 1 && isset($_SESSION['role']) && $_SESSION['role'] == "admin")) {
        set_flash("error", "Please login as an admin first");
        redirect("login");
    }
}

function validateAdmin()
{
    adminSessionCheck();
    $rows = selectData($GLOBALS['pdo'], "SELECT * FROM users WHERE id = ? AND role = 'admin'", [$_SESSION['user_id']]);
    if (empty($rows)) {
        redirect("login");
    }
    return $rows[0];
}