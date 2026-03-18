<?php
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');

$query = $_SERVER['QUERY_STRING'] ?? '';
$target = 'admin-renters-view.php' . ($query !== '' ? ('?' . $query) : '');
header('Location: ' . $target);
exit;
