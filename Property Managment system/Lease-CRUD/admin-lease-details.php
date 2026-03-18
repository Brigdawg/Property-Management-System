<?php
// admin-lease-details.php — View a single lease; handles DELETE.
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: admin-leases-view.php');
    exit;
}

$pdo = get_db();

// Handle DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $pdo->prepare('DELETE FROM lease WHERE LeaseID = ?')->execute([$id]);
    header('Location: admin-leases-view.php?deleted=1');
    exit;
}

// Fetch lease with full JOIN
$stmt = $pdo->prepare("
    SELECT
        l.LeaseID,
        l.Price,
        l.period,
        r.RenterID, r.Firstname AS RenterFirst, r.Lastname AS RenterLast, r.email AS RenterEmail,
        u.UnitID, u.Unit_number, u.Bed, u.bath,
        p.PropertyID, p.Address AS PropertyAddress,
        e.EmpID, e.Firstname AS EmpFirst, e.Lastname AS EmpLast, e.Position
    FROM lease l
    JOIN renter   r ON l.RenterID   = r.RenterID
    JOIN unit     u ON l.UnitID     = u.UnitID
    JOIN property p ON u.PropertyID = p.PropertyID
    JOIN employee e ON l.EmpID      = e.EmpID
    WHERE l.LeaseID = ?
    LIMIT 1
");
$stmt->execute([$id]);
$lease = $stmt->fetch();

if (!$lease) {
    header('Location: admin-leases-view.php');
    exit;
}

// Fetch payment history for this lease
$payments = $pdo->prepare('
    SELECT InvoiceID, date, amount, period
    FROM payment
    WHERE LeaseID = ?
    ORDER BY date DESC
');
$payments->execute([$id]);
$paymentRows = $payments->fetchAll();

$created = isset($_GET['created']);
$updated = isset($_GET['updated']);
$user    = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Lease #<?php echo $id; ?></title>
    <style>
        :root { --bg:#fff; --text:#0f172a; --muted:#475569; --border:#e2e8f0; --soft:#f8fafc; --brand:#0ea5e9; --brand-dark:#0284c7; --danger:#ef4444; --danger-dark:#dc2626; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif; background:var(--bg); color:var(--text); }
        .page { min-height:100vh; display:flex; flex-direction:column; }
        .navbar { height:72px; display:flex; align-items:center; justify-content:space-between; padding:0 28px; border-bottom:1px solid var(--border); background:#fff; }
        .brand { display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; }
        .logo { width:44px; height:44px; border-radius:999px; border:2px solid #0f172a; display:grid; place-items:center; font-weight:700; }
        .nav-links { display:flex; align-items:center; gap:14px; }
        .nav-links a { text-decoration:none; color:var(--muted); font-size:14px; padding:8px 10px; border-radius:10px; }
        .nav-links a:hover { background:var(--soft); color:var(--text); }
        main { flex:1; padding:40px 16px; display:flex; justify-content:center; }
        .wrap { width:min(800px,92vw); }
        .top-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
        h2 { margin:0; font-size:22px; }
        .back-link { font-size:14px; color:var(--brand-dark); text-decoration:none; padding:8px 10px; border-radius:10px; }
        .back-link:hover { background:var(--soft); }
        .alert { border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:16px; }
        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
        .panel { border:1px solid var(--border); border-radius:24px; background:#fff; padding:28px; box-shadow:0 10px 22px rgba(15,23,42,.06); margin-bottom:20px; }
        .panel h3 { margin:0 0 18px; font-size:16px; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; font-weight:700; }
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .field label { display:block; font-size:12px; font-weight:700; color:var(--muted); margin-bottom:4px; text-transform:uppercase; letter-spacing:.04em; }
        .value { padding:10px 12px; border-radius:12px; border:1px solid var(--border); font-size:14px; background:var(--soft); }
        hr { border:none; border-top:1px solid var(--border); margin:22px 0; }
        .actions { display:flex; gap:12px; flex-wrap:wrap; }
        .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 20px; border-radius:12px; border:1px solid transparent; font-weight:700; font-size:14px; cursor:pointer; transition:background .12s, transform .12s; text-decoration:none; }
        .btn-primary { background:var(--brand); color:#fff; }
        .btn-primary:hover { background:var(--brand-dark); transform:translateY(-1px); }
        .btn-outline { background:#fff; color:var(--text); border-color:var(--border); }
        .btn-outline:hover { background:var(--soft); }
        .btn-danger { background:var(--danger); color:#fff; }
        .btn-danger:hover { background:var(--danger-dark); transform:translateY(-1px); }
        .confirm-box { display:none; background:#fef2f2; border:1px solid #fecaca; border-radius:14px; padding:18px; margin-top:16px; }
        .confirm-box p { margin:0 0 14px; font-size:14px; color:#7f1d1d; }
        table { width:100%; border-collapse:collapse; }
        thead th { padding:10px 14px; text-align:left; font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid var(--border); }
        tbody td { padding:11px 14px; font-size:14px; border-bottom:1px solid var(--border); }
        tbody tr:last-child td { border-bottom:none; }
        .empty-payments { padding:20px; text-align:center; color:var(--muted); font-size:13px; }
        footer { border-top:1px solid var(--border); padding:14px; text-align:center; font-size:12px; color:var(--muted); }
        @media(max-width:560px) { .grid-2 { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="page">
    <header class="navbar">
        <a class="brand" href="../Pages/adminhome.php">
            <div class="logo">CP</div>
            <strong>Cowboy Properties</strong>
        </a>
        <nav class="nav-links">
            <a href="../Pages/adminhome.php">Dashboard</a>
            <a href="admin-leases-view.php">Leases</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <div class="wrap">
            <div class="top-row">
                <h2>Lease #<?php echo (int)$lease['LeaseID']; ?></h2>
                <a class="back-link" href="admin-leases-view.php">← Back to Leases</a>
            </div>

            <?php if ($created): ?>
                <div class="alert alert-success">Lease created successfully.</div>
            <?php elseif ($updated): ?>
                <div class="alert alert-success">Lease updated successfully.</div>
            <?php endif; ?>

            <!-- Lease Details -->
            <div class="panel">
                <h3>Lease Details</h3>
                <div class="grid-2">
                    <div class="field">
                        <label>Monthly Price</label>
                        <div class="value">$<?php echo number_format((float)$lease['Price'], 2); ?></div>
                    </div>
                    <div class="field">
                        <label>Period</label>
                        <div class="value"><?php echo (int)$lease['period']; ?> months</div>
                    </div>
                </div>

                <div class="grid-2" style="margin-top:14px">
                    <div class="field">
                        <label>Unit</label>
                        <div class="value"><?php echo htmlspecialchars($lease['Unit_number']); ?> (<?php echo (int)$lease['Bed']; ?>bd / <?php echo (int)$lease['bath']; ?>ba)</div>
                    </div>
                    <div class="field">
                        <label>Property</label>
                        <div class="value"><?php echo htmlspecialchars($lease['PropertyAddress']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Renter Info -->
            <div class="panel">
                <h3>Renter</h3>
                <div class="grid-2">
                    <div class="field">
                        <label>Name</label>
                        <div class="value"><?php echo htmlspecialchars($lease['RenterFirst'] . ' ' . $lease['RenterLast']); ?></div>
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <div class="value"><?php echo htmlspecialchars($lease['RenterEmail']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Employee -->
            <div class="panel">
                <h3>Managing Employee</h3>
                <div class="grid-2">
                    <div class="field">
                        <label>Name</label>
                        <div class="value"><?php echo htmlspecialchars($lease['EmpFirst'] . ' ' . $lease['EmpLast']); ?></div>
                    </div>
                    <div class="field">
                        <label>Position</label>
                        <div class="value"><?php echo htmlspecialchars($lease['Position']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="panel">
                <h3>Payment History</h3>
                <?php if (empty($paymentRows)): ?>
                    <div class="empty-payments">No payments recorded for this lease yet.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Period</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paymentRows as $pay): ?>
                                <tr>
                                    <td><?php echo (int)$pay['InvoiceID']; ?></td>
                                    <td><?php echo htmlspecialchars($pay['date']); ?></td>
                                    <td><?php echo htmlspecialchars($pay['period']); ?></td>
                                    <td>$<?php echo number_format((float)$pay['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="panel">
                <div class="actions">
                    <a class="btn btn-primary" href="admin-lease-edit.php?id=<?php echo $id; ?>">Edit Lease</a>
                    <button class="btn btn-danger" type="button" id="showDeleteBtn">Delete Lease</button>
                </div>

                <div class="confirm-box" id="confirmBox">
                    <p>Are you sure you want to permanently delete <strong>Lease #<?php echo $id; ?></strong>?
                       This cannot be undone and will also remove associated payment records.</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="delete" />
                        <div class="actions">
                            <button class="btn btn-danger" type="submit">Yes, Delete</button>
                            <button class="btn btn-outline" type="button" id="cancelDeleteBtn">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer>&copy; <?php echo date("Y"); ?> Cowboy Properties</footer>
</div>

<script>
    const box = document.getElementById('confirmBox');
    document.getElementById('showDeleteBtn').addEventListener('click',  () => box.style.display = 'block');
    document.getElementById('cancelDeleteBtn').addEventListener('click', () => box.style.display = 'none');
</script>
</body>
</html>
