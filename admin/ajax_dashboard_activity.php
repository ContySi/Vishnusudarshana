<?php
// AJAX endpoint for dashboard recent activity (admin/index.php)
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/html; charset=UTF-8');

$page = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(tracking_id LIKE ? OR customer_name LIKE ? OR service_status LIKE ? OR category_slug LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "SELECT COUNT(*) FROM service_requests $whereSql";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "SELECT id, created_at, category_slug, customer_name, tracking_id, service_status FROM service_requests $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) === 0) {
    echo '<tr><td colspan="6" class="no-data">No recent activity found.</td></tr>';
} else {
    foreach ($rows as $row) {
        echo '<tr>';
        echo '<td>' . date('d-m-Y', strtotime($row['created_at'])) . '</td>';
        echo '<td>' . ($row['category_slug'] === 'appointment' ? 'Appointment' : 'Service') . '</td>';
        echo '<td>' . htmlspecialchars($row['customer_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['tracking_id']) . '</td>';
        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $row['service_status']));
        echo '<td><span class="status-badge ' . $statusClass . '">' . htmlspecialchars($row['service_status']) . '</span></td>';
        echo '<td><a class="view-btn" href="services/view.php?id=' . $row['id'] . ($row['category_slug'] === 'appointment' ? '&type=appointment' : '') . '" target="_blank">View</a></td>';
        echo '</tr>';
    }
}

// Pagination object for JS
$pagination = [
    'totalPages' => $totalPages,
    'currentPage' => $page
];
echo '<script>window.dashboardPagination = ' . json_encode($pagination) . ';</script>';
