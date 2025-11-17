<?php
ob_start();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-pencil-square"></i> Edit Page
                    </h2>
                    <div class="text-muted">
                        <small>
                            <span class="badge bg-primary"><?= View::escape($version) ?></span>
                            <i class="bi bi-chevron-right mx-2"></i>
                            <?= View::escape($pagePath) ?>
                        </small>
                    </div>
                </div>
                <a href="<?= Url::to('/version/' . urlencode($version) . '/page/' . urlencode($pagePath)) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>

            <?php if ($success ?? false): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i>
                    <?= View::escape($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error ?? false): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?= View::escape($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= Url::to('/edit/' . urlencode($version) . '/' . urlencode($pagePath)) ?>">
                <!-- Editor Toolbar -->
                <div class="btn-toolbar mb-3 bg-light p-2 rounded" role="toolbar">
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertMarkdown('**', '**')" title="Bold">
                            <i class="bi bi-type-bold"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertMarkdown('*', '*')" title="Italic">
                            <i class="bi bi-type-italic"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertMarkdown('`', '`')" title="Code">
                            <i class="bi bi-code"></i>
                        </button>
                    </div>
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertMarkdown('[', '](url)')" title="Link">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertMarkdown('![', '](url)')" title="Image">
                            <i class="bi bi-image"></i>
                        </button>
                    </div>
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertMarkdown('\n# ', '')" title="H1">
                            H1
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertMarkdown('\n## ', '')" title="H2">
                            H2
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertMarkdown('\n### ', '')" title="H3">
                            H3
                        </button>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertMarkdown('\n- ', '')" title="List">
                            <i class="bi bi-list-ul"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertMarkdown('\n> ', '')" title="Quote">
                            <i class="bi bi-quote"></i>
                        </button>
                    </div>
                </div>

                <!-- Editor Layout -->
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <i class="bi bi-code-square"></i> Markdown Source
                            </div>
                            <div class="card-body p-0">
                                <textarea id="markdown-editor" name="content" class="form-control border-0 font-monospace" rows="25" style="resize: vertical;"><?= View::escape($markdown) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <i class="bi bi-eye"></i> Preview
                            </div>
                            <div class="card-body">
                                <div id="markdown-preview" class="markdown-content" style="min-height: 400px; max-height: 600px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="d-flex justify-content-between mt-3">
                    <a href="<?= Url::to('/version/' . urlencode($version) . '/page/' . urlencode($pagePath)) ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Live preview
const editor = document.getElementById('markdown-editor');
const preview = document.getElementById('markdown-preview');
let updateTimeout;

function updatePreview() {
    clearTimeout(updateTimeout);
    updateTimeout = setTimeout(() => {
        const markdown = editor.value;

        // Send AJAX request to render markdown
        fetch('<?= Url::to('/preview') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'markdown=' + encodeURIComponent(markdown)
        })
        .then(response => response.text())
        .then(html => {
            preview.innerHTML = html;
            // Re-apply syntax highlighting if hljs is available
            if (typeof hljs !== 'undefined') {
                preview.querySelectorAll('pre code').forEach((block) => {
                    hljs.highlightElement(block);
                });
            }
        })
        .catch(error => {
            console.error('Preview error:', error);
            preview.textContent = 'Error loading preview';
        });
    }, 300); // Debounce for 300ms
}

editor.addEventListener('input', updatePreview);
updatePreview();

// Insert markdown helper
function insertMarkdown(before, after) {
    const start = editor.selectionStart;
    const end = editor.selectionEnd;
    const text = editor.value;
    const selectedText = text.substring(start, end) || 'text';

    const newText = text.substring(0, start) + before + selectedText + after + text.substring(end);
    editor.value = newText;

    editor.focus();
    editor.selectionStart = start + before.length;
    editor.selectionEnd = start + before.length + selectedText.length;

    updatePreview();
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
