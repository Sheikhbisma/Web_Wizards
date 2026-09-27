<?php
$farmer = validateFarmer($pdo);
$farmerId = $farmer['farmer_id'];
$products = selectData($pdo, "SELECT product_id, name, stock_quantity FROM products WHERE farmer_id = ? ORDER BY name ASC", [$farmerId]);
$templates = selectData($pdo, "SELECT w.*, p.name FROM weekly_stock_template w INNER JOIN products p ON w.product_id = p.product_id WHERE w.farmer_id = ? ORDER BY w.day_of_week, p.name", [$farmerId]);
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-calendar-week"></i> Weekly Stock</h2>
            <p>Set recurring stock templates for each day of the week.</p>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="f-grid-2">
        <div class="f-card">
            <div class="f-card-head">
                <h5><i class="bi bi-plus-circle"></i> Add Weekly Stock Template</h5>
            </div>
            <div class="f-card-body">
                <form action="../private/backend-scripting/save-weekly-stock.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="f-field">
                        <label for="f-product">Product</label>
                        <select id="f-product" name="product_id" class="form-select" required>
                            <option value="">Select product</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?php echo (int)$p['product_id']; ?>"><?php echo sanitize_output($p['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="f-field">
                        <label for="f-day">Day of Week</label>
                        <select id="f-day" name="day_of_week" class="form-select" required>
                            <?php foreach ($daysOfWeek as $day): ?>
                                <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="f-field">
                        <label for="f-qty">Default Quantity</label>
                        <input type="number" id="f-qty" name="default_quantity" class="form-control" required min="0">
                    </div>
                    <button type="submit" name="save_template" class="f-btn primary block">
                        <i class="bi bi-save"></i> Save Template
                    </button>
                </form>
                <form action="../private/backend-scripting/save-weekly-stock.php" method="POST" class="mt-3">
                    <?php echo csrf_field(); ?>
                    <button type="submit" name="apply_template" class="f-btn outline block" data-confirm-apply>
                        <i class="bi bi-lightning-charge"></i> Apply Template to Current Stock
                    </button>
                    <p class="f-muted small mt-2 mb-0">
                        Resets every active product's stock back to its template quantity and clears the
                        sold-out flag, so nothing has to be retyped each week.
                    </p>
                </form>
            </div>
        </div>

        <div class="f-col-right">
            <div class="f-card">
                <div class="f-card-head">
                    <h5><i class="bi bi-arrow-repeat"></i> My Recurring Stock</h5>
                </div>
                <div class="f-card-body flush">
                    <?php if (empty($templates)): ?>
                        <div class="f-empty">
                            <i class="bi bi-arrow-repeat"></i>
                            No weekly template yet. Create one to automate stock.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="f-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Day</th>
                                        <th>Template Qty</th>
                                        <th>In Stock Now</th>
                                        <th>Active</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($templates as $row): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo sanitize_output($row['name']); ?></td>
                                        <td><?php echo sanitize_output($row['day_of_week']); ?></td>
                                        <td><span class="fw-bold"><?php echo (int)$row['default_quantity']; ?></span></td>
                                        <td>
                                            <?php
                                            $liveQty = null;
                                            foreach ($products as $pp) {
                                                if ((int)$pp['product_id'] === (int)$row['product_id']) {
                                                    $liveQty = (int)$pp['stock_quantity'];
                                                    break;
                                                }
                                            }
                                            ?>
                                            <?php if ($liveQty === null): ?>
                                                <span class="f-badge grey">n/a</span>
                                            <?php elseif ($liveQty === (int)$row['default_quantity']): ?>
                                                <span class="f-badge green"><?php echo $liveQty; ?></span>
                                            <?php else: ?>
                                                <span class="f-badge amber"><?php echo $liveQty; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['is_active']): ?>
                                                <span class="f-badge green">Yes</span>
                                            <?php else: ?>
                                                <span class="f-badge grey">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="f-actions">
                                                <form action="../private/backend-scripting/save-weekly-stock.php" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="apply_one_template" value="<?php echo (int)$row['template_id']; ?>">
                                                    <button type="submit" class="f-btn ghost sm" title="Reset this product's stock to <?php echo (int)$row['default_quantity']; ?>"
                                                            <?php if (!$row['is_active']) echo 'disabled'; ?>><i class="bi bi-lightning-charge"></i> Apply</button>
                                                </form>
                                                <form action="../private/backend-scripting/save-weekly-stock.php" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="template_id" value="<?php echo (int)$row['template_id']; ?>">
                                                    <button type="submit" name="toggle_template" class="f-btn ghost sm"><i class="bi bi-power"></i> On/Off</button>
                                                    <button type="submit" name="delete_template" class="f-btn danger sm" data-confirm-delete="<?php echo sanitize_output($row['name']); ?>"><i class="bi bi-trash"></i></button>
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
</div>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>

<script>
// Destructive weekly-stock actions get a confirm step. Deleting a template and
// resetting a whole week of stock are both easy to click by accident.
document.addEventListener('click', function (e) {
    var del = e.target.closest('[data-confirm-delete]');
    if (del) {
        var name = del.getAttribute('data-confirm-delete');
        if (!window.confirm('Delete the weekly template for "' + name + '"? Current stock will not change.')) {
            e.preventDefault();
        }
        return;
    }
    var apply = e.target.closest('[data-confirm-apply]');
    if (apply) {
        if (!window.confirm('Reset stock for every active template back to its template quantity?')) {
            e.preventDefault();
        }
    }
});
</script>