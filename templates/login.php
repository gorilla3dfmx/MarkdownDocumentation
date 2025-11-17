<?php
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="card shadow-sm mt-5">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0 text-center">
                        <i class="bi bi-shield-lock"></i> Login
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error ?? false): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i>
                            <?= View::escape($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= Url::to('/login') ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>

                        <input type="hidden" name="redirect" value="<?= View::escape($_GET['redirect'] ?? '/') ?>">

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
