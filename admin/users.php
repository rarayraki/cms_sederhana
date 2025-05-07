<?php
require_once 'includes/header.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== $_SESSION['user_id']) { // Prevent self-deletion
        $conn->query("DELETE FROM users WHERE id = $id");
    }
    header("Location: users.php");
    exit();
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY username");
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Users</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#userModal">
                        <i class="fas fa-plus"></i> New User
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm edit-user" 
                                        data-id="<?php echo $user['id']; ?>"
                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                        data-fullname="<?php echo htmlspecialchars($user['full_name']); ?>"
                                        data-role="<?php echo $user['role']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">New User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="userForm" method="post">
                <div class="modal-body">
                    <input type="hidden" id="user_id" name="id">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="form-text text-muted">Leave blank to keep current password when editing</small>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="editor">Editor</option>
                            <option value="admin">Admin</option>
                        </select>
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
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: 'user_save.php',
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
                alert('An error occurred while saving the user.');
            }
        });
    });

    // Handle edit button click
    $('.edit-user').on('click', function() {
        var id = $(this).data('id');
        var username = $(this).data('username');
        var email = $(this).data('email');
        var fullname = $(this).data('fullname');
        var role = $(this).data('role');
        
        $('#user_id').val(id);
        $('#username').val(username);
        $('#email').val(email);
        $('#full_name').val(fullname);
        $('#role').val(role);
        $('#password').val('').prop('required', false);
        $('#userModalLabel').text('Edit User');
        $('#userModal').modal('show');
    });

    // Reset form when modal is closed
    $('#userModal').on('hidden.bs.modal', function() {
        $('#userForm')[0].reset();
        $('#user_id').val('');
        $('#password').prop('required', true);
        $('#userModalLabel').text('New User');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 