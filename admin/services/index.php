</style>
<style>
.pagination .page-link {
    display: inline-block;
    margin: 0 3px;
    padding: 6px 12px;
    border-radius: 6px;
    background: #f9eaea;
    color: #800000;
    text-decoration: none;
    font-weight: 600;
    border: 1px solid #f3caca;
    min-width: 32px;
}
.pagination .page-link.current {
    background: #800000;
    color: #fff;
    border: 1px solid #800000;
}
.pagination .page-link.disabled {
    background: #f7f7fa;
    color: #bbb;
    border: 1px solid #eee;
    cursor: not-allowed;
}
</style>
<script>
// Debounce helper
function debounce(fn, delay) {
    let timer = null;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.filter-bar');
    const searchInput = form.querySelector('input[name="search"]');
    const tableBody = document.getElementById('serviceTableBody');

    // AJAX load function
    function loadTable(params) {
        const url = 'ajax_list.php?' + new URLSearchParams(params).toString();
        fetch(url)
            .then(r => r.text())
            .then(html => {
                tableBody.innerHTML = html;
                attachPaginationLinks();
            });
    }

    // Gather current filter/search/page params
    function getParams(pageOverride) {
        const fd = new FormData(form);
        const params = Object.fromEntries(fd.entries());
        if (pageOverride) params.page = pageOverride;
        return params;
    }

    // Debounced search
    const debouncedSearch = debounce(function() {
        loadTable(getParams(1));
    }, 300);

    searchInput.addEventListener('input', debouncedSearch);

    // AJAX pagination using data-page
    function attachPaginationLinks() {
        tableBody.querySelectorAll('.pagination .page-link[data-page]').forEach(link => {
            if (link.classList.contains('disabled') || link.classList.contains('current')) return;
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = link.getAttribute('data-page') || 1;
                loadTable(getParams(page));
            });
        });
    }

    // Initial attach for first page
    attachPaginationLinks();

    // AJAX for filter change (category/status)
    form.querySelectorAll('select').forEach(sel => {
        sel.addEventListener('change', function() {
            loadTable(getParams(1));
        });
    });

    // AJAX for form submit (button)
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        loadTable(getParams(1));
    });
});
</script>
<?php
require_once __DIR__ . '/../../config/db.php';

/* ==============================
   SUMMARY COUNTS
============================== */
$todayCount = $pdo->query(
    "SELECT COUNT(*) FROM service_requests WHERE DATE(created_at) = CURDATE()"
)->fetchColumn();

$receivedCount = $pdo->query(
    "SELECT COUNT(*) FROM service_requests WHERE service_status = 'Received'"
)->fetchColumn();

$inProgressCount = $pdo->query(
    "SELECT COUNT(*) FROM service_requests WHERE service_status = 'In Progress'"
)->fetchColumn();

$completedCount = $pdo->query(
    "SELECT COUNT(*) FROM service_requests WHERE service_status = 'Completed'"
)->fetchColumn();

/* ==============================
   FILTERS
============================== */
$statusOptions = ['All', 'Received', 'In Progress', 'Completed'];
$categoryOptions = [
    'All' => 'All Categories',
    'birth-child' => 'Birth & Child Services',
    'marriage-matching' => 'Marriage & Matching',
    'astrology-consultation' => 'Astrology Consultation',
    'muhurat-event' => 'Muhurat & Event Guidance',
    'pooja-vastu-enquiry' => 'Pooja, Ritual & Vastu Enquiry',
];


$selectedStatus   = $_GET['status']   ?? 'All';
$selectedCategory = $_GET['category'] ?? 'All';
$search           = trim($_GET['search'] ?? '');


$where  = [];
$params = [];

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;


if ($selectedStatus !== 'All') {
    $where[]  = 'service_status = ?';
    $params[] = $selectedStatus;
}
if ($selectedCategory !== 'All') {
    $where[]  = 'category_slug = ?';
    $params[] = $selectedCategory;
}
if ($search !== '') {
    $where[] = '(tracking_id LIKE ? OR mobile LIKE ? OR customer_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM service_requests $whereSql";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $perPage));

// Main paginated query
$sql = "
    SELECT id, tracking_id, customer_name, mobile, category_slug,
           total_amount, payment_status, service_status, created_at, selected_products
    FROM service_requests
    $whereSql
    ORDER BY created_at DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin – Service Requests</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    font-family: Arial, sans-serif;
    background: #f7f7fa;
    margin: 0;
}
.admin-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 12px;
}
h1 {
    color: #800000;
    margin-bottom: 18px;
}

/* SUMMARY CARDS */
.summary-cards {
    display: flex;
    gap: 18px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.summary-card {
    flex: 1 1 180px;
    background: #fffbe7;
    border-radius: 14px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 2px 8px #e0bebe22;
}
.summary-count {
    font-size: 2.2em;
    font-weight: 700;
    color: #800000;
}
.summary-label {
    font-size: 1em;
    color: #444;
}

/* FILTER BAR */
.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 18px;
}
.filter-bar label {
    font-weight: 600;
}
.filter-bar select,
.filter-bar button {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 1em;
}
.filter-bar button {
    background: #800000;
    color: #fff;
    border: none;
    cursor: pointer;
}


/* TABLE */
.service-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 2px 12px #e0bebe22;
    border-radius: 12px;
    overflow: hidden;
}
.service-table th,
.service-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #f3caca;
    text-align: left;
}
.service-table th {
    background: #f9eaea;
    color: #800000;
}
.service-table tbody tr:hover {
    background: #f3f7fa;
    cursor: pointer;
}
.status-badge {
    padding: 4px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9em;
    display: inline-block;
    min-width: 80px;
    text-align: center;
}
/* Service Status Colors */
.status-received { background: #e5f0ff; color: #0056b3; }
.status-in-progress { background: #fffbe5; color: #b36b00; }
.status-completed { background: #e5ffe5; color: #1a8917; }
.status-cancelled { background: #ffeaea; color: #c00; }
/* Payment Status Colors */
.payment-paid { background: #e5ffe5; color: #1a8917; }
.payment-pending { background: #f7f7f7; color: #b36b00; }
.payment-failed { background: #ffeaea; color: #c00; }

.view-btn {
    background: #800000;
    color: #fff;
    padding: 6px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

.no-data {
    text-align: center;
    color: #777;
    padding: 24px;
}

@media (max-width: 700px) {
    .summary-cards {
        flex-direction: column;
    }
}
</style>
</head>

<body>
<div class="admin-container">

<h1>Service Requests</h1>

<!-- SUMMARY CARDS -->
<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-count"><?= $todayCount ?></div>
        <div class="summary-label">Today’s Requests</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?= $receivedCount ?></div>
        <div class="summary-label">Pending</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?= $inProgressCount ?></div>
        <div class="summary-label">In Progress</div>
    </div>
    <div class="summary-card">
        <div class="summary-count"><?= $completedCount ?></div>
        <div class="summary-label">Completed</div>
    </div>
</div>

<!-- FILTERS -->
<form class="filter-bar" method="get">
    <label>Category</label>
    <select name="category" onchange="this.form.submit()">
        <?php foreach ($categoryOptions as $k => $v): ?>
            <option value="<?= $k ?>" <?= $selectedCategory === $k ? 'selected' : '' ?>>
                <?= $v ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Status</label>
    <select name="status" onchange="this.form.submit()">
        <?php foreach ($statusOptions as $s): ?>
            <option value="<?= $s ?>" <?= $selectedStatus === $s ? 'selected' : '' ?>>
                <?= $s ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Tracking ID, Mobile, or Customer Name" style="min-width:200px;" />
    <button type="submit">Apply</button>
</form>


<!-- LEGEND -->


<!-- TABLE -->

<table class="service-table">
<thead>
<tr>
    <th>Tracking ID</th>
    <th>Customer</th>
    <th>Mobile</th>
    <th>Product(s)</th>
    <th>Category</th>
    <th>Amount</th>
    <th>Payment</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>
<tbody id="serviceTableBody">
<?php if (!$requests): ?>
<tr>
    <td colspan="10" class="no-data">No service requests found.</td>
</tr>
<?php else: ?>
<?php foreach ($requests as $row): ?>
<tr>
    <td><?= htmlspecialchars($row['tracking_id']) ?></td>
    <td><?= htmlspecialchars($row['customer_name']) ?></td>
    <td><?= htmlspecialchars($row['mobile']) ?></td>
    <td>
        <?php
        $products = '-';
        $decoded = json_decode($row['selected_products'], true);
        if (is_array($decoded) && count($decoded)) {
            $names = [];
            foreach ($decoded as $prod) {
                if (isset($prod['name'])) {
                    $names[] = htmlspecialchars($prod['name']);
                }
            }
            if ($names) {
                $products = implode(', ', $names);
            }
        }
        echo $products;
        ?>
    </td>
    <td>
        <?php
        $catMap = [
            'birth-child' => 'Birth & Child Services',
            'marriage-matching' => 'Marriage & Matching',
            'astrology-consultation' => 'Astrology Consultation',
            'muhurat-event' => 'Muhurat & Event Guidance',
            'pooja-vastu-enquiry' => 'Pooja, Ritual & Vastu Enquiry',
        ];
        $catSlug = $row['category_slug'];
        echo isset($catMap[$catSlug]) ? htmlspecialchars($catMap[$catSlug]) : htmlspecialchars($catSlug);
        ?>
    </td>
    <td>₹<?= number_format($row['total_amount'], 2) ?></td>
    <td>
        <?php
        $payClass = 'payment-' . strtolower(str_replace(' ', '-', $row['payment_status']));
        ?>
        <span class="status-badge <?= $payClass ?>">
            <?= htmlspecialchars($row['payment_status']) ?>
        </span>
    </td>
    <td>
        <?php
        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $row['service_status']));
        ?>
        <span class="status-badge <?= $statusClass ?>">
            <?= htmlspecialchars($row['service_status']) ?>
        </span>
    </td>
    <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
    <td><a class="view-btn" href="view.php?id=<?= $row['id'] ?>">View</a></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>

<!-- PAGINATION CONTROLS -->
<?php if ($totalPages > 1): ?>
<div style="margin: 24px 0; text-align: center;">
    <nav class="pagination">
        <?php
        // Build base URL with filters/search
        $query = $_GET;
        unset($query['page']);
        $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
        $queryStr = http_build_query($query);
        $linkBase = $baseUrl . ($queryStr ? '?' . $queryStr . '&' : '?') . 'page=';

        // Previous
        if ($page > 1) {
            echo '<a href="' . htmlspecialchars($linkBase . ($page - 1)) . '" class="page-link" data-page="' . ($page - 1) . '">&laquo; Previous</a> ';
        } else {
            echo '<span class="page-link disabled">&laquo; Previous</span> ';
        }

        // Page numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $page) {
                echo '<span class="page-link current">' . $i . '</span> ';
            } else {
                echo '<a href="' . htmlspecialchars($linkBase . $i) . '" class="page-link" data-page="' . $i . '">' . $i . '</a> ';
            }
        }

        // Next
        if ($page < $totalPages) {
            echo '<a href="' . htmlspecialchars($linkBase . ($page + 1)) . '" class="page-link" data-page="' . ($page + 1) . '">Next &raquo;</a>';
        } else {
            echo '<span class="page-link disabled">Next &raquo;</span>';
        }
        ?>
    </nav>
</div>
<?php endif; ?>
</style>
<style>
.pagination .page-link {
    display: inline-block;
    margin: 0 3px;
    padding: 6px 12px;
    border-radius: 6px;
    background: #f9eaea;
    color: #800000;
    text-decoration: none;
    font-weight: 600;
    border: 1px solid #f3caca;
    min-width: 32px;
}
.pagination .page-link.current {
    background: #800000;
    color: #fff;
    border: 1px solid #800000;
}
.pagination .page-link.disabled {
    background: #f7f7fa;
    color: #bbb;
    border: 1px solid #eee;
    cursor: not-allowed;
}
</style>

</div>
</body>
</html>
