<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";
require_once "../config/customer.php";

header('Content-Type: application/json');
verify_csrf_ajax();

if (empty($_SESSION['loggedIn']) || ($_SESSION['role'] ?? '') !== 'customer') {
    ajaxOut(false, ['login' => true, 'error' => 'Please login as a customer.']);
}
$custRows = selectData($pdo, "SELECT customer_id FROM customers WHERE user_id = ?", [$_SESSION['user_id']]);
if (empty($custRows)) ajaxOut(false, ['error' => 'Customer profile not found.']);
$cid = (int)$custRows[0]['customer_id'];

$action = $_POST['action'] ?? '';
if ($action !== 'add') ajaxOut(false, ['error' => 'Invalid action.']);

$orderId = (int)($_POST['order_id'] ?? 0);
$productId = (int)($_POST['product_id'] ?? 0);
$farmerId = (int)($_POST['farmer_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($rating < 1 || $rating > 5) ajaxOut(false, ['error' => 'Please choose a rating from 1 to 5.']);
if (!$orderId || !$productId || !$farmerId) ajaxOut(false, ['error' => 'Missing order details.']);

$order = selectData($pdo, "SELECT order_id FROM orders WHERE order_id = ? AND customer_id = ? AND order_status = 'completed'", [$orderId, $cid]);
if (empty($order)) ajaxOut(false, ['error' => 'You can only review items from completed orders.']);

$inOrder = selectData($pdo, "SELECT order_item_id FROM order_items WHERE order_id = ? AND product_id = ?", [$orderId, $productId]);
if (empty($inOrder)) ajaxOut(false, ['error' => 'Item not part of this order.']);

if (hasReview($pdo, $cid, $orderId, $productId)) ajaxOut(false, ['error' => 'You have already reviewed this product.']);

try {
    $pdo->beginTransaction();
    $pdo->prepare("INSERT INTO reviews (order_id, product_id, farmer_id, customer_id, rating, comment) VALUES (?, ?, ?, ?, ?, ?)")
        ->execute([$orderId, $productId, $farmerId, $cid, $rating, $comment ?: null]);
    $pr = selectData($pdo, "SELECT AVG(rating) AS a FROM reviews WHERE product_id = ?", [$productId])[0];
    $pdo->prepare("UPDATE products SET avg_rating = ? WHERE product_id = ?")->execute([round((float)$pr['a'], 2), $productId]);
    $fr = selectData($pdo, "SELECT AVG(rating) AS a FROM reviews WHERE farmer_id = ?", [$farmerId])[0];
    $pdo->prepare("UPDATE farmers SET avg_rating = ? WHERE farmer_id = ?")->execute([round((float)$fr['a'], 2), $farmerId]);
    $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'New Review', ?, 'review')")
        ->execute([$_SESSION['user_id'], 'Thanks for reviewing ' . 'your order' . '! Your feedback helps farmers improve.']);
    $pdo->commit();
    ajaxOut(true, ['msg' => 'Review submitted. Thank you!']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    ajaxOut(false, ['error' => 'Could not submit review.']);
}