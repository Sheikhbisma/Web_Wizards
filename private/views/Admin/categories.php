<?php
validateAdmin();

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$isEdit = false;
$editData = [];

if (isset($_GET['edit'])) {
    $found = selectData($pdo, "SELECT * FROM categories WHERE category_id = ?", [(int)$_GET['edit']]);
    if (!empty($found)) {
        $isEdit = true;
        $editData = $found[0];
        $old = $editData;
    }
}

$categories = selectData($pdo,
    "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) AS product_count
     FROM categories c
     ORDER BY c.category_name ASC");

include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap">
    <div class="a-page-head">
        <div>
            <h2><i class="bi bi-tags"></i> Categories</h2>
            <p>Organise the catalogue so customers can find produce faster.</p>
        </div>
        <div class="a-actions">
            <span class="a-chip"><i class="bi bi-tags"></i> <?php echo count($categories); ?> categories</span>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="a-form-side narrow">
        <div class="a-card">
            <div class="a-card-head">
                <h5><i class="bi bi-<?php echo $isEdit ? 'pencil-square' : 'plus-circle'; ?>"></i> <?php echo $isEdit ? 'Edit Category' : 'Add Category'; ?></h5>
            </div>
            <div class="a-card-body">
                <form action="../private/backend-scripting/save-category.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="category_id" value="<?php echo (int)$editData['category_id']; ?>">
                    <?php endif; ?>

                    <div class="a-field">
                        <label for="category_name">Category Name</label>
                        <input type="text" id="category_name" name="category_name" class="form-control" value="<?php echo sanitize_output($old['category_name'] ?? ''); ?>" required placeholder="e.g. Vegetables">
                    </div>

                    <div class="a-field">
                        <label for="category_description">Description</label>
                        <textarea id="category_description" name="description" class="form-control" rows="3" placeholder="Short description..."><?php echo sanitize_output($old['description'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" name="save_category" class="a-btn primary block">
                        <?php echo $isEdit ? 'Update Category' : 'Add Category'; ?>
                    </button>
                    <?php if ($isEdit): ?>
                        <a href="index.php?page=admin-categories" class="a-btn ghost block mt-2">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="a-card">
            <div class="a-card-head">
                <h5><i class="bi bi-list-ul"></i> All Categories</h5>
                <a href="index.php?page=admin-products" class="a-btn ghost sm"><i class="bi bi-box-seam"></i> Products</a>
            </div>
            <div class="a-card-body flush">
                <?php if (empty($categories)): ?>
                    <div class="a-empty"><i class="bi bi-tags"></i>No categories yet.</div>
                <?php else: ?>
                    <div class="a-table-wrap">
                        <table class="a-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Products</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><strong><?php echo sanitize_output($cat['category_name']); ?></strong></td>
                                        <td><?php echo sanitize_output($cat['description'] ?? '-'); ?></td>
                                        <td><?php echo (int)$cat['product_count']; ?></td>
                                        <td>
                                            <?php echo $cat['is_active']
                                                ? '<span class="a-badge green">Active</span>'
                                                : '<span class="a-badge grey">Inactive</span>'; ?>
                                        </td>
                                        <td>
                                            <div class="a-actions">
                                                <a href="index.php?page=admin-categories&edit=<?php echo (int)$cat['category_id']; ?>" class="a-btn outline sm">Edit</a>
                                                <form action="../private/backend-scripting/save-category.php" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="category_id" value="<?php echo (int)$cat['category_id']; ?>">
                                                    <button type="submit" name="toggle_category" class="a-btn ghost sm">
                                                        <?php echo $cat['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                    </button>
                                                    <button type="submit" name="delete_category" class="a-btn danger-soft sm" onclick="return confirm('Delete this category?');">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/admin-footer.php'; ?>