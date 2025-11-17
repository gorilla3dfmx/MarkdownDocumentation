<?php
/**
 * Simple PDF Generator
 * NOTE: This creates a print-friendly HTML page. For production, install mPDF or TCPDF via composer:
 * composer require mpdf/mpdf
 */
class SimplePDF {
    private $pages = [];
    private $currentPage = '';
    private $title = '';

    public function setTitle($title) {
        $this->title = $title;
    }

    public function addPage() {
        if (!empty($this->currentPage)) {
            $this->pages[] = $this->currentPage;
        }
        $this->currentPage = '';
    }

    public function addHeading($text, $level = 1) {
        $this->currentPage .= "<h{$level}>{$text}</h{$level}>";
    }

    public function addHTML($html) {
        $this->currentPage .= $html;
    }

    public function output() {
        // Add last page
        if (!empty($this->currentPage)) {
            $this->pages[] = $this->currentPage;
        }

        // Generate print-friendly HTML that can be saved as PDF
        $html = $this->generatePrintableHTML();

        // For now, return HTML. Users can use browser's Print to PDF
        // For production, use mPDF:
        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML($html);
        // return $mpdf->Output('', 'S');

        return $html;
    }

    private function generatePrintableHTML() {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($this->title) . '</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 21cm;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 10px;
            page-break-after: avoid;
        }
        h2 {
            color: #0d6efd;
            margin-top: 2em;
            page-break-after: avoid;
        }
        h3, h4, h5, h6 {
            color: #495057;
            margin-top: 1.5em;
            page-break-after: avoid;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: "Courier New", Courier, monospace;
            font-size: 0.9em;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            overflow-x: auto;
            page-break-inside: avoid;
        }
        pre code {
            background: none;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1em 0;
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        blockquote {
            border-left: 4px solid #0d6efd;
            padding-left: 1em;
            margin: 1em 0;
            color: #6c757d;
            font-style: italic;
        }
        img {
            max-width: 100%;
            height: auto;
        }
        .page-break {
            page-break-before: always;
        }
        .cover-page {
            text-align: center;
            padding: 5cm 0;
            page-break-after: always;
        }
        .cover-page h1 {
            font-size: 3em;
            border: none;
        }
        @media print {
            body {
                max-width: 100%;
            }
            a {
                color: #000;
                text-decoration: none;
            }
            a[href]:after {
                content: " (" attr(href) ")";
                font-size: 0.8em;
                color: #666;
            }
        }
    </style>
    <script>
        // Auto-trigger print dialog
        window.onload = function() {
            window.print();
        };
    </script>
</head>
<body>
    <div class="cover-page">
        <h1>' . htmlspecialchars($this->title) . '</h1>
        <p>Generated on ' . date('Y-m-d H:i:s') . '</p>
    </div>
';

        foreach ($this->pages as $index => $page) {
            if ($index > 0) {
                $html .= '<div class="page-break"></div>';
            }
            $html .= $page;
        }

        $html .= '
</body>
</html>';

        return $html;
    }
}
