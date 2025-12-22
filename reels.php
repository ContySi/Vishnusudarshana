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
                title="Instagram Reel" 
                loading="lazy" 
                allowtransparency="true" 
                frameborder="0" 
                scrolling="no"
                allowfullscreen
                class="reels-iframe"
                style="width:100%;height:100%;max-width:400px;max-height:700px;display:block;margin:0 auto;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,0.10);background:#fff;">
            </iframe>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
