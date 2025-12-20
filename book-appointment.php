<?php
require_once __DIR__ . '/config/db.php';
include __DIR__ . '/header.php';

$errors = [];
$successMsg = '';

$name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$appointmentType = $_POST['appointment_type'] ?? '';
$preferredDate = $_POST['preferred_date'] ?? '';
$preferredTime = $_POST['preferred_time'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($phone === '') {
        $errors[] = 'WhatsApp phone number is required.';
    }
    if (!in_array($appointmentType, ['online', 'offline'], true)) {
        $errors[] = 'Appointment type is required.';
    }
    if ($preferredDate === '') {
        $errors[] = 'Preferred date is required.';
    }
    if ($preferredTime === '') {
        $errors[] = 'Preferred time is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO appointments (service_id, service_slug, customer_name, whatsapp, appointment_type, preferred_date, preferred_time_slot, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            // If a services table exists, you can resolve service_id by slug. For now, keep null.
            $serviceId = null;
            $status = 'pending';
            $stmt->execute([
                $serviceId,
                'book-appointment',
                $name,
                $phone,
                $appointmentType,
                $preferredDate,
                $preferredTime,
                $status
            ]);
            $appointmentId = $pdo->lastInsertId();

            if ($appointmentType === 'online') {
                // Redirect to payment for online appointments
                header('Location: payment-init.php?appointment_id=' . urlencode($appointmentId));
                exit;
            }

            $successMsg = 'Your appointment request has been submitted. We will confirm the final time slot shortly.';
            // Clear form
            $name = $phone = $preferredDate = $preferredTime = '';
            $appointmentType = '';
        } catch (Throwable $e) {
            error_log('Appointment insert failed: ' . $e->getMessage());
            $errors[] = 'Failed to submit your request. Please try again.';
        }
    }
}
?>

<main class="main-content" style="padding: 1.5rem 1rem 4rem 1rem; background:#f8f9fa; min-height:100vh;">
    <section class="detail-section" style="background:#fff; padding:1.25rem; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.05); max-width:640px; margin:0 auto;">
        <h1 style="margin-top:0; color:#800000;">Book an Appointment</h1>
        <p style="color:#444;">Schedule an online or offline appointment. We will review your preferred slot and confirm the final time window.</p>

        <?php if ($errors): ?>
            <div style="color:#b00020; margin-bottom:12px;">
                <?php foreach ($errors as $err): ?>
                    <div><?php echo htmlspecialchars($err); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($successMsg): ?>
            <div style="color:#1a8917; margin-bottom:12px; font-weight:600;"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>

        <form method="post" style="display:flex; flex-direction:column; gap:0.9rem;">
            <div class="form-row">
                <label style="font-weight:600;">Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($name); ?>" required style="padding:0.55rem 0.65rem; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
            </div>
            <div class="form-row">
                <label style="font-weight:600;">WhatsApp Number</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required style="padding:0.55rem 0.65rem; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
            </div>
            <div class="form-row">
                <label style="font-weight:600;">Appointment Type</label>
                <div style="display:flex; gap:1rem;">
                    <label><input type="radio" name="appointment_type" value="online" <?php echo $appointmentType==='online'?'checked':''; ?>> Online (payment required)</label>
                    <label><input type="radio" name="appointment_type" value="offline" <?php echo $appointmentType==='offline'?'checked':''; ?>> Offline</label>
                </div>
            </div>
            <div class="form-row">
                <label style="font-weight:600;">Preferred Date</label>
                <input type="date" name="preferred_date" value="<?php echo htmlspecialchars($preferredDate); ?>" required style="padding:0.55rem 0.65rem; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
            </div>
            <div class="form-row">
                <label style="font-weight:600;">Preferred Time</label>
                <input type="time" name="preferred_time" value="<?php echo htmlspecialchars($preferredTime); ?>" required style="padding:0.55rem 0.65rem; border:1px solid #ddd; border-radius:8px; font-size:1rem;">
            </div>
            <button type="submit" class="primary-btn" style="background:#800000; color:#fff; border:none; border-radius:8px; padding:0.8rem 1.4rem; font-size:1rem; font-weight:600; cursor:pointer; box-shadow:0 2px 8px rgba(128,0,0,0.18);">Submit Request</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/footer.php'; ?>
