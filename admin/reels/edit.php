<?php
require_once __DIR__ . '/../../config/db.php';

$id = $_GET['id'] ?? '';
if (!$id || !is_numeric($id)) {
    die('Invalid reel ID.');
}

$stmt = $pdo->prepare('SELECT * FROM instagram_reels WHERE id = ?');
$stmt->execute([$id]);
$reel = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$reel) die('Reel not found.');

$reel_url = $reel['reel_url'];
$is_active = $reel['is_active'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reel_url = trim($_POST['reel_url'] ?? '');
    $reel_url = preg_replace('/\?.*/', '', $reel_url);
    $is_active = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

    if ($reel_url === '') {
        $errors[] = 'Reel URL is required.';
    } elseif (strpos($reel_url, 'https://www.instagram.com/reel/') !== 0) {
        $errors[] = 'URL must start with https://www.instagram.com/reel/';
    }

    // Uniqueness (exclude current)
    $stmt = $pdo->prepare('SELECT id FROM instagram_reels WHERE reel_url = ? AND id != ?');
    $stmt->execute([$reel_url, $id]);
    if ($stmt->fetch()) {
        $errors[] = 'This reel already exists.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE instagram_reels SET reel_url=?, is_active=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$reel_url, $is_active, $id]);
        header('Location: index.php?msg=updated');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Reel</title>
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
    <h1>Edit Reel</h1>
    <a href="index.php" class="back-link">&larr; Cancel</a>
    <?php if ($errors): ?>
        <ul class="error-list">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="post">
        <label class="form-label">Reel URL:
            <input type="text" name="reel_url" class="form-input" value="<?php echo htmlspecialchars($reel_url); ?>" required>
        </label>
        <label class="form-label">Status:
            <select name="is_active" class="form-input">
                <option value="1" <?php if ($is_active == 1) echo 'selected'; ?>>Active</option>
                <option value="0" <?php if ($is_active == 0) echo 'selected'; ?>>Inactive</option>
            </select>
        </label>
        <button type="submit" class="form-btn">Update</button>
    </form>
</div>
</body>
</html>
