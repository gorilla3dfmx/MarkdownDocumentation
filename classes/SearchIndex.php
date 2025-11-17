<?php
/**
 * Search Index - handles full-text search using SQLite
 */
class SearchIndex {

    private static $db = null;

    private static function getDB() {
        if (self::$db === null) {
            self::$db = new PDO('sqlite:' . DB_PATH);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::initDB();
        }
        return self::$db;
    }

    private static function initDB() {
        $db = self::$db;

        // Create search index table
        $db->exec("
            CREATE TABLE IF NOT EXISTS search_index (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                version TEXT NOT NULL,
                page_path TEXT NOT NULL,
                title TEXT NOT NULL,
                content TEXT NOT NULL,
                url TEXT NOT NULL,
                indexed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(version, page_path)
            )
        ");

        // Create full-text search virtual table
        $db->exec("
            CREATE VIRTUAL TABLE IF NOT EXISTS search_fts USING fts5(
                version,
                page_path,
                title,
                content,
                url
            )
        ");
    }

    /**
     * Index a single page
     */
    public static function indexPage($version, $pagePath, $markdown) {
        $db = self::getDB();

        $title = MarkdownParser::extractTitle($markdown);
        $content = strip_tags(MarkdownParser::parse($markdown));
        $url = '/version/' . urlencode($version) . '/page/' . urlencode($pagePath);

        // Insert or update in main index
        $stmt = $db->prepare("
            INSERT OR REPLACE INTO search_index (version, page_path, title, content, url)
            VALUES (:version, :page_path, :title, :content, :url)
        ");

        $stmt->execute([
            ':version' => $version,
            ':page_path' => $pagePath,
            ':title' => $title,
            ':content' => $content,
            ':url' => $url
        ]);

        // Update FTS index
        self::rebuildFTS();
    }

    /**
     * Index all pages for a version
     */
    public static function indexVersion($version) {
        $pages = DocumentationManager::getAllPages($version);

        foreach ($pages as $page) {
            $markdown = DocumentationManager::getPage($version, $page['path']);
            if ($markdown !== null) {
                self::indexPage($version, $page['path'], $markdown);
            }
        }
    }

    /**
     * Index all versions
     */
    public static function indexAll() {
        $versions = DocumentationManager::getVersions();

        foreach ($versions as $version) {
            self::indexVersion($version['name']);
        }
    }

    /**
     * Rebuild FTS index from main index
     */
    private static function rebuildFTS() {
        $db = self::getDB();

        $db->exec("DELETE FROM search_fts");

        $stmt = $db->query("SELECT version, page_path, title, content, url FROM search_index");
        $insertStmt = $db->prepare("
            INSERT INTO search_fts (version, page_path, title, content, url)
            VALUES (:version, :page_path, :title, :content, :url)
        ");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $insertStmt->execute($row);
        }
    }

    /**
     * Search for pages
     */
    public static function search($query, $version = null, $limit = 20, $offset = 0) {
        $db = self::getDB();

        // Prepare query for FTS
        $ftsQuery = implode(' OR ', array_map(function($word) {
            return '"' . str_replace('"', '""', $word) . '"*';
        }, explode(' ', $query)));

        $sql = "
            SELECT
                version,
                page_path,
                title,
                content,
                url,
                snippet(search_fts, 3, '<mark>', '</mark>', '...', 50) as snippet
            FROM search_fts
            WHERE search_fts MATCH :query
        ";

        if ($version) {
            $sql .= " AND version = :version";
        }

        $sql .= " ORDER BY rank LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':query', $ftsQuery, PDO::PARAM_STR);
        if ($version) {
            $stmt->bindValue(':version', $version, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total search results count
     */
    public static function searchCount($query, $version = null) {
        $db = self::getDB();

        $ftsQuery = implode(' OR ', array_map(function($word) {
            return '"' . str_replace('"', '""', $word) . '"*';
        }, explode(' ', $query)));

        $sql = "SELECT COUNT(*) as count FROM search_fts WHERE search_fts MATCH :query";

        if ($version) {
            $sql .= " AND version = :version";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':query', $ftsQuery, PDO::PARAM_STR);
        if ($version) {
            $stmt->bindValue(':version', $version, PDO::PARAM_STR);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
