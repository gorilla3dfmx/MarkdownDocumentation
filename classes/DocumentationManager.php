<?php
/**
 * Documentation Manager - handles version and file hierarchy
 */
class DocumentationManager {

    /**
     * Get all available versions
     */
    public static function getVersions() {
        $versions = [];

        if (!file_exists(DOCS_PATH)) {
            return $versions;
        }

        $dirs = scandir(DOCS_PATH);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;

            // Skip cache and data directories
            if (in_array(strtolower($dir), ['cache', 'data'])) continue;

            $path = DOCS_PATH . '/' . $dir;
            if (is_dir($path)) {
                // Check for version image
                $imageFile = null;
                foreach (['version.png', 'version.jpg', 'version.jpeg', 'version.gif'] as $imgName) {
                    if (file_exists($path . '/' . $imgName)) {
                        $imageFile = $dir . '/' . $imgName;
                        break;
                    }
                }

                $versions[] = [
                    'name' => $dir,
                    'path' => $path,
                    'display' => self::formatVersionName($dir),
                    'image' => $imageFile
                ];
            }
        }

        // Sort versions (newest first)
        usort($versions, function($a, $b) {
            return version_compare($b['name'], $a['name']);
        });

        return $versions;
    }

    /**
     * Get file tree for a specific version
     */
    public static function getFileTree($version) {
        $versionPath = DOCS_PATH . '/' . $version;

        if (!file_exists($versionPath)) {
            return null;
        }

        return self::buildTree($versionPath, $version);
    }

    private static function buildTree($path, $version, $relativePath = '') {
        $tree = [];

        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $fullPath = $path . '/' . $item;
            $itemRelativePath = $relativePath ? $relativePath . '/' . $item : $item;

            if (is_dir($fullPath)) {
                $tree[] = [
                    'type' => 'folder',
                    'name' => $item,
                    'display' => self::formatName($item),
                    'path' => $itemRelativePath,
                    'children' => self::buildTree($fullPath, $version, $itemRelativePath)
                ];
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
                $tree[] = [
                    'type' => 'file',
                    'name' => $item,
                    'display' => self::formatName(pathinfo($item, PATHINFO_FILENAME)),
                    'path' => $itemRelativePath,
                    'url' => Url::to('/version/' . urlencode($version) . '/page/' . $itemRelativePath)
                ];
            }
        }

        // Sort: folders first, then files, alphabetically
        usort($tree, function($a, $b) {
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'folder' ? -1 : 1;
            }
            return strcasecmp($a['display'], $b['display']);
        });

        return $tree;
    }

    /**
     * Get markdown content for a specific page
     */
    public static function getPage($version, $pagePath) {
        $filePath = DOCS_PATH . '/' . $version . '/' . $pagePath;

        // Security: prevent directory traversal
        $realBase = realpath(DOCS_PATH . '/' . $version);
        $realPath = realpath($filePath);

        if (!$realPath || strpos($realPath, $realBase) !== 0) {
            return null;
        }

        if (!file_exists($filePath) || !is_file($filePath)) {
            return null;
        }

        return file_get_contents($filePath);
    }

    /**
     * Save markdown content
     */
    public static function savePage($version, $pagePath, $content) {
        $filePath = DOCS_PATH . '/' . $version . '/' . $pagePath;

        // Security: prevent directory traversal
        $realBase = realpath(DOCS_PATH . '/' . $version);
        $realPathDir = realpath(dirname($filePath));

        if (!$realPathDir || strpos($realPathDir, $realBase) !== 0) {
            return false;
        }

        // Create directory if it doesn't exist
        $dir = dirname($filePath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($filePath, $content) !== false;
    }

    /**
     * Format version name for display
     */
    private static function formatVersionName($name) {
        // Convert v1.0 to Version 1.0, etc.
        if (preg_match('/^v?(\d+\.?\d*\.?\d*)/', $name, $matches)) {
            return 'Version ' . $matches[1];
        }
        return ucfirst($name);
    }

    /**
     * Format file/folder name for display
     */
    private static function formatName($name) {
        // Replace hyphens and underscores with spaces, capitalize words
        $name = str_replace(['-', '_'], ' ', $name);
        return ucwords($name);
    }

    /**
     * Get all pages for a version (flat list)
     */
    public static function getAllPages($version) {
        $versionPath = DOCS_PATH . '/' . $version;

        if (!file_exists($versionPath)) {
            return [];
        }

        $pages = [];
        self::scanPagesRecursive($versionPath, $version, '', $pages);
        return $pages;
    }

    private static function scanPagesRecursive($path, $version, $relativePath, &$pages) {
        $items = scandir($path);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $fullPath = $path . '/' . $item;
            $itemRelativePath = $relativePath ? $relativePath . '/' . $item : $item;

            if (is_dir($fullPath)) {
                self::scanPagesRecursive($fullPath, $version, $itemRelativePath, $pages);
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
                $pages[] = [
                    'path' => $itemRelativePath,
                    'title' => self::formatName(pathinfo($item, PATHINFO_FILENAME)),
                    'url' => Url::to('/version/' . urlencode($version) . '/page/' . $itemRelativePath)
                ];
            }
        }
    }
}
