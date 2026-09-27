<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if (isset($_POST['save_profile']) || isset($_POST['update_profile'])) {
    verify_csrf();

    $userId = $_SESSION['user_id'];
    $contact = $_POST['contact'];
    $stallName = $_POST['stall_name'];
    $description = $_POST['description'];
    $address = $_POST['address'];
    $longitude = $_POST['longitude'];
    $latitude = $_POST['latitude'];
    $contactPerson = $_POST['contact_person'];
    $pickupStart =$_POST['pickup_start'];
    $pickupEnd =$_POST['pickup_end'];
    $cutoffTime =$_POST['cutoff_time'];

    $existingProfile = selectData($pdo, "SELECT profile_image FROM farmers WHERE user_id = ?", [$userId]);
    $dbImageName = !empty($existingProfile) ? $existingProfile[0]['profile_image'] : null;

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $fileName = $_FILES['profile_image']['name'];
        $fileTmpName = $_FILES['profile_image']['tmp_name'];
        $fileSize = $_FILES['profile_image']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($fileExt, ["jpg", "png", "webp", "jpeg"]) && $fileSize < 5000000) {
            $uniqueFileName = 'farmer_' . uniqid('', true) . '.' . $fileExt;
            $uploadFileDir = __DIR__ . '/../public/Uploads/';
            
            if (!is_dir($uploadFileDir)) { mkdir($uploadFileDir, 0755, true); }
            
            if (move_uploaded_file($fileTmpName, $uploadFileDir . $uniqueFileName)) {
                $dbImageName = $uniqueFileName;
            }
        }
    }

    try {
        $pdo->beginTransaction();

        $userStmt = $pdo->prepare("UPDATE users SET contact = ? WHERE id = ?");
        $userStmt->execute([$contact, $userId]);

        $checkStmt = $pdo->prepare("SELECT farmer_id FROM farmers WHERE user_id = ?");
        $checkStmt->execute([$userId]);
        $rowExists = $checkStmt->fetch();

        if ($rowExists) {
            $farmerStmt = $pdo->prepare("UPDATE farmers SET stall_name = ?, description = ?, address = ?, longitude = ?, latitude = ?, contact_person = ?, profile_image = ?, pickup_start = ?,pickup_end = ? , cutoff_time = ? WHERE user_id = ?");
            $farmerStmt->execute([$stallName, $description, $address, $longitude, $latitude, $contactPerson, $dbImageName,$pickupStart, $pickupEnd,$cutoffTime,$userId]);
            
            $msg = "Profile Updated Successfully!";
        } else {
            $farmerStmt = $pdo->prepare("INSERT INTO farmers (user_id, stall_name, description, address, longitude, latitude, contact_person, profile_image,pickup_start,pickup_end,cutoff_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $farmerStmt->execute([$userId, $stallName, $description, $address, $longitude, $latitude, $contactPerson, $dbImageName,$pickupStart, $pickupEnd,$cutoffTime]);
            
            $msg = "Profile Created Successfully!";
        }

        $pdo->commit();

        set_flash("success", $msg);
        unset($_SESSION['old_input']);
        redirect("farmer-dashboard");

    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash("error", "Error: " . $e->getMessage());
        $_SESSION['old_input'] = $_POST;
        redirect("add-profile");
    }
}