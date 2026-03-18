<?php
require_once __DIR__ . '/../auth_check.php';
require_employee('../unauthorized.php');

$query = $_SERVER['QUERY_STRING'] ?? '';
$target = 'admin-renter-details.php' . ($query !== '' ? ('?' . $query) : '');

// Preserve request method/body if an old form posts to this legacy route.
http_response_code(307);
header('Location: ' . $target);
exit;
