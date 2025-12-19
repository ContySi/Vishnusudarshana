<?php
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: text/html; charset=UTF-8');

$selectedStatus   = $_GET['status']   ?? 'All';
$selectedCategory = $_GET['category'] ?? 'All';
$search           = trim($_GET['search'] ?? '');
$page             = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

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

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM service_requests $whereSql";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));

$sql = "
    SELECT id, tracking_id, customer_name, mobile, category_slug,
           total_amount, payment_status, service_status, created_at, product_name, selected_products
    FROM service_requests
    $whereSql
    ORDER BY created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if (!$requests): ?>
<tr>
    <td colspan="10" class="no-data">No service requests found.</td>
</tr>
<?php else: ?>
<?php foreach ($requests as $row): ?>
<tr>
    <td><?= htmlspecialchars($row['tracking_id']) ?></td>
    <td><?= htmlspecialchars($row['customer_name']) ?></td>
    <td><?= htmlspecialchars($row['mobile']) ?></td>
    <td>
        <?php
        $products = '-';
        if (!empty($row['product_name'])) {
            $products = htmlspecialchars($row['product_name']);
        } elseif (!empty($row['selected_products'])) {
            $decoded = json_decode($row['selected_products'], true);
            if (is_array($decoded) && count($decoded)) {
                $names = [];
                foreach ($decoded as $prod) {
                    if (isset($prod['name'])) {
                        $names[] = htmlspecialchars($prod['name']);
                    } elseif (isset($prod['product_name'])) {
                        $names[] = htmlspecialchars($prod['product_name']);
                    }
                }
                if ($names) {
                    $products = implode(', ', $names);
                }
            }
        }
        echo $products;
        ?>
    </td>
    <td><?= htmlspecialchars($row['category_slug']) ?></td>
    <td>₹<?= number_format($row['total_amount'], 2) ?></td>
    <td>
        <?php
        $payClass = 'payment-' . strtolower(str_replace(' ', '-', $row['payment_status']));
        ?>
        <span class="status-badge <?= $payClass ?>">
            <?= htmlspecialchars($row['payment_status']) ?>
        </span>
    </td>
    <td>
        <?php
        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $row['service_status']));
        ?>
        <span class="status-badge <?= $statusClass ?>">
            <?= htmlspecialchars($row['service_status']) ?>
        </span>
    </td>
    <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
    <td><a class="view-btn" href="view.php?id=<?= $row['id'] ?>">View</a></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
<?php if ($totalPages > 1): ?>
<tr>
    <td colspan="10" style="background:#f9eaea; text-align:center; padding:16px 0;">
        <nav class="pagination">
            <?php
            $query = $_GET;
            unset($query['page']);
            $baseUrl = '';
            $queryStr = http_build_query($query);
            $linkBase = ($queryStr ? '?' . $queryStr . '&' : '?') . 'page=';
            if ($page > 1) {
                echo '<a href="' . htmlspecialchars($linkBase . ($page - 1)) . '" class="page-link">&laquo; Previous</a> ';
            } else {
                echo '<span class="page-link disabled">&laquo; Previous</span> ';
            }
            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == $page) {
                    echo '<span class="page-link current">' . $i . '</span> ';
                } else {
                    echo '<a href="' . htmlspecialchars($linkBase . $i) . '" class="page-link">' . $i . '</a> ';
                }
            }
            if ($page < $totalPages) {
                echo '<a href="' . htmlspecialchars($linkBase . ($page + 1)) . '" class="page-link">Next &raquo;</a>';
            } else {
                echo '<span class="page-link disabled">Next &raquo;</span>';
            }
            ?>
        </nav>
    </td>
</tr>
<?php endif; ?>
