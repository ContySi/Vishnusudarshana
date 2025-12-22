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
<div class="admin-container" style="max-width:1100px;margin:0 auto;padding:24px 12px;">
    <h1 style="color:#800000;margin-bottom:18px;">Admin Dashboard</h1>
    <div style="text-align:center;color:#666;font-size:1.08rem;margin-bottom:28px;">Overview of appointments, services, and payments</div>

    <!-- SECTION B: Stat Cards -->
    <div class="summary-cards" style="gap:18px;margin-bottom:24px;flex-wrap:wrap;">
        <div class="summary-card" onclick="window.location.href='services/appointments.php'" style="cursor:pointer;">
            <div class="summary-count" style="font-size:2.2em;font-weight:700;color:#800000;"><?php echo $totalAppointments; ?></div>
            <div class="summary-label" style="font-size:1em;color:#444;">Total Appointments</div>
        </div>
        <div class="summary-card" onclick="window.location.href='services/appointments.php?status=Received'" style="cursor:pointer;">
            <div class="summary-count" style="font-size:2.2em;font-weight:700;color:#800000;"><?php echo $pendingAppointments; ?></div>
            <div class="summary-label" style="font-size:1em;color:#444;">Pending Appointments</div>
        </div>
        <div class="summary-card" onclick="window.location.href='services/accepted-appointments.php'" style="cursor:pointer;">
            <div class="summary-count" style="font-size:2.2em;font-weight:700;color:#800000;"><?php echo $acceptedAppointments; ?></div>
            <div class="summary-label" style="font-size:1em;color:#444;">Accepted Appointments</div>
        </div>
        <div class="summary-card" onclick="window.location.href='services/completed-appointments.php'" style="cursor:pointer;">
            <div class="summary-count" style="font-size:2.2em;font-weight:700;color:#800000;"><?php echo $completedAppointments; ?></div>
            <div class="summary-label" style="font-size:1em;color:#444;">Completed Appointments</div>
        </div>
        <div class="summary-card" onclick="window.location.href='services/index.php'" style="cursor:pointer;">
            <div class="summary-count" style="font-size:2.2em;font-weight:700;color:#800000;"><?php echo $totalServiceRequests; ?></div>
            <div class="summary-label" style="font-size:1em;color:#444;">Total Service Requests</div>
        </div>
    </div>

    <!-- SECTION C: Today Snapshot -->
    <div class="summary-cards" style="gap:18px;margin-bottom:32px;flex-wrap:wrap;">
        <div class="summary-card">
            <div class="summary-count" style="font-size:2.2em;font-weight:700;color:#800000;"><?php echo $todayAppointments; ?></div>
            <div class="summary-label" style="font-size:1em;color:#444;">Today's Appointments</div>
        </div>
        <div class="summary-card">
            <div class="summary-count" style="font-size:2.2em;font-weight:700;color:#800000;"><?php echo $todayServices; ?></div>
            <div class="summary-label" style="font-size:1em;color:#444;">Today's Services</div>
        </div>
        <div class="summary-card">
            <div class="summary-count" style="font-size:2.2em;font-weight:700;color:#800000;"><?php echo $todayPayments; ?></div>
            <div class="summary-label" style="font-size:1em;color:#444;">Today's Payments (Paid)</div>
        </div>
    </div>

    <!-- SECTION D: Recent Activity Table -->
    <div style="margin-bottom:36px;">
        <div class="section-title" style="font-size:22px;color:#800000;font-weight:700;margin-bottom:16px;text-align:center;">Recent Activity</div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <input type="text" id="searchInput" placeholder="Search..." style="padding:7px 12px;border-radius:6px;border:1px solid #ccc;font-size:1em;max-width:220px;">
            <div style="font-size:0.98em;color:#888;">Showing max 10 records</div>
        </div>
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
                <tbody id="activityTableBody">
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
        <div class="summary-card" onclick="window.location.href='services/appointments.php'" style="cursor:pointer;min-width:180px;">
            <div class="card-icon">📅</div>
            <div class="summary-label">Manage Appointments</div>
        </div>
        <div class="summary-card" onclick="window.location.href='services/accepted-appointments.php'" style="cursor:pointer;min-width:180px;">
            <div class="card-icon">✅</div>
            <div class="summary-label">Accepted Appointments</div>
        </div>
        <div class="summary-card" onclick="window.location.href='services/completed-appointments.php'" style="cursor:pointer;min-width:180px;">
            <div class="card-icon">✔️</div>
            <div class="summary-label">Completed Appointments</div>
        </div>
        <div class="summary-card" onclick="window.location.href='services/index.php'" style="cursor:pointer;min-width:180px;">
            <div class="card-icon">🛎️</div>
            <div class="summary-label">Service Requests</div>
        </div>
        <div class="summary-card" onclick="window.location.href='../admin/products/index.php'" style="cursor:pointer;min-width:180px;">
            <div class="card-icon">📦</div>
            <div class="summary-label">Products</div>
        </div>
        <div class="summary-card" onclick="window.location.href='../payment-success.php'" style="cursor:pointer;min-width:180px;">
            <div class="card-icon">💳</div>
            <div class="summary-label">Payments</div>
        </div>
    </div>
</div>
<script>
// Table search filter
document.getElementById('searchInput').addEventListener('keyup', function() {
    var filter = this.value.toLowerCase();
    var rows = document.querySelectorAll('#activityTableBody tr');
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
    });
});
</script>
</body>
</html>
