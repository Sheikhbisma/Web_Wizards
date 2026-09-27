<?php
validateAdmin();

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$isEdit = false;
$editData = [];

// ---- Edit karne ke liye market_id aaya ho toh data load karo ----
if (isset($_GET['edit'])) {
    $found = selectData($pdo, "SELECT * FROM markets WHERE market_id = ?", [(int)$_GET['edit']]);
    if (!empty($found)) {
        $isEdit = true;
        $editData = $found[0];
        $old = $editData;
        $old['days'] = !empty($editData['operating_days']) ? explode(',', $editData['operating_days']) : [];
    }
}

$selectedDays = isset($old['days']) ? $old['days'] : [];
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// ---- Saare markets list karo ----
$markets = selectData($pdo, "SELECT m.*, COUNT(mf.mf_id) AS farmer_count,
                             (SELECT COUNT(*) FROM orders o WHERE o.market_id = m.market_id) AS order_count
                             FROM markets m
                             LEFT JOIN market_farmer mf ON mf.market_id = m.market_id
                             GROUP BY m.market_id
                             ORDER BY m.market_id DESC");

include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap">
    <div class="a-page-head">
        <div>
            <h2><i class="bi bi-shop-window"></i> Manage Markets</h2>
            <p>Add markets, set their location and manage operating hours.</p>
        </div>
        <div class="a-actions">
            <span class="a-chip"><i class="bi bi-shop"></i> <?php echo count($markets); ?> markets</span>
        </div>
    </div>

    <?php echo get_flash(); ?>

    <div class="a-form-side">
        <div class="a-card">
            <div class="a-card-head">
                <h5><i class="bi bi-<?php echo $isEdit ? 'pencil-square' : 'plus-circle'; ?>"></i> <?php echo $isEdit ? 'Edit Market' : 'Add New Market'; ?></h5>
            </div>
            <div class="a-card-body">
                <form action="../private/backend-scripting/save-market.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="market_id" value="<?php echo (int)$editData['market_id']; ?>">
                    <?php endif; ?>

                    <div class="a-field">
                        <label for="market_name">Market Name</label>
                        <input type="text" id="market_name" name="market_name" class="form-control" value="<?php echo sanitize_output($old['market_name'] ?? ''); ?>" required placeholder="e.g. Sunday Bazaar">
                    </div>

                    <div class="a-field">
                        <label for="market_address">Address / Location Description</label>
                        <textarea id="market_address" name="address" class="form-control" rows="2" required placeholder="e.g. Main Clifton Road, Block 4"><?php echo sanitize_output($old['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="a-field">
                        <label>Pin Location on Map</label>
                        <div id="marketMap" class="a-map"></div>
                        <small class="a-hint">Click anywhere on the map to set coordinates.</small>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="a-field">
                                <label for="latitude">Latitude</label>
                                <input type="text" id="latitude" name="latitude" class="form-control" value="<?php echo sanitize_output($old['latitude'] ?? ''); ?>" readonly required placeholder="Click on map">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="a-field">
                                <label for="longitude">Longitude</label>
                                <input type="text" id="longitude" name="longitude" class="form-control" value="<?php echo sanitize_output($old['longitude'] ?? ''); ?>" readonly required placeholder="Click on map">
                            </div>
                        </div>
                    </div>

                    <div class="a-field">
                        <label class="d-block">Operating Days</label>
                        <div class="row g-2">
                            <?php foreach ($daysOfWeek as $day): ?>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="days[]" value="<?php echo $day; ?>" id="day_<?php echo $day; ?>" <?php echo in_array($day, $selectedDays) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="day_<?php echo $day; ?>"><?php echo $day; ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="a-field">
                                <label for="opening_time">Opening Time</label>
                                <input type="time" id="opening_time" name="opening_time" class="form-control" value="<?php echo sanitize_output($old['opening_time'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="a-field">
                                <label for="closing_time">Closing Time</label>
                                <input type="time" id="closing_time" name="closing_time" class="form-control" value="<?php echo sanitize_output($old['closing_time'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="save_market" class="a-btn primary block">
                        <i class="bi bi-check-circle"></i> <?php echo $isEdit ? 'Update Market' : 'Save Market'; ?>
                    </button>
                    <?php if ($isEdit): ?>
                        <a href="index.php?page=manage-markets" class="a-btn ghost block mt-2">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="a-card">
            <div class="a-card-head">
                <h5><i class="bi bi-list-ul"></i> All Markets</h5>
                <a href="index.php?page=admin-settings" class="a-btn ghost sm"><i class="bi bi-gear"></i> Settings</a>
            </div>
            <div class="a-card-body flush">
                <?php if (empty($markets)): ?>
                    <div class="a-empty"><i class="bi bi-shop"></i>No markets yet. Add your first market.</div>
                <?php else: ?>
                    <div class="a-entity-grid market-grid">
                            <?php foreach ($markets as $market): ?>
                                <?php
                                $opening = !empty($market['opening_time']) ? date("h:i A", strtotime($market['opening_time'])) : '';
                                $closing = !empty($market['closing_time']) ? date("h:i A", strtotime($market['closing_time'])) : '';
                                ?>
                                <article class="a-entity-card">
                                    <div class="a-entity-card-head">
                                        <div class="a-entity-title">
                                            <span class="a-entity-icon"><i class="bi bi-shop-window"></i></span>
                                            <div>
                                                <h6><?php echo sanitize_output($market['market_name']); ?></h6>
                                                <p><?php echo sanitize_output($market['address']); ?></p>
                                            </div>
                                        </div>
                                        <span class="a-badge green"><?php echo (int)$market['farmer_count']; ?> farmers</span>
                                    </div>
                                    <div class="a-entity-details">
                                        <div class="wide"><span>Operating days</span><strong><?php echo sanitize_output($market['operating_days'] ?? 'Not set'); ?></strong></div>
                                        <div><span>Hours</span><strong><?php echo ($opening && $closing) ? "$opening – $closing" : 'Not set'; ?></strong></div>
                                        <div><span>Orders</span><strong><?php echo (int)$market['order_count']; ?></strong></div>
                                    </div>
                                    <div class="a-entity-actions">
                                        <?php if (!empty($market['latitude']) && !empty($market['longitude'])): ?>
                                            <a href="https://www.openstreetmap.org/?mlat=<?php echo urlencode($market['latitude']); ?>&mlon=<?php echo urlencode($market['longitude']); ?>#map=15/<?php echo urlencode($market['latitude']); ?>/<?php echo urlencode($market['longitude']); ?>" target="_blank" rel="noopener" class="a-btn info sm"><i class="bi bi-geo-alt"></i> Map</a>
                                        <?php endif; ?>
                                        <a href="index.php?page=manage-markets&edit=<?php echo (int)$market['market_id']; ?>" class="a-btn outline sm">Edit</a>
                                        <form action="../private/backend-scripting/save-market.php" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="market_id" value="<?php echo (int)$market['market_id']; ?>">
                                            <button type="submit" name="delete_market" class="a-btn danger-soft sm" onclick="return confirm('Delete this market? This will also remove its farmer assignments.');">Delete</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var defaultLat = 24.8607;
        var defaultLng = 67.0011;

        var existingLat = document.getElementById('latitude').value;
        var existingLng = document.getElementById('longitude').value;

        var initialLat = existingLat ? parseFloat(existingLat) : defaultLat;
        var initialLng = existingLng ? parseFloat(existingLng) : defaultLng;

        var map = L.map('marketMap').setView([initialLat, initialLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = null;

        if (existingLat && existingLng) {
            marker = L.marker([initialLat, initialLng]).addTo(map);
        }

        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;

            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
        });
    });
</script>

<?php include __DIR__ . '/../../../public/components/admin-footer.php'; ?>