<?php
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: admin-properties-view.php');
    exit;
}

$error = '';
$managers = [];

try {
    $pdo = get_db();

    $managerStmt = $pdo->query("SELECT EmpID, Firstname, Lastname FROM employee ORDER BY Lastname ASC, Firstname ASC");
    $managers = $managerStmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $address = trim((string)($_POST['address'] ?? ''));
        $managerEmpId = (int)($_POST['manager_emp_id'] ?? 0);
        $unitCount = (int)($_POST['unit_count'] ?? 0);

        if ($address === '') {
            $error = 'Address is required.';
        } elseif ($managerEmpId <= 0) {
            $error = 'Manager is required.';
        } elseif ($unitCount < 0) {
            $error = 'Unit count must be 0 or greater.';
        } else {
            $stmt = $pdo->prepare('UPDATE property SET Address = ?, ManagerEmpID = ?, Unit_Count = ? WHERE PropertyID = ?');
            $stmt->execute([$address, $managerEmpId, $unitCount, $id]);

            header('Location: admin-property-details.php?id=' . $id . '&updated=1');
            exit;
        }
    }

    $stmt = $pdo->prepare('SELECT PropertyID, Address, ManagerEmpID, Unit_Count FROM property WHERE PropertyID = ? LIMIT 1');
    $stmt->execute([$id]);
    $property = $stmt->fetch();

    if (!$property) {
        header('Location: admin-properties-view.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Unable to load or update this property right now.';
    $property = [
        'PropertyID' => $id,
        'Address' => '',
        'ManagerEmpID' => 0,
        'Unit_Count' => 0,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property</title>

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

        .navbar {
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            border-bottom: 1px solid var(--border);
            background: #fff;
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
            padding: 40px 16px;
            display: flex;
            justify-content: center;
        }

        .wrap {
            width: min(820px, 92vw);
        }

        .panel {
            border: 1px solid var(--border);
            border-radius: 24px;
            background: #fff;
            padding: 28px;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
        }

        .field {
            display: grid;
            grid-template-columns: 130px 1fr;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        label {
            font-size: 13px;
            font-weight: 700;
            color: var(--muted);
        }

        input,
        select {
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 14px;
            width: 100%;
        }

        input:focus,
        select:focus {
            border-color: rgba(14, 165, 233, .7);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, .15);
            outline: none;
        }

        .actions {
            margin-top: 22px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
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

        .btn {
            padding: 10px 18px;
            border-radius: 12px;
            border: 1px solid transparent;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
            transition: all .15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--brand-dark);
        }

        .btn-outline {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text);
        }

        .btn-outline:hover {
            background: var(--soft);
        }

        footer {
            margin-top: 40px;
            padding: 14px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
            border-top: 1px solid var(--border);
        }
    </style>
</head>

<body>

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
            <h2>Edit Property</h2>

            <div class="panel">
                <?php if ($error): ?>
                    <p class="alert"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form action="admin-property-edit.php?id=<?php echo (int)$id; ?>" method="POST">
                    <div class="field">
                        <label>Address</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars((string)($property['Address'] ?? '')); ?>" required>
                    </div>

                    <div class="field">
                        <label>Manager</label>
                        <select name="manager_emp_id" required>
                            <option value="">-- Select Manager --</option>
                            <?php foreach ($managers as $manager): ?>
                                <option value="<?php echo (int)$manager['EmpID']; ?>" <?php echo ((int)$property['ManagerEmpID'] === (int)$manager['EmpID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string)$manager['Lastname'] . ', ' . (string)$manager['Firstname'] . ' (EmpID ' . (int)$manager['EmpID'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label>Unit Count</label>
                        <input type="number" name="unit_count" min="0" value="<?php echo (int)($property['Unit_Count'] ?? 0); ?>" required>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="admin-property-details.php?id=<?php echo (int)$id; ?>" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        &copy; <?php echo date("Y"); ?> Cowboy Properties
    </footer>

</body>

</html>