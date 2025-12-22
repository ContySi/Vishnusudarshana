<?php
// reels.php - Self-contained gallery page
require_once __DIR__ . '/config/db.php';

// Fetch active reels (latest first)
$reels = $pdo->query("SELECT * FROM instagram_reels WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reels Gallery</title>
    <style>
        :root{--muted:#6b7280;--accent:#8b1538}
        *{box-sizing:border-box}
        html,body{height:100%;margin:0;font-family:Inter,Arial,sans-serif;background:#fafbfd;color:#0b1220}
        .site-header{height:64px;display:flex;align-items:center;gap:12px;padding:0 18px;background:#fff;border-bottom:1px solid #eee;position:sticky;top:0;z-index:20}
        .site-header h1{font-size:16px;margin:0}
        .container{max-width:1200px;margin:18px auto;padding:0 12px}
        .gallery-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
        @media(min-width:768px){.gallery-grid{grid-template-columns:repeat(3,1fr)}}
        @media(min-width:1025px){.gallery-grid{grid-template-columns:repeat(4,1fr)}}
        .card{display:block;background:#fff;border-radius:10px;overflow:hidden;text-decoration:none;color:inherit;box-shadow:0 6px 18px rgba(16,24,40,0.04);transition:transform .18s ease,box-shadow .18s ease}
        .card:focus{outline:3px solid rgba(139,21,56,0.12)}
        .card:hover{transform:translateY(-6px);box-shadow:0 14px 30px rgba(16,24,40,0.08)}
        .thumb{width:100%;aspect-ratio:9/16;background:linear-gradient(180deg,#0f1724 0%,#1f2937 100%);display:flex;align-items:center;justify-content:center;position:relative}
        .play{width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.94);display:flex;align-items:center;justify-content:center}
        .play svg{width:28px;height:28px;fill:var(--accent)}
        .meta{padding:10px 12px;font-size:13px;color:var(--muted)}
        .empty{padding:48px;text-align:center;color:var(--muted)}
    </style>
</head>
<body>
    <header class="site-header">
        <h1>Reels Gallery</h1>
    </header>

    <main class="container" id="main">
        <?php if (empty($reels)): ?>
            <div class="empty">No reels available.</div>
        <?php else: ?>
            <div class="gallery-grid" id="gallery">
                <?php foreach ($reels as $r): ?>
                    <a class="card" href="reel-viewer.php?reel_id=<?php echo (int)$r['id']; ?>" aria-label="Open reel <?php echo (int)$r['id']; ?>">
                        <div class="thumb" aria-hidden="true">
                            <div class="play" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                        <div class="meta">Uploaded: <?php echo htmlspecialchars(substr($r['created_at'],0,10)); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
    // Minimal JS: enable keyboard activation for focused cards
    (function(){
        document.addEventListener('keydown', function(e){
            const el = document.activeElement;
            if(!el) return;
            if(el.classList && el.classList.contains('card')){
                if(e.key==='Enter' || e.key===' '){ el.click(); }
            }
        });
        // Make cards focusable for keyboard users
        document.querySelectorAll('.card').forEach(c => c.setAttribute('tabindex','0'));
    })();
    </script>
</body>
</html>
