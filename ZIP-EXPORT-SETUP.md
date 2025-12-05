# ZIP Export Setup Guide

The ZIP export functionality requires the PHP `zip` extension to be enabled.

## Current Status

The ZIP export feature has been implemented with the following components:

- ✓ Export controller method (ExportController::zip)
- ✓ Route configuration (/export/zip)
- ✓ User interface button on version pages
- ✓ Error handling for missing extension

## Enabling the ZIP Extension

### Windows

1. Locate your `php.ini` file (run `php --ini` to find it)
2. Open `php.ini` in a text editor
3. Find the line `;extension=zip` (it may have a semicolon at the start)
4. Remove the semicolon to uncomment it: `extension=zip`
5. Save the file
6. Restart your web server (Apache, IIS, etc.)

### Linux/Ubuntu

```bash
# Install the zip extension
sudo apt-get install php-zip

# Restart your web server
sudo systemctl restart apache2
# or for nginx with php-fpm
sudo systemctl restart php-fpm
```

### Verify Installation

Run this command to check if the zip extension is loaded:

```bash
php -m | grep zip
```

You should see `zip` in the output.

## Features

Once enabled, the ZIP export will:

- Export all `.md` (markdown) files from the selected version
- Preserve the complete folder hierarchy
- **Exclude** files and folders starting with a dot (e.g., `.order`, `.git`, `.gitignore`)
- Generate a filename like: `documentation-v1.2-2024-01-15.zip`
- Automatically download the ZIP file to the user's browser

## Usage

1. Navigate to any version page (e.g., `/version/v1.2`)
2. Click the "Download as ZIP" button
3. The ZIP file will be downloaded containing all documentation files

## File Structure in ZIP

The exported ZIP maintains the exact folder structure (excluding dot files):

```
documentation-v1.2-2024-01-15.zip
├── Getting-Started/
│   ├── Installation.md
│   ├── Requirements.md
│   └── Pricing.md
├── Core/
│   ├── Viewport.md
│   ├── Camera.md
│   └── Models/
│       ├── Animation.md
│       └── Custom-Meshes.md
└── Reference/
    └── Core/
        ├── Gorilla.Viewport.md
        └── Gorilla.Camera.md
```

**Note:** Files and folders starting with a dot (like `.order`, `.git`, `.gitignore`) are automatically excluded from the export.

## Technical Details

- **Controller**: `classes/ExportController.php` - `zip()` method
- **Route**: `/export/zip?version={version}`
- **Button**: `templates/version.php` - line 59-61
- **Extension Required**: PHP ZipArchive class
