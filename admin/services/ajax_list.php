<?php
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: text/html; charset=UTF-8');

// Filtering logic
$where  = [];
$params = [];
if ($selectedStatus !== 'All') {
    $where[]  = 'service_status = ?';
    $params[] = $selectedStatus;
}
if ($selectedCategory !== 'All') {
    $where[]  = 'category_slug = ?';
    $params[] = $selectedCategory;
}
if ($search !== '') {
    $where[] = '(tracking_id LIKE ? OR mobile LIKE ? OR customer_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT id, tracking_id, customer_name, mobile, category_slug,
           total_amount, payment_status, service_status, created_at, selected_products
    FROM service_requests
    $whereSql
    ORDER BY created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$catMap = [
    'birth-child' => 'Birth & Child Services',
    'marriage-matching' => 'Marriage & Matching',
    'astrology-consultation' => 'Astrology Consultation',
    'muhurat-event' => 'Muhurat & Event Guidance',
    'pooja-vastu-enquiry' => 'Pooja, Ritual & Vastu Enquiry',
];

if (!$requests) {
    echo '<tr><td colspan="10" class="no-data">No service requests found.</td></tr>';
    exit;
}

foreach ($requests as $row) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['tracking_id']) . '</td>';
    echo '<td>' . htmlspecialchars($row['customer_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['mobile']) . '</td>';
    // Product(s)
    echo '<td>';
    $products = '-';
    $decoded = json_decode($row['selected_products'], true);
    if (is_array($decoded) && count($decoded)) {
        $names = [];
        foreach ($decoded as $prod) {
            if (isset($prod['name'])) {
                $names[] = htmlspecialchars($prod['name']);
            }
        }
        if ($names) {
            $products = implode(', ', $names);
        }
    }
    echo $products;
    echo '</td>';
    // Category
    $catSlug = $row['category_slug'];
    echo '<td>' . (isset($catMap[$catSlug]) ? htmlspecialchars($catMap[$catSlug]) : htmlspecialchars($catSlug)) . '</td>';
    // Amount
    echo '<td>₹' . number_format($row['total_amount'], 2) . '</td>';
    // Payment Status
    $payClass = 'payment-' . strtolower(str_replace(' ', '-', $row['payment_status']));
    echo '<td><span class="status-badge ' . $payClass . '">' . htmlspecialchars($row['payment_status']) . '</span></td>';
    // Service Status
    $statusClass = 'status-' . strtolower(str_replace(' ', '-', $row['service_status']));
    echo '<td><span class="status-badge ' . $statusClass . '">' . htmlspecialchars($row['service_status']) . '</span></td>';
    // Date
    echo '<td>' . date('d-m-Y', strtotime($row['created_at'])) . '</td>';
    // Action
    echo '<td><a class="view-btn" href="view.php?id=' . $row['id'] . '">View</a></td>';
    echo '</tr>';
}