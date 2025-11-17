# Quick Installation Guide

## Step 1: Upload Files

Upload all files to your web server directory (e.g., `/var/www/html/docs/`).

## Step 2: Set Permissions

```bash
chmod 755 cache/
chmod 755 data/
```

## Step 3: Change Default Password

Edit `config/config.php` and change line 18:

```php
// OLD - default password
define('AUTH_PASSWORD', password_hash('admin123', PASSWORD_DEFAULT));

// NEW - your secure password
define('AUTH_PASSWORD', password_hash('your-secure-password', PASSWORD_DEFAULT));
```

## Step 4: Build Search Index

Run the search index builder:

```bash
php build-search-index.php
```

## Step 5: Test

Open your browser and navigate to your website:

```
http://your-domain.com/
```

You should see the homepage with available documentation versions.

## Step 6: Login

1. Click "Login" in the navigation
2. Username: `admin`
3. Password: `admin123` (or your custom password if you changed it)

## Default Credentials

**Username:** admin
**Password:** admin123

⚠️ **IMPORTANT:** Change the password in production!

## Troubleshooting

### Issue: "Page not found" errors

**Solution:** Ensure mod_rewrite is enabled:

```bash
# Apache
sudo a2enmod rewrite
sudo service apache2 restart
```

### Issue: Search not working

**Solution:**
1. Check SQLite3 is installed: `php -m | grep sqlite`
2. Ensure `data/` directory has write permissions
3. Run `php build-search-index.php`

### Issue: Cannot save edited pages

**Solution:**
1. Check `docs/` directory has write permissions
2. Ensure you're logged in
3. Check PHP error logs

## Next Steps

1. Add your own documentation to `docs/v1.0/` (or create new version folders)
2. Customize styling in `assets/style.css`
3. Update `SITE_TITLE` in `config/config.php`
4. Rebuild search index after adding new pages

For detailed documentation, see README.md
