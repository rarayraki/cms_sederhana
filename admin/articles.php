<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';

// Handle Delete
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = 'Artikel berhasil dihapus';
    }
    $action = 'list';
}

// Handle Create/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = $_POST['content'];
    $category_id = $_POST['category_id'];
    $status = $_POST['status'];
    $slug = createSlug($title);

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO articles (title, slug, content, category_id, user_id, status) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $slug, $content, $category_id, $_SESSION['user_id'], $status])) {
            $message = 'Artikel berhasil ditambahkan';
            $action = 'list';
        }
    } elseif ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE articles SET title = ?, slug = ?, content = ?, category_id = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$title, $slug, $content, $category_id, $status, $id])) {
            $message = 'Artikel berhasil diperbarui';
            $action = 'list';
        }
    }
}

// Get Categories for Form
$categories = getCategories($pdo);

// Get Article for Edit
$article = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
}

// Get Articles for List
$articles = getArticles($pdo);

// Handle article deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM articles WHERE id = $id");
    header("Location: articles.php");
    exit();
}

// Get all articles with category and author information
$articles = $conn->query("
    SELECT a.*, c.name as category_name, u.username as author_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    LEFT JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC
");
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Articles</h3>
                <div class="card-tools">
                    <a href="article_edit.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New Article
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($article = $articles->fetch_assoc()): ?>
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
                            <td>
                                <a href="article_edit.php?id=<?php echo $article['id']; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="articles.php?delete=<?php echo $article['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this article?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
    $(document).ready(function() {
        $('#content').summernote({
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script> 