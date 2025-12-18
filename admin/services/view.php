<?php
require_once __DIR__ . '/../../config/db.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    echo '<h2>Missing request ID.</h2>';
    exit;
}

// Fetch service request
$stmt = $pdo->prepare('SELECT * FROM service_requests WHERE id = ?');
$stmt->execute([$id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$request) {
    echo '<h2>Service request not found.</h2>';
    exit;
}

// Handle status update
$statusOptions = ['Received', 'In Progress', 'Completed'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_status'])) {
    $newStatus = $_POST['service_status'];
    if (in_array($newStatus, $statusOptions)) {
        // Update service_requests
        $stmt = $pdo->prepare('UPDATE service_requests SET service_status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $id]);
        // Update tracking
        $stmt2 = $pdo->prepare('UPDATE tracking SET service_status = ? WHERE tracking_id = ?');
        $stmt2->execute([$newStatus, $request['tracking_id']]);
        // Refresh data
        $request['service_status'] = $newStatus;
        $successMsg = 'Service status updated.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Service Request</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7fa; margin: 0; }
        .admin-container { max-width: 700px; margin: 0 auto; padding: 28px 12px; }
        h1 { font-size: 1.3em; margin-bottom: 18px; color: #800000; }
        .details-table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 12px #e0bebe22; border-radius: 12px; overflow: hidden; margin-bottom: 24px; }
        .details-table th, .details-table td { padding: 12px 10px; border-bottom: 1px solid #f3caca; text-align: left; font-size: 1em; }
        .details-table th { background: #f9eaea; color: #800000; font-weight: 700; width: 180px; }
        .details-table tr:last-child td { border-bottom: none; }
        .status-badge { padding: 2px 12px; border-radius: 8px; font-weight: 600; font-size: 0.98em; background: #f7e7e7; color: #800000; display: inline-block; }
        .status-badge.status-paid { background: #e5ffe5; color: #1a8917; }
        .status-badge.status-received { background: #e5f0ff; color: #0056b3; }
        .status-badge.status-completed { background: #e5ffe5; color: #1a8917; }
        .status-badge.status-in\ progress { background: #fffbe5; color: #b36b00; }
        .form-bar { margin-bottom: 18px; }
        .form-bar label { font-weight: 600; margin-right: 8px; }
        .form-bar select { padding: 6px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 1em; }
        .form-bar button { background: #800000; color: #fff; border: none; border-radius: 8px; padding: 8px 18px; font-size: 0.98em; font-weight: 600; text-align: center; text-decoration: none; box-shadow: 0 2px 8px #80000022; transition: background 0.15s; display: inline-block; cursor: pointer; margin-left: 10px; }
        .form-bar button:active { background: #5a0000; }
        .success-msg { color: #1a8917; font-weight: 600; margin-bottom: 12px; }
        @media (max-width: 700px) {
            .admin-container { padding: 12px 2px; }
            .details-table th, .details-table td { padding: 8px 4px; font-size: 0.97em; }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <h1>View Service Request</h1>
    <?php if (!empty($successMsg)): ?><div class="success-msg"><?php echo $successMsg; ?></div><?php endif; ?>
    <table class="details-table">
        <tr><th>Tracking ID</th><td><?php echo htmlspecialchars($request['tracking_id']); ?></td></tr>
        <tr><th>Customer Name</th><td><?php echo htmlspecialchars($request['customer_name']); ?></td></tr>
        <tr><th>Mobile</th><td><?php echo htmlspecialchars($request['mobile']); ?></td></tr>
        <tr><th>Email</th><td><?php echo htmlspecialchars($request['email']); ?></td></tr>
        <tr><th>Category</th><td><?php
            $categoryTitles = [
                'birth-child' => 'Birth & Child Services',
                'marriage-matching' => 'Marriage & Matching',
                'astrology-consultation' => 'Astrology Consultation',
                'muhurat-event' => 'Muhurat & Event Guidance',
                'pooja-vastu-enquiry' => 'Pooja, Ritual & Vastu Enquiry',
            ];
            $cat = $request['category_slug'];
            echo isset($categoryTitles[$cat]) ? $categoryTitles[$cat] : htmlspecialchars($cat);
        ?></td></tr>
        <tr><th>Total Amount</th><td>₹<?php echo number_format($request['total_amount'], 2); ?></td></tr>
        <tr><th>Payment Status</th><td><span class="status-badge status-<?php echo strtolower($request['payment_status']); ?>"><?php echo htmlspecialchars($request['payment_status']); ?></span></td></tr>
        <tr><th>Service Status</th><td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $request['service_status'])); ?>"><?php echo htmlspecialchars($request['service_status']); ?></span></td></tr>
        <tr><th>Created Date</th><td><?php echo date('d-m-Y', strtotime($request['created_at'])); ?></td></tr>
    </table>
    <form class="form-bar" method="post">
        <label for="service_status">Update Service Status:</label>
        <select name="service_status" id="service_status">
            <?php foreach ($statusOptions as $opt): ?>
                <option value="<?php echo $opt; ?>" <?php if ($request['service_status'] === $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Update</button>
    </form>
    <a href="index.php" style="color:#800000;text-decoration:underline;font-size:0.98em;">&larr; Back to List</a>
</div>
</body>
</html>
