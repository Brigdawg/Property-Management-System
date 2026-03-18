<?php
require_once __DIR__ . '/../auth_check.php';
require_login('login.php');

$user = current_user();

$pageTitle = 'Maintenance';
$homeLink = $user['role'] === 'employee' ? 'adminhome.php' : 'renter-dashboard.php';

$navLinks = [
    [
        'label' => 'Dashboard',
        'href' => $user['role'] === 'employee' ? 'adminhome.php' : 'renter-dashboard.php'
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

<h1 class="page-title">Maintenance</h1>

<div class="panel panel-sm">
    <h3>Maintenance Ticket Center</h3>
    <p class="center">
        Submit a new maintenance ticket or review your existing requests.
    </p>

    <div class="btn-row" style="margin-top: 28px;">
        <a class="btn" href="create.php">Open a Ticket</a>
        <a class="btn" href="active.php">Active Tickets</a>
        <a class="btn" href="history.php">Ticket History</a>
    </div>
</div>

<?php require_once __DIR__ . '/partials/app-footer.php'; ?>