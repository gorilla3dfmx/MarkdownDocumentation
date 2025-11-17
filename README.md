# Markdown Documentation Website Framework

A complete PHP-based documentation website framework with versioning support, markdown rendering, search functionality, and PDF export. **Built with Bootstrap 5** for a modern, professional look.

## Features

✅ **Multi-version Documentation** - Manage documentation for different versions of your software
✅ **Markdown Support** - Full markdown rendering with syntax highlighting
✅ **Hierarchical Structure** - Organize documentation in folders and subfolders
✅ **Search Functionality** - Full-text search with SQLite FTS5
✅ **PDF Export** - Export documentation pages or entire versions to PDF
✅ **Editing Mode** - Built-in markdown editor with live preview (login required)
✅ **Navigation Tree** - Interactive tree view for easy navigation
✅ **Responsive Design** - Mobile-friendly Bootstrap 5 interface
✅ **Syntax Highlighting** - Code blocks with Highlight.js
✅ **Subfolder Support** - Works in subdirectories with automatic base path detection
✅ **Bootstrap Icons** - Beautiful icons throughout the interface

## Requirements

- PHP 7.4 or higher
- SQLite3 extension (for search functionality)
- Apache with mod_rewrite (or nginx with similar configuration)
- Write permissions for `cache/` and `data/` directories

## Installation

1. **Upload Files**

   Upload all files to your web server directory.

2. **Configure Web Server**

   **For Apache:**
   - Ensure `.htaccess` is enabled and mod_rewrite is active
   - The framework automatically detects the base path (works in subfolders!)

   **For nginx**, add this configuration:

   ```nginx
   location /your-subfolder/ {
       try_files $uri $uri/ /your-subfolder/index.php?$query_string;
   }
   ```

   **Subfolder Support:**
   The framework automatically detects if it's running in a subfolder (e.g., `/doc/v3/`) and adjusts all URLs accordingly. No manual configuration needed!

3. **Set Permissions**

   ```bash
   chmod 755 cache/
   chmod 755 data/
   ```

4. **Configure Authentication**

   Edit `config/config.php` and change the default password:

   ```php
   define('AUTH_PASSWORD', password_hash('your-secure-password', PASSWORD_DEFAULT));
   ```

5. **Add Documentation**

   Create your documentation structure in the `docs/` directory:

   ```
   docs/
   ├── v1.0/
   │   ├── getting-started.md
   │   ├── basic-concepts.md
   │   └── advanced/
   │       └── features.md
   ├── v2.0/
   │   ├── whats-new.md
   │   └── api/
   │       └── reference.md
   ```

6. **Build Search Index**

   Create a script to build the initial search index:

   ```php
   <?php
   require_once 'config/config.php';
   require_once 'classes/DocumentationManager.php';
   require_once 'classes/MarkdownParser.php';
   require_once 'classes/SearchIndex.php';

   SearchIndex::indexAll();
   echo "Search index built successfully!\n";
   ```

## Directory Structure

```
.
├── index.php              # Main entry point
├── .htaccess             # Apache configuration
├── config/
│   └── config.php        # Configuration settings
├── classes/              # PHP classes
│   ├── Router.php
│   ├── Auth.php
│   ├── DocumentationManager.php
│   ├── MarkdownParser.php
│   ├── SearchIndex.php
│   └── *Controller.php
├── templates/            # View templates
│   ├── layout.php
│   ├── home.php
│   ├── page.php
│   ├── editor.php
│   └── search.php
├── assets/              # Static assets
│   ├── style.css
│   └── script.js
├── docs/                # Documentation files
│   ├── v1.0/
│   └── v2.0/
├── cache/               # Cache directory
└── data/                # Database files
```

## Usage

### Viewing Documentation

1. Navigate to the website root to see all available versions
2. Select a version to view its documentation tree
3. Click on any page to view its content
4. Use the search box to find specific content

### Editing Documentation

1. Click "Login" and enter credentials (default: admin/admin123)
2. Navigate to any documentation page
3. Click the "Edit" button
4. Make changes in the markdown editor
5. Click "Save Changes"

### Exporting to PDF

- Click "Export PDF" on any page to export that page
- Or use the export endpoint with multiple pages:
  ```
  /export/pdf?version=v1.0&pages[]=getting-started.md&pages[]=basic-concepts.md
  ```

### Search

1. Use the search box on any page
2. Optionally filter by version
3. Results show matching content with highlighted keywords

## Markdown Features

The framework supports all standard markdown features:

```markdown
# Headings (H1-H6)

**Bold text** and *italic text*

++Underlined text++

[Links](http://example.com)

![Images](image.jpg)

- Bullet lists
- Item 2

1. Numbered lists
2. Item 2

> Blockquotes

`Inline code`

```language
Code blocks with syntax highlighting
```

| Tables | Are | Supported |
|--------|-----|-----------|
| Cell 1 | 2   | 3         |

---

Horizontal rules
```

## Configuration Options

Edit `config/config.php` to customize:

- `SITE_TITLE` - Website title
- `AUTH_USERNAME` / `AUTH_PASSWORD` - Login credentials
- `ITEMS_PER_PAGE` - Search results per page
- `PDF_MARGIN_*` - PDF export margins

## Security

- **Authentication**: Change the default password in `config/config.php`
- **Directory Traversal**: Built-in protection against path traversal attacks
- **SQL Injection**: Uses PDO prepared statements
- **XSS Protection**: All output is escaped

## Customization

### Styling

The framework uses **Bootstrap 5** with custom CSS in `assets/style.css`. You can:

1. **Customize Bootstrap variables** - Add a custom Bootstrap build
2. **Override styles** - Edit `assets/style.css` for specific customizations
3. **Change colors** - Modify Bootstrap color classes in templates
4. **Add custom themes** - Use Bootstrap's theming capabilities

Example custom CSS:
```css
/* Override primary color */
.btn-primary {
    background-color: #your-color;
}
```

### Templates

Modify templates in `templates/` directory to change the layout and structure.

### Markdown Rendering

The `MarkdownParser` class in `classes/MarkdownParser.php` can be extended or replaced with libraries like:
- Parsedown
- CommonMark
- Michelf Markdown

## Performance Tips

1. **Enable OPcache** in production
2. **Use HTTP/2** for better asset loading
3. **Enable gzip compression** (already configured in .htaccess)
4. **Rebuild search index** after documentation updates
5. **Consider using Redis** for caching in high-traffic scenarios

## Troubleshooting

**Search not working:**
- Ensure SQLite3 extension is installed
- Check write permissions on `data/` directory
- Rebuild the search index

**Clean URLs not working:**
- Verify mod_rewrite is enabled
- Check .htaccess is being read
- Test with direct URLs like `/index.php/version/v1.0`

**PDF export issues:**
- The included SimplePDF is basic - consider installing mPDF or TCPDF for production use:
  ```bash
  composer require mpdf/mpdf
  ```

## License

This framework is provided as-is for documentation purposes. Feel free to modify and use it for your projects.

## Support

For issues or questions:
- Check the documentation
- Review the code comments
- Examine example files in `docs/`

---

**Version:** 1.0
**Last Updated:** 2025
