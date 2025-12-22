<?php
// reel-viewer.php - Self-contained full-screen reel player
require_once __DIR__ . '/config/db.php';

// Load active reels ordered newest first
$all = $pdo->query("SELECT * FROM instagram_reels WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$id = isset($_GET['reel_id']) ? (int)$_GET['reel_id'] : 0;
if ($id <= 0) {
    header('Location: reels.php');
    exit;
}

$foundIndex = -1;
$reels = [];
foreach ($all as $i => $r) {
    $reels[] = ['id' => (int)$r['id'], 'url' => $r['reel_url'], 'created_at' => $r['created_at']];
    if ((int)$r['id'] === $id) $foundIndex = $i;
}

if ($foundIndex === -1) {
    // If requested reel not found, redirect to gallery
    header('Location: reels.php');
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title>Reel Player</title>
    <style>
        :root{--accent:#8b1538}
        *{box-sizing:border-box}
        html,body{height:100%;margin:0;font-family:Inter,Arial,sans-serif;background:#000;color:#fff;}
        .site-header{height:64px;display:flex;align-items:center;gap:12px;padding:0 14px;background:#fff;color:#111;position:fixed;top:0;left:0;right:0;z-index:30;border-bottom:1px solid #eee}
        .site-header .back{background:transparent;border:0;color:inherit;font-size:14px;cursor:pointer;padding:8px 10px}
        .site-header h1{font-size:15px;margin:0 6px}

        /* Viewer layout */
        .viewer-wrapper{position:fixed;left:0;width:100vw;overflow:hidden;z-index:20;top:64px}
        .track{position:absolute;left:0;top:0;width:100vw;will-change:transform}
        .slide{width:100vw;display:flex;align-items:center;justify-content:center;background:#000;margin:0;padding:0;overflow:hidden}
        .embed-holder{width:100%;height:100%;display:flex;align-items:center;justify-content:center}
        .embed-box{width:100%;max-width:520px;border-radius:12px;overflow:hidden}

        /* Try to hide extra instagram UI as much as possible */
        blockquote.instagram-media{background:#fff!important;border-radius:12px!important}
        /* Generic hiding of captions/attribution if present */
        .instagram-media ~ div, .instagram-media .ec, .instagram-media .Caption{display:none!important}

        /* Responsive */
        @media(min-width:1025px){ .embed-box{max-width:420px} }
        @media(max-width:767px){ .embed-box{max-width:100vw;border-radius:0} .site-header{height:56px} }
    </style>
</head>
<body>
    <header class="site-header">
        <button class="back" onclick="location.href='reels.php'" aria-label="Back">◀ Back</button>
        <h1>Reel</h1>
    </header>

    <div id="viewer" class="viewer-wrapper" role="region" aria-label="Reel viewer">
        <div id="track" class="track">
            <?php foreach ($reels as $r): ?>
                <section class="slide" data-id="<?php echo $r['id']; ?>">
                    <div class="embed-holder">
                        <div class="embed-box">
                            <blockquote class="instagram-media" data-instgrm-permalink="<?php echo htmlspecialchars($r['url'], ENT_QUOTES, 'UTF-8'); ?>" data-instgrm-version="14"></blockquote>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
    // Self-contained player logic
    (function(){
        const reels = <?php echo json_encode($reels, JSON_HEX_TAG); ?>;
        let currentIndex = <?php echo (int)$foundIndex; ?>;
        let isTransitioning = false;
        const SCROLL_DEBOUNCE = 450;
        const SWIPE_THRESHOLD = 60;
        let lastScroll = 0;

        const header = document.querySelector('.site-header');
        const viewer = document.getElementById('viewer');
        const track = document.getElementById('track');
        const slides = Array.from(document.querySelectorAll('.slide'));
        let reelHeight = 0;

        function getHeaderHeight(){ return header ? header.offsetHeight : 0; }
        function getViewportHeight(){ return window.visualViewport ? window.visualViewport.height : window.innerHeight; }

        function recalc(){
            const h = getHeaderHeight();
            const vh = getViewportHeight();
            reelHeight = Math.max(0, vh - h);
            // Set CSS var and inline sizes for determinism
            viewer.style.top = h + 'px';
            viewer.style.height = reelHeight + 'px';
            slides.forEach(s => { s.style.height = reelHeight + 'px'; s.style.minHeight = reelHeight + 'px'; });
            // position track
            updateTrack(false);
        }

        function updateTrack(animate = true){
            if(!animate){ track.style.transition = 'none'; }
            track.style.transform = `translateY(-${currentIndex * reelHeight}px)`;
            if(!animate){ setTimeout(()=> track.style.transition = '', 0); }
        }

        function goto(idx){
            if(isTransitioning || idx<0 || idx>=slides.length) return;
            isTransitioning = true;
            currentIndex = idx;
            updateTrack(true);
            setTimeout(()=> isTransitioning = false, SCROLL_DEBOUNCE);
        }

        function next(){ goto(currentIndex+1); }
        function prev(){ goto(currentIndex-1); }

        // wheel
        window.addEventListener('wheel', function(e){
            const now = Date.now();
            if(now - lastScroll < SCROLL_DEBOUNCE) return;
            if(Math.abs(e.deltaY) < 20) return;
            if(e.deltaY > 0) next(); else prev();
            lastScroll = now;
        }, {passive:true});

        // touch
        let touchStartY = null, touchDeltaY = 0;
        window.addEventListener('touchstart', e => { if(e.touches.length===1){ touchStartY = e.touches[0].clientY; touchDeltaY=0; } }, {passive:true});
        window.addEventListener('touchmove', e => { if(touchStartY!==null) touchDeltaY = e.touches[0].clientY - touchStartY; }, {passive:true});
        window.addEventListener('touchend', ()=>{ if(touchDeltaY < -SWIPE_THRESHOLD) next(); else if(touchDeltaY > SWIPE_THRESHOLD) prev(); touchStartY=null; touchDeltaY=0; }, {passive:true});

        // keyboard
        window.addEventListener('keydown', function(e){ if(e.key==='ArrowDown' || e.key==='PageDown') next(); if(e.key==='ArrowUp' || e.key==='PageUp') prev(); if(e.key==='Escape') location.href='reels.php'; });

        // Prevent native scroll
        document.body.style.overflow = 'hidden';

        // Resize handlers
        function handleResize(){ recalc(); }
        window.addEventListener('resize', handleResize);
        window.addEventListener('orientationchange', handleResize);
        if(window.visualViewport) window.visualViewport.addEventListener('resize', handleResize);

        // Instagram embed loader (load once)
        function loadIG(callback){
            if(window.instgrm && window.instgrm.Embeds && typeof window.instgrm.Embeds.process==='function'){ callback(); return; }
            if(window._ig_loading) return;
            window._ig_loading = true;
            const s = document.createElement('script'); s.src = 'https://www.instagram.com/embed.js'; s.async = true; s.onload = callback; document.body.appendChild(s);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function(){
            recalc();
            // Ensure track initial position
            updateTrack(false);
            // Load IG embeds after layout stabilized
            loadIG(function(){ if(window.instgrm && window.instgrm.Embeds && typeof window.instgrm.Embeds.process==='function'){ window.instgrm.Embeds.process(); } });
        });
    })();
    </script>
</body>
</html>
