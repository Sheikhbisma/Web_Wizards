<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";
require_once "../config/customer.php";

header('Content-Type: application/json');
verify_csrf_ajax();

$msg = mb_strtolower(trim($_POST['message'] ?? ''));
if ($msg === '') {
    echo json_encode(['reply' => 'Salam! Ask me about markets, farmers, products, orders or pickup times.']);
    exit;
}

$reply = '';

// --- Markets (DB-driven) ---
if (preg_match('/market|location|address|where|hub|stall/i', $msg)) {
    $markets = selectData($pdo, "SELECT market_name, address, operating_days FROM markets WHERE is_active = 1 ORDER BY market_name LIMIT 4");
    if (!$markets) {
        $reply = 'There are no active markets right now. Please check back soon.';
    } else {
        $lines = [];
        foreach ($markets as $m) {
            $lines[] = $m['market_name'] . ' (' . $m['operating_days'] . ') - ' . $m['address'];
        }
        $reply = 'Here are our active pickup markets: ' . implode(' | ', $lines) . '. You can visit the Markets page for the full map and times.';
    }
} elseif (preg_match('/farmer|stall|seller|grower/i', $msg)) {
    $cnt = selectData($pdo, "SELECT COUNT(*) c FROM farmers WHERE approval_status = 'approved'", []);
    $reply = 'We work with ' . (int)$cnt['c'] . ' verified farmer stalls. Every stall is pre-approved by the MarketLink team, and their products, ratings and schedules are listed on the Farmers page.';
} elseif (preg_match('/product|produce|vegetable|fruit|fresh|basket|buy/i', $msg)) {
    $cnt = selectData($pdo, "SELECT COUNT(*) c FROM products WHERE is_available = 1 AND is_sold_out = 0 AND stock_quantity > 0", []);
    $reply = 'There are ' . (int)$cnt['c'] . ' fresh products available to pre-order right now. Browse the Products page and filter by category or market.';
} elseif (preg_match('/pickup|slot|time|hour|when|collect/i', $msg)) {
    $reply = 'Pickup runs during each market operating window (usually 7:00 AM to 2:00 PM) on the market days shown on the market page. At checkout you choose the exact date and 1-hour time slot before placing your order.';
} elseif (preg_match('/pay|payment|price|bill|card|cash/i', $msg)) {
    $reply = 'Payment is settled in person when you collect your order - no online payment is needed and we never ask for card details. Bring the order total to the stall and pay the farmer directly.';
} elseif (preg_match('/cancel|modif|change|cutoff/i', $msg)) {
    $reply = 'You can modify or cancel any pre-order free of charge until the 24-hour cutoff before pickup. Open My Orders, pick the order, and use the Modify or Cancel buttons.';
} elseif (preg_match('/order|parcel|track/i', $msg)) {
    if (!empty($_SESSION['loggedIn']) && ($_SESSION['role'] ?? '') === 'customer') {
        $custRows = selectData($pdo, "SELECT customer_id FROM customers WHERE user_id = ?", [$_SESSION['user_id']]);
        if (!empty($custRows)) {
            $cid = (int)$custRows[0]['customer_id'];
            $active = selectData($pdo, "SELECT COUNT(*) c FROM orders WHERE customer_id = ? AND order_status IN ('placed','accepted','ready')", [$cid]);
            $next = selectData($pdo, "SELECT order_id, pickup_date, pickup_slot FROM orders WHERE customer_id = ? AND order_status IN ('placed','accepted','ready') AND pickup_date >= CURDATE() ORDER BY pickup_date, pickup_slot LIMIT 1", [$cid]);
            if ((int)$active['c'] > 0 && !empty($next)) {
                $reply = 'You have ' . (int)$active['c'] . ' active pre-order(s). Your next pickup is order #' . $next[0]['order_id'] . ' on ' . date('D, M j', strtotime($next[0]['pickup_date'])) . ' at ' . $next[0]['pickup_slot'] . '. Track it from My Orders.';
            } else {
                $reply = 'You have no current pre-orders. Head to the Products page and place your first pre-order!';
            }
        } else {
            $reply = 'I could not find your customer profile. Please contact support if you think this is a mistake.';
        }
    } else {
        $reply = 'To check your orders, please log in to your customer account first - you will find your orders under My Orders after login.';
    }
} elseif (preg_match('/price|cost|how much|rate|worth|per kilo|per kg/i', $msg)) {
    $rows = selectData($pdo, "SELECT product_id, name, price, unit FROM products WHERE is_available = 1 AND is_sold_out = 0 AND price > 0 ORDER BY name");
    $hit = null;
    foreach ($rows as $p) {
        if (mb_strpos($msg, mb_strtolower($p['name'])) !== false) { $hit = $p; break; }
    }
    $reply = $hit
        ? $hit['name'] . ' is Rs ' . (float)$hit['price'] . ' per ' . ($hit['unit'] ?: 'unit') . ' right now. Add it to your cart and pre-order for your nearest market day.'
        : 'Prices are set by each farmer stall. Browse the Products page to compare fresh prices, or tell me the product name and I will look it up for you.';
} elseif (preg_match('/sold out|stock|available|low/i', $msg)) {
    $reply = 'Items that run out are marked Sold Out and cannot be added to the cart. Products with 10 or fewer units left show a Low Stock badge, so you can pre-order before they run out.';
} elseif (preg_match('/rating|review|feedback|stars/i', $msg)) {
    $reply = 'After an order is completed you can rate each product and the stall in the order details. Ratings are averaged and shown on product and farmer pages.';
} elseif (preg_match('/password|reset|login|account|signup|register/i', $msg)) {
    $reply = 'You can register as a customer or a farmer from the Sign Up page. Forgot your password? Use the "Forgot password" link on the login screen.';
} elseif (preg_match('/hi|hello|salam|assalam|hey|aoa/i', $msg)) {
    $reply = 'Salam! How can I help you today? Try asking about markets, farmers, products, pickup times or payment.';
} elseif (preg_match('/thank|thanks|shukria|jazak/i', $msg)) {
    $reply = 'You are most welcome! Is there anything else I can help you with?';
} else {
    $reply = 'Great question! I am a small demo assistant and I can answer about our markets, farmers, products, orders, pickup times and payment. For anything else, our support team is one message away on the Contact page.';
}

echo json_encode(['reply' => $reply]);