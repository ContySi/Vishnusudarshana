<?php
require_once __DIR__ . '/../../config/db.php';

// Service Status Filter
$statusOptions = ['All', 'Received', 'In Progress', 'Completed'];
$selectedStatus = $_GET['status'] ?? 'All';
$where = '';
$params = [];
if ($selectedStatus !== 'All') {
    $where = 'WHERE service_status = ?';
    $params[] = $selectedStatus;
}
$sql = "SELECT id, tracking_id, customer_name, mobile, category_slug, total_amount, payment_status, service_status, created_at FROM service_requests $where ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Service Requests</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7fa; margin: 0; }
        .admin-container { max-width: 1100px; margin: 0 auto; padding: 28px 12px; }
        h1 { font-size: 1.5em; margin-bottom: 18px; color: #800000; }
        .filter-bar { margin-bottom: 18px; }
        .filter-bar label { font-weight: 600; margin-right: 8px; }
        .filter-bar select { padding: 6px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 1em; }
        .service-table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 12px #e0bebe22; border-radius: 12px; overflow: hidden; }
        .service-table th, .service-table td { padding: 12px 10px; border-bottom: 1px solid #f3caca; text-align: left; font-size: 1em; }
        .service-table th { background: #f9eaea; color: #800000; font-weight: 700; }
        .service-table tr:last-child td { border-bottom: none; }
        .status-badge { padding: 2px 12px; border-radius: 8px; font-weight: 600; font-size: 0.98em; background: #f7e7e7; color: #800000; display: inline-block; }
        .status-badge.status-paid { background: #e5ffe5; color: #1a8917; }
        .status-badge.status-received { background: #e5f0ff; color: #0056b3; }
        .status-badge.status-completed { background: #e5ffe5; color: #1a8917; }
        .status-badge.status-in\ progress { background: #fffbe5; color: #b36b00; }
        .view-btn { background: #800000; color: #fff; border: none; border-radius: 8px; padding: 8px 18px; font-size: 0.98em; font-weight: 600; text-align: center; text-decoration: none; box-shadow: 0 2px 8px #80000022; transition: background 0.15s; display: inline-block; cursor: pointer; }
        .view-btn:active { background: #5a0000; }
        @media (max-width: 800px) {
            .admin-container { padding: 12px 2px; }
            .service-table th, .service-table td { padding: 8px 4px; font-size: 0.97em; }
        }
        .no-data { text-align: center; color: #888; padding: 32px 0; font-size: 1.1em; }
    </style>
</head>
<body>
<div class="admin-container">
    <h1>Service Requests</h1>
    <form class="filter-bar" method="get">
        <label for="status">Service Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <?php foreach ($statusOptions as $opt): ?>
                <option value="<?php echo $opt; ?>" <?php if ($selectedStatus === $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <div class="table-responsive">
        <table class="service-table">
            <thead>
                <tr>
                    <th>Tracking ID</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Category</th>
                    <th>Total Amount</th>
                    <th>Payment Status</th>
                    <th>Service Status</th>
                    <th>Created Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$requests): ?>
                    <tr><td colspan="9" class="no-data">No service requests found.</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['tracking_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                            <td><?php
                                $categoryTitles = [
                                    'birth-child' => 'Birth & Child Services',
                                    'marriage-matching' => 'Marriage & Matching',
                                    'astrology-consultation' => 'Astrology Consultation',
                                    'muhurat-event' => 'Muhurat & Event Guidance',
                                    'pooja-vastu-enquiry' => 'Pooja, Ritual & Vastu Enquiry',
                                ];
                                $cat = $row['category_slug'];
                                echo isset($categoryTitles[$cat]) ? $categoryTitles[$cat] : htmlspecialchars($cat);
                            ?></td>
                            <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td><span class="status-badge status-<?php echo strtolower($row['payment_status']); ?>"><?php echo htmlspecialchars($row['payment_status']); ?></span></td>
                            <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['service_status'])); ?>"><?php echo htmlspecialchars($row['service_status']); ?></span></td>
                            <td><?php echo date('d-m-Y', strtotime($row['created_at'])); ?></td>
                            <td><a href="view.php?id=<?php echo $row['id']; ?>" class="view-btn">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
