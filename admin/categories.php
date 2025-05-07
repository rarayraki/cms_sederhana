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
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = 'Kategori berhasil dihapus';
    }
    $action = 'list';
}

// Handle Create/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $slug = createSlug($name);

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $slug, $description])) {
            $message = 'Kategori berhasil ditambahkan';
            $action = 'list';
        }
    } elseif ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        if ($stmt->execute([$name, $slug, $description, $id])) {
            $message = 'Kategori berhasil diperbarui';
            $action = 'list';
        }
    }
}

// Get Category for Edit
$category = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
}

// Get Categories for List
$categories = getCategories($pdo);

// Handle category deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM categories WHERE id = $id");
    header("Location: categories.php");
    exit();
}

// Get all categories
$allCategories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Categories</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#categoryModal">
                        <i class="fas fa-plus"></i> New Category
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = $allCategories->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                            <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                            <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm edit-category" 
                                        data-id="<?php echo $category['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                        data-description="<?php echo htmlspecialchars($category['description'] ?? ''); ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">
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

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">New Category</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="categoryForm" method="post">
                <div class="modal-body">
                    <input type="hidden" id="category_id" name="id">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle form submission
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: 'category_save.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while saving the category.');
            }
        });
    });

    // Handle edit button click
    $('.edit-category').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');
        
        $('#category_id').val(id);
        $('#name').val(name);
        $('#description').val(description);
        $('#categoryModalLabel').text('Edit Category');
        $('#categoryModal').modal('show');
    });

    // Reset form when modal is closed
    $('#categoryModal').on('hidden.bs.modal', function() {
        $('#categoryForm')[0].reset();
        $('#category_id').val('');
        $('#categoryModalLabel').text('New Category');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 