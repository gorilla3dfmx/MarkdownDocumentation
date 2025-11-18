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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold small text-secondary mb-0">NAVIGATION</h6>
                        <?php if ($authenticated ?? false): ?>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#createPageModal" title="Add New Page">
                                    <i class="bi bi-file-plus"></i>
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#createDirectoryModal" title="Add New Directory">
                                    <i class="bi bi-folder-plus"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($tree)): ?>
                        <?php echo renderTree($tree, $pagePath, 0, $authenticated ?? false, $version); ?>
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
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeletePage()">
                            <i class="bi bi-trash"></i> Delete
                        </button>
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

<!-- Create Page Modal -->
<?php if ($authenticated ?? false): ?>
<div class="modal fade" id="createPageModal" tabindex="-1" aria-labelledby="createPageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="createPageModalLabel">
                    <i class="bi bi-file-plus text-success"></i> Add New Page
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createPageForm">
                    <div class="mb-3">
                        <label for="pageVersion" class="form-label">Version <span class="text-danger">*</span></label>
                        <select class="form-select" id="pageVersion" name="version" required>
                            <option value="<?= View::escape($version) ?>" selected><?= View::escape($version) ?></option>
                            <?php foreach ($versions as $v): ?>
                                <?php if ($v['name'] !== $version): ?>
                                    <option value="<?= View::escape($v['name']) ?>"><?= View::escape($v['display']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Select the version where the page should be created</div>
                    </div>

                    <div class="mb-3">
                        <label for="pageLocation" class="form-label">Location (Directory)</label>
                        <select class="form-select" id="pageLocation" name="directory">
                            <option value="">/ (Root - Top level)</option>
                        </select>
                        <div class="form-text">Select the directory where the page should be created. Choose root for top-level pages.</div>
                    </div>

                    <div class="mb-3">
                        <label for="pageFilename" class="form-label">Page Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pageFilename" name="filename" required
                               placeholder="e.g., getting-started or installation-guide">
                        <div class="form-text">Enter the page name (will be used as filename). Use hyphens for spaces. Extension .md will be added automatically.</div>
                    </div>

                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> The page will be created with a default template and you'll be redirected to it.
                    </div>

                    <div id="createPageError" class="alert alert-danger" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="createPageBtn">
                    <i class="bi bi-plus-circle"></i> Create Page
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Directory Modal -->
<div class="modal fade" id="createDirectoryModal" tabindex="-1" aria-labelledby="createDirectoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="createDirectoryModalLabel">
                    <i class="bi bi-folder-plus text-info"></i> Add New Directory
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createDirectoryForm">
                    <div class="mb-3">
                        <label for="dirVersion" class="form-label">Version <span class="text-danger">*</span></label>
                        <select class="form-select" id="dirVersion" name="version" required>
                            <option value="<?= View::escape($version) ?>" selected><?= View::escape($version) ?></option>
                            <?php foreach ($versions as $v): ?>
                                <?php if ($v['name'] !== $version): ?>
                                    <option value="<?= View::escape($v['name']) ?>"><?= View::escape($v['display']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Select the version where the directory should be created</div>
                    </div>

                    <div class="mb-3">
                        <label for="dirParentLocation" class="form-label">Parent Directory</label>
                        <select class="form-select" id="dirParentLocation" name="parent_directory">
                            <option value="">/ (Root - Top level)</option>
                        </select>
                        <div class="form-text">Select the parent directory. Choose root to create a top-level directory.</div>
                    </div>

                    <div class="mb-3">
                        <label for="dirName" class="form-label">Directory Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="dirName" name="directory_name" required
                               placeholder="e.g., advanced or api-reference">
                        <div class="form-text">Enter the directory name. Use hyphens for spaces. Only letters, numbers, hyphens, and underscores allowed.</div>
                    </div>

                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> The directory will be created immediately and will appear in the navigation tree.
                    </div>

                    <div id="createDirectoryError" class="alert alert-danger" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="createDirectoryBtn">
                    <i class="bi bi-plus-circle"></i> Create Directory
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Store current version and page path for JavaScript use
window.currentVersion = <?= json_encode($version) ?>;
window.currentPagePath = <?= json_encode($pagePath) ?>;
</script>
<?php endif; ?>

<?php
function renderTree($tree, $currentPath, $level = 0, $authenticated = false, $version = '') {
    $html = '<ul class="list-unstyled tree-list' . ($level > 0 ? ' ms-3' : '') . '">';

    foreach ($tree as $item) {
        $isActive = ($item['type'] === 'file' && $item['path'] === $currentPath);
        $html .= '<li class="mb-1">';

        if ($item['type'] === 'folder') {
            $html .= '<div class="d-flex align-items-center justify-content-between">';
            $html .= '<div class="d-flex align-items-center">';
            $html .= '<span class="tree-toggle me-1" onclick="toggleFolder(this)" style="cursor: pointer; user-select: none;">
                        <i class="bi bi-chevron-right"></i>
                      </span>';
            $html .= '<i class="bi bi-folder text-warning me-2"></i>';
            $html .= '<span class="text-secondary small">' . View::escape($item['display']) . '</span>';
            $html .= '</div>';

            // Add delete button for folders (only when authenticated)
            if ($authenticated) {
                $folderPath = $item['path'];
                $html .= '<button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" style="font-size: 0.8rem;" onclick="confirmDeleteFolder(\'' . htmlspecialchars($version, ENT_QUOTES) . '\', \'' . htmlspecialchars($folderPath, ENT_QUOTES) . '\', event)" title="Delete folder">
                            <i class="bi bi-trash"></i>
                          </button>';
            }

            $html .= '</div>';

            if (!empty($item['children'])) {
                $html .= '<div class="tree-children" style="display: none;">';
                $html .= renderTree($item['children'], $currentPath, $level + 1, $authenticated, $version);
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
