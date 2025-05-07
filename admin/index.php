<?php
require_once 'includes/header.php';

// Get total articles
$result = $conn->query("SELECT COUNT(*) as total FROM articles");
$total_articles = $result->fetch_assoc()['total'];

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
?>

<!-- Small boxes (Stat box) -->
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
        <div class="small-box bg-warning">
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

<!-- Recent Articles -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Articles</h3>
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
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 