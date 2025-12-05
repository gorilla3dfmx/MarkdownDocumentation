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
                        <?php echo renderTree($tree, '', 0, $authenticated ?? false, $version); ?>
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
                        <i class="bi bi-file-pdf"></i> Export as PDF
                    </a>
                    <a href="<?= Url::to('/export/zip?version=' . urlencode($version)) ?>" class="btn btn-lg btn-success ms-2">
                        <i class="bi bi-robot"></i> Export for AI
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
                                <?php
                                /**
                                 * Render the tree structure with grouping headers
                                 * $level represents the folder depth (0 = root folders)
                                 */
                                function renderOverviewTree($tree, $level = 0, $parentPath = '') {
                                    // Map levels to valid Bootstrap margin classes (ms-0 through ms-5)
                                    // Use padding for deeper levels beyond Bootstrap's scale
                                    $indentMap = [0 => '', 1 => 'ms-3', 2 => 'ms-4', 3 => 'ms-5'];

                                    foreach ($tree as $item) {
                                        if ($item['type'] === 'folder') {
                                            // Render folder as a section header
                                            // Root folders (level 0): h5, no indent
                                            // Nested folders (level > 0): h6 with indent
                                            $headerClass = $level === 0 ? 'h5 mt-4 mb-3 pb-2 border-bottom border-secondary' : 'h6 mt-3 mb-2';
                                            $folderIndent = isset($indentMap[$level]) ? $indentMap[$level] : 'ms-5';
                                            echo '<div class="' . $headerClass . ' ' . $folderIndent . '">';
                                            echo '<i class="bi bi-folder text-warning me-2"></i>';
                                            echo '<strong>' . View::escape($item['display']) . '</strong>';
                                            echo '</div>';

                                            // Render children with increased indentation
                                            if (!empty($item['children'])) {
                                                renderOverviewTree($item['children'], $level + 1, $item['path']);
                                            }
                                        } else {
                                            // Render file as a link
                                            // Files are indented at the same depth as their containing folder's children
                                            $fileIndent = isset($indentMap[$level]) ? $indentMap[$level] : 'ms-5';
                                            echo '<div class="mb-2 ' . $fileIndent . '">';
                                            echo '<a href="' . View::escape($item['url']) . '" class="text-decoration-none d-inline-flex align-items-center">';
                                            echo '<i class="bi bi-file-text text-info me-2"></i>';
                                            echo '<span>' . View::escape($item['display']) . '</span>';
                                            echo '</a>';
                                            echo '</div>';
                                        }
                                    }
                                }

                                // Render the tree structure
                                renderOverviewTree($tree);
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
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
// Store current version for JavaScript use
window.currentVersion = <?= json_encode($version) ?>;

// Function to move an item up or down
function moveItem(version, itemPath, direction, itemType) {
    if (!confirm('Move this ' + itemType + ' ' + direction + '?')) {
        return;
    }

    fetch('<?= Url::to('/api/move-item.php') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            version: version,
            item_path: itemPath,
            direction: direction,
            item_type: itemType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show new order
            window.location.reload();
        } else {
            alert('Failed to move item: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}
</script>
<?php endif; ?>

<?php
function renderTree($tree, $currentPath = '', $level = 0, $authenticated = false, $version = '', $parentPath = '') {
    $html = '<ul class="list-unstyled tree-list' . ($level > 0 ? ' ms-3' : '') . '">';

    $count = count($tree);
    foreach ($tree as $index => $item) {
        $html .= '<li class="mb-1">';

        if ($item['type'] === 'folder') {
            $html .= '<div class="d-flex align-items-center justify-content-between">';
            $html .= '<div class="d-flex align-items-center flex-grow-1">';
            $html .= '<span class="tree-toggle me-1" onclick="toggleFolder(this)" style="cursor: pointer; user-select: none;">
                        <i class="bi bi-chevron-right"></i>
                      </span>';
            $html .= '<i class="bi bi-folder text-warning me-2"></i>';
            $html .= '<span class="text-secondary small">' . View::escape($item['display']) . '</span>';
            $html .= '</div>';

            // Add ordering and delete buttons for folders (only when authenticated)
            if ($authenticated) {
                $html .= '<div class="btn-group btn-group-sm" role="group">';

                // Up button (disabled if first item)
                if ($index > 0) {
                    $html .= '<button type="button" class="btn btn-sm btn-link text-secondary p-0" style="font-size: 0.7rem;" onclick="moveItem(\'' . htmlspecialchars($version, ENT_QUOTES) . '\', \'' . htmlspecialchars($item['path'], ENT_QUOTES) . '\', \'up\', \'folder\')" title="Move up">
                                <i class="bi bi-arrow-up"></i>
                              </button>';
                } else {
                    $html .= '<button type="button" class="btn btn-sm btn-link text-muted p-0" style="font-size: 0.7rem; opacity: 0.3;" disabled title="Already first">
                                <i class="bi bi-arrow-up"></i>
                              </button>';
                }

                // Down button (disabled if last item)
                if ($index < $count - 1) {
                    $html .= '<button type="button" class="btn btn-sm btn-link text-secondary p-0" style="font-size: 0.7rem;" onclick="moveItem(\'' . htmlspecialchars($version, ENT_QUOTES) . '\', \'' . htmlspecialchars($item['path'], ENT_QUOTES) . '\', \'down\', \'folder\')" title="Move down">
                                <i class="bi bi-arrow-down"></i>
                              </button>';
                } else {
                    $html .= '<button type="button" class="btn btn-sm btn-link text-muted p-0" style="font-size: 0.7rem; opacity: 0.3;" disabled title="Already last">
                                <i class="bi bi-arrow-down"></i>
                              </button>';
                }

                // Delete button
                $folderPath = $item['path'];
                $html .= '<button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1" style="font-size: 0.7rem;" onclick="confirmDeleteFolder(\'' . htmlspecialchars($version, ENT_QUOTES) . '\', \'' . htmlspecialchars($folderPath, ENT_QUOTES) . '\', event)" title="Delete folder">
                            <i class="bi bi-trash"></i>
                          </button>';

                $html .= '</div>';
            }

            $html .= '</div>';

            if (!empty($item['children'])) {
                $html .= '<div class="tree-children" style="display: none;">';
                $html .= renderTree($item['children'], $currentPath, $level + 1, $authenticated, $version, $item['path']);
                $html .= '</div>';
            }
        } else {
            $html .= '<div class="d-flex align-items-center justify-content-between">';
            $html .= '<a href="' . View::escape($item['url']) . '" class="d-flex align-items-center text-decoration-none small flex-grow-1">';
            $html .= '<i class="bi bi-file-text text-info me-2"></i>';
            $html .= '<span class="text-light">' . View::escape($item['display']) . '</span>';
            $html .= '</a>';

            // Add ordering buttons for files (only when authenticated)
            if ($authenticated) {
                $html .= '<div class="btn-group btn-group-sm" role="group">';

                // Up button (disabled if first item)
                if ($index > 0) {
                    $html .= '<button type="button" class="btn btn-sm btn-link text-secondary p-0" style="font-size: 0.7rem;" onclick="moveItem(\'' . htmlspecialchars($version, ENT_QUOTES) . '\', \'' . htmlspecialchars($item['path'], ENT_QUOTES) . '\', \'up\', \'file\')" title="Move up">
                                <i class="bi bi-arrow-up"></i>
                              </button>';
                } else {
                    $html .= '<button type="button" class="btn btn-sm btn-link text-muted p-0" style="font-size: 0.7rem; opacity: 0.3;" disabled title="Already first">
                                <i class="bi bi-arrow-up"></i>
                              </button>';
                }

                // Down button (disabled if last item)
                if ($index < $count - 1) {
                    $html .= '<button type="button" class="btn btn-sm btn-link text-secondary p-0" style="font-size: 0.7rem;" onclick="moveItem(\'' . htmlspecialchars($version, ENT_QUOTES) . '\', \'' . htmlspecialchars($item['path'], ENT_QUOTES) . '\', \'down\', \'file\')" title="Move down">
                                <i class="bi bi-arrow-down"></i>
                              </button>';
                } else {
                    $html .= '<button type="button" class="btn btn-sm btn-link text-muted p-0" style="font-size: 0.7rem; opacity: 0.3;" disabled title="Already last">
                                <i class="bi bi-arrow-down"></i>
                              </button>';
                }

                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</li>';
    }

    $html .= '</ul>';
    return $html;
}

$content = ob_get_clean();
include 'layout.php';
?>
