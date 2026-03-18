<?php
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$q = trim($_GET['q'] ?? '');
$created = isset($_GET['created']);
$updated = isset($_GET['updated']);
$deleted = isset($_GET['deleted']);
$error = '';
$employees = [];

try {
    $pdo = get_db();

    if ($q !== '') {
        $stmt = $pdo->prepare('SELECT EmpID, Firstname, Lastname, Position, Email FROM employee WHERE Firstname LIKE ? OR Lastname LIKE ? OR Position LIKE ? OR Email LIKE ? ORDER BY EmpID DESC');
        $term = '%' . $q . '%';
        $stmt->execute([$term, $term, $term, $term]);
    } else {
        $stmt = $pdo->query('SELECT EmpID, Firstname, Lastname, Position, Email FROM employee ORDER BY EmpID DESC');
    }

    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Unable to load employees right now.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Admin Employees</title>
    <style>
        :root {
            --bg: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --border: #e2e8f0;
            --brand: #0ea5e9;
            --brand-dark: #0284c7;
            --soft: #f8fafc;
            --row: #ffffff;
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

        main {
            flex: 1;
            padding: 34px 16px 48px;
            display: flex;
            justify-content: center;
        }

        .wrap {
            width: min(980px, 92vw);
        }

        .title-row {
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

        .subtitle {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .panel {
            border: 1px solid var(--border);
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            background: #fff;
        }

        .search {
            flex: 1;
            position: relative;
        }

        .search input {
            width: 100%;
            padding: 11px 12px 11px 38px;
            border-radius: 14px;
            border: 1px solid var(--border);
            outline: none;
            font-size: 14px;
            background: #fff;
        }

        .search input:focus {
            border-color: rgba(14, 165, 233, .7);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, .15);
        }

        .search .icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 14px;
            user-select: none;
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
            text-decoration: none;
            cursor: pointer;
            transition: transform 120ms ease, background 120ms ease;
            min-width: 92px;
            font-size: 14px;
        }

        .btn:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0px);
        }

        .btn-outline {
            background: #fff;
            color: var(--text);
            border: 1px solid var(--border);
            font-weight: 700;
            min-width: 84px;
        }

        .btn-outline:hover {
            background: var(--soft);
            transform: translateY(-1px);
        }

        .rows {
            display: flex;
            flex-direction: column;
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

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--row);
        }

        .row:last-child {
            border-bottom: none;
        }

        .name {
            font-weight: 700;
            font-size: 14px;
        }

        .meta {
            color: var(--muted);
            font-size: 13px;
        }

        footer {
            border-top: 1px solid var(--border);
            padding: 14px 20px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
        }

        @media (max-width: 560px) {
            .panel-header {
                flex-direction: column;
                align-items: stretch;
            }

            .btn,
            .btn-outline {
                width: 100%;
            }
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
                <a href="admin-employees-view.php">Employees</a>
                <a href="admin-properties-view.php">Properties</a>
                <a href="../Lease-CRUD/admin-leases-view.php">Leases</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </header>

        <main>
            <div class="wrap">
                <div class="title-row">
                    <div>
                        <h1>Employees</h1>
                        <p class="subtitle">Search and manage employee accounts.</p>
                    </div>
                </div>

                <section class="panel" aria-label="Employee list">
                    <?php if ($created): ?>
                        <p class="alert alert-success">Employee created successfully.</p>
                    <?php endif; ?>
                    <?php if ($updated): ?>
                        <p class="alert alert-success">Employee updated successfully.</p>
                    <?php endif; ?>
                    <?php if ($deleted): ?>
                        <p class="alert alert-success">Employee deleted successfully.</p>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <p class="alert alert-error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <div class="panel-header">
                        <form class="search" method="GET" action="admin-employees-view.php">
                            <span class="icon">🔍</span>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search" aria-label="Search employees" />
                        </form>

                        <a class="btn" href="admin-employee-add.php">New</a>
                    </div>

                    <div class="rows">
                        <?php if (!$employees): ?>
                            <div class="row">
                                <div class="name">No employees found.</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($employees as $e): ?>
                                <div class="row">
                                    <div>
                                        <div class="name"><?php echo htmlspecialchars(trim(($e['Firstname'] ?? '') . ' ' . ($e['Lastname'] ?? ''))); ?></div>
                                        <div class="meta"><?php echo htmlspecialchars((string)($e['Position'] ?? '')); ?> | <?php echo htmlspecialchars((string)($e['Email'] ?? '')); ?></div>
                                    </div>
                                    <a class="btn-outline btn" href="admin-employee-details.php?id=<?php echo (int)$e['EmpID']; ?>">View</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>

        <footer>
            &copy; <?php echo date('Y'); ?> Cowboy Properties
        </footer>
    </div>
</body>

</html>