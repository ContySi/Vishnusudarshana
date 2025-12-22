echo "<h1>Add Reel</h1>";
<?php
require_once __DIR__ . '/../../config/db.php';

$reel_url = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$reel_url = trim($_POST['reel_url'] ?? '');
	$reel_url = filter_var($reel_url, FILTER_SANITIZE_URL);
	// Remove trailing params (e.g. ?utm_source...)
	$reel_url = preg_replace('/\?.*/', '', $reel_url);

	// Validation
	if ($reel_url === '') {
		$errors[] = 'Reel URL is required.';
	} elseif (!preg_match('#^https://www\.instagram\.com/reel/[^/?#]+/?$#', $reel_url)) {
		$errors[] = 'URL must be a valid Instagram Reel link.';
	} else {
		// Prevent duplicate
		$stmt = $pdo->prepare('SELECT id FROM instagram_reels WHERE reel_url = ?');
		$stmt->execute([$reel_url]);
		if ($stmt->fetch()) {
			$errors[] = 'This reel already exists.';
		}
	}

	if (!$errors) {
		$stmt = $pdo->prepare('INSERT INTO instagram_reels (reel_url, is_active, created_at) VALUES (?, 1, NOW())');
		$stmt->execute([$reel_url]);
		header('Location: index.php?msg=added');
		exit;
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Add Reel</title>
	<link rel="stylesheet" href="/assets/css/style.css">
	<style>
	body { font-family: Arial, sans-serif; background: #f7f7fa; margin: 0; }
	.admin-container { max-width: 700px; margin: 0 auto; padding: 24px 12px; }
	h1 { color: #800000; margin-bottom: 18px; font-family: inherit; }
	.form-label { font-weight: 600; display:block; margin-bottom:6px; }
	.form-input { width:100%; padding:10px 12px; border-radius:8px; border:1px solid #e0bebe; font-size:1.08em; margin-bottom:16px; font-family: inherit; }
	.form-input:focus { border-color: #800000; outline: none; }
	.form-btn { background:#800000; color:#fff; border:none; border-radius:8px; padding:12px 0; font-size:1.08em; font-weight:600; width:100%; cursor:pointer; margin-top:10px; transition: background 0.15s; }
	.form-btn:hover { background: #a00000; }
	.back-link { display:inline-block; margin-bottom:18px; color:#800000; text-decoration:none; font-weight:600; }
	.error-list { color:#c00; margin-bottom:18px; }
	</style>
</head>
<body>
<?php include __DIR__ . '/../includes/top-menu.php'; ?>
<div class="admin-container">
	<h1>Add Reel</h1>
	<a href="index.php" class="back-link">&larr; Back to Reel List</a>
	<?php if ($errors): ?>
		<ul class="error-list">
			<?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
		</ul>
	<?php endif; ?>
	<form method="post">
		<label class="form-label">Reel URL:
			<input type="text" name="reel_url" class="form-input" value="<?php echo htmlspecialchars($reel_url, ENT_QUOTES, 'UTF-8'); ?>" required autocomplete="off" spellcheck="false">
		</label>
		<button type="submit" class="form-btn">Add Reel</button>
	</form>
</div>
</body>
</html>
