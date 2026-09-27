<?php
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];
$products = selectData($pdo, "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.farmer_id = ? ORDER BY p.product_id DESC", [$farmerId]);
include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-basket"></i> My Products</h2>
            <p>Manage your product stock, pricing and availability.</p>
        </div>
        <div class="f-actions">
            <a href="index.php?page=farmer-stock" class="f-btn outline"><i class="bi bi-calendar-week"></i> Weekly Template</a>
            <a href="farmer-product-form" class="f-btn primary"><i class="bi bi-plus-circle"></i> Add Product</a>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="f-card">
        <div class="f-card-body flush">
            <?php if (empty($products)): ?>
                <div class="f-empty">
                    <i class="bi bi-basket2"></i>
                    No products yet. Add your first item to start selling.
                </div>
            <?php else: ?>
                <div class="f-entity-grid">
                    <?php foreach ($products as $item): ?>
                        <article class="f-entity-card">
                            <div class="f-product-media">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?php echo prodImgSrc($item); ?>" alt="<?php echo sanitize_output($item['name']); ?>" loading="lazy">
                                <?php else: ?>
                                    <span class="f-product-placeholder"><i class="bi bi-image"></i></span>
                                <?php endif; ?>
                                <?php if ($item['is_sold_out']): ?>
                                    <span class="f-badge red">Sold out</span>
                                <?php elseif (!$item['is_available']): ?>
                                    <span class="f-badge grey">Unavailable</span>
                                <?php else: ?>
                                    <span class="f-badge green">Available</span>
                                <?php endif; ?>
                            </div>
                            <div class="f-product-title">
                                <div>
                                    <h5><?php echo sanitize_output($item['name']); ?></h5>
                                    <span class="f-muted"><?php echo sanitize_output($item['category_name'] ?? 'Uncategorized'); ?></span>
                                </div>
                                <strong class="f-product-price">Rs <?php echo number_format((float)$item['price'], 2); ?></strong>
                            </div>
                            <div class="f-product-meta">
                                <span><small>Unit</small><strong><?php echo sanitize_output($item['unit']); ?></strong></span>
                                <span><small>Stock</small><strong><?php echo (int)$item['stock_quantity']; ?></strong></span>
                            </div>
                            <div class="f-product-actions">
                                <a href="index.php?page=farmer-product-form&id=<?php echo (int)$item['product_id']; ?>" class="f-btn outline sm"><i class="bi bi-pencil"></i> Edit</a>
                                <form action="../private/backend-scripting/save-product.php" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
                                    <button type="submit" name="toggle_sold" class="f-btn ghost sm"><i class="bi bi-bag-x"></i> Sold out</button>
                                    <button type="submit" name="toggle_unavailable" class="f-btn ghost sm"><i class="bi bi-eye-slash"></i> Hide</button>
                                    <button type="submit" name="delete_product" class="f-btn danger sm" aria-label="Delete <?php echo sanitize_output($item['name']); ?>" onclick="return confirm('Delete this product?');"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>