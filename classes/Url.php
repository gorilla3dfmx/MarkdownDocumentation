<?php
/**
 * URL Helper class
 */
class Url {
    /**
     * Generate URL with base path
     */
    public static function to($path = '') {
        $path = ltrim($path, '/');
        return BASE_URL . '/' . $path;
    }

    /**
     * Generate asset URL
     */
    public static function asset($path) {
        $path = ltrim($path, '/');
        return BASE_URL . '/assets/' . $path;
    }

    /**
     * Redirect to URL
     */
    public static function redirect($path) {
        header('Location: ' . self::to($path));
        exit;
    }
}
