<?php
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center py-5">
            <div class="display-1 text-primary mb-4">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <h1 class="display-4 mb-3">404 - Page Not Found</h1>
            <p class="lead text-muted mb-4">
                The page you're looking for doesn't exist.
            </p>

            <?php if (!empty($path)): ?>
                <div class="alert alert-light border">
                    <strong>Requested Path:</strong>
                    <code><?= View::escape($path) ?></code>
                </div>
            <?php endif; ?>

            <a href="<?= Url::to('/') ?>" class="btn btn-primary btn-lg">
                <i class="bi bi-house"></i> Go to Home
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = '404 - Page Not Found';
$authenticated = Auth::isAuthenticated();
include 'layout.php';
?>
