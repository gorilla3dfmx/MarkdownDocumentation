<?php
/**
 * Home Controller - displays version overview
 */
class HomeController {

    public function index($params = []) {
        $versions = DocumentationManager::getVersions();

        $versionTrees = [];
        foreach ($versions as $version) {
            $versionTrees[$version['name']] = DocumentationManager::getFileTree($version['name']);
        }

        return View::render('home', [
            'title' => SITE_TITLE,
            'versions' => $versions,
            'versionTrees' => $versionTrees,
            'authenticated' => Auth::isAuthenticated()
        ]);
    }
}
