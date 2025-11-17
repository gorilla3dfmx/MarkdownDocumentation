<?php
/**
 * Search Controller
 */
class SearchController {

    public function index($params = []) {
        $query = $_GET['q'] ?? '';
        $version = $_GET['version'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));

        $results = [];
        $total = 0;

        if (!empty($query)) {
            $offset = ($page - 1) * ITEMS_PER_PAGE;
            $results = SearchIndex::search($query, $version, ITEMS_PER_PAGE, $offset);
            $total = SearchIndex::searchCount($query, $version);
        }

        $versions = DocumentationManager::getVersions();

        return View::render('search', [
            'title' => 'Search - ' . SITE_TITLE,
            'query' => $query,
            'selectedVersion' => $version,
            'results' => $results,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / ITEMS_PER_PAGE),
            'versions' => $versions,
            'authenticated' => Auth::isAuthenticated()
        ]);
    }
}
