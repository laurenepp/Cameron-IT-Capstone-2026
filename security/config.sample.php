<?php
// ============================================================
// config.sample.php
// ============================================================
// This is the TEMPLATE for your database connection.
// 
// HOW TO USE:
//   1. Copy this file and rename it to config.php
//   2. Fill in your actual database name, username, password
//   3. config.php goes in .gitignore so passwords never hit GitHub
//   4. This sample file stays in GitHub so teammates know the format
// ============================================================

// --- Database Settings ---
define('DB_HOST', 'localhost');       // Almost always localhost for UniServer
define('DB_NAME', 'your_db_name');    // The name of your database in phpMyAdmin
define('DB_USER', 'root');            // UniServer default username
define('DB_PASS', '');                // UniServer default password (blank)
define('DB_CHARSET', 'utf8mb4');      // Supports all characters including emojis

// --- App Settings ---
define('APP_NAME', 'Desert Riverside Clinic');
define('BASE_URL', 'http://localhost/');  // Change to match your URL

// --- Session Timeout Settings ---
define('SESSION_TIMEOUT', 900);      // 15 minutes of inactivity (in seconds)
define('SESSION_MAX_LIFE', 14400);   // 4 hour absolute max (in seconds)

// ============================================================
// DO NOT EDIT BELOW THIS LINE
// This creates the database connection using the values above.
// ============================================================
function getDBConnection() {
    static $pdo = null; // Only connect once per page load

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Show errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return arrays
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Real prepared statements (prevents SQL injection)
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        } catch (PDOException $e) {
            // In production: log the error, don't show it to users
            error_log("Database connection failed: " . $e->getMessage());
            die("Connection failed. Please contact an administrator.");
        }
    }

    return $pdo;
}
?>
