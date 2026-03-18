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
$created = isset($_GET['created']);
$openTickets = [];
$progressTickets = [];

try {
  $pdo = get_db();

  $schema = $pdo->query('SHOW COLUMNS FROM maintenance')->fetchAll(PDO::FETCH_ASSOC);
  $columns = array_map(static fn($row) => (string)$row['Field'], $schema);

  $idCol = pick_col($columns, ['MaintenanceID', 'maintenance_id', 'TicketID', 'ticket_id', 'ticket_number', 'TicketNumber', 'id']);
  $statusCol = pick_col($columns, ['Status', 'status', 'ticket_status', 'TicketStatus']);
  $createdCol = pick_col($columns, ['created_at', 'date_submitted', 'submitted_at', 'CreatedAt', 'DateSubmitted', 'Date']);
  $updatedCol = pick_col($columns, ['updated_at', 'last_updated', 'UpdatedAt', 'LastUpdated']);
  $issueCol = pick_col($columns, ['Issue', 'issue', 'description', 'Description']);

  $select = [];
  $select[] = $idCol ? qid($idCol) . ' AS ticket_id' : 'NULL AS ticket_id';
  $select[] = $createdCol ? qid($createdCol) . ' AS submitted_at' : 'NULL AS submitted_at';
  $select[] = $updatedCol ? qid($updatedCol) . ' AS updated_at' : 'NULL AS updated_at';
  $select[] = $statusCol ? qid($statusCol) . ' AS status' : 'NULL AS status';
  $select[] = $issueCol ? qid($issueCol) . ' AS issue_text' : 'NULL AS issue_text';

  $sql = 'SELECT ' . implode(', ', $select) . ' FROM maintenance';

  // OLDEST TICKETS FIRST
  if ($createdCol) {
    $sql .= ' ORDER BY ' . qid($createdCol) . ' ASC';
  } elseif ($idCol) {
    $sql .= ' ORDER BY ' . qid($idCol) . ' ASC';
  }

  $stmt = $pdo->query($sql);
  $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($tickets as $ticket) {

    $status = strtolower(trim((string)($ticket['status'] ?? 'open')));

    if ($status === 'in progress') {
      $progressTickets[] = $ticket;
    } elseif (in_array($status, ['closed', 'completed', 'resolved'], true)) {
      continue;
    } else {
      $openTickets[] = $ticket;
    }
  }
} catch (Throwable $e) {
  $error = 'Could not load active tickets right now.';
}

$pageTitle = 'Active Tickets';

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

<h1 class="page-title">Active Tickets</h1>

<div class="panel active-panel">

  <?php if ($created): ?>
    <p class="message-success">Ticket submitted successfully.</p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="message-error"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <style>
    .active-panel {
      max-width: 1380px;
      width: 1380px;
      margin-left: auto;
      margin-right: auto;
      padding: 24px 26px 30px;
    }

    .ticket-columns {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-top: 25px;
    }

    .ticket-column {
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 18px;
      background: #fff;
    }

    .ticket-column h3 {
      text-align: center;
      margin-bottom: 20px;
    }

    .ticket-column table {
      width: 100%;
      border-collapse: collapse;
      table-layout: auto;
    }

    .ticket-column th,
    .ticket-column td {
      padding: 12px 10px;
      border-bottom: 1px solid var(--border);
      text-align: left;
      vertical-align: middle;
    }

    .ticket-column th {
      background: var(--soft);
      font-weight: 700;
      font-size: 13px;
      white-space: nowrap;
    }

    .ticket-column td {
      font-size: 14px;
    }

    /* column sizing */

    .ticket-column th:nth-child(1),
    .ticket-column td:nth-child(1) {
      width: 120px;
      white-space: nowrap;
    }

    .ticket-column th:nth-child(2),
    .ticket-column td:nth-child(2) {
      width: 150px;
      white-space: nowrap;
    }

    .ticket-column th:nth-child(3),
    .ticket-column td:nth-child(3) {
      width: 130px;
      white-space: nowrap;
    }

    .ticket-column th:nth-child(4),
    .ticket-column td:nth-child(4) {
      width: auto;
    }

    .ticket-column th:nth-child(5),
    .ticket-column td:nth-child(5) {
      width: 100px;
      text-align: center;
    }

    .view-btn {
      padding: 7px 14px;
      font-size: 13px;
      white-space: nowrap;
    }

    .status-open {
      color: #0369a1;
      font-weight: 600;
    }

    .status-progress {
      color: #c2410c;
      font-weight: 600;
    }

    @media(max-width:1000px) {
      .ticket-columns {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <div class="ticket-columns">

    <div class="ticket-column">

      <h3>Open Tickets</h3>

      <table>

        <tr>
          <th>Ticket Number</th>
          <th>Date Submitted</th>
          <th>Status</th>
          <th>Issue</th>
          <th>View</th>
        </tr>

        <?php if (!$openTickets): ?>

          <tr>
            <td colspan="5">No open tickets found.</td>
          </tr>

        <?php else: ?>

          <?php foreach ($openTickets as $ticket): ?>

            <tr>

              <td><?php echo htmlspecialchars((string)$ticket['ticket_id']); ?></td>

              <td><?php echo htmlspecialchars((string)($ticket['submitted_at'] ?? '-')); ?></td>

              <td>
                <span class="status-open">
                  <?php echo htmlspecialchars((string)($ticket['status'] ?? 'Open')); ?>
                </span>
              </td>

              <td><?php echo htmlspecialchars((string)($ticket['issue_text'] ?? '-')); ?></td>

              <td>
                <a class="btn btn-secondary view-btn"
                  href="ticket-view.php?id=<?php echo urlencode((string)$ticket['ticket_id']); ?>">
                  View
                </a>
              </td>

            </tr>

          <?php endforeach; ?>

        <?php endif; ?>

      </table>

    </div>


    <div class="ticket-column">

      <h3>In Progress</h3>

      <table>

        <tr>
          <th>Ticket Number</th>
          <th>Date Submitted</th>
          <th>Status</th>
          <th>Issue</th>
          <th>View</th>
        </tr>

        <?php if (!$progressTickets): ?>

          <tr>
            <td colspan="5">No tickets in progress.</td>
          </tr>

        <?php else: ?>

          <?php foreach ($progressTickets as $ticket): ?>

            <tr>

              <td><?php echo htmlspecialchars((string)$ticket['ticket_id']); ?></td>

              <td><?php echo htmlspecialchars((string)($ticket['submitted_at'] ?? '-')); ?></td>

              <td>
                <span class="status-progress">
                  <?php echo htmlspecialchars((string)($ticket['status'] ?? 'In Progress')); ?>
                </span>
              </td>

              <td><?php echo htmlspecialchars((string)($ticket['issue_text'] ?? '-')); ?></td>

              <td>
                <a class="btn btn-secondary view-btn"
                  href="ticket-view.php?id=<?php echo urlencode((string)$ticket['ticket_id']); ?>">
                  View
                </a>
              </td>

            </tr>

          <?php endforeach; ?>

        <?php endif; ?>

      </table>

    </div>

  </div>

  <div class="btn-row" style="margin-top:30px;">
    <a class="btn btn-secondary" href="maintenance.php">Back</a>
  </div>

</div>

<?php require_once __DIR__ . '/partials/app-footer.php'; ?>