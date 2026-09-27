<?php
$pageTitle = 'Checkout - MarketLink';
$cust = customerGuard($pdo);

$items = cartItems($pdo);
if (empty($items)) {
    set_flash('error', 'Your cart is empty. Add products first.');
    redirect('products');
}
include __DIR__ . '/../../../public/components/header.php';

$groups = [];
foreach ($items as $p) {
    $fid = (int)$p['farmer_id'];
    if (!isset($groups[$fid])) {
        $groups[$fid] = ['farmer' => $p['stall_name'], 'farmer_id' => $fid, 'items' => []];
    }
    $groups[$fid]['items'][] = $p;
}

foreach ($groups as $fid => $g) {
    $stalls = selectData($pdo, "SELECT m.*, mf.mf_id FROM market_farmer AS mf
        INNER JOIN markets AS m ON m.market_id = mf.market_id AND m.is_active = 1
        WHERE mf.farmer_id = ? ORDER BY m.market_name", [$fid]);
    $groups[$fid]['markets'] = $stalls;
}

$cid = (int)$cust['customer_id'];
?>

<div class="c-page">
    <div class="c-wrap">
        <div class="c-page-head">
            <div>
                <div class="section-kicker">Checkout</div>
                <h2 class="mt-1 mb-1">Confirm Your Pre-Order</h2>
                <p>Pick a market, date and time slot for every farmer. Payment is made in person at pickup.</p>
            </div>
            <a href="<?php echo ML_asset('cart'); ?>" class="c-btn ghost"><i class="bi bi-arrow-left"></i> Back to Cart</a>
        </div>

        <div class="alert alert-success">
            <i class="bi bi-wallet2"></i>
            <div><b>Cash at pickup.</b> No online payment is needed. Bring the amount to the stall when you collect your basket.</div>
        </div>

        <?php foreach ($groups as $g): ?>
            <div class="c-card">
                <div class="c-card-head">
                    <h5><i class="bi bi-person-badge"></i> <?php echo sanitize_output($g['farmer']); ?></h5>
                    <span class="c-muted"><?php echo count($g['items']); ?> item(s) · <?php
                        $gt = 0; foreach ($g['items'] as $it) $gt += $it['price'] * $it['qty'];
                        echo money($gt);
                    ?></span>
                </div>
                <div class="c-card-body">
                    <form class="checkout-form" data-checkout-form data-farmer="<?php echo $g['farmer_id']; ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="place">
                        <input type="hidden" name="farmer_id" value="<?php echo $g['farmer_id']; ?>">

                        <div class="c-card mb-3">
                            <div class="c-table-wrap">
                                <table class="c-table">
                                    <thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Total</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($g['items'] as $it): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span style="width:40px;height:40px;border-radius:10px;overflow:hidden;flex-shrink:0;display:inline-block;"><?php echo prodImg($it); ?></span>
                                                        <span><?php echo sanitize_output($it['name']); ?> <span class="c-muted">/ <?php echo sanitize_output($it['unit']); ?></span></span>
                                                    </div>
                                                </td>
                                                <td class="text-center"><?php echo (int)$it['qty']; ?></td>
                                                <td class="text-end fw-semibold"><?php echo money($it['price'] * $it['qty']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="c-field">
                            <label><?php echo $g['farmer']; ?> is available at these markets</label>
                            <select name="market_id" class="form-select btn-pick-market" required>
                                <?php foreach ($g['markets'] as $mk): ?>
                                    <option value="<?php echo $mk['market_id']; ?>" data-days="<?php echo sanitize_output($mk['operating_days']); ?>" data-open="<?php echo sanitize_output($mk['opening_time']); ?>" data-close="<?php echo sanitize_output($mk['closing_time']); ?>"><?php echo sanitize_output($mk['market_name']); ?> — <?php echo $mk['address']; ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($g['markets'])): ?><option value="">This farmer has no active market</option><?php endif; ?>
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="c-field">
                                    <label>Pickup date (market days)</label>
                                    <div class="d-flex flex-wrap gap-2 date-chips">
                                        <span class="c-muted">Choose a market above to see dates.</span>
                                    </div>
                                    <input type="hidden" name="pickup_date" value="" data-date-field>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="c-field">
                                    <label>Time slot</label>
                                    <div class="d-flex flex-wrap gap-2 slot-chips">
                                        <span class="c-muted">Choose a date to see slots.</span>
                                    </div>
                                    <input type="hidden" name="pickup_slot" value="" data-slot-field>
                                </div>
                            </div>
                        </div>

                        <div class="c-field">
                            <label>Special instructions <span class="c-muted fw-normal">(optional)</span></label>
                            <textarea name="instructions" class="form-control" rows="2" placeholder="e.g. choose ripe mangoes, extra packaging..."></textarea>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <span class="c-muted"><i class="bi bi-clock-history me-1"></i>Pre-orders can be modified until 24h before pickup.</span>
                            <button type="submit" class="c-btn primary" <?php echo empty($g['markets']) ? 'disabled' : ''; ?>><i class="bi bi-check2-circle"></i> Place Pre-Order</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <a href="<?php echo ML_asset('cart'); ?>" class="c-btn ghost sm"><i class="bi bi-arrow-left"></i> Back to cart</a>
            <a href="<?php echo ML_asset('orders'); ?>" class="c-muted">My Orders <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>

<script>
(function () {
    var DAY_SHORT = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var DAY_NUMBERS = { monday: 1, tuesday: 2, wednesday: 3, thursday: 4, friday: 5, saturday: 6, sunday: 0 };

    function toMinutes(time) {
        var parts = String(time).split(':');
        return (+parts[0] * 60) + (+parts[1] || 0);
    }

    function toTimeLabel(minutes) {
        var h = Math.floor(minutes / 60);
        var m = minutes % 60;
        return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
    }

    // Market opening/closing se 1 ghante ke slots banata hai (max 6).
    function makeSlots(open, close) {
        var slots = [];
        var start = toMinutes(open);
        var end = toMinutes(close);
        for (var t = start; (t + 60) <= end && slots.length < 6; t += 90) {
            slots.push(toTimeLabel(t) + ' - ' + toTimeLabel(t + 60));
        }
        return slots;
    }

    // "monday,friday" jese days se aage ke 14 din ke pickup dates banata hai.
    function makeDates(daysCsv) {
        var wanted = daysCsv.split(',').map(function (d) { return DAY_NUMBERS[d.trim().toLowerCase()]; });
        var dates = [];
        var today = new Date();

        for (var i = 1; i <= 14; i++) {
            var d = new Date(today.getFullYear(), today.getMonth(), today.getDate() + i);
            if (wanted.indexOf(d.getDay()) === -1) continue;

            var mm = d.getMonth() + 1;
            var dd = d.getDate();
            dates.push({
                iso: d.getFullYear() + '-' + (mm < 10 ? '0' : '') + mm + '-' + (dd < 10 ? '0' : '') + dd,
                label: DAY_SHORT[d.getDay()] + ', ' + mm + '/' + dd
            });
        }
        return dates;
    }

    // Ek chip ko select karna (date ya slot).
    function selectChip(box, chip) {
        box.querySelectorAll('.ml-slot').forEach(function (c) { c.classList.remove('selected'); });
        chip.classList.add('selected');
    }

    // Har farmer ke checkout form ko wire karo.
    document.querySelectorAll('[data-checkout-form]').forEach(function (form) {
        var marketSelect = form.querySelector('[name="market_id"]');
        if (!marketSelect) return;

        var dateBox = form.querySelector('.date-chips');
        var slotBox = form.querySelector('.slot-chips');
        var dateField = form.querySelector('[data-date-field]');
        var slotField = form.querySelector('[data-slot-field]');
        var selectedDate = '';

        // Abhi selected market ka option wapas karo.
        function selectedMarket() {
            return marketSelect.options[marketSelect.selectedIndex];
        }

        function buildDates() {
            var market = selectedMarket();
            if (!market || !market.getAttribute('data-days')) {
                dateBox.innerHTML = '<span class="small text-muted">No market selected.</span>';
                return;
            }

            var dates = makeDates(market.getAttribute('data-days'));
            dateBox.innerHTML = '';

            if (!dates.length) {
                dateBox.innerHTML = '<span class="small text-muted">Market has no open days in the next 14 days.</span>';
                return;
            }

            dates.forEach(function (d) {
                var chip = document.createElement('span');
                chip.className = 'ml-slot' + (selectedDate === d.iso ? ' selected' : '');
                chip.textContent = d.label;
                chip.addEventListener('click', function () {
                    selectedDate = d.iso;
                    selectChip(dateBox, chip);
                    dateField.value = d.iso;
                    slotField.value = '';
                    buildSlots();
                });
                dateBox.appendChild(chip);
            });
        }

        function buildSlots() {
            var market = selectedMarket();
            var slots = market ? makeSlots(market.getAttribute('data-open'), market.getAttribute('data-close')) : [];
            slotBox.innerHTML = '';

            if (!slots.length) {
                slotBox.innerHTML = '<span class="small text-muted">No slots available for this market.</span>';
                return;
            }

            slots.forEach(function (s) {
                var chip = document.createElement('span');
                chip.className = 'ml-slot';
                chip.textContent = s;
                chip.addEventListener('click', function () {
                    selectChip(slotBox, chip);
                    slotField.value = s;
                });
                slotBox.appendChild(chip);
            });
        }

        marketSelect.addEventListener('change', function () {
            selectedDate = '';
            dateField.value = '';
            slotField.value = '';
            buildDates();
        });

        buildDates();
    });

    // Form submit par order place karo.
    document.querySelectorAll('[data-checkout-form]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var submitBtn = form.querySelector('button[type="submit"]');
            var formData = new FormData(form);

            if (!formData.get('pickup_date') || !formData.get('pickup_slot')) {
                toast('Please select a pickup date and time slot first.', false);
                return;
            }

            submitBtn.disabled = true;
            var body = new URLSearchParams(formData);
            body.set('csrf_token', window.ML.csrf);

            fetch('../private/backend-scripting/customer-order.php', { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.ok) {
                        setBadge('[data-cart-count]', res.count);
                        toast(res.msg || 'Pre-order placed!');
                        setTimeout(function () { window.location.href = window.ML.base + '/order?id=' + res.order_id; }, 900);
                    } else {
                        submitBtn.disabled = false;
                        toast(res.error || 'Could not place order', false);
                    }
                }).catch(function () { submitBtn.disabled = false; toast('Network error', false); });
        });
    });
})();
</script>

<?php include __DIR__ . '/../../../public/components/footer.php'; ?>