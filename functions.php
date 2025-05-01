<?php
// functions.php

// Start session and load database connection
session_start();
require_once __DIR__ . '/config.php';

/**
 * Check if a user is logged in.
 *
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Retrieve the current logged-in user record.
 *
 * @return array|null
 */
function current_user() {
    global $pdo;
    if (!is_logged_in()) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([ $_SESSION['user_id'] ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Require that a user is logged in, or redirect to login page.
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require that the current user has a specific role.
 *
 * @param string $role  One of 'adopter', 'store_owner', or 'vet'
 */
function require_role($role) {
    $u = current_user();
    if (!$u || $u['role'] !== $role) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirect a logged-in user to their role-specific dashboard.
 */
function redirect_to_dashboard() {
    $u = current_user();
    if (!$u) {
        header('Location: login.php');
        exit;
    }
    switch ($u['role']) {
        case 'adopter':
            header('Location: dashboard/adopter.php');
            break;
        case 'store_owner':
            header('Location: dashboard/store.php');
            break;
        case 'vet':
            header('Location: dashboard/vet.php');
            break;
        default:
            header('Location: login.php');
            break;
    }
    exit;
}
