<?php
// login.php — Authenticates users against the cowboy_properties database.
// Employees are redirected to adminhome.php; Renters to renter-dashboard.php.

session_start();
require_once __DIR__ . '/../db.php';

// Already logged in → skip the form
if (!empty($_SESSION['user_id'])) {
    header($_SESSION['role'] === 'employee' ? 'Location: adminhome.php' : 'Location: renter-dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email']    ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } else {
        $pdo = get_db();

        // Check employee table first
        $stmt = $pdo->prepare('SELECT EmpID, Firstname, Lastname, Email, Password, Position FROM employee WHERE Email = ? LIMIT 1');
        $stmt->execute([$email]);
        $emp = $stmt->fetch();

        if ($emp && password_verify($password, $emp['Password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = (int)$emp['EmpID'];
            $_SESSION['user_name']  = $emp['Firstname'] . ' ' . $emp['Lastname'];
            $_SESSION['user_email'] = $emp['Email'];
            $_SESSION['role']       = 'employee';
            $_SESSION['position']   = $emp['Position'];
            header('Location: adminhome.php');
            exit;
        }

        // Check renter table
        $stmt = $pdo->prepare('SELECT RenterID, Firstname, Lastname, email, password FROM renter WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $renter = $stmt->fetch();

        if ($renter && password_verify($password, $renter['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = (int)$renter['RenterID'];
            $_SESSION['user_name']  = $renter['Firstname'] . ' ' . $renter['Lastname'];
            $_SESSION['user_email'] = $renter['email'];
            $_SESSION['role']       = 'renter';
            $_SESSION['position']   = 'Renter';
            header('Location: renter-dashboard.php');
            exit;
        }

        $error = 'Invalid email or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Login</title>
    <style>
        :root {
            --bg: #ffffff; --text: #0f172a; --muted: #475569; --border: #e2e8f0;
            --brand: #0ea5e9; --brand-dark: #0284c7; --card: #ffffff; --soft: #f8fafc;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background: var(--bg); color: var(--text); }
        .page { min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { height: 72px; display: flex; align-items: center; justify-content: space-between; padding: 0 28px; border-bottom: 1px solid var(--border); background: #fff; position: sticky; top: 0; z-index: 10; }
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; }
        .logo { width: 44px; height: 44px; border-radius: 999px; border: 2px solid #0f172a; display: grid; place-items: center; font-weight: 700; letter-spacing: .5px; }
        .brand-text { display: flex; flex-direction: column; line-height: 1.1; }
        .brand-text strong { font-size: 15px; }
        .brand-text span { font-size: 12px; color: var(--muted); }
        .nav-links { display: flex; align-items: center; gap: 14px; }
        .nav-links a { text-decoration: none; color: var(--muted); font-size: 14px; padding: 8px 10px; border-radius: 10px; }
        .nav-links a:hover { background: var(--soft); color: var(--text); }
        main { flex: 1; display: grid; place-items: center; padding: 40px 16px; }
        .card { width: min(520px, 92vw); background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 28px; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08); }
        .card h2 { margin: 0 0 4px; font-size: 20px; font-weight: 700; }
        .card .subhead { margin: 0 0 18px; font-size: 13px; color: var(--muted); }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 10px; padding: 10px 14px; font-size: 13px; margin-bottom: 16px; }
        .field { margin-bottom: 14px; }
        label { display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px; }
        input[type="email"], input[type="password"] { width: 100%; padding: 11px 12px; border-radius: 12px; border: 1px solid var(--border); outline: none; background: #fff; font-size: 14px; }
        input:focus { border-color: rgba(14, 165, 233, .7); box-shadow: 0 0 0 4px rgba(14, 165, 233, .15); }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 16px; border-radius: 12px; border: 1px solid transparent; background: var(--brand); color: #fff; font-weight: 700; font-size: 14px; letter-spacing: .2px; text-decoration: none; cursor: pointer; transition: transform 120ms ease, background 120ms ease; min-width: 120px; }
        .btn:hover { background: var(--brand-dark); transform: translateY(-1px); }
        .btn:active { transform: translateY(0px); }
        hr { border: none; border-top: 1px solid var(--border); margin: 18px 0; }
        .subhead-sm { font-size: 13px; color: var(--muted); margin: 0 0 10px; }
        .footer { border-top: 1px solid var(--border); padding: 14px 20px; text-align: center; font-size: 12px; color: var(--muted); }
    </style>
</head>
<body>
<div class="page">
    <header class="navbar">
        <a class="brand" href="../index.php">
            <div class="logo">CP</div>
            <div class="brand-text">
                <strong>Cowboy Properties</strong>
                <span>Property Management Portal</span>
            </div>
        </a>
        <nav class="nav-links">
            <a href="../index.php">Home</a>
            <a href="login.php">Login</a>
        </nav>
    </header>

    <main>
        <section class="card" aria-label="Login Form">
            <h2>Sign In</h2>
            <p class="subhead">Use your email and password to access your account.</p>

            <?php if ($error): ?>
                <div class="alert-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST" novalidate>
                <div class="field">
                    <label for="email">Email Address:</label>
                    <input id="email" name="email" type="email" placeholder="you@example.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           autocomplete="email" required />
                </div>
                <div class="field">
                    <label for="password">Password:</label>
                    <input id="password" name="password" type="password" placeholder="••••••••"
                           autocomplete="current-password" required />
                </div>
                <button class="btn" type="submit">SIGN IN</button>

                <hr />
                <p class="subhead-sm">New Renter?</p>
                <a class="btn" href="../signup.php">SIGN UP</a>
            </form>
        </section>
    </main>

    <footer class="footer">
        &copy; <?php echo date("Y"); ?> Cowboy Properties
    </footer>
</div>
</body>
</html>
