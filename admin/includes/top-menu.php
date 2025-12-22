<?php
// admin/includes/top-menu.php
// Reusable admin top navigation bar

// --- BASE_URL dynamic detection (works for /, /1/, /subdir/, etc.) ---
$scriptName = $_SERVER['SCRIPT_NAME'];
// Example: /1/admin/services/completed-appointments.php
$basePath = explode('/1/', $scriptName)[0];
// Result: /1 (or empty string for root)

$menu = [
    'Dashboard' => [
        'url' => '1/admin/index.php',
        'icon' => '🏠',
    ],
    'Appointments' => [
        'icon' => '📅',
        'submenu' => [
            'Pending Appointments'   => '/admin/services/appointments.php',
            'Accepted Appointments'  => '/admin/services/accepted-appointments.php',
            'Completed Appointments' => '/admin/services/completed-appointments.php',
        ]
    ],
    'Services' => [
        'icon' => '🛠️',
        'submenu' => [
            'Service Requests' => '/admin/products/index.php',
            'Add New Service'  => '/admin/products/add.php',
            'Categories'       => '/admin/products/categories.php',
        ]
    ],
    'Payments' => [
        'icon' => '💳',
        'submenu' => [
            'All Payments'    => '/admin/payments/index.php',
            'Failed Payments' => '/admin/payments/failed.php',
        ]
    ],
    'Reports' => [
        'icon' => '📊',
        'submenu' => [
            'Daily Report'   => '/admin/reports/daily.php',
            'Monthly Report' => '/admin/reports/monthly.php',
        ]
    ],
    'Settings' => [
        'icon' => '⚙️',
        'submenu' => [
            'Profile'         => '/admin/settings/profile.php',
            'Change Password' => '/admin/settings/password.php',
        ]
    ],
    'Logout' => [
        'url' => '/admin/logout.php',
        'icon' => '🚪',
    ],
];

$current = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

function isActive($url, $current) {
    return basename($url) === $current;
}
?>
<!-- Admin Top Menu Bar -->
<nav class="admin-top-menu" id="adminTopMenu">
    <div class="admin-top-menu-inner">
        <div class="admin-top-menu-logo">Admin Panel</div>
        <ul class="admin-top-menu-list" id="adminTopMenuList">
            <?php foreach ($menu as $label => $item): ?>
                <?php
                $hasSub = isset($item['submenu']);
                $isActiveMain = false;
                $isActiveSub = false;
                if ($hasSub) {
                    foreach ($item['submenu'] as $sublabel => $suburl) {
                        if (isActive($suburl, $current)) {
                            $isActiveSub = true;
                            break;
                        }
                    }
                } else {
                    $isActiveMain = isset($item['url']) && isActive($item['url'], $current);
                }
                ?>
                <li class="admin-top-menu-item<?= $hasSub ? ' has-sub' : '' ?><?= $isActiveMain ? ' active' : '' ?><?= $isActiveSub ? ' active' : '' ?>">
                    <?php if ($hasSub): ?>
                        <a href="#" class="admin-top-menu-link" tabindex="0">
                            <span class="icon"><?= $item['icon'] ?? '' ?></span> <?= htmlspecialchars($label) ?>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <ul class="admin-top-menu-dropdown">
                            <?php foreach ($item['submenu'] as $sublabel => $suburl): ?>
                                <li class="<?= isActive($suburl, $current) ? 'active' : '' ?>">
                                    <a href="<?= htmlspecialchars($suburl) ?>"> <?= htmlspecialchars($sublabel) ?> </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($item['url']) ?>" class="admin-top-menu-link">
                            <span class="icon"><?= $item['icon'] ?? '' ?></span> <?= htmlspecialchars($label) ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="admin-top-menu-mobile-toggle" id="adminTopMenuMobileToggle">☰</div>
    </div>
</nav>
<!-- End Admin Top Menu Bar -->

<!-- Top Menu CSS (add to main admin CSS) -->
<style>
.admin-top-menu {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1002;
    background: #fffbe7;
    box-shadow: 0 2px 8px #e0bebe22;
    border-bottom: 2px solid #f3caca;
    height: 56px;
}
.admin-top-menu-inner {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    height: 56px;
    padding: 0 18px;
    justify-content: space-between;
}
.admin-top-menu-logo {
    font-size: 1.25em;
    font-weight: 700;
    color: #800000;
    letter-spacing: 1px;
    margin-right: 32px;
}
.admin-top-menu-list {
    list-style: none;
    display: flex;
    margin: 0;
    padding: 0;
    align-items: center;
}
.admin-top-menu-item {
    position: relative;
}
.admin-top-menu-link {
    display: flex;
    align-items: center;
    padding: 0 18px;
    height: 56px;
    color: #800000;
    font-weight: 600;
    text-decoration: none;
    font-size: 1em;
    transition: background 0.15s;
}
.admin-top-menu-link:hover, .admin-top-menu-item.active > .admin-top-menu-link {
    background: #f9eaea;
    color: #b30000;
}
.admin-top-menu-item.has-sub:hover > .admin-top-menu-dropdown,
.admin-top-menu-item.has-sub:focus-within > .admin-top-menu-dropdown {
    display: block;
}
.admin-top-menu-dropdown {
    display: none;
    position: absolute;
    top: 56px;
    left: 0;
    min-width: 210px;
    background: #fff;
    border: 1px solid #f3caca;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 4px 16px #e0bebe22;
    z-index: 1003;
}
.admin-top-menu-dropdown li {
    border-bottom: 1px solid #f3caca;
}
.admin-top-menu-dropdown li:last-child {
    border-bottom: none;
}
.admin-top-menu-dropdown a {
    display: block;
    padding: 12px 20px;
    color: #800000;
    text-decoration: none;
    font-size: 1em;
    background: #fff;
    transition: background 0.12s;
}
.admin-top-menu-dropdown li.active > a,
.admin-top-menu-dropdown a:hover {
    background: #f9eaea;
    color: #b30000;
}
.admin-top-menu-item.active > .admin-top-menu-link,
.admin-top-menu-dropdown li.active > a {
    color: #b30000;
    font-weight: 700;
}
.admin-top-menu-mobile-toggle {
    display: none;
    font-size: 2em;
    color: #800000;
    cursor: pointer;
    margin-left: 18px;
}
@media (max-width: 900px) {
    .admin-top-menu-inner { flex-direction: column; align-items: stretch; height: auto; }
    .admin-top-menu-list {
        flex-direction: column;
        width: 100%;
        display: none;
        background: #fffbe7;
        border-top: 1px solid #f3caca;
    }
    .admin-top-menu-list.show { display: flex; }
    .admin-top-menu-item { width: 100%; }
    .admin-top-menu-link { height: 48px; padding: 0 14px; }
    .admin-top-menu-dropdown {
        position: static;
        min-width: 100%;
        box-shadow: none;
        border-radius: 0 0 10px 10px;
        border: none;
        background: #fffbe7;
    }
    .admin-top-menu-dropdown a { padding: 10px 18px; }
    .admin-top-menu-mobile-toggle { display: block; }
}
body { padding-top: 56px !important; }
</style>
<!-- End Top Menu CSS -->

<!-- Top Menu JS (minimal for mobile toggle) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('adminTopMenuMobileToggle');
    var menu = document.getElementById('adminTopMenuList');
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            menu.classList.toggle('show');
        });
    }
    // Dropdown click for mobile
    var items = document.querySelectorAll('.admin-top-menu-item.has-sub > .admin-top-menu-link');
    items.forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 900) {
                e.preventDefault();
                var dropdown = this.nextElementSibling;
                if (dropdown) dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
            }
        });
    });
});
</script>
<!-- End Top Menu JS -->
