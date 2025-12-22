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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="admin-container">
    <h1 class="section-title" style="margin-bottom: 8px;">Admin Dashboard</h1>
    <div style="text-align:center;color:#666;font-size:1.08rem;margin-bottom:28px;">Overview of appointments, services, and payments</div>

    <!-- SECTION B: Stat Cards -->
    <div class="summary-cards">
        <a href="services/appointments.php" class="summary-card" style="text-decoration:none;">
            <div class="summary-count"><?php echo $totalAppointments; ?></div>
            <div class="summary-label">Total Appointments</div>
        </a>
        <a href="services/appointments.php?status=Received" class="summary-card" style="text-decoration:none;">
            <div class="summary-count"><?php echo $pendingAppointments; ?></div>
            <div class="summary-label">Pending Appointments</div>
        </a>
        <a href="services/accepted-appointments.php" class="summary-card" style="text-decoration:none;">
            <div class="summary-count"><?php echo $acceptedAppointments; ?></div>
            <div class="summary-label">Accepted Appointments</div>
        </a>
        <a href="services/completed-appointments.php" class="summary-card" style="text-decoration:none;">
            <div class="summary-count"><?php echo $completedAppointments; ?></div>
            <div class="summary-label">Completed Appointments</div>
        </a>
        <a href="services/index.php" class="summary-card" style="text-decoration:none;">
            <div class="summary-count"><?php echo $totalServiceRequests; ?></div>
            <div class="summary-label">Total Service Requests</div>
        </a>
    </div>

    <!-- SECTION C: Today Snapshot -->
    <div class="summary-cards" style="gap:18px;margin-bottom:32px;">
        <div class="summary-card" style="flex:1 1 0;min-width:0;">
            <div class="summary-count"><?php echo $todayAppointments; ?></div>
            <div class="summary-label">Today's Appointments</div>
        </div>
        <div class="summary-card" style="flex:1 1 0;min-width:0;">
            <div class="summary-count"><?php echo $todayServices; ?></div>
            <div class="summary-label">Today's Services</div>
        </div>
        <div class="summary-card" style="flex:1 1 0;min-width:0;">
            <div class="summary-count"><?php echo $todayPayments; ?></div>
            <div class="summary-label">Today's Payments (Paid)</div>
        </div>
    </div>

    <!-- SECTION D: Recent Activity Table -->
    <div style="margin-bottom:36px;">
        <div class="section-title" style="margin-bottom:16px;">Recent Activity</div>
        <div style="overflow-x:auto;">
            <table class="service-table">
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
                    <tr><td colspan="6" class="no-data">No recent activity found.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentRows as $row): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td><?php echo ($row['category_slug'] === 'appointment') ? 'Appointment' : 'Service'; ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['tracking_id']); ?></td>
                            <td>
                                <?php
                                    $status = strtolower($row['service_status']);
                                    $badgeClass = 'status-badge ';
                                    if ($status === 'received') $badgeClass .= 'status-received';
                                    elseif ($status === 'accepted') $badgeClass .= 'status-accepted';
                                    elseif ($status === 'completed') $badgeClass .= 'status-completed';
                                    else $badgeClass .= 'status-other';
                                ?>
                                <span class="<?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['service_status']); ?></span>
                            </td>
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
    <div class="summary-cards" style="gap:18px;flex-wrap:wrap;margin-bottom:0;">
        <a href="services/appointments.php" class="action-card" style="min-width:180px;text-decoration:none;">
            <div class="card-icon">📅</div>
            <h4>Manage Appointments</h4>
        </a>
        <a href="services/accepted-appointments.php" class="action-card" style="min-width:180px;text-decoration:none;">
            <div class="card-icon">✅</div>
            <h4>Accepted Appointments</h4>
        </a>
        <a href="services/completed-appointments.php" class="action-card" style="min-width:180px;text-decoration:none;">
            <div class="card-icon">✔️</div>
            <h4>Completed Appointments</h4>
        </a>
        <a href="services/index.php" class="action-card" style="min-width:180px;text-decoration:none;">
            <div class="card-icon">🛎️</div>
            <h4>Service Requests</h4>
        </a>
        <a href="../admin/products/index.php" class="action-card" style="min-width:180px;text-decoration:none;">
            <div class="card-icon">📦</div>
            <h4>Products</h4>
        </a>
        <a href="../payment-success.php" class="action-card" style="min-width:180px;text-decoration:none;">
            <div class="card-icon">💳</div>
            <h4>Payments</h4>
        </a>
    </div>
</div>
</body>
</html>
