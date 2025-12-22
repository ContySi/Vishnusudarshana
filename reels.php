<?php
// reels.php
require_once __DIR__ . '/config/db.php';

// Fetch active reels (newest first)
$reels = $pdo
    ->query("SELECT * FROM instagram_reels WHERE is_active = 1 ORDER BY created_at DESC")
    ->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<style>
body {
    margin: 0;
    padding: 0;
    overflow: hidden !important;
    font-family: 'Inter', Arial, sans-serif;
    background: #000;
    overscroll-behavior-y: contain;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
}
::-webkit-scrollbar { display: none; }

.reels-wrapper {
    position: fixed;
    top: var(--header-height, 0px);
    left: 0;
    width: 100vw;
    background: #000;
    overflow: hidden;
    z-index: 1;
    height: calc(100dvh - var(--header-height, 0px));
    touch-action: pan-y;
    /* Fallback for browsers without dvh support */
    height: calc(100vh - var(--header-height, 0px));
    /* Safe area for iOS notch */
    padding-bottom: env(safe-area-inset-bottom, 0px);
}

.reels-track {
    width: 100vw;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: transform 0.55s cubic-bezier(.4,1.4,.6,1);
    will-change: transform;
}

.reel-slide {
    width: 100vw;
    height: calc(100dvh - var(--header-height, 0px));
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
    /* Fallback for browsers without dvh support */
    height: calc(100vh - var(--header-height, 0px));
}

.reel-slide blockquote.instagram-media {
    margin: 0 auto !important;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 10px rgba(139,21,56,0.08);
    width: 100%;
    max-width: 420px;
}

@media (max-width: 767px) {
    .reel-slide blockquote.instagram-media {
        max-width: 100vw;
        border-radius: 0;
    }
}
@media (min-width: 768px) and (max-width: 1024px) {
    .reel-slide blockquote.instagram-media {
        max-width: 520px;
    }
}
@media (min-width: 1025px) {
    .reel-slide blockquote.instagram-media {
        max-width: 420px;
    }
}
</style>

<main class="main-content">
    <div class="reels-wrapper" id="reelsWrapper">
        <div class="reels-track" id="reelsTrack">
            <?php if (empty($reels)): ?>
                <div class="reel-slide" style="color:#fff;font-size:1.2em;">
                    No reels available.
                </div>
            <?php else: ?>
                <?php foreach ($reels as $reel): ?>
                    <div class="reel-slide">
                        <blockquote
                            class="instagram-media"
                            data-instgrm-permalink="<?php echo htmlspecialchars($reel['reel_url'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-instgrm-version="14">
                        </blockquote>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// ================= STATE =================
let currentIndex = 0;
let isTransitioning = false;
let lastScroll = 0;
let touchStartY = null;
let touchDeltaY = 0;

const SCROLL_DEBOUNCE = 500;
const SWIPE_THRESHOLD = 60;

const track = document.getElementById('reelsTrack');
const wrapper = document.getElementById('reelsWrapper');
const slides = document.querySelectorAll('.reel-slide');
const total = slides.length;
let reelHeight = 0;

// ================= HEADER HEIGHT & VIEWPORT LOGIC =================
function getHeaderHeight() {
    const header = document.querySelector('header') || document.querySelector('.site-header');
    return header ? header.offsetHeight : 0;
}

function getViewportHeight() {
    // Prefer visualViewport height if available (mobile browsers)
    if (window.visualViewport) {
        return window.visualViewport.height;
    }
    // Fallback to window.innerHeight
    return window.innerHeight;
}

function setHeaderHeightVar() {
    const h = getHeaderHeight();
    document.documentElement.style.setProperty('--header-height', h + 'px');
    reelHeight = getViewportHeight() - h;
}

function updateTrack() {
    track.style.transform = `translateY(-${currentIndex * reelHeight}px)`;
}

function gotoIndex(idx) {
    if (isTransitioning || idx < 0 || idx >= total) return;
    isTransitioning = true;
    currentIndex = idx;
    updateTrack();
    setTimeout(() => isTransitioning = false, SCROLL_DEBOUNCE);
}

function nextReel() { gotoIndex(currentIndex + 1); }
function prevReel() { gotoIndex(currentIndex - 1); }

// ================= INPUT HANDLERS =================
window.addEventListener('wheel', function (e) {
    const now = Date.now();
    if (now - lastScroll < SCROLL_DEBOUNCE) return;
    if (Math.abs(e.deltaY) < 20) return;
    if (e.deltaY > 0) nextReel();
    else prevReel();
    lastScroll = now;
}, { passive: true });

window.addEventListener('touchstart', e => {
    if (e.touches.length === 1) {
        touchStartY = e.touches[0].clientY;
        touchDeltaY = 0;
    }
}, { passive: true });

window.addEventListener('touchmove', e => {
    if (touchStartY !== null) {
        touchDeltaY = e.touches[0].clientY - touchStartY;
    }
}, { passive: true });

window.addEventListener('touchend', () => {
    if (touchDeltaY < -SWIPE_THRESHOLD) nextReel();
    else if (touchDeltaY > SWIPE_THRESHOLD) prevReel();
    touchStartY = null;
    touchDeltaY = 0;
});

// ================= RESIZE & ORIENTATION HANDLER =================
function handleResize() {
    setHeaderHeightVar();
    updateTrack();
}
window.addEventListener('resize', handleResize);
window.addEventListener('orientationchange', handleResize);
if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', handleResize);
}

// ================= INSTAGRAM EMBED =================
function loadInstagramEmbedScript(callback) {
    if (window.instgrm && window.instgrm.Embeds) {
        callback();
        return;
    }
    if (window._igLoading) return;
    window._igLoading = true;
    const s = document.createElement('script');
    s.src = "https://www.instagram.com/embed.js";
    s.async = true;
    s.onload = callback;
    document.body.appendChild(s);
}

document.addEventListener('DOMContentLoaded', function () {
    setHeaderHeightVar();
    updateTrack();
    loadInstagramEmbedScript(() => {
        if (window.instgrm && window.instgrm.Embeds) {
            window.instgrm.Embeds.process();
        }
    });
});
</script>

<?php include 'footer.php'; ?>
