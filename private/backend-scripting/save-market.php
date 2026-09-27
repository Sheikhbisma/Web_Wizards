<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if (isset($_POST['save_market'])) {
    verify_csrf();

    $marketName   = trim($_POST['market_name'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $latitude     = !empty($_POST['latitude']) ? trim($_POST['latitude']) : null;
    $longitude    = !empty($_POST['longitude']) ? trim($_POST['longitude']) : null;
    $openingTime  = !empty($_POST['opening_time']) ? $_POST['opening_time'] : null;
    $closingTime  = !empty($_POST['closing_time']) ? $_POST['closing_time'] : null;
    $mapProvider  = $_POST['map_provider'] ?? 'OpenStreetMap';
    
    $operatingDays = isset($_POST['days']) ? implode(',', $_POST['days']) : null;

    if (empty($marketName) || empty($address)) {
        set_flash("error", "Market Name and Address are required fields.");
        $_SESSION['old_input'] = $_POST;
        redirect("add-market");
    }

    if (!empty($openingTime) && !empty($closingTime)) {
        if ($openingTime >= $closingTime) {
            set_flash("error", "Opening time must be earlier than the closing time.");
            $_SESSION['old_input'] = $_POST;
            redirect("add-market");
        }
    }

    if ($latitude !== null && ($latitude < -90 || $latitude > 90)) {
        set_flash("error", "Invalid Latitude value. Must be between -90 and 90.");
        $_SESSION['old_input'] = $_POST;
        redirect("add-market");
    }

    if ($longitude !== null && ($longitude < -180 || $longitude > 180)) {
        set_flash("error", "Invalid Longitude value. Must be between -180 and 180.");
        $_SESSION['old_input'] = $_POST;
        redirect("add-market");
    }

    $marketData = [
        'market_name'    => $marketName,
        'address'        => $address,
        'latitude'       => $latitude,
        'longitude'      => $longitude,
        'operating_days' => $operatingDays,
        'opening_time'   => $openingTime,
        'closing_time'   => $closingTime,
        'map_provider'   => $mapProvider,
        'is_active'      => 1
    ];

    try {
        $inserted = insertData($pdo, 'markets', $marketData);

        if ($inserted) {
            set_flash("success", "Market Added Successfully!");
            unset($_SESSION['old_input']);
            redirect("admin-dashboard");
        } else {
            set_flash("error", "Failed to add market.");
            $_SESSION['old_input'] = $_POST;
            redirect("add-market");
        }

    } catch (Exception $e) {
        set_flash("error", "Database Error: " . $e->getMessage());
        $_SESSION['old_input'] = $_POST;
        redirect("add-market");
    }
}