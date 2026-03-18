<?php
define('DB_PRIMARY_HOST', '127.0.0.1');
define('DB_PRIMARY_PORT', 8889);
define('DB_PRIMARY_NAME', 'cowboy_properties');
define('DB_PRIMARY_USER', 'root');
define('DB_PRIMARY_PASS', 'root');

define('DB_SECONDARY_HOST', 'localhost');
define('DB_SECONDARY_PORT', 3307);
define('DB_SECONDARY_NAME', 'cowboy_properties');
define('DB_SECONDARY_USER', 'root');
define('DB_SECONDARY_PASS', '');

define('DB_TERTIARY_HOST', 'localhost');
define('DB_TERTIARY_PORT', 3306);
define('DB_TERTIARY_NAME', 'cowboy_properties');
define('DB_TERTIARY_USER', 'root');
define('DB_TERTIARY_PASS', '');

/**
 * Returns a shared PDO instance (singleton).
 * Halts with a readable error message if the connection fails.
 */
function get_db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $connections = [
            [
                'host' => DB_PRIMARY_HOST,
                'port' => DB_PRIMARY_PORT,
                'name' => DB_PRIMARY_NAME,
                'user' => DB_PRIMARY_USER,
                'pass' => DB_PRIMARY_PASS,
            ],
            [
                'host' => DB_SECONDARY_HOST,
                'port' => DB_SECONDARY_PORT,
                'name' => DB_SECONDARY_NAME,
                'user' => DB_SECONDARY_USER,
                'pass' => DB_SECONDARY_PASS,
            ],
            [
                'host' => DB_TERTIARY_HOST,
                'port' => DB_TERTIARY_PORT,
                'name' => DB_TERTIARY_NAME,
                'user' => DB_TERTIARY_USER,
                'pass' => DB_TERTIARY_PASS,
            ],
        ];

        $lastError = null;
        foreach ($connections as $cfg) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $cfg['host'],
                $cfg['port'],
                $cfg['name']
            );

            try {
                $pdo = new PDO($dsn, $cfg['user'], $cfg['pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                break;
            } catch (PDOException $e) {
                $lastError = $e;
            }
        }

        if ($pdo === null) {
            die('<div style="font-family:sans-serif;padding:30px;color:#b91c1c">'
                . '<h2>Database Connection Error</h2>'
                . '<p>' . htmlspecialchars($lastError ? $lastError->getMessage() : 'Unable to connect to any configured database.') . '</p>'
                . '<p>Check that MySQL is running and credentials in <code>db.php</code> are correct.</p>'
                . '</div>');
        }
    }

    return $pdo;
}
