<?php
require_once __DIR__ . '/includes/top-menu.php';
// Database connection
require_once __DIR__ . '/../config/db.php';

function getCount($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $count = $stmt->fetchColumn();
    return $count !== false ? (int)$count : 0;
}

// STAT CARDS
$totalAppointments = getCount($pdo, "SELECT COUNT(*) FROM service_requests WHERE category_slug = ?", ['appointment']);
$pendingAppointments = getCount($pdo, "SELECT COUNT(*) FROM service_requests WHERE category_slug = ? AND service_status = ?", ['appointment', 'Received']);
$acceptedAppointments = getCount($pdo, "SELECT COUNT(*) FROM service_requests WHERE category_slug = ? AND service_status = ?", ['appointment', 'Accepted']);
$completedAppointments = getCount($pdo, "SELECT COUNT(*) FROM service_requests WHERE category_slug = ? AND service_status = ?", ['appointment', 'Completed']);
$totalServiceRequests = getCount($pdo, "SELECT COUNT(*) FROM service_requests WHERE category_slug != ?", ['appointment']);

// TODAY SNAPSHOT
$today = date('Y-m-d');
$todayAppointments = getCount($pdo, "SELECT COUNT(*) FROM service_requests WHERE category_slug = ? AND DATE(created_at) = ?", ['appointment', $today]);
$todayServices = getCount($pdo, "SELECT COUNT(*) FROM service_requests WHERE category_slug != ? AND DATE(created_at) = ?", ['appointment', $today]);
$todayPayments = getCount($pdo, "SELECT COUNT(*) FROM service_requests WHERE payment_status = ? AND DATE(created_at) = ?", ['Paid', $today]);

// RECENT ACTIVITY
$recentSql = "SELECT id, created_at, category_slug, customer_name, tracking_id, service_status FROM service_requests ORDER BY created_at DESC LIMIT 10";
$recentStmt = $pdo->prepare($recentSql);
$recentStmt->execute();
$recentRows = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .dashboard-header { margin-bottom: 24px; }
        .dashboard-header h1 { font-size: 2.2rem; color: #2c3e50; margin-bottom: 6px; }
        .dashboard-header p { color: #666; font-size: 1.1rem; }
        .stat-row, .mini-row, .quick-links { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 32px; }
        .stat-card, .mini-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(44,62,80,0.07); padding: 28px 24px; flex: 1 1 180px; min-width: 180px; text-align: center; transition: box-shadow 0.2s; cursor: pointer; text-decoration: none; color: inherit; }
        .stat-card:hover, .mini-card:hover { box-shadow: 0 4px 16px rgba(44,62,80,0.13); }
        .stat-card .stat-number { font-size: 2.3rem; font-weight: 700; color: #1abc9c; margin-bottom: 8px; }
        .stat-card .stat-label { font-size: 1.1rem; color: #34495e; }
        .mini-card { padding: 18px 10px; background: #f8f9fa; border: 1px solid #e1e4e8; }
        .mini-card .mini-number { font-size: 1.3rem; font-weight: 600; color: #2980b9; }
        .mini-card .mini-label { font-size: 0.98rem; color: #555; }
        .activity-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(44,62,80,0.07); }
        .activity-table th, .activity-table td { padding: 12px 10px; text-align: left; }
        .activity-table th { background: #f4f6f8; color: #2c3e50; font-weight: 600; }
        .activity-table tr { border-bottom: 1px solid #eaeaea; }
        .activity-table tr:last-child { border-bottom: none; }
        .activity-table td .view-btn { background: #1abc9c; color: #fff; border: none; border-radius: 4px; padding: 6px 14px; font-size: 0.98rem; cursor: pointer; text-decoration: none; transition: background 0.18s; }
        .activity-table td .view-btn:hover { background: #16a085; }
        .empty-state { text-align: center; color: #888; padding: 32px 0; font-size: 1.1rem; }
        .quick-links { gap: 16px; flex-wrap: wrap; }
        .quick-link-btn { background: #2980b9; color: #fff; border: none; border-radius: 6px; padding: 14px 28px; font-size: 1.08rem; font-weight: 500; cursor: pointer; text-decoration: none; margin-bottom: 8px; transition: background 0.18s; }
        .quick-link-btn:hover { background: #1abc9c; color: #fff; }
        @media (max-width: 900px) {
            .stat-row, .mini-row, .quick-links { flex-direction: column; gap: 14px; }
            .stat-card, .mini-card { min-width: 0; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <!-- SECTION A: Page Header -->
    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <p>Overview of appointments, services, and payments</p>
    </div>

    <!-- SECTION B: Stat Cards -->
    <div class="stat-row">
        <a href="services/appointments.php" class="stat-card">
            <div class="stat-number"><?php echo $totalAppointments; ?></div>
            <div class="stat-label">Total Appointments</div>
        </a>
        <a href="services/appointments.php?status=Received" class="stat-card">
            <div class="stat-number"><?php echo $pendingAppointments; ?></div>
            <div class="stat-label">Pending Appointments</div>
        </a>
        <a href="services/accepted-appointments.php" class="stat-card">
            <div class="stat-number"><?php echo $acceptedAppointments; ?></div>
            <div class="stat-label">Accepted Appointments</div>
        </a>
        <a href="services/completed-appointments.php" class="stat-card">
            <div class="stat-number"><?php echo $completedAppointments; ?></div>
            <div class="stat-label">Completed Appointments</div>
        </a>
        <a href="services/index.php" class="stat-card">
            <div class="stat-number"><?php echo $totalServiceRequests; ?></div>
            <div class="stat-label">Total Service Requests</div>
        </a>
    </div>

    <!-- SECTION C: Today Snapshot -->
    <div class="mini-row">
        <div class="mini-card">
            <div class="mini-number"><?php echo $todayAppointments; ?></div>
            <div class="mini-label">Today's Appointments</div>
        </div>
        <div class="mini-card">
            <div class="mini-number"><?php echo $todayServices; ?></div>
            <div class="mini-label">Today's Services</div>
        </div>
        <div class="mini-card">
            <div class="mini-number"><?php echo $todayPayments; ?></div>
            <div class="mini-label">Today's Payments (Paid)</div>
        </div>
    </div>

    <!-- SECTION D: Recent Activity Table -->
    <div style="margin-bottom:32px;">
        <h2 style="font-size:1.25rem;color:#2c3e50;margin-bottom:12px;">Recent Activity</h2>
        <div style="overflow-x:auto;">
        <table class="activity-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Customer Name</th>
                    <th>Tracking ID</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($recentRows) === 0): ?>
                <tr><td colspan="6" class="empty-state">No recent activity found.</td></tr>
            <?php else: ?>
                <?php foreach ($recentRows as $row): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                        <td><?php echo ($row['category_slug'] === 'appointment') ? 'Appointment' : 'Service'; ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['tracking_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['service_status']); ?></td>
                        <td>
                            <a class="view-btn" href="services/view.php?id=<?php echo $row['id']; ?><?php if ($row['category_slug'] === 'appointment') echo '&type=appointment'; ?>" target="_blank">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- SECTION E: Quick Links -->
    <div class="quick-links">
        <a href="services/appointments.php" class="quick-link-btn">Manage Appointments</a>
        <a href="services/accepted-appointments.php" class="quick-link-btn">Accepted Appointments</a>
        <a href="services/completed-appointments.php" class="quick-link-btn">Completed Appointments</a>
        <a href="services/index.php" class="quick-link-btn">Service Requests</a>
        <a href="../admin/products/index.php" class="quick-link-btn">Products</a>
        <a href="../payment-success.php" class="quick-link-btn">Payments</a>
    </div>
</div>
<script>
// Responsive tweaks if needed
</script>
</body>
</html>
