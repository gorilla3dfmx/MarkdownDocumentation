<?php
/**
 * Build Search Index
 * Run this script to build or rebuild the search index
 *
 * Usage: php build-search-index.php
 */

require_once 'config/config.php';

// Autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

echo "Building search index...\n\n";

try {
    // Get all versions
    $versions = DocumentationManager::getVersions();

    if (empty($versions)) {
        echo "No versions found in docs/ directory.\n";
        exit(1);
    }

    $totalPages = 0;

    foreach ($versions as $version) {
        echo "Indexing version: {$version['name']}\n";

        $pages = DocumentationManager::getAllPages($version['name']);

        foreach ($pages as $page) {
            echo "  - Indexing: {$page['path']}\n";

            $markdown = DocumentationManager::getPage($version['name'], $page['path']);
            if ($markdown !== null) {
                SearchIndex::indexPage($version['name'], $page['path'], $markdown);
                $totalPages++;
            }
        }
    }

    echo "\n✓ Successfully indexed $totalPages pages across " . count($versions) . " version(s).\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
