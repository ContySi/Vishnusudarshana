
<?php
// admin/services/appointmentmanagement.php
require_once __DIR__ . '/../../config/db.php';

// ==========================
// AJAX REQUEST HANDLER FIRST
// ==========================
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $selectedDate = $_GET['date'] ?? '';
    $appointments = [];
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM appointments 
            WHERE payment_status = 'paid'
              AND status = 'pending'
              AND preferred_date = ?
            ORDER BY preferred_time_slot ASC, id ASC
        ");
        $stmt->execute([$selectedDate]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    ?>
    <style>
    <?php include __DIR__ . '/../../assets/css/style.css'; ?>
    </style>
    <form id="acceptAppointmentsForm">
        <table class="service-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Preferred Time</th>
                    <th>Notes</th>
                    <th>Service Start</th>
                    <th>Service End</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$appointments): ?>
                <tr>
                    <td colspan="12" class="no-data">No appointments for this date.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= $a['id'] ?></td>
                        <td><?= htmlspecialchars($a['customer_name']) ?></td>
                        <td><?= htmlspecialchars($a['mobile']) ?></td>
                        <td><?= htmlspecialchars($a['email']) ?></td>
                        <td><?= ucfirst($a['appointment_type']) ?></td>
                        <td><?= htmlspecialchars($a['preferred_time_slot']) ?></td>
                        <td><?= nl2br(htmlspecialchars($a['notes'])) ?></td>
                        <td>
                            <input type="time" name="from[<?= $a['id'] ?>]" style="width:90px;">
                        </td>
                        <td>
                            <input type="time" name="to[<?= $a['id'] ?>]" style="width:90px;">
                        </td>
                        <td><span class="status-badge payment-paid">Paid</span></td>
                        <td><span class="status-badge status-pending">Pending</span></td>
                        <td><?= date('d-m-Y H:i', strtotime($a['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <?php if ($appointments): ?>
            <div style="margin-top:18px;text-align:right;">
                <button type="submit" class="view-btn">Accept Appointments</button>
            </div>
        <?php endif; ?>
    </form>
    <script src="../../assets/js/language.js"></script>
    <?php
    exit;
}

/**
 * ==========================
 * NORMAL PAGE RENDER
 * ==========================
 */


?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php include_once '../../header.php'; ?>
<?php include_once '../sidebar.php'; ?>

/* Fetch pending paid appointment dates */
$stmt = $pdo->prepare("
    SELECT preferred_date, COUNT(*) AS count
    FROM appointments
    WHERE payment_status = 'paid'
      AND status = 'pending'
    GROUP BY preferred_date
    ORDER BY preferred_date ASC
");
$stmt->execute();
$dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedDate = $dates[0]['preferred_date'] ?? '';
if (isset($_GET['date'])) {
    foreach ($dates as $d) {
        if ($d['preferred_date'] === $_GET['date']) {
            $selectedDate = $_GET['date'];
            break;
        }
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="page-title">Appointment Management</h1>

        <div style="margin-bottom:18px;">
            <?php if ($dates): ?>
                <label><strong>Select Date:</strong></label>
                <select id="dateSelect" style="padding:6px 12px;border-radius:6px;">
                    <?php foreach ($dates as $d): ?>
                        <option value="<?= $d['preferred_date'] ?>" <?= $selectedDate === $d['preferred_date'] ? 'selected' : '' ?>>
                            <?= date('d M Y', strtotime($d['preferred_date'])) ?> (<?= $d['count'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <span>No pending appointments.</span>
            <?php endif; ?>
        </div>

        <div id="appointmentTableContainer">
            <!-- AJAX table loads here -->
        </div>
    </div>
</div>

document.addEventListener('DOMContentLoaded', function () {

    function loadAppointments(date) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'appointmentmanagement.php?ajax=1&date=' + encodeURIComponent(date));
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById('appointmentTableContainer').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    var dateSelect = document.getElementById('dateSelect');
    if (dateSelect) {
        loadAppointments(dateSelect.value);

        dateSelect.addEventListener('change', function () {
            loadAppointments(this.value);
        });
    }

    document.addEventListener('submit', function (e) {
        if (e.target.id === 'acceptAppointmentsForm') {
            e.preventDefault();
            alert('Acceptance logic will be added next (Phase-1 continuation).');
        }
    });
});
<script src="../../assets/js/language.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function loadAppointments(date) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'appointmentmanagement.php?ajax=1&date=' + encodeURIComponent(date));
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById('appointmentTableContainer').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
    var dateSelect = document.getElementById('dateSelect');
    if (dateSelect) {
        loadAppointments(dateSelect.value);
        dateSelect.addEventListener('change', function () {
            loadAppointments(this.value);
        });
    }
    document.addEventListener('submit', function (e) {
        if (e.target.id === 'acceptAppointmentsForm') {
            e.preventDefault();
            alert('Acceptance logic will be added next (Phase-1 continuation).');
        }
    });
});
</script>

<?php include_once '../../footer.php'; ?>
</body>
</html>
