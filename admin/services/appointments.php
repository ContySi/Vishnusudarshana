
<?php
// PHASE 5 – Appointment acceptance logic
// Handle acceptance POST (backend logic)
$acceptSuccess = false;
$acceptError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_action']) && $_POST['accept_action'] === 'accept_selected') {
    $selectedIds = isset($_POST['selected_ids']) && is_array($_POST['selected_ids']) ? array_filter(array_map('intval', $_POST['selected_ids'])) : [];
    $fromTime = $_POST['fromTime'] ?? '';
    $toTime = $_POST['toTime'] ?? '';
    $selectedDate = $_POST['selected_date'] ?? '';
    if (empty($selectedIds)) {
        $acceptError = 'No appointments selected.';
    } elseif (!preg_match('/^\d{2}:\d{2}$/', $fromTime) || !preg_match('/^\d{2}:\d{2}$/', $toTime)) {
        $acceptError = 'Both time fields are required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
        $acceptError = 'Invalid date.';
    } else {
        try {
            $pdo->beginTransaction();
            $inClause = implode(',', array_fill(0, count($selectedIds), '?'));
            $params = $selectedIds;
            $params[] = $selectedDate;
            $params[] = $fromTime;
            $params[] = $toTime;
            $now = date('Y-m-d H:i:s');
            $params[] = $now;
            $updateSql = "UPDATE appointments SET status = 'accepted', assigned_date = ?, assigned_from_time = ?, assigned_to_time = ?, accepted_at = ? WHERE id IN ($inClause) AND payment_status = 'paid' AND status = 'pending'";
            $updateParams = array_merge([$selectedDate, $fromTime, $toTime, $now], $selectedIds);
            $stmt = $pdo->prepare($updateSql);
            if (!$stmt->execute($updateParams)) {
                throw new Exception('Database update failed.');
            }
            if ($stmt->rowCount() !== count($selectedIds)) {
                throw new Exception('Some appointments could not be updated (may have changed status).');
            }
            $pdo->commit();
            $acceptSuccess = true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $acceptError = 'Error: ' . $e->getMessage();
        }
    }
}
// Appointment Management Admin Page (read-only, no AJAX, no writes)
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../sidebar.php';
require_once __DIR__ . '/../../config/db.php';

// Fetch all pending paid appointment dates (for date picker)
$dateStmt = $pdo->prepare("SELECT DISTINCT preferred_date FROM appointments WHERE payment_status = 'paid' AND status = 'pending' ORDER BY preferred_date ASC");
$dateStmt->execute();
$dates = $dateStmt->fetchAll(PDO::FETCH_COLUMN);

// Determine selected date: GET param or oldest pending date
$selectedDate = '';
if (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) {
    $selectedDate = $_GET['date'];
} elseif (!empty($dates)) {
    $selectedDate = $dates[0];
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
