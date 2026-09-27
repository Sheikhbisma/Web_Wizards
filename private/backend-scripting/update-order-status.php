<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect("farmer-orders");
}

verify_csrf();
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];
$orderId = (int)($_POST['order_id'] ?? 0);

if ($orderId <= 0) {
    set_flash("error", "Invalid order.");
    redirect("farmer-orders");
}

$orderActions = [
    'accept_order'    => 'accepted',
    'decline_order'   => 'declined',
    'mark_ready'      => 'ready',
    'mark_completed'  => 'completed'
];

$newStatus = null;
foreach ($orderActions as $button => $status) {
    if (isset($_POST[$button])) {
        $newStatus = $status;
        break;
    }
}

if ($newStatus === null) {
    redirect("farmer-orders");
}

$validFrom = [
    'accepted'  => ['placed'],
    'declined'  => ['placed'],
    'ready'     => ['accepted'],
    'completed' => ['accepted', 'ready']
];

$exists = selectData($pdo, "SELECT order_status FROM orders WHERE order_id = ? AND farmer_id = ?", [$orderId, $farmerId]);

if (empty($exists)) {
    set_flash("error", "Order not found.");
    redirect("farmer-orders");
}

if (!in_array($exists[0]['order_status'], $validFrom[$newStatus])) {
    set_flash("error", "This action is not allowed for the current order status.");
    redirect("farmer-orders");
}

if ($newStatus === 'declined') {
    try {
        $pdo->beginTransaction();
        $items = selectData($pdo, "SELECT product_id, quantity FROM order_items WHERE order_id = ?", [$orderId]);
        $rst = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
        foreach ($items as $it) $rst->execute([(int)$it['quantity'], (int)$it['product_id']]);
        $pdo->prepare("UPDATE orders SET order_status = 'declined' WHERE order_id = ? AND farmer_id = ?")->execute([$orderId, $farmerId]);
        $pdo->commit();
        set_flash("success", "Order #$orderId declined. Stock restored.");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash("error", "Could not decline order.");
        redirect("farmer-orders");
    }
    notifyOrderStatus($pdo, $orderId, 'declined');
    redirect("farmer-orders");
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ? AND farmer_id = ?");
    $stmt->execute([$newStatus, $orderId, $farmerId]);

    $messages = [
        'accepted'   => "Order #$orderId accepted successfully.",
        'declined'   => "Order #$orderId declined.",
        'ready'      => "Order #$orderId marked as ready for pickup.",
        'completed'  => "Order #$orderId marked as completed."
    ];

    set_flash("success", $messages[$newStatus]);
} catch (Exception $e) {
    set_flash("error", "Could not update order. " . $e->getMessage());
    redirect("farmer-orders");
}

notifyOrderStatus($pdo, $orderId, $newStatus);

redirect("farmer-orders");
