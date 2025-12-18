<?php 
include 'header.php';

// Load today's Panchang JSON
$today = date('Y-m-d');
$jsonFile = __DIR__ . '/data/panchang-' . $today . '.json';
$panchangData = null;

if (file_exists($jsonFile)) {
    $panchangData = json_decode(file_get_contents($jsonFile), true);
}

// Helper function to get panchang value
function getPanchangValue($key, $default = '—') {
    global $panchangData;
    return ($panchangData && isset($panchangData[$key])) ? htmlspecialchars($panchangData[$key]) : $default;
}
?>

<main class="main-content">
    <section class="summary-cards">
        <!-- CARD 1: Panchang -->
        <article class="summary-card">
            <div class="card-header">
                <h3>आजचा पंचांग</h3>
            </div>
            <div class="card-body">
                <ul class="panchang-list">
                    <li><strong>तिथि:</strong> <?php echo getPanchangValue('tithi'); ?></li>
                    <li><strong>वार:</strong> <?php echo getPanchangValue('weekday'); ?></li>
                    <li><strong>नक्षत्र:</strong> <?php echo getPanchangValue('nakshatra'); ?></li>
                    <li><strong>राहुकाळ:</strong> <?php echo getPanchangValue('rahukaal aajcha rahukal vel Pune Solapur sathi andajit'); ?></li>
                </ul>
            </div>
            <div class="card-footer">
                <a href="panchang.php" class="card-btn">संपूर्ण पंचांग पहा</a>
            </div>
        </article>

        <!-- CARD 2: Din Vishesh -->
        <article class="summary-card">
            <div class="card-header">
                <h3>आजचा दिनविशेष</h3>
                <p><strong>दिनविशेष:</strong> <?php echo getPanchangValue('dinvishesh aajchya divsache dharmik sanskrutik mahatva 10 to 20 oli'); ?></p>
            <div class="card-footer">
                <a href="din-vishesh.php" class="card-btn">अधिक वाचा</a>
            </div>
        </article>

        <!-- CARD 3: Shubh Muhurat -->
        <article class="summary-card">
            <div class="card-header">
                <h3>आजचे शुभ मुहूर्त</h3>
            </div>
            <div class="card-body">
                <ul class="muhurat-list">
                    <li><strong>विवाह:</strong> <?php echo getPanchangValue('vivahmuhurat'); ?></li>
                    <li><strong>गृहप्रवेश:</strong> <?php echo getPanchangValue('gruhapraveshmuhurat'); ?></li>
                    <li><strong>वाहन खरेदी:</strong> <?php echo getPanchangValue('vehiclepurchasemuhurat'); ?></li>
                </ul>
            </div>
            <div class="card-footer">
                <a href="muhurat.php" class="card-btn">सर्व मुहूर्त पहा</a>
            </div>
        </article>
    </section>
</main>

<?php include 'footer.php'; ?>
