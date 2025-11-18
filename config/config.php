<?php
/**
 * Configuration file
 */

// Base paths
define('BASE_PATH', dirname(__DIR__));
define('DOCS_PATH', BASE_PATH . '/docs');
define('CACHE_PATH', BASE_PATH . '/cache');
define('TEMPLATES_PATH', BASE_PATH . '/templates');

// Base URL - Set this to your subfolder path (e.g., '/doc/v3' or '' for root)
// Auto-detect if not set
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', $scriptPath !== '/' ? $scriptPath : '');

// Database for search index (SQLite)
define('DB_PATH', BASE_PATH . '/data/search.db');

// Authentication
define('AUTH_USERNAME', 'admin');
define('AUTH_PASSWORD', password_hash('admin123', PASSWORD_DEFAULT)); // Change this!

// Application settings
define('SITE_TITLE', 'Gorilla3D Framework - Documentation');
define('ITEMS_PER_PAGE', 20);

// PDF settings
define('PDF_MARGIN_LEFT', 15);
define('PDF_MARGIN_RIGHT', 15);
define('PDF_MARGIN_TOP', 16);
define('PDF_MARGIN_BOTTOM', 16);

// Create necessary directories
$dirs = [CACHE_PATH, dirname(DB_PATH)];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}
