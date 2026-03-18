<?php
require_once __DIR__ . '/../auth_check.php';
require_login('login.php');
require_once __DIR__ . '/../db.php';

$user = current_user();

$error = '';
$success = '';
$renterInfo = null;

$values = [
    'location' => '',
    'issue_type' => '',
    'description' => '',
];

try {
    $pdo = get_db();

    $userId = (int)($user['id'] ?? 0);
    $userEmail = trim((string)($user['email'] ?? ''));

    /*
      Try to resolve the logged-in renter.
      1) First by renter ID
      2) If that fails, try by email
    */
    $renterStmt = null;
    $renter = false;

    if ($userId > 0) {
        $renterStmt = $pdo->prepare(" 
            SELECT RenterID, Firstname, Lastname, email
            FROM renter
            WHERE RenterID = ?
            LIMIT 1
        ");
        $renterStmt->execute([$userId]);
        $renter = $renterStmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$renter && $userEmail !== '') {
        $renterStmt = $pdo->prepare(" 
            SELECT RenterID, Firstname, Lastname, email
            FROM renter
            WHERE email = ?
            LIMIT 1
        ");
        $renterStmt->execute([$userEmail]);
        $renter = $renterStmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$renter) {
        throw new RuntimeException('Could not identify the logged-in renter.');
    }

    $renterId = (int)$renter['RenterID'];

    /*
      Pull the renter's most recent lease / unit / property info
    */
    $infoStmt = $pdo->prepare(" 
        SELECT
            r.RenterID,
            r.Firstname,
            r.Lastname,
            u.UnitID,
            u.Unit_number,
            p.PropertyID,
            p.Address,
            p.ManagerEmpID
        FROM renter r
        LEFT JOIN lease l
            ON l.RenterID = r.RenterID
        LEFT JOIN unit u
            ON u.UnitID = l.UnitID
        LEFT JOIN property p
            ON p.PropertyID = u.PropertyID
        WHERE r.RenterID = ?
        ORDER BY l.LeaseID DESC
        LIMIT 1
    ");
    $infoStmt->execute([$renterId]);
    $renterInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);

    if (!$renterInfo) {
        throw new RuntimeException('Could not load renter account information.');
    }

    if (empty($renterInfo['UnitID'])) {
        throw new RuntimeException('No unit is linked to this renter account.');
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

/*
  Handle form submit
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $values['location'] = trim($_POST['location'] ?? '');
    $values['issue_type'] = trim($_POST['issue_type'] ?? '');
    $values['description'] = trim($_POST['description'] ?? '');

    if (
        $values['location'] === '' ||
        $values['issue_type'] === '' ||
        $values['description'] === ''
    ) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $pdo = get_db();

            $unitId = (int)$renterInfo['UnitID'];
            $renterId = (int)$renterInfo['RenterID'];
            $empId = !empty($renterInfo['ManagerEmpID']) ? (int)$renterInfo['ManagerEmpID'] : null;

            $issueText =
                ucfirst(str_replace('_', ' ', $values['location'])) . ' | ' .
                ucfirst($values['issue_type']) . ' | ' .
                $values['description'];

            if ($empId > 0) {
                $insert = $pdo->prepare(" 
                    INSERT INTO maintenance (UnitID, RenterID, EmpID, Date, Issue, Status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insert->execute([
                    $unitId,
                    $renterId,
                    $empId,
                    date('Y-m-d'),
                    $issueText,
                    'Open'
                ]);
            } else {
                $insert = $pdo->prepare(" 
                    INSERT INTO maintenance (UnitID, RenterID, EmpID, Date, Issue, Status)
                    VALUES (?, ?, NULL, ?, ?, ?)
                ");
                $insert->execute([
                    $unitId,
                    $renterId,
                    date('Y-m-d'),
                    $issueText,
                    'Open'
                ]);
            }

            header('Location: active.php?created=1');
            exit;
        } catch (Throwable $e) {
            $error = 'Could not create ticket: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Create Ticket';
$homeLink = $user['role'] === 'employee' ? 'adminhome.php' : 'renter-dashboard.php';

$navLinks = [
    [
        'label' => 'Dashboard',
        'href' => $homeLink
    ],
];

if ($user['role'] !== 'employee') {
    $navLinks[] = [
        'label' => 'Manage Account',
        'href' => 'renter-manage-account.php'
    ];
}

$navLinks[] = [
    'label' => 'Logout',
    'href' => '../logout.php'
];

require_once __DIR__ . '/partials/app-header.php';
?>

<h1 class="page-title">Create A Ticket</h1>

<div class="panel" style="max-width: 900px; margin: 0 auto;">
    <?php if ($error): ?>
        <p class="message-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($renterInfo): ?>
        <form action="create.php" method="post">

            <div class="form-grid">
                <div class="form-group">
                    <label>Firstname</label>
                    <input type="text"
                        value="<?php echo htmlspecialchars((string)($renterInfo['Firstname'] ?? '')); ?>"
                        readonly>
                </div>

                <div class="form-group">
                    <label>Lastname</label>
                    <input type="text"
                        value="<?php echo htmlspecialchars((string)($renterInfo['Lastname'] ?? '')); ?>"
                        readonly>
                </div>

                <div class="form-group">
                    <label>Unit</label>
                    <input type="text"
                        value="<?php echo htmlspecialchars((string)($renterInfo['Unit_number'] ?? '')); ?>"
                        readonly>
                </div>

                <div class="form-group">
                    <label>Property</label>
                    <input type="text"
                        value="<?php echo htmlspecialchars((string)($renterInfo['Address'] ?? '')); ?>"
                        readonly>
                </div>

                <div class="form-group">
                    <label>Location of issue</label>
                    <select name="location" required>
                        <option value="">-- Select --</option>
                        <option value="bathroom" <?php echo $values['location'] === 'bathroom' ? 'selected' : ''; ?>>Bathroom</option>
                        <option value="kitchen" <?php echo $values['location'] === 'kitchen' ? 'selected' : ''; ?>>Kitchen</option>
                        <option value="living_room" <?php echo $values['location'] === 'living_room' ? 'selected' : ''; ?>>Living Room</option>
                        <option value="bedroom" <?php echo $values['location'] === 'bedroom' ? 'selected' : ''; ?>>Bedroom</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Type of issue</label>
                    <select name="issue_type" required>
                        <option value="">-- Select --</option>
                        <option value="general" <?php echo $values['issue_type'] === 'general' ? 'selected' : ''; ?>>General</option>
                        <option value="plumbing" <?php echo $values['issue_type'] === 'plumbing' ? 'selected' : ''; ?>>Plumbing</option>
                        <option value="lighting" <?php echo $values['issue_type'] === 'lighting' ? 'selected' : ''; ?>>Lighting</option>
                        <option value="other" <?php echo $values['issue_type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label>Detailed Description of issue</label>
                <textarea name="description" rows="8" required><?php echo htmlspecialchars($values['description']); ?></textarea>
            </div>

            <div class="btn-row" style="margin-top: 24px;">
                <button class="btn" type="submit">Submit</button>
                <a class="btn btn-secondary" href="maintenance.php">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/app-footer.php'; ?>