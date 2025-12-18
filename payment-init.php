<?php
session_start();
require_once 'header.php';


// Step 2: Read POST data
$category = $_POST['category'] ?? '';
$form_data = $_POST;
$product_ids = $_POST['product_ids'] ?? [];
$quantities = $_POST['qty'] ?? [];

// Validate products and total
if (!$category || empty($product_ids) || empty($quantities)) {
    echo '<main class="main-content"><h2>Invalid payment request</h2>';
    echo '<p>Please select at least one service/product.</p>';
    echo '<a href="service-review.php?category=' . htmlspecialchars($category) . '" class="review-back-link">&larr; Back to Review</a></main>';
    require_once 'footer.php';
    exit;
}

// Fetch product details from DB
require_once __DIR__ . '/config/db.php';
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare summary and total
$selected_products = [];
$total_amount = 0;
foreach ($products as $product) {
    $pid = $product['id'];
    $qty = isset($quantities[$pid]) ? max(1, intval($quantities[$pid])) : 1;
    $line_total = $product['price'] * $qty;
    $selected_products[] = [
        'id' => $pid,
        'name' => $product['product_name'],
        'desc' => $product['short_description'],
        'price' => $product['price'],
        'qty' => $qty,
        'line_total' => $line_total
    ];
    $total_amount += $line_total;
}
if ($total_amount <= 0) {
    echo '<main class="main-content"><h2>Invalid total amount</h2>';
    echo '<p>Total amount must be greater than zero.</p>';
    echo '<a href="service-review.php?category=' . htmlspecialchars($category) . '" class="review-back-link">&larr; Back to Review</a></main>';
    require_once 'footer.php';
    exit;
}

// Store in session
$_SESSION['pending_payment'] = [
    'category' => $category,
    'customer_details' => $form_data,
    'products' => $selected_products,
    'total_amount' => $total_amount
];

// UI
?>
<main class="main-content">
    <h1 class="review-title">Payment Summary</h1>
    <div class="review-card">
        <h2 class="section-title">Customer Details</h2>
        <div class="details-list">
            <div class="details-row">
                <span class="details-label">Name:</span>
                <span class="details-value"><?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?></span>
            </div>
            <div class="details-row">
                <span class="details-label">Mobile:</span>
                <span class="details-value"><?php echo htmlspecialchars($form_data['mobile'] ?? ''); ?></span>
            </div>
            <div class="details-row">
                <span class="details-label">Email:</span>
                <span class="details-value"><?php echo htmlspecialchars($form_data['email'] ?? ''); ?></span>
            </div>
        </div>
    </div>
    <div class="review-card">
        <h2 class="section-title">Selected Services</h2>
        <ul class="product-list">
            <?php foreach ($selected_products as $prod): ?>
            <li class="product-item">
                <div class="product-info">
                    <div class="product-name"><?php echo htmlspecialchars($prod['name']); ?></div>
                    <div class="product-desc"><?php echo htmlspecialchars($prod['desc']); ?></div>
                </div>
                <div class="qty-controls">
                    <span class="details-label">Qty:</span>
                    <span class="details-value"><?php echo $prod['qty']; ?></span>
                </div>
                <div class="line-total">₹<?php echo number_format($prod['line_total'], 2); ?></div>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="sticky-total">
            Total: <span id="totalPrice">₹<?php echo number_format($total_amount, 2); ?></span>
        </div>
    </div>
    <button class="pay-btn" id="rzpPayBtn" style="margin-top:18px;">Proceed to Secure Payment</button>
    <a href="service-review.php?category=<?php echo htmlspecialchars($category); ?>" class="review-back-link">&larr; Back to Review</a>
</main>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
<?php
$pending = $_SESSION['pending_payment'] ?? [];
$customer = $pending['customer_details'] ?? [];
$total_amount = $pending['total_amount'] ?? 0;
$amount_in_paise = (int)round($total_amount * 100);
$name = isset($customer['full_name']) ? addslashes($customer['full_name']) : '';
$email = isset($customer['email']) ? addslashes($customer['email']) : '';
$mobile = isset($customer['mobile']) ? addslashes($customer['mobile']) : '';
?>

const options = {
    key: "rzp_test_a3iYwPnLkGMlDM", // TODO: Replace with your Razorpay Key ID
    amount: <?php echo $amount_in_paise; ?>,
    currency: "INR",
    name: "Vishnusudarshana Dharmik Sanskar Kendra",
    description: "Service Payment",
    prefill: {
        name: "<?php echo $name; ?>",
        email: "<?php echo $email; ?>",
        contact: "<?php echo $mobile; ?>"
    },
    theme: {
        color: "#800000"
    },
    handler: function (response) {
        // On success, redirect with payment_id
        window.location.href = "payment-success.php?payment_id=" + encodeURIComponent(response.razorpay_payment_id);
    },
    modal: {
        ondismiss: function() {
            window.location.href = "payment-failed.php";
        }
    }
};
document.getElementById('rzpPayBtn').onclick = function(e){
    e.preventDefault();
    var rzp = new Razorpay(options);
    rzp.open();
};
</script>
<?php require_once 'footer.php'; ?>
<style>
/* ...reuse review page styles for consistency... */
.main-content { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 18px; box-shadow: 0 4px 24px #e0bebe33; padding: 18px 12px 28px 12px; }
.review-title { font-size: 1.18em; font-weight: bold; margin-bottom: 18px; text-align: center; }
.review-card { background: #f9eaea; border-radius: 14px; box-shadow: 0 2px 8px #e0bebe33; padding: 16px; margin-bottom: 18px; }
.section-title { font-size: 1.05em; color: #800000; margin-bottom: 10px; font-weight: 600; }
.details-list { display: flex; flex-direction: column; gap: 8px; }
.details-row { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px dashed #e0bebe; padding-bottom: 4px; }
.details-label { color: #a03c3c; font-weight: 500; margin-right: 6px; }
.details-value { color: #333; max-width: 60%; word-break: break-word; }
.product-list { margin: 0; padding: 0; list-style: none; }
.product-item { display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f3caca; padding: 10px 0; }
.product-item:last-child { border-bottom: none; }
.product-info { flex: 1; }
.product-name { font-weight: 600; color: #800000; font-size: 1em; }
.product-desc { font-size: 0.95em; color: #555; margin: 2px 0 2px 0; }
.qty-controls { display: flex; align-items: center; gap: 4px; }
.line-total { font-size: 0.98em; color: #800000; font-weight: 600; min-width: 60px; text-align: right; }
.sticky-total { position: sticky; bottom: 0; background: #fff; padding: 14px 0 0 0; text-align: right; font-size: 1.13em; border-top: 1px solid #e0bebe; box-shadow: 0 -2px 8px #e0bebe22; z-index: 10; }
.pay-btn { width: 100%; background: #800000; color: #fff; border: none; border-radius: 8px; padding: 14px 0; font-size: 1.08em; font-weight: 600; margin-top: 10px; cursor: pointer; box-shadow: 0 2px 8px #80000022; transition: background 0.15s; }
.pay-btn:disabled { background: #ccc; color: #fff; cursor: not-allowed; }
.review-back-link { display:block;text-align:center;margin-top:18px;color:#1a8917;font-size:0.98em;text-decoration:none; }
@media (max-width: 700px) { .main-content { padding: 8px 2px 16px 2px; border-radius: 0; } }
</style>
