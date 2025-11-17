// Documentation Framework JavaScript with Bootstrap

document.addEventListener('DOMContentLoaded', function() {
    // Initialize syntax highlighting
    if (typeof hljs !== 'undefined') {
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
