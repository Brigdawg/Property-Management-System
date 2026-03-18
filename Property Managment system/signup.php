<?php
// signup.php — New renter self-registration
session_start();
require_once __DIR__ . '/db.php';

// Already logged in → redirect away
if (!empty($_SESSION['user_id'])) {
    header('Location: Pages/renter-dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname']  ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $phone     = trim($_POST['phone']     ?? '');
    $password  = $_POST['password']       ?? '';
    $confirm   = $_POST['confirm']        ?? '';

    // ── Basic validation ────────────────────────────────────────────────────
    if (!$firstname || !$lastname || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $pdo = get_db();

        // Check for duplicate email
        $check = $pdo->prepare('SELECT RenterID FROM renter WHERE email = ? LIMIT 1');
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'An account with that email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('
                INSERT INTO renter (Firstname, Lastname, email, password, phone)
                VALUES (?, ?, ?, ?, ?)
            ');
            $insert->execute([$firstname, $lastname, $email, $hashed, $phone ?: null]);

            $newId = (int)$pdo->lastInsertId();

            // Log the new renter in immediately
            $_SESSION['user_id']    = $newId;
            $_SESSION['user_name']  = $firstname . ' ' . $lastname;
            $_SESSION['user_email'] = $email;
            $_SESSION['role']       = 'renter';
            $_SESSION['position']   = 'Renter';

            header('Location: Pages/renter-dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Sign Up</title>
    <style>
        :root {
            --bg: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --border: #e2e8f0;
            --brand: #0ea5e9;
            --brand-dark: #0284c7;
            --card: #ffffff;
            --soft: #f8fafc;
            --error-bg: #fef2f2;
            --error-border: #fecaca;
            --error-text: #b91c1c;
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
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .brand-text strong {
            font-size: 15px;
        }

        .brand-text span {
            font-size: 12px;
            color: var(--muted);
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
            display: grid;
            place-items: center;
            padding: 40px 16px;
        }

        .card {
            width: min(540px, 92vw);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        }

        .card h2 {
            margin: 0 0 6px;
            font-size: 20px;
            font-weight: 700;
        }

        .card .subhead {
            margin: 0 0 20px;
            font-size: 13px;
            color: var(--muted);
        }

        .row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .field {
            margin-bottom: 14px;
        }

        label {
            display: block;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px;
        }

        label .req {
            color: var(--brand-dark);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 11px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            outline: none;
            background: #fff;
            font-size: 14px;
        }

        input:focus {
            border-color: rgba(14, 165, 233, .7);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, .15);
        }

        .alert-error {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error-text);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid transparent;
            background: var(--brand);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            width: 100%;
            transition: transform 120ms ease, background 120ms ease;
        }

        .btn:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .footer-note {
            margin-top: 16px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }

        .footer-note a {
            color: var(--brand-dark);
            text-decoration: underline;
        }

        .footer {
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
            <a class="brand" href="Pages/login.php">
                <div class="logo">CP</div>
                <div class="brand-text">
                    <strong>Cowboy Properties</strong>
                    <span>Property Management Portal</span>
                </div>
            </a>
            <nav class="nav-links">
                <a href="Pages/login.php">Login</a>
            </nav>
        </header>

        <main>
            <section class="card" aria-label="Sign Up Form">
                <h2>Create a Renter Account</h2>
                <p class="subhead">Fill in your details below to get started.</p>

                <?php if ($error): ?>
                    <div class="alert-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="signup.php" novalidate>

                    <div class="row-2">
                        <div class="field">
                            <label for="firstname">First Name <span class="req">*</span></label>
                            <input
                                id="firstname"
                                name="firstname"
                                type="text"
                                placeholder="Jane"
                                value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>"
                                required />
                        </div>
                        <div class="field">
                            <label for="lastname">Last Name <span class="req">*</span></label>
                            <input
                                id="lastname"
                                name="lastname"
                                type="text"
                                placeholder="Smith"
                                value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>"
                                required />
                        </div>
                    </div>

                    <div class="field">
                        <label for="email">Email Address <span class="req">*</span></label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            placeholder="jane@example.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required />
                    </div>

                    <div class="field">
                        <label for="phone">Phone Number</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            placeholder="555-123-4567"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" />
                    </div>

                    <div class="field">
                        <label for="password">Password <span class="req">*</span> <span style="color:var(--muted);font-size:11px">(min. 8 characters)</span></label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="••••••••"
                            required />
                    </div>

                    <div class="field">
                        <label for="confirm">Confirm Password <span class="req">*</span></label>
                        <input
                            id="confirm"
                            name="confirm"
                            type="password"
                            placeholder="••••••••"
                            required />
                    </div>

                    <button class="btn" type="submit">CREATE ACCOUNT</button>

                    <p class="footer-note">
                        Already have an account? <a href="Pages/login.php">Sign in</a>
                    </p>
                </form>
            </section>
        </main>

        <footer class="footer">
            &copy; <?php echo date("Y"); ?> Cowboy Properties
        </footer>
    </div>
</body>

</html>