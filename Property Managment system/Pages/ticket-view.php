<?php
require_once __DIR__ . '/../auth_check.php';
require_login('login.php');
require_once __DIR__ . '/../db.php';

$user = current_user();

$ticketId = $_GET['id'] ?? null;

if (!$ticketId) {
    die("Ticket ID missing.");
}

$error = '';
$success = '';
$ticket = null;

try {
    $pdo = get_db();

    $stmt = $pdo->prepare("
        SELECT *
        FROM maintenance
        WHERE TicketID = ?
    ");
    $stmt->execute([$ticketId]);

    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        die("Ticket not found.");
    }

    // Only employees/admins can update
    if (isset($_POST['update_status']) && $user['role'] === 'employee') {
        $newStatus = trim($_POST['status'] ?? '');

        if (!in_array($newStatus, ['Open', 'In Progress', 'Closed'], true)) {
            throw new RuntimeException('Invalid status selected.');
        }

        $stmt = $pdo->prepare("
            UPDATE maintenance
            SET Status = ?
            WHERE TicketID = ?
        ");
        $stmt->execute([$newStatus, $ticketId]);

        $success = "Ticket status updated.";
        $ticket['Status'] = $newStatus;
    }

    // Only employees/admins can delete
    if (isset($_POST['delete_ticket']) && $user['role'] === 'employee') {
        $stmt = $pdo->prepare("
            DELETE FROM maintenance
            WHERE TicketID = ?
        ");
        $stmt->execute([$ticketId]);

        header("Location: active.php");
        exit;
    }

} catch (Throwable $e) {
    $error = "Could not load ticket: " . $e->getMessage();
}

$pageTitle = "View Ticket";

$homeLink = $user['role'] === 'employee'
    ? 'adminhome.php'
    : 'renter-dashboard.php';

$navLinks = [
    ['label' => 'Dashboard', 'href' => $homeLink]
];

if ($user['role'] !== 'employee') {
    $navLinks[] = ['label' => 'Manage Account', 'href' => 'renter-manage-account.php'];
}

$navLinks[] = ['label' => 'Logout', 'href' => '../logout.php'];

require_once __DIR__ . '/partials/app-header.php';
?>

<h1 class="page-title">Ticket #<?php echo htmlspecialchars((string)$ticket['TicketID']); ?></h1>

<div class="panel panel-sm">

    <?php if ($error): ?>
        <p class="message-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="message-success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <?php if ($ticket): ?>
        <div style="text-align:left; line-height:1.8;">
            <p><strong>Ticket Number:</strong> <?php echo htmlspecialchars((string)($ticket['TicketID'] ?? '-')); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars((string)($ticket['Status'] ?? '-')); ?></p>
            <p><strong>Date Submitted:</strong> <?php echo htmlspecialchars((string)($ticket['Date'] ?? '-')); ?></p>
            <p><strong>Issue:</strong><br><?php echo nl2br(htmlspecialchars((string)($ticket['Issue'] ?? ''))); ?></p>
            <p><strong>Unit ID:</strong> <?php echo htmlspecialchars((string)($ticket['UnitID'] ?? '-')); ?></p>
            <p><strong>Renter ID:</strong> <?php echo htmlspecialchars((string)($ticket['RenterID'] ?? '-')); ?></p>
            <p><strong>Employee ID:</strong> <?php echo htmlspecialchars((string)($ticket['EmpID'] ?? '-')); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($user['role'] === 'employee' && $ticket): ?>
        <hr style="margin: 24px 0;">

        <h3>Update Ticket</h3>

        <form method="post" style="margin-bottom: 20px;">
            <select name="status">
                <option value="Open" <?php if (($ticket['Status'] ?? '') === 'Open') echo 'selected'; ?>>Open</option>
                <option value="In Progress" <?php if (($ticket['Status'] ?? '') === 'In Progress') echo 'selected'; ?>>In Progress</option>
                <option value="Closed" <?php if (($ticket['Status'] ?? '') === 'Closed') echo 'selected'; ?>>Closed</option>
            </select>

            <button class="btn" type="submit" name="update_status">Update Status</button>
        </form>

        <form method="post" onsubmit="return confirm('Delete this ticket?');">
            <button class="btn btn-danger" type="submit" name="delete_ticket">Delete Ticket</button>
        </form>
    <?php endif; ?>

    <div class="btn-row" style="margin-top:30px;">
        <a class="btn btn-secondary" href="active.php">Back</a>
    </div>

</div>

<?php require_once __DIR__ . '/partials/app-footer.php'; ?>