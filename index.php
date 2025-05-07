<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$articles = getArticles($pdo, $limit, $offset);
$categories = getCategories($pdo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Sederhana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">CMS Sederhana</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Beranda</a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="category.php?slug=<?php echo $category['slug']; ?>">
                            <?php echo sanitize($category['name']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <?php foreach ($articles as $article): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title">
                            <a href="article.php?slug=<?php echo $article['slug']; ?>" class="text-decoration-none text-dark">
                                <?php echo sanitize($article['title']); ?>
                            </a>
                        </h2>
                        <p class="card-text text-muted">
                            <small>
                                <i class="bi bi-person"></i> <?php echo sanitize($article['author_name']); ?> |
                                <i class="bi bi-folder"></i> <?php echo sanitize($article['category_name']); ?> |
                                <i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($article['created_at'])); ?>
                            </small>
                        </p>
                        <p class="card-text">
                            <?php echo substr(strip_tags($article['content']), 0, 200) . '...'; ?>
                        </p>
                        <a href="article.php?slug=<?php echo $article['slug']; ?>" class="btn btn-primary">Baca Selengkapnya</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Kategori</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php foreach ($categories as $category): ?>
                            <li class="mb-2">
                                <a href="category.php?slug=<?php echo $category['slug']; ?>" class="text-decoration-none">
                                    <?php echo sanitize($category['name']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-6">
                    <h5>CMS Sederhana</h5>
                    <p>Sebuah sistem manajemen konten sederhana yang dibuat dengan PHP dan MySQL.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?php echo date('Y'); ?> CMS Sederhana. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 