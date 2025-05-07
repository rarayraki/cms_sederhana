<?php
require_once 'includes/header.php';

// Get total articles
$result = $conn->query("SELECT COUNT(*) as total FROM articles");
$total_articles = $result->fetch_assoc()['total'];

// Get total published articles
$result = $conn->query("SELECT COUNT(*) as total FROM articles WHERE status = 'published'");
$published_articles = $result->fetch_assoc()['total'];

// Get total categories
$result = $conn->query("SELECT COUNT(*) as total FROM categories");
$total_categories = $result->fetch_assoc()['total'];

// Get total users
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $result->fetch_assoc()['total'];

// Get recent articles
$recent_articles = $conn->query("
    SELECT a.*, c.name as category_name, u.username as author_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    LEFT JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");

// Get articles by category
$articles_by_category = $conn->query("
    SELECT c.name, COUNT(a.id) as total
    FROM categories c
    LEFT JOIN articles a ON c.id = a.category_id
    GROUP BY c.id
    ORDER BY total DESC
    LIMIT 5
");

// Get recent users
$recent_users = $conn->query("
    SELECT * FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $total_articles; ?></h3>
                        <p>Total Articles</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <a href="articles.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $published_articles; ?></h3>
                        <p>Published Articles</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="articles.php?status=published" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo $total_categories; ?></h3>
                        <p>Categories</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <a href="categories.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="users.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-7 connectedSortable">
                <!-- Recent Articles -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-newspaper mr-1"></i>
                            Recent Articles
                        </h3>
                        <div class="card-tools">
                            <a href="articles.php" class="btn btn-tool">
                                <i class="fas fa-list"></i> View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($article = $recent_articles->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($article['title']); ?></td>
                                    <td><?php echo htmlspecialchars($article['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $article['status'] == 'published' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($article['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($article['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Articles by Category -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-1"></i>
                            Articles by Category
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Total Articles</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($category = $articles_by_category->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td><?php echo $category['total']; ?></td>
                                        <td>
                                            <?php 
                                            $percentage = $total_articles > 0 ? round(($category['total'] / $total_articles) * 100, 1) : 0;
                                            echo $percentage . '%';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right col -->
            <section class="col-lg-5 connectedSortable">
                <!-- Recent Users -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            Recent Users
                        </h3>
                        <div class="card-tools">
                            <a href="users.php" class="btn btn-tool">
                                <i class="fas fa-list"></i> View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt mr-1"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="article_edit.php" class="btn btn-primary btn-block mb-2">
                                    <i class="fas fa-plus"></i> New Article
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal" data-target="#categoryModal">
                                    <i class="fas fa-plus"></i> New Category
                                </button>
                            </div>
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-info btn-block mb-2" data-toggle="modal" data-target="#userModal">
                                    <i class="fas fa-user-plus"></i> New User
                                </button>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <a href="../index.php" class="btn btn-secondary btn-block mb-2" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> View Site
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 