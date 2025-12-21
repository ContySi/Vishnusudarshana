    // ...existing code...
    <?php
    // ================= TOP OF FILE: NO OUTPUT =================
    // Validate and read payment_id
    $payment_id = isset($_GET['payment_id']) ? trim($_GET['payment_id']) : '';
    if ($payment_id === '') {
        header('Location: services.php?msg=missing_payment_id');
        exit;
    }

    // Read session pending payment data
    $pending = isset($_SESSION['pending_payment']) ? $_SESSION['pending_payment'] : [];
    $paymentSource = isset($pending['source']) ? $pending['source'] : '';

    // ================= APPOINTMENT PAYMENT FLOW =================
    if ($paymentSource === 'appointment') {
        require_once __DIR__ . '/config/db.php';
        // Read appointment form data ONLY from session
        $form = isset($_SESSION['book_appointment']) && is_array($_SESSION['book_appointment']) ? $_SESSION['book_appointment'] : [];
        $customerName    = isset($form['full_name'])        ? trim($form['full_name'])        : '';
        $mobile          = isset($form['mobile'])           ? trim($form['mobile'])           : '';
        $email           = isset($form['email'])            ? trim($form['email'])            : '';
        $appointmentType = isset($form['appointment_type']) ? trim($form['appointment_type']) : '';
        $preferredDate   = isset($form['preferred_date'])   ? trim($form['preferred_date'])   : '';
        $preferredTime   = isset($form['preferred_time'])   ? trim($form['preferred_time'])   : '';
        $notes           = isset($form['notes'])            ? trim($form['notes'])            : '';

        // Validate required fields
        $requiredFields = [
            'customer_name'    => $customerName,
            'mobile'           => $mobile,
            'appointment_type' => $appointmentType,
            'preferred_date'   => $preferredDate
        ];
        $missingFields = [];
        foreach ($requiredFields as $field => $value) {
            if ($value === '') {
                $missingFields[] = $field;
            }
        }
        if (!empty($missingFields)) {
            error_log('Appointment insert failed: missing fields: ' . implode(', ', $missingFields));
            header('Location: services.php?msg=missing_required_appointment_fields');
            exit;
        }

        // Check for duplicate appointment using transaction_ref
        $dupCheck = $pdo->prepare("SELECT id FROM appointments WHERE transaction_ref = ?");
        $dupCheck->execute([$payment_id]);
        $existing = $dupCheck->fetch(PDO::FETCH_ASSOC);
        if ($existing && !empty($existing['id'])) {
            $appointmentId = (int)$existing['id'];
        } else {
            $sql = "INSERT INTO appointments (customer_name, mobile, email, appointment_type, preferred_date, preferred_time_slot, notes, status, payment_status, transaction_ref, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'paid', ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $customerName,
            <?php
            // ================= TOP OF FILE: NO OUTPUT =================
            $payment_id = isset($_GET['payment_id']) ? trim($_GET['payment_id']) : '';
            if ($payment_id === '') {
                header('Location: services.php?msg=missing_payment_id');
                exit;
            }

            $pending = isset($_SESSION['pending_payment']) ? $_SESSION['pending_payment'] : [];
            $paymentSource = isset($pending['source']) ? $pending['source'] : '';

            // ================= APPOINTMENT PAYMENT FLOW =================
            if ($paymentSource === 'appointment') {
                require_once __DIR__ . '/config/db.php';
                $form = isset($_SESSION['book_appointment']) && is_array($_SESSION['book_appointment']) ? $_SESSION['book_appointment'] : [];
                $customerName    = isset($form['full_name'])        ? trim($form['full_name'])        : '';
                $mobile          = isset($form['mobile'])           ? trim($form['mobile'])           : '';
                $email           = isset($form['email'])            ? trim($form['email'])            : '';
                $appointmentType = isset($form['appointment_type']) ? trim($form['appointment_type']) : '';
                $preferredDate   = isset($form['preferred_date'])   ? trim($form['preferred_date'])   : '';
                $preferredTime   = isset($form['preferred_time'])   ? trim($form['preferred_time'])   : '';
                $notes           = isset($form['notes'])            ? trim($form['notes'])            : '';

                $requiredFields = [
                    'customer_name'    => $customerName,
                    'mobile'           => $mobile,
                    'appointment_type' => $appointmentType,
                    'preferred_date'   => $preferredDate
                ];
                $missingFields = [];
                foreach ($requiredFields as $field => $value) {
                    if ($value === '') {
                        $missingFields[] = $field;
                    }
                }
                if (!empty($missingFields)) {
                    error_log('Appointment insert failed: missing fields: ' . implode(', ', $missingFields));
                    header('Location: services.php?msg=missing_required_appointment_fields');
                    exit;
                }

                $dupCheck = $pdo->prepare("SELECT id FROM appointments WHERE transaction_ref = ?");
                $dupCheck->execute([$payment_id]);
                $existing = $dupCheck->fetch(PDO::FETCH_ASSOC);
                if ($existing && !empty($existing['id'])) {
                    $appointmentId = (int)$existing['id'];
                } else {
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
                        $payment_id
                    ]);
                    $appointmentId = (int)$pdo->lastInsertId();
                }

                $trackingId = 'APT-' . str_pad((string)$appointmentId, 6, '0', STR_PAD_LEFT);
                unset($_SESSION['pending_payment']);
                unset($_SESSION['appointment_products']);
                unset($_SESSION['book_appointment']);

                require_once 'header.php';
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

            // ================= NON-APPOINTMENT (NORMAL SERVICE) FLOW =================
            require_once __DIR__ . '/config/db.php';
            $category = isset($pending['category_slug']) ? $pending['category_slug'] : (isset($pending['category']) ? $pending['category'] : '');
            if ($category === 'appointment') {
                header('Location: services.php?msg=invalid_service_flow');
                exit;
            }

            $date = date('Ymd');
            $rand = strtoupper(bin2hex(random_bytes(3)));
            $tracking_id = "VDSK-$date-$rand";

            $customerName = isset($pending['customer_details']['full_name']) ? $pending['customer_details']['full_name'] : (isset($pending['customer_name']) ? $pending['customer_name'] : '');
            $mobile = isset($pending['customer_details']['mobile']) ? $pending['customer_details']['mobile'] : (isset($pending['mobile']) ? $pending['mobile'] : '');
            $email = isset($pending['customer_details']['email']) ? $pending['customer_details']['email'] : (isset($pending['email']) ? $pending['email'] : '');
            $city = isset($pending['customer_details']['city']) ? $pending['customer_details']['city'] : (isset($pending['city']) ? $pending['city'] : '');
            $formData = isset($pending['form_data']) ? $pending['form_data'] : (isset($pending['form']) ? $pending['form'] : []);
            $selectedProducts = isset($pending['selected_products']) ? $pending['selected_products'] : (isset($pending['products']) ? $pending['products'] : []);
            $totalAmount = isset($pending['total_amount']) ? $pending['total_amount'] : 0;
            $paymentId = $payment_id;
            $categoryName = $category;

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
                $tracking_id,
                $customerName,
                $mobile,
                $category,
                'Received'
            ]);

            require_once 'header.php';
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
            <?php require_once 'footer.php'; exit; ?>
    } else {
