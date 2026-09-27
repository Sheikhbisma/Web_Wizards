<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect("farmer-reviews");
}

verify_csrf();
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];

if (isset($_POST['save_response'])) {
    $reviewId   = (int)($_POST['review_id'] ?? 0);
    $response   = trim($_POST['farmer_response'] ?? '');

    if ($reviewId <= 0) {
        set_flash("error", "Invalid review.");
        redirect("farmer-reviews");
    }

    // Review must belong to this farmer (directly or through product)
    $review = selectData($pdo,
        "SELECT r.review_id FROM reviews r
         WHERE r.review_id = ?
           AND (r.farmer_id = ?
                OR (r.farmer_id IS NULL AND r.product_id IN (SELECT product_id FROM products WHERE farmer_id = ?)))",
        [$reviewId, $farmerId, $farmerId]);

    if (empty($review)) {
        set_flash("error", "Review not found.");
        redirect("farmer-reviews");
    }

    if ($response == "") {
        set_flash("error", "Please write a response before saving.");
        redirect("farmer-reviews");
    }

    try {
        $stmt = $pdo->prepare("UPDATE reviews SET farmer_response = ? WHERE review_id = ?");
        $stmt->execute([$response, $reviewId]);
        set_flash("success", "Response posted for the review.");
    } catch (Exception $e) {
        set_flash("error", "Could not save response. " . $e->getMessage());
    }
}

redirect("farmer-reviews");