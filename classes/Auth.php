<?php
/**
 * Authentication handler
 */
class Auth {

    public static function login($username, $password) {
        if ($username === AUTH_USERNAME && password_verify($password, AUTH_PASSWORD)) {
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            return true;
        }
        return false;
    }

    public static function logout() {
        unset($_SESSION['authenticated']);
        unset($_SESSION['username']);
        session_destroy();
    }

    public static function isAuthenticated() {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            Url::redirect('/login');
        }
    }

    public static function getUsername() {
        return $_SESSION['username'] ?? null;
    }
}
