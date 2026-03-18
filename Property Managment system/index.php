<?php
// this is the landing page
// index.php (Home Page)
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Home</title>
    <style>
        :root {
            --bg: #ffffff;
            --text: #0f172a;
            /* slate-900 */
            --muted: #475569;
            /* slate-600 */
            --border: #e2e8f0;
            /* slate-200 */
            --brand: #0ea5e9;
            /* sky-500 */
            --brand-dark: #0284c7;
            /* sky-600 */
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

        /* Page shell */
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
            letter-spacing: 0.5px;
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
            background: #f8fafc;
            color: var(--text);
        }

        /* Main content */
        main {
            flex: 1;
            display: grid;
            place-items: center;
            padding: 48px 16px;
        }

        .hero {
            width: min(720px, 92vw);
            text-align: center;
            padding: 28px 18px;
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: clamp(28px, 4vw, 44px);
            letter-spacing: -0.02em;
        }

        .hero p {
            margin: 0 0 22px;
            color: var(--muted);
            font-size: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 12px;
            border: 1px solid transparent;
            background: var(--brand);
            color: white;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: transform 120ms ease, background 120ms ease;
            min-width: 140px;
        }

        .btn:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0px);
        }

        /* Optional footer (simple, clean) */
        footer {
            border-top: 1px solid var(--border);
            padding: 14px 20px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="page">
        <header class="navbar">
            <a class="brand" href="index.php">
                <div class="logo">CP</div>
                <div class="brand-text">
                    <strong>Cowboy Properties</strong>
                    <span>Property Management Portal</span>
                </div>
            </a>

            <!-- Keep this minimal like your wireframe; you can remove links if you want -->
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="Pages/login.php">Login</a>
            </nav>
        </header>

        <main>
            <section class="hero" aria-label="Welcome">
                <h1>Welcome to Cowboy Properties</h1>
                <p>Please log in to access your dashboard and manage records.</p>
                <a class="btn" href="Pages/login.php">Login</a>
            </section>
        </main>

        <footer>
            &copy; <?php echo date("Y"); ?> Cowboy Properties
        </footer>
    </div>
</body>

</html>