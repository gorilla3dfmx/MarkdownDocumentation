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
                        <?php echo renderTree($tree, $pagePath); ?>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="col-lg-9 col-xl-8 px-4">
            <article class="markdown-content">
                <!-- Page Actions -->
                <div class="d-flex justify-content-end gap-2 mb-3">
                    <?php if ($authenticated ?? false): ?>
                        <a href="<?= Url::to('/edit/' . urlencode($version) . '/' . urlencode($pagePath)) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    <?php endif; ?>
                    <a href="<?= Url::to('/export/pdf?version=' . urlencode($version) . '&pages[]=' . urlencode($pagePath)) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="bi bi-file-pdf"></i> Export PDF
                    </a>
                </div>

                <!-- Markdown Content -->
                <div class="content-body">
                    <?= $content ?>
                </div>
            </article>
        </main>

        <!-- Table of Contents Sidebar -->
        <?php if (!empty($toc) && count($toc) > 1): ?>
            <aside class="col-xl-2 d-none d-xl-block">
                <div class="sticky-top pt-3" style="top: 1rem;">
                    <h6 class="fw-bold small text-secondary mb-3">ON THIS PAGE</h6>
                    <nav class="toc-nav">
                        <ul class="list-unstyled">
                            <?php foreach ($toc as $item): ?>
                                <?php if ($item['level'] <= 3): ?>
                                    <li class="toc-item toc-level-<?= $item['level'] ?> mb-2">
                                        <a href="#<?= View::escape($item['slug']) ?>" class="text-decoration-none text-dark small d-block">
                                            <strong><?= View::escape($item['title']) ?></strong>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                </div>
            </aside>
        <?php endif; ?>
    </div>
</div>

<?php
function renderTree($tree, $currentPath, $level = 0) {
    $html = '<ul class="list-unstyled tree-list' . ($level > 0 ? ' ms-3' : '') . '">';

    foreach ($tree as $item) {
        $isActive = ($item['type'] === 'file' && $item['path'] === $currentPath);
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
                $html .= renderTree($item['children'], $currentPath, $level + 1);
                $html .= '</div>';
            }
        } else {
            $activeClass = $isActive ? ' fw-bold bg-primary bg-opacity-25 rounded px-2 py-1' : '';
            $html .= '<a href="' . View::escape($item['url']) . '" class="d-flex align-items-center text-decoration-none small' . $activeClass . '">';
            $html .= '<i class="bi bi-file-text text-info me-2"></i>';
            $html .= '<span class="' . ($isActive ? 'text-primary' : 'text-light') . '">' . View::escape($item['display']) . '</span>';
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
