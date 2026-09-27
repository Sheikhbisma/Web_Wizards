<?php
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];
$categories = selectData($pdo, "SELECT * FROM categories WHERE is_active = 1 ORDER BY category_name ASC");
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
$product = [];
$isEdit = false;

if (!empty($_GET['id'])) {
    $found = selectData($pdo, "SELECT * FROM products WHERE product_id = ? AND farmer_id = ?", [$_GET['id'], $farmerId]);
    if (!empty($found)) {
        $product = $found[0];
        $isEdit = true;
    }
}

include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-box-seam"></i> <?php echo $isEdit ? 'Edit Product' : 'Add Product'; ?></h2>
            <p><?php echo $isEdit ? 'Update the details of your product.' : 'Add a new product to your stall.'; ?></p>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="f-card">
                <div class="f-card-head">
                    <h5><i class="bi bi-file-earmark-plus"></i> <?php echo $isEdit ? 'Product Details' : 'New Product'; ?></h5>
                </div>
                <div class="f-card-body">
                    <form action="../private/backend-scripting/save-product.php" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
                        <?php endif; ?>

                        <div class="f-field">
                            <label for="f-name">Product Name</label>
                            <input type="text" id="f-name" name="name" class="form-control" required value="<?php echo sanitize_output($old['name'] ?? $product['name'] ?? ''); ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="f-field">
                                    <label for="f-category">Category</label>
                                    <select id="f-category" name="category_id" class="form-select" required>
                                        <option value="">Select category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo (int)$cat['category_id']; ?>" <?php echo (($old['category_id'] ?? $product['category_id'] ?? '') == $cat['category_id']) ? 'selected' : ''; ?>>
                                                <?php echo sanitize_output($cat['category_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="f-field">
                                    <label for="f-price">Price (Rs)</label>
                                    <input type="number" id="f-price" step="0.01" name="price" class="form-control" required value="<?php echo sanitize_output($old['price'] ?? $product['price'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="f-field">
                                    <label for="f-unit">Unit</label>
                                    <input type="text" id="f-unit" name="unit" class="form-control" placeholder="kg, dozen, piece" required value="<?php echo sanitize_output($old['unit'] ?? $product['unit'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="f-field">
                            <label for="f-stock">Quantity Available</label>
                            <input type="number" id="f-stock" name="stock_quantity" class="form-control" required value="<?php echo sanitize_output($old['stock_quantity'] ?? $product['stock_quantity'] ?? '0'); ?>">
                        </div>

                        <div class="f-field">
                            <label for="f-desc">Description</label>
                            <textarea id="f-desc" name="description" class="form-control" rows="3"><?php echo sanitize_output($old['description'] ?? $product['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="f-field">
                            <label for="f-image">Product Image</label>
                            <input type="file" id="f-image" name="image" class="form-control">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?php echo prodImgSrc($product); ?>" width="70" class="mt-3 rounded d-block" style="border-radius:12px !important;">
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-4 mb-4 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_available" id="is_available" value="1" <?php echo (($old['is_available'] ?? $product['is_available'] ?? 1) == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_available">Available for sale</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_sold_out" id="is_sold_out" value="1" <?php echo (($old['is_sold_out'] ?? $product['is_sold_out'] ?? 0) == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_sold_out">Sold out</label>
                            </div>
                        </div>

                        <button type="submit" name="save_product" class="f-btn primary block">
                            <i class="bi bi-check-circle"></i> <?php echo $isEdit ? 'Update Product' : 'Save Product'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>