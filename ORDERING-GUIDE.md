# Documentation Ordering Guide

This documentation system supports two methods for controlling the order of pages and folders:

1. **YAML Frontmatter** - for individual page ordering
2. **.order File** - for controlling the order of both files and folders in a directory

## Method 1: YAML Frontmatter (Pages Only)

Add frontmatter to the top of your markdown files to specify the order:

```markdown
---
order: 1
---

# Your Page Title

Your content here...
```

## Method 2: .order File (Files and Folders)

Create a `.order` file in any directory to control the order of its contents (both files and folders).

### .order File Format

Create a file named `.order` (no extension) in a directory with one item per line:

```
# Comments start with #
# List items in the order you want them to appear

Getting-Started
Core
Reference

# You can also include specific files
installation.md
quick-start.md
```

### .order File Rules

- One item name per line
- Use the exact filename or folder name (case-sensitive)
- Lines starting with `#` are comments
- Empty lines are ignored
- Items not listed will appear alphabetically after listed items
- Works for both folders and markdown files

## Frontmatter Format

- Frontmatter must be at the very beginning of the file
- Start and end with `---` on separate lines
- Use `order:` followed by a number (lower numbers appear first)
- Pages without an `order` field appear alphabetically after ordered pages
- **Frontmatter is automatically hidden** - it will not be displayed when viewing the page

## Priority Order

When both methods are used, the priority is:

1. **Frontmatter `order:`** in individual markdown files (highest priority)
2. **.order file** in the parent directory
3. **Alphabetical** sorting (default)

## Example Structures

### Example 1: Using .order File

**File structure:**
```
docs/v1.2/
  ├── .order
  ├── Getting-Started/
  ├── Core/
  ├── Reference/
  └── FAQ.md
```

**docs/v1.2/.order:**
```
Getting-Started
Core
FAQ.md
Reference
```

Result: Getting-Started → Core → FAQ.md → Reference

### Example 2: Using Frontmatter

**getting-started.md**
```markdown
---
order: 1
---

# Getting Started
```

**installation.md**
```markdown
---
order: 2
---

# Installation
```

### Example 3: Mixed Approach

**File structure:**
```
docs/v1.2/
  ├── .order (controls folder order)
  └── Getting-Started/
      ├── Installation.md (order: 1 in frontmatter)
      ├── Requirements.md (order: 2 in frontmatter)
      └── Pricing.md (no order, alphabetical)
```

**docs/v1.2/.order:**
```
Getting-Started
Core
Reference
```

This gives you full control: use `.order` for folder structure and frontmatter for fine-tuning page order within folders.

## Directory-Based Organization

The version overview page now displays:
- Pages grouped by their directory structure
- Top-level directories as section headers
- Nested directories as sub-sections
- Proper indentation for hierarchical structure

## Sorting Rules

1. **Explicit Order**: Pages with `order:` frontmatter appear first, sorted by order number
2. **Folders**: Directories appear before unordered pages
3. **Alphabetical**: Pages without `order:` appear alphabetically at the end

## Additional Frontmatter Fields

### Exclude from Export

You can exclude specific pages from PDF and ZIP exports using the `exclude_export` field:

```markdown
---
order: 1
exclude_export: true
---

# Internal Development Notes

This page will not be included in PDF or ZIP exports.
```

### Other Fields

You can add other fields for future use:

```markdown
---
order: 1
exclude_export: false
title: Custom Page Title
author: John Doe
date: 2024-01-15
tags: [tutorial, beginner]
---
```

**Supported fields:**
- `order` - Controls sorting order (integer)
- `exclude_export` - Excludes page from exports (true/false)

Other fields are stored but not currently used.

## Notes

- The order value can be any integer (positive or negative)
- Decimal values are converted to integers
- Pages with the same order value are sorted alphabetically
- Frontmatter is optional - pages work fine without it
