
<?php

require_once __DIR__ . '/../../config/db.php';

// PHASE 2.1 – Dynamic appointment statistics from service_requests


// Total Appointments
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE category_slug = 'appointment' AND payment_status = 'Paid'");
$stmtTotal->execute();
$totalAppointments = (int)$stmtTotal->fetchColumn();

// Today's Appointments
$stmtToday = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE category_slug = 'appointment' AND payment_status = 'Paid' AND DATE(created_at) = CURDATE()");
$stmtToday->execute();
$todayAppointments = (int)$stmtToday->fetchColumn();

// Pending (Received)
$stmtPending = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE category_slug = 'appointment' AND payment_status = 'Paid' AND service_status = 'Received'");
$stmtPending->execute();
$pendingAppointments = (int)$stmtPending->fetchColumn();

// Accepted
$stmtAccepted = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE category_slug = 'appointment' AND payment_status = 'Paid' AND service_status = 'Accepted'");
$stmtAccepted->execute();
$acceptedAppointments = (int)$stmtAccepted->fetchColumn();

// Completed
$stmtCompleted = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE category_slug = 'appointment' AND payment_status = 'Paid' AND service_status = 'Completed'");
$stmtCompleted->execute();
$completedAppointments = (int)$stmtCompleted->fetchColumn();

// PHASE 2 – Appointment listing from service_requests (category_slug=appointment)

// Fetch appointment bookings from service_requests table, filtered by selected date if set
$sql = "
    SELECT id, tracking_id, customer_name, mobile, email, payment_status, service_status, created_at
    FROM service_requests
    WHERE category_slug = 'appointment' AND payment_status = 'Paid'";

// Filter by selected date if available
if ($selectedDate !== null) {
    $sql .= " AND DATE(created_at) = :selectedDate";
}

// Optional: Add service_status filter if it exists
if (isset($selectedStatus) && in_array($selectedStatus, ['Received', 'Pending'])) {
    $sql .= " AND service_status IN ('Received', 'Pending')";
}

$sql .= " ORDER BY created_at ASC";

$stmt = $pdo->prepare($sql);
if ($selectedDate !== null) {
    $stmt->bindValue(':selectedDate', $selectedDate);
}
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);


// STEP 3.2 – Auto select oldest pending appointment date
// If a valid date is provided in the query string and exists in $pendingDates, use it.
// Otherwise, default to the oldest pending date. If none, set to null.
if (isset($_GET['date']) && isset($pendingDates[$_GET['date']])) {
    $selectedDate = $_GET['date'];
} elseif (!empty($pendingDates)) {
    // Fallback: select the oldest pending date (first key in sorted array)
    $selectedDate = array_key_first($pendingDates);
} else {
    // No pending dates available
    $selectedDate = null;
}
// STEP 3.1 – Fetch pending appointment dates (no UI changes yet)
// This block fetches all dates that have PENDING appointments (service_status = 'Received')
// and stores them as an associative array: [ 'YYYY-MM-DD' => count ]
$pendingDates = [];
$stmtPendingDates = $pdo->prepare("SELECT DATE(created_at) AS appointment_date, COUNT(*) AS total FROM service_requests WHERE category_slug = 'appointment' AND payment_status = 'Paid' AND service_status = 'Received' GROUP BY DATE(created_at) ORDER BY appointment_date ASC");
$stmtPendingDates->execute();
foreach ($stmtPendingDates->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $pendingDates[$row['appointment_date']] = (int)$row['total'];
}


// STEP 3.6 – Auto fallback if selected date has no appointments
// If no appointments for selected date and there are still pending dates, fallback to next oldest date (one time only)
if (empty($appointments) && $selectedDate !== null && !empty($pendingDates)) {
    // Find the next oldest date after the current $selectedDate
    $dates = array_keys($pendingDates);
    $currentIndex = array_search($selectedDate, $dates);
    // Defensive: if not found or last date, fallback to first date
    if ($currentIndex === false || $currentIndex + 1 >= count($dates)) {
        $fallbackDate = $dates[0];
    } else {
        $fallbackDate = $dates[$currentIndex + 1];
    }
    // Only fallback if it's a different date
    if ($fallbackDate !== $selectedDate) {
        $selectedDate = $fallbackDate;
        // Re-run the query for the new date
        $sql = "
            SELECT id, tracking_id, customer_name, mobile, email, payment_status, service_status, created_at
            FROM service_requests
            WHERE category_slug = 'appointment' AND payment_status = 'Paid' AND DATE(created_at) = :selectedDate
            ORDER BY created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':selectedDate', $selectedDate);
        $stmt->execute();
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointment Management</title>
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
.service-table tbody tr:hover {
    background: #f3f7fa;
    cursor: pointer;
}
.status-badge {
    padding: 4px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9em;
    display: inline-block;
    min-width: 80px;
    text-align: center;
}
.status-received { background: #e5f0ff; color: #0056b3; }
.status-in-progress { background: #fffbe5; color: #b36b00; }
.status-completed { background: #e5ffe5; color: #1a8917; }
.status-cancelled { background: #ffeaea; color: #c00; }
.payment-paid { background: #e5ffe5; color: #1a8917; }
.payment-pending { background: #f7f7f7; color: #b36b00; }
.payment-failed { background: #ffeaea; color: #c00; }
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
.pagination {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin: 18px 0;
    flex-wrap: wrap;
}
.pagination a,
.pagination span {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    color: #333;
}
.pagination .page-link.current {
    background: #800000;
    color: #fff;
    border-color: #800000;
}
.pagination .page-link.disabled {
    opacity: 0.4;
    cursor: not-allowed;
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
<h1>Appointment Management</h1>
<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-count"><?php echo $todayAppointments; ?></div>
        <div class="summary-label">Today’s Appointments</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?php echo $pendingAppointments; ?></div>
        <div class="summary-label">Pending</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?php echo $acceptedAppointments; ?></div>
        <div class="summary-label">Accepted</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?php echo $completedAppointments; ?></div>
        <div class="summary-label">Completed</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?php echo $totalAppointments; ?></div>
        <div class="summary-label">Total Appointments</div>
    </div>
</div>
<?php if (empty($pendingDates)): ?>
    <div style="margin: 32px 0; text-align: center; font-size: 1.2em; color: #800000; font-weight: 600;">No pending appointments.</div>
<?php else: ?>
    <!-- STEP 3.3 – Calendar UI for pending appointments (no filtering yet) -->
    <div class="filter-bar" style="margin-bottom: 10px;">
        <label for="calendar-date">Pending Date</label>
        <input type="date" id="calendar-date" value="<?= htmlspecialchars($selectedDate ?? '') ?>" />
    </div>
    <?php if ($selectedDate): ?>
        <div style="margin-bottom: 18px; font-weight: 600; color: #800000;">
            Total appointments on this date: <?= $pendingDates[$selectedDate] ?? 0 ?>
        </div>
    <?php endif; ?>
    <div id="appointmentContainer">
        <table class="service-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" /></th>
                    <th>Tracking ID</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Payment Status</th>
                    <th>Service Status</th>
                    <th>Created Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr>
                        <td colspan="8" class="no-data">No appointment bookings found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><input type="checkbox" value="<?= $appointment['id'] ?>" /></td>
                            <td><?= htmlspecialchars($appointment['tracking_id']) ?></td>
                            <td><?= htmlspecialchars($appointment['customer_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['mobile']) ?></td>
                            <td><?= htmlspecialchars($appointment['email']) ?></td>
                            <td class="status-<?= strtolower($appointment['payment_status']) ?>"><?= htmlspecialchars($appointment['payment_status']) ?></td>
                            <td class="status-<?= strtolower($appointment['service_status']) ?>"><?= htmlspecialchars($appointment['service_status']) ?></td>
                            <td><?= htmlspecialchars($appointment['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div id="pagination" class="pagination"></div>
<?php endif; ?>
</div>
<script>
// Allowed dates for pending appointments (from PHP)
const allowedDates = <?= json_encode(array_keys($pendingDates)) ?>;
const selectedDate = <?= json_encode($selectedDate) ?>;

// Calendar validation logic
const calendarInput = document.getElementById('calendar-date');
let lastValidDate = selectedDate;
calendarInput.addEventListener('change', function() {
    if (!allowedDates.includes(calendarInput.value)) {
        alert('No appointments on selected date');
        calendarInput.value = lastValidDate;
    } else {
        lastValidDate = calendarInput.value;
    }
});

// Select/Deselect all checkboxes
const selectAllCheckbox = document.getElementById('selectAll');
selectAllCheckbox.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('#appointmentContainer input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
});
</script>
</body>
</html>
