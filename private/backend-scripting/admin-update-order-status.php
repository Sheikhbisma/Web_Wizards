<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect("order-listing");
}

verify_csrf();
validateAdmin();
$orderId = (int)($_POST['order_id'] ?? 0);

if ($orderId <= 0) {
    set_flash("error", "Invalid order.");
    redirect("order-listing");
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
    redirect("order-listing");
}

$validFrom = [
    'accepted'  => ['placed'],
    'declined'  => ['placed'],
    'ready'     => ['accepted'],
    'completed' => ['accepted', 'ready']
];

$exists = selectData($pdo, "SELECT order_status FROM orders WHERE order_id = ?", [$orderId]);

if (empty($exists)) {
    set_flash("error", "Order not found.");
    redirect("order-listing");
}

if (!in_array($exists[0]['order_status'], $validFrom[$newStatus])) {
    set_flash("error", "This action is not allowed for the current order status.");
    redirect("order-listing");
}

if ($newStatus === 'declined') {
    try {
        $pdo->beginTransaction();
        $items = selectData($pdo, "SELECT product_id, quantity FROM order_items WHERE order_id = ?", [$orderId]);
        $rst = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
        foreach ($items as $it) $rst->execute([(int)$it['quantity'], (int)$it['product_id']]);
        $pdo->prepare("UPDATE orders SET order_status = 'declined' WHERE order_id = ?")->execute([$orderId]);
        $pdo->commit();
        set_flash("success", "Order #$orderId declined by admin. Stock restored.");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash("error", "Could not decline order.");
        redirect("order-listing");
    }
    notifyOrderStatus($pdo, $orderId, 'declined', 'admin');
    redirect("order-listing");
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->execute([$newStatus, $orderId]);

    $messages = [
        'accepted'   => "Order #$orderId accepted successfully.",
        'declined'   => "Order #$orderId declined.",
        'ready'      => "Order #$orderId marked as ready for pickup.",
        'completed'  => "Order #$orderId marked as completed."
    ];

    set_flash("success", $messages[$newStatus]);
} catch (Exception $e) {
    set_flash("error", "Could not update order. " . $e->getMessage());
    redirect("order-listing");
}

notifyOrderStatus($pdo, $orderId, $newStatus, 'admin');

redirect("order-listing");