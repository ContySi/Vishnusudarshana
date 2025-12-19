<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once 'header.php';

// Step 2: Validate payment_id
$payment_id = $_GET['payment_id'] ?? '';
if (!$payment_id) {
    echo '<main class="main-content"><h2>Missing payment ID</h2><a href="services.php" class="review-back-link">&larr; Back to Home</a></main>';
    require_once 'footer.php';
    exit;
}

// Step 3: Create tables if not exist

$pdo->exec("CREATE TABLE IF NOT EXISTS service_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id VARCHAR(30) UNIQUE,
    category_slug VARCHAR(50),
    customer_name VARCHAR(255),
    mobile VARCHAR(20),
    email VARCHAR(255),
    city VARCHAR(255),
    form_data JSON,
    selected_products JSON,
    total_amount DECIMAL(10,2),
    payment_id VARCHAR(100),
    payment_status VARCHAR(20),
    service_status VARCHAR(50) DEFAULT 'Received',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);");
$pdo->exec("CREATE TABLE IF NOT EXISTS tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_id VARCHAR(30),
    customer_name VARCHAR(255),
    mobile VARCHAR(20),
    service_category VARCHAR(50),
    service_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);");

// Step 4: Generate tracking ID
$date = date('Ymd');
$rand = strtoupper(bin2hex(random_bytes(3)));
$tracking_id = "VDSK-$date-$rand";

// Step 5: Save service_requests
$pending = $_SESSION['pending_payment'] ?? [];
if (empty($pending)) {
    echo '<main class="main-content"><h2>No pending payment data found.</h2><a href="services.php" class="review-back-link">&larr; Back to Home</a></main>';
    require_once 'footer.php';
    exit;
}
// Extract and map data
$category = $pending['category_slug'] ?? $pending['category'] ?? '';
$customer = $pending['customer_details'] ?? [];
$products = $pending['products'] ?? [];
$totalAmount = $pending['total_amount'] ?? 0;
$customerName = $customer['full_name'] ?? '';
$mobile = $customer['mobile'] ?? '';
$email = $customer['email'] ?? '';
$city = $customer['city'] ?? '';
$formData = $pending['form_data'] ?? $customer;
$selectedProducts = $products;
$paymentId = $payment_id;

// Prevent duplicate insert by checking payment_id
$stmtCheck = $pdo->prepare("SELECT tracking_id FROM service_requests WHERE payment_id = ?");
$stmtCheck->execute([$paymentId]);
$existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
if ($existing && !empty($existing['tracking_id'])) {
        $trackingId = $existing['tracking_id'];
} else {
        $stmt = $pdo->prepare("
            INSERT INTO service_requests (
                tracking_id,
                category_slug,
                customer_name,
                mobile,
                email,
                city,
                form_data,
                selected_products,
                total_amount,
                payment_id,
                payment_status,
                service_status
            ) VALUES (
                :tracking_id,
                :category_slug,
                :customer_name,
                :mobile,
                :email,
                :city,
                :form_data,
                :selected_products,
                :total_amount,
                :payment_id,
                :payment_status,
                :service_status
            )
        ");
        $stmt->execute([
            ':tracking_id'       => $tracking_id,
            ':category_slug'     => $category,
            ':customer_name'     => $customerName,
            ':mobile'            => $mobile,
            ':email'             => $email,
            ':city'              => $city,
            ':form_data'         => json_encode($formData),
            ':selected_products' => json_encode($selectedProducts),
            ':total_amount'      => $totalAmount,
            ':payment_id'        => $paymentId,
            ':payment_status'    => 'Paid',
            ':service_status'    => 'Received'
        ]);
        $trackingId = $tracking_id;
        // Step 6: Save tracking
        $stmtTrack = $pdo->prepare("
            INSERT INTO tracking (
                tracking_id,
                customer_name,
                mobile,
                service_category,
                service_status
            ) VALUES (?, ?, ?, ?, ?)
        ");
        $stmtTrack->execute([
            $trackingId,
            $customerName,
            $mobile,
            $category,
            'Received'
        ]);
}
// Step 7: Clear session
unset($_SESSION['pending_payment']);
// Step 8: Confirmation UI
?>
<main class="main-content">
    <h1 class="review-title">Thank You for Your Payment!</h1>
    <div class="review-card" style="text-align:center;">
        <h2 class="section-title">Your Tracking ID</h2>
        <div style="font-size:1.3em;font-weight:700;color:#800000;letter-spacing:1px;margin:18px 0 12px 0;">
            <?php echo htmlspecialchars($tracking_id); ?>
        </div>
        <div style="color:#333;margin-bottom:18px;">Our team will contact you shortly.<br>Keep your tracking ID for future reference.</div>
        <a href="track.php?tracking_id=<?php echo urlencode($tracking_id); ?>" class="pay-btn" style="display:inline-block;width:auto;padding:12px 28px;">Track Your Service</a>
    </div>
</main>
<?php require_once 'footer.php'; ?>
<style>
.main-content { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 18px; box-shadow: 0 4px 24px #e0bebe33; padding: 18px 12px 28px 12px; }
.review-title { font-size: 1.18em; font-weight: bold; margin-bottom: 18px; text-align: center; }
.review-card { background: #f9eaea; border-radius: 14px; box-shadow: 0 2px 8px #e0bebe33; padding: 16px; margin-bottom: 18px; }
.section-title { font-size: 1.05em; color: #800000; margin-bottom: 10px; font-weight: 600; }
.pay-btn { background: #800000; color: #fff; border: none; border-radius: 8px; padding: 14px 0; font-size: 1.08em; font-weight: 600; margin-top: 10px; cursor: pointer; box-shadow: 0 2px 8px #80000022; transition: background 0.15s; text-decoration:none; }
.pay-btn:active { background: #5a0000; }
.review-back-link { display:block;text-align:center;margin-top:18px;color:#1a8917;font-size:0.98em;text-decoration:none; }
@media (max-width: 700px) { .main-content { padding: 8px 2px 16px 2px; border-radius: 0; } }
</style>
