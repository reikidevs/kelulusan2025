<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect('/admin/login.php');
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Delete student
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        set_flash_message('Data siswa berhasil dihapus', 'success');
    } else {
        set_flash_message('Gagal menghapus data siswa', 'danger');
    }
    
    redirect(base_url('/admin/students.php'));
}

// Page title
$page_title = 'Kelola Data Siswa';

// Header
include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><?php echo $page_title; ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo base_url('/'); ?>">Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?php echo base_url('/admin/'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Data Siswa</li>
            </ol>
        </nav>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Daftar Siswa</h5>
            <div>
                <a href="add_student.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Tambah Siswa
                </a>
                <a href="import_excel.php" class="btn btn-success btn-sm ms-1">
                    <i class="fas fa-file-import me-1"></i> Import Data
                </a>
                <a href="export.php" class="btn btn-info btn-sm ms-1">
                    <i class="fas fa-file-export me-1"></i> Export Data
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan nama/nomor ujian" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="class" class="form-select">
                                <option value="">Semua Kelas</option>
                                <?php
                                // Get unique classes
                                $sql = "SELECT DISTINCT class FROM students ORDER BY class";
                                $result = $conn->query($sql);
                                
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $selected = (isset($_GET['class']) && $_GET['class'] === $row['class']) ? 'selected' : '';
                                        echo "<option value=\"{$row['class']}\" {$selected}>{$row['class']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="jurusan" class="form-select">
                                <option value="">Semua Jurusan</option>
                                <?php
                                // Get unique jurusan
                                $sql = "SELECT DISTINCT jurusan FROM students ORDER BY jurusan";
                                $result = $conn->query($sql);
                                
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $selected = (isset($_GET['jurusan']) && $_GET['jurusan'] === $row['jurusan']) ? 'selected' : '';
                                        echo "<option value=\"{$row['jurusan']}\" {$selected}>{$row['jurusan']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="lulus" <?php echo (isset($_GET['status']) && $_GET['status'] === 'lulus') ? 'selected' : ''; ?>>Lulus</option>
                                <option value="tidak_lulus" <?php echo (isset($_GET['status']) && $_GET['status'] === 'tidak_lulus') ? 'selected' : ''; ?>>Tidak Lulus</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal"><i class="fas fa-trash"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Students Table -->
            <div class="table-responsive">
                <table class="table table-hover data-table">
                    <thead>
                        <tr>
                            <th>No. Ujian</th>
                            <th>Password</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Tanggal Lahir</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Pagination settings
                        $items_per_page = 10;
                        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        if ($current_page < 1) $current_page = 1;
                        $offset = ($current_page - 1) * $items_per_page;
                        
                        // Build query with filters
                        $sql = "SELECT * FROM students WHERE 1=1";
                        $count_sql = "SELECT COUNT(*) as total FROM students WHERE 1=1";
                        $params = [];
                        $types = "";
                        
                        // Add search filter
                        if (isset($_GET['search']) && !empty($_GET['search'])) {
                            $search = '%' . $_GET['search'] . '%';
                            $where_clause = " AND (name LIKE ? OR exam_number LIKE ?)";
                            $sql .= $where_clause;
                            $count_sql .= $where_clause;
                            $params[] = $search;
                            $params[] = $search;
                            $types .= "ss";
                        }
                        
                        // Add class filter
                        if (isset($_GET['class']) && !empty($_GET['class'])) {
                            $where_clause = " AND class = ?";
                            $sql .= $where_clause;
                            $count_sql .= $where_clause;
                            $params[] = $_GET['class'];
                            $types .= "s";
                        }
                        
                        // Add jurusan filter
                        if (isset($_GET['jurusan']) && !empty($_GET['jurusan'])) {
                            $where_clause = " AND jurusan = ?";
                            $sql .= $where_clause;
                            $count_sql .= $where_clause;
                            $params[] = $_GET['jurusan'];
                            $types .= "s";
                        }
                        
                        // Add status filter
                        if (isset($_GET['status']) && !empty($_GET['status'])) {
                            $where_clause = " AND status = ?";
                            $sql .= $where_clause;
                            $count_sql .= $where_clause;
                            $params[] = $_GET['status'];
                            $types .= "s";
                        }
                        
                        // Get total count for pagination
                        $count_stmt = $conn->prepare($count_sql);
                        if (!empty($params)) {
                            $count_stmt->bind_param($types, ...$params);
                        }
                        $count_stmt->execute();
                        $count_result = $count_stmt->get_result()->fetch_assoc();
                        $total_items = $count_result['total'];
                        $total_pages = ceil($total_items / $items_per_page);
                        
                        // Sort order and add pagination
                        $sql .= " ORDER BY class, name LIMIT ?, ?";
                        $params[] = $offset;
                        $params[] = $items_per_page;
                        $types .= "ii";
                        
                        // Prepare and execute statement
                        $stmt = $conn->prepare($sql);
                        
                        if (!empty($params)) {
                            $stmt->bind_param($types, ...$params);
                        }
                        
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $row['exam_number'] . '</td>';
                                // Password with hide/show functionality
                                $password = isset($row['password']) ? $row['password'] : 'N/A';
                                echo '<td>
                                      <div class="password-field">
                                        <span class="password-hidden">••••••</span>
                                        <span class="password-visible d-none">' . $password . '</span>
                                        <button type="button" class="btn btn-sm btn-icon toggle-password">
                                          <i class="fas fa-eye"></i>
                                        </button>
                                      </div>
                                    </td>';
                                echo '<td>' . $row['nisn'] . '</td>';
                                echo '<td>' . $row['name'] . '</td>';
                                echo '<td>' . $row['class'] . '</td>';
                                $jurusan = isset($row['jurusan']) ? $row['jurusan'] : 'N/A';
                                echo '<td>' . $jurusan . '</td>';
                                echo '<td>' . format_tanggal_indo($row['birth_date']) . '</td>';
                                echo '<td><span class="badge ' . ($row['status'] === 'lulus' ? 'badge-lulus' : 'badge-tidak_lulus') . '">' . 
                                      ($row['status'] === 'lulus' ? 'Lulus' : 'Tidak Lulus') . '</span></td>';
                                echo '<td>
                                        <div class="btn-group" role="group">
                                            <a href="edit_student.php?id=' . $row['id'] . '" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" data-id="' . $row['id'] . '" data-name="' . htmlspecialchars($row['name']) . '" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#confirmationModal" data-action="delete" data-id="' . $row['id'] . '" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                      </td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="10" class="text-center">Tidak ada data siswa</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $items_per_page, $total_items); ?> dari <?php echo $total_items; ?> data
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <!-- Previous page link -->
                        <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo '?page=' . ($current_page - 1) . 
                                (isset($_GET['search']) && !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . 
                                (isset($_GET['class']) && !empty($_GET['class']) ? '&class=' . urlencode($_GET['class']) : '') . 
                                (isset($_GET['jurusan']) && !empty($_GET['jurusan']) ? '&jurusan=' . urlencode($_GET['jurusan']) : '') . 
                                (isset($_GET['status']) && !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''); ?>">
                                &laquo; Sebelumnya
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">&laquo; Sebelumnya</span>
                        </li>
                        <?php endif; ?>
                        
                        <!-- Page numbers -->
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            echo '<li class="page-item' . ($i == $current_page ? ' active' : '') . '">';
                            if ($i == $current_page) {
                                echo '<span class="page-link">' . $i . '</span>';
                            } else {
                                echo '<a class="page-link" href="?page=' . $i . 
                                    (isset($_GET['search']) && !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . 
                                    (isset($_GET['class']) && !empty($_GET['class']) ? '&class=' . urlencode($_GET['class']) : '') . 
                                    (isset($_GET['jurusan']) && !empty($_GET['jurusan']) ? '&jurusan=' . urlencode($_GET['jurusan']) : '') . 
                                    (isset($_GET['status']) && !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '') . 
                                    '">' . $i . '</a>';
                            }
                            echo '</li>';
                        }
                        ?>
                        
                        <!-- Next page link -->
                        <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo '?page=' . ($current_page + 1) . 
                                (isset($_GET['search']) && !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . 
                                (isset($_GET['class']) && !empty($_GET['class']) ? '&class=' . urlencode($_GET['class']) : '') . 
                                (isset($_GET['jurusan']) && !empty($_GET['jurusan']) ? '&jurusan=' . urlencode($_GET['jurusan']) : '') . 
                                (isset($_GET['status']) && !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''); ?>">
                                Selanjutnya &raquo;
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Selanjutnya &raquo;</span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['reset_password_data'])): ?>
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <strong>Password untuk siswa <?php echo htmlspecialchars($_SESSION['reset_password_data']['student_name']); ?> berhasil direset!</strong>
    <div class="mt-2">Password baru: <strong><?php echo $_SESSION['reset_password_data']['password']; ?></strong></div>
    <p class="mb-0 mt-2"><small>Harap catat password ini karena tidak akan ditampilkan lagi.</small></p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['reset_password_data']); endif; ?>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="confirmAction" class="btn btn-danger">Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">Hapus Data Berdasarkan Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Anda akan menghapus seluruh data siswa dengan filter:</p>
                <ul id="filterSummary" class="mb-3">
                    <!-- Filter summary will be inserted here via JavaScript -->
                </ul>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="confirmBulkDelete" class="btn btn-danger">Hapus Semua Data</button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notificationModalBody">
                <!-- Message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Konfirmasi Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Anda yakin ingin mereset password untuk siswa <strong id="studentName"></strong>?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i> Tindakan ini akan menghasilkan password baru secara acak dan tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="resetPasswordBtn" class="btn btn-warning">
                    <i class="fas fa-key me-1"></i> Reset Password
                </a>
            </div>
        </div>
    </div>
</div>

<!-- DataTables JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<style>
.password-field {
    display: flex;
    align-items: center;
    gap: 5px;
}
.btn-icon {
    padding: 0.2rem 0.5rem;
    font-size: 0.8rem;
}
</style>

<script>
// Toggle password visibility and handle confirmation modal
document.addEventListener('DOMContentLoaded', function() {
    // Reset Password Modal
    const resetPasswordModal = document.getElementById('resetPasswordModal');
    if (resetPasswordModal) {
        resetPasswordModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const studentId = button.getAttribute('data-id');
            const studentName = button.getAttribute('data-name');
            
            document.getElementById('studentName').textContent = studentName;
            document.getElementById('resetPasswordBtn').href = 'reset_password.php?id=' + studentId;
        });
    }
    
    // Password toggle functionality
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordField = this.closest('.password-field');
            const hiddenText = passwordField.querySelector('.password-hidden');
            const visibleText = passwordField.querySelector('.password-visible');
            const icon = this.querySelector('i');
            
            if (hiddenText.classList.contains('d-none')) {
                // Switch to hidden
                hiddenText.classList.remove('d-none');
                visibleText.classList.add('d-none');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                // Switch to visible
                hiddenText.classList.add('d-none');
                visibleText.classList.remove('d-none');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });
    
    // Bulk Delete functionality
    const bulkDeleteModal = document.getElementById('bulkDeleteModal');
    const filterSummary = document.getElementById('filterSummary');
    const confirmBulkDeleteBtn = document.getElementById('confirmBulkDelete');
    
    if (bulkDeleteModal) {
        bulkDeleteModal.addEventListener('show.bs.modal', function() {
            // Get current filter values
            const searchValue = document.querySelector('input[name="search"]').value.trim();
            const classValue = document.querySelector('select[name="class"]').value;
            const jurusanValue = document.querySelector('select[name="jurusan"]').value;
            const statusValue = document.querySelector('select[name="status"]').value;
            
            // Clear previous filter summary
            filterSummary.innerHTML = '';
            
            // Build filter summary list
            if (searchValue) {
                filterSummary.innerHTML += `<li>Pencarian: <strong>${searchValue}</strong></li>`;
            }
            
            if (classValue) {
                filterSummary.innerHTML += `<li>Kelas: <strong>${classValue}</strong></li>`;
            }
            
            if (jurusanValue) {
                filterSummary.innerHTML += `<li>Jurusan: <strong>${jurusanValue}</strong></li>`;
            }
            
            if (statusValue) {
                let statusText = statusValue === 'lulus' ? 'Lulus' : 'Tidak Lulus';
                filterSummary.innerHTML += `<li>Status: <strong>${statusText}</strong></li>`;
            }
            
            // If no filters, show 'All data'
            if (!searchValue && !classValue && !jurusanValue && !statusValue) {
                filterSummary.innerHTML = '<li><strong>Semua data siswa!</strong></li>';
            }
        });
        
        // Handle bulk delete confirmation
        if (confirmBulkDeleteBtn) {
            confirmBulkDeleteBtn.addEventListener('click', function() {
                // Get filter values
                const searchValue = document.querySelector('input[name="search"]').value.trim();
                const classValue = document.querySelector('select[name="class"]').value;
                const jurusanValue = document.querySelector('select[name="jurusan"]').value;
                const statusValue = document.querySelector('select[name="status"]').value;
                
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'bulk_delete.php';
                
                // Add filter values as hidden inputs
                const addInput = (name, value) => {
                    if (value) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        input.value = value;
                        form.appendChild(input);
                    }
                };
                
                addInput('search', searchValue);
                addInput('class', classValue);
                addInput('jurusan', jurusanValue);
                addInput('status', statusValue);
                
                // Add bulk delete flag
                const deleteFlag = document.createElement('input');
                deleteFlag.type = 'hidden';
                deleteFlag.name = 'bulk_delete';
                deleteFlag.value = '1';
                form.appendChild(deleteFlag);
                
                // Add CSRF token or any other security measures if needed
                
                // Submit the form
                document.body.appendChild(form);
                form.submit();
            });
        }
    }
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltips.length > 0) {
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }
    
    // Show notification modal for flash messages
    <?php if (isset($_SESSION['flash_message']) && !empty($_SESSION['flash_message'])): ?>
        const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
        const modalBody = document.getElementById('notificationModalBody');
        
        // Set message type and content
        const messageType = '<?php echo $_SESSION['flash_message_type']; ?>';
        const messageContent = '<?php echo $_SESSION['flash_message']; ?>';
        
        // Create alert div
        modalBody.innerHTML = `<div class="alert alert-${messageType} mb-0">${messageContent}</div>`;
        
        // Show modal
        notificationModal.show();
        
        // Clear flash message after displaying
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_message_type']); ?>
    <?php endif; ?>
    
    // Handle confirmation modal for delete action
    const confirmationModal = document.getElementById('confirmationModal');
    if (confirmationModal) {
        let actionType = '';
        let itemId = '';
        
        // When the modal is shown, update the action and ID
        confirmationModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            actionType = button.getAttribute('data-action');
            itemId = button.getAttribute('data-id');
        });
        
        // When the confirm button is clicked, perform the action
        const confirmButton = document.getElementById('confirmAction');
        if (confirmButton) {
            confirmButton.addEventListener('click', function() {
                if (actionType === 'delete' && itemId) {
                    // Redirect to the delete.php dengan base_url
                    const deleteUrl = '<?php echo base_url("/admin/delete.php"); ?>?id=' + itemId;
                    console.log('Redirecting to:', deleteUrl); // Debug untuk melihat URL yang dibentuk
                    window.location.href = deleteUrl;
                }
            });
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
