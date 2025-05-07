<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$slug = $_GET['slug'] ?? '';

// Get Article
$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    LEFT JOIN users u ON a.user_id = u.id 
    WHERE a.slug = ? AND a.status = 'published'
");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    header("HTTP/1.0 404 Not Found");
    echo "Artikel tidak ditemukan";
    exit();
}

// Get Categories for Sidebar
$categories = getCategories($pdo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($article['title']); ?> - CMS Sederhana</title>
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
                        <a class="nav-link" href="index.php">Beranda</a>
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
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="category.php?slug=<?php echo $article['category_slug']; ?>"><?php echo sanitize($article['category_name']); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo sanitize($article['title']); ?></li>
                    </ol>
                </nav>

                <article>
                    <h1 class="mb-3"><?php echo sanitize($article['title']); ?></h1>
                    <p class="text-muted">
                        <small>
                            <i class="bi bi-person"></i> <?php echo sanitize($article['author_name']); ?> |
                            <i class="bi bi-folder"></i> <a href="category.php?slug=<?php echo $article['category_slug']; ?>" class="text-decoration-none"><?php echo sanitize($article['category_name']); ?></a> |
                            <i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($article['created_at'])); ?>
                        </small>
                    </p>
                    <div class="content">
                        <?php echo $article['content']; ?>
                    </div>
                </article>
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