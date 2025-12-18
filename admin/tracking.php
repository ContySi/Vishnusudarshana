<?php
// Simple admin panel for tracking management
include '../config/db.php';

// Handle status update
$updateMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['track_id'], $_POST['status'])) {
    $track_id = intval($_POST['track_id']);
    $status = $_POST['status'];
    $allowed = ['Pending', 'In Progress', 'Completed'];
    if (in_array($status, $allowed, true)) {
        $stmt = $pdo->prepare('UPDATE tracking SET status = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $track_id]);
        $updateMsg = 'Status updated successfully.';
    }
}

// Fetch all tracking records
$stmt = $pdo->query('SELECT id, service_name, customer_name, mobile, status FROM tracking ORDER BY created_at DESC');
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Service Tracking</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f8f8; margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 32px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 24px; }
        h2 { text-align: center; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f0f0f0; }
        tr:last-child td { border-bottom: none; }
        form { margin: 0; }
        select, button { padding: 6px 10px; font-size: 1rem; }
        .msg { background: #e6ffed; color: #237804; padding: 10px 12px; border-radius: 6px; margin-bottom: 18px; text-align: center; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Service Tracking Management</h2>
        <?php if ($updateMsg): ?>
            <div class="msg"><?php echo $updateMsg; ?></div>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service Name</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="track_id" value="<?php echo $row['id']; ?>">
                            <select name="status">
                                <option value="Pending"<?php if ($row['status'] === 'Pending') echo ' selected'; ?>>Pending</option>
                                <option value="In Progress"<?php if ($row['status'] === 'In Progress') echo ' selected'; ?>>In Progress</option>
                                <option value="Completed"<?php if ($row['status'] === 'Completed') echo ' selected'; ?>>Completed</option>
                            </select>
                    </td>
                    <td>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($rows)): ?>
            <div style="text-align:center; color:#888;">No tracking records found.</div>
        <?php endif; ?>
    </div>
</body>
</html>
