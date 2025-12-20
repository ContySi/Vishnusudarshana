<?php
require_once __DIR__ . '/../../config/db.php';

$categoryOptions = [
    'appointment' => 'Appointment Booking',
    'birth-child' => 'Birth & Child Services',
    'marriage-matching' => 'Marriage & Matching',
    'astrology-consultation' => 'Astrology Consultation',
    'muhurat-event' => 'Muhurat & Event Guidance',
    'pooja-vastu-enquiry' => 'Pooja, Ritual & Vastu Enquiry',
];

$product_name = $product_slug = $category_slug = $short_description = $price = '';
$is_active = 1;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $product_slug = trim($_POST['product_slug'] ?? '');
    $category_slug = $_POST['category_slug'] ?? '';
    $short_description = trim($_POST['short_description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $is_active = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

    // Validation
    if ($product_name === '') $errors[] = 'Product name is required.';
    if ($product_slug === '') $errors[] = 'Product slug is required.';
    if ($category_slug === '' || !isset($categoryOptions[$category_slug])) $errors[] = 'Category is required.';
    if ($price === '' || !is_numeric($price)) $errors[] = 'Valid price is required.';

    // Slug uniqueness
    $stmt = $pdo->prepare("SELECT id FROM products WHERE product_slug = ?");
    $stmt->execute([$product_slug]);
    if ($stmt->fetch()) {
        $errors[] = 'Slug must be unique.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO products (category_slug, product_name, product_slug, short_description, price, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$category_slug, $product_name, $product_slug, $short_description, $price, $is_active]);
        header('Location: index.php');
        exit;
    }
}

function generateSlug($str) {
    $slug = strtolower(trim($str));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
</head>
<body>
    <h1>Add Product</h1>
    <a href="index.php">&larr; Back to Product List</a>
    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="post">
        <label>Product Name: <input type="text" name="product_name" id="product_name" value="<?php echo htmlspecialchars($product_name); ?>" oninput="updateSlug()" required></label><br><br>
        <label>Product Slug: <input type="text" name="product_slug" id="product_slug" value="<?php echo htmlspecialchars($product_slug); ?>" required></label><br><br>
        <label>Category:
            <select name="category_slug" required>
                <option value="">--Select--</option>
                <?php foreach ($categoryOptions as $val => $label): ?>
                    <option value="<?php echo $val; ?>" <?php if ($category_slug === $val) echo 'selected'; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </label><br><br>
        <label>Short Description:<br>
            <textarea name="short_description" rows="3" cols="40"><?php echo htmlspecialchars($short_description); ?></textarea>
        </label><br><br>
        <label>Price: <input type="number" name="price" value="<?php echo htmlspecialchars($price); ?>" step="0.01" required></label><br><br>
        <label>Status:
            <select name="is_active">
                <option value="1" <?php if ($is_active == 1) echo 'selected'; ?>>Active</option>
                <option value="0" <?php if ($is_active == 0) echo 'selected'; ?>>Inactive</option>
            </select>
        </label><br><br>
        <button type="submit">Add Product</button>
    </form>
    <script>
    function updateSlug() {
        var name = document.getElementById('product_name').value;
        var slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        document.getElementById('product_slug').value = slug;
    }
    </script>
</body>
</html>
