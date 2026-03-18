<?php
// admin-leases-view.php — List all leases with full JOIN across 5 tables.
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$pdo = get_db();

$q       = trim($_GET['q'] ?? '');
$created = isset($_GET['created']);
$updated = isset($_GET['updated']);
$deleted = isset($_GET['deleted']);

// 5-table JOIN: lease + renter + unit + property + employee
$sql = "
    SELECT
        l.LeaseID,
        r.Firstname   AS RenterFirst,
        r.Lastname    AS RenterLast,
        u.Unit_number,
        p.Address     AS PropertyAddress,
        l.Price,
        l.period,
        e.Firstname   AS EmpFirst,
        e.Lastname    AS EmpLast,
        e.Position
    FROM lease l
    JOIN renter   r ON l.RenterID  = r.RenterID
    JOIN unit     u ON l.UnitID    = u.UnitID
    JOIN property p ON u.PropertyID = p.PropertyID
    JOIN employee e ON l.EmpID     = e.EmpID
";

if ($q !== '') {
    $sql .= " WHERE r.Firstname LIKE :q
               OR r.Lastname  LIKE :q
               OR p.Address   LIKE :q
               OR u.Unit_number LIKE :q";
    $sql .= " ORDER BY l.LeaseID DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':q' => "%$q%"]);
} else {
    $sql .= " ORDER BY l.LeaseID DESC";
    $stmt = $pdo->query($sql);
}

$leases = $stmt->fetchAll();
$user   = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Leases</title>
    <style>
        :root { --bg:#fff; --text:#0f172a; --muted:#475569; --border:#e2e8f0; --brand:#0ea5e9; --brand-dark:#0284c7; --soft:#f8fafc; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif; background:var(--bg); color:var(--text); }
        .page { min-height:100vh; display:flex; flex-direction:column; }
        .navbar { height:72px; display:flex; align-items:center; justify-content:space-between; padding:0 28px; border-bottom:1px solid var(--border); background:#fff; position:sticky; top:0; z-index:10; }
        .brand { display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; }
        .logo { width:44px; height:44px; border-radius:999px; border:2px solid #0f172a; display:grid; place-items:center; font-weight:700; }
        .brand-text { display:flex; flex-direction:column; line-height:1.1; }
        .brand-text strong { font-size:15px; }
        .brand-text span { font-size:12px; color:var(--muted); }
        .nav-links { display:flex; align-items:center; gap:14px; }
        .nav-links a { text-decoration:none; color:var(--muted); font-size:14px; padding:8px 10px; border-radius:10px; }
        .nav-links a:hover { background:var(--soft); color:var(--text); }
        .nav-user { font-size:13px; color:var(--muted); padding:0 4px; }
        main { flex:1; padding:34px 16px 48px; display:flex; justify-content:center; }
        .wrap { width:min(1060px,92vw); }
        .title-row { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; margin-bottom:14px; }
        h1 { margin:0; font-size:22px; }
        .subtitle { margin:4px 0 0; color:var(--muted); font-size:13px; }
        .alert { border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:14px; }
        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
        .panel { border:1px solid var(--border); border-radius:22px; background:#fff; box-shadow:0 10px 22px rgba(15,23,42,.06); overflow:hidden; }
        .panel-header { display:flex; align-items:center; gap:12px; padding:14px 16px; border-bottom:1px solid var(--border); }
        .search-form { flex:1; display:flex; gap:8px; }
        .search-form input { flex:1; padding:10px 12px; border-radius:12px; border:1px solid var(--border); font-size:14px; outline:none; }
        .search-form input:focus { border-color:rgba(14,165,233,.7); box-shadow:0 0 0 4px rgba(14,165,233,.15); }
        .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 16px; border-radius:12px; border:1px solid transparent; background:var(--brand); color:#fff; font-weight:700; font-size:14px; text-decoration:none; cursor:pointer; transition:background .12s, transform .12s; white-space:nowrap; }
        .btn:hover { background:var(--brand-dark); transform:translateY(-1px); }
        .btn-sm { padding:7px 14px; font-size:13px; min-width:auto; }
        .btn-outline { background:#fff; color:var(--text); border-color:var(--border); }
        .btn-outline:hover { background:var(--soft); }
        table { width:100%; border-collapse:collapse; }
        thead th { padding:12px 16px; text-align:left; font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid var(--border); background:var(--soft); }
        tbody tr:hover { background:var(--soft); }
        tbody td { padding:13px 16px; font-size:14px; border-bottom:1px solid var(--border); vertical-align:middle; }
        tbody tr:last-child td { border-bottom:none; }
        .empty { padding:40px; text-align:center; color:var(--muted); font-size:14px; }
        .badge { display:inline-block; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
        footer { border-top:1px solid var(--border); padding:14px; text-align:center; font-size:12px; color:var(--muted); }
    </style>
</head>
<body>
<div class="page">
    <header class="navbar">
        <a class="brand" href="../Pages/adminhome.php">
            <div class="logo">CP</div>
            <div class="brand-text">
                <strong>Cowboy Properties</strong>
                <span>Lease Management</span>
            </div>
        </a>
        <nav class="nav-links">
            <a href="../Pages/adminhome.php">Dashboard</a>
            <a href="../Pages/admin-renters-view.php">Renters</a>
            <a href="admin-leases-view.php">Leases</a>
            <span class="nav-user"><?php echo htmlspecialchars($user['name']); ?></span>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <div class="wrap">
            <div class="title-row">
                <div>
                    <h1>Leases</h1>
                    <p class="subtitle"><?php echo count($leases); ?> lease<?php echo count($leases) !== 1 ? 's' : ''; ?> found.</p>
                </div>
            </div>

            <?php if ($created): ?>
                <div class="alert alert-success">Lease created successfully.</div>
            <?php elseif ($updated): ?>
                <div class="alert alert-success">Lease updated successfully.</div>
            <?php elseif ($deleted): ?>
                <div class="alert alert-success">Lease deleted successfully.</div>
            <?php endif; ?>

            <div class="panel">
                <div class="panel-header">
                    <form class="search-form" method="GET">
                        <input name="q" type="text" placeholder="Search by renter name, property, or unit…"
                               value="<?php echo htmlspecialchars($q); ?>" />
                        <button class="btn btn-outline btn-sm" type="submit">Search</button>
                        <?php if ($q): ?>
                            <a class="btn btn-outline btn-sm" href="admin-leases-view.php">Clear</a>
                        <?php endif; ?>
                    </form>
                    <a class="btn" href="admin-lease-add.php">+ New Lease</a>
                </div>

                <?php if (empty($leases)): ?>
                    <div class="empty">No leases found. Click <strong>+ New Lease</strong> to create one.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Renter</th>
                                <th>Unit</th>
                                <th>Property</th>
                                <th>Price / mo</th>
                                <th>Period (mo)</th>
                                <th>Manager</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leases as $l): ?>
                                <tr>
                                    <td><?php echo (int)$l['LeaseID']; ?></td>
                                    <td><?php echo htmlspecialchars($l['RenterFirst'] . ' ' . $l['RenterLast']); ?></td>
                                    <td><span class="badge"><?php echo htmlspecialchars($l['Unit_number']); ?></span></td>
                                    <td><?php echo htmlspecialchars($l['PropertyAddress']); ?></td>
                                    <td>$<?php echo number_format((float)$l['Price'], 2); ?></td>
                                    <td><?php echo (int)$l['period']; ?></td>
                                    <td><?php echo htmlspecialchars($l['EmpFirst'] . ' ' . $l['EmpLast']); ?></td>
                                    <td>
                                        <a class="btn btn-outline btn-sm" href="admin-lease-details.php?id=<?php echo (int)$l['LeaseID']; ?>">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>&copy; <?php echo date("Y"); ?> Cowboy Properties</footer>
</div>
</body>
</html>
