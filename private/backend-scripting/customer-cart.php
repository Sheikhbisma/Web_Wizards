<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";
require_once "../config/customer.php";

verify_csrf_ajax();

$action = $_POST['action'] ?? '';
$cart = $_SESSION['cart'] ?? [];

if ($action === 'add') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $rows = selectData($pdo, "SELECT product_id, stock_quantity, is_available, is_sold_out FROM products WHERE product_id = ?", [$pid]);
    if (empty($rows)) {
        ajaxOut(false, ['error' => 'Product not found.']);
    }
    $p = $rows[0];
    if (!$p['is_available'] || $p['is_sold_out'] || (int)$p['stock_quantity'] <= 0) {
        ajaxOut(false, ['error' => 'This product is currently sold out.']);
    }
    $max = (int)$p['stock_quantity'];
    $prev = (int)($cart[$pid] ?? 0);
    $next = min($prev + $qty, $max);
    $cart[$pid] = $next;
    $_SESSION['cart'] = $cart;
    ajaxOut(true, ['count' => array_sum($cart), 'msg' => $next > $prev ? 'Added to cart' : 'Stock limit reached', 'qty' => $next]);
}

if ($action === 'update') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $rows = selectData($pdo, "SELECT stock_quantity, price, is_available, is_sold_out FROM products WHERE product_id = ?", [$pid]);
    if (empty($rows) || !$rows[0]['is_available'] || $rows[0]['is_sold_out'] || (int)$rows[0]['stock_quantity'] <= 0) {
        unset($cart[$pid]);
        $_SESSION['cart'] = $cart;
        ajaxOut(true, ['count' => array_sum($cart), 'purged' => true, 'empty' => empty($cart), 'price' => 0]);
    }
    $max = (int)$rows[0]['stock_quantity'];
    if ($qty > $max) $qty = $max;
    $price = (float)$rows[0]['price'];
    if ($qty <= 0) {
        unset($cart[$pid]);
    } else {
        $cart[$pid] = $qty;
    }
    $_SESSION['cart'] = $cart;
    ajaxOut(true, ['count' => array_sum($cart), 'price' => $price]);
}

if ($action === 'remove') {
    $pid = (int)($_POST['product_id'] ?? 0);
    if (isset($cart[$pid])) unset($cart[$pid]);
    $_SESSION['cart'] = $cart;
    ajaxOut(true, ['count' => array_sum($cart), 'empty' => empty($cart)]);
}

ajaxOut(false, ['error' => 'Invalid cart action.']);