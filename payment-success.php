    // Validate required fields before insert
    $requiredFields = [
        'customer_name' => $customerName,
        'mobile' => $mobile,
        'preferred_date' => $preferredDate,
        'appointment_type' => $appointmentType
    ];
    $missingFields = [];
    foreach ($requiredFields as $field => $value) {
        if ($value === null || $value === '') {
            $missingFields[] = $field;
        }
    }
    if (!empty($missingFields)) {
        error_log('Appointment insert failed: missing fields: ' . implode(', ', $missingFields));
        // Redirect safely to services page with error message
        header('Location: services.php?msg=missing_required_appointment_fields');
        exit;
    }
// Defensive: Never allow transaction_ref to be NULL
if (empty($payment_id)) {
    echo '<main class="main-content"><h2>Payment Error</h2><div class="review-card">Missing or invalid payment reference.</div></main>';
    require_once 'footer.php';
    exit;
}
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';
require_once 'header.php';
require_once __DIR__ . '/helpers/send_whatsapp.php';

// Define $payment_id and $paymentSource safely at the top
$payment_id = isset($_GET['payment_id']) ? trim($_GET['payment_id']) : '';
if (empty($payment_id)) {
    // Optionally handle missing payment_id (redirect or error)
}
$paymentSource = isset($_SESSION['pending_payment']['source']) ? $_SESSION['pending_payment']['source'] : '';

// Centralized appointment payment handling
if (isset($paymentSource) && $paymentSource === 'appointment') {
    // Use session for appointment form data
    $form = isset($_SESSION['book_appointment']) && is_array($_SESSION['book_appointment']) ? $_SESSION['book_appointment'] : (isset($pending['appointment_form']) && is_array($pending['appointment_form']) ? $pending['appointment_form'] : []);
    // Map fields, only insert NULL if truly missing
    $customerName = isset($form['full_name']) && trim($form['full_name']) !== '' ? $form['full_name'] : null;
    $mobile = isset($form['mobile']) && trim($form['mobile']) !== '' ? $form['mobile'] : null;
    $email = isset($form['email']) && trim($form['email']) !== '' ? $form['email'] : null;
    $appointmentType = isset($form['appointment_type']) && trim($form['appointment_type']) !== '' ? $form['appointment_type'] : null;
    $preferredDate = isset($form['preferred_date']) && trim($form['preferred_date']) !== '' ? $form['preferred_date'] : null;
    $preferredTime = isset($form['preferred_time']) && trim($form['preferred_time']) !== '' ? $form['preferred_time'] : null;
    $notes = isset($form['notes']) && trim($form['notes']) !== '' ? $form['notes'] : null;

    // Prevent duplicate appointment for same payment
    $dupCheck = $pdo->prepare("SELECT id FROM appointments WHERE transaction_ref = ?");
    $dupCheck->execute([$payment_id]);
    $existing = $dupCheck->fetch(PDO::FETCH_ASSOC);
    if ($existing && !empty($existing['id'])) {
        $appointmentId = (int)$existing['id'];
        // Optionally update payment_status if not already 'paid'
        if (!isset($existing['payment_status']) || $existing['payment_status'] !== 'paid') {
            $stmt = $pdo->prepare("UPDATE appointments SET payment_status = 'paid' WHERE id = ?");
            $stmt->execute([$appointmentId]);
        }
    } else {
        // Always use $payment_id for transaction_ref, never NULL
        $sql = "INSERT INTO appointments (customer_name, mobile, email, appointment_type, preferred_date, preferred_time_slot, notes, status, payment_status, transaction_ref, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'paid', ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $customerName,
            $mobile,
            $email,
            $appointmentType,
            $preferredDate,
            $preferredTime,
            $notes,
            $payment_id // always non-null here
        ]);
        $appointmentId = (int)$pdo->lastInsertId();
    }

    $trackingId = 'APT-' . str_pad((string)$appointmentId, 6, '0', STR_PAD_LEFT);
    unset($_SESSION['pending_payment']);
    unset($_SESSION['appointment_products']);

    // Render appointment success UI and exit
    echo '<main class="main-content">';
    echo '<h1 class="review-title">Thank You for Your Payment!</h1>';
    echo '<div class="review-card" style="text-align:center;">';
    echo '<h2 class="section-title">Your Appointment Tracking ID</h2>';
    echo '<div style="font-size:1.3em;font-weight:700;color:#800000;letter-spacing:1px;margin:18px 0 12px 0;">' . htmlspecialchars($trackingId) . '</div>';
    echo '<div style="color:#333;margin-bottom:18px;">Your appointment is confirmed and payment received.<br>We will contact you soon to finalize your time slot.</div>';
    echo '<a href="services.php" class="pay-btn" style="display:inline-block;width:auto;padding:12px 28px;">Back to Services</a>';
    echo '</div>';
    echo '</main>';
    echo '<style>.main-content { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 18px; box-shadow: 0 4px 24px #e0bebe33; padding: 18px 12px 28px 12px; } .review-title { font-size: 1.18em; font-weight: bold; margin-bottom: 18px; text-align: center; } .review-card { background: #f9eaea; border-radius: 14px; box-shadow: 0 2px 8px #e0bebe33; padding: 16px; margin-bottom: 18px; } .section-title { font-size: 1.05em; color: #800000; margin-bottom: 10px; font-weight: 600; } .pay-btn { background: #800000; color: #fff; border: none; border-radius: 8px; padding: 14px 0; font-size: 1.08em; font-weight: 600; margin-top: 10px; cursor: pointer; box-shadow: 0 2px 8px #80000022; transition: background 0.15s; text-decoration:none; } .pay-btn:active { background: #5a0000; } .review-back-link { display:block;text-align:center;margin-top:18px;color:#1a8917;font-size:0.98em;text-decoration:none; } @media (max-width: 700px) { .main-content { padding: 8px 2px 16px 2px; border-radius: 0; } }</style>';
    require_once 'footer.php';
    exit;
}
    // Insert or update appointment record post-payment
    $appointmentId = $pending['appointment_id'] ?? null;
    $customer = $pending['customer_details'] ?? [];
    $form = $pending['appointment_form'] ?? [];
    $customerName = $customer['full_name'] ?? '';
    $mobile = $customer['mobile'] ?? '';
    $email = $customer['email'] ?? '';

    // Prevent duplicate appointment for same payment
    if (!$appointmentId) {
        $dupCheck = $pdo->prepare("SELECT id FROM appointments WHERE transaction_ref = ?");
        $dupCheck->execute([$payment_id]);
        $existing = $dupCheck->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $appointmentId = $existing['id'];
        }
    }

    if ($appointmentId) {
        // Update payment status for existing appointment
        $hasUpdatedAt = false;
        try { $colCheck = $pdo->query("SHOW COLUMNS FROM appointments LIKE 'updated_at'"); $hasUpdatedAt = (bool)$colCheck->fetch(); } catch (Throwable $e) {}
        $stmt = $hasUpdatedAt
            ? $pdo->prepare("UPDATE appointments SET payment_status = 'paid', transaction_ref = ?, updated_at = NOW() WHERE id = ?")
            : $pdo->prepare("UPDATE appointments SET payment_status = 'paid', transaction_ref = ? WHERE id = ?");
        $stmt->execute([$payment_id, $appointmentId]);
    } else {
        // Insert new appointment
        $hasServiceId = false; $hasProductId = false; $hasUpdatedAt = false;
        try { $hasServiceId = (bool)$pdo->query("SHOW COLUMNS FROM appointments LIKE 'service_id'")->fetch(); } catch (Throwable $e) {}
        try { $hasProductId = (bool)$pdo->query("SHOW COLUMNS FROM appointments LIKE 'product_id'")->fetch(); } catch (Throwable $e) {}
        try { $hasUpdatedAt = (bool)$pdo->query("SHOW COLUMNS FROM appointments LIKE 'updated_at'")->fetch(); } catch (Throwable $e) {}

        $baseCols = ['customer_name','mobile','email','appointment_type','preferred_date','preferred_time_slot','notes','status','payment_status','transaction_ref'];
        $cols = $baseCols; $params = [
            ':customer_name' => $customerName,
            ':mobile'        => $mobile,
            ':email'         => $email ?: null,
            ':type'          => $form['appointment_type'] ?? 'online',
            ':pdate'         => $form['preferred_date'] ?? date('Y-m-d'),
            ':ptime'         => $form['preferred_time'] ?? '',
            ':notes'         => $form['notes'] ?? null,
            ':status'        => 'pending',
            ':pstatus'       => 'paid',
            ':txref'         => $payment_id,
        ];
        if ($hasServiceId && !empty($form['service_id'])) { $cols = array_merge(['service_id'],$cols); $params[':service_id'] = (int)$form['service_id']; }
        if ($hasProductId && !empty($form['product_id'])) { $cols = array_merge(['product_id'],$cols); $params[':product_id'] = (int)$form['product_id']; }

        $columnsSql = implode(', ', $cols);
        $placeholdersSql = implode(', ', array_map(function($c){
            switch($c){
                case 'service_id': return ':service_id';
                case 'product_id': return ':product_id';
                case 'customer_name': return ':customer_name';
                case 'mobile': return ':mobile';
                case 'email': return ':email';
                case 'appointment_type': return ':type';
                case 'preferred_date': return ':pdate';
                case 'preferred_time_slot': return ':ptime';
                case 'notes': return ':notes';
                case 'status': return ':status';
                case 'payment_status': return ':pstatus';
                case 'transaction_ref': return ':txref';
                default: return ':' . $c;
            }
        }, $cols));

        $sql = "INSERT INTO appointments ($columnsSql) VALUES ($placeholdersSql)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $appointmentId = (int)$pdo->lastInsertId();
    }

    $trackingId = 'APT-' . str_pad((string)$appointmentId, 6, '0', STR_PAD_LEFT);
    unset($_SESSION['pending_payment']);
    unset($_SESSION['appointment_products']);

    // Render appointment confirmation page and exit
    echo '<main class="main-content">';
    echo '<h1 class="review-title">Payment Successful!</h1>';
    echo '<div class="review-card" style="text-align:center;">';
    echo '<h2 class="section-title">Appointment Confirmed</h2>';
    echo '<div style="font-size:1.3em;font-weight:700;color:#800000;letter-spacing:1px;margin:18px 0 12px 0;">' . htmlspecialchars($trackingId) . '</div>';
    echo '<div style="color:#333;margin-bottom:18px;">Your appointment payment is received. We will contact you shortly to confirm the final time slot.</div>';
    echo '<a href="services.php" class="pay-btn" style="display:inline-block;width:auto;padding:12px 28px;">Back to Services</a>';
    echo '</div>';
    echo '</main>';
    echo '<style>.main-content { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 18px; box-shadow: 0 4px 24px #e0bebe33; padding: 18px 12px 28px 12px; } .review-title { font-size: 1.18em; font-weight: bold; margin-bottom: 18px; text-align: center; } .review-card { background: #f9eaea; border-radius: 14px; box-shadow: 0 2px 8px #e0bebe33; padding: 16px; margin-bottom: 18px; } .section-title { font-size: 1.05em; color: #800000; margin-bottom: 10px; font-weight: 600; } .pay-btn { background: #800000; color: #fff; border: none; border-radius: 8px; padding: 14px 0; font-size: 1.08em; font-weight: 600; margin-top: 10px; cursor: pointer; box-shadow: 0 2px 8px #80000022; transition: background 0.15s; text-decoration:none; } .pay-btn:active { background: #5a0000; } .review-back-link { display:block;text-align:center;margin-top:18px;color:#1a8917;font-size:0.98em;text-decoration:none; } @media (max-width: 700px) { .main-content { padding: 8px 2px 16px 2px; border-radius: 0; } }</style>';
    require_once 'footer.php';
    exit; // Explicit exit to prevent fallthrough



// Only run service_requests logic for non-appointment categories
$category = $pending['category_slug'] ?? $pending['category'] ?? '';
if ($category !== 'appointment') {
    // Insert service request for non-appointment category
    $customerName = $pending['customer_details']['full_name'] ?? $pending['customer_name'] ?? '';
    $mobile = $pending['customer_details']['mobile'] ?? $pending['mobile'] ?? '';
    $email = $pending['customer_details']['email'] ?? $pending['email'] ?? '';
    $city = $pending['customer_details']['city'] ?? $pending['city'] ?? '';
    $formData = $pending['form_data'] ?? $pending['form'] ?? [];
    $selectedProducts = $pending['selected_products'] ?? $pending['products'] ?? [];
    $totalAmount = $pending['total_amount'] ?? 0;
    $paymentId = $payment_id;
    $categoryName = $category;

    $sql = "INSERT INTO service_requests (
        tracking_id, category_slug, customer_name, mobile, email, city, form_data, selected_products, total_amount, payment_id, payment_status, service_status
    ) VALUES (
        :tracking_id, :category_slug, :customer_name, :mobile, :email, :city, :form_data, :selected_products, :total_amount, :payment_id, :payment_status, :service_status
    )";
    $stmt = $pdo->prepare($sql);
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


// Step 2 (cont): Prepare tracking link
$trackingLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
    "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/track.php?tracking_id=" . urlencode($trackingId);

// Step 3: Call WhatsApp function (with error safety) for non-appointment services only
if (
    (isset($category) && $category !== 'appointment') &&
    (isset($paymentSource) && $paymentSource !== 'appointment')
) {
    if (!empty($mobile)) {
        try {
            sendWhatsAppMessage(
                $mobile,
                'payment_success',
                'en',
                [
                    'name' => $customerName,
                    'tracking_id' => $trackingId,
                    'category' => $categoryName,
                    'tracking_link' => $trackingLink
                ]
            );
        } catch (Throwable $e) {
            error_log('WhatsApp notification failed: ' . $e->getMessage());
            // Do not interrupt page rendering
        }
    }
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
