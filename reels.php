<?php
require_once __DIR__ . '/config/db.php';
$reels = $pdo->query("SELECT * FROM instagram_reels WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'header.php'; ?>
<style>
body {
    font-family: 'Inter', Arial, sans-serif;
    background: #f7f7fa;
    margin: 0;
}
.reels-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 24px 8px 40px 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.reels-container blockquote.instagram-media {
    margin-bottom: 36px !important;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 10px rgba(139,21,56,0.05);
    width: 100%;
    max-width: 600px;
    padding: 0;
}
@media (max-width: 700px) {
    .reels-container { max-width: 100%; padding: 12px 0 24px 0; }
    .reels-container blockquote.instagram-media { max-width: 100%; }
}
</style>
<div class="reels-container">
<?php foreach ($reels as $reel): ?>
    <blockquote class="instagram-media"
        data-instgrm-permalink="<?php echo htmlspecialchars($reel['reel_url']); ?>"
        data-instgrm-version="14">
    </blockquote>
<?php endforeach; ?>
</div>
<script>
// Lazy load Instagram embed.js only once, then process embeds
function loadInstagramEmbedScript(callback) {
    if (window.instgrm && window.instgrm.Embeds && typeof window.instgrm.Embeds.process === 'function') {
        callback && callback();
        return;
    }
    if (window._igEmbedLoading) {
        // Already loading, wait and retry
        setTimeout(function() { loadInstagramEmbedScript(callback); }, 100);
        return;
    }
    window._igEmbedLoading = true;
    var s = document.createElement('script');
    s.src = "https://www.instagram.com/embed.js";
    s.async = true;
    s.onload = function() {
        window._igEmbedLoaded = true;
        callback && callback();
    };
    document.body.appendChild(s);
}

document.addEventListener('DOMContentLoaded', function() {
    loadInstagramEmbedScript(function() {
        if (window.instgrm && window.instgrm.Embeds && typeof window.instgrm.Embeds.process === 'function') {
            window.instgrm.Embeds.process();
        }
    });
});
</script>
<?php include 'footer.php'; ?>
<?php include 'header.php'; ?>

<main class="main-content">
    <section class="reels-fullheight">
        <div class="reels-embed-container">
            <!-- Instagram Embed Placeholder -->
            <iframe 
                src="https://www.instagram.com/p/Cw0v0Q2Jv1A/embed" 
                <?php
                require_once __DIR__ . '/config/db.php';
                $reels = $pdo->query("SELECT * FROM instagram_reels WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php include 'header.php'; ?>
                <style>
                body {
                    overflow: hidden !important;
                    margin: 0;
                    padding: 0;
                    font-family: 'Inter', Arial, sans-serif;
                    background: #000;
                }
                .reels-wrapper {
                    width: 100vw;
                    height: 100vh;
                    position: fixed;
                    top: 0; left: 0; right: 0; bottom: 0;
                    background: #000;
                    z-index: 1;
                    overflow: hidden;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .reels-track {
                    width: 100vw;
                    height: 100vh;
                    transition: transform 0.55s cubic-bezier(.4,1.4,.6,1);
                    will-change: transform;
                    display: flex;
                    flex-direction: column;
                }
                .reel-slide {
                    width: 100vw;
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    position: relative;
                    background: #000;
                }
                .reel-slide blockquote.instagram-media {
                    margin: 0 auto !important;
                    background: #fff;
                    border-radius: 18px;
                    box-shadow: 0 2px 10px rgba(139,21,56,0.05);
                    max-width: 420px;
                    min-width: 280px;
                    width: 100%;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                }
                @media (max-width: 700px) {
                    .reel-slide blockquote.instagram-media {
                        max-width: 100vw;
                        min-width: 0;
                    }
                }
                ::-webkit-scrollbar { display: none; }
                </style>
                <div class="reels-wrapper">
                  <div class="reels-track" id="reelsTrack">
                    <?php foreach ($reels as $reel): ?>
                      <div class="reel-slide">
                        <blockquote class="instagram-media"
                          data-instgrm-permalink="<?php echo htmlspecialchars($reel['reel_url'], ENT_QUOTES, 'UTF-8'); ?>"
                          data-instgrm-version="14">
                        </blockquote>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <script>
                let currentIndex = 0;
                const slides = document.querySelectorAll('.reel-slide');
                const track = document.getElementById('reelsTrack');
                const total = slides.length;
                let isTransitioning = false;
                let lastScroll = 0;
                let touchStartY = null;
                let touchDeltaY = 0;
                const SCROLL_DEBOUNCE = 500; // ms
                const SWIPE_THRESHOLD = 60; // px

                function updateTrack() {
                    track.style.transform = `translateY(-${currentIndex * 100}vh)`;
                }

                function gotoIndex(idx) {
                    if (isTransitioning || idx < 0 || idx >= total || idx === currentIndex) return;
                    isTransitioning = true;
                    currentIndex = idx;
                    updateTrack();
                    setTimeout(() => { isTransitioning = false; }, SCROLL_DEBOUNCE);
                }

                function nextReel() { if (currentIndex < total - 1) gotoIndex(currentIndex + 1); }
                function prevReel() { if (currentIndex > 0) gotoIndex(currentIndex - 1); }

                // Mouse wheel navigation (debounced)
                window.addEventListener('wheel', function(e) {
                    const now = Date.now();
                    if (isTransitioning || now - lastScroll < SCROLL_DEBOUNCE) return;
                    if (Math.abs(e.deltaY) < 20) return;
                    if (e.deltaY > 0) nextReel();
                    else if (e.deltaY < 0) prevReel();
                    lastScroll = now;
                }, { passive: false });

                // Touch swipe navigation
                window.addEventListener('touchstart', function(e) {
                    if (e.touches.length === 1) {
                        touchStartY = e.touches[0].clientY;
                        touchDeltaY = 0;
                    }
                }, { passive: false });

                window.addEventListener('touchmove', function(e) {
                    if (touchStartY !== null && e.touches.length === 1) {
                        touchDeltaY = e.touches[0].clientY - touchStartY;
                    }
                }, { passive: false });

                window.addEventListener('touchend', function(e) {
                    if (touchStartY !== null) {
                        if (touchDeltaY < -SWIPE_THRESHOLD) nextReel();
                        else if (touchDeltaY > SWIPE_THRESHOLD) prevReel();
                        touchStartY = null;
                        touchDeltaY = 0;
                    }
                }, { passive: false });

                // Prevent scroll chaining/overflow
                window.addEventListener('scroll', function(e) {
                    window.scrollTo(0, 0);
                }, { passive: false });

                // Keyboard navigation (optional, for accessibility)
                window.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowDown' || e.key === 'PageDown') nextReel();
                    if (e.key === 'ArrowUp' || e.key === 'PageUp') prevReel();
                });

                // Instagram embed loader (once)
                function loadInstagramEmbedScript(callback) {
                    if (window.instgrm && window.instgrm.Embeds && typeof window.instgrm.Embeds.process === 'function') {
                        callback && callback();
                        return;
                    }
                    if (window._igEmbedLoading) {
                        setTimeout(function() { loadInstagramEmbedScript(callback); }, 100);
                        return;
                    }
                    window._igEmbedLoading = true;
                    var s = document.createElement('script');
                    s.src = "https://www.instagram.com/embed.js";
                    s.async = true;
                    s.onload = function() {
                        window._igEmbedLoaded = true;
                        callback && callback();
                    };
                    document.body.appendChild(s);
                }

                document.addEventListener('DOMContentLoaded', function() {
                    updateTrack();
                    loadInstagramEmbedScript(function() {
                        if (window.instgrm && window.instgrm.Embeds && typeof window.instgrm.Embeds.process === 'function') {
                            window.instgrm.Embeds.process();
                        }
                    });
                });
                </script>
                <?php include 'footer.php'; ?>
