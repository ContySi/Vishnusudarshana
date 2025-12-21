<?php
// PHASE 1 – Layout & assets only
// Data logic will be added in later phases

// No header, sidebar, or footer includes (see index.php)

?>
<link rel="stylesheet" href="/assets/css/style.css">
<!-- Add any other CSS includes from index.php here if present -->

<div class="main-content">
    <h1>Appointment Management</h1>
    <div style="font-size:1.08em;color:#666;margin-bottom:18px;">Manage paid appointment bookings</div>
    <!-- PHASE 1 – UI Skeleton with shared admin CSS & JS -->
    <div id="appointmentContainer">Loading appointments...</div>
</div>

<!-- Copy/reuse all JS includes from admin/services/index.php -->
<script src="/assets/js/language.js"></script>
<!-- Add any other JS includes from index.php here if present -->

<!-- No footer include (see index.php) -->
}

// Fetch appointments for selected date (if any date is selected)
$appointments = [];
if ($selectedDate) {
    $stmt = $pdo->prepare(
        "SELECT id, customer_name, mobile, appointment_type, preferred_date, preferred_time_slot, notes, created_at
         FROM appointments
         WHERE payment_status = 'paid' AND status = 'pending' AND preferred_date = ?
         ORDER BY preferred_time_slot ASC, created_at ASC"
    );
    $stmt->execute([$selectedDate]);
    $appointments = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <!-- Add any other CSS/JS includes from index.php here -->
</head>
<body>
<div class="main-content">
    <h1 class="page-title">Appointment Management</h1>
    <div class="subtitle" style="margin-bottom:18px;color:#555;font-size:1.08em;">
        Manage paid appointment bookings
    </div>
    <div id="appointmentList">
        <form method="get" action="appointments.php" style="margin-bottom:18px;">
            <label for="dateInput"><strong>Select Date:</strong></label>
            <input type="date" id="dateInput" name="date" value="<?= htmlspecialchars($selectedDate) ?>"
                <?php if (!empty($dates)): ?>
                    min="<?= htmlspecialchars($dates[0]) ?>" max="<?= htmlspecialchars(end($dates)) ?>"
                <?php endif; ?>
            >
            <button type="submit" style="margin-left:8px;">Go</button>
        </form>
        <?php if ($acceptSuccess): ?>
            <div style="background:#e5ffe5;color:#1a8917;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;">Appointments accepted successfully.</div>
        <?php elseif ($acceptError): ?>
            <div style="background:#ffe5e5;color:#b00020;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;">Error: <?= htmlspecialchars($acceptError) ?></div>
        <?php endif; ?>
        <form method="post" id="acceptForm" autocomplete="off">
            <input type="hidden" name="accept_action" value="accept_selected">
            <input type="hidden" name="selected_date" value="<?= htmlspecialchars($selectedDate) ?>">
            <table class="service-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Mobile</th>
                        <th>Appointment Type</th>
                        <th>Preferred Date</th>
                        <th>Preferred Time Slot</th>
                        <th>Notes</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($selectedDate && empty($appointments)): ?>
                    <tr><td colspan="9" class="no-data">No pending appointments</td></tr>
                <?php elseif ($selectedDate): ?>
                    <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><input type="checkbox" class="row-checkbox" name="selected_ids[]" value="<?= htmlspecialchars($a['id']) ?>"></td>
                        <td><?= htmlspecialchars($a['id']) ?></td>
                        <td><?= htmlspecialchars($a['customer_name']) ?></td>
                        <td><?= htmlspecialchars($a['mobile']) ?></td>
                        <td><?= htmlspecialchars($a['appointment_type']) ?></td>
                        <td><?= htmlspecialchars($a['preferred_date']) ?></td>
                        <td><?= htmlspecialchars($a['preferred_time_slot']) ?></td>
                        <td><?= htmlspecialchars($a['notes']) ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($a['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top:22px;">
                <button id="acceptBtn" type="button" disabled style="background:#800000;color:#fff;padding:10px 24px;border-radius:8px;font-size:1.08em;font-weight:600;cursor:pointer;">Accept Selected Appointments</button>
            </div>
            <div id="acceptSection" style="display:none;margin-top:18px;background:#f9eaea;padding:18px 16px;border-radius:10px;max-width:420px;">
                <div style="margin-bottom:12px;font-weight:600;">Allocate Service Time</div>
                <div style="margin-bottom:10px;">
                    <label for="fromTime">From Time:</label>
                    <input type="time" id="fromTime" name="fromTime" required style="margin-left:8px;">
                </div>
                <div style="margin-bottom:16px;">
                    <label for="toTime">To Time:</label>
                    <input type="time" id="toTime" name="toTime" required style="margin-left:24px;">
                </div>
            </div>
        </form>
        <script>
        // PHASE 5 – Appointment acceptance logic
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const selectAll = document.getElementById('selectAll');
            const acceptBtn = document.getElementById('acceptBtn');
            const acceptSection = document.getElementById('acceptSection');
            const acceptForm = document.getElementById('acceptForm');
            const fromTime = document.getElementById('fromTime');
            const toTime = document.getElementById('toTime');
            function updateAcceptBtn() {
                const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                acceptBtn.disabled = !anyChecked;
            }
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateAcceptBtn);
            });
            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
                    updateAcceptBtn();
                });
            }
            acceptBtn.addEventListener('click', function () {
                acceptSection.style.display = 'block';
            });
            acceptForm.addEventListener('submit', function (e) {
                // Validate before submit
                const checked = Array.from(checkboxes).some(cb => cb.checked);
                if (!checked) {
                    e.preventDefault();
                    alert('Please select at least one appointment.');
                    return false;
                }
                if (!fromTime.value || !toTime.value) {
                    e.preventDefault();
                    alert('Both time fields are required.');
                    return false;
                }
                // Allow form to submit
            });
            updateAcceptBtn();
        });
        </script>
    </div>
</div>
<?php require_once __DIR__ . '/../../footer.php'; ?>
</body>
</html>
