<?php
validateAdmin();

$totalFarmers   = selectData($pdo, "SELECT COUNT(*) AS total FROM farmers");
$pendingFarmers = selectData($pdo, "SELECT COUNT(*) AS total FROM farmers WHERE approval_status = 'pending'");
$totalCustomers = selectData($pdo, "SELECT COUNT(*) AS total FROM users WHERE role = 'customer'");
$activeCustomers = selectData($pdo, "SELECT COUNT(*) AS total FROM users WHERE role = 'customer' AND status = 'active'");
$totalMarkets   = selectData($pdo, "SELECT COUNT(*) AS total FROM markets");
$totalOrders    = selectData($pdo, "SELECT COUNT(*) AS total FROM orders");
$revenue        = selectData($pdo, "SELECT SUM(total_amount) AS total FROM orders WHERE order_status IN ('accepted','ready','completed')");
$totalProducts  = selectData($pdo, "SELECT COUNT(*) AS total FROM products");

$recentOrders = selectData($pdo,
    "SELECT o.order_id, o.total_amount, o.order_status, o.order_date,
            c.full_name, f.stall_name
     FROM orders o
     JOIN customers c ON o.customer_id = c.customer_id
     JOIN farmers f ON o.farmer_id = f.farmer_id
     ORDER BY o.order_date DESC, o.order_id DESC
     LIMIT 6");

include __DIR__ . '/../../../public/components/admin-sidebar.php';
?>

<div class="a-wrap harvest-dashboard-wrap">
    <?php echo get_flash(); ?>

    <!-- TOP ROW: 3 CARDS (Matched to Reference Image) -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Weather's today -->
        <div class="col-12 col-lg-4 col-xl-3">
            <div class="h-card h-weather-card">
                <span class="h-card-sub">Weather's today</span>
                <h4 class="h-weather-day"><?php echo date('l'); ?></h4>
                <span class="h-weather-date">[<?php echo date('jS M, Y'); ?>]</span>

                <div class="d-flex align-items-center justify-content-between mt-3 mb-2">
                    <div>
                        <div class="h-temp-main">29°<span class="h-temp-unit">C</span></div>
                        <span class="h-temp-time">9.35 hours</span>
                    </div>

                    <!-- Circular Temperature Dial Gauge -->
                    <div class="h-temp-dial">
                        <svg viewBox="0 0 100 100" class="h-dial-svg">
                            <circle cx="50" cy="50" r="40" class="h-dial-track"></circle>
                            <circle cx="50" cy="50" r="40" class="h-dial-prog" stroke-dasharray="251.2" stroke-dashoffset="65"></circle>
                        </svg>
                        <div class="h-dial-inner">
                            <span class="h-dial-val">25°<small>C</small></span>
                            <span class="h-dial-lbl">Room temp</span>
                        </div>
                    </div>
                </div>

                <div class="h-weather-chips">
                    <div class="h-wchip"><i class="fa-solid fa-wind"></i> <span>0Km/h</span></div>
                    <div class="h-wchip"><i class="fa-solid fa-droplet"></i> <span>86%</span></div>
                    <div class="h-wchip"><i class="fa-solid fa-gauge-high"></i> <span>1007hPa</span></div>
                </div>
            </div>
        </div>

        <!-- Card 2: Plant growth activity -->
        <div class="col-12 col-lg-4 col-xl-5">
            <div class="h-card h-growth-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="h-card-title mb-0">Plant growth activity</h6>
                    <span class="h-pill-badge">Weekly <i class="fa-solid fa-chevron-down ms-1" style="font-size: 0.65rem;"></i></span>
                </div>

                <!-- SVG Plant Growth Curve -->
                <div class="h-growth-chart-wrap">
                    <svg viewBox="0 0 420 130" class="h-growth-svg" preserveAspectRatio="none">
                        <!-- Curved Grid Lines -->
                        <line x1="20" y1="110" x2="400" y2="110" stroke="#f0f5ec" stroke-width="1.5"></line>
                        <line x1="20" y1="65" x2="400" y2="65" stroke="#f0f5ec" stroke-width="1.5" stroke-dasharray="4 4"></line>
                        
                        <!-- Smooth Growth Curve -->
                        <path d="M 40,95 C 100,90 140,75 195,50 C 260,25 320,60 380,35" fill="none" stroke="#228b58" stroke-width="2.5" stroke-linecap="round"></path>
                    </svg>

                    <!-- Milestone Node 1 -->
                    <div class="h-growth-node" style="left: 10%; top: 70%;">
                        <div class="h-node-dot seed"><i class="fa-solid fa-seedling"></i></div>
                        <span class="h-node-lbl">Seed Phase (W1)</span>
                    </div>

                    <!-- Milestone Node 2 -->
                    <div class="h-growth-node" style="left: 48%; top: 35%;">
                        <div class="h-node-tooltip">1.6 cm</div>
                        <div class="h-node-dot final"><i class="fa-solid fa-leaf"></i></div>
                        <span class="h-node-lbl">Final Growth (W3)</span>
                    </div>

                    <!-- Milestone Node 3 -->
                    <div class="h-growth-node" style="left: 88%; top: 22%;">
                        <div class="h-node-dot veg"><i class="fa-solid fa-tree"></i></div>
                        <span class="h-node-lbl">Vegetation (W25)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Featured Harvest Showcase -->
        <div class="col-12 col-lg-4 col-xl-4">
            <div class="h-card h-showcase-card">
                <img src="https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&w=700&q=80" alt="Greenhouse Farm" class="h-showcase-img">
                <div class="h-showcase-overlay">
                    <span class="h-showcase-badge"><i class="fa-solid fa-circle-check text-leaf me-1"></i> Greenhouse Farm Verified</span>
                    <h5 class="h-showcase-title">Organic Hydroponics Active</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM ROW: 2 MAIN CARDS (Matched to Reference Image) -->
    <div class="row g-3 mb-4">
        <!-- Left: Summary of Production Column Chart -->
        <div class="col-12 col-lg-8">
            <div class="h-card h-prod-summary-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="h-card-title mb-0">Summary of production</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button class="h-icon-btn-sm" title="Filter"><i class="fa-solid fa-sliders"></i></button>
                        <button class="h-icon-btn-sm" title="Expand"><i class="fa-solid fa-arrow-up-right-from-square"></i></button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-2 border-bottom border-light-subtle">
                    <span class="text-muted small fw-medium">Comparing with last year</span>
                    <div class="d-flex align-items-center gap-3">
                        <span class="h-legend-item"><span class="h-legend-dot current"></span> Current Year Production</span>
                        <span class="h-legend-item"><span class="h-legend-dot last"></span> Last Year Production</span>
                    </div>
                </div>

                <!-- Column Chart Mockup (JAN - DEC with dynamic tooltips) -->
                <div class="h-barchart-container">
                    <div class="h-barchart-scale">
                        <span>5000</span>
                        <span>4000</span>
                        <span>3000</span>
                        <span>2000</span>
                        <span>1000</span>
                        <span>0</span>
                    </div>

                    <div class="h-barchart-bars">
                        <?php
                        $months = [
                            ['JAN', 28, 18], ['FEB', 38, 26], ['MAR', 45, 34],
                            ['APR', 58, 42], ['MAY', 72, 54], ['JUN', 92, 70, true],
                            ['JUL', 84, 62], ['AUG', 96, 74], ['SEP', 88, 68],
                            ['OCT', 68, 52], ['NOV', 58, 44], ['DEC', 76, 58]
                        ];
                        foreach ($months as $m):
                            $isTooltip = $m[3] ?? false;
                        ?>
                            <div class="h-bar-col">
                                <?php if ($isTooltip): ?>
                                    <div class="h-bar-tooltip-pop">onion 1.60</div>
                                    <div class="h-bar-tooltip-line"></div>
                                <?php endif; ?>
                                <div class="h-bar-pillar">
                                    <div class="h-bar-seg-current" style="height: <?php echo $m[1]; ?>%;"></div>
                                    <div class="h-bar-seg-last" style="height: <?php echo $m[2]; ?>%;"></div>
                                </div>
                                <span class="h-bar-lbl"><?php echo $m[0]; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Vertical Harvest Farms Spotlight Card -->
        <div class="col-12 col-lg-4">
            <div class="h-card h-vertical-farm-card">
                <div class="h-vfarm-media">
                    <img src="https://images.unsplash.com/photo-1530836369250-ef72a3f5cda8?auto=format&fit=crop&w=600&q=80" alt="Vertical Farm" class="h-vfarm-img">
                </div>
                
                <div class="h-vfarm-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="h-vfarm-title mb-0">Vertical Harvest<br>Farms</h5>
                        <button class="h-vfarm-play-btn" title="Play Walkthrough">
                            <i class="fa-solid fa-play"></i>
                        </button>
                    </div>

                    <!-- Progress bar -->
                    <div class="h-vfarm-progress-wrap mb-2">
                        <div class="d-flex justify-content-between small text-white-50 mb-1" style="font-size: 0.74rem;">
                            <span>18.90</span>
                            <span>36.00</span>
                        </div>
                        <div class="h-vfarm-progressbar">
                            <div class="h-vfarm-progfill" style="width: 52%;"></div>
                            <span class="h-vfarm-progdot" style="left: 52%;"></span>
                        </div>
                    </div>

                    <p class="h-vfarm-desc mb-0">
                        Vertical Farming is a novel method of growing crops by artificially stacking plants vertically above each other in skyscrapers or by using the third dimension of space.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT ORDERS & LIVE BACKEND HUB TABLE -->
    <div class="h-card p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="h-card-title mb-1"><i class="fa-solid fa-receipt text-success me-2"></i>Live System Orders</h5>
                <span class="text-muted small">Total Revenue: <strong>Rs <?php echo number_format((float)($revenue[0]['total'] ?? 0), 2); ?></strong> | Active Customers: <strong><?php echo (int)($activeCustomers[0]['total'] ?? 0); ?></strong></span>
            </div>
            <a href="order-listing" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold">View All Orders</a>
        </div>

        <?php if (empty($recentOrders)): ?>
            <div class="text-center py-4 text-muted"><i class="fa-solid fa-bag-shopping fs-3 mb-2 d-block opacity-50"></i>No orders yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase text-muted">
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Farmer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo (int)$order['order_id']; ?></strong></td>
                                <td><?php echo sanitize_output($order['full_name'] ?? 'Customer'); ?></td>
                                <td><?php echo sanitize_output($order['stall_name']); ?></td>
                                <td class="fw-semibold text-success">Rs <?php echo number_format((float)$order['total_amount'], 2); ?></td>
                                <td><?php echo adminStatusBadge($order['order_status']); ?></td>
                                <td class="text-muted small"><?php echo date("d M Y, h:i A", strtotime($order['order_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../../public/components/admin-footer.php'; ?>