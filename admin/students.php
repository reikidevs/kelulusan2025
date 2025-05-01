<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect('/kelulusan2025/admin/login.php');
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
    
    redirect('/kelulusan2025/admin/students.php');
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
                <li class="breadcrumb-item"><a href="/kelulusan2025/">Beranda</a></li>
                <li class="breadcrumb-item"><a href="/kelulusan2025/admin/">Dashboard</a></li>
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
                <a href="import.php" class="btn btn-success btn-sm ms-1">
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
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan nama/nomor ujian" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
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
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="lulus" <?php echo (isset($_GET['status']) && $_GET['status'] === 'lulus') ? 'selected' : ''; ?>>Lulus</option>
                                <option value="tidak_lulus" <?php echo (isset($_GET['status']) && $_GET['status'] === 'tidak_lulus') ? 'selected' : ''; ?>>Tidak Lulus</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
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
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tanggal Lahir</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Build query with filters
                        $sql = "SELECT * FROM students WHERE 1=1";
                        $params = [];
                        $types = "";
                        
                        // Apply search filter
                        if (isset($_GET['search']) && !empty($_GET['search'])) {
                            $search = '%' . $_GET['search'] . '%';
                            $sql .= " AND (name LIKE ? OR exam_number LIKE ?)";
                            $params[] = $search;
                            $params[] = $search;
                            $types .= "ss";
                        }
                        
                        // Apply class filter
                        if (isset($_GET['class']) && !empty($_GET['class'])) {
                            $sql .= " AND class = ?";
                            $params[] = $_GET['class'];
                            $types .= "s";
                        }
                        
                        // Apply status filter
                        if (isset($_GET['status']) && !empty($_GET['status'])) {
                            $sql .= " AND status = ?";
                            $params[] = $_GET['status'];
                            $types .= "s";
                        }
                        
                        $sql .= " ORDER BY class, name";
                        
                        // Prepare and execute query
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
                                echo '<td>' . $row['nisn'] . '</td>';
                                echo '<td>' . $row['name'] . '</td>';
                                echo '<td>' . $row['class'] . '</td>';
                                echo '<td>' . format_tanggal_indo($row['birth_date']) . '</td>';
                                echo '<td><span class="badge ' . ($row['status'] === 'lulus' ? 'badge-lulus' : 'badge-tidak_lulus') . '">' . 
                                      ($row['status'] === 'lulus' ? 'Lulus' : 'Tidak Lulus') . '</span></td>';
                                echo '<td>
                                        <a href="edit_student.php?id=' . $row['id'] . '" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#confirmationModal" data-action="delete" data-id="' . $row['id'] . '" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                      </td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7" class="text-center">Tidak ada data siswa</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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

<!-- DataTables JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<?php include '../includes/footer.php'; ?>
