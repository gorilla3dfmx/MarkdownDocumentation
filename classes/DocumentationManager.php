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

        // Parse .order file if it exists in this directory
        $orderMap = self::parseOrderFile($path);

        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            // Skip any files or folders starting with a dot
            if ($item[0] === '.') continue;

            $fullPath = $path . '/' . $item;
            $itemRelativePath = $relativePath ? $relativePath . '/' . $item : $item;

            // Determine order from .order file or use default
            $orderFromFile = PHP_INT_MAX;
            if (isset($orderMap[$item])) {
                $orderFromFile = $orderMap[$item];
            }

            if (is_dir($fullPath)) {
                $tree[] = [
                    'type' => 'folder',
                    'name' => $item,
                    'display' => self::formatName($item),
                    'path' => $itemRelativePath,
                    'order' => $orderFromFile,
                    'children' => self::buildTree($fullPath, $version, $itemRelativePath)
                ];
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
                // Get frontmatter to extract order (frontmatter takes precedence over .order file)
                $frontmatter = self::getFileFrontmatter($fullPath);
                $order = isset($frontmatter['order']) ? (int)$frontmatter['order'] : $orderFromFile;
                $excludeExport = isset($frontmatter['exclude_export']) ? (bool)$frontmatter['exclude_export'] : false;

                $tree[] = [
                    'type' => 'file',
                    'name' => $item,
                    'display' => self::formatName(pathinfo($item, PATHINFO_FILENAME)),
                    'path' => $itemRelativePath,
                    'url' => Url::to('/version/' . urlencode($version) . '/page/' . $itemRelativePath),
                    'order' => $order,
                    'exclude_export' => $excludeExport
                ];
            }
        }

        // Sort: files first, then folders (each group sorted by order/alphabetically)
        usort($tree, function($a, $b) {
            // First, separate files from folders - files always come first
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'file' ? -1 : 1;
            }

            // Within the same type, sort by order if both have explicit orders
            if ($a['order'] !== PHP_INT_MAX && $b['order'] !== PHP_INT_MAX) {
                return $a['order'] - $b['order'];
            }

            // If only one has explicit order, it comes first within its type
            if ($a['order'] !== PHP_INT_MAX) return -1;
            if ($b['order'] !== PHP_INT_MAX) return 1;

            // Otherwise sort alphabetically within type
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

        $content = file_get_contents($filePath);

        // Strip frontmatter from the content before returning
        $parsed = self::parseFrontmatter($content);
        return $parsed['content'];
    }

    /**
     * Get raw markdown content for a specific page (including frontmatter)
     * Used by the editor to preserve frontmatter when editing
     */
    public static function getPageRaw($version, $pagePath) {
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

        // Return raw content with frontmatter intact
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
     * Parse frontmatter from markdown content
     * Returns array with 'frontmatter' and 'content' keys
     */
    public static function parseFrontmatterPublic($content) {
        return self::parseFrontmatter($content);
    }

    /**
     * Parse frontmatter from markdown content (internal)
     * Returns array with 'frontmatter' and 'content' keys
     */
    private static function parseFrontmatter($content) {
        $frontmatter = [];
        $mainContent = $content;

        // Check for YAML frontmatter (--- at start)
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
            $yamlContent = $matches[1];
            $mainContent = $matches[2];

            // Parse simple YAML (key: value pairs)
            $lines = explode("\n", $yamlContent);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || $line[0] === '#') continue;

                if (preg_match('/^(\w+):\s*(.+)$/', $line, $match)) {
                    $key = trim($match[1]);
                    $value = trim($match[2]);

                    // Remove quotes if present
                    $value = trim($value, '"\'');

                    // Convert to appropriate type
                    if (is_numeric($value)) {
                        $value = strpos($value, '.') !== false ? (float)$value : (int)$value;
                    } elseif (strtolower($value) === 'true') {
                        $value = true;
                    } elseif (strtolower($value) === 'false') {
                        $value = false;
                    }

                    $frontmatter[$key] = $value;
                }
            }
        }

        return [
            'frontmatter' => $frontmatter,
            'content' => $mainContent
        ];
    }

    /**
     * Get frontmatter from a file
     */
    private static function getFileFrontmatter($filePath) {
        if (!file_exists($filePath) || !is_file($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        $parsed = self::parseFrontmatter($content);
        return $parsed['frontmatter'];
    }

    /**
     * Parse .order file from a directory
     * Returns an associative array with item names as keys and their order as values
     */
    private static function parseOrderFile($directoryPath) {
        $orderFile = $directoryPath . '/.order';
        $orderMap = [];

        if (!file_exists($orderFile) || !is_file($orderFile)) {
            return $orderMap;
        }

        $content = file_get_contents($orderFile);
        $lines = explode("\n", $content);
        $order = 1;

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if (empty($line) || $line[0] === '#') {
                continue;
            }

            // Store the order for this item
            $orderMap[$line] = $order;
            $order++;
        }

        return $orderMap;
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

    /**
     * Get all pages for a version in correct order (respecting .order files and frontmatter)
     * Returns a flat list of pages ordered according to the tree hierarchy
     */
    public static function getAllPagesOrdered($version) {
        $tree = self::getFileTree($version);

        if ($tree === null) {
            return [];
        }

        $pages = [];
        self::flattenTreeToPages($tree, $pages);
        return $pages;
    }

    /**
     * Helper method to flatten tree structure into ordered page list
     * Optionally filter out pages marked with exclude_export
     */
    private static function flattenTreeToPages($tree, &$pages, $excludeExported = true) {
        foreach ($tree as $item) {
            if ($item['type'] === 'file') {
                // Skip files marked for exclusion from exports
                if ($excludeExported && isset($item['exclude_export']) && $item['exclude_export']) {
                    continue;
                }

                $pages[] = [
                    'path' => $item['path'],
                    'title' => $item['display'],
                    'url' => $item['url']
                ];
            } elseif ($item['type'] === 'folder' && !empty($item['children'])) {
                // Recursively process children (folders and files are already sorted in the tree)
                self::flattenTreeToPages($item['children'], $pages, $excludeExported);
            }
        }
    }

    /**
     * Move an item (file or folder) up or down in the ordering
     */
    public static function moveItem($version, $itemPath, $direction, $itemType) {
        if ($itemType === 'file') {
            // For files: update frontmatter order values
            return self::moveFileByFrontmatter($version, $itemPath, $direction);
        } else {
            // For folders: use .order file
            return self::moveFolderByOrderFile($version, $itemPath, $direction);
        }
    }

    /**
     * Move a file by updating frontmatter order values
     */
    private static function moveFileByFrontmatter($version, $itemPath, $direction) {
        // Get the parent directory and current file
        $pathParts = explode('/', $itemPath);
        $fileName = array_pop($pathParts);
        $parentPath = implode('/', $pathParts);

        $fullParentPath = DOCS_PATH . '/' . $version;
        if (!empty($parentPath)) {
            $fullParentPath .= '/' . $parentPath;
        }

        // Get the tree for this directory to find siblings
        $tree = self::buildTree($fullParentPath, $version, $parentPath);

        // Extract only files
        $files = array_filter($tree, function($item) {
            return $item['type'] === 'file';
        });
        $files = array_values($files); // Re-index

        // Find current file
        $currentIndex = null;
        foreach ($files as $index => $file) {
            if ($file['name'] === $fileName) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex === null) {
            return false;
        }

        // Calculate new position
        if ($direction === 'up' && $currentIndex > 0) {
            $swapIndex = $currentIndex - 1;
        } elseif ($direction === 'down' && $currentIndex < count($files) - 1) {
            $swapIndex = $currentIndex + 1;
        } else {
            return false; // Can't move
        }

        // Get current file and swap file
        $currentFile = $files[$currentIndex];
        $swapFile = $files[$swapIndex];

        // Read both files and extract frontmatter
        $currentFilePath = $fullParentPath . '/' . $currentFile['name'];
        $swapFilePath = $fullParentPath . '/' . $swapFile['name'];

        $currentContent = file_get_contents($currentFilePath);
        $swapContent = file_get_contents($swapFilePath);

        $currentParsed = self::parseFrontmatter($currentContent);
        $swapParsed = self::parseFrontmatter($swapContent);

        // Get current order values (or assign new ones)
        $currentOrder = $currentFile['order'] !== PHP_INT_MAX ? $currentFile['order'] : ($currentIndex + 1) * 10;
        $swapOrder = $swapFile['order'] !== PHP_INT_MAX ? $swapFile['order'] : ($swapIndex + 1) * 10;

        // Swap the order values
        $newCurrentOrder = $swapOrder;
        $newSwapOrder = $currentOrder;

        // Update frontmatter in both files
        $currentParsed['frontmatter']['order'] = $newCurrentOrder;
        $swapParsed['frontmatter']['order'] = $newSwapOrder;

        // Write back files with updated frontmatter
        $newCurrentContent = self::buildFrontmatterContent($currentParsed['frontmatter'], $currentParsed['content']);
        $newSwapContent = self::buildFrontmatterContent($swapParsed['frontmatter'], $swapParsed['content']);

        file_put_contents($currentFilePath, $newCurrentContent);
        file_put_contents($swapFilePath, $newSwapContent);

        return true;
    }

    /**
     * Move a folder by updating .order file
     */
    private static function moveFolderByOrderFile($version, $itemPath, $direction) {
        try {
            $pathParts = explode('/', $itemPath);
            $itemName = array_pop($pathParts);
            $parentPath = implode('/', $pathParts);

            $fullParentPath = DOCS_PATH . '/' . $version;
            if (!empty($parentPath)) {
                $fullParentPath .= '/' . $parentPath;
            }

            if (!is_dir($fullParentPath)) {
                throw new Exception("Parent directory does not exist: $fullParentPath");
            }

            // Get tree to find current order
            $tree = self::buildTree($fullParentPath, $version, $parentPath);

            // Extract only folders
            $folders = array_filter($tree, function($item) {
                return $item['type'] === 'folder';
            });
            $folders = array_values($folders);

            if (empty($folders)) {
                throw new Exception("No folders found in directory");
            }

            // Find current position
            $currentIndex = null;
            foreach ($folders as $index => $folder) {
                if ($folder['name'] === $itemName) {
                    $currentIndex = $index;
                    break;
                }
            }

            if ($currentIndex === null) {
                throw new Exception("Folder not found in tree: $itemName");
            }

            // Calculate new position
            if ($direction === 'up' && $currentIndex > 0) {
                $swapIndex = $currentIndex - 1;
            } elseif ($direction === 'down' && $currentIndex < count($folders) - 1) {
                $swapIndex = $currentIndex + 1;
            } else {
                return false; // Can't move (already at edge)
            }

            // Create/update .order file
            $orderFile = $fullParentPath . '/.order';
            $orderMap = [];

            // Read existing .order or create from directory
            if (file_exists($orderFile)) {
                $content = file_get_contents($orderFile);
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || $line[0] === '#') continue;
                    $orderMap[] = $line;
                }
            } else {
                // Create order from all items in directory
                $items = @scandir($fullParentPath);
                if ($items === false) {
                    throw new Exception("Failed to read directory: $fullParentPath");
                }
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..' || $item[0] === '.') continue;
                    $orderMap[] = $item;
                }
            }

            // Find and swap in order map
            $currentPos = array_search($itemName, $orderMap);
            if ($currentPos === false) {
                $orderMap[] = $itemName; // Add if not found
                $currentPos = count($orderMap) - 1;
            }

            // Determine swap position
            if ($direction === 'up' && $currentPos > 0) {
                $swapPos = $currentPos - 1;
            } elseif ($direction === 'down' && $currentPos < count($orderMap) - 1) {
                $swapPos = $currentPos + 1;
            } else {
                return false;
            }

            // Swap
            $temp = $orderMap[$currentPos];
            $orderMap[$currentPos] = $orderMap[$swapPos];
            $orderMap[$swapPos] = $temp;

            // Write back
            $newContent = implode("\n", $orderMap) . "\n";
            $writeResult = @file_put_contents($orderFile, $newContent);

            if ($writeResult === false) {
                throw new Exception("Failed to write .order file: $orderFile");
            }

            return true;
        } catch (Exception $e) {
            error_log("moveFolderByOrderFile error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build frontmatter content string from frontmatter array and content
     */
    private static function buildFrontmatterContent($frontmatter, $content) {
        if (empty($frontmatter)) {
            return $content;
        }

        $yaml = "---\n";
        foreach ($frontmatter as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $yaml .= "$key: $value\n";
        }
        $yaml .= "---\n\n";

        return $yaml . $content;
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
