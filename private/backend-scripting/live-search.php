<?php
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if ($q === '' || mb_strlen($q) < 2) {
    echo json_encode(['q' => $q, 'products' => [], 'farmers' => [], 'markets' => []]);
    exit;
}

$like = '%' . $q . '%';

$products = selectData($pdo, "SELECT p.product_id, p.name, p.price, p.unit, p.image_url, f.stall_name, c.category_name
    FROM products p
    INNER JOIN farmers f ON f.farmer_id = p.farmer_id AND f.approval_status = 'approved'
    INNER JOIN users u ON u.id = f.user_id AND u.status = 'active'
    LEFT JOIN categories c ON c.category_id = p.category_id
    WHERE p.is_available = 1 AND p.is_sold_out = 0 AND p.stock_quantity > 0
      AND (p.name LIKE ? OR p.description LIKE ? OR f.stall_name LIKE ? OR c.category_name LIKE ?)
    ORDER BY p.name LIMIT 5", [$like, $like, $like, $like]);

$farmers = selectData($pdo, "SELECT farmer_id, stall_name FROM farmers
    WHERE approval_status = 'approved' AND stall_name LIKE ? ORDER BY stall_name LIMIT 3", [$like]);

$markets = selectData($pdo, "SELECT market_id, market_name, address FROM markets
    WHERE is_active = 1 AND (market_name LIKE ? OR address LIKE ?) ORDER BY market_name LIMIT 3", [$like, $like]);

$outProducts = [];
foreach ($products as $p) {
    $outProducts[] = [
        'id'    => (int)$p['product_id'],
        'name'  => $p['name'],
        'price' => (float)$p['price'],
        'unit'  => $p['unit'] ?? '',
        'stall' => $p['stall_name'],
        'img'   => prodImgSrc($p),
    ];
}

$outFarmers = [];
foreach ($farmers as $f) {
    $outFarmers[] = ['id' => (int)$f['farmer_id'], 'stall' => $f['stall_name']];
}

$outMarkets = [];
foreach ($markets as $m) {
    $outMarkets[] = ['id' => (int)$m['market_id'], 'name' => $m['market_name'], 'address' => $m['address']];
}

echo json_encode(['q' => $q, 'products' => $outProducts, 'farmers' => $outFarmers, 'markets' => $outMarkets]);