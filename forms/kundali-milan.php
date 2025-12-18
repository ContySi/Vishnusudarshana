<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>
<?php include '../header.php'; ?>
<?php
$successMsg = $errorMsg = '';
$successMsg = $errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../config/db.php';
    // Sanitize inputs
    $groom_name   = trim($_POST['groom_name'] ?? '');
    $groom_dob    = trim($_POST['groom_dob'] ?? '');
    $groom_tob    = trim($_POST['groom_tob'] ?? '');
    $groom_place  = trim($_POST['groom_place'] ?? '');
    $bride_name   = trim($_POST['bride_name'] ?? '');
    $bride_dob    = trim($_POST['bride_dob'] ?? '');
    $bride_tob    = trim($_POST['bride_tob'] ?? '');
    $bride_place  = trim($_POST['bride_place'] ?? '');
    $mobile       = trim($_POST['mobile'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');

    // Validate required fields
    if ($groom_name === '' || $bride_name === '' || $mobile === '') {
        $errorMsg = 'Please fill all required fields (Groom Name, Bride Name, Mobile Number).';
    } else {
        try {
            // Insert into kundali_milan_requests
            $stmt = $pdo->prepare("INSERT INTO kundali_milan_requests (groom_name, groom_dob, groom_tob, groom_place, bride_name, bride_dob, bride_tob, bride_place, mobile, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $groom_name, $groom_dob, $groom_tob, $groom_place,
                $bride_name, $bride_dob, $bride_tob, $bride_place,
                $mobile, $notes
            ]);
            $last_id = $pdo->lastInsertId();
            // Insert into tracking
            $stmt2 = $pdo->prepare("INSERT INTO tracking (service_name, service_ref_id, customer_name, mobile, status, created_at, updated_at) VALUES ('Kundali Milan', ?, ?, ?, 'Pending', NOW(), NOW())");
            $stmt2->execute([$last_id, $groom_name, $mobile]);
            $successMsg = 'Your Kundali Milan request has been submitted successfully.<br><span style="font-weight:400;">Please use your mobile number to track your service.</span>';
        } catch (PDOException $e) {
            $errorMsg = 'An error occurred while submitting your request. Please try again.';
        }
    }
}
?>
<main class="main-content">
    <section class="summary-card" style="max-width:500px;margin:0 auto;">
        <h1 style="text-align:center;">कुंडली मिलान फॉर्म</h1>
        <?php if (!empty($successMsg)): ?>
            <div class="card-success" style="background:#e6ffed;color:#237804;padding:16px 12px;border-radius:8px;margin-bottom:18px;text-align:center;font-weight:600;">
                <?php echo $successMsg; ?>
            </div>
        <?php elseif (!empty($errorMsg)): ?>
            <div class="card-error" style="background:#fff1f0;color:#cf1322;padding:16px 12px;border-radius:8px;margin-bottom:18px;text-align:center;font-weight:600;">
                <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>
        <?php if (empty($successMsg)): ?>
        <form class="kundali-milan-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
            <fieldset style="border:none; margin-bottom:18px;">
                <legend style="font-weight:700; color:var(--maroon); margin-bottom:8px;">वराची माहिती (Groom Details)</legend>
                <div class="form-group">
                    <label for="groom_name">वराचे नाव</label>
                    <input type="text" id="groom_name" name="groom_name" required autocomplete="name">
                </div>
                <div class="form-group">
                    <label for="groom_dob">जन्म तारीख</label>
                    <input type="date" id="groom_dob" name="groom_dob" required>
                </div>
                <div class="form-group">
                    <label for="groom_tob">जन्म वेळ</label>
                    <input type="time" id="groom_tob" name="groom_tob" required>
                </div>
                <div class="form-group">
                    <label for="groom_place">जन्म स्थान</label>
                    <input type="text" id="groom_place" name="groom_place" required autocomplete="address-level2">
                </div>
            </fieldset>
            <fieldset style="border:none; margin-bottom:18px;">
                <legend style="font-weight:700; color:var(--maroon); margin-bottom:8px;">वधूची माहिती (Bride Details)</legend>
                <div class="form-group">
                    <label for="bride_name">वधूचे नाव</label>
                    <input type="text" id="bride_name" name="bride_name" required autocomplete="name">
                </div>
                <div class="form-group">
                    <label for="bride_dob">जन्म तारीख</label>
                    <input type="date" id="bride_dob" name="bride_dob" required>
                </div>
                <div class="form-group">
                    <label for="bride_tob">जन्म वेळ</label>
                    <input type="time" id="bride_tob" name="bride_tob" required>
                </div>
                <div class="form-group">
                    <label for="bride_place">जन्म स्थान</label>
                    <input type="text" id="bride_place" name="bride_place" required autocomplete="address-level2">
                </div>
            </fieldset>
            <fieldset style="border:none; margin-bottom:18px;">
                <legend style="font-weight:700; color:var(--maroon); margin-bottom:8px;">संपर्क (Contact)</legend>
                <div class="form-group">
                    <label for="mobile">मोबाइल नंबर</label>
                    <input type="tel" id="mobile" name="mobile" required pattern="[0-9]{10,15}" autocomplete="tel">
                </div>
                <div class="form-group">
                    <label for="notes">अतिरिक्त माहिती (Optional)</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
            </fieldset>
            <button type="submit" class="card-btn" style="width:100%;font-size:1.1rem;">Submit for Kundali Milan</button>
        </form>
        <?php endif; ?>
    </section>
</main>
<?php include '../footer.php'; ?>
