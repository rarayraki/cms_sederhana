<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$slug = $_GET['slug'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get Category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    header("HTTP/1.0 404 Not Found");
    echo "Kategori tidak ditemukan";
    exit();
}

// Get Articles by Category
$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name, u.username as author_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    LEFT JOIN users u ON a.user_id = u.id 
    WHERE a.category_id = ? AND a.status = 'published' 
    ORDER BY a.created_at DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(1, $category['id'], PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get Categories for Sidebar
$categories = getCategories($pdo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($category['name']); ?> - CMS Sederhana</title>
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
                    <?php foreach ($categories as $cat): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $cat['id'] === $category['id'] ? 'active' : ''; ?>" href="category.php?slug=<?php echo $cat['slug']; ?>">
                            <?php echo sanitize($cat['name']); ?>
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
                <h1 class="mb-4"><?php echo sanitize($category['name']); ?></h1>
                <?php if ($category['description']): ?>
                    <p class="lead mb-4"><?php echo sanitize($category['description']); ?></p>
                <?php endif; ?>

                <?php if (count($articles) > 0): ?>
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
                <?php else: ?>
                    <div class="alert alert-info">
                        Belum ada artikel dalam kategori ini.
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Kategori</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php foreach ($categories as $cat): ?>
                            <li class="mb-2">
                                <a href="category.php?slug=<?php echo $cat['slug']; ?>" class="text-decoration-none <?php echo $cat['id'] === $category['id'] ? 'fw-bold' : ''; ?>">
                                    <?php echo sanitize($cat['name']); ?>
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