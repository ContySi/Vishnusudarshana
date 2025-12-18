<?php
require_once '../../config/db.php';

$id = $_GET['id'] ?? '';
if (!$id || !is_numeric($id)) {
    die('Invalid product ID.');
}

// Confirm deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        header('Location: index.php');
        exit;
    } else {
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Product</title>
</head>
<body>
    <h1>Delete Product</h1>
    <form method="post">
        <p>Are you sure you want to delete this product permanently?</p>
        <button type="submit" name="confirm" value="yes">Yes, Delete</button>
        <button type="submit" name="confirm" value="no">Cancel</button>
    </form>
    <a href="index.php">&larr; Back to Product List</a>
</body>
</html>
