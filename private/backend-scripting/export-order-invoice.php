<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

validateAdmin();

$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    set_flash("error", "Invalid order.");
    redirect("order-listing");
}

$order = selectData($pdo,
    "SELECT o.*, c.full_name, u.email, u.contact, f.stall_name, f.contact_person, m.market_name, m.address AS market_address
     FROM orders o
     JOIN customers c ON o.customer_id = c.customer_id
     JOIN users u ON c.user_id = u.id
     LEFT JOIN farmers f ON o.farmer_id = f.farmer_id
     LEFT JOIN markets m ON o.market_id = m.market_id
     WHERE o.order_id = ?", [$orderId]);

if (empty($order)) {
    set_flash("error", "Order not found.");
    redirect("order-listing");
}

if ($order[0]['order_status'] !== 'completed') {
    set_flash("error", "Invoice is only available for completed orders.");
    redirect("order-listing");
}

$rows = $order[0];

$items = selectData($pdo,
    "SELECT p.name, oi.quantity, oi.unit_price, oi.subtotal
     FROM order_items oi
     LEFT JOIN products p ON oi.product_id = p.product_id
     WHERE oi.order_id = ? ORDER BY oi.order_item_id", [$orderId]);

$invoiceNumber = 'INV-' . str_pad((string)$orderId, 6, '0', STR_PAD_LEFT);
$generatedAt = date("d M Y, h:i A", strtotime($rows['order_date']));
$pickupAt = !empty($rows['pickup_date'])
    ? date("d M Y", strtotime($rows['pickup_date'])) . (!empty($rows['pickup_slot']) ? ' (' . htmlspecialchars($rows['pickup_slot']) . ')' : '')
    : 'As per merchant schedule';

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"MarketLink-Invoice-{$orderId}.xls\"");
header("Cache-Control: max-age=0");
echo "\xEF\xBB\xBF";
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Invoice</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
echo '<body>';
echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial;font-size:12px;width:100%;">';

echo '<tr><td colspan="4" style="background:#0b3528;color:#ffffff;font-size:18px;font-weight:bold;">MarketLink - Invoice</td></tr>';
echo '<tr><td colspan="4" style="background:#e6f4ec;font-size:13px;">Invoice No: ' . $invoiceNumber . ' | Order No: #' . (int)$orderId . ' | Invoice Date: ' . $generatedAt . '</td></tr>';

echo '<tr>';
echo '<td colspan="2" valign="top"><b>Billed To (Customer)</b><br>' . htmlspecialchars($rows['full_name'] ?? 'Customer');
echo '<br>' . htmlspecialchars($rows['email'] ?? '');
echo '<br>' . htmlspecialchars($rows['contact'] ?? '') . '</td>';
echo '<td colspan="2" valign="top"><b>Farmer / Stall</b><br>' . htmlspecialchars($rows['stall_name'] ?? 'N/A');
echo '<br>' . htmlspecialchars($rows['contact_person'] ?? '');
echo '<br>' . htmlspecialchars($rows['market_name'] ?? 'Farmers Market') . '</td>';
echo '</tr>';

echo '<tr><td colspan="2"><b>Market:</b> ' . htmlspecialchars($rows['market_name'] ?? 'Farmers Market') . '</td>';
echo '<td colspan="2"><b>Pickup:</b> ' . $pickupAt . '</td></tr>';

echo '<tr style="background:#e6f4ec;font-weight:bold;"><td>#</td><td>Product</td><td>Qty</td><td align="right">Amount (Rs)</td></tr>';
$total = 0.00;
$i = 1;
foreach ($items as $item) {
    $lineTotal = (float)$item['subtotal'];
    $total += $lineTotal;
    echo '<tr><td>' . $i . '</td><td>' . htmlspecialchars($item['name'] ?? 'Product') . '</td>'
        . '<td>' . (int)$item['quantity'] . ' x Rs ' . number_format((float)$item['unit_price'], 2) . '</td>'
        . '<td align="right">' . number_format($lineTotal, 2) . '</td></tr>';
    $i++;
}

echo '<tr><td colspan="3" align="right" style="font-weight:bold;">Total Amount</td>'
    . '<td align="right" style="font-weight:bold;">Rs ' . number_format($total, 2) . '</td></tr>';
echo '<tr><td colspan="4" style="font-size:11px;color:#555;">Payment is settled in person at pickup. This is a computer generated invoice and does not require a signature.</td></tr>';
echo '<tr><td colspan="4" style="background:#f2f6f3;font-size:11px;">MarketLink | Fresh produce, direct from the farmer | www.marketlink.local</td></tr>';

echo '</table></body></html>';