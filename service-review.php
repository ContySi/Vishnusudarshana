<?php
require_once __DIR__ . '/config/db.php';

// Step 2: Read GET parameters
$category = $_GET['category'] ?? '';
$request_id = $_GET['request_id'] ?? '';

if (!$category || !$request_id) {
    echo '<h2>Missing information</h2>';
    echo '<p>Category and request ID are required.</p>';
    echo '<a href="services.php">&larr; Back to Services</a>';
    exit;
}

// Step 3: Load form data
$stmt = $pdo->prepare('SELECT * FROM service_requests WHERE id = ?');
$stmt->execute([$request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    echo '<h2>Request not found</h2>';
    echo '<a href="services.php">&larr; Back to Services</a>';
    exit;
}

$form_data = json_decode($request['form_data_json'] ?? '{}', true);

// Step 4: Load products
$stmt = $pdo->prepare('SELECT * FROM products WHERE category_slug = ? AND is_active = 1 ORDER BY price ASC');
$stmt->execute([$category]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Review & Product Selection</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f7f7f7; }
        .container { max-width: 480px; margin: 0 auto; padding: 16px; background: #fff; min-height: 100vh; }
        h1, h2 { text-align: center; }
        .card { background: #f9f9f9; border-radius: 8px; padding: 16px; margin-bottom: 20px; box-shadow: 0 2px 8px #0001; }
        .product-list { margin: 0; padding: 0; list-style: none; }
        .product-item { display: flex; align-items: flex-start; gap: 12px; border-bottom: 1px solid #eee; padding: 12px 0; }
        .product-item:last-child { border-bottom: none; }
        .product-info { flex: 1; }
        .product-name { font-weight: bold; }
        .product-desc { font-size: 0.95em; color: #555; margin: 4px 0; }
        .product-price { color: #1a8917; font-weight: bold; }
        .total-bar { position: sticky; bottom: 0; background: #fff; padding: 16px 0 0 0; text-align: right; font-size: 1.2em; border-top: 1px solid #eee; }
        .btn { display: block; width: 100%; background: #1a8917; color: #fff; border: none; border-radius: 6px; padding: 14px; font-size: 1.1em; margin-top: 12px; cursor: pointer; }
        .btn:active { background: #166d13; }
        @media (max-width: 600px) { .container { padding: 8px; } }
    </style>
</head>
<body>
<div class="container">
    <h1>Review Your Details</h1>
    <div class="card">
        <h2>Submitted Information</h2>
        <ul style="padding-left: 0; list-style: none;">
            <?php foreach ($form_data as $key => $val): ?>
                <li><strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</strong> <?php echo htmlspecialchars(is_array($val) ? implode(', ', $val) : $val); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <h2>Select Services</h2>
    <?php if (!$products): ?>
        <div class="card">No services available currently.</div>
    <?php else: ?>
    <form id="productForm" method="post" action="payment.php">
        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request_id); ?>">
        <ul class="product-list">
            <?php foreach ($products as $product): ?>
            <li class="product-item">
                <input type="checkbox" name="product_ids[]" value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>">
                <div class="product-info">
                    <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                    <div class="product-desc"><?php echo htmlspecialchars($product['short_description']); ?></div>
                    <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="total-bar">
            Total: <span id="totalPrice">₹0.00</span>
        </div>
        <button type="submit" class="btn">Proceed to Payment</button>
    </form>
    <?php endif; ?>
    <a href="services.php" style="display:block;text-align:center;margin-top:24px;color:#1a8917;">&larr; Back to Services</a>
</div>
<script>
// Step 6: Price calculation
function updateTotal() {
    let total = 0;
    document.querySelectorAll('input[type=checkbox][name="product_ids[]"]:checked').forEach(cb => {
        total += parseFloat(cb.getAttribute('data-price'));
    });
    document.getElementById('totalPrice').textContent = '₹' + total.toFixed(2);
}
document.querySelectorAll('input[type=checkbox][name="product_ids[]"]').forEach(cb => {
    cb.addEventListener('change', updateTotal);
});

// Step 7: Validate selection before submit
const form = document.getElementById('productForm');
if (form) {
    form.addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('input[type=checkbox][name="product_ids[]"]:checked');
        if (checked.length === 0) {
            alert('Please select at least one service to proceed.');
            e.preventDefault();
        }
    });
}
</script>
</body>
</html>
