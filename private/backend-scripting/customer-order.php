<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";
require_once "../config/customer.php";

header('Content-Type: application/json');
verify_csrf_ajax();

if (empty($_SESSION['loggedIn']) || ($_SESSION['role'] ?? '') !== 'customer') {
    ajaxOut(false, ['login' => true, 'error' => 'Please login as a customer first.']);
}
$custRows = selectData($pdo, "SELECT customer_id FROM customers WHERE user_id = ?", [$_SESSION['user_id']]);
if (empty($custRows)) {
    ajaxOut(false, ['error' => 'Customer profile not found.']);
}
$cid = (int)$custRows[0]['customer_id'];
$action = $_POST['action'] ?? '';

if ($action === 'place') {
    $farmerId = (int)($_POST['farmer_id'] ?? 0);
    $marketId = (int)($_POST['market_id'] ?? 0);
    $pickupDate = $_POST['pickup_date'] ?? '';
    $pickupSlot = $_POST['pickup_slot'] ?? '';
    $instructions = trim($_POST['instructions'] ?? '');

    if (!$farmerId || !$marketId || !$pickupDate || !$pickupSlot) {
        ajaxOut(false, ['error' => 'Please select market, date and time slot.']);
    }

    $link = selectData($pdo, "SELECT mf_id FROM market_farmer WHERE farmer_id = ? AND market_id = ?", [$farmerId, $marketId]);
    if (empty($link)) {
        ajaxOut(false, ['error' => 'This farmer is not scheduled at the selected market.']);
    }

$cart = $_SESSION['cart'] ?? [];

    $slotEnd = slotEndTime($pickupSlot);
    $pickTs = strtotime($pickupDate . $slotEnd);
    if (strtotime($pickupDate) < strtotime(date('Y-m-d', strtotime('+1 day')))) {
        ajaxOut(false, ['error' => 'Pickup must be at least one full day in the future.']);
    }
    $cutoff = $pickTs ? date('Y-m-d H:i:s', $pickTs - 86400) : null;

    try {
        $pdo->beginTransaction();
        $items = [];
        foreach ($cart as $pid => $qty) {
            $rows = selectData($pdo, "SELECT product_id, price, stock_quantity, is_available, is_sold_out FROM products WHERE product_id = ? AND farmer_id = ? FOR UPDATE", [$pid, $farmerId]);
            if (!empty($rows) && $rows[0]['is_available'] && !$rows[0]['is_sold_out'] && (int)$rows[0]['stock_quantity'] > 0) {
                $q = min((int)$qty, (int)$rows[0]['stock_quantity']);
                if ($q > 0) $items[] = ['product_id' => (int)$pid, 'price' => (float)$rows[0]['price'], 'qty' => $q];
            }
        }
        if (empty($items)) {
            $pdo->rollBack();
            ajaxOut(false, ['error' => 'No available items in your cart for this farmer.']);
        }

        $total = 0;
        foreach ($items as $it) $total += $it['price'] * $it['qty'];

        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, farmer_id, market_id, pickup_date, pickup_slot, total_amount, order_status, cutoff_time, special_instructions)
            VALUES (?, ?, ?, ?, ?, ?, 'placed', ?, ?)");
        $stmt->execute([$cid, $farmerId, $marketId, $pickupDate, $pickupSlot, $total, $cutoff, $instructions ?: null]);
        $oid = (int)$pdo->lastInsertId();

        $ist = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $dec = $pdo->prepare("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE product_id = ?");
        foreach ($items as $it) {
            $ist->execute([$oid, $it['product_id'], $it['qty'], $it['price'], $it['price'] * $it['qty']]);
            $dec->execute([$it['qty'], $it['product_id']]);
        }
        $pdo->prepare("UPDATE farmers SET total_orders = total_orders + 1 WHERE farmer_id = ?")->execute([$farmerId]);
        $pdo->prepare("UPDATE customers SET loyalty_points = loyalty_points + FLOOR(? / 500) WHERE customer_id = ?")->execute([$total, $cid]);
        $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Pre-Order Placed', ?, 'order')")
            ->execute([$_SESSION['user_id'], 'Your pre-order #' . $oid . ' is confirmed. Pay in person at pickup on ' . date('M j', strtotime($pickupDate)) . '.']);
        foreach ($items as $it) {
            if (isset($cart[(string)$it['product_id']]) || isset($cart[$it['product_id']])) {
                unset($cart[$it['product_id']], $cart[(string)$it['product_id']]);
            }
        }
        $pdo->commit();
        $_SESSION['cart'] = $cart;
        ajaxOut(true, ['order_id' => $oid, 'count' => array_sum($cart), 'msg' => 'Pre-order placed successfully!']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        ajaxOut(false, ['error' => 'Could not place order. Please try again.']);
    }
}

if ($action === 'modify' || $action === 'cancel') {
    $oid = (int)($_POST['order_id'] ?? 0);
    $order = selectData($pdo, "SELECT * FROM orders WHERE order_id = ? AND customer_id = ?", [$oid, $cid]);
    if (empty($order)) ajaxOut(false, ['error' => 'Order not found.']);
    $o = $order[0];
    if (!canEditOrder($o)) ajaxOut(false, ['error' => 'This order can no longer be modified.']);

    if ($action === 'cancel') {
        try {
            $pdo->beginTransaction();
            $items = selectData($pdo, "SELECT product_id, quantity FROM order_items WHERE order_id = ?", [$oid]);
            $rst = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
            foreach ($items as $it) $rst->execute([(int)$it['quantity'], (int)$it['product_id']]);
            $pdo->prepare("UPDATE orders SET order_status = 'cancelled' WHERE order_id = ?")->execute([$oid]);
            $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Order Cancelled', ?, 'order')")
                ->execute([$_SESSION['user_id'], 'Your pre-order #' . $oid . ' has been cancelled. Stock has been returned to the farmer.']);
            $pdo->commit();
            ajaxOut(true, ['msg' => 'Pre-order cancelled.']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            ajaxOut(false, ['error' => 'Could not cancel the order.']);
        }
    }

    $pickupDate = $_POST['pickup_date'] ?? '';
    $pickupSlot = $_POST['pickup_slot'] ?? '';
    $instructions = trim($_POST['instructions'] ?? '');
    if (!$pickupDate || !$pickupSlot) ajaxOut(false, ['error' => 'Please select date and slot.']);
    $slotEnd = slotEndTime($pickupSlot);
    $pickTs = strtotime($pickupDate . $slotEnd);
    $cutoff = $pickTs ? date('Y-m-d H:i:s', $pickTs - 86400) : null;
    try {
        $pdo->prepare("UPDATE orders SET pickup_date = ?, pickup_slot = ?, cutoff_time = ?, special_instructions = ? WHERE order_id = ?")
            ->execute([$pickupDate, $pickupSlot, $cutoff, $instructions ?: null, $oid]);
        $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Order Updated', ?, 'order')")
            ->execute([$_SESSION['user_id'], 'Pre-order #' . $oid . ' details were updated by you.']);
        ajaxOut(true, ['msg' => 'Order details updated.']);
    } catch (Exception $e) {
        ajaxOut(false, ['error' => 'Could not update the order.']);
    }
}

ajaxOut(false, ['error' => 'Invalid order action.']);