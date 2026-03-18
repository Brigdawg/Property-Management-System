<?php
// adminhome.php — Admin Dashboard Home
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Admin</title>
    <style>
        :root {
            --bg: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --border: #e2e8f0;
            --brand: #0ea5e9;
            --brand-dark: #0284c7;
            --soft: #f8fafc;
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

        /* Navbar */
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

        /* Main */
        main {
            flex: 1;
            padding: 42px 16px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .wrap {
            width: min(980px, 92vw);
        }

        .page-title {
            margin: 0 0 18px;
            font-size: 20px;
            letter-spacing: -0.01em;
        }

        .page-subtitle {
            margin: 0 0 28px;
            color: var(--muted);
            font-size: 14px;
        }

        .tiles {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }

        .tile {
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 26px 18px;
            background: #fff;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 160px;
            text-align: center;
        }

        .tile h3 {
            margin: 0 0 14px;
            font-size: 18px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border-radius: 12px;
            border: 1px solid transparent;
            background: var(--brand);
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 120ms ease, background 120ms ease;
            min-width: 110px;
        }

        .btn:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0px);
        }

        /* Responsive */
        @media (max-width: 820px) {
            .tiles {
                grid-template-columns: 1fr;
            }

            .tile {
                min-height: 140px;
            }
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
                <div class="brand-text">
                    <strong>Cowboy Properties</strong>
                    <span>Admin Dashboard</span>
                </div>
            </a>

            <nav class="nav-links">
                <a href="adminhome.php">Dashboard</a>
                <a href="admin-renters-view.php">Renters</a>
                <a href="admin-properties-view.php">Properties</a>
                <a href="admin-employees-view.php">Employees</a>
                <a href="../Lease-CRUD/admin-leases-view.php">Leases</a>
                <span style="font-size:13px;color:var(--muted);padding:0 4px"><?php echo htmlspecialchars($user['name']); ?></span>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>

        <main>
            <div class="wrap">
                <h1 class="page-title">Admin Home</h1>
                <p class="page-subtitle">Choose a section to view and manage records.</p>

                <section class="tiles" aria-label="Admin Sections">
                    <div class="tile">
                        <h3>Renters</h3>
                        <a class="btn" href="admin-renters-view.php">View</a>
                    </div>

                    <div class="tile">
                        <h3>Properties</h3>
                        <a class="btn" href="admin-properties-view.php">View</a>
                    </div>

                    <div class="tile">
                        <h3>Employees</h3>
                        <a class="btn" href="admin-employees-view.php">View</a>
                    </div>

                    <div class="tile">
                        <h3>Leases</h3>
                        <a class="btn" href="../Lease-CRUD/admin-leases-view.php">View</a>
                    </div>

                    <div class="tile">
                        <h3>Maintenance</h3>
                        <a class="btn" href="maintenance.php">View</a>
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