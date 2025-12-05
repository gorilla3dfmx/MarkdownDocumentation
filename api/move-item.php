<?php
/**
 * API: Move item up or down in ordering
 */

// Disable error display - log errors instead
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Prevent any output before JSON
ob_start();

@session_start();
@require_once __DIR__ . '/../config/config.php';

// Load required classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        @require_once $file;
    }
});

// Clear any output buffer and set JSON header
ob_end_clean();
header('Content-Type: application/json');

// Require authentication
if (!Auth::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get parameters
$version = $_POST['version'] ?? '';
$itemPath = $_POST['item_path'] ?? '';
$direction = $_POST['direction'] ?? ''; // 'up' or 'down'
$itemType = $_POST['item_type'] ?? ''; // 'file' or 'folder'

if (empty($version) || empty($itemPath) || empty($direction) || empty($itemType)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Move the item
    $result = DocumentationManager::moveItem($version, $itemPath, $direction, $itemType);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Item moved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to move item']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
