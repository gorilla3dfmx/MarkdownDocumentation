<?php
/**
 * Editor Controller - handles markdown editing
 */
class EditorController {

    public function edit($params) {
        Auth::requireAuth();

        $version = $params['version'] ?? '';
        $pagePath = $params['path'] ?? '';

        // Decode path
        $pagePath = urldecode($pagePath);

        $markdown = DocumentationManager::getPage($version, $pagePath);

        if ($markdown === null) {
            http_response_code(404);
            return View::render('404', ['path' => '/edit/' . $version . '/' . $pagePath]);
        }

        $success = $_SESSION['edit_success'] ?? null;
        $error = $_SESSION['edit_error'] ?? null;
        unset($_SESSION['edit_success']);
        unset($_SESSION['edit_error']);

        return View::render('editor', [
            'title' => 'Edit - ' . SITE_TITLE,
            'version' => $version,
            'pagePath' => $pagePath,
            'markdown' => $markdown,
            'success' => $success,
            'error' => $error,
            'authenticated' => Auth::isAuthenticated()
        ]);
    }

    public function save($params) {
        Auth::requireAuth();

        $version = $params['version'] ?? '';
        $pagePath = $params['path'] ?? '';
        $content = $_POST['content'] ?? '';

        // Decode path
        $pagePath = urldecode($pagePath);

        if (DocumentationManager::savePage($version, $pagePath, $content)) {
            // Update search index
            SearchIndex::indexPage($version, $pagePath, $content);

            $_SESSION['edit_success'] = 'Page saved successfully';
        } else {
            $_SESSION['edit_error'] = 'Failed to save page';
        }

        Url::redirect('/edit/' . urlencode($version) . '/' . urlencode($pagePath));
    }
}
