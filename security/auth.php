<?php
// ============================================================
// auth.php - Authentication Functions
// ============================================================
// This file handles everything related to:
//   - Starting secure sessions
//   - Logging users in and out
//   - Checking if someone is logged in
//   - Checking session timeouts
//
// HOW TO USE ON ANY PAGE:
//   require_once '../security/config.php';
//   require_once '../security/auth.php';
//   requireLogin(); // Kicks out anyone not logged in
// ============================================================

require_once __DIR__ . '/config.php';  // Loads database connection

// ============================================================
// startSecureSession()
// Call this at the TOP of every PHP page before any HTML.
// Sets up secure session cookie settings.
// ============================================================
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Make cookies harder to steal
        ini_set('session.cookie_httponly', 1);   // JS can't access the cookie
        ini_set('session.cookie_samesite', 'Strict'); // Prevents CSRF attacks
        ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);

        session_start();
    }
}

// ============================================================
// loginUser($username, $password)
// Checks credentials against the User_Login_Info table.
// Returns true on success, false on failure.
// ============================================================
function loginUser($username, $password) {
    $pdo = getDBConnection();

    // IMPORTANT: We use a prepared statement here.
    // The ? placeholder means the username is NEVER directly put in the SQL string.
    // This prevents SQL injection attacks.
    $stmt = $pdo->prepare("
        SELECT u.User_ID, u.Password_Hash, u.Provider_User_ID, r.Role_Name
        FROM User_Login_Info u
        JOIN User usr ON u.Provider_User_ID = usr.User_ID
        JOIN Roles r ON usr.Role_ID = r.Role_ID
        WHERE u.Username = ?
        LIMIT 1
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Check: Does this username exist AND does the password match?
    if ($user && password_verify($password, $user['Password_Hash'])) {
        
        // SUCCESS - Set up their session
        session_regenerate_id(true);  // New session ID prevents session fixation attacks
        
        $_SESSION['user_id']    = $user['User_ID'];
        $_SESSION['user_name']  = $username;
        $_SESSION['user_role']  = $user['Role_Name'];
        $_SESSION['created']    = time();
        $_SESSION['last_active'] = time();

        // Log the successful login in the Audit_Log table
        logSecurityEvent('LOGIN_SUCCESS', $user['User_ID'], "User logged in: $username");
        
        return true;

    } else {
        // FAIL - Log it (important for detecting brute force attacks)
        logSecurityEvent('LOGIN_FAIL', null, "Failed login attempt for username: $username");
        return false;
    }
}

// ============================================================
// logoutUser()
// Clears the session and sends user to login page.
// ============================================================
function logoutUser() {
    startSecureSession();
    
    $user_id = $_SESSION['user_id'] ?? 'unknown';
    logSecurityEvent('LOGOUT', $user_id, "User logged out");

    // Destroy everything
    $_SESSION = [];
    session_destroy();

    // Send them to the login page
    header('Location: ' . BASE_URL . 'assets/login.php');
    exit();
}

// ============================================================
// requireLogin()
// Put this at the top of any page that needs a logged-in user.
// If they're not logged in, they get sent to the login page.
// ============================================================
function requireLogin() {
    startSecureSession();

    // Not logged in at all?
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'assets/login.php');
        exit();
    }

    // Session timed out from inactivity?
    if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > SESSION_TIMEOUT)) {
        logSecurityEvent('SESSION_TIMEOUT', $_SESSION['user_id'], "Session expired");
        logoutUser();
    }

    // Session exceeded 4-hour maximum?
    if (isset($_SESSION['created']) && (time() - $_SESSION['created'] > SESSION_MAX_LIFE)) {
        logSecurityEvent('SESSION_MAX_EXCEEDED', $_SESSION['user_id'], "Max session time reached");
        logoutUser();
    }

    // Still here? Update last active time.
    $_SESSION['last_active'] = time();
}

// ============================================================
// getCurrentUser()
// Returns info about who is currently logged in.
// Example: $user = getCurrentUser(); echo $user['role'];
// ============================================================
function getCurrentUser() {
    startSecureSession();
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id'   => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role'],
    ];
}

// ============================================================
// logSecurityEvent($event_type, $user_id, $details)
// Writes an entry to the Audit_Log table.
// Called automatically by login/logout functions.
// ============================================================
function logSecurityEvent($event_type, $user_id, $details = '') {
    try {
        $pdo = getDBConnection();

        $stmt = $pdo->prepare("
            INSERT INTO Audit_Log (User_ID, Table_Name, Audit_Date, Action_Type, Details)
            VALUES (?, 'User_Login_Info', NOW(), ?, ?)
        ");
        $stmt->execute([$user_id, $event_type, $details]);

    } catch (Exception $e) {
        // If logging fails, write to PHP error log instead
        error_log("Audit log failed: $event_type | $details | " . $e->getMessage());
    }
}
?>
