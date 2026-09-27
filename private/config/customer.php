<?php

$ML = [
    'site_name' => 'MarketLink',
    'tagline'   => 'Farm Fresh, Market Public',
    'currency'  => 'Rs ',
    'base_url'  => '/techwiz7/public',
];

// ------------------------------------------------------------------
// URL helper
// ------------------------------------------------------------------

function ML_asset($path)
{
    return '/techwiz7/public/' . ltrim($path, '/');
}
// ------------------------------------------------------------------
// Login / current user
// ------------------------------------------------------------------

// "Remember me" cookie ho to user ko phir se login kar do.
function autologin($pdo)
{
    if (!empty($_SESSION['loggedIn'])) {
        return;
    }
    if (empty($_COOKIE['ml_remember'])) {
        return;
    }

    $parts = explode(':', $_COOKIE['ml_remember']);
    if (count($parts) !== 2) {
        return;
    }

    list($uid, $token) = $parts;
    $rows = selectData($pdo, "SELECT * FROM users WHERE id = ? AND remember_token = ? AND status = 'active' AND is_verified = 1", [(int)$uid, $token]);
    if (empty($rows)) {
        return;
    }

    $u = $rows[0];
    session_regenerate_id(true);
    $_SESSION['loggedIn'] = true;
    $_SESSION['role']     = $u['role'];
    $_SESSION['user_id']  = $u['id'];
    $_SESSION['username'] = $u['username'];
    $_SESSION['email']    = $u['email'];
}

// Abhi logged-in user ka record (kisi bhi role ke liye). Nahi to empty array.
function currentUser($pdo)
{
    autologin($pdo);
    if (empty($_SESSION['loggedIn']) || empty($_SESSION['user_id'])) {
        return [];
    }

    $rows = selectData($pdo, "SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    return $rows[0] ?? [];
}

// Sirf customer login allow karo, warna signup par bhej do.
function customerSessionCheck()
{
    $isCustomer = !empty($_SESSION['loggedIn']) && ($_SESSION['role'] ?? '') === 'customer';
    if (!$isCustomer) {
        set_flash('error', 'Please login as a customer first');
        $_SESSION['signupactive'] = false;
        redirect('signup');
    }
}

// Logged-in customer ka profile (customers + users + market) return karo.
function currentCustomer($pdo)
{
    autologin($pdo);
    if (empty($_SESSION['loggedIn']) || empty($_SESSION['user_id'])) {
        return [];
    }
    if (($_SESSION['role'] ?? '') !== 'customer') {
        return [];
    }

    $rows = selectData($pdo, "SELECT c.customer_id, c.full_name, c.address, c.city, c.loyalty_points, c.profile_image,
            c.preferred_market_id, m.market_name AS preferred_market,
            u.id AS user_id, u.username, u.email, u.contact, u.status, u.created_at
            FROM customers AS c
            INNER JOIN users AS u ON u.id = c.user_id
            LEFT JOIN markets AS m ON m.market_id = c.preferred_market_id
            WHERE c.user_id = ?", [$_SESSION['user_id']]);
    return $rows[0] ?? [];
}

// Protected customer pages ke liye: login check + deactivated check + profile check.
function customerGuard($pdo)
{
    customerSessionCheck();

    $user = currentUser($pdo);
    if (($user['status'] ?? 'active') === 'deactivated') {
        unset($_SESSION['loggedIn'], $_SESSION['role'], $_SESSION['user_id'], $_SESSION['username'], $_SESSION['email']);
        set_flash('error', 'Your account has been deactivated. Please contact admin.');
        $_SESSION['signupactive'] = false;
        redirect('signup');
    }

    $cust = currentCustomer($pdo);
    if (empty($cust)) {
        set_flash('error', 'Your customer profile was not found. Please contact support.');
        $_SESSION['signupactive'] = false;
        redirect('signup');
    }

    return $cust;
}

// ------------------------------------------------------------------
// Ajax helpers
// ------------------------------------------------------------------

function verify_csrf_ajax()
{
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        ajaxOut(false, ['error' => 'Invalid or expired security token. Refresh the page and try again.']);
    }
}

function ajaxOut($ok, $data = [])
{
    header('Content-Type: application/json');
    echo json_encode(array_merge(['ok' => $ok], $data));
    exit;
}

// ------------------------------------------------------------------
// Money / order display
// ------------------------------------------------------------------

function money($n)
{
    return 'Rs ' . number_format((float)$n, 0);
}

function orderRef($id)
{
    return 'EB-' . date('ymd') . '-' . str_pad((int)$id, 4, '0', STR_PAD_LEFT);
}

function orderStatusMap()
{
    return [
        'placed'    => ['label' => 'Pre-Ordered', 'class' => 'text-bg-secondary'],
        'accepted'  => ['label' => 'Accepted', 'class' => 'text-bg-primary'],
        'ready'     => ['label' => 'Ready', 'class' => 'text-bg-success'],
        'completed' => ['label' => 'Completed', 'class' => 'text-bg-dark'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'text-bg-danger'],
        'declined'  => ['label' => 'Declined', 'class' => 'text-bg-danger'],
    ];
}

function orderStatusBadge($status)
{
    $map = orderStatusMap();
    $s = $map[$status] ?? ['label' => ucfirst($status), 'class' => 'text-bg-secondary'];
    return '<span class="badge rounded-pill ' . $s['class'] . '">' . $s['label'] . '</span>';
}

// Order "placed" ya "accepted" ho AUR cutoff time abhi na guzra ho to edit ho sakta hai.
function canEditOrder($order)
{
    $editableStatuses = ['placed', 'accepted'];
    $editableStatus   = in_array($order['order_status'], $editableStatuses, true);
    $beforeCutoff     = !empty($order['cutoff_time']) && strtotime($order['cutoff_time']) > time();
    return $editableStatus && $beforeCutoff;
}

// Cutoff ke ilawa kitna time bacha hai — "2h 30m left" jese.
function cutoffInfo($order)
{
    if (empty($order['cutoff_time'])) {
        return ['editable' => false, 'label' => 'No cutoff set'];
    }

    $minutesLeft = (int)round((strtotime($order['cutoff_time']) - time()) / 60);
    $editable    = canEditOrder($order);

    if ($minutesLeft <= 0) {
        return ['editable' => false, 'label' => 'Cutoff passed'];
    }
    if ($minutesLeft >= 60) {
        $hours   = floor($minutesLeft / 60);
        $minutes = $minutesLeft % 60;
        return ['editable' => $editable, 'label' => $hours . 'h ' . ($minutes ? $minutes . 'm ' : '') . 'left'];
    }
    return ['editable' => $editable, 'label' => $minutesLeft . 'm left'];
}

// ------------------------------------------------------------------
// Cart helpers
// ------------------------------------------------------------------

function cartCount()
{
    return array_sum($_SESSION['cart'] ?? []);
}

function cartItems($pdo)
{
    $cart  = $_SESSION['cart'] ?? [];
    $items = [];

    foreach ($cart as $pid => $qty) {
        $rows = selectData($pdo, "SELECT p.product_id, p.farmer_id, p.category_id, p.name, p.description, p.price, p.unit,
                p.stock_quantity, p.image_url, p.is_available, p.is_sold_out, p.avg_rating,
                f.stall_name, c.category_name
                FROM products AS p
                INNER JOIN farmers AS f ON f.farmer_id = p.farmer_id
                LEFT JOIN categories AS c ON c.category_id = p.category_id
                WHERE p.product_id = ?", [$pid]);

        if (empty($rows)) {
            continue;
        }

        $p         = $rows[0];
        $maxQty    = max((int)$p['stock_quantity'], 0);
        $inStock   = $p['is_available'] && !$p['is_sold_out'] && $maxQty > 0;
        $p['qty']  = $inStock ? min((int)$qty, $maxQty) : 0;
        $items[]   = $p;
    }

    return $items;
}

function cartTotal($pdo)
{
    $total = 0;
    foreach (cartItems($pdo) as $p) {
        $total += $p['price'] * $p['qty'];
    }
    return $total;
}

// ------------------------------------------------------------------
// Favorites helpers
// ------------------------------------------------------------------

function favCount($pdo, $customerId)
{
    $rows = selectData($pdo, "SELECT COUNT(*) AS c FROM favorites WHERE customer_id = ?", [$customerId]);
    return (int)($rows[0]['c'] ?? 0);
}

// Kya yeh product / farmer / market favorited hai?
function isFav($pdo, $customerId, $farmerId = null, $productId = null, $marketId = null)
{
    if ($productId !== null) {
        $rows = selectData($pdo, "SELECT favorite_id FROM favorites WHERE customer_id = ? AND product_id = ?", [$customerId, $productId]);
    } elseif ($farmerId !== null) {
        $rows = selectData($pdo, "SELECT favorite_id FROM favorites WHERE customer_id = ? AND farmer_id = ? AND product_id IS NULL", [$customerId, $farmerId]);
    } elseif ($marketId !== null) {
        $rows = selectData($pdo, "SELECT favorite_id FROM favorites WHERE customer_id = ? AND market_id = ?", [$customerId, $marketId]);
    } else {
        return false;
    }
    return !empty($rows);
}

// ------------------------------------------------------------------
// Notification helpers
// ------------------------------------------------------------------

function notifCount($pdo, $userId)
{
    $rows = selectData($pdo, "SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
    return (int)($rows[0]['c'] ?? 0);
}

function addNotif($pdo, $userId, $title, $message, $type)
{
    $allowed = ['order', 'restock', 'announcement', 'review'];
    $type    = in_array($type, $allowed, true) ? $type : 'announcement';
    return insertData($pdo, 'notifications', [
        'user_id' => $userId,
        'title'   => $title,
        'message' => $message,
        'type'    => $type,
    ]);
}

// ------------------------------------------------------------------
// Pickup date / slot helpers
// ------------------------------------------------------------------

// Market ke operating days ke aage 14 din ke andar walay dates.
function pickupDates($market)
{
    if (empty($market['operating_days'])) {
        return [];
    }

    $days   = array_map('strtolower', array_map('trim', explode(',', $market['operating_days'])));
    $dowMap = ['monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 7];
    $dates  = [];

    for ($i = 1; $i <= 14; $i++) {
        $ts  = strtotime("+{$i} day 00:00:00");
        $key = strtolower(date('l', $ts));
        if (in_array($key, $days, true)) {
            $dates[] = [
                'date'  => date('Y-m-d', $ts),
                'label' => date('D, M j', $ts),
            ];
        }
    }

    return $dates;
}

// Market ke opening/closing times se 1 ghante ke pickup slots banao (max 6).
function pickupSlots($market)
{
    if (empty($market['opening_time']) || empty($market['closing_time'])) {
        return [];
    }

    $start       = strtotime($market['opening_time']);
    $end         = strtotime($market['closing_time']);
    $slotLength  = 3600;  // 1 hour slot
    $slotGap     = 5400;  // har 90 minute par naya slot
    $slots       = [];

    for ($t = $start; ($t + $slotLength) <= $end && count($slots) < 6; $t += $slotGap) {
        $slots[] = date('H:i', $t) . ' - ' . date('H:i', $t + $slotLength);
    }

    return $slots;
}

// Slot string "07:00 - 08:00" ka aakhri time nikal do.
function slotEndTime($slot)
{
    $parts = explode('-', (string)$slot);
    if (count($parts) < 2) {
        return '00:00';
    }
    return trim($parts[1]);
}

// ------------------------------------------------------------------
// Display helpers
// ------------------------------------------------------------------

function starsHtml($rating, $size = 18)
{
    $rating = (float)$rating;
    $out    = '<span class="ml-stars" style="font-size:' . $size . 'px;">';
    for ($i = 1; $i <= 5; $i++) {
        $cls = $rating >= $i ? 'bi-star-fill' : 'bi-star';
        $out .= '<i class="bi ' . $cls . ' ms-1"></i>';
    }
    $out .= '<span class="ms-2 small fw-semibold">' . number_format($rating, 1) . '</span>';
    $out .= '</span>';
    return $out;
}

function timeAgo($datetime)
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . ' min ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day(s) ago';
    return date('M j, Y', strtotime($datetime));
}

function stockBadge($p)
{
    if (!$p['is_available'] || $p['is_sold_out'] || (int)$p['stock_quantity'] <= 0) {
        return '<span class="badge text-bg-danger">Sold Out</span>';
    }
    if ((int)$p['stock_quantity'] <= 10) {
        return '<span class="badge text-bg-warning text-dark">Low Stock - ' . (int)$p['stock_quantity'] . ' left</span>';
    }
    return '<span class="badge text-bg-success">In Stock</span>';
}

function hasReview($pdo, $customerId, $orderId, $productId)
{
    $rows = selectData($pdo, "SELECT review_id FROM reviews WHERE customer_id = ? AND order_id = ? AND product_id = ?", [$customerId, $orderId, $productId]);
    return !empty($rows);
}

function getReviewedIds($pdo, $customerId, $orderId)
{
    $rows = selectData($pdo, "SELECT product_id FROM reviews WHERE customer_id = ? AND order_id = ?", [$customerId, $orderId]);
    $ids  = [];
    foreach ($rows as $r) {
        $ids[] = (int)$r['product_id'];
    }
    return $ids;
}

// Category glyph. This used to return an emoji, but emoji render differently
// on every OS, ignore the site's colour palette, and read as placeholder art
// next to the real product photos. Font Awesome is already loaded on every
// page, so the icon inherits currentColor and stays on-brand.
function catIconClass($name)
{
    $map = [
        'Vegetables' => 'fa-carrot',
        'Fruits'     => 'fa-apple-whole',
        'Dairy'      => 'fa-bottle-droplet',
        'Bakery'     => 'fa-bread-slice',
        'Herbs'      => 'fa-leaf',
        'Meat'       => 'fa-drumstick',
        'Grains'     => 'fa-wheat-awn',
    ];
    return $map[$name] ?? 'fa-basket-shopping';
}

function catIcon($name, $extraClass = '')
{
    return '<i class="fa-solid ' . catIconClass($name) . ($extraClass ? ' ' . $extraClass : '') . '"></i>';
}

// Product image ka canonical web URL. DB me sirf filename store hota hai, is
// liye ye do jagah dekhta hai: nayi uploads 'Uploads/img/' me, legacy records
// 'Uploads/' root me. Jo file disk par na mile wahan '' return hota hai taake
// caller icon placeholder par fallback kar sake.
function prodImgSrc($p)
{
    if (empty($p['image_url'])) {
        return '';
    }
    $file = basename((string)$p['image_url']);
    $root = __DIR__ . '/../../public/Uploads/';
    foreach (['img/' . $file, $file] as $rel) {
        if (is_file($root . $rel)) {
            return ML_asset('Uploads/' . $rel);
        }
    }
    return ML_asset('Uploads/img/' . $file);
}

function prodImg($p)
{
    $src = prodImgSrc($p);
    if ($src !== '') {
        return '<img src="' . $src . '" alt="' . sanitize_output($p['name']) . '" loading="lazy">';
    }
    return '<span class="ml-img-fallback">' . catIcon($p['category_name'] ?? '') . '</span>';
}

function marketPickups($mk)
{
    $shortDays = [
        'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed',
        'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun',
    ];

    $days = [];
    foreach (explode(',', $mk['operating_days'] ?? '') as $d) {
        $day = strtolower(trim($d));
        if (isset($shortDays[$day])) {
            $days[] = $shortDays[$day];
        }
    }

    return $days ? implode(' · ', $days) : '—';
}