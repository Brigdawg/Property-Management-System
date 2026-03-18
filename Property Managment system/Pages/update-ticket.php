<?php
require_once __DIR__ . '/../auth_check.php';
require_login('login.php');
require_once __DIR__ . '/../db.php';

$user = current_user();

$error = '';
$success = '';
$ticket = null;

$values = [
    'location' => '',
    'issue_type' => '',
    'description' => '',
    'status' => '',
];

$ticketId = trim($_GET['id'] ?? $_POST['ticket_id'] ?? '');

if ($ticketId === '') {
    die('Ticket ID missing.');
}

try {
    $pdo = get_db();

    $stmt = $pdo->prepare("
        SELECT
            m.TicketID,
            m.UnitID,
            m.RenterID,
            m.EmpID,
            m.Date,
            m.Issue,
            m.Status,
            r.Firstname,
            r.Lastname,
            u.Unit_number,
            p.Address,
            p.ManagerEmpID
        FROM maintenance m
        LEFT JOIN renter r ON r.RenterID = m.RenterID
        LEFT JOIN unit u ON u.UnitID = m.UnitID
        LEFT JOIN property p ON p.PropertyID = u.PropertyID
        WHERE m.TicketID = ?
        LIMIT 1
    ");
    $stmt->execute([$ticketId]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        throw new RuntimeException('Ticket not found.');
    }

    /*
      Parse stored Issue text format:
      "Bathroom | Plumbing | sink leaking"
      Fallback to raw description if not in that format.
    */
    $issueText = (string)($ticket['Issue'] ?? '');
    $parts = array_map('trim', explode('|', $issueText, 3));

    if (count($parts) === 3) {
        $values['location'] = strtolower(str_replace(' ', '_', $parts[0]));
        $values['issue_type'] = strtolower($parts[1]);
        $values['description'] = $parts[2];
    } else {
        $values['description'] = $issueText;
    }

    $values['status'] = (string)($ticket['Status'] ?? 'Open');

} catch (Throwable $e) {
    $error = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    if ($user['role'] !== 'employee') {
        $error = 'You do not have permission to update tickets.';
    } else {
        $values['location'] = trim($_POST['location'] ?? '');
        $values['issue_type'] = trim($_POST['issue_type'] ?? '');
        $values['description'] = trim($_POST['description'] ?? '');
        $values['status'] = trim($_POST['status'] ?? '');

        if (
            $values['location'] === '' ||
            $values['issue_type'] === '' ||
            $values['description'] === '' ||
            $values['status'] === ''
        ) {
            $error = 'Please fill in all required fields.';
        } elseif (!in_array($values['status'], ['Open', 'In Progress', 'Closed'], true)) {
            $error = 'Invalid status selected.';
        } else {
            try {
                $pdo = get_db();

                $issueText =
                    ucfirst(str_replace('_', ' ', $values['location'])) . ' | ' .
                    ucfirst($values['issue_type']) . ' | ' .
                    $values['description'];

                $stmt = $pdo->prepare("
                    UPDATE maintenance
                    SET Issue = ?, Status = ?, EmpID = ?
                    WHERE TicketID = ?
                ");
                $stmt->execute([
                    $issueText,
                    $values['status'],
                    $user['id'] ?? $ticket['EmpID'] ?? null,
                    $ticketId
                ]);

                $success = 'Ticket updated successfully.';

                // refresh ticket display values
                $ticket['Issue'] = $issueText;
                $ticket['Status'] = $values['status'];

            } catch (Throwable $e) {
                $error = 'Could not update ticket: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Update Ticket';
$homeLink = $user['role'] === 'employee' ? 'adminhome.php' : 'renter-dashboard.php';

$navLinks = [
    ['label' => 'Dashboard', 'href' => $homeLink]
];

if ($user['role'] !== 'employee') {
    $navLinks[] = ['label' => 'Manage Account', 'href' => 'renter-manage-account.php'];
}

$navLinks[] = ['label' => 'Logout', 'href' => '../logout.php'];

require_once __DIR__ . '/partials/app-header.php';
?>

<h1 class="page-title">Update Ticket</h1>

<div class="panel" style="max-width: 900px; margin: 0 auto;">
    <?php if ($error): ?>
        <p class="message-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="message-success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <?php if ($ticket): ?>
        <form method="post" action="update-ticket.php?id=<?php echo urlencode((string)$ticket['TicketID']); ?>">
            <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars((string)$ticket['TicketID']); ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label>Firstname</label>
                    <input type="text"
                           value="<?php echo htmlspecialchars((string)($ticket['Firstname'] ?? '')); ?>"
                           readonly>
                </div>

                <div class="form-group">
                    <label>Lastname</label>
                    <input type="text"
                           value="<?php echo htmlspecialchars((string)($ticket['Lastname'] ?? '')); ?>"
                           readonly>
                </div>

                <div class="form-group">
                    <label>Unit</label>
                    <input type="text"
                           value="<?php echo htmlspecialchars((string)($ticket['Unit_number'] ?? '')); ?>"
                           readonly>
                </div>

                <div class="form-group">
                    <label>Property</label>
                    <input type="text"
                           value="<?php echo htmlspecialchars((string)($ticket['Address'] ?? '')); ?>"
                           readonly>
                </div>

                <div class="form-group">
                    <label>Location of issue</label>
                    <select name="location" <?php echo $user['role'] === 'employee' ? '' : 'disabled'; ?> required>
                        <option value="">-- Select --</option>
                        <option value="bathroom" <?php echo $values['location'] === 'bathroom' ? 'selected' : ''; ?>>Bathroom</option>
                        <option value="kitchen" <?php echo $values['location'] === 'kitchen' ? 'selected' : ''; ?>>Kitchen</option>
                        <option value="living_room" <?php echo $values['location'] === 'living_room' ? 'selected' : ''; ?>>Living Room</option>
                        <option value="bedroom" <?php echo $values['location'] === 'bedroom' ? 'selected' : ''; ?>>Bedroom</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Type of issue</label>
                    <select name="issue_type" <?php echo $user['role'] === 'employee' ? '' : 'disabled'; ?> required>
                        <option value="">-- Select --</option>
                        <option value="general" <?php echo $values['issue_type'] === 'general' ? 'selected' : ''; ?>>General</option>
                        <option value="plumbing" <?php echo $values['issue_type'] === 'plumbing' ? 'selected' : ''; ?>>Plumbing</option>
                        <option value="lighting" <?php echo $values['issue_type'] === 'lighting' ? 'selected' : ''; ?>>Lighting</option>
                        <option value="other" <?php echo $values['issue_type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" <?php echo $user['role'] === 'employee' ? '' : 'disabled'; ?> required>
                        <option value="Open" <?php echo $values['status'] === 'Open' ? 'selected' : ''; ?>>Open</option>
                        <option value="In Progress" <?php echo $values['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Closed" <?php echo $values['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label>Detailed Description of issue</label>
                <textarea name="description" rows="8" <?php echo $user['role'] === 'employee' ? '' : 'readonly'; ?> required><?php echo htmlspecialchars($values['description']); ?></textarea>
            </div>

            <div class="btn-row" style="margin-top: 24px;">
                <?php if ($user['role'] === 'employee'): ?>
                    <button class="btn" type="submit">Save Changes</button>
                <?php endif; ?>

                <a class="btn btn-secondary" href="ticket-view.php?id=<?php echo urlencode((string)$ticket['TicketID']); ?>">View Ticket</a>
                <a class="btn btn-secondary" href="history.php">Back</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/app-footer.php'; ?>