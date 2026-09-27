<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect("farmer-products");
}

verify_csrf();
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if (isset($_POST['delete_product']) && $productId > 0) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ? AND farmer_id = ?");
    $stmt->execute([$productId, $farmerId]);
    set_flash("success", "Product deleted");
    redirect("farmer-products");
}

if (isset($_POST['toggle_sold']) && $productId > 0) {
    $row = selectData($pdo, "SELECT is_sold_out FROM products WHERE product_id = ? AND farmer_id = ?", [$productId, $farmerId]);
    if (!empty($row)) {
        $newVal = $row[0]['is_sold_out'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE products SET is_sold_out = ? WHERE product_id = ? AND farmer_id = ?");
        $stmt->execute([$newVal, $productId, $farmerId]);
        set_flash("success", $newVal ? "Marked as sold out" : "Marked back in stock");
    }
    redirect("farmer-products");
}

if (isset($_POST['toggle_unavailable']) && $productId > 0) {
    $row = selectData($pdo, "SELECT is_available FROM products WHERE product_id = ? AND farmer_id = ?", [$productId, $farmerId]);
    if (!empty($row)) {
        $newVal = $row[0]['is_available'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE products SET is_available = ? WHERE product_id = ? AND farmer_id = ?");
        $stmt->execute([$newVal, $productId, $farmerId]);
        set_flash("success", $newVal ? "Product is available again" : "Product marked unavailable");
    }
    redirect("farmer-products");
}

if (isset($_POST['save_product'])) {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $price = $_POST['price'] ?? '';
    $unit = trim($_POST['unit'] ?? '');
    $qty = max(0, (int)($_POST['stock_quantity'] ?? 0));
    $description = trim($_POST['description'] ?? '');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    $isSoldOut = isset($_POST['is_sold_out']) ? 1 : 0;

    if ($name == "" || $price === "" || $unit == "" || $categoryId == 0) {
        set_flash("error", "Please fill product name, category, price and unit");
        $_SESSION['old_input'] = $_POST;
        redirect("farmer-product-form");
    }

    $imageName = null;
    if ($productId > 0) {
        $oldRow = selectData($pdo, "SELECT image_url FROM products WHERE product_id = ? AND farmer_id = ?", [$productId, $farmerId]);
        $imageName = !empty($oldRow) ? $oldRow[0]['image_url'] : null;
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($fileExt, ["jpg", "png", "webp", "jpeg"]) && $_FILES['image']['size'] < 5000000) {
            $imageName = 'product_' . uniqid('', true) . '.' . $fileExt;
            // Uploads/img/ is the location prodImg() resolves against, so new
            // uploads must land there or the image 404s on the storefront.
            $uploadFileDir = __DIR__ . '/../../public/Uploads/img/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadFileDir . $imageName);
        }
    }

    if ($productId > 0) {
        $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, unit = ?, stock_quantity = ?, image_url = ?, is_available = ?, is_sold_out = ? WHERE product_id = ? AND farmer_id = ?");
        $stmt->execute([$categoryId, $name, $description, $price, $unit, $qty, $imageName, $isAvailable, $isSoldOut, $productId, $farmerId]);
        set_flash("success", "Product updated");
    } else {
        insertData($pdo, "products", [
            "farmer_id" => $farmerId,
            "category_id" => $categoryId,
            "name" => $name,
            "description" => $description,
            "price" => $price,
            "unit" => $unit,
            "stock_quantity" => $qty,
            "image_url" => $imageName,
            "is_available" => $isAvailable,
            "is_sold_out" => $isSoldOut
        ]);
        set_flash("success", "Product added");
    }

    unset($_SESSION['old_input']);
    redirect("farmer-products");
}

redirect("farmer-products");
