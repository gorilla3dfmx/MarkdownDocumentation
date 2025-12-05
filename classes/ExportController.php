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
                    $zip->addFile($fullPath, $relativePath);
                }
            }
        }
    }
}
