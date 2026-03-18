<?php
require_once __DIR__ . '/../auth_check.php';
require_renter('../unauthorized.php');
require_once __DIR__ . '/../db.php';

$default_amount = '0.00';
$error = '';

try {
    $pdo = get_db();

    $renterId = (int)($_SESSION['user_id'] ?? $_GET['renter_id'] ?? $_POST['renter_id'] ?? 0);
    if ($renterId <= 0) {
        $fallbackStmt = $pdo->query("SELECT RenterID FROM renter ORDER BY RenterID ASC LIMIT 1");
        $renterId = (int)$fallbackStmt->fetchColumn();
    }

    $leaseStmt = $pdo->prepare(" 
        SELECT LeaseID, Price, EmpID
        FROM lease
        WHERE RenterID = :renter_id
        ORDER BY LeaseID DESC
        LIMIT 1
    ");
    $leaseStmt->execute([':renter_id' => $renterId]);
    $lease = $leaseStmt->fetch();

    if (!$lease) {
        $error = 'No active lease was found for this renter.';
    } else {
        $default_amount = number_format((float)$lease['Price'], 2, '.', '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amountRaw = trim((string)($_POST['amount'] ?? ''));
            $methodInput = trim((string)($_POST['method'] ?? ''));
            $cardInput = trim((string)($_POST['card'] ?? ''));

            $amount = (float)$amountRaw;

            $methodMap = [
                'credit' => 'Credit Card',
                'debit' => 'Debit Card',
                'bank' => 'Bank Transfer',
                'Credit Card' => 'Credit Card',
                'Debit Card' => 'Debit Card',
                'Bank Transfer' => 'Bank Transfer',
            ];
            $method = $methodMap[$methodInput] ?? '';

            $digitsOnly = preg_replace('/\D+/', '', $cardInput ?? '');
            $last4 = $digitsOnly !== '' ? substr($digitsOnly, -4) : null;

            if ($amount <= 0) {
                $error = 'Please enter a valid payment amount.';
            } elseif ($method === '') {
                $error = 'Please choose a valid payment method.';
            } else {
                $paymentPeriod = (int)date('Ym');
                $insertStmt = $pdo->prepare(" 
                    INSERT INTO payment (RenterID, LeaseID, EmpID, period, date, amount)
                    VALUES (:renter_id, :lease_id, :emp_id, :period, :payment_date, :amount)
                ");

                $insertStmt->execute([
                    ':renter_id' => $renterId,
                    ':lease_id' => (int)$lease['LeaseID'],
                    ':emp_id' => (int)$lease['EmpID'],
                    ':period' => $paymentPeriod,
                    ':payment_date' => date('Y-m-d'),
                    ':amount' => $amount,
                ]);

                header('Location: renter-manage-account.php?payment=success&renter_id=' . $renterId);
                exit;
            }
        }
    }
} catch (Throwable $e) {
    $error = 'Unable to submit payment right now.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cowboy Properties | Make a Payment</title>
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
            grid-template-columns: 110px 1fr;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        label {
            font-size: 15px;
            font-weight: 800;
            color: var(--text);
        }

        input,
        select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 14px;
            outline: none;
            background: #fff;
        }

        input:focus,
        select:focus {
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
                <a href="../index.php">Dashboard</a>
                <a href="renter-manage-account.php">Manage Account</a>
                <a href="../logout.php">Logout</a>
            </nav>

            <!-- Spacer to keep center-nav centered -->
            <div style="width:120px;"></div>
        </header>

        <main>
            <div class="wrap">
                <h1 class="page-title">Make a Payment</h1>

                <section class="panel" aria-label="Make payment panel">
                    <div class="card" aria-label="Make payment form">
                        <h3>Make Payment</h3>

                        <?php if ($error): ?>
                            <p style="color:#b91c1c; margin-top:0;"><?php echo htmlspecialchars($error); ?></p>
                        <?php endif; ?>

                        <form id="paymentForm" action="" method="POST">
                            <input type="hidden" name="renter_id" value="<?php echo htmlspecialchars((string)($_GET['renter_id'] ?? $_POST['renter_id'] ?? '')); ?>" />
                            <div class="field">
                                <label for="amount">Amount:</label>
                                <input id="amount" name="amount" type="number" min="0.01" step="0.01" value="<?php echo htmlspecialchars((string)($_POST['amount'] ?? $default_amount)); ?>" />
                            </div>

                            <div class="field">
                                <label for="method">Method:</label>
                                <select id="method" name="method">
                                    <option value="credit" <?php echo (($_POST['method'] ?? '') === 'credit') ? 'selected' : ''; ?>>Credit Card</option>
                                    <option value="debit" <?php echo (($_POST['method'] ?? '') === 'debit') ? 'selected' : ''; ?>>Debit Card</option>
                                    <option value="bank" <?php echo (($_POST['method'] ?? '') === 'bank') ? 'selected' : ''; ?>>Bank Transfer</option>
                                </select>
                            </div>

                            <div class="field" style="margin-bottom:0;">
                                <label for="card">Card #:</label>
                                <input id="card" name="card" type="text" value="<?php echo htmlspecialchars((string)($_POST['card'] ?? '')); ?>" placeholder="1234 5678 9012 3456" />
                            </div>

                            <div class="btn-row">
                                <button class="btn" type="submit">Submit Payment</button>
                                <a class="btn btn-outline" href="renter-manage-account.php">Cancel</a>
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