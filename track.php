
<?php
include 'header.php';
include 'config/db.php';

$results = [];
$errorMsg = '';
$searched = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched = true;
    $mobile = trim($_POST['mobile'] ?? '');
    if ($mobile === '' || !preg_match('/^[0-9]{10,15}$/', $mobile)) {
        $errorMsg = 'Please enter a valid mobile number.';
    } else {
        $stmt = $pdo->prepare('SELECT service_name, status, created_at FROM tracking WHERE mobile = ? ORDER BY created_at DESC');
        $stmt->execute([$mobile]);
        $results = $stmt->fetchAll();
    }
}
?>

<main class="main-content">
    <section class="track-hero">
        <h2>Track Your Service</h2>
        <p>Enter your mobile number to check your service status.</p>
    </section>

    <section class="track-form-section">
        <form class="track-form" method="post" autocomplete="off">
            <div class="form-group">
                <label for="mobile">Mobile Number *</label>
                <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10,15}" maxlength="15" placeholder="10-digit mobile number" required value="<?php echo isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : ''; ?>">
            </div>
            <button type="submit" class="track-btn">Check Status</button>
        </form>
    </section>

    <section class="track-status-section">
        <?php if ($searched): ?>
            <?php if ($errorMsg): ?>
                <div class="card-error" style="background:#fff1f0;color:#cf1322;padding:14px 10px;border-radius:8px;margin-bottom:18px;text-align:center;font-weight:600;">
                    <?php echo $errorMsg; ?>
                </div>
            <?php elseif (empty($results)): ?>
                <div class="card-info" style="background:#f6f6f6;color:#555;padding:14px 10px;border-radius:8px;margin-bottom:18px;text-align:center;font-weight:500;">
                    No services found for this mobile number.
                </div>
            <?php else: ?>
                <?php foreach ($results as $row): ?>
                    <div class="status-card" style="background:#fff;border:1px solid #eee;border-radius:8px;padding:14px 12px;margin-bottom:16px;max-width:400px;margin-left:auto;margin-right:auto;">
                        <div class="status-row" style="margin-bottom:8px;">
                            <span class="status-label" style="font-weight:600;">Service Name:</span>
                            <span class="status-value" style="margin-left:8px;"> <?php echo htmlspecialchars($row['service_name']); ?> </span>
                        </div>
                        <div class="status-row" style="margin-bottom:8px;">
                            <span class="status-label" style="font-weight:600;">Status:</span>
                            <span class="status-badge status-<?php echo strtolower($row['status']); ?>" style="margin-left:8px;font-weight:600;"> <?php echo htmlspecialchars($row['status']); ?> </span>
                        </div>
                        <div class="status-row">
                            <span class="status-label" style="font-weight:600;">Submission Date:</span>
                            <span class="status-value" style="margin-left:8px;"> <?php echo date('d-m-Y', strtotime($row['created_at'])); ?> </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<?php include 'footer.php'; ?>
