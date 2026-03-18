<?php
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: admin-employees-view.php');
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
            $depChecks = [
                'properties' => 'SELECT COUNT(*) FROM property WHERE ManagerEmpID = ?',
                'leases' => 'SELECT COUNT(*) FROM lease WHERE EmpID = ?',
                'payments' => 'SELECT COUNT(*) FROM payment WHERE EmpID = ?',
                'maintenance tickets' => 'SELECT COUNT(*) FROM maintenance WHERE EmpID = ?',
                'assignments' => 'SELECT COUNT(*) FROM assignment WHERE EmpID = ?',
            ];

            $usedBy = [];
            foreach ($depChecks as $label => $sql) {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                $count = (int)$stmt->fetchColumn();
                if ($count > 0) {
                    $usedBy[] = $label . ' (' . $count . ')';
                }
            }

            if ($usedBy) {
                $error = 'Cannot delete employee because linked records exist: ' . implode(', ', $usedBy) . '.';
            } else {
                $del = $pdo->prepare('DELETE FROM employee WHERE EmpID = ?');
                $del->execute([$id]);
                header('Location: admin-employees-view.php?deleted=1');
                exit;
            }
        }
    }

    $stmt = $pdo->prepare('SELECT EmpID, Firstname, Lastname, Position, Email, phone FROM employee WHERE EmpID = ? LIMIT 1');
    $stmt->execute([$id]);
    $employee = $stmt->fetch();

    if (!$employee) {
        header('Location: admin-employees-view.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Unable to load this employee right now.';
    $employee = [
        'EmpID' => $id,
        'Firstname' => '',
        'Lastname' => '',
        'Position' => '',
        'Email' => '',
        'phone' => '',
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details</title>

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
            width: min(800px, 92vw);
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
            grid-template-columns: 100px 1fr;
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

        @media (max-width: 640px) {
            .field {
                grid-template-columns: 1fr;
            }

            label {
                color: var(--text);
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
                <a href="adminhome.php">Dashboard</a>
                <a href="admin-employees-view.php">Employees</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>

        <main>
            <div class="wrap">
                <div class="top-row">
                    <h2>Employee Details</h2>
                    <a class="back-link" href="admin-employees-view.php">← Back to Employees</a>
                </div>

                <div class="panel">
                    <?php if ($created): ?>
                        <p class="alert alert-success">Employee created successfully.</p>
                    <?php endif; ?>
                    <?php if ($updated): ?>
                        <p class="alert alert-success">Employee updated successfully.</p>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <p class="alert alert-error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <form>
                        <div class="field">
                            <label>EmpID</label>
                            <input type="text" value="<?php echo (int)($employee['EmpID'] ?? 0); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>First Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['Firstname'] ?? ''); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Last Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['Lastname'] ?? ''); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Position</label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['Position'] ?? ''); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Email</label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['Email'] ?? ''); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Phone</label>
                            <input type="text" value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>" readonly>
                        </div>

                        <div class="field">
                            <label>Role</label>
                            <input type="text" value="Employee" readonly>
                        </div>

                        <div class="actions">
                            <a class="btn btn-primary" href="admin-employee-edit.php?id=<?php echo (int)$employee['EmpID']; ?>">Edit</a>
                            <button class="btn btn-danger" id="deleteBtn" type="button">Delete</button>
                        </div>
                    </form>

                    <form id="deleteForm" action="admin-employee-details.php?id=<?php echo (int)$id; ?>" method="POST" style="display:none;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="confirmText" name="confirm_text" value="">
                    </form>
                </div>
            </div>
        </main>

        <footer>
            &copy; <?php echo date('Y'); ?> Cowboy Properties
        </footer>
    </div>

    <script>
        document.getElementById('deleteBtn').addEventListener('click', () => {
            const ok = confirm('Delete this employee?');
            if (!ok) {
                return;
            }

            const typed = prompt('Type DELETE to confirm permanently removing this employee.');
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