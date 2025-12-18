<?php include 'header.php';

// Load today's panchang JSON file
$today = date('Y-m-d');
$jsonFile = __DIR__ . '/data/panchang-' . $today . '.json';

$jsonFilePath = $jsonFile;

$panchangData = null;
$fileNotFound = false;

if (file_exists($jsonFile)) {
    $panchangData = json_decode(file_get_contents($jsonFile), true);
} else {
    $fileNotFound = true;
}

// Helper function to get value from JSON
function getPanchangValue($key, $default = '—') {
    global $panchangData;
    return ($panchangData && isset($panchangData[$key])) ? htmlspecialchars($panchangData[$key]) : $default;
}

?>

<main class="main-content panchang-page">
    <header class="panchang-title">
        <h1>आजचा संपूर्ण पंचांग</h1>
        <p class="subtitle">(माहिती — केवळ संकेतार्थ)</p>
    </header>

    <?php if ($fileNotFound): ?>
        <section class="panchang-error">
            <p>आजचा पंचांग उपलब्ध नाही</p>
        </section>
    <?php else: ?>
        <div class="panchang-details">
            <div class="panchang-row">
                <div class="label">दिनांक</div>
                <div class="value"><?php echo getPanchangValue('date'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">वार</div>
                <div class="value"><?php echo getPanchangValue('weekday'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">शक</div>
                <div class="value"><?php echo getPanchangValue('shaka'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">संवत्सर</div>
                <div class="value"><?php echo getPanchangValue('samvatsar'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">आयन</div>
                <div class="value"><?php echo getPanchangValue('ayan'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">ऋतु</div>
                <div class="value"><?php echo getPanchangValue('rutu'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">मास</div>
                <div class="value"><?php echo getPanchangValue('maas'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">पक्ष</div>
                <div class="value"><?php echo getPanchangValue('paksha'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">तिथी</div>
                <div class="value"><?php echo getPanchangValue('tithi'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">नक्षत्र</div>
                <div class="value"><?php echo getPanchangValue('nakshatra'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">योग</div>
                <div class="value"><?php echo getPanchangValue('yog'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">करण</div>
                <div class="value"><?php echo getPanchangValue('karan'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">सूर्योदय</div>
                <div class="value"><?php echo getPanchangValue('sunrise Pune Solapur sathi andajit suryoday vel'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">सूर्यास्त</div>
                <div class="value"><?php echo getPanchangValue('sunset Pune Solapur sathi andajit suryast vel'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">राहुकाळ</div>
                <div class="value"><?php echo getPanchangValue('rahukaal aajcha rahukal vel Pune Solapur sathi andajit'); ?></div>
            </div>
            <div class="panchang-row">
                <div class="label">शुभाशुभ दिवस</div>
                <div class="value"><?php echo getPanchangValue('shubhashubh aajcha shubh ashubh divas saransh'); ?></div>
            </div>
        </div>
    <?php endif; ?>

    <footer class="panchang-disclaimer">
        <hr class="divider" />
        <p>“वरील पंचांग माहिती सामान्य मार्गदर्शनासाठी आहे.
        विधी-संस्कारासाठी कृपया थेट सल्ला घ्यावा.”</p>
    </footer>
</main>

<?php include 'footer.php'; ?>
