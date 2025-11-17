<?php
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="text-center mb-4">
                <h1 class="display-5 fw-bold">
                    <i class="bi bi-search"></i> Search Documentation
                </h1>
            </div>

            <!-- Search Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="<?= Url::to('/search') ?>">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" name="q" class="form-control" value="<?= View::escape($query) ?>" placeholder="Search documentation..." autofocus>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="version" class="form-select form-select-lg">
                                    <option value="">All Versions</option>
                                    <?php foreach ($versions as $v): ?>
                                        <option value="<?= View::escape($v['name']) ?>" <?= $v['name'] === $selectedVersion ? 'selected' : '' ?>>
                                            <?= View::escape($v['display']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Results -->
            <?php if (!empty($query)): ?>
                <div class="mb-3">
                    <p class="text-muted">
                        Found <strong><?= $total ?></strong> result<?= $total !== 1 ? 's' : '' ?>
                        <?php if ($selectedVersion): ?>
                            in <strong><?= View::escape($selectedVersion) ?></strong>
                        <?php endif; ?>
                    </p>
                </div>

                <?php if (empty($results)): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i>
                        No results found for "<strong><?= View::escape($query) ?></strong>".
                    </div>
                <?php else: ?>
                    <div class="list-group mb-4">
                        <?php foreach ($results as $result): ?>
                            <a href="<?= View::escape($result['url']) ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?= View::escape($result['title']) ?></h5>
                                    <small class="text-muted">
                                        <span class="badge bg-primary"><?= View::escape($result['version']) ?></span>
                                    </small>
                                </div>
                                <p class="mb-1 text-muted small">
                                    <i class="bi bi-file-text"></i> <?= View::escape($result['page_path']) ?>
                                </p>
                                <div class="result-snippet small">
                                    <?= $result['snippet'] ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Search results pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= Url::to('/search?q=' . urlencode($query) . '&version=' . urlencode($selectedVersion) . '&page=' . ($page - 1)) ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <li class="page-item disabled">
                                    <span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span>
                                </li>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= Url::to('/search?q=' . urlencode($query) . '&version=' . urlencode($selectedVersion) . '&page=' . ($page + 1)) ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
