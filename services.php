<?php include 'header.php'; ?>

<main class="services-main">
    <section class="service-categories">
        <h1 class="services-title">Choose a Service Type</h1>
        <div class="categories-grid">
            <a class="category-card" href="services.php?category=birth-child">
                <div class="category-icon" aria-label="Birth & Child Services">👶</div>
                <div class="category-info">
                    <h2>Birth & Child Services</h2>
                    <p>Janma Patrika, name suggestions, baby horoscope and child guidance.</p>
                    <span class="category-badge paid">Paid Service</span>
                </div>
            </a>
            <a class="category-card" href="services.php?category=marriage-matching">
                <div class="category-icon" aria-label="Marriage & Matching">💍</div>
                <div class="category-info">
                    <h2>Marriage & Matching</h2>
                    <p>Kundali Milan, marriage prediction and compatibility analysis.</p>
                    <span class="category-badge paid">Paid Service</span>
                </div>
            </a>
            <a class="category-card" href="services.php?category=astrology-consultation">
                <div class="category-icon" aria-label="Astrology Consultation">🔮</div>
                <div class="category-info">
                    <h2>Astrology Consultation</h2>
                    <p>Career, marriage, health, finance and personal guidance.</p>
                    <span class="category-badge consult">Consultation</span>
                </div>
            </a>
            <a class="category-card" href="services.php?category=muhurat-event">
                <div class="category-icon" aria-label="Muhurat & Event Guidance">📅</div>
                <div class="category-info">
                    <h2>Muhurat & Event Guidance</h2>
                    <p>Marriage, griha pravesh, vehicle purchase and business start muhurat.</p>
                    <span class="category-badge guidance">Guidance</span>
                </div>
            </a>
            <a class="category-card" href="services.php?category=pooja-vastu-enquiry">
                <div class="category-icon" aria-label="Pooja, Ritual & Vastu Enquiry">🕉️</div>
                <div class="category-info">
                    <h2>Pooja, Ritual & Vastu Enquiry</h2>
                    <p>Pooja, shanti, dosh nivaran, yagya and vastu consultation.</p>
                    <span class="category-badge enquiry">Enquiry (No Payment)</span>
                </div>
            </a>
        </div>
    </section>
</main>

<style>
.services-main {
    padding: 1.5rem 0 4.5rem 0;
    background: #f8f9fa;
    min-height: 100vh;
}
.services-title {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    color: #222;
}
.categories-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    max-width: 600px;
    margin: 0 auto;
}
.category-card {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    padding: 1.25rem 1rem;
    text-decoration: none;
    color: #222;
    transition: box-shadow 0.2s, transform 0.2s;
    cursor: pointer;
    min-height: 110px;
}
.category-card:hover, .category-card:focus {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transform: translateY(-2px) scale(1.02);
}
.category-icon {
    font-size: 2.2rem;
    margin-right: 1.25rem;
    flex-shrink: 0;
}
.category-info h2 {
    font-size: 1.18rem;
    margin: 0 0 0.2rem 0;
    font-weight: 600;
}
.category-info p {
    font-size: 1.02rem;
    margin: 0 0 0.5rem 0;
    color: #666;
}
.category-badge {
    display: inline-block;
    font-size: 0.92rem;
    padding: 0.18em 0.7em;
    border-radius: 0.7em;
    margin-top: 0.2em;
    font-weight: 500;
    letter-spacing: 0.01em;
}
.category-badge.paid {
    background: #ffe5e5;
    color: #b00020;
}
.category-badge.consult {
    background: #e5f0ff;
    color: #0056b3;
}
.category-badge.guidance {
    background: #e5ffe5;
    color: #1b5e20;
}
.category-badge.enquiry {
    background: #f3e5ff;
    color: #6a1b9a;
}
@media (min-width: 600px) {
    .categories-grid {
        grid-template-columns: 1fr 1fr;
    }
    .category-info h2 {
        font-size: 1.22rem;
    }
    .category-info p {
        font-size: 1.08rem;
    }
}
@media (min-width: 900px) {
    .categories-grid {
        grid-template-columns: 1fr 1fr 1fr;
    }
}
</style>

<?php include 'footer.php'; ?>
