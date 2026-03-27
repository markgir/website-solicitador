<?php
/**
 * Authentication functions
 */

session_start();

require_once __DIR__ . '/db.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_user_id']) && !empty($_SESSION['admin_user_id']);
}

/**
 * Require authentication — redirect to login if not authenticated
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Attempt login with username and password
 */
function attemptLogin($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, password_hash, name FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_name'] = $user['name'];
        session_regenerate_id(true);
        return true;
    }

    return false;
}

/**
 * Logout
 */
function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Get current admin user name
 */
function getAdminName() {
    return isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Change admin password
 */
function changePassword($userId, $currentPassword, $newPassword) {
    $db = getDB();
    $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        return false;
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE admin_users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    return $stmt->execute([$newHash, $userId]);
}
