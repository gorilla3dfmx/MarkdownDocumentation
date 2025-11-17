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
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openImageModal()" title="Insert Image">
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

            <!-- Image Gallery Modal -->
            <div class="modal fade" id="imageModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title"><i class="bi bi-image"></i> Insert Image</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <ul class="nav nav-tabs mb-3" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#upload-tab">
                                        <i class="bi bi-upload"></i> Upload
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#gallery-tab" onclick="loadImageGallery()">
                                        <i class="bi bi-images"></i> Gallery
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Upload Tab -->
                                <div class="tab-pane fade show active" id="upload-tab">
                                    <div class="mb-3">
                                        <label for="imageFile" class="form-label">Choose an image</label>
                                        <input type="file" class="form-control" id="imageFile" accept="image/*">
                                        <div class="form-text">Max 5MB. Formats: JPG, PNG, GIF, WebP</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="imageAlt" class="form-label">Alt Text (optional)</label>
                                        <input type="text" class="form-control" id="imageAlt" placeholder="Describe the image">
                                    </div>
                                    <button type="button" class="btn btn-primary" onclick="uploadImage()">
                                        <i class="bi bi-upload"></i> Upload & Insert
                                    </button>
                                    <div id="uploadStatus" class="mt-2"></div>
                                </div>

                                <!-- Gallery Tab -->
                                <div class="tab-pane fade" id="gallery-tab">
                                    <div id="imageGallery" class="row g-3">
                                        <div class="col-12 text-center text-muted">
                                            <div class="spinner-border" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

// Image modal functions
let imageModal;

function openImageModal() {
    if (!imageModal) {
        imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
    }
    imageModal.show();
}

function uploadImage() {
    const fileInput = document.getElementById('imageFile');
    const altText = document.getElementById('imageAlt').value || 'image';
    const statusDiv = document.getElementById('uploadStatus');

    if (!fileInput.files || !fileInput.files[0]) {
        statusDiv.innerHTML = '<div class="alert alert-warning">Please select an image</div>';
        return;
    }

    const formData = new FormData();
    formData.append('image', fileInput.files[0]);

    statusDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Uploading...</div>';

    fetch('<?= Url::to('/upload-image') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle"></i> Image uploaded successfully!</div>';

            // Insert markdown
            const imageMd = `![${altText}](${data.path})`;
            insertImageMarkdown(imageMd);

            // Reset form
            fileInput.value = '';
            document.getElementById('imageAlt').value = '';

            // Close modal after short delay
            setTimeout(() => {
                imageModal.hide();
                statusDiv.innerHTML = '';
            }, 1000);
        } else {
            statusDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${data.error}</div>`;
        }
    })
    .catch(error => {
        statusDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Upload failed</div>';
    });
}

function loadImageGallery() {
    const galleryDiv = document.getElementById('imageGallery');
    galleryDiv.innerHTML = '<div class="col-12 text-center text-muted"><div class="spinner-border" role="status"></div></div>';

    fetch('<?= Url::to('/images/list') ?>')
    .then(response => response.json())
    .then(data => {
        if (data.success && data.images.length > 0) {
            let html = '';
            data.images.forEach(img => {
                const sizeKB = Math.round(img.size / 1024);
                html += `
                    <div class="col-md-4 col-lg-3">
                        <div class="card bg-secondary h-100 image-card" onclick="selectImage('${img.path}', '${img.filename}')">
                            <img src="<?= Url::to('/') ?>${img.path}" class="card-img-top" alt="${img.filename}" style="height: 150px; object-fit: cover; cursor: pointer;">
                            <div class="card-body p-2">
                                <small class="text-truncate d-block" title="${img.filename}">${img.filename}</small>
                                <small class="text-muted">${sizeKB} KB</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            galleryDiv.innerHTML = html;
        } else {
            galleryDiv.innerHTML = '<div class="col-12 text-center text-muted">No images uploaded yet</div>';
        }
    })
    .catch(error => {
        galleryDiv.innerHTML = '<div class="col-12 text-center text-danger">Failed to load gallery</div>';
    });
}

function selectImage(path, filename) {
    const altText = prompt('Enter alt text for the image:', filename.replace(/\.[^/.]+$/, ''));
    if (altText !== null) {
        const imageMd = `![${altText || 'image'}](${path})`;
        insertImageMarkdown(imageMd);
        imageModal.hide();
    }
}

function insertImageMarkdown(markdown) {
    const start = editor.selectionStart;
    const end = editor.selectionEnd;
    const text = editor.value;

    const newText = text.substring(0, start) + markdown + text.substring(end);
    editor.value = newText;

    editor.focus();
    editor.selectionStart = start + markdown.length;
    editor.selectionEnd = start + markdown.length;

    updatePreview();
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
