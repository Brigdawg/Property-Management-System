<?php
// admin-lease-add.php — Admin creates a new lease record in the database.
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$pdo    = get_db();
$errors = [];
$values = ['renter_id' => '', 'unit_id' => '', 'emp_id' => '', 'price' => '', 'period' => ''];

// Load dropdowns
$renters   = $pdo->query('SELECT RenterID, Firstname, Lastname FROM renter ORDER BY Lastname, Firstname')->fetchAll();
$units     = $pdo->query('
    SELECT u.UnitID, u.Unit_number, u.Bed, u.bath, u.price, p.Address
    FROM unit u
    JOIN property p ON u.PropertyID = p.PropertyID
    ORDER BY p.Address, u.Unit_number
')->fetchAll();
$employees = $pdo->query('SELECT EmpID, Firstname, Lastname, Position FROM employee ORDER BY Lastname, Firstname')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['renter_id'] = trim($_POST['renter_id'] ?? '');
    $values['unit_id']   = trim($_POST['unit_id']   ?? '');
    $values['emp_id']    = trim($_POST['emp_id']     ?? '');
    $values['price']     = trim($_POST['price']      ?? '');
    $values['period']    = trim($_POST['period']     ?? '');

    if (!$values['renter_id']) $errors[] = 'Please select a renter.';
    if (!$values['unit_id'])   $errors[] = 'Please select a unit.';
    if (!$values['emp_id'])    $errors[] = 'Please select a managing employee.';
    if (!is_numeric($values['price']) || (float)$values['price'] <= 0)
        $errors[] = 'Price must be a positive number.';
    if (!is_numeric($values['period']) || (int)$values['period'] <= 0)
        $errors[] = 'Period must be a positive number of months.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('
            INSERT INTO lease (RenterID, EmpID, UnitID, Price, period)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            (int)$values['renter_id'],
            (int)$values['emp_id'],
            (int)$values['unit_id'],
            (float)$values['price'],
            (int)$values['period'],
        ]);
        $newId = (int)$pdo->lastInsertId();
        header('Location: admin-lease-details.php?id=' . $newId . '&created=1');
        exit;
    }
}

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | New Lease</title>
    <style>
        :root { --bg:#fff; --text:#0f172a; --muted:#475569; --border:#e2e8f0; --soft:#f8fafc; --brand:#0ea5e9; --brand-dark:#0284c7; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif; background:var(--bg); color:var(--text); }
        .page { min-height:100vh; display:flex; flex-direction:column; }
        .navbar { height:72px; display:flex; align-items:center; justify-content:space-between; padding:0 28px; border-bottom:1px solid var(--border); background:#fff; position:sticky; top:0; z-index:10; }
        .brand { display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; }
        .logo { width:44px; height:44px; border-radius:999px; border:2px solid #0f172a; display:grid; place-items:center; font-weight:700; }
        .nav-links { display:flex; align-items:center; gap:14px; }
        .nav-links a { text-decoration:none; color:var(--muted); font-size:14px; padding:8px 10px; border-radius:10px; }
        .nav-links a:hover { background:var(--soft); color:var(--text); }
        main { flex:1; padding:40px 16px 60px; display:flex; justify-content:center; }
        .wrap { width:min(700px,92vw); }
        .top-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
        h1 { margin:0; font-size:22px; }
        .back-link { font-size:14px; color:var(--brand-dark); text-decoration:none; padding:8px 10px; border-radius:10px; }
        .back-link:hover { background:var(--soft); }
        .panel { border:1px solid var(--border); border-radius:24px; background:#fff; padding:28px; box-shadow:0 10px 22px rgba(15,23,42,.06); }
        .alert-error { background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:18px; }
        .alert-error ul { margin:6px 0 0; padding-left:18px; }
        .row-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .field { margin-bottom:16px; }
        label { display:block; font-size:13px; font-weight:700; color:var(--muted); margin-bottom:6px; }
        input, select { width:100%; padding:11px 12px; border-radius:12px; border:1px solid var(--border); font-size:14px; outline:none; background:#fff; }
        input:focus, select:focus { border-color:rgba(14,165,233,.7); box-shadow:0 0 0 4px rgba(14,165,233,.15); }
        .hint { font-size:11px; color:var(--muted); margin-top:4px; }
        hr { border:none; border-top:1px solid var(--border); margin:20px 0; }
        .actions { display:flex; gap:12px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 20px; border-radius:12px; border:1px solid transparent; background:var(--brand); color:#fff; font-weight:700; font-size:14px; cursor:pointer; transition:background .12s, transform .12s; text-decoration:none; }
        .btn:hover { background:var(--brand-dark); transform:translateY(-1px); }
        .btn-outline { background:#fff; color:var(--text); border-color:var(--border); }
        .btn-outline:hover { background:var(--soft); }
        footer { border-top:1px solid var(--border); padding:14px; text-align:center; font-size:12px; color:var(--muted); }
        @media(max-width:560px) { .row-2 { grid-template-columns:1fr; } }
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
                <h1>New Lease</h1>
                <a class="back-link" href="admin-leases-view.php">← Back to Leases</a>
            </div>

            <div class="panel">
                <?php if (!empty($errors)): ?>
                    <div class="alert-error">
                        <strong>Please fix the following:</strong>
                        <ul><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>

                    <div class="field">
                        <label for="renter_id">Renter *</label>
                        <select id="renter_id" name="renter_id" required>
                            <option value="">— Select a renter —</option>
                            <?php foreach ($renters as $r): ?>
                                <option value="<?php echo (int)$r['RenterID']; ?>"
                                    <?php echo $values['renter_id'] == $r['RenterID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($r['Firstname'] . ' ' . $r['Lastname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="unit_id">Unit *</label>
                        <select id="unit_id" name="unit_id" required>
                            <option value="">— Select a unit —</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?php echo (int)$u['UnitID']; ?>"
                                    <?php echo $values['unit_id'] == $u['UnitID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($u['Address'] . ' — Unit ' . $u['Unit_number'] . ' (' . $u['Bed'] . 'bd/' . $u['bath'] . 'ba, $' . number_format((float)$u['price'], 0) . '/mo)'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="hint">Unit list shows property address, unit number, bed/bath, and listed price.</p>
                    </div>

                    <div class="field">
                        <label for="emp_id">Managing Employee *</label>
                        <select id="emp_id" name="emp_id" required>
                            <option value="">— Select an employee —</option>
                            <?php foreach ($employees as $e): ?>
                                <option value="<?php echo (int)$e['EmpID']; ?>"
                                    <?php echo $values['emp_id'] == $e['EmpID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($e['Firstname'] . ' ' . $e['Lastname'] . ' (' . $e['Position'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row-2">
                        <div class="field">
                            <label for="price">Monthly Price ($) *</label>
                            <input id="price" name="price" type="number" step="0.01" min="0"
                                   placeholder="e.g. 1450.00"
                                   value="<?php echo htmlspecialchars($values['price']); ?>" required />
                        </div>
                        <div class="field">
                            <label for="period">Period (months) *</label>
                            <input id="period" name="period" type="number" min="1"
                                   placeholder="e.g. 12"
                                   value="<?php echo htmlspecialchars($values['period']); ?>" required />
                            <p class="hint">Number of months for the lease term.</p>
                        </div>
                    </div>

                    <hr>

                    <div class="actions">
                        <button class="btn" type="submit">Create Lease</button>
                        <a class="btn btn-outline" href="admin-leases-view.php">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>&copy; <?php echo date("Y"); ?> Cowboy Properties</footer>
</div>
</body>
</html>
