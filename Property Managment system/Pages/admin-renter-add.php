<?php
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$errors = [];
$values = [
    'renterid' => '',
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['renterid'] = trim($_POST['renterid'] ?? '');
    $values['firstname'] = trim($_POST['firstname'] ?? '');
    $values['lastname'] = trim($_POST['lastname'] ?? '');
    $values['email'] = strtolower(trim($_POST['email'] ?? ''));
    $values['phone'] = trim($_POST['phone'] ?? '');
    $values['password'] = (string)($_POST['password'] ?? '');

    if ($values['firstname'] === '' || $values['lastname'] === '' || $values['email'] === '' || $values['password'] === '') {
        $errors[] = 'Firstname, lastname, email, and password are required.';
    }
    if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!$errors) {
        try {
            $pdo = get_db();

            if ($values['renterid'] !== '') {
                $stmt = $pdo->prepare('INSERT INTO renter (RenterID, Firstname, Lastname, email, password, phone) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    (int)$values['renterid'],
                    $values['firstname'],
                    $values['lastname'],
                    $values['email'],
                    password_hash($values['password'], PASSWORD_DEFAULT),
                    $values['phone'] !== '' ? $values['phone'] : null,
                ]);
                $newId = (int)$values['renterid'];
            } else {
                $stmt = $pdo->prepare('INSERT INTO renter (Firstname, Lastname, email, password, phone) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([
                    $values['firstname'],
                    $values['lastname'],
                    $values['email'],
                    password_hash($values['password'], PASSWORD_DEFAULT),
                    $values['phone'] !== '' ? $values['phone'] : null,
                ]);
                $newId = (int)$pdo->lastInsertId();
            }

            header('Location: admin-renter-details.php?id=' . $newId . '&created=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Unable to create renter. Confirm the ID/email is unique and try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Add Renter</title>
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

        /* Admin header (same style as your admin pages) */
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

        input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 14px;
            outline: none;
            background: #fff;
        }

        input:focus {
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
            <a class="brand" href="adminhome.php">
                <div class="logo">CP</div>
                <strong>Cowboy Properties</strong>
            </a>

            <nav class="nav-links">
                <a href="adminhome.php">Dashboard</a>
                <a href="admin-renters-view.php">Renters</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>

        <main>
            <div class="wrap">
                <div class="top-row">
                    <h1>Add Renter</h1>
                    <a class="back-link" href="admin-renters-view.php">← Back</a>
                </div>

                <section class="panel" aria-label="Add renter panel">
                    <div class="card">
                        <h2>New Renter Information</h2>

                        <?php if ($errors): ?>
                            <div class="alert">
                                <?php echo htmlspecialchars(implode(' ', $errors)); ?>
                            </div>
                        <?php endif; ?>

                        <form action="admin-renter-add.php" method="POST">
                            <div class="field">
                                <label for="renterid">RenterID (PK)</label>
                                <input id="renterid" name="renterid" type="number" value="<?php echo htmlspecialchars($values['renterid']); ?>" placeholder="optional: leave blank for auto ID" />
                            </div>

                            <div class="field">
                                <label for="firstname">Firstname</label>
                                <input id="firstname" name="firstname" type="text" value="<?php echo htmlspecialchars($values['firstname']); ?>" placeholder="Jane" required />
                            </div>

                            <div class="field">
                                <label for="lastname">Lastname</label>
                                <input id="lastname" name="lastname" type="text" value="<?php echo htmlspecialchars($values['lastname']); ?>" placeholder="Renter" required />
                            </div>

                            <div class="field">
                                <label for="email">Email</label>
                                <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($values['email']); ?>" placeholder="jane.renter@email.com" required />
                            </div>

                            <div class="field">
                                <label for="phone">Phone</label>
                                <input id="phone" name="phone" type="text" value="<?php echo htmlspecialchars($values['phone']); ?>" placeholder="(555) 222-4444" />
                            </div>

                            <div class="field" style="margin-bottom:0;">
                                <label for="password">Password</label>
                                <input id="password" name="password" type="password" placeholder="Temporary password" required />
                            </div>

                            <div class="btn-row">
                                <button class="btn" type="submit">Create Renter</button>
                                <a class="btn btn-outline" href="admin-renters-view.php">Cancel</a>
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