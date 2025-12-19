<?php
require_once __DIR__ . '/../../config/db.php';

/* ==============================
   SUMMARY COUNTS
============================== */
$todayCount = $pdo->query(
    "SELECT COUNT(*) FROM service_requests WHERE DATE(created_at) = CURDATE()"
)->fetchColumn();

$receivedCount = $pdo->query(
    "SELECT COUNT(*) FROM service_requests WHERE service_status = 'Received'"
)->fetchColumn();

$inProgressCount = $pdo->query(
    "SELECT COUNT(*) FROM service_requests WHERE service_status = 'In Progress'"
)->fetchColumn();

$completedCount = $pdo->query(
    "SELECT COUNT(*) FROM service_requests WHERE service_status = 'Completed'"
)->fetchColumn();

/* ==============================
   FILTERS
============================== */
$statusOptions = ['All', 'Received', 'In Progress', 'Completed'];
$categoryOptions = [
    'All' => 'All Categories',
    'birth-child' => 'Birth & Child Services',
    'marriage-matching' => 'Marriage & Matching',
    'astrology-consultation' => 'Astrology Consultation',
    'muhurat-event' => 'Muhurat & Event Guidance',
    'pooja-vastu-enquiry' => 'Pooja, Ritual & Vastu Enquiry',
];

$selectedStatus   = $_GET['status']   ?? 'All';
$selectedCategory = $_GET['category'] ?? 'All';

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

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT id, tracking_id, customer_name, mobile, category_slug,
           total_amount, payment_status, service_status, created_at
    FROM service_requests
    $whereSql
    ORDER BY created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin – Service Requests</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    font-family: Arial, sans-serif;
    background: #f7f7fa;
    margin: 0;
}
.admin-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 12px;
}
h1 {
    color: #800000;
    margin-bottom: 18px;
}

/* SUMMARY CARDS */
.summary-cards {
    display: flex;
    gap: 18px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.summary-card {
    flex: 1 1 180px;
    background: #fffbe7;
    border-radius: 14px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 2px 8px #e0bebe22;
}
.summary-count {
    font-size: 2.2em;
    font-weight: 700;
    color: #800000;
}
.summary-label {
    font-size: 1em;
    color: #444;
}

/* FILTER BAR */
.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 18px;
}
.filter-bar label {
    font-weight: 600;
}
.filter-bar select,
.filter-bar button {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 1em;
}
.filter-bar button {
    background: #800000;
    color: #fff;
    border: none;
    cursor: pointer;
}

/* TABLE */
.service-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 2px 12px #e0bebe22;
    border-radius: 12px;
    overflow: hidden;
}
.service-table th,
.service-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #f3caca;
    text-align: left;
}
.service-table th {
    background: #f9eaea;
    color: #800000;
}
.status-badge {
    padding: 4px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9em;
}
.status-received { background: #e5f0ff; color: #0056b3; }
.status-in-progress { background: #fffbe5; color: #b36b00; }
.status-completed { background: #e5ffe5; color: #1a8917; }

.view-btn {
    background: #800000;
    color: #fff;
    padding: 6px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

.no-data {
    text-align: center;
    color: #777;
    padding: 24px;
}

@media (max-width: 700px) {
    .summary-cards {
        flex-direction: column;
    }
}
</style>
</head>

<body>
<div class="admin-container">

<h1>Service Requests</h1>

<!-- SUMMARY CARDS -->
<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-count"><?= $todayCount ?></div>
        <div class="summary-label">Today’s Requests</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?= $receivedCount ?></div>
        <div class="summary-label">Pending</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?= $inProgressCount ?></div>
        <div class="summary-label">In Progress</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?= $completedCount ?></div>
        <div class="summary-label">Completed</div>
    </div>
</div>

<!-- FILTERS -->
<form class="filter-bar" method="get">
    <label>Category</label>
    <select name="category" onchange="this.form.submit()">
        <?php foreach ($categoryOptions as $k => $v): ?>
            <option value="<?= $k ?>" <?= $selectedCategory === $k ? 'selected' : '' ?>>
                <?= $v ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Status</label>
    <select name="status" onchange="this.form.submit()">
        <?php foreach ($statusOptions as $s): ?>
            <option value="<?= $s ?>" <?= $selectedStatus === $s ? 'selected' : '' ?>>
                <?= $s ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Apply</button>
</form>

<!-- TABLE -->
<table class="service-table">
<thead>
<tr>
    <th>Tracking ID</th>
    <th>Customer</th>
    <th>Mobile</th>
    <th>Category</th>
    <th>Amount</th>
    <th>Payment</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php if (!$requests): ?>
<tr>
    <td colspan="9" class="no-data">No service requests found.</td>
</tr>
<?php else: ?>
<?php foreach ($requests as $row): ?>
<tr>
    <td><?= htmlspecialchars($row['tracking_id']) ?></td>
    <td><?= htmlspecialchars($row['customer_name']) ?></td>
    <td><?= htmlspecialchars($row['mobile']) ?></td>
    <td><?= htmlspecialchars($row['category_slug']) ?></td>
    <td>₹<?= number_format($row['total_amount'], 2) ?></td>
    <td><?= htmlspecialchars($row['payment_status']) ?></td>
    <td>
        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['service_status'])) ?>">
            <?= htmlspecialchars($row['service_status']) ?>
        </span>
    </td>
    <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
    <td><a class="view-btn" href="view.php?id=<?= $row['id'] ?>">View</a></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>

</div>
</body>
</html>
