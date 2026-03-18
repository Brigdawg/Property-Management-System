<?php
// seed.php — Inserts sample test users with properly hashed passwords.
//
// HOW TO USE:
//   1. Make sure MAMP is running and the cowboy_properties DB exists.
//   2. Open your browser and navigate to:
//        http://localhost:8889/WebProject/seed.php
//      (use port 8888 if that's what MAMP shows for Apache)
// or for Addison, http://localhost:8090/IS_6465_Group_Project/seed.php 
//   3. You should see "Seeding complete!" with the test credentials.
//   4. After seeding, DELETE or RENAME this file — it should not remain
//      accessible in a real production environment.

require_once __DIR__ . '/db.php';

$pdo = get_db();

// ── Sample Employee (admin/manager) ──────────────────────────────────────────
$empPassword = password_hash('admin123', PASSWORD_DEFAULT);

$pdo->prepare("
    INSERT INTO employee (Firstname, Lastname, Position, Email, Password, phone)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE Password = VALUES(Password)
")->execute([
    'Admin',
    'User',
    'Property Manager',
    'admin@cowboyproperties.com',
    $empPassword,
    '555-000-0001',
]);

// ── Sample Renter ─────────────────────────────────────────────────────────────
$renterPassword = password_hash('renter123', PASSWORD_DEFAULT);

$pdo->prepare("
    INSERT INTO renter (Firstname, Lastname, email, password, phone)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE password = VALUES(password)
")->execute([
    'Jane',
    'Renter',
    'renter@cowboyproperties.com',
    $renterPassword,
    '555-000-0002',
]);

echo '<div style="font-family:sans-serif;padding:30px;max-width:560px">';
echo '<h2 style="color:#0ea5e9">✔ Seeding complete!</h2>';
echo '<p>The following test accounts are now in the database:</p>';
echo '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%">';
echo '<tr style="background:#f1f5f9"><th>Role</th><th>Email</th><th>Password</th></tr>';
echo '<tr><td>Employee&nbsp;(admin)</td><td>admin@cowboyproperties.com</td><td>admin123</td></tr>';
echo '<tr><td>Renter</td><td>renter@cowboyproperties.com</td><td>renter123</td></tr>';
echo '</table>';
echo '<p style="color:#b91c1c;margin-top:20px"><strong>Security reminder:</strong> Delete or rename this file when you are done testing.</p>';
echo '</div>';
