<?php
/**
 * Documentation Controller - displays documentation pages
 */
class DocumentationController {

    public function version($params) {
        $version = $params['version'] ?? '';

        $versions = DocumentationManager::getVersions();
        $tree = DocumentationManager::getFileTree($version);

        if ($tree === null) {
            http_response_code(404);
            return View::render('404', ['path' => '/version/' . $version]);
        }

        return View::render('version', [
            'title' => 'Version ' . $version . ' - ' . SITE_TITLE,
            'version' => $version,
            'versions' => $versions,
            'tree' => $tree,
            'authenticated' => Auth::isAuthenticated()
        ]);
    }

    public function page($params) {
        $version = $params['version'] ?? '';
        $pagePath = $params['path'] ?? '';

        // Decode path
        $pagePath = urldecode($pagePath);

        $markdown = DocumentationManager::getPage($version, $pagePath);

        if ($markdown === null) {
            http_response_code(404);
            return View::render('404', ['path' => '/version/' . $version . '/page/' . $pagePath]);
        }

        // Parse markdown
        $html = MarkdownParser::parse($markdown);
        $title = MarkdownParser::extractTitle($markdown);
        $toc = MarkdownParser::extractTOC($markdown);

        // Get navigation tree
        $versions = DocumentationManager::getVersions();
        $tree = DocumentationManager::getFileTree($version);

        return View::render('page', [
            'title' => $title . ' - ' . SITE_TITLE,
            'pageTitle' => $title,
            'content' => $html,
            'toc' => $toc,
            'version' => $version,
            'pagePath' => $pagePath,
            'versions' => $versions,
            'tree' => $tree,
            'authenticated' => Auth::isAuthenticated()
        ]);
    }
}
