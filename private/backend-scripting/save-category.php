<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect("admin-categories");
}

verify_csrf();
validateAdmin();

$categoryId = (int)($_POST['category_id'] ?? 0);

// ---- ADD / UPDATE ----
if (isset($_POST['save_category'])) {
    $name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        set_flash("error", "Category name is required.");
        $_SESSION['old_input'] = $_POST;
        redirect("admin-categories");
    }

    try {
        $dupes = selectData($pdo, "SELECT category_id FROM categories WHERE category_name = ? AND category_id != ?", [$name, $categoryId]);
        if (!empty($dupes)) {
            set_flash("error", "A category with this name already exists.");
            $_SESSION['old_input'] = $_POST;
            redirect("admin-categories");
        }

        if ($categoryId > 0) {
            $stmt = $pdo->prepare("UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?");
            $stmt->execute([$name, $description, $categoryId]);
            set_flash("success", "Category updated successfully.");
        } else {
            insertData($pdo, "categories", [
                "category_name" => $name,
                "description"   => $description,
                "is_active"     => 1
            ]);
            set_flash("success", "Category added successfully.");
        }

        unset($_SESSION['old_input']);
    } catch (Exception $e) {
        set_flash("error", "Database Error: " . $e->getMessage());
        $_SESSION['old_input'] = $_POST;
    }

    redirect("admin-categories");
}

// ---- TOGGLE ACTIVE ----
if (isset($_POST['toggle_category'])) {
    $row = selectData($pdo, "SELECT is_active FROM categories WHERE category_id = ?", [$categoryId]);
    if (!empty($row)) {
        $newVal = $row[0]['is_active'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE categories SET is_active = ? WHERE category_id = ?");
        $stmt->execute([$newVal, $categoryId]);
        set_flash("success", $newVal ? "Category activated." : "Category deactivated.");
    }
    redirect("admin-categories");
}

// ---- DELETE ----
if (isset($_POST['delete_category'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        set_flash("success", "Category deleted.");
    } catch (Exception $e) {
        set_flash("error", "Could not delete. Category may still be in use.");
    }
    redirect("admin-categories");
}