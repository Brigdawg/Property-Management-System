<?php


function session_init(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Require any authenticated user.
 * Redirects to $login_url if no valid session exists.
 */
function require_login(string $login_url = 'login.php'): void
{
    session_init();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $login_url);
        exit;
    }
}

/**
 * Require a logged-in employee (admin / staff).
 * Redirects to $login_url if the user is not an employee.
 */
function require_employee(string $login_url = 'login.php'): void
{
    require_login($login_url);
    if (($_SESSION['role'] ?? '') !== 'employee') {
        header('Location: ' . $login_url);
        exit;
    }
}

/**
 * Require a logged-in renter.
 * Redirects to $login_url if the user is not a renter.
 */
function require_renter(string $login_url = 'login.php'): void
{
    require_login($login_url);
    if (($_SESSION['role'] ?? '') !== 'renter') {
        header('Location: ' . $login_url);
        exit;
    }
}

/**
 * Returns true if a user is currently logged in.
 */
function is_logged_in(): bool
{
    session_init();
    return !empty($_SESSION['user_id']);
}

/**
 * Returns an array of the current user's session data.
 * Safe to call even if no session exists (returns empty values).
 */
function current_user(): array
{
    session_init();
    return [
        'id'       => $_SESSION['user_id']    ?? null,
        'name'     => $_SESSION['user_name']   ?? '',
        'email'    => $_SESSION['user_email']  ?? '',
        'role'     => $_SESSION['role']        ?? '',
        'position' => $_SESSION['position']    ?? '',
    ];
}
