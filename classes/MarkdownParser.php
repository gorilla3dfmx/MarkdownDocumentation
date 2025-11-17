<?php
/**
 * Markdown Parser using Parsedown
 * This is a lightweight implementation
 */
class MarkdownParser {

    public static function parse($markdown) {
        $html = self::parseMarkdown($markdown);

        // Add syntax highlighting class to code blocks
        $html = preg_replace(
            '/<pre><code class="language-([^"]+)">/',
            '<pre><code class="language-$1 hljs">',
            $html
        );

        return $html;
    }

    private static function parseMarkdown($text) {
        // Simple markdown parser implementation
        // Bold: **text** or __text__
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $text);

        // Italic: *text* or _text_
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/_(.+?)_/', '<em>$1</em>', $text);

        // Underline: ++text++
        $text = preg_replace('/\+\+(.+?)\+\+/', '<u>$1</u>', $text);

        // Code blocks with language
        $text = preg_replace_callback(
            '/```(\w+)?\n(.*?)```/s',
            function($matches) {
                $lang = $matches[1] ?: 'text';
                $code = htmlspecialchars($matches[2]);
                return '<pre><code class="language-' . $lang . '">' . $code . '</code></pre>';
            },
            $text
        );

        // Inline code: `code`
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);

        // Headers
        $text = preg_replace('/^######\s+(.+)$/m', '<h6>$1</h6>', $text);
        $text = preg_replace('/^#####\s+(.+)$/m', '<h5>$1</h5>', $text);
        $text = preg_replace('/^####\s+(.+)$/m', '<h4>$1</h4>', $text);
        $text = preg_replace('/^###\s+(.+)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^##\s+(.+)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^#\s+(.+)$/m', '<h1>$1</h1>', $text);

        // Links: [text](url)
        $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $text);

        // Images: ![alt](url)
        $text = preg_replace('/!\[([^\]]*)\]\(([^\)]+)\)/', '<img src="$2" alt="$1" />', $text);

        // Unordered lists
        $text = preg_replace_callback(
            '/((?:^[\*\-\+]\s+.+$\n?)+)/m',
            function($matches) {
                $items = preg_replace('/^[\*\-\+]\s+(.+)$/m', '<li>$1</li>', $matches[1]);
                return '<ul>' . $items . '</ul>';
            },
            $text
        );

        // Ordered lists
        $text = preg_replace_callback(
            '/((?:^\d+\.\s+.+$\n?)+)/m',
            function($matches) {
                $items = preg_replace('/^\d+\.\s+(.+)$/m', '<li>$1</li>', $matches[1]);
                return '<ol>' . $items . '</ol>';
            },
            $text
        );

        // Blockquotes
        $text = preg_replace('/^>\s+(.+)$/m', '<blockquote>$1</blockquote>', $text);

        // Horizontal rule
        $text = preg_replace('/^[\-\*_]{3,}$/m', '<hr />', $text);

        // Tables
        $text = preg_replace_callback(
            '/((?:^\|.+\|$\n?)+)/m',
            function($matches) {
                $lines = explode("\n", trim($matches[1]));
                if (count($lines) < 2) return $matches[0];

                $html = '<table class="markdown-table">';

                // Header
                $headerCells = array_map('trim', explode('|', trim($lines[0], '|')));
                $html .= '<thead><tr>';
                foreach ($headerCells as $cell) {
                    $html .= '<th>' . trim($cell) . '</th>';
                }
                $html .= '</tr></thead>';

                // Skip separator line (line 1)
                // Body
                $html .= '<tbody>';
                for ($i = 2; $i < count($lines); $i++) {
                    if (empty(trim($lines[$i]))) continue;
                    $cells = array_map('trim', explode('|', trim($lines[$i], '|')));
                    $html .= '<tr>';
                    foreach ($cells as $cell) {
                        $html .= '<td>' . trim($cell) . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';

                return $html;
            },
            $text
        );

        // Paragraphs (double newline)
        $text = preg_replace('/\n\n/', '</p><p>', $text);
        $text = '<p>' . $text . '</p>';

        // Clean up empty paragraphs and fix nesting
        $text = preg_replace('/<p><\/p>/', '', $text);
        $text = preg_replace('/<p>(<h[1-6]>)/', '$1', $text);
        $text = preg_replace('/(<\/h[1-6]>)<\/p>/', '$1', $text);
        $text = preg_replace('/<p>(<ul>|<ol>|<pre>|<blockquote>|<table|<hr)/', '$1', $text);
        $text = preg_replace('/(<\/ul>|<\/ol>|<\/pre>|<\/blockquote>|<\/table>|<\/hr>)<\/p>/', '$1', $text);

        return $text;
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
