<?php
/**
 * Configuration file
 */

// Base paths
define('BASE_PATH', dirname(__DIR__));
define('DOCS_PATH', BASE_PATH . '/docs');
define('CACHE_PATH', BASE_PATH . '/cache');
define('TEMPLATES_PATH', BASE_PATH . '/templates');

// Base URL - Set this to your subfolder path (e.g., '/doc/v3' or '' for root)
// Auto-detect if not set
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', $scriptPath !== '/' ? $scriptPath : '');

// Database for search index (SQLite)
define('DB_PATH', BASE_PATH . '/data/search.db');

// Authentication
define('AUTH_USERNAME', 'admin');
define('AUTH_PASSWORD', password_hash('admin123', PASSWORD_DEFAULT)); // Change this!

// Application settings
define('SITE_TITLE', 'Gorilla3D Framework - Documentation');
define('ITEMS_PER_PAGE', 20);

// AI exports (llms.txt via build-llms.php, Claude skill via "Export for AI")
define('PROJECT_TITLE', 'Gorilla3D Framework Documentation');
define('PROJECT_SUMMARY',
    'Gorilla3D is a 3D rendering and game development add-on framework for Embarcadero Delphi ' .
    'FireMonkey (FMX), written in Object Pascal. It replaces the FMX 3D viewport with a multi-pass ' .
    'renderer (TGorillaViewport) and adds node-based and PBR materials, shadows and post-processing ' .
    'render passes, model import (glTF, FBX, OBJ, DAE, STL, ...), skeletal animation, physics, particles, ' .
    'terrain, audio, scripting and gaming components such as dialogue, inventory, skill system and multiplayer arena.');
define('PROJECT_NOTES', [
    'Supported IDE: Delphi 10.1.1 up to Delphi 13; target platforms Windows, Linux, Android and macOS.',
    'Gorilla3D components must be placed on a TGorillaViewport; TForm3D and TViewport3D are not supported.',
    'Core types are prefixed with `TGorilla`, units are named `Gorilla.<Subsystem>.<Component>`.',
    'Pages under "Reference" document the public API per unit; the other sections are guides.',
]);

// Claude skill: name prefix (lowercase, digits, hyphens; version is appended, e.g. gorilla3d-v1-5)
// and the "when to use" sentence of the skill description
define('SKILL_NAME', 'gorilla3d');
define('SKILL_TRIGGER',
    'Use when writing, reviewing or explaining Delphi FireMonkey code that uses Gorilla3D ' .
    '(TGorilla* classes, Gorilla.* units, TGorillaViewport, materials, render passes, models, animation, ' .
    'physics, particles, terrain, audio, scripting, gaming components), or when answering questions ' .
    'about Gorilla3D features, installation, platforms and licensing.');

// PDF settings
define('PDF_MARGIN_LEFT', 15);
define('PDF_MARGIN_RIGHT', 15);
define('PDF_MARGIN_TOP', 16);
define('PDF_MARGIN_BOTTOM', 16);

// Create necessary directories
$dirs = [CACHE_PATH, dirname(DB_PATH)];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}
