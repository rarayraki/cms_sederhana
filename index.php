<?php
require_once 'config/database.php';

// Get categories for navigation
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Get articles
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where = "WHERE status = 'published'";
if ($category_id) {
    $where .= " AND category_id = $category_id";
}
if ($search) {
    $search = $conn->real_escape_string($search);
    $where .= " AND (title LIKE '%$search%' OR content LIKE '%$search%')";
}

$articles = $conn->query("
    SELECT a.*, c.name as category_name, u.username as author_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    LEFT JOIN users u ON a.author_id = u.id 
    $where
    ORDER BY a.created_at DESC 
    LIMIT $offset, $per_page
");

// Get total articles for pagination
$total_result = $conn->query("SELECT COUNT(*) as total FROM articles $where");
$total_articles = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_articles / $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Sederhana</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        .article-card {
            margin-bottom: 20px;
        }
        .article-meta {
            color: #6c757d;
            font-size: 0.9em;
        }
        .article-content {
            margin-top: 10px;
        }
        .article-content img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
            <div class="container">
                <a href="index.php" class="navbar-brand">
                    <span class="brand-text font-weight-light">CMS Sederhana</span>
                </a>

                <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse order-3" id="navbarCollapse">
                    <!-- Left navbar links -->
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link">Home</a>
                        </li>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                        <li class="nav-item">
                            <a href="index.php?category=<?php echo $category['id']; ?>" class="nav-link">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>

                    <!-- Right navbar links -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a href="login.php" class="nav-link">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Articles</h1>
                        </div>
                        <div class="col-sm-6">
                            <form action="" method="get" class="form-inline float-right">
                                <?php if ($category_id): ?>
                                <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                                <?php endif; ?>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="content">
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <?php if ($articles->num_rows > 0): ?>
                                <?php while ($article = $articles->fetch_assoc()): ?>
                                <div class="card article-card">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="article.php?slug=<?php echo $article['slug']; ?>" class="text-dark">
                                                <?php echo htmlspecialchars($article['title']); ?>
                                            </a>
                                        </h5>
                                        <div class="article-meta">
                                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($article['category_name'] ?? 'Uncategorized'); ?> |
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($article['author_name']); ?> |
                                            <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                        </div>
                                        <div class="article-content">
                                            <?php 
                                            $content = strip_tags($article['content']);
                                            echo strlen($content) > 300 ? substr($content, 0, 300) . '...' : $content;
                                            ?>
                                        </div>
                                        <a href="article.php?slug=<?php echo $article['slug']; ?>" class="btn btn-primary btn-sm mt-2">Read More</a>
                                    </div>
                                </div>
                                <?php endwhile; ?>

                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $category_id ? '&category='.$category_id : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">Previous</a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_id ? '&category='.$category_id : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $category_id ? '&category='.$category_id : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>">Next</a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info">No articles found.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 1.0.0
            </div>
            <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#">CMS Sederhana</a>.</strong> All rights reserved.
        </footer>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html> 