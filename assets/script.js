// Documentation Framework JavaScript with Bootstrap

document.addEventListener('DOMContentLoaded', function() {
    // Initialize syntax highlighting
    if (typeof hljs !== 'undefined') {
        // Configure highlight.js to ignore warnings about escaped HTML (we properly escape server-side)
        hljs.configure({ ignoreUnescapedHTML: true });
        hljs.highlightAll();
    }

    // Initialize tree navigation
    initTreeNavigation();

    // Add heading anchors
    addHeadingAnchors();

    // Auto-expand active tree items
    expandActiveTreeItems();
});

/**
 * Toggle folder in navigation tree
 */
function toggleFolder(element) {
    element.classList.toggle('open');
    const parent = element.closest('li');
    const childrenDiv = parent.querySelector('.tree-children');

    if (childrenDiv) {
        if (childrenDiv.style.display === 'none') {
            childrenDiv.style.display = 'block';
        } else {
            childrenDiv.style.display = 'none';
        }
    }
}

/**
 * Initialize tree navigation
 */
function initTreeNavigation() {
    // Auto-expand tree to show active page
    const activeItem = document.querySelector('.tree-list a.fw-bold');
    if (activeItem) {
        let parent = activeItem.closest('li');
        while (parent) {
            const childrenDiv = parent.querySelector('.tree-children');
            if (childrenDiv) {
                childrenDiv.style.display = 'block';
                const toggle = parent.querySelector('.tree-toggle');
                if (toggle) {
                    toggle.classList.add('open');
                }
            }
            parent = parent.parentElement.closest('li');
        }
    }
}

/**
 * Expand all parent folders of active tree items
 */
function expandActiveTreeItems() {
    const activeBadges = document.querySelectorAll('.tree-list .bg-primary');

    activeBadges.forEach(badge => {
        let element = badge;
        while (element) {
            if (element.classList && element.classList.contains('tree-children')) {
                element.style.display = 'block';

                // Find and rotate the toggle icon
                const parentLi = element.parentElement;
                const toggle = parentLi.querySelector('.tree-toggle');
                if (toggle) {
                    toggle.classList.add('open');
                }
            }
            element = element.parentElement;
        }
    });
}

/**
 * Add anchor links to headings
 */
function addHeadingAnchors() {
    const headings = document.querySelectorAll('.markdown-content h1, .markdown-content h2, .markdown-content h3');

    headings.forEach(heading => {
        // Generate ID from heading text
        const id = heading.textContent
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-+|-+$/g, '');

        heading.id = id;

        // Add anchor link
        const anchor = document.createElement('a');
        anchor.href = '#' + id;
        anchor.className = 'heading-anchor';
        anchor.innerHTML = '<i class="bi bi-link-45deg"></i>';

        heading.appendChild(anchor);
    });
}

/**
 * Smooth scroll to anchors
 */
document.addEventListener('click', function(e) {
    if (e.target.tagName === 'A' || e.target.closest('a')) {
        const link = e.target.tagName === 'A' ? e.target : e.target.closest('a');
        const href = link.getAttribute('href');

        if (href && href.startsWith('#')) {
            e.preventDefault();
            const id = href.substring(1);
            const element = document.getElementById(id);

            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                window.history.pushState(null, null, '#' + id);
            }
        }
    }
});

/**
 * Highlight active TOC item on scroll
 */
if (document.querySelector('.toc-nav')) {
    let ticking = false;

    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                highlightTOC();
                ticking = false;
            });
            ticking = true;
        }
    });

    function highlightTOC() {
        const headings = document.querySelectorAll('.markdown-content h1, .markdown-content h2, .markdown-content h3');
        const tocLinks = document.querySelectorAll('.toc-nav a');

        let current = '';
        const scrollPos = window.scrollY + 100;

        headings.forEach(heading => {
            const top = heading.offsetTop;
            if (scrollPos >= top) {
                current = heading.id;
            }
        });

        tocLinks.forEach(link => {
            link.classList.remove('fw-bold', 'text-primary');
            link.classList.add('text-muted');

            if (link.getAttribute('href') === '#' + current) {
                link.classList.remove('text-muted');
                link.classList.add('fw-bold', 'text-primary');
            }
        });
    }
}

/**
 * Copy code blocks
 */
document.querySelectorAll('pre code').forEach(block => {
    const button = document.createElement('button');
    button.className = 'btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2';
    button.innerHTML = '<i class="bi bi-clipboard"></i>';
    button.title = 'Copy code';

    button.addEventListener('click', function() {
        navigator.clipboard.writeText(block.textContent).then(() => {
            button.innerHTML = '<i class="bi bi-check2"></i>';
            setTimeout(() => {
                button.innerHTML = '<i class="bi bi-clipboard"></i>';
            }, 2000);
        });
    });

    const pre = block.parentElement;
    if (pre && pre.tagName === 'PRE') {
        pre.style.position = 'relative';
        pre.appendChild(button);
    }
});

/**
 * File Management Functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals if they exist
    const createPageModal = document.getElementById('createPageModal');
    const createDirectoryModal = document.getElementById('createDirectoryModal');

    if (createPageModal) {
        // Load directory tree when modal opens or version changes
        const pageVersionSelect = document.getElementById('pageVersion');
        const pageLocationSelect = document.getElementById('pageLocation');

        createPageModal.addEventListener('show.bs.modal', function() {
            loadDirectoryTree(pageVersionSelect.value, pageLocationSelect);
        });

        pageVersionSelect.addEventListener('change', function() {
            loadDirectoryTree(this.value, pageLocationSelect);
        });

        // Handle create page button
        document.getElementById('createPageBtn').addEventListener('click', function() {
            handleCreatePage();
        });
    }

    if (createDirectoryModal) {
        // Load directory tree when modal opens or version changes
        const dirVersionSelect = document.getElementById('dirVersion');
        const dirParentLocationSelect = document.getElementById('dirParentLocation');

        createDirectoryModal.addEventListener('show.bs.modal', function() {
            loadDirectoryTree(dirVersionSelect.value, dirParentLocationSelect);
        });

        dirVersionSelect.addEventListener('change', function() {
            loadDirectoryTree(this.value, dirParentLocationSelect);
        });

        // Handle create directory button
        document.getElementById('createDirectoryBtn').addEventListener('click', function() {
            handleCreateDirectory();
        });
    }
});

/**
 * Load directory tree for location picker
 */
function loadDirectoryTree(version, selectElement) {
    // Show loading state
    selectElement.innerHTML = '<option value="">Loading...</option>';
    selectElement.disabled = true;

    // Detect base URL from current page path
    const path = window.location.pathname;
    let baseUrl = '';

    // Extract base path (e.g., /docs, /doc/v3, etc.)
    const matches = path.match(/^(\/[^\/]+)/);
    if (matches && matches[1] !== '/index.php' && matches[1] !== '/version' && matches[1] !== '/edit') {
        baseUrl = matches[1];
    }

    const url = `${baseUrl}/api/directory-tree.php?version=${encodeURIComponent(version)}`;

    console.log('Detected base URL:', baseUrl);
    console.log('Loading directory tree from:', url);

    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text();
        })
        .then(text => {
            console.log('Response text:', text);
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                throw new Error('Invalid JSON response: ' + text);
            }

            // Check for error in response
            if (data.error) {
                throw new Error(data.error);
            }

            // Clear and add root option
            selectElement.innerHTML = '<option value="">/ (Root - Top level)</option>';

            // Build options from tree
            console.log('Building options from data:', data);
            buildLocationOptions(data, selectElement, '', 0);

            selectElement.disabled = false;
        })
        .catch(error => {
            console.error('Error loading directory tree:', error);
            console.error('Full error:', error);
            selectElement.innerHTML = '<option value="">/ (Root - Top level)</option>';
            selectElement.disabled = false;

            // Show more detailed error
            let errorMsg = 'Error loading directories: ' + error.message;
            if (error.message.includes('Invalid JSON response')) {
                errorMsg += '\n\nThis usually means you are not logged in or there is a server error. Please check the browser console for details.';
            }
            alert(errorMsg);
        });
}

/**
 * Recursively build location options with visual hierarchy
 */
function buildLocationOptions(tree, selectElement, parentPath, level) {
    tree.forEach(item => {
        const indent = '\u00A0\u00A0'.repeat(level); // Non-breaking spaces for indentation
        const icon = '\u{1F4C1}'; // Folder icon
        const displayName = item.name.replace(/-/g, ' ').replace(/_/g, ' ');
        const displayText = `${indent}${icon} ${displayName}`;

        const option = document.createElement('option');
        option.value = item.path;
        option.textContent = displayText;
        selectElement.appendChild(option);

        // Recursively add children
        if (item.children && item.children.length > 0) {
            buildLocationOptions(item.children, selectElement, item.path, level + 1);
        }
    });
}

/**
 * Handle create page form submission
 */
function handleCreatePage() {
    const form = document.getElementById('createPageForm');
    const errorDiv = document.getElementById('createPageError');
    const createBtn = document.getElementById('createPageBtn');

    // Hide previous errors
    errorDiv.style.display = 'none';

    // Get form data
    const formData = new FormData(form);

    // Validate
    if (!formData.get('filename')) {
        errorDiv.textContent = 'Please enter a page name';
        errorDiv.style.display = 'block';
        return;
    }

    // Disable button and show loading
    createBtn.disabled = true;
    createBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

    // Detect base URL from current page path
    const path = window.location.pathname;
    let baseUrl = '';
    const matches = path.match(/^(\/[^\/]+)/);
    if (matches && matches[1] !== '/index.php' && matches[1] !== '/version' && matches[1] !== '/edit') {
        baseUrl = matches[1];
    }

    // Submit form
    console.log('Creating page with data:', Object.fromEntries(formData));

    fetch(`${baseUrl}/api/create-page.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Create page response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Create page response text:', text);
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            throw new Error('Invalid JSON response: ' + text);
        }

        if (data.error) {
            // Show error
            errorDiv.textContent = data.error;
            errorDiv.style.display = 'block';
            createBtn.disabled = false;
            createBtn.innerHTML = '<i class="bi bi-plus-circle"></i> Create Page';
        } else if (data.success) {
            // Redirect to new page
            console.log('Redirecting to:', data.url);
            window.location.href = data.url;
        }
    })
    .catch(error => {
        console.error('Error creating page:', error);
        errorDiv.textContent = 'An error occurred while creating the page: ' + error.message;
        errorDiv.style.display = 'block';
        createBtn.disabled = false;
        createBtn.innerHTML = '<i class="bi bi-plus-circle"></i> Create Page';
    });
}

/**
 * Handle create directory form submission
 */
function handleCreateDirectory() {
    const form = document.getElementById('createDirectoryForm');
    const errorDiv = document.getElementById('createDirectoryError');
    const createBtn = document.getElementById('createDirectoryBtn');

    // Hide previous errors
    errorDiv.style.display = 'none';

    // Get form data
    const formData = new FormData(form);

    // Validate
    if (!formData.get('directory_name')) {
        errorDiv.textContent = 'Please enter a directory name';
        errorDiv.style.display = 'block';
        return;
    }

    // Disable button and show loading
    createBtn.disabled = true;
    createBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

    // Detect base URL from current page path
    const path = window.location.pathname;
    let baseUrl = '';
    const matches = path.match(/^(\/[^\/]+)/);
    if (matches && matches[1] !== '/index.php' && matches[1] !== '/version' && matches[1] !== '/edit') {
        baseUrl = matches[1];
    }

    // Submit form
    console.log('Creating directory with data:', Object.fromEntries(formData));

    fetch(`${baseUrl}/api/create-directory.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Create directory response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Create directory response text:', text);
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            throw new Error('Invalid JSON response: ' + text);
        }

        if (data.error) {
            // Show error
            errorDiv.textContent = data.error;
            errorDiv.style.display = 'block';
            createBtn.disabled = false;
            createBtn.innerHTML = '<i class="bi bi-plus-circle"></i> Create Directory';
        } else if (data.success) {
            // Reload page to show new directory in tree
            console.log('Directory created successfully, reloading...');
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error creating directory:', error);
        errorDiv.textContent = 'An error occurred while creating the directory: ' + error.message;
        errorDiv.style.display = 'block';
        createBtn.disabled = false;
        createBtn.innerHTML = '<i class="bi bi-plus-circle"></i> Create Directory';
    });
}

/**
 * Confirm and delete current page
 */
function confirmDeletePage() {
    // Get page information from window variables
    const version = window.currentVersion;
    const pagePath = window.currentPagePath;

    if (!version || !pagePath) {
        alert('Unable to determine page information');
        return;
    }

    // Extract page name from path for display
    const pageName = pagePath.split('/').pop();

    // Show confirmation dialog
    const confirmed = confirm(
        `Are you sure you want to delete this page?\n\n` +
        `Page: ${pageName}\n` +
        `Version: ${version}\n\n` +
        `This action cannot be undone.`
    );

    if (!confirmed) {
        return;
    }

    // Detect base URL from current page path
    const path = window.location.pathname;
    let baseUrl = '';
    const matches = path.match(/^(\/[^\/]+)/);
    if (matches && matches[1] !== '/index.php' && matches[1] !== '/version' && matches[1] !== '/edit') {
        baseUrl = matches[1];
    }

    // Prepare form data
    const formData = new FormData();
    formData.append('version', version);
    formData.append('page_path', pagePath);

    console.log('Deleting page:', version, pagePath);

    // Call delete API
    fetch(`${baseUrl}/api/delete-page.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Delete response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Delete response text:', text);
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            throw new Error('Invalid JSON response: ' + text);
        }

        if (data.error) {
            alert('Error deleting page: ' + data.error);
        } else if (data.success) {
            console.log('Page deleted successfully, redirecting to:', data.redirect);
            // Redirect to version overview
            window.location.href = data.redirect;
        }
    })
    .catch(error => {
        console.error('Error deleting page:', error);
        alert('An error occurred while deleting the page: ' + error.message);
    });
}

/**
 * Confirm and delete folder
 */
function confirmDeleteFolder(version, folderPath, event) {
    // Stop event propagation to prevent folder toggle
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    if (!version || !folderPath) {
        alert('Unable to determine folder information');
        return;
    }

    // Extract folder name from path for display
    const folderName = folderPath.split('/').pop();

    // Show confirmation dialog
    const confirmed = confirm(
        `Are you sure you want to delete this folder?\n\n` +
        `Folder: ${folderName}\n` +
        `Path: ${folderPath}\n` +
        `Version: ${version}\n\n` +
        `Note: The folder can only be deleted if it contains no markdown (.md) files.\n\n` +
        `This action cannot be undone.`
    );

    if (!confirmed) {
        return;
    }

    // Detect base URL from current page path
    const path = window.location.pathname;
    let baseUrl = '';
    const matches = path.match(/^(\/[^\/]+)/);
    if (matches && matches[1] !== '/index.php' && matches[1] !== '/version' && matches[1] !== '/edit') {
        baseUrl = matches[1];
    }

    // Prepare form data
    const formData = new FormData();
    formData.append('version', version);
    formData.append('directory_path', folderPath);

    console.log('Deleting folder:', version, folderPath);

    // Call delete API
    fetch(`${baseUrl}/api/delete-directory.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Delete folder response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Delete folder response text:', text);
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            throw new Error('Invalid JSON response: ' + text);
        }

        if (data.error) {
            alert('Error deleting folder: ' + data.error);
        } else if (data.success) {
            console.log('Folder deleted successfully, reloading page...');
            // Reload page to refresh the navigation tree
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error deleting folder:', error);
        alert('An error occurred while deleting the folder: ' + error.message);
    });
}
