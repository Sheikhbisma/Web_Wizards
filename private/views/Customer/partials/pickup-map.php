<?php
/**
 * Shared pickup-point map with directions.
 *
 * Mirrors the map on home.php so every map in the project offers the same
 * "Get Directions" affordance and the same URL format:
 *   https://www.openstreetmap.org/directions?to=<lat>%2C<lng>
 *
 * The .mlp-map-* / .mlp-pin-* / .mlp-popup-* styles already live in
 * public/css/customer.css, so nothing here needs a local <style> block.
 *
 * Required before including:
 *   $pmPoints  array of ['lat','lng','name','kind','url']
 *   $pmMapId   unique DOM id for the map container
 * Optional:
 *   $pmTitle, $pmSub, $pmKicker, $pmShowList
 */
if (empty($pmPoints) || empty($pmMapId)) {
    return;
}

/* Drop anything without usable coordinates, otherwise Leaflet throws on
   a null lat and the whole map fails to initialise. A 0/0 pair means the
   row was saved without a pin, so treat that as missing too.
   Note the explicit parens: && binds tighter than ||, so without them
   this expression would keep any row whose longitude is non-zero. */
$pmPoints = array_values(array_filter($pmPoints, function ($p) {
    if (!isset($p['lat'], $p['lng']) || !is_numeric($p['lat']) || !is_numeric($p['lng'])) {
        return false;
    }
    return ((float)$p['lat'] !== 0.0) || ((float)$p['lng'] !== 0.0);
}));

if (empty($pmPoints)) {
    return;
}

$pmTitle  = $pmTitle  ?? 'Find Your Pickup Point';
$pmKicker = $pmKicker ?? 'Stalls &amp; Pickup Hubs';
$pmSub    = $pmSub    ?? 'Every point below sits on the map. Pick the closest one and open directions straight to it.';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<section class="mlp-section" id="<?php echo htmlspecialchars($pmMapId); ?>Section">
    <div class="container">
        <div class="mlp-head">
            <div class="mlp-kicker"><?php echo $pmKicker; ?></div>
            <h2 class="mlp-title"><?php echo $pmTitle; ?></h2>
            <p class="mlp-sub"><?php echo $pmSub; ?></p>
        </div>

        <div class="mlp-map-grid">
            <div class="mlp-map-shell">
                <div id="<?php echo htmlspecialchars($pmMapId); ?>" class="mlp-map-canvas" role="region" aria-label="Map of pickup points"></div>
                <div class="mlp-map-legend">
                    <span><i class="bi bi-square-fill mlp-dot-market"></i> Market / pickup point</span>
                    <span><i class="bi bi-circle-fill mlp-dot-farmer"></i> Farmer stall</span>
                </div>
            </div>

            <div class="mlp-map-side">
                <h3 class="mlp-side-title">Get Directions</h3>
                <p class="mlp-side-sub">Open a pickup point in your map app, or read the page for stall details.</p>
                <div class="mlp-map-list" data-stagger="70">
                    <?php foreach ($pmPoints as $p): ?>
                        <?php
                        $pLat = (float)$p['lat'];
                        $pLng = (float)$p['lng'];
                        $pDirUrl = 'https://www.openstreetmap.org/directions?to=' . $pLat . '%2C' . $pLng;
                        $pIsMarket = ($p['kind'] ?? 'market') === 'market';
                        ?>
                        <div class="mlp-map-row">
                            <span class="mlp-map-ico <?php echo $pIsMarket ? 'market' : 'farmer'; ?>">
                                <i class="bi <?php echo $pIsMarket ? 'bi-shop' : 'bi-person-fill'; ?>"></i>
                            </span>
                            <div class="mlp-map-meta">
                                <?php if (!empty($p['url'])): ?>
                                    <a href="<?php echo htmlspecialchars($p['url']); ?>" class="mlp-map-name"><?php echo htmlspecialchars($p['name']); ?></a>
                                <?php else: ?>
                                    <span class="mlp-map-name"><?php echo htmlspecialchars($p['name']); ?></span>
                                <?php endif; ?>
                                <span class="mlp-map-addr"><?php echo htmlspecialchars($p['kind'] ?? ''); ?><?php
                                    echo !empty($p['addr']) ? ' &middot; ' . htmlspecialchars($p['addr']) : '';
                                ?></span>
                            </div>
                            <a class="mlp-map-dir" target="_blank" rel="noopener"
                               href="<?php echo htmlspecialchars($pDirUrl); ?>"
                               title="Directions to <?php echo htmlspecialchars($p['name']); ?>"
                               aria-label="Directions to <?php echo htmlspecialchars($p['name']); ?>">
                                <i class="bi bi-signpost-split"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById(<?php echo json_encode($pmMapId); ?>);
    if (!el || typeof L === 'undefined') { return; }

    var points = <?php echo json_encode($pmPoints, JSON_UNESCAPED_SLASHES); ?>;

    var marketIcon = L.divIcon({
        className: '',
        html: '<span class="mlp-pin mlp-pin-market"><i class="bi bi-shop"></i></span>',
        iconSize: [34, 34], iconAnchor: [17, 34], popupAnchor: [0, -32]
    });
    var farmerIcon = L.divIcon({
        className: '',
        html: '<span class="mlp-pin mlp-pin-farmer"><i class="bi bi-person-fill"></i></span>',
        iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -26]
    });

    var marketBounds = [];
    var anyPoint = false;
    var map = L.map(el, { scrollWheelZoom: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    points.forEach(function (p) {
        var lat = parseFloat(p.lat), lng = parseFloat(p.lng);
        if (!isFinite(lat) || !isFinite(lng)) { return; }
        anyPoint = true;
        var isMarket = p.kind === 'market';
        if (isMarket) { marketBounds.push([lat, lng]); }
        L.marker([lat, lng], { icon: isMarket ? marketIcon : farmerIcon })
            .addTo(map)
            .bindPopup(
                '<div class="mlp-popup">' +
                '<span class="mlp-popup-kind">' + (isMarket ? 'Market / pickup point' : 'Farmer stall') + '</span>' +
                '<strong>' + String(p.name).replace(/[<>&]/g, '') + '</strong>' +
                (p.url ? '<a class="mlp-popup-btn" href="' + p.url + '">View details</a>' : '') +
                '<a class="mlp-popup-btn ghost" target="_blank" rel="noopener" href="https://www.openstreetmap.org/directions?to=' +
                lat + '%2C' + lng + '">Directions</a>' +
                '</div>'
            );
    });

    if (marketBounds.length) {
        map.fitBounds(marketBounds, { padding: [45, 45], maxZoom: 14 });
    } else if (anyPoint) {
        map.setView([points[0].lat, points[0].lng], 11);
    } else {
        map.setView([24.8607, 67.0011], 12);
    }

    /* Leaflet mis-measures when it initialises inside a container that is
       still laying out (a tab, an accordion, a lazy section). Nudge it once
       the paint settles so the tiles are not offset. */
    setTimeout(function () { map.invalidateSize(); }, 220);
});
</script>
