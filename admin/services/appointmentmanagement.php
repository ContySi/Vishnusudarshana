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
        <div id="serviceTimeFrame" style="display:none; margin-bottom:16px;">
            <label style="font-weight:600;">Service From:</label>
            <input type="time" id="serviceFrom" name="service_from" style="margin-right:18px;">
            <label style="font-weight:600;">Service To:</label>
            <input type="time" id="serviceTo" name="service_to">
        </div>
        <table class="service-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
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
                    <td colspan="13" class="no-data">No appointments for this date.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><input type="checkbox" class="row-checkbox" name="selected[]" value="<?= $a['id'] ?>"></td>
                        <td><?= $a['id'] ?></td>
                        <td><?= htmlspecialchars($a['customer_name']) ?></td>
                        <td><?= htmlspecialchars($a['mobile']) ?></td>
                        <td><?= htmlspecialchars($a['email']) ?></td>
                        <td><?= ucfirst($a['appointment_type']) ?></td>
                        <td><?= htmlspecialchars($a['preferred_time_slot']) ?></td>
                        <td><?= nl2br(htmlspecialchars($a['notes'])) ?></td>
                        <td></td>
                        <td></td>
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
            <label for="dateInput"><strong>Select Date:</strong></label>
            <input type="date" id="dateInput" style="padding:6px 12px;border-radius:6px;" value="<?= htmlspecialchars($selectedDate) ?>" min="<?= htmlspecialchars($dates[0]['preferred_date']) ?>" max="<?= htmlspecialchars($dates[count($dates)-1]['preferred_date']) ?>">
        <?php else: ?>
            <span>No pending appointments.</span>
        <?php endif; ?>
    </div>
    <div id="appointmentTableContainer">
        <!-- AJAX table loads here -->
    </div>
</div>

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

    function bindSelectAll() {
        var selectAll = document.getElementById('selectAll');
        var checkboxes = document.querySelectorAll('.row-checkbox');
        var timeFrame = document.getElementById('serviceTimeFrame');
        function updateTimeFrameVisibility() {
            if (timeFrame) {
                var anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                timeFrame.style.display = anyChecked ? '' : 'none';
            }
        }
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(function (cb) { cb.checked = selectAll.checked; });
                updateTimeFrameVisibility();
            });
        }
        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                if (!cb.checked && selectAll) selectAll.checked = false;
                else if (selectAll && Array.from(checkboxes).every(x => x.checked)) selectAll.checked = true;
                updateTimeFrameVisibility();
            });
        });
        updateTimeFrameVisibility();
    }
    document.addEventListener('submit', function (e) {
        if (e.target.id === 'acceptAppointmentsForm') {
            var checkboxes = document.querySelectorAll('.row-checkbox');
            var selectedIds = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            var anyChecked = selectedIds.length > 0;
            var from = document.getElementById('serviceFrom');
            var to = document.getElementById('serviceTo');
            var dateInput = document.getElementById('dateInput');
            var selectedDate = dateInput ? dateInput.value : '';
            if (!anyChecked) {
                e.preventDefault();
                alert('Please select at least one appointment.');
                return;
            }
            if (!from.value || !to.value) {
                e.preventDefault();
                alert('Please select both Service From and Service To times.');
                return;
            }
            e.preventDefault();
            var msg = 'Selected IDs: ' + selectedIds.join(', ') + '\nDate: ' + selectedDate + '\nFrom: ' + from.value + '\nTo: ' + to.value;
            alert(msg);
            // console.log({selectedIds, selectedDate, from: from.value, to: to.value});
        }
    });
    // Re-bind after AJAX load
    var container = document.getElementById('appointmentTableContainer');
    var observer = new MutationObserver(function() { bindSelectAll(); });
    if (container) observer.observe(container, { childList: true, subtree: true });
    bindSelectAll();
}); 
</script>
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
    var dateInput = document.getElementById('dateInput');
    if (dateInput) {
        loadAppointments(dateInput.value);
        dateInput.addEventListener('change', function () {
            if (this.value) loadAppointments(this.value);
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


<script src="../../assets/js/language.js"></script>
</body>
</html>
