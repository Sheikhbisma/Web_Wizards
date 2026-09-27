<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";
require_once "../config/customer.php";

header('Content-Type: application/json');
verify_csrf_ajax();

if (empty($_SESSION['loggedIn'])) ajaxOut(false, ['login' => true, 'error' => 'Please login.']);
$uid = (int)$_SESSION['user_id'];

$action = $_POST['action'] ?? '';
if ($action === 'readall') {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$uid]);
    ajaxOut(true, []);
}
if ($action === 'read') {
    $nid = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?")->execute([$nid, $uid]);
    ajaxOut(true, ['count' => notifCount($pdo, $uid)]);
}
ajaxOut(false, ['error' => 'Invalid action.']);