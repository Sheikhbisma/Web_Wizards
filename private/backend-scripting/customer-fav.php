<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";
require_once "../config/customer.php";

verify_csrf_ajax();

if (empty($_SESSION['loggedIn']) || ($_SESSION['role'] ?? '') !== 'customer') {
    ajaxOut(false, ['login' => true, 'error' => 'Please login as a customer to use favorites.']);
}
$custRows = selectData($pdo, "SELECT customer_id FROM customers WHERE user_id = ?", [$_SESSION['user_id']]);
if (empty($custRows)) {
    ajaxOut(false, ['error' => 'Customer profile not found.']);
}
$cid = (int)$custRows[0]['customer_id'];
$favType = $_POST['fav_type'] ?? '';
$id = (int)($_POST['id'] ?? 0);

if ($favType === 'product') {
    if (!empty(selectData($pdo, "SELECT favorite_id FROM favorites WHERE customer_id = ? AND product_id = ?", [$cid, $id]))) {
        $pdo->prepare("DELETE FROM favorites WHERE customer_id = ? AND product_id = ?")->execute([$cid, $id]);
        ajaxOut(true, ['removed' => true, 'count' => favCount($pdo, $cid)]);
    }
    $pdo->prepare("INSERT INTO favorites (customer_id, product_id) VALUES (?, ?)")->execute([$cid, $id]);
    ajaxOut(true, ['removed' => false, 'count' => favCount($pdo, $cid)]);
}

if ($favType === 'farmer') {
    if (!empty(selectData($pdo, "SELECT favorite_id FROM favorites WHERE customer_id = ? AND farmer_id = ?", [$cid, $id]))) {
        $pdo->prepare("DELETE FROM favorites WHERE customer_id = ? AND farmer_id = ?")->execute([$cid, $id]);
        ajaxOut(true, ['removed' => true, 'count' => favCount($pdo, $cid)]);
    }
    $pdo->prepare("INSERT INTO favorites (customer_id, farmer_id) VALUES (?, ?)")->execute([$cid, $id]);
    ajaxOut(true, ['removed' => false, 'count' => favCount($pdo, $cid)]);
}

if ($favType === 'market') {
    if (empty(selectData($pdo, "SELECT market_id FROM markets WHERE market_id = ? AND is_active = 1", [$id]))) {
        ajaxOut(false, ['error' => 'Market not found.']);
    }
    if (!empty(selectData($pdo, "SELECT favorite_id FROM favorites WHERE customer_id = ? AND market_id = ?", [$cid, $id]))) {
        $pdo->prepare("DELETE FROM favorites WHERE customer_id = ? AND market_id = ?")->execute([$cid, $id]);
        ajaxOut(true, ['removed' => true, 'count' => favCount($pdo, $cid)]);
    }
    $pdo->prepare("INSERT INTO favorites (customer_id, market_id) VALUES (?, ?)")->execute([$cid, $id]);
    ajaxOut(true, ['removed' => false, 'count' => favCount($pdo, $cid)]);
}

ajaxOut(false, ['error' => 'Invalid favorite action.']);