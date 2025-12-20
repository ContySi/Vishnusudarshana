<?php 
require_once 'header.php';
require_once __DIR__ . '/config/db.php';

$service = isset($_GET['service']) ? trim($_GET['service']) : '';

$services = [
    'book-appointment' => [
        'title' => 'Book an Appointment',
        'icon' => '📅',
        'description' => 'Schedule an online or offline appointment. We will review your preferred slot and confirm the final time window.',
    ],
];

$defaultService = [
    'title' => 'Service Details',
    'icon' => '🕉️',
    'description' => 'Service information will appear here.',
];

$serviceData = $services[$service] ?? $defaultService;

// Fetch appointment products from database
$products = [];
if ($service === 'book-appointment') {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE category_slug = ? AND is_active = 1 ORDER BY price ASC');
    $stmt->execute(['appointment']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($serviceData['title']); ?> - Service Details</title>
    <style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    background: linear-gradient(135deg, #f7e7e7 0%, #f7f7fa 100%);
    min-height: 10vh;
}
.main-content {
    max-width: 480px;
    margin: 0 auto;
    background: transparent;
    padding: 16px 8px 24px 8px;
    min-height: 10vh;
}
.detail-header {
    text-align: center;
    margin-bottom: 12px;
}
.detail-icon-large {
    font-size: 2.1em;
    margin-bottom: 4px;
}
.detail-title {
    font-size: 1.18em;
    font-weight: bold;
    margin: 0;
}
.detail-section {
    margin-bottom: 14px;
}
.detail-description {
    color: #444;
    font-size: 0.98em;
    margin: 0 0 6px 0;
}
.how-works-section { margin-bottom: 10px; }
.how-works-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: flex-start;
    align-items: center;
    font-size: 0.93em;
}
.how-step {
    display: flex;
    align-items: center;
    gap: 3px;
    background: #fffbe7;
    border-radius: 12px;
    padding: 3px 10px 3px 6px;
    font-size: 0.93em;
    box-shadow: 0 2px 8px #e0bebe33;
    white-space: nowrap;
}
.how-dot.maroon-dot {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    background: #800000;
    border: 1.5px solid #800000;
    color: #fff;
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.93em;
    margin-right: 4px;
    box-shadow: 0 1px 2px #80000022;
}
.appointment-form { display: flex; flex-direction: column; gap: 0.9rem; }
.form-row { display: flex; flex-direction: column; gap: 0.35rem; }
.form-row label { font-weight: 600; color: #333; font-size: 0.95em; }
.form-row input, .form-row textarea { padding: 0.55rem 0.65rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
.inline-options { display: flex; gap: 1rem; }
.proceed-btn.maroon-btn {
    display: inline-block;
    background: #800000;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 12px 28px;
    font-size: 1.05em;
    margin-top: 10px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s;
    font-weight: 600;
    box-shadow: 0 2px 8px #80000022;
    width: 100%;
}
.proceed-btn.maroon-btn:active {
    background: #5a0000;
}
.cat-helper-text {
    color: #888;
    font-size: 0.93em;
    margin-top: 8px;
    margin-bottom: 0;
}
/* Product Selection Styles */
.product-list { margin: 0 0 14px 0; padding: 0; list-style: none; }
.product-item { display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f3caca; padding: 14px 0; }
.product-item:last-child { border-bottom: none; }
.product-info { flex: 1; }
.product-checkbox { width: 28px; height: 28px; accent-color: #800000; cursor: pointer; }
.product-name { font-weight: 600; color: #800000; font-size: 1.08em; }
.product-desc { font-size: 0.97em; color: #555; margin: 2px 0 2px 0; }
.product-price { color: #1a8917; font-weight: 600; font-size: 1.08em; margin-top: 6px; }
.qty-controls { display: flex; align-items: center; gap: 4px; }
.qty-btn { background: #f5faff; border: 1px solid #e0bebe; color: #800000; border-radius: 50%; width: 22px; height: 22px; font-size: 1em; cursor: pointer; }
.qty-input { width: 32px; text-align: center; border: 1px solid #e0bebe; border-radius: 6px; padding: 2px 0; font-size: 1em; }
.line-total { font-size: 0.98em; color: #800000; font-weight: 600; min-width: 60px; text-align: right; }
.selected-total { background: #f9eaea; border-radius: 10px; padding: 12px; text-align: right; font-size: 1.13em; color: #800000; font-weight: 600; margin: 10px 0; }
@media (max-width: 700px) {
    .main-content { padding: 8px 2px 16px 2px; }
    .how-works-inline { flex-direction: ; gap: 4px; align-items: flex-start; }
}
    </style>
</head>
<body>
<main class="main-content">
    <section class="detail-header">
        <div class="detail-icon-large"><?php echo $serviceData['icon']; ?></div>
        <h1 class="detail-title"><?php echo htmlspecialchars($serviceData['title']); ?></h1>
    </section>

    <section class="detail-section">
        <h3>Description</h3>
        <p class="detail-description"><?php echo htmlspecialchars($serviceData['description']); ?></p>
    </section>

    <?php if ($service === 'book-appointment'): ?>
    <!-- Procedure Section -->
    <section class="how-works-section">
        <h3>How This Service Works</h3>
        <div class="how-works-inline">
            <span class="how-step"><span class="how-dot maroon-dot">1</span>Fill the form</span>
            <span class="how-step"><span class="how-dot maroon-dot">2</span>Submit details</span>
            <span class="how-step"><span class="how-dot maroon-dot">3</span>Confirm slot</span>
            <span class="how-step"><span class="how-dot maroon-dot">4</span>Pay (if online)</span>
            <span class="how-step"><span class="how-dot maroon-dot">5</span>Attend appointment</span>
        </div>
    </section>

    <section class="detail-section">
        <h3>Appointment Form</h3>
        <form class="appointment-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] ? dirname($_SERVER['PHP_SELF']) : ''); ?>/appointment-process.php" id="appointmentForm">
            <input type="hidden" name="service_id" value="0">
            
            <!-- Product Selection -->
            <?php if (!empty($products)): ?>
            <div class="form-row">
                <label>Select Service(s) <span style="color:#d00;">*</span></label>
                <ul class="product-list">
                    <?php foreach ($products as $product): ?>
                    <li class="product-item">
                        <div class="product-info">
                            <div style="display:flex;align-items:center;gap:14px;">
                                <input type="checkbox" class="product-checkbox" name="product_ids[]" value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>">
                                <div>
                                    <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                    <div class="product-desc"><?php echo htmlspecialchars($product['short_description']); ?></div>
                                </div>
                            </div>
                            <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                        </div>
                        <div class="qty-controls">
                            <button type="button" class="qty-btn" onclick="changeQty(this, -1)" disabled>−</button>
                            <input type="number" class="qty-input" name="qty[<?php echo $product['id']; ?>]" value="1" min="1" max="99" readonly>
                            <button type="button" class="qty-btn" onclick="changeQty(this, 1)" disabled>+</button>
                        </div>
                        <div class="line-total" id="line-total-<?php echo $product['id']; ?>">₹<?php echo number_format($product['price'], 2); ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="selected-total">
                    Total: <span id="totalPrice">₹0.00</span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-row">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-row">
                <label>Mobile Number</label>
                <input type="tel" name="mobile" required>
            </div>
            <div class="form-row">
                <label>Email (optional)</label>
                <input type="email" name="email">
            </div>
            <div class="form-row">
                <label>Appointment Type</label>
                <div class="inline-options">
                    <label><input type="radio" name="appointment_type" value="online" required> Online</label>
                    <label><input type="radio" name="appointment_type" value="offline" required> Offline</label>
                </div>
            </div>
            <div class="form-row">
                <label>Preferred Date</label>
                <input type="date" name="preferred_date" required>
            </div>
            <div class="form-row">
                <label>Preferred Time Window</label>
                <input type="text" name="preferred_time" placeholder="e.g., 10:00 AM - 12:00 PM" required>
            </div>
            <div class="form-row">
                <label>Notes</label>
                <textarea name="notes" rows="3" placeholder="Share any details or questions"></textarea>
            </div>
            <button type="submit" class="proceed-btn maroon-btn" id="submitBtn">Submit Request</button>
        </form>
        <div class="cat-helper-text">We will review your request and confirm the final appointment slot. Online appointments require payment after confirmation.</div>
    </section>
    <?php endif; ?>

    <a href="services.php" style="display:block;text-align:center;margin-top:14px;color:#1a8917;font-size:0.98em;">&larr; Back to Services</a>
</main>
<script>
function updateTotals() {
    let total = 0;
    let hasSelection = false;
    document.querySelectorAll('.product-item').forEach(function(row) {
        const cb = row.querySelector('input[type=checkbox][name="product_ids[]"]');
        if (!cb) return;
        const qtyInput = row.querySelector('.qty-input');
        const price = parseFloat(cb.getAttribute('data-price'));
        let qty = parseInt(qtyInput.value);
        if (!cb.checked) {
            qty = 0;
        } else {
            hasSelection = true;
        }
        const lineTotal = price * qty;
        const lineTotalEl = row.querySelector('.line-total');
        if (lineTotalEl) {
            lineTotalEl.textContent = '₹' + lineTotal.toFixed(2);
        }
        total += lineTotal;
        qtyInput.disabled = !cb.checked;
        row.querySelectorAll('.qty-btn').forEach(btn => btn.disabled = !cb.checked);
    });
    const totalPriceEl = document.getElementById('totalPrice');
    if (totalPriceEl) {
        totalPriceEl.textContent = '₹' + total.toFixed(2);
    }
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn && document.querySelectorAll('.product-checkbox').length > 0) {
        if (!hasSelection) {
            submitBtn.disabled = true;
            submitBtn.style.background = '#ccc';
            submitBtn.style.cursor = 'not-allowed';
        } else {
            submitBtn.disabled = false;
            submitBtn.style.background = '#800000';
            submitBtn.style.cursor = 'pointer';
        }
    }
}
function changeQty(btn, delta) {
    const row = btn.closest('.product-item');
    const qtyInput = row.querySelector('.qty-input');
    let qty = parseInt(qtyInput.value) + delta;
    if (qty < 1) qty = 1;
    if (qty > 99) qty = 99;
    qtyInput.value = qty;
    updateTotals();
}
// Initialize event listeners
document.querySelectorAll('input[type=checkbox][name="product_ids[]"]').forEach(cb => {
    cb.addEventListener('change', updateTotals);
});
document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', function() { updateTotals(); });
});
// Form validation
const form = document.getElementById('appointmentForm');
if (form) {
    form.addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        if (checkboxes.length > 0) {
            const hasChecked = Array.from(checkboxes).some(cb => cb.checked);
            if (!hasChecked) {
                e.preventDefault();
                alert('Please select at least one service to proceed.');
                return false;
            }
        }
    });
}
window.onload = updateTotals;
</script>
<?php require_once 'footer.php'; ?>
