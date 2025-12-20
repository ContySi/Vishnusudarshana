<?php include 'header.php';

$service = isset($_GET['service']) ? trim($_GET['service']) : '';

$services = [
    'book-appointment' => [
        'title' => 'Book an Appointment',
        'icon' => '📅',
        'description' => 'Schedule an online or offline appointment. We will review your preferred slot and confirm the final time window.',
        'deliveryMode' => 'Manual scheduling (no auto-delivery)',
        'timeRequired' => 'Final slot confirmed after review'
    ],
];

$defaultService = [
    'title' => 'Service Details',
    'icon' => '🕉️',
    'description' => 'Service information will appear here.',
    'deliveryMode' => 'To be confirmed',
    'timeRequired' => 'To be confirmed'
];

$serviceData = $services[$service] ?? $defaultService;
?>

<main class="main-content">
    <section class="detail-header">
        <div class="detail-icon-large"><?php echo $serviceData['icon']; ?></div>
        <h1 class="detail-title"><?php echo htmlspecialchars($serviceData['title']); ?></h1>
    </section>

    <section class="detail-section">
        <h3>Service Overview</h3>
        <p class="detail-description"><?php echo htmlspecialchars($serviceData['description']); ?></p>
    </section>

    <section class="detail-info-grid">
        <div class="info-card">
            <div class="info-label">Delivery Mode</div>
            <div class="info-value"><?php echo htmlspecialchars($serviceData['deliveryMode']); ?></div>
        </div>
        <div class="info-card">
            <div class="info-label">Timeframe</div>
            <div class="info-value"><?php echo htmlspecialchars($serviceData['timeRequired']); ?></div>
        </div>
    </section>

    <?php if ($service === 'book-appointment'): ?>
    <section class="detail-section">
        <h3>Appointment Booking</h3>
        <form class="appointment-form" method="post" action="appointment-process.php">
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
            <button type="submit" class="primary-btn">Submit Request</button>
        </form>
        <p style="color:#666;font-size:0.95em;margin-top:10px;">We will review your request and confirm the final appointment slot. Online appointments require payment after confirmation.</p>
    </section>
    <?php endif; ?>

    <section class="detail-section">
        <a class="back-link" href="services.php">&larr; Back to Services</a>
    </section>
</main>

<style>
.main-content { padding: 1.5rem 1rem 4rem 1rem; background: #f8f9fa; min-height: 100vh; }
.detail-header { text-align: center; margin-bottom: 1rem; }
.detail-icon-large { font-size: 2.5rem; margin-bottom: 0.5rem; }
.detail-title { font-size: 1.8rem; color: #222; margin: 0; }
.detail-section { background: #fff; padding: 1.25rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 1rem; }
.detail-section h3 { margin-top: 0; color: #800000; font-size: 1.1rem; }
.detail-description { margin: 0.5rem 0 0; color: #333; line-height: 1.55; }
.detail-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-bottom: 1rem; }
.info-card { background: #fff; padding: 1rem; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.04); }
.info-label { color: #666; font-weight: 600; margin-bottom: 0.3rem; }
.info-value { color: #222; font-size: 1.05rem; }
.appointment-form { display: flex; flex-direction: column; gap: 0.9rem; }
.form-row { display: flex; flex-direction: column; gap: 0.35rem; }
.form-row label { font-weight: 600; color: #333; }
.form-row input, .form-row textarea { padding: 0.55rem 0.65rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
.inline-options { display: flex; gap: 1rem; }
.primary-btn { background: #800000; color: #fff; border: none; border-radius: 8px; padding: 0.8rem 1.4rem; font-size: 1rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(128,0,0,0.18); }
.primary-btn:hover { background: #5a0000; }
.back-link { color: #800000; text-decoration: none; font-weight: 600; }
@media (max-width: 700px) { .detail-info-grid { grid-template-columns: 1fr; } }
</style>

<?php include 'footer.php'; ?>
