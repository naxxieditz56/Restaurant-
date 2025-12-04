<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (isLoggedIn()) {
    $userId = $_SESSION['admin_id'];
    
    // Clear remember token from database
    $stmt = $pdo->prepare("UPDATE admin_users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$userId]);
    
    // Clear remember me cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    
    // Log activity
    logActivity('logout', 'User logged out');
}

// Destroy all session data
$_SESSION = array();

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear any remaining cookies
setcookie('PHPSESSID', '', time() - 3600, '/');

// Redirect to login page
header('Location: login.php?logout=success');
exit();
?>
