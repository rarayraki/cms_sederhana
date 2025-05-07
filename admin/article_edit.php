<?php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = null;
$message = '';

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result->fetch_assoc();
}

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id'] ?: null;
    $status = $_POST['status'];
    $slug = strtolower(str_replace(' ', '-', $title));

    if ($id > 0) {
        // Update existing article
        $stmt = $conn->prepare("UPDATE articles SET title = ?, content = ?, category_id = ?, status = ?, slug = ? WHERE id = ?");
        $stmt->bind_param("ssisss", $title, $content, $category_id, $status, $slug, $id);
    } else {
        // Create new article
        $stmt = $conn->prepare("INSERT INTO articles (title, content, category_id, status, slug, author_id) VALUES (?, ?, ?, ?, ?, ?)");
        $author_id = $_SESSION['user_id'];
        $stmt->bind_param("ssisss", $title, $content, $category_id, $status, $slug, $author_id);
    }

    if ($stmt->execute()) {
        $message = "Article " . ($id > 0 ? "updated" : "created") . " successfully!";
        if ($id == 0) {
            header("Location: article_edit.php?id=" . $stmt->insert_id);
            exit();
        }
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?php echo $id > 0 ? 'Edit Article' : 'New Article'; ?></h3>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($article['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="draft" <?php echo ($article['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($article['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="articles.php" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summernote -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
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

<?php require_once 'includes/footer.php'; ?> 