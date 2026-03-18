<?php
// unauthorized.php — Shown when a user tries to access a page they don't have permission for.
require_once __DIR__ . '/auth_check.php';
session_init();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Access Denied</title>
    <style>
        :root {
            --bg: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --border: #e2e8f0;
            --brand: #0ea5e9;
            --brand-dark: #0284c7;
            --soft: #f8fafc;
            --danger: #ef4444;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .page { min-height: 100vh; display: flex; flex-direction: column; }

        .navbar {
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            border-bottom: 1px solid var(--border);
            background: #fff;
        }

        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; }

        .logo {
            width: 44px; height: 44px; border-radius: 999px;
            border: 2px solid #0f172a; display: grid;
            place-items: center; font-weight: 700;
        }

        .nav-links a {
            text-decoration: none; color: var(--muted);
            font-size: 14px; padding: 8px 10px; border-radius: 10px;
        }

        .nav-links a:hover { background: var(--soft); color: var(--text); }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
        }

        .card {
            width: min(480px, 92vw);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px 32px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            text-align: center;
        }

        .icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .card h1 {
            margin: 0 0 10px;
            font-size: 22px;
            color: var(--danger);
        }

        .card p {
            margin: 0 0 28px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .btn-row { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 12px;
            border: 1px solid transparent;
            background: var(--brand);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            transition: background .12s, transform .12s;
        }

        .btn:hover { background: var(--brand-dark); transform: translateY(-1px); }

        .btn-outline {
            background: #fff;
            color: var(--text);
            border-color: var(--border);
        }

        .btn-outline:hover { background: var(--soft); }

        footer {
            border-top: 1px solid var(--border);
            padding: 14px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
        }
    </style>
</head>
<body>
<div class="page">
    <header class="navbar">
        <a class="brand" href="index.php">
            <div class="logo">CP</div>
            <strong>Cowboy Properties</strong>
        </a>
        <nav class="nav-links">
            <?php if (is_logged_in()): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="Pages/login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <div class="card">
            <div class="icon">&#128274;</div>
            <h1>Access Denied</h1>
            <p>You do not have permission to view this page.<br>
               Please log in with an account that has the required role.</p>

            <div class="btn-row">
                <?php if (is_logged_in()): ?>
                    <?php $u = current_user(); ?>
                    <?php if ($u['role'] === 'employee'): ?>
                        <a class="btn" href="Pages/adminhome.php">Go to Dashboard</a>
                    <?php else: ?>
                        <a class="btn" href="Pages/renter-dashboard.php">Go to Dashboard</a>
                    <?php endif; ?>
                    <a class="btn btn-outline" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="btn" href="Pages/login.php">Go to Login</a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>&copy; <?php echo date("Y"); ?> Cowboy Properties</footer>
</div>
</body>
</html>
