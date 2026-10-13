<?php
/**
 * Doc Index - titles, short descriptions and sections of the pages of a version.
 * Shared by build-llms.php and the Claude skill export.
 */
class DocIndex {

    public static function formatName($name) {
        return ucwords(str_replace(['-', '_'], ' ', $name));
    }

    /** Markdown content of a page without frontmatter */
    public static function pageContent($version, $path) {
        $content = DocumentationManager::getPage($version, $path);
        return $content === null ? '' : str_replace(["\r\n", "\r"], "\n", $content);
    }

    /** First H1 of the page, otherwise derived from the file name */
    public static function pageTitle($content, $path) {
        if (preg_match('/^#\s+(.+?)\s*#*\s*$/m', $content, $m)) {
            return trim($m[1]);
        }
        return self::formatName(pathinfo($path, PATHINFO_FILENAME));
    }

    /** First prose paragraph of the page, plain text, shortened */
    public static function pageDescription($content, $maxLen = 180) {
        $content = preg_replace('/^```.*?^```\s*$/ms', '', $content);
        foreach (preg_split('/\n\s*\n/', $content) as $block) {
            $block = trim($block);
            if ($block === '' || preg_match('/^(#|[-*+]\s|\d+\.\s|\||!\[|<|---|===)/', $block)) {
                continue;
            }
            if ($block[0] === '>') {
                $block = trim(preg_replace('/^>\s?/m', '', $block));
            }
            $text = preg_replace('/!\[[^\]]*\]\([^)]*\)/', '', $block);
            $text = preg_replace('/\[([^\]]*)\]\([^)]*\)/', '$1', $text);
            $text = preg_replace('/(\*\*|__|\+\+)(.+?)\1/', '$2', $text);
            $text = trim(preg_replace('/\s+/', ' ', $text));
            if ($text === '') {
                continue;
            }
            if (mb_strlen($text) > $maxLen) {
                $text = mb_substr($text, 0, $maxLen);
                $text = preg_replace('/\s+\S*$/u', '', $text) . ' ...';
            }
            return $text;
        }
        return '';
    }

    /**
     * Collects the pages of a version grouped by folder, in site navigation order
     * (.order files and frontmatter). Pages with exclude_export are skipped.
     * Each section: ['title' => 'Core / Passes', 'path' => 'Core/Passes', 'pages' => [...]]
     */
    public static function collectSections($tree, $version, $trail = [], $folderPath = '', &$sections = []) {
        $files = [];
        foreach ($tree as $item) {
            if ($item['type'] !== 'file' || !empty($item['exclude_export'])) continue;
            $content = self::pageContent($version, $item['path']);
            $files[] = [
                'path'        => $item['path'],
                'title'       => self::pageTitle($content, $item['path']),
                'description' => self::pageDescription($content),
                'content'     => $content,
            ];
        }
        if ($files) {
            $sections[] = [
                'title' => $trail ? implode(' / ', $trail) : 'Overview',
                'path'  => $folderPath,
                'pages' => $files,
            ];
        }
        foreach ($tree as $item) {
            if ($item['type'] === 'folder' && !empty($item['children'])) {
                self::collectSections($item['children'], $version,
                    array_merge($trail, [self::formatName($item['name'])]), $item['path'], $sections);
            }
        }
        return $sections;
    }
}
