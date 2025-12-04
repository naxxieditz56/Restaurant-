<?php
// Authentication Helper Functions

/**
 * Check if user has required permission
 */
function hasPermission($requiredPermission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $role = $_SESSION['admin_role'];
    
    // Permission hierarchy
    $permissions = [
        'super_admin' => ['super_admin', 'admin', 'editor', 'viewer'],
        'admin' => ['admin', 'editor', 'viewer'],
        'editor' => ['editor', 'viewer'],
        'viewer' => ['viewer']
    ];
    
    if (!isset($permissions[$role])) {
        return false;
    }
    
    return in_array($requiredPermission, $permissions[$role]);
}

/**
 * Require specific permission
 */
function requirePermission($requiredPermission) {
    if (!hasPermission($requiredPermission)) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        header('Location: dashboard.php');
        exit();
    }
}

/**
 * Check if user can access module
 */
function canAccessModule($module) {
    $role = $_SESSION['admin_role'] ?? '';
    
    $modulePermissions = [
        'dashboard' => ['super_admin', 'admin', 'editor', 'viewer'],
        'menu' => ['super_admin', 'admin', 'editor'],
        'reservations' => ['super_admin', 'admin', 'editor'],
        'gallery' => ['super_admin', 'admin', 'editor'],
        'testimonials' => ['super_admin', 'admin', 'editor'],
        'staff' => ['super_admin', 'admin'],
        'settings' => ['super_admin'],
        'analytics' => ['super_admin', 'admin'],
        'users' => ['super_admin'],
        'reports' => ['super_admin', 'admin']
    ];
    
    if (!isset($modulePermissions[$module])) {
        return false;
    }
    
    return in_array($role, $modulePermissions[$module]);
}

/**
 * Get current user info
 */
function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

/**
 * Update user last activity
 */
function updateUserActivity() {
    global $pdo;
    
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("UPDATE admin_users SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
    }
}

/**
 * Check session timeout (30 minutes)
 */
function checkSessionTimeout() {
    $timeout = 30 * 60; // 30 minutes in seconds
    
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
        // Session expired
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit();
    }
    
    $_SESSION['LAST_ACTIVITY'] = time();
}

/**
 * Validate password strength
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    return $errors;
}

/**
 * Generate secure password hash
 */
function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $password;
}

/**
 * Check if user needs to change password (90 days)
 */
function checkPasswordExpiry($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT password_changed_at FROM admin_users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user || !$user['password_changed_at']) {
        return true;
    }
    
    $expiryDate = new DateTime($user['password_changed_at']);
    $expiryDate->modify('+90 days');
    $now = new DateTime();
    
    return $now > $expiryDate;
}

/**
 * Log failed login attempt
 */
function logFailedLogin($username, $ipAddress) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO login_attempts (username, ip_address, attempt_time) VALUES (?, ?, NOW())");
    $stmt->execute([$username, $ipAddress]);
}

/**
 * Check if IP is blocked (5 failed attempts in 15 minutes)
 */
function isIpBlocked($ipAddress) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                          WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE) 
                          AND success = 0");
    $stmt->execute([$ipAddress]);
    $result = $stmt->fetch();
    
    return $result['attempts'] >= 5;
}

/**
 * Get user permissions for sidebar
 */
function getUserPermissions() {
    $role = $_SESSION['admin_role'] ?? '';
    
    $permissions = [
        'dashboard' => true,
        'menu' => in_array($role, ['super_admin', 'admin', 'editor']),
        'reservations' => in_array($role, ['super_admin', 'admin', 'editor']),
        'gallery' => in_array($role, ['super_admin', 'admin', 'editor']),
        'testimonials' => in_array($role, ['super_admin', 'admin', 'editor']),
        'staff' => in_array($role, ['super_admin', 'admin']),
        'settings' => $role === 'super_admin',
        'analytics' => in_array($role, ['super_admin', 'admin']),
        'reports' => in_array($role, ['super_admin', 'admin']),
        'users' => $role === 'super_admin'
    ];
    
    return $permissions;
}

/**
 * Two-factor authentication check
 */
function requires2FA() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT two_factor_enabled FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();
    
    return $user && $user['two_factor_enabled'];
}

/**
 * Generate 2FA secret
 */
function generate2FASecret() {
    require_once 'includes/phpqrcode/qrlib.php';
    
    $secret = random_bytes(20);
    $secretBase32 = base64_encode($secret);
    
    return [
        'secret' => $secretBase32,
        'qr_code' => generateQRCode($secretBase32)
    ];
}

/**
 * Generate QR code for 2FA
 */
function generateQRCode($secret) {
    $issuer = 'Jack Fry\'s Admin';
    $account = $_SESSION['admin_username'] ?? 'admin';
    
    $totpUri = sprintf(
        'otpauth://totp/%s:%s?secret=%s&issuer=%s',
        rawurlencode($issuer),
        rawurlencode($account),
        $secret,
        rawurlencode($issuer)
    );
    
    // Generate QR code (in real implementation, use a QR code library)
    return $totpUri;
}

/**
 * Verify 2FA code
 */
function verify2FACode($secret, $code) {
    // In real implementation, use a TOTP library like RobThree/TwoFactorAuth
    // This is a simplified version
    $timestamp = floor(time() / 30);
    $validCodes = [];
    
    for ($i = -1; $i <= 1; $i++) {
        $time = $timestamp + $i;
        $hmac = hash_hmac('sha1', pack('N*', 0) . pack('N*', $time), $secret, true);
        $offset = ord($hmac[19]) & 0xf;
        $hashpart = substr($hmac, $offset, 4);
        $value = unpack('N', $hashpart);
        $value = $value[1] & 0x7fffffff;
        $validCodes[] = str_pad($value % 1000000, 6, '0', STR_PAD_LEFT);
    }
    
    return in_array($code, $validCodes);
}

/**
 * Audit trail logging
 */
function auditLog($action, $details = '', $userId = null) {
    global $pdo;
    
    $userId = $userId ?? $_SESSION['admin_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, details, ip_address, user_agent) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $details, $ipAddress, $userAgent]);
}

/**
 * Get user activity log
 */
function getUserActivityLog($userId, $limit = 50) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

/**
 * Force password change if required
 */
function checkForcePasswordChange() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check if user needs to change password
    if (checkPasswordExpiry($_SESSION['admin_id'])) {
        $_SESSION['force_password_change'] = true;
        header('Location: change-password.php?expired=1');
        exit();
    }
    
    // Check if password change was forced by admin
    global $pdo;
    $stmt = $pdo->prepare("SELECT force_password_change FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();
    
    if ($user && $user['force_password_change']) {
        $_SESSION['force_password_change'] = true;
        header('Location: change-password.php?forced=1');
        exit();
    }
}

// Initialize session timeout check
if (isLoggedIn()) {
    checkSessionTimeout();
    updateUserActivity();
    checkForcePasswordChange();
}
?>
