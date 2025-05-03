<?php
require_once '../includes/functions.php';

// Hanya superadmin yang boleh mengakses
if (!is_logged_in() || !is_superadmin()) {
    redirect('/kelulusan2025/admin/login.php');
}

$page_title = 'Kelola Akun Admin';
include '../includes/header.php';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle Add Admin
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = clean_input($_POST['name']);
        $username = clean_input($_POST['username']);
        $password = $_POST['password'];
        $role = clean_input($_POST['role']);
        
        // Validate input
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Nama tidak boleh kosong';
        }
        
        if (empty($username)) {
            $errors[] = 'Username tidak boleh kosong';
        } else {
            // Check if username already exists
            $check_sql = "SELECT id FROM users WHERE username = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = 'Username sudah digunakan';
            }
        }
        
        if (empty($password)) {
            $errors[] = 'Password tidak boleh kosong';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }
        
        // If no errors, insert new admin
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssss', $name, $username, $hashed_password, $role);
            
            if ($stmt->execute()) {
                set_flash_message('Admin berhasil ditambahkan', 'success');
                redirect('/kelulusan2025/admin/admins.php');
            } else {
                set_flash_message('Gagal menambahkan admin: ' . $conn->error, 'danger');
            }
        } else {
            // Set error messages
            set_flash_message(implode('<br>', $errors), 'danger');
        }
    }
    
    // Handle Edit Admin
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = clean_input($_POST['id']);
        $name = clean_input($_POST['name']);
        $username = clean_input($_POST['username']);
        $role = clean_input($_POST['role']);
        $password = $_POST['password']; // Only update if not empty
        
        // Validate input
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Nama tidak boleh kosong';
        }
        
        if (empty($username)) {
            $errors[] = 'Username tidak boleh kosong';
        } else {
            // Check if username already exists (excluding current user)
            $check_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param('si', $username, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = 'Username sudah digunakan';
            }
        }
        
        // If password provided, validate it
        if (!empty($password) && strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }
        
        // If no errors, update admin
        if (empty($errors)) {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET name = ?, username = ?, password = ?, role = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssi', $name, $username, $hashed_password, $role, $id);
            } else {
                $sql = "UPDATE users SET name = ?, username = ?, role = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sssi', $name, $username, $role, $id);
            }
            
            if ($stmt->execute()) {
                set_flash_message('Admin berhasil diperbarui', 'success');
                redirect('/kelulusan2025/admin/admins.php');
            } else {
                set_flash_message('Gagal memperbarui admin: ' . $conn->error, 'danger');
            }
        } else {
            // Set error messages
            set_flash_message(implode('<br>', $errors), 'danger');
        }
    }
    
    // Handle Delete Admin
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = clean_input($_POST['id']);
        
        // Cannot delete your own account
        if ($id == $_SESSION['user_id']) {
            set_flash_message('Anda tidak dapat menghapus akun Anda sendiri', 'danger');
            redirect('/kelulusan2025/admin/admins.php');
        }
        
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            set_flash_message('Admin berhasil dihapus', 'success');
        } else {
            set_flash_message('Gagal menghapus admin: ' . $conn->error, 'danger');
        }
        
        redirect('/kelulusan2025/admin/admins.php');
    }
}

// Get all admins
$sql = "SELECT id, name, username, role FROM users ORDER BY id DESC";
$result = $conn->query($sql);
$admins = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
}
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Kelola Akun Admin</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo base_url('/admin/'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kelola Admin</li>
            </ol>
        </nav>
    </div>
    
    <?php display_flash_message(); ?>
    
    <div class="card">
        <div class="card-body">
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="fas fa-plus me-1"></i>Tambah Admin
            </button>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admins)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data admin</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($admin['name']) ?></td>
                                    <td><?= htmlspecialchars($admin['username']) ?></td>
                                    <td>
                                        <?php if ($admin['role'] == 'superadmin'): ?>
                                            <span class="badge bg-danger">Super Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning edit-btn" 
                                                data-id="<?= $admin['id'] ?>"
                                                data-name="<?= htmlspecialchars($admin['name']) ?>"
                                                data-username="<?= htmlspecialchars($admin['username']) ?>"
                                                data-role="<?= $admin['role'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#editAdminModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        
                                        <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                    data-id="<?= $admin['id'] ?>"
                                                    data-name="<?= htmlspecialchars($admin['name']) ?>"
                                                    data-bs-toggle="modal" data-bs-target="#deleteAdminModal">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAdminModalLabel">Tambah Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAdminModalLabel">Edit Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Admin Modal -->
<div class="modal fade" id="deleteAdminModal" tabindex="-1" aria-labelledby="deleteAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAdminModalLabel">Hapus Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus admin <strong id="delete_name"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Handle edit button click
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_name').value = this.getAttribute('data-name');
            document.getElementById('edit_username').value = this.getAttribute('data-username');
            document.getElementById('edit_role').value = this.getAttribute('data-role');
            document.getElementById('edit_password').value = '';
        });
    });
    
    // Handle delete button click
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('delete_id').value = this.getAttribute('data-id');
            document.getElementById('delete_name').textContent = this.getAttribute('data-name');
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
