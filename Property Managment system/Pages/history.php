<?php
require_once __DIR__ . '/../auth_check.php';
require_login('login.php');
require_once __DIR__ . '/../db.php';

$user = current_user();

function pick_col(array $columns, array $candidates): ?string
{
    $lookup = [];
    foreach ($columns as $col) {
        $lookup[strtolower((string)$col)] = (string)$col;
    }

    foreach ($candidates as $name) {
        $key = strtolower((string)$name);
        if (isset($lookup[$key])) {
            return $lookup[$key];
        }
    }
    return null;
}

function qid(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

$error = '';
$success = '';
$tickets = [];

try {
    $pdo = get_db();

    $schema = $pdo->query('SHOW COLUMNS FROM maintenance')->fetchAll(PDO::FETCH_ASSOC);
    $columns = array_map(static fn($row) => (string)$row['Field'], $schema);

    $idCol = pick_col($columns, ['MaintenanceID', 'maintenance_id', 'TicketID', 'ticket_id', 'ticket_number', 'TicketNumber', 'id']);
    $statusCol = pick_col($columns, ['Status', 'status', 'ticket_status', 'TicketStatus']);
    $createdCol = pick_col($columns, ['created_at', 'date_submitted', 'submitted_at', 'CreatedAt', 'DateSubmitted', 'Date']);
    $updatedCol = pick_col($columns, ['updated_at', 'last_updated', 'UpdatedAt', 'LastUpdated']);
    $issueCol = pick_col($columns, ['Issue', 'issue', 'description', 'Description']);

    if (!$idCol) {
        throw new RuntimeException('Could not determine ticket ID column.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['role'] === 'employee') {
        $ticketId = trim($_POST['ticket_id'] ?? '');
        $action = trim($_POST['action'] ?? '');

        if ($ticketId === '') {
            throw new RuntimeException('Missing ticket ID.');
        }

        if ($action === 'update_status') {
            if (!$statusCol) {
                throw new RuntimeException('Status column not found.');
            }

            $newStatus = trim($_POST['status'] ?? '');
            if (!in_array($newStatus, ['Open', 'In Progress', 'Closed'], true)) {
                throw new RuntimeException('Invalid status selected.');
            }

            $sql = 'UPDATE maintenance SET ' . qid($statusCol) . ' = ? WHERE ' . qid($idCol) . ' = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$newStatus, $ticketId]);

            $success = "Ticket #{$ticketId} updated successfully.";
        }

        if ($action === 'delete_ticket') {
            $sql = 'DELETE FROM maintenance WHERE ' . qid($idCol) . ' = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ticketId]);

            $success = "Ticket #{$ticketId} deleted successfully.";
        }
    }

    $select = [];
    $select[] = qid($idCol) . ' AS ticket_id';
    $select[] = $statusCol ? qid($statusCol) . ' AS status' : 'NULL AS status';
    $select[] = $createdCol ? qid($createdCol) . ' AS submitted_at' : 'NULL AS submitted_at';
    $select[] = $updatedCol ? qid($updatedCol) . ' AS updated_at' : 'NULL AS updated_at';
    $select[] = $issueCol ? qid($issueCol) . ' AS issue_text' : 'NULL AS issue_text';

    $sql = 'SELECT ' . implode(', ', $select) . ' FROM maintenance';

    if ($statusCol) {
        $sql .= ' WHERE LOWER(' . qid($statusCol) . ') IN (?, ?, ?)';
    }

    if ($createdCol) {
        $sql .= ' ORDER BY ' . qid($createdCol) . ' ASC';
    } else {
        $sql .= ' ORDER BY ' . qid($idCol) . ' ASC';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['closed', 'completed', 'resolved']);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $error = 'Could not load ticket history: ' . $e->getMessage();
}

$pageTitle = 'Ticket History';
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

<h1 class="page-title">Ticket History</h1>

<div class="panel history-panel">

    <?php if ($success): ?>
        <p class="message-success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="message-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <style>
        .history-panel {
            max-width: 1500px;
            width: 96%;
            margin: 0 auto;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        .history-table th,
        .history-table td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: middle;
        }

        .history-table th {
            background: var(--soft);
            font-weight: 700;
            font-size: 13px;
            white-space: nowrap;
        }

        .history-table td {
            font-size: 14px;
        }

        .history-table th:nth-child(1),
        .history-table td:nth-child(1) {
            width: 110px;
            white-space: nowrap;
        }

        .history-table th:nth-child(2),
        .history-table td:nth-child(2) {
            width: 150px;
            white-space: nowrap;
        }

        .history-table th:nth-child(3),
        .history-table td:nth-child(3) {
            width: 130px;
            white-space: nowrap;
        }

        .history-table th:nth-child(4),
        .history-table td:nth-child(4) {
            width: auto;
        }

        .history-table th:nth-child(5),
        .history-table td:nth-child(5) {
            width: 110px;
            text-align: center;
        }

        .history-table th:nth-child(6),
        .history-table td:nth-child(6) {
            width: 220px;
        }

        .history-table th:nth-child(7),
        .history-table td:nth-child(7) {
            width: 120px;
            text-align: center;
        }

        .mini-form {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .mini-form select {
            padding: 8px 10px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
        }

        .mini-btn {
            padding: 7px 12px;
            font-size: 13px;
            min-width: 72px;
            white-space: nowrap;
        }
    </style>

    <div class="table-wrap">
        <table class="history-table">
            <tr>
                <th>Ticket Number</th>
                <th>Date Submitted</th>
                <th>Status</th>
                <th>Issue</th>
                <th>View</th>
                <?php if ($user['role'] === 'employee'): ?>
                    <th>Update Status</th>
                    <th>Delete</th>
                <?php endif; ?>
            </tr>

            <?php if (!$tickets): ?>
                <tr>
                    <td colspan="<?php echo $user['role'] === 'employee' ? '7' : '5'; ?>" class="empty-state">
                        No closed tickets found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <?php
                    $status = (string)($ticket['status'] ?? '-');
                    $statusClass = 'status-closed';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string)($ticket['ticket_id'] ?? '-')); ?></td>
                        <td><?php echo htmlspecialchars((string)($ticket['submitted_at'] ?? '-')); ?></td>
                        <td>
                            <span class="<?php echo htmlspecialchars($statusClass); ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars((string)($ticket['issue_text'] ?? '-')); ?></td>
                        <td>
                            <a class="btn btn-secondary mini-btn" href="ticket-view.php?id=<?php echo urlencode((string)($ticket['ticket_id'] ?? '')); ?>">
                                View
                            </a>
                        </td>

                        <?php if ($user['role'] === 'employee'): ?>
                            <td>
                                <form method="post" class="mini-form">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars((string)$ticket['ticket_id']); ?>">

                                    <select name="status">
                                        <option value="Open" <?php if ($status === 'Open') echo 'selected'; ?>>Open</option>
                                        <option value="In Progress" <?php if ($status === 'In Progress') echo 'selected'; ?>>In Progress</option>
                                        <option value="Closed" <?php if ($status === 'Closed') echo 'selected'; ?>>Closed</option>
                                    </select>

                                    <button class="btn mini-btn" type="submit">Save</button>
                                </form>
                            </td>

                            <td>
                                <form method="post" onsubmit="return confirm('Delete this ticket?');">
                                    <input type="hidden" name="action" value="delete_ticket">
                                    <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars((string)$ticket['ticket_id']); ?>">
                                    <button class="btn btn-danger mini-btn" type="submit">Delete</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

    <div class="btn-row" style="margin-top: 28px;">
        <a class="btn btn-secondary" href="maintenance.php">Back</a>
    </div>
</div>

<?php require_once __DIR__ . '/partials/app-footer.php'; ?>