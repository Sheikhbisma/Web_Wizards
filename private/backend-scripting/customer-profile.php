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
$uid = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'info') {
    $fullName = trim($_POST['full_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $market = (int)($_POST['preferred_market_id'] ?? 0);
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if ($fullName === '' || $contact === '') ajaxOut(false, ['error' => 'Name and phone are required.']);
    try {
        $pdo->prepare("UPDATE users SET contact = ? WHERE id = ?")->execute([$contact, $uid]);
        $pdo->prepare("UPDATE customers SET full_name = ?, preferred_market_id = ?, city = ?, address = ? WHERE customer_id = ?")
            ->execute([$fullName, $market ?: null, $city ?: null, $address ?: null, $cid]);
        $_SESSION['username'] = $fullName;
        ajaxOut(true, ['msg' => 'Profile updated.', 'reload' => true]);
    } catch (Exception $e) {
        ajaxOut(false, ['error' => 'Could not update profile.']);
    }
}

if ($action === 'photo') {
    if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        ajaxOut(false, ['error' => 'Please choose an image to upload.']);
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
    finfo_close($finfo);
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!in_array($mime, $allowed, true)) ajaxOut(false, ['error' => 'Only JPG, PNG or WebP images are allowed.']);
    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) ajaxOut(false, ['error' => 'Image must be under 2MB.']);

    $dir = __DIR__ . '/../../public/Uploads/img/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $ext = $extMap[$mime];
    $name = 'customer_' . bin2hex(random_bytes(10)) . '.' . $ext;
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $name)) {
        ajaxOut(false, ['error' => 'Could not save the image.']);
    }
    try {
        $pdo->prepare("UPDATE customers SET profile_image = ? WHERE customer_id = ?")->execute([$name, $cid]);
        ajaxOut(true, ['msg' => 'Profile photo updated.', 'reload' => true]);
    } catch (Exception $e) {
        ajaxOut(false, ['error' => 'Could not save photo.']);
    }
}

if ($action === 'password') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($new) < 8) ajaxOut(false, ['error' => 'New password must be at least 8 characters.']);
    if ($new !== $confirm) ajaxOut(false, ['error' => 'New passwords do not match.']);
    $rows = selectData($pdo, "SELECT password FROM users WHERE id = ?", [$uid]);
    if (empty($rows) || !password_verify($current, $rows[0]['password'])) {
        ajaxOut(false, ['error' => 'Current password is incorrect.']);
    }
    try {
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
        ajaxOut(true, ['msg' => 'Password updated successfully.']);
    } catch (Exception $e) {
        ajaxOut(false, ['error' => 'Could not update password.']);
    }
}

ajaxOut(false, ['error' => 'Invalid profile action.']);