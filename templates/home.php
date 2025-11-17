<?php
ob_start();
?>

<div class="container">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-primary">
            <i class="bi bi-journals"></i> Documentation Versions
        </h1>
        <p class="lead text-secondary">Select a version to browse the documentation</p>
    </div>

    <?php if (empty($versions)): ?>
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle"></i>
            <strong>No documentation found.</strong> Please add markdown files to the <code>docs/</code> directory.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($versions as $version): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <?php if (!empty($version['image'])): ?>
                            <img src="<?= Url::to('/docs/' . View::escape($version['image'])) ?>" class="card-img-top" alt="<?= View::escape($version['display']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="bi bi-book" style="font-size: 4rem; color: white; opacity: 0.9;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-header bg-dark border-bottom border-secondary">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-folder"></i>
                                <a href="<?= Url::to('/version/' . urlencode($version['name'])) ?>" class="text-white text-decoration-none">
                                    <?= View::escape($version['display']) ?>
                                </a>
                            </h5>
                        </div>
                        <div class="card-body bg-dark">
                            <?php if (isset($versionTrees[$version['name']]) && !empty($versionTrees[$version['name']])): ?>
                                <h6 class="text-secondary mb-3">Contents:</h6>
                                <ul class="list-unstyled">
                                    <?php
                                    $tree = $versionTrees[$version['name']];
                                    $count = 0;
                                    foreach ($tree as $item):
                                        if ($count >= 5) break;
                                        $count++;
                                    ?>
                                        <li class="mb-2">
                                            <?php if ($item['type'] === 'folder'): ?>
                                                <i class="bi bi-folder text-warning"></i>
                                                <span class="text-secondary"><?= View::escape($item['display']) ?></span>
                                            <?php else: ?>
                                                <i class="bi bi-file-text text-info"></i>
                                                <a href="<?= View::escape($item['url']) ?>" class="text-decoration-none text-light">
                                                    <?= View::escape($item['display']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if (count($tree) > 5): ?>
                                        <li class="text-secondary">
                                            <small><em>... and <?= count($tree) - 5 ?> more</em></small>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-dark border-top border-secondary">
                            <div class="d-grid gap-2">
                                <a href="<?= Url::to('/version/' . urlencode($version['name'])) ?>" class="btn btn-primary">
                                    <i class="bi bi-arrow-right-circle"></i> Browse Documentation
                                </a>
                                <a href="<?= Url::to('/export/pdf?version=' . urlencode($version['name'])) ?>" class="btn btn-outline-secondary" target="_blank">
                                    <i class="bi bi-file-pdf"></i> Export as PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
