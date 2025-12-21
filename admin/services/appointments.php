<?php
require_once __DIR__ . '/../../config/db.php';
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

<!-- PHASE 2 – Appointment listing from service_requests (category_slug=appointment) -->

<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-count">0</div>
        <div class="summary-label">Today’s Appointments</div>
    </div>
    <div class="summary-card">
        <div class="summary-count">0</div>
        <div class="summary-label">Pending</div>
    </div>
    <div class="summary-card">
        <div class="summary-count">0</div>
        <div class="summary-label">Accepted</div>
    </div>
    <div class="summary-card">
        <div class="summary-count">0</div>
        <div class="summary-label">Completed</div>
    </div>
</div>

<div id="appointmentContainer">
    <table class="service-table">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
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
        <?php
        // PHASE 2 – Appointment listing from service_requests (category_slug=appointment)
        $query = "SELECT id, tracking_id, customer_name, mobile, email, payment_status, service_status, created_at FROM service_requests WHERE category_slug = 'appointment' AND payment_status = 'Paid' ORDER BY created_at ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows): ?>
            <tr><td colspan="8" class="no-data">No appointment bookings found.</td></tr>
        <?php else:
            foreach ($rows as $row): ?>
            <tr>
                <td><input type="checkbox" class="row-checkbox" value="<?= htmlspecialchars($row['id']) ?>"></td>
                <td><?= htmlspecialchars($row['tracking_id']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['mobile']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><span class="status-badge payment-<?= strtolower(str_replace(' ', '-', $row['payment_status'])) ?>"><?= htmlspecialchars($row['payment_status']) ?></span></td>
                <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['service_status'])) ?>"><?= htmlspecialchars($row['service_status']) ?></span></td>
                <td><?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></td>
            </tr>
        <?php endforeach;
        endif; ?>
        </tbody>
    </table>
</div>

<script>
// PHASE 2 – Select all feature
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
        });
    }
});
</script>
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
        <div class="summary-count">0</div>
        <div class="summary-label">Today’s Appointments</div>
    </div>
    <div class="summary-card">
        <div class="summary-count">0</div>
        <div class="summary-label">Pending</div>
    </div>
    <div class="summary-card">
        <div class="summary-count">0</div>
        <div class="summary-label">Accepted</div>
    </div>
    <div class="summary-card">
        <div class="summary-count">0</div>
        <div class="summary-label">Completed</div>
    </div>
</div>
<form class="filter-bar" method="get">
    <label>Date</label>
    <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>" />
    <button type="submit">Apply</button>
</form>
<table class="service-table">
<thead>
<tr>
    <th>ID</th>
    <th>Customer</th>
    <th>Mobile</th>
    <th>Type</th>
    <th>Date</th>
    <th>Time Slot</th>
    <th>Status</th>
    <th>Created</th>
    <th>Action</th>
</tr>
</thead>
<tbody id="serviceTableBody">
    <tr>
        <td colspan="9" class="no-data">No appointments found.</td>
    </tr>
</tbody>
</table>
<div id="pagination" class="pagination"></div>
</div>
</body>
</html>
