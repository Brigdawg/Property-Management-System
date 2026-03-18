<?php
require_once __DIR__ . '/../auth_check.php';
require_renter('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$renter = [
    'name' => 'Renter',
    'unit' => 'N/A',
    'lease_status' => 'No Active Lease',
    'balance' => '$0.00',
];

$error = '';

try {
    $pdo = get_db();

    $renterId = (int)($_SESSION['user_id'] ?? $_GET['renter_id'] ?? 0);

    if ($renterId <= 0) {
        $fallbackStmt = $pdo->query("SELECT RenterID FROM renter ORDER BY RenterID ASC LIMIT 1");
        $renterId = (int)$fallbackStmt->fetchColumn();
    }

    if ($renterId > 0) {
        $summaryStmt = $pdo->prepare(" 
            SELECT
                r.RenterID,
                r.Firstname,
                r.Lastname,
                l.LeaseID,
                l.Price,
                l.period,
                u.Unit_number
            FROM renter r
            LEFT JOIN lease l
                ON l.RenterID = r.RenterID
            LEFT JOIN unit u
                ON u.UnitID = l.UnitID
            WHERE r.RenterID = :renter_id
            ORDER BY l.LeaseID DESC
            LIMIT 1
        ");
        $summaryStmt->execute([':renter_id' => $renterId]);
        $summary = $summaryStmt->fetch();

        if ($summary) {
            $renter['name'] = trim(($summary['Firstname'] ?? '') . ' ' . ($summary['Lastname'] ?? '')) ?: 'Renter';
            $renter['unit'] = $summary['Unit_number'] ?? 'N/A';
            $renter['lease_status'] = !empty($summary['LeaseID']) ? 'Active' : 'No Active Lease';

            $balanceValue = 0.00;
            $leaseId = (int)($summary['LeaseID'] ?? 0);

            if ($leaseId > 0) {
                $price = (float)($summary['Price'] ?? 0);
                $periodMonths = (int)($summary['period'] ?? 0);
                $totalDue = $price * $periodMonths;

                $paymentStmt = $pdo->prepare(" 
                    SELECT COALESCE(SUM(amount), 0)
                    FROM payment
                    WHERE LeaseID = :lease_id
                ");
                $paymentStmt->execute([':lease_id' => $leaseId]);
                $totalPaid = (float)$paymentStmt->fetchColumn();

                $balanceValue = $totalDue - $totalPaid;
            }

            $renter['balance'] = '$' . number_format($balanceValue, 2);
        }
    }
} catch (Throwable $e) {
    $error = 'Unable to load dashboard data right now.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renter Dashboard</title>

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

        /* Navbar */
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
            padding: 60px 16px;
            display: flex;
            justify-content: center;
        }

        .wrap {
            width: min(1000px, 92vw);
            text-align: center;
        }

        .page-title {
            margin-bottom: 28px;
            font-size: 26px;
            letter-spacing: -0.02em;
        }

        /* Larger renter info box */
        .panel {
            border: 1px solid var(--border);
            border-radius: 24px;
            background: #fff;
            padding: 40px 48px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
            max-width: 650px;
            margin: 0 auto;
        }

        .panel h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .panel ul {
            list-style: none;
            padding: 0;
            margin: 0 0 28px;
            font-size: 16px;
            line-height: 1.8;
        }

        .panel li {
            margin-bottom: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 14px;
            border: 1px solid transparent;
            background: var(--brand);
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all .15s ease;
            min-width: 200px;
        }

        .btn:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
        }

        .btn-row {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

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
            <a class="brand" href="../index.php">
                <div class="logo">CP</div>
                <strong>Cowboy Properties</strong>
            </a>

            <!-- Navigation centered in navbar -->
            <nav class="center-nav">
                <a href="../index.php">Dashboard</a>
                <a href="renter-manage-account.php">Manage Account</a>
                <a href="../logout.php">Logout</a>
            </nav>

            <!-- Spacer to keep center nav centered -->
            <div style="width:120px;"></div>
        </header>

        <main>
            <div class="wrap">
                <h1 class="page-title">Renter Dashboard</h1>

                <div class="panel">
                    <h3>Account Summary</h3>
                    <?php if ($error): ?>
                        <p style="color:#b91c1c; margin-top:0;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <ul>
                        <li><strong>Renter Name:</strong> <?php echo htmlspecialchars($renter["name"]); ?></li>
                        <li><strong>Unit Number:</strong> <?php echo htmlspecialchars($renter["unit"]); ?></li>
                        <li><strong>Lease Status:</strong> <?php echo htmlspecialchars($renter["lease_status"]); ?></li>
                        <li><strong>Account Balance:</strong> <?php echo htmlspecialchars($renter["balance"]); ?></li>
                    </ul>

                    <div class="btn-row">
                        <a class="btn" href="renter-manage-account.php">Manage Account</a>
                        <a class="btn" href="maintenance.php">Maintenance</a>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            &copy; <?php echo date("Y"); ?> Cowboy Properties
        </footer>

    </div>
</body>

</html>