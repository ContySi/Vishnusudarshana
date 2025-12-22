<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid reel ID.']);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM instagram_reels WHERE id = ?');
$stmt->execute([$id]);

if ($stmt->rowCount()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Delete failed.']);
}
