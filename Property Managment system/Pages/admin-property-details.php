<?php
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: admin-properties-view.php');
    exit;
}

$created = isset($_GET['created']);
$updated = isset($_GET['updated']);
$error = '';

try {
    $pdo = get_db();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
        $confirmText = trim((string)($_POST['confirm_text'] ?? ''));
        if ($confirmText !== 'DELETE') {
            $error = 'Delete cancelled. Type DELETE in the second confirmation to proceed.';
        } else {
            $del = $pdo->prepare('DELETE FROM property WHERE PropertyID = ?');
            $del->execute([$id]);
            header('Location: admin-properties-view.php?deleted=1');
            exit;
        }
    }

    $stmt = $pdo->prepare('
        SELECT
            p.PropertyID,
            p.Address,
            p.ManagerEmpID,
            p.Unit_Count,
            e.Firstname,
            e.Lastname
        FROM property p
        LEFT JOIN employee e ON e.EmpID = p.ManagerEmpID
        WHERE p.PropertyID = ?
        LIMIT 1
    ');
    $stmt->execute([$id]);
    $property = $stmt->fetch();

    if (!$property) {
        header('Location: admin-properties-view.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Unable to load this property right now.';
    $property = [
        'PropertyID' => $id,
        'Address' => '',
        'ManagerEmpID' => '',
        'Unit_Count' => '',
        'Firstname' => '',
        'Lastname' => '',
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details</title>

    <style>
        :root {
            --bg: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --border: #e2e8f0;
            --soft: #f8fafc;
            --brand: #0ea5e9;
            --brand-dark: #0284c7;
            --danger: #ef4444;
            --danger-dark: #dc2626;
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
            padding: 40px 16px;
            display: flex;
            justify-content: center;
        }

        .wrap {
            width: min(820px, 92vw);
        }

        .top-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        h2 {
            margin: 0;
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

        input {
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 14px;
            background: #fff;
        }

        input[readonly] {
            background: #fff;
            color: var(--text);
        }

        .actions {
            margin-top: 22px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
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

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-danger:hover {
            background: var(--danger-dark);
        }

        .alert {
            margin: 0 0 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13px;
            background: #fff;
        }

        .alert-success {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .alert-error {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        footer {
            border-top: 1px solid var(--border);
            padding: 14px 20px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
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
                    <h2>Property Details</h2>
                    <a class="back-link" href="admin-properties-view.php">← Back to Properties</a>
                </div>

                <div class="panel">
                    <?php if ($created): ?>
                        <p class="alert alert-success">Property created successfully.</p>
                    <?php endif; ?>
                    <?php if ($updated): ?>
                        <p class="alert alert-success">Property updated successfully.</p>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <p class="alert alert-error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <?php $managerName = trim((string)($property['Firstname'] ?? '') . ' ' . (string)($property['Lastname'] ?? '')); ?>

                    <form>
                        <div class="field">
                            <label>Property ID</label>
                            <input type="text" value="<?php echo (int)($property['PropertyID'] ?? 0); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Address</label>
                            <input type="text" value="<?php echo htmlspecialchars((string)($property['Address'] ?? '')); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Manager</label>
                            <input type="text" value="<?php echo htmlspecialchars($managerName !== '' ? $managerName : 'Unassigned'); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Manager EmpID</label>
                            <input type="text" value="<?php echo (int)($property['ManagerEmpID'] ?? 0); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Unit Count</label>
                            <input type="text" value="<?php echo (int)($property['Unit_Count'] ?? 0); ?>" readonly>
                        </div>

                        <div class="actions">
                            <a class="btn btn-primary" href="admin-property-edit.php?id=<?php echo (int)$property['PropertyID']; ?>">Edit</a>
                            <button class="btn btn-danger" id="deleteBtn" type="button">Delete</button>
                        </div>
                    </form>

                    <form id="deleteForm" action="admin-property-details.php?id=<?php echo (int)$id; ?>" method="POST" style="display:none;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="confirmText" name="confirm_text" value="">
                    </form>
                </div>
            </div>
        </main>

        <footer>
            &copy; <?php echo date("Y"); ?> Cowboy Properties
        </footer>
    </div>

    <script>
        document.getElementById("deleteBtn").addEventListener("click", () => {
            const ok = confirm("Delete this property?");
            if (!ok) {
                return;
            }

            const typed = prompt('Type DELETE to confirm permanently removing this property.');
            if (typed !== 'DELETE') {
                alert('Delete cancelled. You must type DELETE exactly.');
                return;
            }

            document.getElementById('confirmText').value = typed;
            document.getElementById('deleteForm').submit();
        });
    </script>

</body>

</html>