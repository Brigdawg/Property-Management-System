<?php
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$errors = [];
$values = [
    'propertyid' => '',
    'address' => '',
    'manager_emp_id' => '',
    'unit_count' => '0',
];
$managers = [];

try {
    $pdo = get_db();
    $managerStmt = $pdo->query("SELECT EmpID, Firstname, Lastname FROM employee ORDER BY Lastname ASC, Firstname ASC");
    $managers = $managerStmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Could not load employees.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['propertyid'] = trim((string)($_POST['propertyid'] ?? ''));
    $values['address'] = trim((string)($_POST['address'] ?? ''));
    $values['manager_emp_id'] = trim((string)($_POST['manager_emp_id'] ?? ''));
    $values['unit_count'] = trim((string)($_POST['unit_count'] ?? '0'));

    if ($values['address'] === '') {
        $errors[] = 'Address is required.';
    }
    if ($values['manager_emp_id'] === '' || (int)$values['manager_emp_id'] <= 0) {
        $errors[] = 'Manager is required.';
    }
    if ($values['unit_count'] === '' || (int)$values['unit_count'] < 0) {
        $errors[] = 'Unit count must be 0 or greater.';
    }

    if (!$errors) {
        try {
            $pdo = get_db();

            if ($values['propertyid'] !== '') {
                $stmt = $pdo->prepare('INSERT INTO property (PropertyID, Address, ManagerEmpID, Unit_Count) VALUES (?, ?, ?, ?)');
                $stmt->execute([
                    (int)$values['propertyid'],
                    $values['address'],
                    (int)$values['manager_emp_id'],
                    (int)$values['unit_count'],
                ]);
                $newId = (int)$values['propertyid'];
            } else {
                $stmt = $pdo->prepare('INSERT INTO property (Address, ManagerEmpID, Unit_Count) VALUES (?, ?, ?)');
                $stmt->execute([
                    $values['address'],
                    (int)$values['manager_emp_id'],
                    (int)$values['unit_count'],
                ]);
                $newId = (int)$pdo->lastInsertId();
            }

            header('Location: admin-property-details.php?id=' . $newId . '&created=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Unable to create property. Confirm the ID is unique and manager exists.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Add Property</title>
    <style>
        :root {
            --bg: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --border: #e2e8f0;
            --soft: #f8fafc;
            --brand: #0ea5e9;
            --brand-dark: #0284c7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            border-bottom: 1px solid var(--border);
            background: #fff;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .logo {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            border: 2px solid #0f172a;
            display: grid;
            place-items: center;
            font-weight: 700;
            letter-spacing: .5px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--muted);
            font-size: 14px;
            padding: 8px 10px;
            border-radius: 10px;
        }

        .nav-links a:hover {
            background: var(--soft);
            color: var(--text);
        }

        main {
            flex: 1;
            padding: 40px 16px 60px;
            display: flex;
            justify-content: center;
        }

        .wrap {
            width: min(900px, 92vw);
        }

        .top-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: -0.01em;
        }

        .back-link {
            font-size: 14px;
            color: var(--brand-dark);
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 10px;
        }

        .back-link:hover {
            background: var(--soft);
        }

        .panel {
            border: 1px solid var(--border);
            border-radius: 24px;
            background: #fff;
            padding: 26px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
        }

        .card {
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px;
            background: #fff;
        }

        .card h2 {
            margin: 0 0 14px;
            font-size: 16px;
            text-align: center;
        }

        .field {
            display: grid;
            grid-template-columns: 140px 1fr;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        label {
            font-size: 13px;
            font-weight: 800;
            color: var(--muted);
        }

        input,
        select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 14px;
            outline: none;
            background: #fff;
        }

        input:focus,
        select:focus {
            border-color: rgba(14, 165, 233, .7);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, .15);
        }

        .btn-row {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 999px;
            border: 1px solid transparent;
            background: var(--brand);
            color: #fff;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: transform 120ms ease, background 120ms ease;
            min-width: 180px;
            font-size: 14px;
        }

        .alert {
            margin: 0 0 12px;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13px;
            color: #991b1b;
            background: #fef2f2;
        }

        .btn:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: #fff;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-outline:hover {
            background: var(--soft);
        }

        footer {
            border-top: 1px solid var(--border);
            padding: 14px 20px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
        }

        @media (max-width: 760px) {
            .field {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <header class="navbar">
            <a class="brand" href="../index.php">
                <div class="logo">CP</div>
                <strong>Cowboy Properties</strong>
            </a>

            <nav class="nav-links">
                <a href="../index.php">Dashboard</a>
                <a href="admin-renters-view.php">Renters</a>
                <a href="admin-properties-view.php">Properties</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>

        <main>
            <div class="wrap">
                <div class="top-row">
                    <h1>Add Property</h1>
                    <a class="back-link" href="admin-properties-view.php">← Back</a>
                </div>

                <section class="panel" aria-label="Add property panel">
                    <div class="card">
                        <h2>New Property Information</h2>

                        <?php if ($errors): ?>
                            <div class="alert">
                                <?php echo htmlspecialchars(implode(' ', $errors)); ?>
                            </div>
                        <?php endif; ?>

                        <form action="admin-property-add.php" method="POST">
                            <div class="field">
                                <label for="propertyid">PropertyID (PK)</label>
                                <input id="propertyid" name="propertyid" type="number" value="<?php echo htmlspecialchars($values['propertyid']); ?>" placeholder="optional: leave blank for auto ID" />
                            </div>

                            <div class="field">
                                <label for="address">Address</label>
                                <input id="address" name="address" type="text" value="<?php echo htmlspecialchars($values['address']); ?>" placeholder="101 Cedar St, Stillwater, OK" required />
                            </div>

                            <div class="field">
                                <label for="manager_emp_id">Manager</label>
                                <select id="manager_emp_id" name="manager_emp_id" required>
                                    <option value="">-- Select Manager --</option>
                                    <?php foreach ($managers as $manager): ?>
                                        <option value="<?php echo (int)$manager['EmpID']; ?>" <?php echo ($values['manager_emp_id'] === (string)$manager['EmpID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars((string)$manager['Lastname'] . ', ' . (string)$manager['Firstname'] . ' (EmpID ' . (int)$manager['EmpID'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field" style="margin-bottom:0;">
                                <label for="unit_count">Unit Count</label>
                                <input id="unit_count" name="unit_count" type="number" min="0" value="<?php echo htmlspecialchars($values['unit_count']); ?>" required />
                            </div>

                            <div class="btn-row">
                                <button class="btn" type="submit">Create Property</button>
                                <a class="btn btn-outline" href="admin-properties-view.php">Cancel</a>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </main>

        <footer>
            &copy; <?php echo date("Y"); ?> Cowboy Properties
        </footer>
    </div>
</body>

</html>