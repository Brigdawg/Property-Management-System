<?php
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: admin-employees-view.php');
    exit;
}

$error = '';

try {
    $pdo = get_db();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if ($firstname === '' || $lastname === '' || $position === '' || $email === '') {
            $error = 'Firstname, lastname, position, and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            if ($password !== '') {
                $stmt = $pdo->prepare('UPDATE employee SET Firstname = ?, Lastname = ?, Position = ?, Email = ?, phone = ?, Password = ? WHERE EmpID = ?');
                $stmt->execute([$firstname, $lastname, $position, $email, $phone !== '' ? $phone : null, password_hash($password, PASSWORD_DEFAULT), $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE employee SET Firstname = ?, Lastname = ?, Position = ?, Email = ?, phone = ? WHERE EmpID = ?');
                $stmt->execute([$firstname, $lastname, $position, $email, $phone !== '' ? $phone : null, $id]);
            }

            header('Location: admin-employee-details.php?id=' . $id . '&updated=1');
            exit;
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
    $error = 'Unable to load or update this employee right now.';
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
    <title>Edit Employee</title>

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
            grid-template-columns: 110px 1fr;
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
        }

        input:focus {
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
            <h2>Edit Employee</h2>

            <div class="panel">
                <?php if ($error): ?>
                    <p class="alert"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form action="admin-employee-edit.php?id=<?php echo (int)$id; ?>" method="POST">

                    <div class="field">
                        <label>First Name</label>
                        <input type="text" name="firstname" value="<?php echo htmlspecialchars($employee['Firstname'] ?? ''); ?>" required>
                    </div>

                    <div class="field">
                        <label>Last Name</label>
                        <input type="text" name="lastname" value="<?php echo htmlspecialchars($employee['Lastname'] ?? ''); ?>" required>
                    </div>

                    <div class="field">
                        <label>Position</label>
                        <input type="text" name="position" value="<?php echo htmlspecialchars($employee['Position'] ?? ''); ?>" required>
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($employee['Email'] ?? ''); ?>" required>
                    </div>

                    <div class="field">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>">
                    </div>

                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep existing password">
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="admin-employee-details.php?id=<?php echo (int)$id; ?>" class="btn btn-outline">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> Cowboy Properties
    </footer>

</body>

</html>