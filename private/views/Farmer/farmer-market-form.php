<?php
validateFarmer($pdo);
$is_edit = false;
$editData = [];

if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = selectData($pdo, "SELECT * FROM market_farmer WHERE mf_id = ? AND farmer_id = ?", [$edit_id, $_SESSION['user_id']]);
    if (!empty($result)) {
        $is_edit = true;
        $editData = $result[0];
    }
}

$markets = selectData($pdo, "SELECT market_id, market_name, operating_days FROM markets WHERE is_active = 1");
$old = $_SESSION['old_input'] ?? $editData;
unset($_SESSION['old_input']);

include __DIR__ . '/../../../public/components/farmer-sidebar.php';
?>

<div class="f-wrap">
    <div class="f-page-head">
        <div>
            <h2><i class="bi bi-shop"></i> <?php echo $is_edit ? 'Update Market Assignment' : 'Join / Add Market'; ?></h2>
            <p>Assign your stall to a market for a specific day of the week.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="f-card">
                <div class="f-card-head">
                    <h5><i class="bi bi-shop"></i> Market Assignment</h5>
                </div>
                <div class="f-card-body">
                    <?php echo get_flash(); ?>

                    <form action="../private/backend-scripting/save-farmer-market.php" method="POST">
                        <?php echo csrf_field(); ?>

                        <?php if ($is_edit): ?>
                            <input type="hidden" name="mf_id" value="<?php echo sanitize_output($editData['mf_id']); ?>">
                        <?php endif; ?>

                        <div class="f-field">
                            <label for="marketSelect">Select Market</label>
                            <select name="market_id" id="marketSelect" class="form-select" required>
                                <option value="">-- Choose Market --</option>
                                <?php if (!empty($markets)): ?>
                                    <?php foreach ($markets as $m): ?>
                                        <option value="<?php echo (int)$m['market_id']; ?>" data-days="<?php echo sanitize_output($m['operating_days']); ?>" <?php echo (isset($old['market_id']) && $old['market_id'] == $m['market_id']) ? 'selected' : ''; ?>>
                                            <?php echo sanitize_output($m['market_name']); ?> (Days: <?php echo sanitize_output($m['operating_days']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="f-field">
                            <label for="stallNumber">Stall Number</label>
                            <input type="text" id="stallNumber" name="stall_number" class="form-control" value="<?php echo sanitize_output($old['stall_number'] ?? ''); ?>" required placeholder="e.g. Stall #12">
                        </div>

                        <div class="f-field">
                            <label for="daySelect">Day of Week</label>
                            <select name="day_of_week" id="daySelect" class="form-select" required>
                                <option value="">-- First Select Market --</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="f-field">
                                    <label for="pickupStart">Pickup Start Time</label>
                                    <input type="time" id="pickupStart" name="pickup_start" class="form-control" value="<?php echo sanitize_output($old['pickup_start'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="f-field">
                                    <label for="pickupEnd">Pickup End Time</label>
                                    <input type="time" id="pickupEnd" name="pickup_end" class="form-control" value="<?php echo sanitize_output($old['pickup_end'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="save_farmer_market" class="f-btn primary block mt-2">
                            <i class="bi bi-check-circle"></i> <?php echo $is_edit ? 'Update Market Assignment' : 'Save Market Assignment'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const marketSelect = document.getElementById("marketSelect");
    const daySelect = document.getElementById("daySelect");
    const oldDay = "<?php echo $old['day_of_week'] ?? ''; ?>";

    function updateDays() {
        const option = marketSelect.options[marketSelect.selectedIndex];
        const days = option.getAttribute("data-days");
        daySelect.innerHTML = '<option value="">-- Select Day --</option>';
        if (!days) return;
        days.split(",").forEach(function (day) {
            day = day.trim();
            const opt = document.createElement("option");
            opt.value = day;
            opt.textContent = day;
            if (day === oldDay) opt.selected = true;
            daySelect.appendChild(opt);
        });
    }

    marketSelect.addEventListener("change", updateDays);
    if (marketSelect.value) updateDays();
});
</script>

<?php include __DIR__ . '/../../../public/components/farmer-footer.php'; ?>