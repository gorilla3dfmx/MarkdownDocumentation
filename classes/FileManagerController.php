<?php

class FileManagerController {
    private $docManager;

    public function __construct() {
        $this->docManager = new DocumentationManager();
    }

    /**
     * Get directory tree structure for a version
     * Returns JSON for the location picker
     */
    public function getDirectoryTree($params) {
        header('Content-Type: application/json');

        if (!Auth::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $version = $_GET['version'] ?? '';

        if (empty($version)) {
            http_response_code(400);
            echo json_encode(['error' => 'Version is required']);
            exit;
        }

        $versionPath = DOCS_PATH . '/' . $version;

        if (!is_dir($versionPath)) {
            http_response_code(404);
            echo json_encode(['error' => 'Version not found']);
            exit;
        }

        $tree = $this->buildDirectoryTree($versionPath, '');

        echo json_encode($tree);
        exit;
    }

    /**
     * Recursively build directory tree (folders only)
     */
    private function buildDirectoryTree($path, $relativePath = '') {
        $items = [];

        if (!is_dir($path)) {
            return $items;
        }

        $entries = scandir($path);

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || $entry === 'cache' || $entry === 'data') {
                continue;
            }

            $fullPath = $path . '/' . $entry;

            if (is_dir($fullPath)) {
                $newRelativePath = $relativePath ? $relativePath . '/' . $entry : $entry;
                $items[] = [
                    'name' => $entry,
                    'path' => $newRelativePath,
                    'children' => $this->buildDirectoryTree($fullPath, $newRelativePath)
                ];
            }
        }

        return $items;
    }

    /**
     * Create a new markdown page
     */
    public function createPage($params) {
        header('Content-Type: application/json');

        if (!Auth::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $version = $_POST['version'] ?? '';
        $directory = $_POST['directory'] ?? '';
        $filename = $_POST['filename'] ?? '';

        if (empty($version) || empty($filename)) {
            http_response_code(400);
            echo json_encode(['error' => 'Version and filename are required']);
            exit;
        }

        // Sanitize filename - remove any path traversal attempts
        $filename = basename($filename);

        // Ensure .md extension
        if (!preg_match('/\.md$/i', $filename)) {
            $filename .= '.md';
        }

        // Build full path
        $basePath = DOCS_PATH . '/' . $version;

        if (!empty($directory)) {
            // Sanitize directory path
            $directory = str_replace(['../', '..\\'], '', $directory);
            $targetPath = $basePath . '/' . $directory . '/' . $filename;
            $targetDir = dirname($targetPath);
        } else {
            $targetPath = $basePath . '/' . $filename;
            $targetDir = $basePath;
        }

        // Security check - ensure path is within docs directory
        $realBasePath = realpath($basePath);
        $realTargetDir = realpath($targetDir);

        if ($realTargetDir === false) {
            // Directory doesn't exist yet, check parent
            $realTargetDir = realpath(dirname($targetPath));
        }

        if ($realTargetDir !== false && strpos($realTargetDir, $realBasePath) !== 0) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid path']);
            exit;
        }

        // Check if file already exists
        if (file_exists($targetPath)) {
            http_response_code(409);
            echo json_encode(['error' => 'File already exists']);
            exit;
        }

        // Create directory if it doesn't exist
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create directory']);
                exit;
            }
        }

        // Create default markdown content
        $title = ucwords(str_replace(['-', '_', '.md'], [' ', ' ', ''], $filename));
        $content = "# $title\n\nWrite your content here...";

        // Write file
        if (file_put_contents($targetPath, $content) === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create file']);
            exit;
        }

        // Build the page URL for redirect
        $pagePath = !empty($directory) ? $directory . '/' . $filename : $filename;
        // Don't urlencode the pagePath - the router expects slashes in the path
        $pageUrl = BASE_URL . '/version/' . urlencode($version) . '/page/' . $pagePath;

        echo json_encode([
            'success' => true,
            'message' => 'Page created successfully',
            'url' => $pageUrl,
            'path' => $pagePath
        ]);
        exit;
    }

    /**
     * Create a new directory
     */
    public function createDirectory($params) {
        header('Content-Type: application/json');

        if (!Auth::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $version = $_POST['version'] ?? '';
        $parentDirectory = $_POST['parent_directory'] ?? '';
        $directoryName = $_POST['directory_name'] ?? '';

        if (empty($version) || empty($directoryName)) {
            http_response_code(400);
            echo json_encode(['error' => 'Version and directory name are required']);
            exit;
        }

        // Sanitize directory name - remove any invalid characters
        $directoryName = preg_replace('/[^a-zA-Z0-9_-]/', '-', $directoryName);
        $directoryName = basename($directoryName);

        if (empty($directoryName)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid directory name']);
            exit;
        }

        // Build full path
        $basePath = DOCS_PATH . '/' . $version;

        if (!empty($parentDirectory)) {
            // Sanitize parent directory path
            $parentDirectory = str_replace(['../', '..\\'], '', $parentDirectory);
            $targetPath = $basePath . '/' . $parentDirectory . '/' . $directoryName;
        } else {
            $targetPath = $basePath . '/' . $directoryName;
        }

        // Security check - ensure path is within docs directory
        $realBasePath = realpath($basePath);
        $parentPath = dirname($targetPath);

        if (!is_dir($parentPath)) {
            http_response_code(404);
            echo json_encode(['error' => 'Parent directory does not exist']);
            exit;
        }

        $realParentPath = realpath($parentPath);

        if (strpos($realParentPath, $realBasePath) !== 0) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid path']);
            exit;
        }

        // Check if directory already exists
        if (is_dir($targetPath)) {
            http_response_code(409);
            echo json_encode(['error' => 'Directory already exists']);
            exit;
        }

        // Create directory
        if (!mkdir($targetPath, 0755, true)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create directory']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Directory created successfully',
            'path' => !empty($parentDirectory) ? $parentDirectory . '/' . $directoryName : $directoryName
        ]);
        exit;
    }

    /**
     * Get list of all versions
     */
    public function getVersions($params) {
        header('Content-Type: application/json');

        if (!Auth::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $versions = $this->docManager->getVersions();

        echo json_encode(['versions' => $versions]);
        exit;
    }

    /**
     * Delete a page
     */
    public function deletePage($params) {
        header('Content-Type: application/json');

        if (!Auth::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $version = $_POST['version'] ?? '';
        $pagePath = $_POST['page_path'] ?? '';

        if (empty($version) || empty($pagePath)) {
            http_response_code(400);
            echo json_encode(['error' => 'Version and page path are required']);
            exit;
        }

        // Build full path
        $basePath = DOCS_PATH . '/' . $version;
        $targetPath = $basePath . '/' . $pagePath;

        // Security check - ensure path is within docs directory
        $realBasePath = realpath($basePath);
        $realTargetPath = realpath($targetPath);

        if ($realTargetPath === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Page not found']);
            exit;
        }

        if (strpos($realTargetPath, $realBasePath) !== 0) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid path']);
            exit;
        }

        // Ensure it's a file, not a directory
        if (!is_file($realTargetPath)) {
            http_response_code(400);
            echo json_encode(['error' => 'Path is not a file']);
            exit;
        }

        // Delete the file
        if (!unlink($realTargetPath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete page']);
            exit;
        }

        // Return success with redirect URL to version overview
        $redirectUrl = BASE_URL . '/version/' . urlencode($version);

        echo json_encode([
            'success' => true,
            'message' => 'Page deleted successfully',
            'redirect' => $redirectUrl
        ]);
        exit;
    }

    /**
     * Delete a directory (only if empty of markdown files)
     */
    public function deleteDirectory($params) {
        header('Content-Type: application/json');

        if (!Auth::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $version = $_POST['version'] ?? '';
        $directoryPath = $_POST['directory_path'] ?? '';

        if (empty($version) || empty($directoryPath)) {
            http_response_code(400);
            echo json_encode(['error' => 'Version and directory path are required']);
            exit;
        }

        // Build full path
        $basePath = DOCS_PATH . '/' . $version;
        $targetPath = $basePath . '/' . $directoryPath;

        // Security check - ensure path is within docs directory
        $realBasePath = realpath($basePath);
        $realTargetPath = realpath($targetPath);

        if ($realTargetPath === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Directory not found']);
            exit;
        }

        if (strpos($realTargetPath, $realBasePath) !== 0) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid path']);
            exit;
        }

        // Ensure it's a directory, not a file
        if (!is_dir($realTargetPath)) {
            http_response_code(400);
            echo json_encode(['error' => 'Path is not a directory']);
            exit;
        }

        // Check if directory contains any markdown files (recursively)
        if ($this->containsMarkdownFiles($realTargetPath)) {
            http_response_code(400);
            echo json_encode(['error' => 'Directory contains markdown files and cannot be deleted. Please delete or move all .md files first.']);
            exit;
        }

        // Delete the directory and its contents (only non-md files and empty subdirectories)
        if (!$this->removeDirectory($realTargetPath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete directory']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Directory deleted successfully'
        ]);
        exit;
    }

    /**
     * Check if directory contains markdown files (recursively)
     */
    private function containsMarkdownFiles($dir) {
        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;

            if (is_file($path) && preg_match('/\.md$/i', $item)) {
                return true;
            }

            if (is_dir($path)) {
                if ($this->containsMarkdownFiles($path)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Recursively remove directory
     */
    private function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }

        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                if (!$this->removeDirectory($path)) {
                    return false;
                }
            } else {
                // Delete non-markdown files
                if (!unlink($path)) {
                    return false;
                }
            }
        }

        // Remove the directory itself
        return rmdir($dir);
    }
}
