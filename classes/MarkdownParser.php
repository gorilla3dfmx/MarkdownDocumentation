<?php
/**
 * Markdown Parser using Parsedown
 * Provides full markdown support including nested lists
 */

// Load Parsedown library
require_once __DIR__ . '/../parsedown/Parsedown.php';

class MarkdownParser {

    private static $parsedown = null;

    private static function getParsedown() {
        if (self::$parsedown === null) {
            self::$parsedown = new Parsedown();
            self::$parsedown->setSafeMode(false); // Allow HTML in markdown
            self::$parsedown->setBreaksEnabled(false); // Standard markdown line breaks
        }
        return self::$parsedown;
    }

    public static function parse($markdown) {
        $parsedown = self::getParsedown();

        // Use Parsedown to convert markdown to HTML
        $html = $parsedown->text($markdown);

        // Post-process: Add syntax highlighting class to code blocks
        $html = preg_replace(
            '/<code class="language-([^"]+)">/',
            '<code class="language-$1 hljs">',
            $html
        );

        // Post-process: Handle relative URLs for images and links
        $html = self::processRelativeUrls($html);

        // Post-process: Add markdown-table class to tables
        $html = preg_replace('/<table>/', '<table class="markdown-table">', $html);

        return $html;
    }

    private static function processRelativeUrls($html) {
        // Fix relative image URLs starting with docs/ or data/
        $html = preg_replace_callback(
            '/<img src="([^"]+)"/',
            function($matches) {
                $url = $matches[1];
                if (preg_match('#^(docs/|data/)#', $url)) {
                    $url = Url::to('/' . $url);
                }
                return '<img src="' . $url . '"';
            },
            $html
        );

        // Fix relative link URLs starting with docs/ or data/
        $html = preg_replace_callback(
            '/<a href="([^"]+)"/',
            function($matches) {
                $url = $matches[1];
                if (preg_match('#^(docs/|data/)#', $url)) {
                    $url = Url::to('/' . $url);
                }
                return '<a href="' . $url . '"';
            },
            $html
        );

        return $html;
    }

    public static function extractTitle($markdown) {
        // Extract first H1 heading
        if (preg_match('/^#\s+(.+)$/m', $markdown, $matches)) {
            return trim($matches[1]);
        }
        return 'Untitled';
    }

    public static function extractTOC($markdown) {
        // Extract all headings for table of contents
        preg_match_all('/^(#{1,6})\s+(.+)$/m', $markdown, $matches, PREG_SET_ORDER);

        $toc = [];
        foreach ($matches as $match) {
            $level = strlen($match[1]);
            $title = trim($match[2]);
            $slug = self::slugify($title);

            $toc[] = [
                'level' => $level,
                'title' => $title,
                'slug' => $slug
            ];
        }

        return $toc;
    }

    private static function slugify($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}
