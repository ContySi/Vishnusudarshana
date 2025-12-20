<?php
// admin/services/appointmentmanagement.php

require_once __DIR__ . '/../../config/db.php';

/**
 * ==========================
 * AJAX REQUEST HANDLER FIRST
 * ==========================
 */
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
    <?php
    exit;
}

/**
 * ==========================
 * NORMAL PAGE RENDER
 * ==========================
 */



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


<style>
body {
    font-family: Arial, sans-serif;
    background: #f7f7fa;
    margin: 0;
}
.main-content {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 12px;
}
h1.page-title {
    color: #800000;
    margin-bottom: 18px;
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
.status-pending { background: #fffbe5; color: #b36b00; }
.payment-paid { background: #e5ffe5; color: #1a8917; }
.no-data {
    text-align: center;
    color: #777;
    padding: 24px;
}
.view-btn {
    background: #800000;
    color: #fff;
    padding: 6px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
@media (max-width: 700px) {
    .main-content { padding: 8px 2px 16px 2px; border-radius: 0; }
}
</style>

<div class="main-content">
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
<script src="../../assets/js/language.js"></script>
</body>
</html>
