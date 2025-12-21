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
    <!-- PHASE 2 – Read-only appointment listing from appointments table -->
    <div id="appointmentContainer">
        <?php
        // PHASE 2 – Read-only appointment listing from appointments table
        $stmt = $pdo->prepare("SELECT id, customer_name, mobile, appointment_type, preferred_date, preferred_time_slot, notes, payment_status, created_at FROM appointments WHERE payment_status = 'paid' AND status = 'pending' ORDER BY preferred_date ASC, preferred_time_slot ASC, created_at ASC");
        $stmt->execute();
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <table class="service-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Appointment ID</th>
                    <th>Customer Name</th>
                    <th>Mobile Number</th>
                    <th>Appointment Type</th>
                    <th>Preferred Date</th>
                    <th>Preferred Time Slot</th>
                    <th>Notes</th>
                    <th>Payment Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($appointments)): ?>
                <tr><td colspan="10" class="no-data">No pending paid appointments found.</td></tr>
            <?php else: ?>
                <?php foreach ($appointments as $a): ?>
                <tr>
                    <td><input type="checkbox" class="row-checkbox" value="<?= htmlspecialchars($a['id']) ?>"></td>
                    <td><?= htmlspecialchars($a['id']) ?></td>
                    <td><?= htmlspecialchars($a['customer_name']) ?></td>
                    <td><?= htmlspecialchars($a['mobile']) ?></td>
                    <td><?= htmlspecialchars($a['appointment_type']) ?></td>
                    <td><?= htmlspecialchars($a['preferred_date']) ?></td>
                    <td><?= htmlspecialchars($a['preferred_time_slot']) ?></td>
                    <td><?= htmlspecialchars($a['notes']) ?></td>
                    <td><span class="status-badge payment-<?= strtolower(htmlspecialchars($a['payment_status'])) ?>"><?= htmlspecialchars(ucfirst($a['payment_status'])) ?></span></td>
                    <td><?= date('d-m-Y H:i', strtotime($a['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
