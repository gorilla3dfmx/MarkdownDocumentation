<?php
/**
 * Image Controller - handles image uploads and management
 */
class ImageController {

    public function upload($params) {
        Auth::requireAuth();

        header('Content-Type: application/json');

        if (!isset($_FILES['image'])) {
            echo json_encode(['success' => false, 'error' => 'No image uploaded']);
            exit;
        }

        $file = $_FILES['image'];

        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
            exit;
        }

        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 5MB.']);
            exit;
        }

        // Create images directory if it doesn't exist
        $imagesDir = BASE_PATH . '/data/images';
        if (!file_exists($imagesDir)) {
            mkdir($imagesDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_', true) . '.' . $extension;
        $targetPath = $imagesDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo json_encode(['success' => false, 'error' => 'Failed to save image']);
            exit;
        }

        // Return success with image path
        $imagePath = 'data/images/' . $filename;
        echo json_encode([
            'success' => true,
            'path' => $imagePath,
            'filename' => $filename
        ]);
        exit;
    }

    public function list($params) {
        Auth::requireAuth();

        header('Content-Type: application/json');

        $imagesDir = BASE_PATH . '/data/images';
        $images = [];

        if (file_exists($imagesDir)) {
            $files = scandir($imagesDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;

                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $images[] = [
                        'filename' => $file,
                        'path' => 'data/images/' . $file,
                        'size' => filesize($imagesDir . '/' . $file),
                        'modified' => filemtime($imagesDir . '/' . $file)
                    ];
                }
            }

            // Sort by modified time, newest first
            usort($images, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
        }

        echo json_encode(['success' => true, 'images' => $images]);
        exit;
    }

    public function delete($params) {
        Auth::requireAuth();

        header('Content-Type: application/json');

        $filename = $_POST['filename'] ?? '';

        if (empty($filename)) {
            echo json_encode(['success' => false, 'error' => 'No filename provided']);
            exit;
        }

        // Security: prevent directory traversal
        $filename = basename($filename);
        $filePath = BASE_PATH . '/data/images/' . $filename;

        if (!file_exists($filePath)) {
            echo json_encode(['success' => false, 'error' => 'Image not found']);
            exit;
        }

        if (unlink($filePath)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete image']);
        }
        exit;
    }
}
