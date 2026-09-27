<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();
    $farmer = validateFarmer($pdo);
    $farmerId = $farmer['farmer_id'];

    if (isset($_POST['save_template'])) {
        $productId       = (int)$_POST['product_id'];
        $dayOfWeek       = trim($_POST['day_of_week']);
        $defaultQuantity = (float)$_POST['default_quantity'];

        // Validation
        if ($productId <= 0 || empty($dayOfWeek) || $defaultQuantity < 0) {
            set_flash('error', 'Please fill in all required fields correctly.');
            redirect('farmer-stock');
            exit();
        }

        $productExists = selectData($pdo, "SELECT product_id FROM products WHERE product_id = ? AND farmer_id = ?", [$productId, $farmerId]);
        if (empty($productExists)) {
            set_flash('error', 'Product not found.');
            redirect('farmer-stock');
            exit();
        }

        // Same product + day already has a template? Then update it
        $existing = selectData($pdo, "SELECT template_id FROM weekly_stock_template WHERE farmer_id = ? AND product_id = ? AND day_of_week = ?", [$farmerId, $productId, $dayOfWeek]);
        if (!empty($existing)) {
            $stmt = $pdo->prepare("UPDATE weekly_stock_template SET default_quantity = ?, is_active = 1 WHERE template_id = ?");
            $stmt->execute([$defaultQuantity, $existing[0]['template_id']]);
            set_flash('success', 'Weekly stock template updated successfully!');
        } else {
            insertData($pdo, 'weekly_stock_template', [
                'farmer_id'        => $farmerId,
                'product_id'       => $productId,
                'day_of_week'      => $dayOfWeek,
                'default_quantity' => $defaultQuantity,
                'is_active'        => 1
            ]);
            set_flash('success', 'Weekly stock template added successfully!');
        }

        redirect('farmer-stock');
        exit();
    }

    // --- 2. APPLY TEMPLATE (copy template qty back into current stock) ---
    // A product that sold out last week carries is_sold_out = 1, and every
    // storefront query filters that flag out. So restoring stock_quantity
    // alone would leave the item invisible. is_sold_out must be cleared too or
    // "apply" looks like it did nothing. is_available is left alone because
    // that is the farmer's own hide switch, not a stock signal.
    if (isset($_POST['apply_template']) || isset($_POST['apply_one_template'])) {

        $applyOne = isset($_POST['apply_one_template']) ? (int)$_POST['apply_one_template'] : 0;

        if ($applyOne > 0) {
            $one = selectData($pdo,
                "SELECT product_id, default_quantity FROM weekly_stock_template
                 WHERE farmer_id = ? AND template_id = ? AND is_active = 1",
                [$farmerId, $applyOne]);

            if (empty($one)) {
                set_flash('error', 'Template not found or switched off.');
                redirect('farmer-stock');
                exit();
            }

            $qty = (int)$one[0]['default_quantity'];
            $stmt = $pdo->prepare("UPDATE products
                SET stock_quantity = ?, is_sold_out = 0
                WHERE product_id = ? AND farmer_id = ?");
            $stmt->execute([$qty, (int)$one[0]['product_id'], $farmerId]);

            set_flash('success', $stmt->rowCount()
                ? "Stock reset to $qty from template."
                : 'Template applied, but that product no longer exists.');
            redirect('farmer-stock');
            exit();
        }

        $activeTemplates = selectData($pdo,
            "SELECT w.product_id, w.default_quantity
             FROM weekly_stock_template w
             INNER JOIN products p ON p.product_id = w.product_id
             WHERE w.farmer_id = ? AND w.is_active = 1",
            [$farmerId]);

        if (empty($activeTemplates)) {
            set_flash('error', 'No active weekly template found to apply.');
            redirect('farmer-stock');
            exit();
        }

        $updated = 0;
        $stmt = $pdo->prepare("UPDATE products
            SET stock_quantity = ?, is_sold_out = 0
            WHERE product_id = ? AND farmer_id = ?");
        foreach ($activeTemplates as $template) {
            $stmt->execute([(int)$template['default_quantity'], (int)$template['product_id'], $farmerId]);
            $updated += $stmt->rowCount();
        }

        set_flash('success', "Weekly stock restored from template ($updated product(s) reset).");
        redirect('farmer-stock');
        exit();
    }

    // --- 3. TOGGLE TEMPLATE (On/Off) ---
    if (isset($_POST['toggle_template'])) {
        $templateId = (int)$_POST['template_id'];

        if ($templateId > 0) {
            $template = selectData($pdo, "SELECT is_active FROM weekly_stock_template WHERE template_id = ? AND farmer_id = ?", [$templateId, $farmerId]);

            if (!empty($template)) {
                $newStatus = $template[0]['is_active'] ? 0 : 1;
                $stmt = $pdo->prepare("UPDATE weekly_stock_template SET is_active = ? WHERE template_id = ? AND farmer_id = ?");
                $stmt->execute([$newStatus, $templateId, $farmerId]);

                set_flash('success', 'Template status updated successfully!');
            } else {
                set_flash('error', 'Template not found.');
            }
        } else {
            set_flash('error', 'Invalid template ID.');
        }

        redirect('farmer-stock');
        exit();
    }

    // --- 4. DELETE TEMPLATE ---
    if (isset($_POST['delete_template'])) {
        $templateId = (int)$_POST['template_id'];

        if ($templateId > 0) {
            $stmt = $pdo->prepare("DELETE FROM weekly_stock_template WHERE template_id = ? AND farmer_id = ?");
            $stmt->execute([$templateId, $farmerId]);

            set_flash('success', 'Weekly stock template deleted successfully!');
        } else {
            set_flash('error', 'Invalid template ID.');
        }

        redirect('farmer-stock');
        exit();
    }
}

redirect("farmer-stock");