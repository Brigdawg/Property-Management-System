<?php
require_once __DIR__ . '/../auth_check.php';
require_renter('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$renter = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'unit' => 'N/A',
    'lease_start' => 'N/A',
    'lease_end' => 'N/A',
    'rent' => '$0.00',
];

$payments = [];
$error = '';
$paymentSuccess = isset($_GET['payment']) && $_GET['payment'] === 'success';

try {
    $pdo = get_db();

    $renterId = (int)($_SESSION['user_id'] ?? $_GET['renter_id'] ?? 0);
    if ($renterId <= 0) {
        $fallbackStmt = $pdo->query("SELECT RenterID FROM renter ORDER BY RenterID ASC LIMIT 1");
        $renterId = (int)$fallbackStmt->fetchColumn();
    }

    if ($renterId > 0) {
        $infoStmt = $pdo->prepare(" 
            SELECT
                r.RenterID,
                r.Firstname,
                r.Lastname,
                r.email,
                r.phone,
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
        $infoStmt->execute([':renter_id' => $renterId]);
        $info = $infoStmt->fetch();

        if ($info) {
            $renter['first_name'] = (string)($info['Firstname'] ?? '');
            $renter['last_name'] = (string)($info['Lastname'] ?? '');
            $renter['email'] = (string)($info['email'] ?? '');
            $renter['phone'] = (string)($info['phone'] ?? '');
            $renter['unit'] = (string)($info['Unit_number'] ?? 'N/A');
            $renter['lease_start'] = 'N/A';
            $renter['lease_end'] = !empty($info['period']) ? ((string)$info['period'] . ' months') : 'N/A';
            $renter['rent'] = '$' . number_format((float)($info['Price'] ?? 0), 2);
        }

        $paymentsStmt = $pdo->prepare(" 
            SELECT
                date,
                amount,
                period
            FROM payment
            WHERE RenterID = :renter_id
            ORDER BY date DESC
        ");
        $paymentsStmt->execute([':renter_id' => $renterId]);

        while ($row = $paymentsStmt->fetch()) {
            $payments[] = [
                'date' => (string)($row['date'] ?? ''),
                'amount' => '$' . number_format((float)($row['amount'] ?? 0), 2),
                'method' => 'N/A',
                'status' => !empty($row['period']) ? ('Posted (' . (string)$row['period'] . ')') : 'Posted',
            ];
        }
    }
} catch (Throwable $e) {
    $error = 'Unable to load account details right now.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Manage Account</title>
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

        /* Centered dashboard panel */
        .panel {
            margin: 0 auto;
            border: 1px solid var(--border);
            border-radius: 24px;
            background: #fff;
            padding: 26px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
            width: min(980px, 100%);
            text-align: left;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 18px;
            align-items: start;
        }

        .stack {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 16px 16px;
            background: #fff;
        }

        .card h3 {
            margin: 0 0 10px;
            font-size: 16px;
            text-align: center;
        }

        .card ul {
            margin: 0;
            padding-left: 18px;
            line-height: 1.9;
            color: var(--text);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid var(--border);
            padding: 10px 10px;
            text-align: left;
        }

        th {
            background: var(--soft);
            font-weight: 800;
            color: var(--text);
        }

        .btn-row {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 20px;
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
        }

        .btn:hover {
            background: var(--brand-dark);
            transform: translateY(-1px);
        }

        footer {
            border-top: 1px solid var(--border);
            padding: 14px 20px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .panel {
                padding: 18px;
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
                <a href="../index.php">Dashboard</a>
                <a href="renter-manage-account.php">Manage Account</a>
                <a href="../logout.php">Logout</a>
            </nav>

            <!-- Spacer to keep center-nav centered -->
            <div style="width:120px;"></div>
        </header>

        <main>
            <div class="wrap">
                <h1 class="page-title">Manage Account</h1>

                <section class="panel" aria-label="Manage account panel">
                    <?php if ($paymentSuccess): ?>
                        <p style="color:#166534; margin-top:0; margin-bottom:14px;">Payment submitted successfully.</p>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <p style="color:#b91c1c; margin-top:0; margin-bottom:14px;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <div class="grid">
                        <!-- Left side: Personal + Lease -->
                        <div class="stack">
                            <div class="card" aria-label="Personal Information">
                                <h3>Personal Information</h3>
                                <ul>
                                    <li><strong>First Name:</strong> <?php echo htmlspecialchars($renter["first_name"]); ?></li>
                                    <li><strong>Last Name:</strong> <?php echo htmlspecialchars($renter["last_name"]); ?></li>
                                    <li><strong>Email:</strong> <?php echo htmlspecialchars($renter["email"]); ?></li>
                                    <li><strong>Phone:</strong> <?php echo htmlspecialchars($renter["phone"]); ?></li>
                                </ul>
                            </div>

                            <div class="card" aria-label="Lease Information">
                                <h3>Lease Information</h3>
                                <ul>
                                    <li><strong>Unit Number:</strong> <?php echo htmlspecialchars($renter["unit"]); ?></li>
                                    <li><strong>Lease Start Date:</strong> <?php echo htmlspecialchars($renter["lease_start"]); ?></li>
                                    <li><strong>Lease End Date:</strong> <?php echo htmlspecialchars($renter["lease_end"]); ?></li>
                                    <li><strong>Rent Amount:</strong> <?php echo htmlspecialchars($renter["rent"]); ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Right side: Payment History -->
                        <div class="card" aria-label="Payment History">
                            <h3>Payment History</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$payments): ?>
                                        <tr>
                                            <td colspan="4">No payment history yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($payments as $p): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($p["date"]); ?></td>
                                                <td><?php echo htmlspecialchars($p["amount"]); ?></td>
                                                <td><?php echo htmlspecialchars($p["method"]); ?></td>
                                                <td><?php echo htmlspecialchars($p["status"]); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="btn-row">
                        <a class="btn" href="renter-edit-info.php">Edit Information</a>
                        <a class="btn" href="renter-make-payment.php">Make Payment</a>
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