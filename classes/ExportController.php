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

        // If no specific pages selected, export all
        if (empty($pages)) {
            $allPages = DocumentationManager::getAllPages($version);
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
}
