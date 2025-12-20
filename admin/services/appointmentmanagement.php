<?php
// admin/services/appointmentmanagement.php
// Structure copied from admin/services/index.php, business logic to be added later

include_once '../../header.php';
include_once '../sidebar.php';
    <?php
    exit;
}
// ...existing code...
?>
                </div>
            </div>
        </div>
    </div>
    <?php include_once '../../footer.php'; ?>
    <!-- JS Includes -->
    <script src="../../assets/js/language.js"></script>
    <!-- Add any other JS includes from index.php here -->
</body>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="page-title">Appointment Management</h1>
<?php
require_once __DIR__ . '/../../config/db.php';

// Fetch all pending, paid appointments grouped by preferred_date
$stmt = $pdo->prepare("SELECT preferred_date, COUNT(*) as count FROM appointments WHERE payment_status = 'paid' AND status = 'pending' GROUP BY preferred_date ORDER BY preferred_date ASC");
$stmt->execute();
$dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedDate = '';
if ($dates) {
    $selectedDate = $dates[0]['preferred_date']; // Default to oldest
    if (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) {
        foreach ($dates as $d) {
            if ($d['preferred_date'] === $_GET['date']) {
                $selectedDate = $_GET['date'];
                break;
            }
        }
    }
}
?>
<!-- Date Dropdown -->
<div style="margin-bottom:18px;">
    <?php if ($dates): ?>
        <label for="dateSelect" style="font-weight:600;margin-right:8px;">Select Date:</label>
        <select id="dateSelect" style="padding:6px 12px;border-radius:6px;">
            <?php foreach ($dates as $d): ?>
                <option value="<?= htmlspecialchars($d['preferred_date']) ?>" <?= $selectedDate === $d['preferred_date'] ? 'selected' : '' ?>>
                    <?= date('d M Y', strtotime($d['preferred_date'])) ?> (<?= $d['count'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    <?php else: ?>
        <span style="color:#777;">No pending paid appointments.</span>
    <?php endif; ?>
</div>

<!-- Appointment Table -->
<div id="appointmentTableContainer">
<?php
if ($selectedDate) {
    $stmt2 = $pdo->prepare("SELECT * FROM appointments WHERE payment_status = 'paid' AND status = 'pending' AND preferred_date = ? ORDER BY preferred_time_slot ASC, id ASC");
    $stmt2->execute([$selectedDate]);
    $appointments = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} else {
    $appointments = [];
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
                <tr><td colspan="12" class="no-data">No appointments for this date.</td></tr>
            <?php else: ?>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= $a['id'] ?></td>
                        <td><?= htmlspecialchars($a['customer_name']) ?></td>
                        <td><?= htmlspecialchars($a['mobile']) ?></td>
                        <td><?= htmlspecialchars($a['email']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($a['appointment_type'])) ?></td>
                        <td><?= htmlspecialchars($a['preferred_time_slot']) ?></td>
                        <td><?= nl2br(htmlspecialchars($a['notes'])) ?></td>
                        <td>
                            <input type="time" name="from[<?= $a['id'] ?>]" class="service-time-input" style="width:90px;" value="<?= $a['time_from'] ? htmlspecialchars(substr($a['time_from'],0,5)) : '' ?>">
                        </td>
                        <td>
                            <input type="time" name="to[<?= $a['id'] ?>]" class="service-time-input" style="width:90px;" value="<?= $a['time_to'] ? htmlspecialchars(substr($a['time_to'],0,5)) : '' ?>">
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
            <button type="submit" class="view-btn" style="font-size:1em;">Accept Appointments</button>
        </div>
    <?php endif; ?>
    </form>
</div>

<script>
// AJAX date change handler (reuse admin/services/index.php style)
document.addEventListener('DOMContentLoaded', function() {
    var dateSelect = document.getElementById('dateSelect');
    if (dateSelect) {
        dateSelect.addEventListener('change', function() {
            var date = dateSelect.value;
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'appointmentmanagement.php?date=' + encodeURIComponent(date) + '&ajax=1', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var container = document.getElementById('appointmentTableContainer');
                    if (container) container.innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        });
    }

    // Accept Appointments button (no business logic yet)
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (form && form.id === 'acceptAppointmentsForm') {
            e.preventDefault();
            // Placeholder: show alert for now
            alert('Accept Appointments: This will process the selected appointments. (Business logic not implemented)');
        }
    });
});
</script>

<?php
// AJAX endpoint: only render table if ajax=1
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    ob_end_clean();
    header('Content-Type: text/html; charset=utf-8');
    if ($selectedDate) {
        $stmt2 = $pdo->prepare("SELECT * FROM appointments WHERE payment_status = 'paid' AND status = 'pending' AND preferred_date = ? ORDER BY preferred_time_slot ASC, id ASC");
        $stmt2->execute([$selectedDate]);
        $appointments = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $appointments = [];
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
                <tr><td colspan="12" class="no-data">No appointments for this date.</td></tr>
            <?php else: ?>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= $a['id'] ?></td>
                        <td><?= htmlspecialchars($a['customer_name']) ?></td>
                        <td><?= htmlspecialchars($a['mobile']) ?></td>
                        <td><?= htmlspecialchars($a['email']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($a['appointment_type'])) ?></td>
                        <td><?= htmlspecialchars($a['preferred_time_slot']) ?></td>
                        <td><?= nl2br(htmlspecialchars($a['notes'])) ?></td>
                        <td>
                            <input type="time" name="from[<?= $a['id'] ?>]" class="service-time-input" style="width:90px;" value="<?= $a['time_from'] ? htmlspecialchars(substr($a['time_from'],0,5)) : '' ?>">
                        </td>
                        <td>
                            <input type="time" name="to[<?= $a['id'] ?>]" class="service-time-input" style="width:90px;" value="<?= $a['time_to'] ? htmlspecialchars(substr($a['time_to'],0,5)) : '' ?>">
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
            <button type="submit" class="view-btn" style="font-size:1em;">Accept Appointments</button>
        </div>
    <?php endif; ?>
    </form>
    <?php
    exit;
}
                </div>
            </div>
        </div>
    </div>
    <?php include_once '../../footer.php'; ?>
    <!-- JS Includes -->
    <script src="../../assets/js/language.js"></script>
    <!-- Add any other JS includes from index.php here -->
</body>
</html>
