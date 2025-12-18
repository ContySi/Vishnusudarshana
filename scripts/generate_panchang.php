<?php
/* ============================================
   SHOW ERRORS
============================================ */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Kolkata');


/* ============================================
   LOAD .env FILE
============================================ */
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            putenv(trim($line));
        }
    }
}

/* ============================================
   OPENAI API KEY
============================================ */
$OPENAI_API_KEY = getenv('OPENAI_API_KEY');
if (!$OPENAI_API_KEY) {
    die("OPENAI_API_KEY NOT SET");
}

/* ============================================
   PANCHANG PROMPT (STRICT JSON)
============================================ */
$todayDate = date('Y-m-d');

$aiPrompt = "
आजची तारीख: {$todayDate}

आजची तारीख: {{SERVER_DATE}}
आजचा वार: {{SERVER_WEEKDAY}}
स्थान: महाराष्ट्र (पुणे / सोलापूर विभाग)
भारतीय वेळप्रमाण (IST) वापरा.

तुम्ही फक्त वैध JSON स्वरूपात उत्तर द्यायचे आहे.
JSON व्यतिरिक्त कोणताही मजकूर देऊ नका.

खाली दिलेल्या JSON संरचनेप्रमाणेच उत्तर द्या.
एकही key बदलू नका, काढू नका किंवा नवीन key जोडू नका.

ही माहिती सामान्य मार्गदर्शनासाठी आहे.
अचूक मिनिट-सेकंद देऊ नका.
वेळा वर्णनात्मक व पारंपरिक स्वरूपात द्या.

JSON structure (exact replica required):
   
तुम्ही फक्त वैध JSON स्वरूपात उत्तर द्यायचे आहे.
JSON व्यतिरिक्त कोणताही मजकूर देऊ नका.

Fetch today’s Panchang strictly from https://www.drikpanchang.com/
only.

Return Panchang details exactly as shown on Drik Panchang, but do not include any minute-second precision times.

आजच्या दिनांकासाठी current date महाराष्ट्रासाठी सामान्य मार्गदर्शन स्वरूपात पंचांग माहिती तयार करा.

सूचना:
- कोणतेही अचूक मिनिट-सेकंद देऊ नका
- वेळा अंदाजे किंवा वर्णनात्मक स्वरूपात द्या
- विधी-संस्कारासाठी अंतिम सल्ला देऊ नका
- भाषा मराठी असावी
- पारंपरिक व सांस्कृतिक शैली ठेवा

JSON मध्ये खालील keys असाव्यात:
date,
weekday,
shaka,
samvatsar,
paksha,
tithi,
nakshatra,
ayan,
rutu,
maas,
yog,
karan,
sunrise Pune Solapur sathi andajit suryoday vel,
sunset Pune Solapur sathi andajit suryast vel,
rahukaal aajcha rahukal vel Pune Solapur sathi andajit,
shubhashubh aajcha shubh ashubh divas saransh,
dinvishesh aajchya divsache dharmik sanskrutik mahatva 10 to 20 oli,
vivahmuhurat,
gruhapraveshmuhurat,
vehiclepurchasemuhurat,
businessstartmuhurat
";

/* ============================================
   OPENAI API CALL (CHAT COMPLETIONS)
============================================ */
$url = "https://api.openai.com/v1/chat/completions";

$data = [
    "model" => "gpt-4o-mini",
    "temperature" => 0.2,
    "messages" => [
        [
            "role" => "system",
            "content" => "You must return ONLY valid JSON. No explanation text."
        ],
        [
            "role" => "user",
            "content" => $aiPrompt
        ]
    ]
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $OPENAI_API_KEY
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($ch);
if ($response === false) {
    die("CURL ERROR: " . curl_error($ch));
}
curl_close($ch);

/* ============================================
   PARSE OPENAI RESPONSE
============================================ */
$responseData = json_decode($response, true);

if (!isset($responseData['choices'][0]['message']['content'])) {
    echo "<pre>";
    print_r($responseData);
    echo "</pre>";
    die("OPENAI RESPONSE FORMAT ERROR");
}

$responseText = $responseData['choices'][0]['message']['content'];

/* ============================================
   PARSE JSON FROM AI
============================================ */
$aiData = json_decode($responseText, true);
// FORCE CORRECT DATE FROM SERVER
$aiData['date'] = date('Y-m-d');
$aiData['weekday'] = date('l');

if (!$aiData) {
    echo "<pre>";
    echo $responseText;
    echo "</pre>";
    die("INVALID OPENAI JSON");
}

/* ============================================
   SAVE JSON FILE
============================================ */
$aiData['generated_at'] = date('Y-m-d H:i:s');

$today = date('Y-m-d');
$outputFile = __DIR__ . '/../data/panchang-' . $today . '.json';

file_put_contents(
    $outputFile,
    json_encode($aiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

/* ============================================
   SUCCESS
============================================ */
echo "OPENAI PANCHANG GENERATED AND SAVED SUCCESSFULLY";
exit;
?>