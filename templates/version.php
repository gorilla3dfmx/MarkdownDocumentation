<?php
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <aside class="col-lg-3 col-xl-2 bg-dark border-end border-secondary px-0">
            <div class="sticky-top pt-3" style="top: 1rem;">
                <!-- Version Selector -->
                <div class="px-3 mb-3">
                    <label for="version-select" class="form-label fw-bold small text-secondary">VERSION</label>
                    <select id="version-select" class="form-select form-select-sm" onchange="window.location.href='<?= Url::to('/version/') ?>'+this.value">
                        <?php foreach ($versions as $v): ?>
                            <option value="<?= View::escape($v['name']) ?>" <?= $v['name'] === $version ? 'selected' : '' ?>>
                                <?= View::escape($v['display']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Navigation Tree -->
                <div class="px-3">
                    <h6 class="fw-bold small text-secondary mb-3">NAVIGATION</h6>
                    <?php if (!empty($tree)): ?>
                        <?php echo renderTree($tree); ?>
                    <?php else: ?>
                        <p class="text-muted small">No pages found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="col-lg-9 col-xl-10 px-4 py-5">
            <div class="text-center">
                <h1 class="display-4 mb-4">
                    <i class="bi bi-folder-open text-primary"></i>
                    <?= View::escape('Version ' . $version) ?>
                </h1>
                <p class="lead text-secondary">Select a page from the navigation to view the documentation.</p>

                <div class="mt-4 mb-4">
                    <a href="<?= Url::to('/export/pdf?version=' . urlencode($version)) ?>" class="btn btn-lg btn-primary" target="_blank">
                        <i class="bi bi-file-pdf"></i> Export Complete Documentation as PDF
                    </a>
                </div>

                <?php if (!empty($tree)): ?>
                    <div class="mt-5">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-ul"></i> Available Pages
                                </h5>
                            </div>
                            <div class="card-body text-start">
                                <div class="row">
                                    <?php
                                    $allPages = [];
                                    function collectPages($tree, &$pages) {
                                        foreach ($tree as $item) {
                                            if ($item['type'] === 'file') {
                                                $pages[] = $item;
                                            } elseif (!empty($item['children'])) {
                                                collectPages($item['children'], $pages);
                                            }
                                        }
                                    }
                                    collectPages($tree, $allPages);

                                    $half = ceil(count($allPages) / 2);
                                    $columns = [array_slice($allPages, 0, $half), array_slice($allPages, $half)];
                                    ?>

                                    <?php foreach ($columns as $column): ?>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <?php foreach ($column as $page): ?>
                                                    <li class="mb-2">
                                                        <a href="<?= View::escape($page['url']) ?>" class="text-decoration-none">
                                                            <i class="bi bi-file-text text-info"></i>
                                                            <?= View::escape($page['display']) ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php
function renderTree($tree, $level = 0) {
    $html = '<ul class="list-unstyled tree-list' . ($level > 0 ? ' ms-3' : '') . '">';

    foreach ($tree as $item) {
        $html .= '<li class="mb-1">';

        if ($item['type'] === 'folder') {
            $html .= '<div class="d-flex align-items-center">';
            $html .= '<span class="tree-toggle me-1" onclick="toggleFolder(this)" style="cursor: pointer; user-select: none;">
                        <i class="bi bi-chevron-right"></i>
                      </span>';
            $html .= '<i class="bi bi-folder text-warning me-2"></i>';
            $html .= '<span class="text-secondary small">' . View::escape($item['display']) . '</span>';
            $html .= '</div>';

            if (!empty($item['children'])) {
                $html .= '<div class="tree-children" style="display: none;">';
                $html .= renderTree($item['children'], $level + 1);
                $html .= '</div>';
            }
        } else {
            $html .= '<a href="' . View::escape($item['url']) . '" class="d-flex align-items-center text-decoration-none small">';
            $html .= '<i class="bi bi-file-text text-info me-2"></i>';
            $html .= '<span class="text-light">' . View::escape($item['display']) . '</span>';
            $html .= '</a>';
        }

        $html .= '</li>';
    }

    $html .= '</ul>';
    return $html;
}

$content = ob_get_clean();
include 'layout.php';
?>
