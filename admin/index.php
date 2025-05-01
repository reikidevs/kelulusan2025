<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect('/kelulusan2025/admin/login.php');
}

// Count stats
$total_students = count_students();
$total_passed = count_students_by_status('lulus');
$total_failed = count_students_by_status('tidak_lulus');

// Get announcement status
$announcement_active = is_announcement_active();

// Page title
$page_title = 'Dashboard Admin';

// Header
include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><?php echo $page_title; ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/kelulusan2025/">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <div class="dashboard-icon bg-primary text-white">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4><?php echo $total_students; ?></h4>
                    <p class="text-muted">Total Siswa</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <div class="dashboard-icon bg-success text-white">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4><?php echo $total_passed; ?></h4>
                    <p class="text-muted">Siswa Lulus</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <div class="dashboard-icon bg-danger text-white">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h4><?php echo $total_failed; ?></h4>
                    <p class="text-muted">Siswa Tidak Lulus</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <div class="dashboard-icon <?php echo $announcement_active ? 'bg-success' : 'bg-warning'; ?> text-white">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h4><?php echo $announcement_active ? 'Aktif' : 'Tidak Aktif'; ?></h4>
                    <p class="text-muted">Status Pengumuman</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Menu Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="students.php" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 p-3 w-100">
                                <i class="fas fa-users"></i> Kelola Data Siswa
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="add_student.php" class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2 p-3 w-100">
                                <i class="fas fa-user-plus"></i> Tambah Siswa Baru
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="import.php" class="btn btn-outline-info d-flex align-items-center justify-content-center gap-2 p-3 w-100">
                                <i class="fas fa-file-import"></i> Import Data Siswa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Students -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Siswa Terbaru</h5>
            <a href="students.php" class="btn btn-sm btn-primary">Lihat Semua</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Ujian</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get recent students
                        $sql = "SELECT * FROM students ORDER BY id DESC LIMIT 5";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $row['exam_number'] . '</td>';
                                echo '<td>' . $row['name'] . '</td>';
                                echo '<td>' . $row['class'] . '</td>';
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
                            echo '<tr><td colspan="5" class="text-center">Tidak ada data siswa</td></tr>';
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

<?php include '../includes/footer.php'; ?>
