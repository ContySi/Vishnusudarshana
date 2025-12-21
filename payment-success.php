<?php
/**
 * payment-success.php
 * CLEAN FINAL VERSION
 * Handles:
 * 1) Appointment payments → appointments table
 * 2) Normal services → service_requests table
 */

/* ======================
   STEP 1: BOOTSTRAP
   ====================== */

// Session is already started in header.php — DO NOT start here
require_once __DIR__ . '/config/db.php';

// Validate payment_id
$payment_id = isset($_GET['payment_id']) ? trim($_GET['payment_id']) : '';
if ($payment_id === '') {
    header('Location: services.php?msg=missing_payment_id');
    exit;
}

// Read pending payment session
$pending = $_SESSION['pending_payment'] ?? [];
$paymentSource = $pending['source'] ?? 'service';

/* ======================
   STEP 2: APPOINTMENT FLOW
   ====================== */
if ($paymentSource === 'appointment') {

    // Read appointment form data ONCE
    $form = $_SESSION['book_appointment'] ?? [];

    $customerName    = trim($form['full_name'] ?? '');
    $mobile          = trim($form['mobile'] ?? '');
    $email           = trim($form['email'] ?? '');
    $appointmentType = trim($form['appointment_type'] ?? '');
    $preferredDate   = trim($form['preferred_date'] ?? '');
    $preferredTime   = trim($form['preferred_time'] ?? '');
    $notes           = trim($form['notes'] ?? '');

    // Validate required fields
    $required = [
        'customer_name'    => $customerName,
        'mobile'           => $mobile,
        'appointment_type' => $appointmentType,
        'preferred_date'   => $preferredDate
    ];

    $missing = [];
    foreach ($required as $key => $val) {
        if ($val === '') $missing[] = $key;
    }

    if (!empty($missing)) {
        error_log('Appointment payment failed. Missing: ' . implode(', ', $missing));
        header('Location: services.php?msg=appointment_data_missing');
        exit;
    }

    // Prevent duplicate appointment for same payment
    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE transaction_ref = ? LIMIT 1");
    $stmt->execute([$payment_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $appointmentId = (int)$existing['id'];
    } else {
        // Insert appointment
        $stmt = $pdo->prepare("
            INSERT INTO appointments (
                customer_name,
                mobile,
                email,
                appointment_type,
                preferred_date,
                preferred_time_slot,
                notes,
                status,
                payment_status,
                transaction_ref,
                created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, 'pending', 'paid', ?, NOW()
            )
        ");
        $stmt->execute([
            $customerName,
            $mobile,
            $email ?: null,
            $appointmentType,
            $preferredDate,
            $preferredTime ?: null,
            $notes ?: null,
            $payment_id
        ]);
        $appointmentId = (int)$pdo->lastInsertId();
    }

    // Generate Appointment Tracking ID
    $trackingId = 'APT-' . str_pad($appointmentId, 6, '0', STR_PAD_LEFT);

    // Clear appointment-related session data
    unset($_SESSION['pending_payment']);
    unset($_SESSION['book_appointment']);
    unset($_SESSION['appointment_products']);

    /* ======================
       RENDER APPOINTMENT UI
       ====================== */
    require_once 'header.php';
    ?>
    <main class="main-content">
        <h1 class="review-title">Payment Successful</h1>

        <div class="review-card">
            <h2 class="section-title">Appointment Confirmed</h2>

            <div class="tracking-id">
                <?= htmlspecialchars($trackingId) ?>
            </div>

            <p class="success-text">
                Your appointment payment has been received.<br>
                Pandit Ji will contact you shortly to confirm the final time slot.
            </p>

            <a href="services.php" class="pay-btn">Back to Services</a>
        </div>
    </main>

    <style>
        .main-content { max-width:480px;margin:0 auto;padding:18px; }
        .review-title { text-align:center;font-size:1.2em;margin-bottom:16px; }
        .review-card { background:#f9eaea;border-radius:14px;padding:16px;text-align:center; }
        .section-title { color:#800000;font-weight:600;margin-bottom:10px; }
        .tracking-id { font-size:1.4em;font-weight:700;color:#800000;margin:12px 0; }
        .success-text { color:#333;margin-bottom:18px; }
        .pay-btn { display:inline-block;background:#800000;color:#fff;padding:12px 28px;
                   border-radius:8px;text-decoration:none;font-weight:600; }
        .pay-btn:active { background:#5a0000; }
    </style>
    <?php
    require_once 'footer.php';
    exit;
}

/* ======================
   STEP 3: NORMAL SERVICE FLOW
   ====================== */

$category = $pending['category_slug'] ?? $pending['category'] ?? '';
if ($category === '') {
    header('Location: services.php?msg=invalid_service');
    exit;
}

// Generate tracking
$tracking_id = 'VDSK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

$customerName = $pending['customer_details']['full_name'] ?? '';
$mobile       = $pending['customer_details']['mobile'] ?? '';
$email        = $pending['customer_details']['email'] ?? '';
$city         = $pending['customer_details']['city'] ?? '';
$formData     = $pending['form_data'] ?? [];
$products     = $pending['selected_products'] ?? [];
$totalAmount  = $pending['total_amount'] ?? 0;

// Insert service request
$stmt = $pdo->prepare("
    INSERT INTO service_requests (
        tracking_id, category_slug, customer_name, mobile, email, city,
        form_data, selected_products, total_amount, payment_id, payment_status, service_status
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid', 'Received'
    )
");
$stmt->execute([
    $tracking_id,
    $category,
    $customerName,
    $mobile,
    $email,
    $city,
    json_encode($formData),
    json_encode($products),
    $totalAmount,
    $payment_id
]);

unset($_SESSION['pending_payment']);

/* ======================
   RENDER SERVICE UI
   ====================== */
require_once 'header.php';
?>
<main class="main-content">
    <h1 class="review-title">Thank You for Your Payment!</h1>

    <div class="review-card">
        <h2 class="section-title">Your Tracking ID</h2>

        <div class="tracking-id">
            <?= htmlspecialchars($tracking_id) ?>
        </div>

        <p class="success-text">
            Our team will contact you shortly.<br>
            Keep your tracking ID for reference.
        </p>

        <a href="track.php?tracking_id=<?= urlencode($tracking_id) ?>" class="pay-btn">
            Track Your Service
        </a>
    </div>
</main>

<style>
    .main-content { max-width:480px;margin:0 auto;padding:18px; }
    .review-title { text-align:center;font-size:1.2em;margin-bottom:16px; }
    .review-card { background:#f9eaea;border-radius:14px;padding:16px;text-align:center; }
    .section-title { color:#800000;font-weight:600;margin-bottom:10px; }
    .tracking-id { font-size:1.4em;font-weight:700;color:#800000;margin:12px 0; }
    .success-text { color:#333;margin-bottom:18px; }
    .pay-btn { display:inline-block;background:#800000;color:#fff;padding:12px 28px;
               border-radius:8px;text-decoration:none;font-weight:600; }
</style>
<?php
require_once 'footer.php';
exit;
