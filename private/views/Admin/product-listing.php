<?php
validateAdmin();

if (isset($_POST['delete_product'])) {
    verify_csrf();
    $product_id = (int)($_POST['product_id'] ?? 0);

    if (!empty($product_id)) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);

        if ($stmt->rowCount() > 0) {
            set_flash("success", "Product deleted successfully.");
        } else {
            set_flash("error", "Product not found.");
        }
    } else {
        set_flash("error", "Invalid product ID.");
    }
    redirect("product-listing");
}

if (isset($_POST['toggle_product'])) {
    verify_csrf();
    $product_id = (int)($_POST['product_id'] ?? 0);

    if (!empty($product_id)) {
        $row = selectData($pdo, "SELECT is_available FROM products WHERE product_id = ?", [$product_id]);
        if (!empty($row)) {
            $newVal = $row[0]['is_available'] ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE products SET is_available = ? WHERE product_id = ?");
            $stmt->execute([$newVal, $product_id]);
            set_flash("success", $newVal ? "Product made visible again." : "Product hidden from customers.");
        }
    }
    redirect("product-listing");
}

$sql = "SELECT p.product_id, p.name AS product_name, p.price, p.unit, p.stock_quantity, p.is_available, p.is_sold_out,
               c.category_name, f.stall_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN farmers f ON p.farmer_id = f.farmer_id
        ORDER BY p.product_id DESC";
$products = selectData($pdo, $sql);

include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap">
    <div class="a-page-head">
        <div>
            <h2><i class="bi bi-box-seam"></i> Manage Products</h2>
            <p>Monitor listings, stock levels and storefront visibility.</p>
        </div>
        <div class="a-actions">
            <span class="a-chip"><i class="bi bi-basket"></i> <?php echo count($products); ?> products</span>
            <span class="a-chip"><i class="bi bi-eye"></i> <?php echo count(array_filter($products, function ($p) { return (int)$p['is_available'] === 1; })); ?> visible</span>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="a-card">
        <div class="a-card-head">
            <h5><i class="bi bi-list-ul"></i> All Products</h5>
            <a href="index.php?page=admin-categories" class="a-btn ghost sm"><i class="bi bi-tags"></i> Categories</a>
        </div>
        <div class="a-card-body flush">
            <?php if (empty($products)): ?>
                <div class="a-empty"><i class="bi bi-basket"></i>No products found.</div>
            <?php else: ?>
                <div class="a-entity-grid product-grid">
                    <?php foreach ($products as $row): ?>
                        <article class="a-entity-card product-card">
                            <div class="a-entity-card-head">
                                <span class="a-entity-icon"><i class="bi bi-box-seam"></i></span>
                                <?php echo $row['is_available'] ? '<span class="a-badge green">Visible</span>' : '<span class="a-badge grey">Hidden</span>'; ?>
                            </div>
                            <div class="product-card-title">
                                <span class="a-sub">Product #<?php echo (int)$row['product_id']; ?></span>
                                <h6><?php echo sanitize_output($row['product_name']); ?></h6>
                                <?php if ($row['is_sold_out']): ?><span class="a-badge red">Sold out</span><?php endif; ?>
                            </div>
                            <div class="a-entity-details">
                                <div><span>Farmer</span><strong><?php echo sanitize_output($row['stall_name'] ?? 'N/A'); ?></strong></div>
                                <div><span>Category</span><strong><?php echo sanitize_output($row['category_name'] ?? 'Uncategorized'); ?></strong></div>
                                <div><span>Price</span><strong>Rs <?php echo number_format((float)$row['price'], 2); ?> <small>/ <?php echo sanitize_output($row['unit'] ?? 'unit'); ?></small></strong></div>
                                <div><span>Stock</span><strong><?php echo (int)$row['stock_quantity']; ?></strong></div>
                            </div>
                            <div class="a-entity-actions">
                                <form action="" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo (int)$row['product_id']; ?>">
                                    <button type="submit" name="toggle_product" class="a-btn ghost sm"><?php echo $row['is_available'] ? 'Hide listing' : 'Show listing'; ?></button>
                                </form>
                                <form action="" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo (int)$row['product_id']; ?>">
                                    <button type="submit" name="delete_product" class="a-btn danger-soft sm" onclick="return confirm('Delete this product?');">Delete</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/admin-footer.php'; ?>