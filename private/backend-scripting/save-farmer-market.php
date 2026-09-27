<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if (isset($_POST['save_farmer_market'])) {
    verify_csrf();

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT farmer_id FROM farmers WHERE user_id = ?");
$stmt->execute([$userId]);
$farmer = $stmt->fetch();

if (!$farmer) {
    set_flash("error", "Please complete your farmer profile first.");
    $_SESSION['old_input'] = $_POST;
    redirect("add-farmer-profile"); 
}

$farmerId = $farmer['farmer_id'];
    $mfId         = $_POST['mf_id'] ?? null;
    $marketId     = $_POST['market_id'] ?? null;
    $stallNumber  = trim($_POST['stall_number'] ?? '');
    $dayOfWeek    = $_POST['day_of_week'] ?? '';
    $pickupStart  = $_POST['pickup_start'] ?? '';
    $pickupEnd    = $_POST['pickup_end'] ?? '';

    if (empty($marketId) || empty($stallNumber) || empty($dayOfWeek) || empty($pickupStart) || empty($pickupEnd)) {
        set_flash("error", "All required fields must be filled.");
        $_SESSION['old_input'] = $_POST;
        redirect("add-farmer-market" . (!empty($mfId) ? "?edit_id=" . $mfId : ""));
    }

    if (!empty($pickupStart) && !empty($pickupEnd)) {
        if ($pickupStart >= $pickupEnd) {
            set_flash("error", "Pickup start time must be earlier than the pickup end time.");
            $_SESSION['old_input'] = $_POST;
            redirect("add-farmer-market" . (!empty($mfId) ? "?edit_id=" . $mfId : ""));
        }
    }

    try {
        if (!empty($mfId)) {
            $existing = selectData($pdo, "SELECT mf_id FROM market_farmer WHERE mf_id = ? AND farmer_id = ?", [$mfId, $farmerId]);
            if (!empty($existing)) {
                $stmt = $pdo->prepare("UPDATE market_farmer SET market_id = ?, stall_number = ?, pickup_start = ?, pickup_end = ?, day_of_week = ? WHERE mf_id = ? AND farmer_id = ?");
                $updated = $stmt->execute([$marketId, $stallNumber, $pickupStart, $pickupEnd, $dayOfWeek, $mfId, $farmerId]);

                if ($updated) {
                    set_flash("success", "Market Assignment Updated Successfully!");
                    unset($_SESSION['old_input']);
                    redirect("farmer-dashboard");
                } else {
                    set_flash("error", "Failed to update market assignment.");
                    $_SESSION['old_input'] = $_POST;
                    redirect("add-farmer-market?edit_id=" . $mfId);
                }
            }
        }

      $stmt = $pdo->prepare("INSERT INTO market_farmer (market_id, farmer_id, stall_number, pickup_start, pickup_end, day_of_week) VALUES (?, ?, ?, ?, ?, ?)");
        $inserted = $stmt->execute([$marketId, $farmerId, $stallNumber, $pickupStart, $pickupEnd, $dayOfWeek]);

        if ($inserted) {
            set_flash("success", "Market Assigned Successfully!");
            unset($_SESSION['old_input']);
            redirect("farmer-dashboard");
        } else {
            set_flash("error", "Failed to assign market.");
            $_SESSION['old_input'] = $_POST;
            redirect("add-farmer-market");
        }

    } catch (Exception $e) {
        set_flash("error", "Database Error: " . $e->getMessage());
        $_SESSION['old_input'] = $_POST;
        redirect("add-farmer-market" . (!empty($mfId) ? "?edit_id=" . $mfId : ""));
    }
}