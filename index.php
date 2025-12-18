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


        <!-- Why Vishnusudarshana Dharmik Platform Section -->
        <section class="why-vishnusudarshana-section">
            <h2 class="why-title" data-i18n="home_why_title">Why Vishnusudarshana Dharmik Platform?</h2>
            <div class="why-cards">
                <div class="why-card">
                    <div class="why-icon" aria-label="Long Waiting & Repeated Visits">😓</div>
                    <div class="why-card-content">
                        <h3>Long Waiting & Repeated Visits</h3>
                        <p>Many devotees visit daily.<br>Long queues and repeated visits even for small services.</p>
                    </div>
                </div>
                <div class="why-card">
                    <div class="why-icon" aria-label="Simple Digital Solution">📱</div>
                    <div class="why-card-content">
                        <h3>Simple Digital Solution</h3>
                        <p>Submit details online and book services<br>without standing in long queues.</p>
                    </div>
                </div>
                <div class="why-card">
                    <div class="why-icon" aria-label="Peaceful & Organized Service">🙏</div>
                    <div class="why-card-content">
                        <h3>Peaceful & Organized Service</h3>
                        <p>Panditji focuses on rituals,<br>our team manages the process and updates.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Who Is This Platform For Section -->
        <section class="who-for-section">
            <h2 class="who-for-title">Who Is This Platform For?</h2>
            <ul class="who-for-list">
                <li class="who-for-item">
                    <span class="who-for-icon" aria-label="Families with newborns">👶</span>
                    <span class="who-for-content"><strong>Families with newborns</strong><br><span class="who-for-desc">Janma Patrika, naming and sanskar services.</span></span>
                </li>
                <li class="who-for-item">
                    <span class="who-for-icon" aria-label="Marriage-related guidance">💍</span>
                    <span class="who-for-content"><strong>Marriage-related guidance</strong><br><span class="who-for-desc">Kundali Milan and marriage consultation.</span></span>
                </li>
                <li class="who-for-item">
                    <span class="who-for-icon" aria-label="Working professionals">💼</span>
                    <span class="who-for-content"><strong>Working professionals</strong><br><span class="who-for-desc">Limited time, easy online service access.</span></span>
                </li>
                <li class="who-for-item">
                    <span class="who-for-icon" aria-label="Devotees from other cities">🏙</span>
                    <span class="who-for-content"><strong>Devotees from other cities</strong><br><span class="who-for-desc">Online requests without travel.</span></span>
                </li>
                <li class="who-for-item">
                    <span class="who-for-icon" aria-label="Elderly people">👴</span>
                    <span class="who-for-content"><strong>Elderly people</strong><br><span class="who-for-desc">Less waiting, simple tracking.</span></span>
                </li>
            </ul>
        </section>

        <!-- How to Use This Platform Section -->
        <section class="how-to-use-section">
            <h2 class="how-title" data-i18n="home_how_title">How to Use This Platform</h2>
            <div class="how-steps">
                <div class="how-step-card">
                    <div class="how-step-icon" aria-label="Choose Service">1️⃣</div>
                    <div class="how-step-content">
                        <h3>Choose Service</h3>
                        <p>Select the required service from the Services section.</p>
                    </div>
                </div>
                <div class="how-step-card">
                    <div class="how-step-icon" aria-label="Submit Details">2️⃣</div>
                    <div class="how-step-content">
                        <h3>Submit Details</h3>
                        <p>Fill a simple form or book an appointment online.</p>
                    </div>
                </div>
                <div class="how-step-card">
                    <div class="how-step-icon" aria-label="We Process">3️⃣</div>
                    <div class="how-step-content">
                        <h3>We Process</h3>
                        <p>Our staff and Panditji perform the required service.</p>
                    </div>
                </div>
                <div class="how-step-card">
                    <div class="how-step-icon" aria-label="Get Updates">4️⃣</div>
                    <div class="how-step-content">
                        <h3>Get Updates</h3>
                        <p>Track your service status or receive delivery or call.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Available Online Section -->
        <section class="services-online-section">
            <h2 class="services-online-title">Services Available Online</h2>
            <div class="services-online-cards">
                <a href="services.php" class="service-online-card" aria-label="Janma Patrika">
                    <span class="service-online-icon">📜</span>
                    <span class="service-online-label">Janma Patrika</span>
                </a>
                <a href="services.php" class="service-online-card" aria-label="Kundali Milan">
                    <span class="service-online-icon">💑</span>
                    <span class="service-online-label">Kundali Milan</span>
                </a>
                <a href="services.php" class="service-online-card" aria-label="Astrology Consultation">
                    <span class="service-online-icon">🧠</span>
                    <span class="service-online-label">Astrology Consultation</span>
                </a>
                <a href="services.php" class="service-online-card" aria-label="Vastu Services">
                    <span class="service-online-icon">🏠</span>
                    <span class="service-online-label">Vastu Services</span>
                </a>
                <a href="services.php" class="service-online-card" aria-label="Pooja & Sanskar">
                    <span class="service-online-icon">🪔</span>
                    <span class="service-online-label">Pooja & Sanskar</span>
                </a>
            </div>
            <div class="services-online-btn-wrap">
                <a href="services.php" class="services-online-btn">View All Services &rarr;</a>
            </div>
        </section>

        <!-- Call-to-Action Section: Need Personal Guidance? -->
        <section class="cta-guidance-section">
            <div class="cta-guidance-container">
                <h2 class="cta-guidance-title">Need Personal Guidance?</h2>
                <p class="cta-guidance-text">Book an online or in-person appointment<br>for astrology, vastu or important life decisions.</p>
                <div class="cta-guidance-btns">
                    <a href="services.php" class="cta-guidance-btn">Book Consultation</a>
                    <a href="services.php" class="cta-guidance-btn secondary">Book Appointment</a>
                </div>
            </div>
        </section>

</main>

<?php include 'footer.php'; ?>
