<?php
/**
 * Export Controller - handles PDF export
 */
class ExportController {

    public function pdf($params = []) {
        $version = $_GET['version'] ?? '';
        $pages = $_GET['pages'] ?? [];

        if (!is_array($pages)) {
            $pages = [$pages];
        }

        // If no specific pages selected, export all in correct order
        if (empty($pages)) {
            $allPages = DocumentationManager::getAllPagesOrdered($version);
            $pages = array_column($allPages, 'path');
        }

        // Generate PDF (returns HTML)
        $html = $this->generatePDF($version, $pages);

        // Output as HTML for browser's Print to PDF
        // In production, use mPDF to generate actual PDF
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    private function generatePDF($version, $pages) {
        // Simple PDF generation using FPDF-like approach
        // For production, use libraries like mPDF or TCPDF

        require_once __DIR__ . '/SimplePDF.php';

        $pdf = new SimplePDF();
        $pdf->setTitle(SITE_TITLE . ' - Version ' . $version);

        foreach ($pages as $pagePath) {
            $markdown = DocumentationManager::getPage($version, $pagePath);
            if ($markdown === null) continue;

            $html = MarkdownParser::parse($markdown);

            // Add page (HTML already contains the heading from markdown)
            $pdf->addPage();
            $pdf->addHTML($html);
        }

        return $pdf->output();
    }

    public function zip($params = []) {
        $version = $_GET['version'] ?? '';

        if (empty($version)) {
            http_response_code(400);
            echo 'Version parameter is required';
            return;
        }

        // Check if ZipArchive is available
        if (!class_exists('ZipArchive')) {
            http_response_code(500);
            echo 'ZIP functionality is not available. Please enable the PHP zip extension.';
            return;
        }

        // Verify version exists
        $versionPath = DOCS_PATH . '/' . $version;
        if (!file_exists($versionPath) || !is_dir($versionPath)) {
            http_response_code(404);
            echo 'Version not found';
            return;
        }

        // Create ZIP file
        $zipFilename = 'documentation-' . $version . '-' . date('Y-m-d') . '.zip';
        $tempZipPath = sys_get_temp_dir() . '/' . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            http_response_code(500);
            echo 'Failed to create ZIP file';
            return;
        }

        // Get file tree to check for exclude_export flags
        $tree = DocumentationManager::getFileTree($version);

        // Add all markdown files with their folder structure
        $this->addDirectoryToZip($zip, $versionPath, $version, '', $tree);

        $zip->close();

        // Send ZIP file to browser
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($tempZipPath));
        readfile($tempZipPath);

        // Clean up temporary file
        unlink($tempZipPath);
    }

    private function addDirectoryToZip($zip, $dirPath, $baseDir, $zipPath = '', $tree = null) {
        $items = scandir($dirPath);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            // Skip files and folders starting with a dot
            if ($item[0] === '.') continue;

            $fullPath = $dirPath . '/' . $item;
            $relativePath = $zipPath ? $zipPath . '/' . $item : $item;

            if (is_dir($fullPath)) {
                // Find the corresponding folder in the tree
                $subTree = null;
                if ($tree !== null) {
                    foreach ($tree as $treeItem) {
                        if ($treeItem['type'] === 'folder' && $treeItem['name'] === $item) {
                            $subTree = $treeItem['children'] ?? null;
                            break;
                        }
                    }
                }

                // Add directory (empty folder)
                $zip->addEmptyDir($relativePath);

                // Recursively add contents
                $this->addDirectoryToZip($zip, $fullPath, $baseDir, $relativePath, $subTree);
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
                // Check if this file should be excluded from export
                $shouldExclude = false;
                if ($tree !== null) {
                    foreach ($tree as $treeItem) {
                        if ($treeItem['type'] === 'file' && $treeItem['name'] === $item) {
                            $shouldExclude = isset($treeItem['exclude_export']) && $treeItem['exclude_export'];
                            break;
                        }
                    }
                }

                // Add markdown file only if not excluded
                if (!$shouldExclude) {
                    $content = $this->prepareMarkdownForExport($zip, file_get_contents($fullPath), $relativePath);
                    $zip->addFromString($relativePath, $content);
                }
            }
        }
    }

    /**
     * Strip frontmatter and bundle referenced local images into the ZIP.
     * Image links are rewritten relative to the markdown file's location in the archive.
     */
    private function prepareMarkdownForExport($zip, $markdown, $relativePath) {
        // Remove frontmatter (order, exclude_export, ...)
        $parsed = DocumentationManager::parseFrontmatterPublic($markdown);
        $content = ltrim($parsed['content'], "\r\n");

        // Prefix to reach the archive root from this file's folder
        $prefix = str_repeat('../', substr_count($relativePath, '/'));

        $replaceImage = function ($src) use ($zip, $prefix) {
            $imagePath = $this->resolveLocalImage($src);
            if ($imagePath === null) return $src;

            if ($zip->locateName($imagePath) === false) {
                $zip->addFile(BASE_PATH . '/' . $imagePath, $imagePath);
            }
            return $prefix . $imagePath;
        };

        // Markdown images: ![alt](src "title")
        $content = preg_replace_callback(
            '/(!\[[^\]]*\]\(\s*<?)([^)\s>]+)/',
            function ($m) use ($replaceImage) {
                return $m[1] . $replaceImage($m[2]);
            },
            $content
        );

        // HTML images: <img src="...">
        $content = preg_replace_callback(
            '/(<img\b[^>]*?\bsrc\s*=\s*["\'])([^"\']+)/i',
            function ($m) use ($replaceImage) {
                return $m[1] . $replaceImage($m[2]);
            },
            $content
        );

        return $content;
    }

    /**
     * Resolve an image reference to a path relative to BASE_PATH.
     * Returns null for external URLs or files that don't exist / lie outside BASE_PATH.
     */
    private function resolveLocalImage($src) {
        if (preg_match('#^([a-z][a-z0-9+.-]*:|//)#i', $src)) return null;

        $path = rawurldecode(preg_replace('/[?#].*$/', '', $src));

        // Strip site base URL (e.g. /doc/v3/data/images/...)
        if (BASE_URL !== '' && strpos($path, BASE_URL . '/') === 0) {
            $path = substr($path, strlen(BASE_URL));
        }
        $path = ltrim($path, '/');
        if (strpos($path, './') === 0) $path = substr($path, 2);

        $basePath = realpath(BASE_PATH);
        $fullPath = realpath(BASE_PATH . '/' . $path);
        if ($fullPath === false || !is_file($fullPath)) return null;
        if (strpos($fullPath, $basePath . DIRECTORY_SEPARATOR) !== 0) return null;

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'])) return null;

        return str_replace('\\', '/', substr($fullPath, strlen($basePath) + 1));
    }
}
