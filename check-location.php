<?php
echo "<h1>File Location Check</h1>";
echo "<pre>";
echo "Current file: " . __FILE__ . "\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Document root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'not set') . "\n";
echo "Script filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'not set') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n\n";

echo "Does api directory exist? ";
if (is_dir(__DIR__ . '/api')) {
    echo "YES\n";
    echo "Contents:\n";
    $files = scandir(__DIR__ . '/api');
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file\n";
        }
    }
} else {
    echo "NO\n";
}

echo "\n\nTrying to access api/directory-tree.php:\n";
$apiFile = __DIR__ . '/api/directory-tree.php';
echo "Path: $apiFile\n";
echo "Exists: " . (file_exists($apiFile) ? 'YES' : 'NO') . "\n";

echo "\n\nWhat URL should you use to access the API?\n";
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
echo "Based on current location, try: " . $basePath . "/api/directory-tree.php?version=v2.0\n";
echo "</pre>";
