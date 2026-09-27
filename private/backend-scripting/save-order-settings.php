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

if (isset($_POST['save_cutoff'])) {
    $cutoffTime  = trim($_POST['cutoff_time'] ?? '');
    $pickupStart = trim($_POST['pickup_start'] ?? '');
    $pickupEnd   = trim($_POST['pickup_end'] ?? '');

    if (!empty($pickupStart) && !empty($pickupEnd) && $pickupStart >= $pickupEnd) {
        set_flash("error", "Pickup end time must be later than pickup start time.");
        redirect("farmer-orders");
    }

    try {
        $stmt = $pdo->prepare("UPDATE farmers SET cutoff_time = ?, pickup_start = ?, pickup_end = ? WHERE farmer_id = ?");
        $stmt->execute([
            !empty($cutoffTime) ? $cutoffTime : null,
            !empty($pickupStart) ? $pickupStart : null,
            !empty($pickupEnd) ? $pickupEnd : null,
            $farmerId
        ]);
        set_flash("success", "Order settings saved successfully.");
    } catch (Exception $e) {
        set_flash("error", "Could not save settings. " . $e->getMessage());
    }
    redirect("farmer-orders");
}

// ---- Add a pickup slot ----
if (isset($_POST['add_slot'])) {
    $dayOfWeek = trim($_POST['slot_day'] ?? '');
    $slotStart = trim($_POST['slot_start'] ?? '');
    $slotEnd   = trim($_POST['slot_end'] ?? '');

    if (empty($dayOfWeek) || empty($slotStart) || empty($slotEnd)) {
        set_flash("error", "Please fill day, start and end time for the pickup slot.");
        redirect("farmer-orders");
    }
    if ($slotStart >= $slotEnd) {
        set_flash("error", "Slot end time must be later than slot start time.");
        redirect("farmer-orders");
    }

    try {
        insertData($pdo, "pickup_slots", [
            "farmer_id"   => $farmerId,
            "day_of_week" => $dayOfWeek,
            "slot_start"  => $slotStart,
            "slot_end"    => $slotEnd,
            "is_active"   => 1
        ]);
        set_flash("success", "Pickup slot added successfully.");
    } catch (Exception $e) {
        set_flash("error", "Could not add pickup slot. " . $e->getMessage());
    }
    redirect("farmer-orders");
}

// ---- Toggle a pickup slot on/off ----
if (isset($_POST['toggle_slot'])) {
    $slotId = (int)($_POST['slot_id'] ?? 0);
    $row = selectData($pdo, "SELECT is_active FROM pickup_slots WHERE slot_id = ? AND farmer_id = ?", [$slotId, $farmerId]);

    if (!empty($row)) {
        $newStatus = $row[0]['is_active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE pickup_slots SET is_active = ? WHERE slot_id = ? AND farmer_id = ?");
        $stmt->execute([$newStatus, $slotId, $farmerId]);
        set_flash("success", $newStatus ? "Pickup slot activated." : "Pickup slot deactivated.");
    } else {
        set_flash("error", "Pickup slot not found.");
    }
    redirect("farmer-orders");
}

// ---- Delete a pickup slot ----
if (isset($_POST['delete_slot'])) {
    $slotId = (int)($_POST['slot_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM pickup_slots WHERE slot_id = ? AND farmer_id = ?");
    $stmt->execute([$slotId, $farmerId]);
    set_flash("success", "Pickup slot deleted.");
    redirect("farmer-orders");
}

redirect("farmer-orders");