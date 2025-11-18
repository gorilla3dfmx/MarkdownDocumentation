<?php
/**
 * Documentation Website Framework
 * Main entry point
 */

session_start();

// Configuration
require_once 'config/config.php';

// Autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize Router
$router = new Router();

// Define routes
$router->get('/', 'HomeController@index');
$router->get('/version/{version}', 'DocumentationController@version');
$router->get('/version/{version}/page/{path}', 'DocumentationController@page');
$router->get('/search', 'SearchController@index');
$router->get('/export/pdf', 'ExportController@pdf');

// Authentication routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Editor routes (protected)
$router->get('/edit/{version}/{path}', 'EditorController@edit');
$router->post('/edit/{version}/{path}', 'EditorController@save');
$router->post('/preview', 'EditorController@preview');

// Image management routes (protected)
$router->post('/upload-image', 'ImageController@upload');
$router->get('/images/list', 'ImageController@list');
$router->post('/images/delete', 'ImageController@delete');

// File management routes (protected)
$router->get('/api/directory-tree', 'FileManagerController@getDirectoryTree');
$router->get('/api/versions', 'FileManagerController@getVersions');
$router->post('/api/create-page', 'FileManagerController@createPage');
$router->post('/api/create-directory', 'FileManagerController@createDirectory');

// Dispatch request
$router->dispatch();
