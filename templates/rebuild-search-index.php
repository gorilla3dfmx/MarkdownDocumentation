<?php
ob_start();

// Execute the search index rebuild
$output = [];
$totalPages = 0;
$success = false;
$errorMessage = null;

try {
    // Get all versions
    $versions = DocumentationManager::getVersions();

    if (empty($versions)) {
        $errorMessage = "No versions found in docs/ directory.";
    } else {
        $output[] = ['type' => 'info', 'message' => 'Starting search index rebuild...'];

        foreach ($versions as $version) {
            $output[] = ['type' => 'version', 'message' => "Indexing version: {$version['name']}"];

            $pages = DocumentationManager::getAllPages($version['name']);

            foreach ($pages as $page) {
                $output[] = ['type' => 'page', 'message' => "Indexing: {$page['path']}"];

                $markdown = DocumentationManager::getPage($version['name'], $page['path']);
                if ($markdown !== null) {
                    SearchIndex::indexPage($version['name'], $page['path'], $markdown);
                    $totalPages++;
                }
            }
        }

        $output[] = ['type' => 'success', 'message' => "Successfully indexed $totalPages pages across " . count($versions) . " version(s)."];
        $success = true;
    }

} catch (Exception $e) {
    $errorMessage = "Error: " . $e->getMessage();
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card bg-dark border-secondary">
                <div class="card-header bg-secondary">
                    <h4 class="mb-0">
                        <i class="bi bi-arrow-clockwise"></i> Rebuild Search Index
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= View::escape($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> Search index rebuilt successfully!
                        </div>
                    <?php endif; ?>

                    <div class="bg-black p-3 rounded" style="max-height: 500px; overflow-y: auto; font-family: monospace; font-size: 0.9rem;">
                        <?php foreach ($output as $line): ?>
                            <?php
                            $icon = '';
                            $class = 'text-light';

                            switch ($line['type']) {
                                case 'info':
                                    $icon = '<i class="bi bi-info-circle text-info"></i>';
                                    $class = 'text-info';
                                    break;
                                case 'version':
                                    $icon = '<i class="bi bi-folder text-warning"></i>';
                                    $class = 'text-warning fw-bold';
                                    break;
                                case 'page':
                                    $icon = '<i class="bi bi-file-text text-secondary"></i>';
                                    $class = 'text-secondary';
                                    break;
                                case 'success':
                                    $icon = '<i class="bi bi-check-circle text-success"></i>';
                                    $class = 'text-success fw-bold';
                                    break;
                            }
                            ?>
                            <div class="mb-1 <?= $class ?>">
                                <?= $icon ?> <?= View::escape($line['message']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <a href="<?= Url::to('/search') ?>" class="btn btn-primary">
                            <i class="bi bi-search"></i> Go to Search
                        </a>
                        <a href="<?= Url::to('/') ?>" class="btn btn-secondary">
                            <i class="bi bi-house"></i> Go to Home
                        </a>
                        <button onclick="location.reload()" class="btn btn-outline-info">
                            <i class="bi bi-arrow-clockwise"></i> Rebuild Again
                        </button>
                    </div>

                    <div class="mt-3">
                        <small class="text-secondary">
                            <i class="bi bi-info-circle"></i>
                            The search index has been rebuilt and is now ready to use.
                            All previously indexed pages have been updated with the latest content.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
