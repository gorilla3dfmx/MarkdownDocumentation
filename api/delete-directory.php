<?php
/**
 * API endpoint: Delete directory
 */

session_start();

// Fix SCRIPT_NAME so BASE_URL is calculated correctly
// When accessed as /docs/api/delete-directory.php, we want BASE_URL to be /docs not /docs/api
$_SERVER['SCRIPT_NAME'] = dirname(dirname($_SERVER['SCRIPT_NAME'])) . '/index.php';

$basePath = dirname(__DIR__);
require_once $basePath . '/config/config.php';

// Autoloader
spl_autoload_register(function ($class) use ($basePath) {
    $file = $basePath . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$controller = new FileManagerController();
$controller->deleteDirectory([]);
