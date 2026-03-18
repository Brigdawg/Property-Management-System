<?php
require_once __DIR__ . '/../auth_check.php';
require_renter('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$renterId = (int)($_SESSION['user_id'] ?? $_GET['renter_id'] ?? $_POST['renter_id'] ?? 0);
$renter = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
];

$error = '';

try {
    $pdo = get_db();

    if ($renterId <= 0) {
        $fallbackStmt = $pdo->query('SELECT RenterID FROM renter ORDER BY RenterID ASC LIMIT 1');
        $renterId = (int)$fallbackStmt->fetchColumn();
    }

    if ($renterId <= 0) {
        $error = 'No renter account was found to edit.';
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $phone = trim($_POST['phone'] ?? '');

            if ($firstName === '' || $lastName === '' || $email === '') {
                $error = 'First name, last name, and email are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                $updateStmt = $pdo->prepare('
                    UPDATE renter
                    SET Firstname = :firstname,
                        Lastname = :lastname,
                        email = :email,
                        phone = :phone
                    WHERE RenterID = :renter_id
                ');
                $updateStmt->execute([
                    ':firstname' => $firstName,
                    ':lastname' => $lastName,
                    ':email' => $email,
                    ':phone' => $phone !== '' ? $phone : null,
                    ':renter_id' => $renterId,
                ]);

                header('Location: renter-manage-account.php?updated=success&renter_id=' . $renterId);
                exit;
            }

            $renter['first_name'] = $firstName;
            $renter['last_name'] = $lastName;
            $renter['email'] = $email;
            $renter['phone'] = $phone;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $error === '') {
            $infoStmt = $pdo->prepare('SELECT Firstname, Lastname, email, phone FROM renter WHERE RenterID = :renter_id LIMIT 1');
            $infoStmt->execute([':renter_id' => $renterId]);
            $info = $infoStmt->fetch();

            if ($info) {
                $renter['first_name'] = (string)($info['Firstname'] ?? '');
                $renter['last_name'] = (string)($info['Lastname'] ?? '');
                $renter['email'] = (string)($info['email'] ?? '');
                $renter['phone'] = (string)($info['phone'] ?? '');
            } elseif ($error === '') {
                $error = 'Unable to load renter details.';
            }
        }
    }
} catch (Throwable $e) {
    $error = 'Unable to update account details right now.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Update Account Information</title>
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

        /* Header (same style) */
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

        .center-nav {
            display: flex;
            gap: 22px;
            font-weight: 600;
        }

        .center-nav a {
            text-decoration: none;
            color: var(--muted);
            font-size: 14px;
            padding: 8px 12px;
            border-radius: 10px;
        }

        .center-nav a:hover {
            background: var(--soft);
            color: var(--text);
        }

        main {
            flex: 1;
            padding: 44px 16px 60px;
            display: flex;
            justify-content: center;
        }

        .wrap {
            width: min(1100px, 92vw);
            text-align: center;
        }

        .page-title {
            margin: 0 0 22px;
            font-size: 26px;
            letter-spacing: -0.02em;
        }

        .panel {
            margin: 0 auto;
            border: 1px solid var(--border);
            border-radius: 24px;
            background: #fff;
            padding: 26px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
            width: min(900px, 100%);
            text-align: left;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px 18px;
            background: #fff;
        }

        .card h3 {
            margin: 0 0 14px;
            font-size: 16px;
            text-align: center;
        }

        .field {
            display: grid;
            grid-template-columns: 120px 1fr;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        label {
            font-size: 13px;
            font-weight: 800;
            color: var(--text);
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
            min-width: 170px;
            font-size: 14px;
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

        @media (max-width: 720px) {
            .field {
                grid-template-columns: 1fr;
            }

            label {
                color: var(--muted);
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

            <!-- Nav centered in navbar -->
            <nav class="center-nav">
                <a href="renter-dashboard.php">Dashboard</a>
                <a href="renter-manage-account.php">Manage Account</a>
                <a href="../logout.php">Logout</a>
            </nav>

            <!-- Spacer to keep center-nav centered -->
            <div style="width:120px;"></div>
        </header>

        <main>
            <div class="wrap">
                <h1 class="page-title">Update Account Information</h1>

                <section class="panel" aria-label="Update account info panel">
                    <div class="card" aria-label="Edit personal information">
                        <h3>Edit Personal Information</h3>

                        <?php if ($error): ?>
                            <p style="color:#b91c1c; margin-top:0; margin-bottom:14px;"><?php echo htmlspecialchars($error); ?></p>
                        <?php endif; ?>

                        <form id="editForm" action="renter-edit-info.php" method="POST">
                            <input type="hidden" name="renter_id" value="<?php echo (int)$renterId; ?>" />
                            <div class="field">
                                <label for="first_name">First Name:</label>
                                <input id="first_name" name="first_name" type="text" value="<?php echo htmlspecialchars($renter['first_name']); ?>" required />
                            </div>

                            <div class="field">
                                <label for="last_name">Last Name:</label>
                                <input id="last_name" name="last_name" type="text" value="<?php echo htmlspecialchars($renter['last_name']); ?>" required />
                            </div>

                            <div class="field">
                                <label for="email">Email:</label>
                                <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($renter['email']); ?>" required />
                            </div>

                            <div class="field" style="margin-bottom:0;">
                                <label for="phone">Phone:</label>
                                <input id="phone" name="phone" type="text" value="<?php echo htmlspecialchars($renter['phone']); ?>" />
                            </div>

                            <div class="btn-row">
                                <button class="btn" type="submit">Save Changes</button>
                                <a class="btn btn-outline" href="renter-manage-account.php<?php echo $renterId > 0 ? '?renter_id=' . (int)$renterId : ''; ?>">Cancel</a>
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