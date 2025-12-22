<?php
require_once __DIR__ . '/../../config/db.php';

// Pagination setup
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Count total
$totalStmt = $pdo->query("SELECT COUNT(*) FROM instagram_reels");
$totalRows = (int)$totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

// Fetch reels
$stmt = $pdo->prepare("SELECT * FROM instagram_reels ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reels = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Instagram Reels</title>
	<link rel="stylesheet" href="/assets/css/style.css">
	<style>
	body { font-family: Arial, sans-serif; background: #f7f7fa; margin: 0; }
	.admin-container { max-width: 1100px; margin: 0 auto; padding: 24px 12px; }
	h1 { color: #800000; margin-bottom: 18px; font-family: inherit; }
	.add-btn { display:inline-block; background:#800000; color:#fff; padding:8px 18px; border-radius:8px; text-decoration:none; font-weight:600; margin-bottom:18px; transition: background 0.15s; }
	.add-btn:hover { background: #a00000; }
	.service-table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 12px #e0bebe22; border-radius: 12px; overflow: hidden; font-family: inherit; }
	.service-table th, .service-table td { padding: 12px 10px; border-bottom: 1px solid #f3caca; text-align: left; font-size: 1.04em; }
	.service-table th { background: #f9eaea; color: #800000; font-weight: 700; letter-spacing: 0.01em; }
	.service-table tr:last-child td { border-bottom: none; }
	.action-btn { background: #007bff; color: #fff; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-weight: 600; margin-right: 6px; transition: background 0.15s; }
	.action-btn.delete { background: #c00; }
	.action-btn:hover { background: #0056b3; }
	.action-btn.delete:hover { background: #a00000; }
	.status-badge { padding: 4px 12px; border-radius: 8px; font-weight: 600; font-size: 0.98em; display: inline-block; min-width: 80px; text-align: center; }
	.status-completed { background: #e5ffe5; color: #1a8917; }
	.status-cancelled { background: #ffeaea; color: #c00; }
	@media (max-width: 700px) {
		.admin-container { padding: 12px 2px; }
		.service-table th, .service-table td { padding: 10px 6px; font-size: 0.97em; }
		.service-table { min-width: 600px; }
	}
	</style>
	<script>
	// AJAX enable/disable toggle
	function toggleStatus(id, current) {
		var xhr = new XMLHttpRequest();
		xhr.open('POST', 'toggle_status.php');
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.onload = function() {
			if (xhr.status === 200) {
				location.reload();
			}
		};
		xhr.send('id=' + encodeURIComponent(id) + '&status=' + (current ? 0 : 1));
	}

	// AJAX delete for reels
	function deleteReel(id, btn) {
		if (!confirm('Delete this reel?')) return;
		btn.disabled = true;
		fetch('delete.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: 'id=' + encodeURIComponent(id)
		})
		.then(r => r.json())
		.then(data => {
			if (data.success) {
				// Remove row from table
				var row = btn.closest('tr');
				if (row) row.remove();
			} else {
				alert(data.error || 'Delete failed.');
				btn.disabled = false;
			}
		})
		.catch(() => { alert('Delete failed.'); btn.disabled = false; });
	}
	</script>
</head>
<body>
<?php include __DIR__ . '/../includes/top-menu.php'; ?>
<div class="admin-container">
	<h1>Instagram Reels</h1>
	<a href="add.php" class="add-btn">+ Add Reel</a>
	<div style="overflow-x:auto;">
	<table class="service-table">
		<thead>
			<tr>
				<th>ID</th>
				<th>Reel URL</th>
				<th>Status</th>
				<th>Created Date</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($reels as $reel): ?>
			<tr>
				<td><?php echo $reel['id']; ?></td>
				<td><a href="<?php echo htmlspecialchars($reel['reel_url']); ?>" target="_blank"><?php echo htmlspecialchars($reel['reel_url']); ?></a></td>
				<td>
					<span class="status-badge <?php echo $reel['is_active'] ? 'status-completed' : 'status-cancelled'; ?>">
						<?php echo $reel['is_active'] ? 'Active' : 'Inactive'; ?>
					</span>
					<button onclick="toggleStatus(<?php echo $reel['id']; ?>, <?php echo $reel['is_active'] ? '1' : '0'; ?>)" class="action-btn" style="background:#f3caca;color:#800000;padding:2px 10px;font-size:0.95em;min-width:unset;">
						<?php echo $reel['is_active'] ? 'Disable' : 'Enable'; ?>
					</button>
				</td>
				<td><?php echo date('Y-m-d H:i', strtotime($reel['created_at'])); ?></td>
				<td>
					<a href="edit.php?id=<?php echo $reel['id']; ?>" class="action-btn">Edit</a>
					<button type="button" class="action-btn delete" onclick="deleteReel(<?php echo $reel['id']; ?>, this)">Delete</button>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	</div>
	<!-- Pagination -->
	<div style="margin:18px 0;text-align:center;">
		<?php if ($totalPages > 1): ?>
			<?php for ($i = 1; $i <= $totalPages; $i++): ?>
				<?php if ($i == $page): ?>
					<span class="page-link current" style="background:#800000;color:#fff;border-color:#800000;padding:6px 12px;border-radius:6px;"> <?php echo $i; ?> </span>
				<?php else: ?>
					<a href="?page=<?php echo $i; ?>" class="page-link" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;background:#fff;color:#333;text-decoration:none;"> <?php echo $i; ?> </a>
				<?php endif; ?>
			<?php endfor; ?>
		<?php endif; ?>
	</div>
</div>
</body>
</html>
