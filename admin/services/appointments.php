<?php
/**
 * admin/services/appointments.php
 *
 * Appointment Management – Phase 3 (Pending-first, date-driven)
 * Data source: service_requests table
 * category_slug = 'appointment'
 */

require_once __DIR__ . '/../../config/db.php';

/* ============================================================
   PHASE 2.1 – DYNAMIC APPOINTMENT STATISTICS
   ============================================================ */

// Total appointments
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM service_requests
    WHERE category_slug = 'appointment'
      AND payment_status = 'Paid'
");
$stmt->execute();
$totalAppointments = (int)$stmt->fetchColumn();

// Today's appointments
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM service_requests
    WHERE category_slug = 'appointment'
      AND payment_status = 'Paid'
      AND DATE(created_at) = CURDATE()
");
$stmt->execute();
$todayAppointments = (int)$stmt->fetchColumn();

// Pending (Received)
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM service_requests
    WHERE category_slug = 'appointment'
      AND payment_status = 'Paid'
      AND service_status = 'Received'
");
$stmt->execute();
$pendingAppointments = (int)$stmt->fetchColumn();

// Accepted
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM service_requests
    WHERE category_slug = 'appointment'
      AND payment_status = 'Paid'
      AND service_status = 'Accepted'
");
$stmt->execute();
$acceptedAppointments = (int)$stmt->fetchColumn();

// Completed
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM service_requests
    WHERE category_slug = 'appointment'
      AND payment_status = 'Paid'
      AND service_status = 'Completed'
");
$stmt->execute();
$completedAppointments = (int)$stmt->fetchColumn();

/* ============================================================
   STEP 3.1 – FETCH PENDING APPOINTMENT DATES
   ============================================================ */

$pendingDates = [];

$stmt = $pdo->prepare("
    SELECT DATE(created_at) AS appointment_date, COUNT(*) AS total
    FROM service_requests
    WHERE category_slug = 'appointment'
      AND payment_status = 'Paid'
      AND service_status = 'Received'
    GROUP BY DATE(created_at)
    ORDER BY appointment_date ASC
");
$stmt->execute();

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $pendingDates[$row['appointment_date']] = (int)$row['total'];
}

/* ============================================================
   STEP 3.2 – AUTO-SELECT OLDEST PENDING DATE
   ============================================================ */

if (!empty($pendingDates)) {
    if (isset($_GET['date']) && isset($pendingDates[$_GET['date']])) {
        $selectedDate = $_GET['date'];
    } else {
        $selectedDate = array_key_first($pendingDates);
    }
} else {
    $selectedDate = null;
}

/* ============================================================
   STEP 3.5 – FETCH APPOINTMENTS FOR SELECTED DATE
   ============================================================ */

$appointments = [];

if ($selectedDate !== null) {
    $stmt = $pdo->prepare("
        SELECT
            id,
            tracking_id,
            customer_name,
            mobile,
            email,
            payment_status,
            service_status,
            created_at
        FROM service_requests
        WHERE category_slug = 'appointment'
          AND payment_status = 'Paid'
          AND service_status = 'Received'
          AND DATE(created_at) = ?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$selectedDate]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    align-items: center;
}
.filter-bar label {
    font-weight: 600;
}
.filter-bar input[type="date"] {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 1em;
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
.payment-paid { background: #e5ffe5; color: #1a8917; }
.no-data {
    text-align: center;
    color: #777;
    padding: 24px;
}
@media (max-width: 700px) {
    .summary-cards { flex-direction: column; }
}
</style>
</head>

<body>

<div class="admin-container">
<h1>Appointment Management</h1>

<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-count"><?= $todayAppointments ?></div>
        <div class="summary-label">Today’s Appointments</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?= $pendingAppointments ?></div>
        <div class="summary-label">Pending</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?= $acceptedAppointments ?></div>
        <div class="summary-label">Accepted</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?= $completedAppointments ?></div>
        <div class="summary-label">Completed</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?= $totalAppointments ?></div>
        <div class="summary-label">Total Appointments</div>
    </div>
</div>

<?php if (empty($pendingDates)): ?>
    <div class="no-data" style="font-size:1.2em;color:#800000;font-weight:600;">
        No pending appointments.
    </div>
<?php else: ?>

<div class="filter-bar">
    <label for="calendar-date">Pending Date</label>
    <input type="date"
           id="calendar-date"
           value="<?= htmlspecialchars($selectedDate) ?>">
</div>

<div style="margin-bottom:18px;font-weight:600;color:#800000;">
    Total appointments on this date: <?= $pendingDates[$selectedDate] ?? 0 ?>
</div>

<table class="service-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>Tracking ID</th>
            <th>Customer Name</th>
            <th>Mobile</th>
            <th>Email</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($appointments)): ?>
            <tr>
                <td colspan="8" class="no-data">No appointment bookings found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($appointments as $a): ?>
                <tr>
                    <td>
                        <input type="checkbox" class="rowCheckbox" value="<?= (int)$a['id'] ?>">
                    </td>
                    <td><?= htmlspecialchars($a['tracking_id']) ?></td>
                    <td><?= htmlspecialchars($a['customer_name']) ?></td>
                    <td><?= htmlspecialchars($a['mobile']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td><span class="status-badge payment-paid">Paid</span></td>
                    <td><span class="status-badge status-received">Pending</span></td>
                    <td><?= htmlspecialchars($a['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php endif; ?>

</div>

<script>
const allowedDates = <?= json_encode(array_keys($pendingDates)) ?>;
const calendarInput = document.getElementById('calendar-date');
const selectAll = document.getElementById('selectAll');

if (calendarInput) {
    calendarInput.addEventListener('change', function () {
        if (!allowedDates.includes(this.value)) {
            alert('No appointments on selected date');
            this.value = "<?= $selectedDate ?>";
            return;
        }
        window.location.href = '?date=' + this.value;
    });
}

if (selectAll) {
    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.rowCheckbox').forEach(cb => {
            cb.checked = selectAll.checked;
        });
    });
}
</script>

</body>
</html>
