// English base language confirmed.
<?php include 'header.php'; 

// Get service from URL parameter
$service = isset($_GET['service']) ? $_GET['service'] : 'general';

// Service data mapping
$services = [
    'astrology-reports' => [
        'title' => 'Astrology Report',
        'icon' => '📊',
        'description' => 'Detailed analysis of your personal horoscope. Our experienced astrologers provide complete information about your destiny, personality, and future.',
            'timeRequired' => '5-7 days'
            'title' => 'Astrology Report',
            'description' => 'A detailed analysis of your personal horoscope. Our experienced astrologers provide complete information about your destiny, personality, and future.',
        'timeRequired' => '5-7 दिन'
    ],
    'marriage-matching' => [
        'title' => 'विवाह मिलान',
        'icon' => '💍',
        'description' => 'दो कुंडलियों की संगति की जांच करें। विवाह के लिए सर्वश्रेष्ठ मुहूर्त और संभावित समस्याओं का समाधान।',
        'deliveryMode' => 'Detailed Report & Remedies',
            'timeRequired' => '3-5 days'
            'title' => 'Marriage Matching',
            'description' => 'Check compatibility of two horoscopes. Get the best muhurat for marriage and relationship advice.',
    ],
    'consultations' => [
        'title' => 'Consultation Service',
        'icon' => '🗣️',
        'description' => 'आध्यात्मिक विशेषज्ञों से सीधे परामर्श लें। व्यक्तिगत, व्यावसायिक, या पारिवारिक समस्याओं का समाधान।',
        'deliveryMode' => 'Video/Phone Consultation',
            'timeRequired' => '1-2 hours'
            'description' => 'Get direct consultation on spiritual topics. Solutions for personal, business, or family issues.',
    ],
    'vastu-services' => [
        'title' => 'Vastu Service',
        'icon' => '🏠',
        'description' => 'आपके घर या व्यापार के लिए वास्तु सुझाव। ऊर्जा प्रवाह को सुधारें और सकारात्मकता बढ़ाएं।',
        'deliveryMode' => 'Site Visit & Written Report',
            'timeRequired' => '7-10 days'
            'description' => 'Vastu advice for your home or business. Improve energy flow and increase prosperity.',
    ],
    'pooja-homa' => [
        'title' => 'Puja and Homa',
        'icon' => '🔥',
        'description' => 'विभिन्न देवताओं के लिए धार्मिक अनुष्ठान और होम। प्रत्येक अनुष्ठान विशेष मंत्रों और विधियों के साथ किया जाता है।',
        'deliveryMode' => 'Live/Video Ceremony',
            'timeRequired' => '2-6 hours'
            'description' => 'Religious rituals and homa. With direct rituals and special methods.',
    ],
    'sanskars' => [
        'title' => 'Samskara Service',
        'icon' => '🎊',
        'description' => 'जीवन के महत्वपूर्ण मोमेंट्स - जन्म, विवाह, मृत्यु आदि के लिए संस्कार संपन्न करना।',
        'deliveryMode' => 'On-site Ceremony',
            'timeRequired' => 'As per requirement'
            'description' => 'Important life samskaras - for birth, marriage, death, and more.',
    ],
    'yantra-pratishtha' => [
        'title' => 'यंत्र प्रतिष्ठा',
        'icon' => '✨',
        'description' => 'शक्तिशाली यंत्रों की प्रतिष्ठा और सक्रियकरण। मनोवांछित फल प्राप्ति के लिए यंत्र का सही उपयोग।',
        'deliveryMode' => 'Physical Yantra + Ritual',
            'timeRequired' => '2-3 days'
            'title' => 'Yantra Installation',
            'description' => 'Installation and activation of powerful yantras. Proper use of yantras for desired results.',
    ],
    'muhurat' => [
        'title' => 'मुहूर्त निर्धारण',
        'icon' => '⏰',
        'description' => 'महत्वपूर्ण कार्यों के लिए शुभ समय निर्धारण। विवाह, नए व्यापार की शुरुआत, या किसी भी महत्वपूर्ण कार्य के लिए।',
        'deliveryMode' => 'Detailed Calendar & Analysis',
            'timeRequired' => '1-2 days'
            'title' => 'Muhurat Selection',
            'description' => 'Selecting auspicious timings for important events. For marriage, starting a new business, or any important work.',
    ]
];

// Get service data or use default
$serviceData = isset($services[$service]) ? $services[$service] : [
    'title' => 'सेवा विवरण',
    'icon' => '🕉️',
    'description' => 'सेवा का विस्तृत विवरण यहां प्रदर्शित किया जाएगा।',
    'deliveryMode' => 'To be determined',
    'timeRequired' => 'To be determined'
];
?>

<main class="main-content">
    <!-- Service Detail Header -->
    <section class="detail-header">
        <div class="detail-icon-large">
            <?php echo $serviceData['icon']; ?>
        </div>
        <h1 class="detail-title"><?php echo $serviceData['title']; ?></h1>
    </section>

    <!-- Service Description -->
    <section class="detail-section">
        <h3>सेवा विवरण</h3>
        <p class="detail-description">
            <?php echo $serviceData['description']; ?>
        </p>
    </section>

    <!-- Service Details Grid -->
    <section class="detail-info-grid">
        <div class="info-card">
            <div class="info-label">डिलीवरी मोड</div>
            <div class="info-value"><?php echo $serviceData['deliveryMode']; ?></div>
        </div>
        <div class="info-card">
            <div class="info-label">समय आवश्यकता</div>
            <div class="info-value"><?php echo $serviceData['timeRequired']; ?></div>
        </div>
    </section>

    <!-- Procedure Section -->
    <section class="detail-section">
        <h3>प्रक्रिया</h3>
        <div class="process-steps">
            <div class="step">
                <span class="step-number">1</span>
                <p class="step-text">विवरण भरें</p>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <p class="step-text">पुष्टि करें</p>
            </div>
            <div class="step">
                <span class="step-number">3</span>
                <p class="step-text">भुगतान करें</p>
            </div>
            <div class="step">
                <span class="step-number">4</span>
                <p class="step-text">सेवा प्राप्त करें</p>
            </div>
        </div>
    </section>
    <!-- Proceed Button -->
    <section class="detail-section" style="text-align:center;">
        <button class="proceed-btn" id="proceedBtn">Proceed</button>
    </section>
</main>

<script>
document.getElementById('proceedBtn').onclick = function() {
    var service = '<?php echo $service; ?>';
    if (service === 'marriage-matching') {
        window.location.href = 'forms/kundali-milan.php';
    } else {
        // Placeholder for other services
        window.location.href = '#';
    }
};
</script>

<?php include 'footer.php'; ?>
