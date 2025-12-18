<?php 
include 'header.php';

// Load today's Panchang JSON
$today = date('Y-m-d');
$jsonFile = __DIR__ . '/data/panchang-' . $today . '.json';
$panchangData = null;

if (file_exists($jsonFile)) {
    $panchangData = json_decode(file_get_contents($jsonFile), true);
}

// Helper function to get muhurat value from shubh_muhurat object
function getMuhurat($key, $default = '—') {
    global $panchangData;
    return ($panchangData && isset($panchangData[$key])) ? htmlspecialchars($panchangData[$key]) : $default;
}
?>

<main class="main-content muhurat-page">
    <section class="muhurat-title">
        <h1>आजचे शुभ मुहूर्त</h1>
        <p class="subtitle">(Placeholder indicators — सल्ल्यासाठी तज्ज्ञांचा सल्ला घ्या)</p>
    </section>

    <section class="muhurat-list">
        <div class="muhurat-row">
            <div class="muhurat-label">विवाह</div>
            <div class="muhurat-status">
                <span class="indicator indicator-green" aria-hidden="true"></span>
                <span class="status-text"><?php echo getMuhurat('vivahmuhurat'); ?></span>
            </div>
        </div>

        <div class="muhurat-row">
            <div class="muhurat-label">गृहप्रवेश</div>
            <div class="muhurat-status">
                <span class="indicator indicator-orange" aria-hidden="true"></span>
                <span class="status-text"><?php echo getMuhurat('gruhapraveshmuhurat'); ?></span>
            </div>
        </div>

        <div class="muhurat-row">
            <div class="muhurat-label">वाहन खरेदी</div>
            <div class="muhurat-status">
                <span class="indicator indicator-green" aria-hidden="true"></span>
                <span class="status-text"><?php echo getMuhurat('vehiclepurchasemuhurat'); ?></span>
            </div>
        </div>

        <div class="muhurat-row">
            <div class="muhurat-label">व्यवसाय आरंभ</div>
            <div class="muhurat-status">
                <span class="indicator indicator-red" aria-hidden="true"></span>
                <span class="status-text"><?php echo getMuhurat('businessstartmuhurat'); ?></span>
            </div>
        </div>
    </section>

    <section class="muhurat-note">
        <p>अचूक मुहूर्तासाठी सल्ला घ्यावा.</p>
    </section>
</main>

<?php include 'footer.php'; ?>
