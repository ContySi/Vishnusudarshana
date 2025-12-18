<?php
require_once '../../config/db.php';

$categoryOptions = [
    'birth-child' => 'Birth & Child Services',
    'marriage-matching' => 'Marriage & Matching',
    'astrology-consultation' => 'Astrology Consultation',
    'muhurat-event' => 'Muhurat & Event Guidance',
    'pooja-vastu-enquiry' => 'Pooja, Ritual & Vastu Enquiry',
];

$id = $_GET['id'] ?? '';
if (!$id || !is_numeric($id)) {
    die('Invalid product ID.');
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) die('Product not found.');

$name = $product['name'];
$slug = $product['slug'];
$category = $product['category'];
$description = $product['description'];
$price = $product['price'];
$status = $product['status'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $status = $_POST['status'] ?? 'inactive';

    if ($name === '') $errors[] = 'Product name is required.';
    if ($slug === '') $errors[] = 'Product slug is required.';
    if ($category === '' || !isset($categoryOptions[$category])) $errors[] = 'Category is required.';
    if ($price === '' || !is_numeric($price)) $errors[] = 'Valid price is required.';

    // Slug uniqueness (exclude current product)
    $stmt = $conn->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
    $stmt->bind_param('si', $slug, $id);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $errors[] = 'Slug must be unique.';
    }

    if (!$errors) {
        $stmt = $conn->prepare("UPDATE products SET name=?, slug=?, category=?, description=?, price=?, status=? WHERE id=?");
        $stmt->bind_param('ssssdsi', $name, $slug, $category, $description, $price, $status, $id);
        $stmt->execute();
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <script>
    function updateSlug() {
        var name = document.getElementById('name').value;
        var slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        document.getElementById('slug').value = slug;
    }
    </script>
</head>
<body>
    <h1>Edit Product</h1>
    <a href="index.php">&larr; Back to Product List</a>
    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="post">
        <label>Product Name: <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" oninput="updateSlug()" required></label><br><br>
        <label>Product Slug: <input type="text" name="slug" id="slug" value="<?php echo htmlspecialchars($slug); ?>" required></label><br><br>
        <label>Category:
            <select name="category" required>
                <option value="">--Select--</option>
                <?php foreach ($categoryOptions as $val => $label): ?>
                    <option value="<?php echo $val; ?>" <?php if ($category === $val) echo 'selected'; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </label><br><br>
        <label>Short Description:<br>
            <textarea name="description" rows="3" cols="40"><?php echo htmlspecialchars($description); ?></textarea>
        </label><br><br>
        <label>Price: <input type="number" name="price" value="<?php echo htmlspecialchars($price); ?>" step="0.01" required></label><br><br>
        <label>Status:
            <select name="status">
                <option value="active" <?php if ($status === 'active') echo 'selected'; ?>>Active</option>
                <option value="inactive" <?php if ($status === 'inactive') echo 'selected'; ?>>Inactive</option>
            </select>
        </label><br><br>
        <button type="submit">Save Changes</button>
    </form>
</body>
</html>
