<?php
require_once __DIR__ . '/../config/dbconnect.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/customer.php';

$msg = '';

function mlCount($pdo, $sql, $binds = [])
{
    $row = selectData($pdo, $sql, $binds);
    return (int)($row[0]['c'] ?? 0);
}

$demoUser = selectData($pdo, "SELECT id FROM users WHERE email = ?", ['daniyal@gmail.com']);
if (!empty($demoUser)) {
    $demoUserId = (int)$demoUser[0]['id'];
    $demoCust = selectData($pdo, "SELECT customer_id FROM customers WHERE user_id = ?", [$demoUserId]);
    $healthyStock = mlCount($pdo, "SELECT COUNT(*) c FROM products p INNER JOIN farmers f ON f.farmer_id = p.farmer_id WHERE p.stock_quantity > 0 AND p.is_available = 1 AND p.is_sold_out = 0");
    if (!empty($demoCust) && mlCount($pdo, "SELECT COUNT(*) c FROM orders WHERE customer_id = ?", [$demoCust[0]['customer_id']]) > 0 && $healthyStock >= 20) {
        echo '<div style="padding:20px;font-family:sans-serif;">Demo data is already seeded. Visit the customer dashboard.</div>';
        echo '<a href="/techwiz7/public/login" style="padding:20px;">Login</a>';
        exit;
    }
}

$existingMk = selectData($pdo, "SELECT market_id FROM markets ORDER BY market_id");
$marketIds = [];
if (count($existingMk) >= 5) {
    foreach ($existingMk as $m) {
        $marketIds[] = (int)$m['market_id'];
    }
    $msg .= 'Markets: already present (' . count($marketIds) . ')<br>';
} else {
    $markets = [
        ['Clifton Fresh Market', 'Plot 12, MCB Road, Clifton, Karachi', 24.81000000, 67.03000000, 'Tuesday,Thursday,Saturday', '07:00:00', '14:00:00'],
        ['Gulshan Community Market', 'University Road, Gulshan-e-Iqbal, Karachi', 24.93300000, 67.07500000, 'Monday,Wednesday,Friday', '06:30:00', '13:30:00'],
        ['Saddar Juma Bazaar', 'Zaibunnisa Street, Saddar, Karachi', 24.85400000, 67.03500000, 'Friday', '05:30:00', '12:00:00'],
        ['North Nazimabad Green Market', 'Block H, North Nazimabad, Karachi', 24.95600000, 67.03300000, 'Tuesday,Friday,Sunday', '07:00:00', '15:00:00'],
        ['Malir City Farm Market', 'Main Malir Halt Road, Karachi', 24.89700000, 67.19200000, 'Wednesday,Saturday', '06:00:00', '13:00:00'],
    ];
    foreach ($markets as $m) {
        if (mlCount($pdo, "SELECT COUNT(*) c FROM markets WHERE market_name = ?", [$m[0]]) > 0) {
            $r = selectData($pdo, "SELECT market_id FROM markets WHERE market_name = ?", [$m[0]]);
            $marketIds[] = (int)$r[0]['market_id'];
            continue;
        }
        insertData($pdo, 'markets', [
            'market_name'   => $m[0],
            'address'       => $m[1],
            'latitude'      => $m[2],
            'longitude'     => $m[3],
            'operating_days'=> $m[4],
            'opening_time'  => $m[5],
            'closing_time'  => $m[6],
            'map_provider'  => 'OpenStreetMap',
            'is_active'     => 1,
        ]);
        $marketIds[] = (int)$pdo->lastInsertId();
    }
    $msg .= 'Markets: ' . count($markets) . '<br>';
}

$farmers = [
    ['AliRaza', 'ali.raza@gmail.com', '03331234567', 'Ayesha Farms', 'Ayesha Farms produce organic vegetables grown in Tando Adam, harvested before sunrise and at your gate by noon.', 'Plot 8, Farm Colony Tando Adam', 25.76310000, 68.66130000, 'Ali Raza'],
    ['Nida', 'nida.orchard@gmail.com', '03451234567', 'Nida Orchard', 'Family-run mango and citrus orchard. Seasonal fruits only, picked ripe and packed the same morning.', 'Orchard Road, Mirpurkhas', 25.52600000, 69.01150000, 'Nida Bano'],
    ['GreenGate', 'greengate.dairy@gmail.com', '03211234567', 'GreenGate Dairy', 'Pure desi dairy — raw milk, fresh butter and clotted cream collected every single morning.', 'Green Gate Lane, Latifabad, Hyderabad', 25.39200000, 68.37200000, 'Usman Khan'],
    ['SunriseBakery', 'sunrise.bake@gmail.com', '03001234567', 'Sunrise Bakery', 'Wood-fired whole-wheat bread, sourdough and flatbread baked daily at dawn.', 'Shop 21, Shahrah-e-Quaideen, Saddar', 24.86110000, 67.00990000, 'Farhan Sheikh'],
    ['HerbHaven', 'herbhaven.pk@gmail.com', '03151234567', 'Herb Haven', 'Hydroponic herbs and microgreens grown in Karachi — basil, mint, coriander and greens year round.', 'Zone C, Scheme 33, Karachi', 24.98330000, 67.08200000, 'Sana Abbasi'],
    ['Farm2Basket', 'farm2basket@gmail.com', '03161234567', 'Farm2Basket', 'A collective of small holder farmers delivering mixed seasonal baskets of fruit and vegetables.', 'Main PAF Road, Malir, Karachi', 24.91010000, 67.20300000, 'Imran Shah'],
];

$farmerIds = [];
$addedFarmers = 0;
foreach ($farmers as $f) {
    $u = selectData($pdo, "SELECT id FROM users WHERE email = ?", [$f[1]]);
    if (!empty($u)) {
        $uid = (int)$u[0]['id'];
    } else {
        insertData($pdo, 'users', [
            'username' => $f[0], 'email' => $f[1], 'contact' => $f[2],
            'password' => password_hash('Farm@123', PASSWORD_DEFAULT),
            'otp_code' => '111111', 'otp_expiry' => null,
            'is_verified' => 1, 'role' => 'farmer', 'status' => 'active',
        ]);
        $uid = (int)$pdo->lastInsertId();
    }
    $fr = selectData($pdo, "SELECT farmer_id FROM farmers WHERE user_id = ?", [$uid]);
    if (!empty($fr)) {
        $farmerIds[] = (int)$fr[0]['farmer_id'];
        continue;
    }
    insertData($pdo, 'farmers', [
        'user_id' => $uid, 'stall_name' => $f[3], 'description' => $f[4],
        'profile_image' => null, 'address' => $f[5],
        'latitude' => $f[6], 'longitude' => $f[7],
        'approval_status' => 'approved', 'avg_rating' => 0,
        'total_orders' => 0, 'contact_person' => $f[8],
    ]);
    $farmerIds[] = (int)$pdo->lastInsertId();
    $addedFarmers++;
}
$msg .= 'Farmers: ' . count($farmerIds) . ' (' . $addedFarmers . ' new)<br>';

$links = [
    [$marketIds[0], 0, 'A-01', '07:00:00', '13:30:00', 'Tuesday'],
    [$marketIds[0], 1, 'A-02', '07:00:00', '13:00:00', 'Tuesday'],
    [$marketIds[0], 4, 'C-05', '07:00:00', '13:30:00', 'Thursday'],
    [$marketIds[0], 5, 'B-12', '07:00:00', '13:00:00', 'Saturday'],
    [$marketIds[1], 1, 'D-03', '06:30:00', '12:30:00', 'Wednesday'],
    [$marketIds[1], 2, 'D-04', '06:30:00', '12:30:00', 'Wednesday'],
    [$marketIds[1], 4, 'E-09', '06:30:00', '12:30:00', 'Monday'],
    [$marketIds[2], 3, 'F-01', '05:30:00', '11:00:00', 'Friday'],
    [$marketIds[2], 0, 'F-02', '05:30:00', '11:00:00', 'Friday'],
    [$marketIds[3], 2, 'G-07', '07:00:00', '14:00:00', 'Sunday'],
    [$marketIds[3], 3, 'G-08', '07:00:00', '14:00:00', 'Sunday'],
    [$marketIds[3], 5, 'H-02', '07:00:00', '13:30:00', 'Tuesday'],
    [$marketIds[4], 0, 'J-01', '06:00:00', '12:00:00', 'Wednesday'],
    [$marketIds[4], 1, 'J-02', '06:00:00', '12:00:00', 'Wednesday'],
    [$marketIds[4], 4, 'K-05', '06:00:00', '12:00:00', 'Saturday'],
];
$addedLinks = 0;
foreach ($links as $l) {
    if (mlCount($pdo, "SELECT COUNT(*) c FROM market_farmer WHERE market_id = ? AND farmer_id = ? AND stall_number = ?", [$l[0], $farmerIds[$l[1]], $l[2]]) > 0) {
        continue;
    }
    insertData($pdo, 'market_farmer', [
        'market_id' => $l[0], 'farmer_id' => $farmerIds[$l[1]],
        'stall_number' => $l[2], 'pickup_start' => $l[3], 'pickup_end' => $l[4], 'day_of_week' => $l[5],
    ]);
    $addedLinks++;
}
$msg .= 'Market links: ' . count($links) . ' (' . $addedLinks . ' new)<br>';

$products = [
    [0, 'Tomatoes', 1, 120, '1kg', 80, 'Sun ripened desi tomatoes, tangy and juicy.'],
    [0, 'Potatoes', 1, 90, '1kg', 60, 'Firm sandy-grown potatoes, perfect for sabzi and fries.'],
    [0, 'Onions', 1, 150, '1kg', 50, 'Pungent red onions, stored crisp.'],
    [0, 'Cucumbers', 1, 45, '1kg', 90, 'Cool greenhouse cucumbers, crunchy and hydrating.'],
    [1, 'Mangoes Sindhri', 2, 40, '1kg', 350, 'Sweet aromatic Sindhri mangoes, orchard fresh.'],
    [1, 'Oranges', 2, 75, '1kg', 160, 'Juicy kinnow oranges, seeded and sweet.'],
    [1, 'Bananas', 2, 120, '1kg', 100, 'Ripe but firm bananas, bunch fresh.'],
    [1, 'Strawberries', 2, 30, '250g', 320, 'Plump red strawberries, farm selected.'],
    [2, 'Fresh Milk', 3, 60, '1L', 180, 'Raw desi milk, boiled and bottled every morning.'],
    [2, 'Desi Butter', 3, 25, '500g', 700, 'Hand churned desi butter from pure cream.'],
    [2, 'Desi Yogurt', 3, 40, '500g', 160, 'Thick, tangy desi dahi made from whole milk.'],
    [2, 'Cream Malai', 3, 20, '250g', 240, 'Clotted cream collected from fresh milk.'],
    [3, 'Whole Wheat Bread', 4, 35, '1 loaf', 150, 'Wood fired whole wheat loaf, baked at dawn.'],
    [3, 'Sourdough Bread', 4, 25, '1 loaf', 260, 'Naturally leavened sourdough, crusty and light.'],
    [3, 'Roti (12 pc)', 4, 30, 'pack', 120, 'Hand rolled chapati, soft and ready to warm.'],
    [4, 'Fresh Mint', 5, 30, '100g', 60, 'Fragrant mint sprigs grown hydroponically.'],
    [4, 'Coriander', 5, 30, '100g', 40, 'Tender coriander with deep green leaves.'],
    [4, 'Basil', 5, 20, '50g', 110, 'Sweet basil ideal for sauces and salads.'],
    [5, 'Mixed Veg Basket', 1, 35, 'basket', 550, 'Seasonal box of 8+ vegetables, market selection.'],
    [5, 'Leafy Greens Pack', 1, 40, '500g', 140, 'Spinach, mustard greens and lettuce mix.'],
];
$pid = [];
$addedProducts = 0;
foreach ($products as $pr) {
    $fid = $farmerIds[$pr[0]];
    $ex = selectData($pdo, "SELECT product_id FROM products WHERE farmer_id = ? AND name = ?", [$fid, $pr[1]]);
    if (!empty($ex)) {
        $pid[] = (int)$ex[0]['product_id'];
        $pdo->prepare("UPDATE products SET description = ?, price = ?, unit = ?, stock_quantity = ?, image_url = NULL WHERE product_id = ?")
            ->execute([$pr[6], $pr[3], $pr[4], $pr[5], $ex[0]['product_id']]);
        continue;
    }
    insertData($pdo, 'products', [
        'farmer_id' => $fid, 'category_id' => $pr[2],
        'name' => $pr[1], 'description' => $pr[6], 'price' => $pr[3],
        'unit' => $pr[4], 'stock_quantity' => $pr[5],
        'image_url' => null, 'is_available' => 1, 'is_sold_out' => 0, 'avg_rating' => 0,
    ]);
    $pid[] = (int)$pdo->lastInsertId();
    $addedProducts++;
}
$msg .= 'Products: ' . count($products) . ' (' . $addedProducts . ' new)<br>';

if (!empty($demoUser)) {
    $demoUserId = (int)$demoUser[0]['id'];
    $demoCust = selectData($pdo, "SELECT customer_id FROM customers WHERE user_id = ?", [$demoUserId]);
    if (!empty($demoCust)) {
        $demoCid = (int)$demoCust[0]['customer_id'];
    } else {
        insertData($pdo, 'customers', [
            'user_id' => $demoUserId, 'full_name' => 'Daniyal Ahmed',
            'preferred_market_id' => $marketIds[0], 'loyalty_points' => 120,
            'address' => 'House 4, Street 9, DHA Phase 5, Karachi', 'city' => 'Karachi', 'profile_image' => null,
        ]);
        $demoCid = (int)$pdo->lastInsertId();
    }
} else {
    insertData($pdo, 'users', [
        'username' => 'Daniyal', 'email' => 'daniyal@gmail.com', 'contact' => '03331231234',
        'password' => password_hash('Test@123', PASSWORD_DEFAULT),
        'otp_code' => '555555', 'otp_expiry' => null, 'is_verified' => 1,
        'role' => 'customer', 'status' => 'active',
    ]);
    $demoUserId = (int)$pdo->lastInsertId();
    insertData($pdo, 'customers', [
        'user_id' => $demoUserId, 'full_name' => 'Daniyal Ahmed',
        'preferred_market_id' => $marketIds[0], 'loyalty_points' => 120,
        'address' => 'House 4, Street 9, DHA Phase 5, Karachi', 'city' => 'Karachi', 'profile_image' => null,
    ]);
    $demoCid = (int)$pdo->lastInsertId();
}
$msg .= 'Demo customer ready<br>';

$favs = [
    [$demoCid, $farmerIds[0], null],
    [$demoCid, $farmerIds[2], null],
    [$demoCid, null, $pid[4]],
    [$demoCid, null, $pid[0]],
    [$demoCid, null, $pid[16]],
];
$addedFavs = 0;
foreach ($favs as $fv) {
    if (mlCount($pdo, "SELECT COUNT(*) c FROM favorites WHERE customer_id = ? AND (farmer_id <=> ?) AND (product_id <=> ?)",
        [$fv[0], $fv[1], $fv[2]]) > 0) {
        continue;
    }
    insertData($pdo, 'favorites', ['customer_id' => $fv[0], 'farmer_id' => $fv[1], 'product_id' => $fv[2]]);
    $addedFavs++;
}
$msg .= 'Favorites: 5 (' . $addedFavs . ' new)<br>';

$hasOrders = mlCount($pdo, "SELECT COUNT(*) c FROM orders WHERE customer_id = ?", [$demoCid]) > 0;

if (!$hasOrders) {
    $now = time();
    $pickDate = function ($days) use ($now) {
        return date('Y-m-d', strtotime('+' . $days . ' day', $now));
    };

    $seedOrder = function ($farmerId, $marketId, $items, $status, $pickDate, $slot, $instruction, $oldDays) use ($pdo, $demoCid, $now) {
        $pickup = strtotime($pickDate . ' ' . slotEndTime($slot));
        $cutoff = date('Y-m-d H:i:s', $pickup - 86400);
        $total = 0;
        foreach ($items as $it) {
            $total += $it[2] * $it[3];
        }
        insertData($pdo, 'orders', [
            'customer_id' => $demoCid, 'farmer_id' => $farmerId, 'market_id' => $marketId,
            'pickup_date' => $pickDate, 'pickup_slot' => $slot, 'total_amount' => $total,
            'order_status' => $status, 'cutoff_time' => $cutoff, 'special_instructions' => $instruction,
            'order_date' => date('Y-m-d H:i:s', $now - $oldDays * 86400),
        ]);
        $oid = (int)$pdo->lastInsertId();
        foreach ($items as $it) {
            insertData($pdo, 'order_items', [
                'order_id' => $oid, 'product_id' => $it[0],
                'quantity' => $it[3], 'unit_price' => $it[2], 'subtotal' => $it[2] * $it[3],
            ]);
        }
        $pdo->prepare("UPDATE farmers SET total_orders = total_orders + 1 WHERE farmer_id = ?")->execute([$farmerId]);
        $pdo->prepare("UPDATE customers SET loyalty_points = loyalty_points + FLOOR(? / 500) WHERE customer_id = ?")->execute([$total, $demoCid]);
        return $oid;
    };

    $oid1 = $seedOrder($farmerIds[0], $marketIds[0],
        [[$pid[0], 'Tomatoes', 120, 2], [$pid[2], 'Onions', 50, 3]],
        'completed', $pickDate(-6), '07:00 - 08:00', 'Firm green tomatoes please', 7);
    $pdo->prepare("UPDATE orders SET pickup_date = ?, pickup_slot = ?, cutoff_time = ? WHERE order_id = ?")
        ->execute([$pickDate(-6), '07:00 - 08:00', date('Y-m-d H:i:s', strtotime($pickDate(-6) . ' 08:00') - 86400), $oid1]);

    insertData($pdo, 'reviews', [
        'order_id' => $oid1, 'product_id' => $pid[0], 'farmer_id' => $farmerIds[0],
        'customer_id' => $demoCid, 'rating' => 5, 'comment' => 'Best tomatoes I have found in Karachi. Super fresh.',
        'farmer_response' => 'Shukriya Daniyal! See you next week.', 'review_date' => date('Y-m-d H:i:s', $now - 5 * 86400),
    ]);
    $pdo->prepare("UPDATE products SET avg_rating = 5.0 WHERE product_id = ?")->execute([$pid[0]]);
    $pdo->prepare("UPDATE farmers SET avg_rating = 5.0 WHERE farmer_id = ?")->execute([$farmerIds[0]]);

    $oid2 = $seedOrder($farmerIds[3], $marketIds[2],
        [[$pid[12], 'Whole Wheat Bread', 150, 2], [$pid[14], 'Roti', 120, 1]],
        'completed', $pickDate(-3), '05:30 - 06:30', 'Please pack bread separately', 4);
    $pdo->prepare("UPDATE orders SET pickup_date = ?, pickup_slot = ?, cutoff_time = ? WHERE order_id = ?")
        ->execute([$pickDate(-3), '05:30 - 06:30', date('Y-m-d H:i:s', strtotime($pickDate(-3) . ' 06:30') - 86400), $oid2]);

    insertData($pdo, 'reviews', [
        'order_id' => $oid2, 'product_id' => $pid[12], 'farmer_id' => $farmerIds[3],
        'customer_id' => $demoCid, 'rating' => 4, 'comment' => 'Bread is excellent, still warm at pickup.',
        'review_date' => date('Y-m-d H:i:s', $now - 2 * 86400),
    ]);
    $pdo->prepare("UPDATE products SET avg_rating = 4.0 WHERE product_id = ?")->execute([$pid[12]]);
    $pdo->prepare("UPDATE farmers SET avg_rating = 4.0 WHERE farmer_id = ?")->execute([$farmerIds[3]]);

    $oid3 = $seedOrder($farmerIds[2], $marketIds[1],
        [[$pid[8], 'Fresh Milk', 180, 3], [$pid[10], 'Desi Yogurt', 160, 2]],
        'ready', $pickDate(0), '06:30 - 08:00', 'Need milk before 7am please', 0);
    $pdo->prepare("UPDATE orders SET pickup_date = ?, pickup_slot = ?, cutoff_time = ? WHERE order_id = ?")
        ->execute([$pickDate(0), '06:30 - 07:30', date('Y-m-d H:i:s', strtotime($pickDate(0) . ' 07:30') - 86400), $oid3]);

    $oid4 = $seedOrder($farmerIds[1], $marketIds[0],
        [[$pid[4], 'Mangoes Sindhri', 350, 2], [$pid[5], 'Oranges', 160, 3]],
        'accepted', $pickDate(3), '07:00 - 08:30', 'Choose ripe mangoes', 1);
    $oid5 = $seedOrder($farmerIds[5], $marketIds[3],
        [[$pid[18], 'Mixed Veg Basket', 550, 1], [$pid[19], 'Leafy Greens Pack', 140, 2]],
        'placed', $pickDate(5), '07:00 - 08:00', 'Amla basket this time', 0);

    $oid6 = $seedOrder($farmerIds[4], $marketIds[0],
        [[$pid[16], 'Coriander', 40, 4]],
        'cancelled', $pickDate(-2), '07:00 - 08:00', '', 3);
    $msg .= 'Orders: 6<br>';

    $notifs = [
        [$demoUserId, 'Order Ready', 'Your milk order #' . $oid3 . ' is ready for pickup today at Gulshan Community Market.', 'order'],
        [$demoUserId, 'Order Accepted', 'Farmer Nida Orchard accepted your order #' . $oid4 . '.', 'order'],
        [$demoUserId, 'Order Placed', 'Pre-order #' . $oid5 . ' is confirmed. Payment will be settled at pickup.', 'order'],
        [$demoUserId, 'Order Cancelled', 'Pre-order #' . $oid6 . ' has been cancelled. See you soon!', 'order'],
        [$demoUserId, 'Welcome to MarketLink', 'Pre-order fresh produce from farmers and pick up at the market.', 'announcement'],
    ];
    foreach ($notifs as $n) {
        insertData($pdo, 'notifications', [
            'user_id' => $n[0], 'title' => $n[1], 'message' => $n[2],
            'type' => $n[3], 'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s', $now - rand(1, 6) * 3600),
        ]);
    }
    $msg .= 'Notifications: 5<br>';
} else {
    $msg .= 'Orders: already present, skipped<br>';
}

echo '<div style="padding:20px;font-family:sans-serif;line-height:1.8;">';
echo '<h3>MarketLink Demo Data Ready</h3>';
echo $msg;
echo 'Login with <b>daniyal@gmail.com</b> / <b>Test@123</b>';
echo ' &middot; <a href="/techwiz7/public/customer-dashboard">Open Customer Dashboard</a>';
echo '</div>';