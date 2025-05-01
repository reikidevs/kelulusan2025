<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect('/kelulusan2025/admin/login.php');
}

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('ID siswa tidak valid', 'danger');
    redirect('/kelulusan2025/admin/students.php');
}

$id = (int)$_GET['id'];
$error = '';

// Get student data
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash_message('Data siswa tidak ditemukan', 'danger');
    redirect('/kelulusan2025/admin/students.php');
}

$student = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $exam_number = clean_input($_POST['exam_number']);
    $nisn = clean_input($_POST['nisn']);
    $name = clean_input($_POST['name']);
    $class = clean_input($_POST['class']);
    $birth_date = clean_input($_POST['birth_date']);
    $status = clean_input($_POST['status']);
    
    // Validate input
    if (empty($exam_number) || empty($nisn) || empty($name) || empty($class) || empty($birth_date)) {
        $error = 'Semua field harus diisi';
    } else {
        // Check if exam number already exists (excluding current student)
        $check_sql = "SELECT * FROM students WHERE exam_number = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $exam_number, $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Nomor ujian sudah terdaftar';
        } else {
            // Update student data
            $update_sql = "UPDATE students SET exam_number = ?, nisn = ?, name = ?, class = ?, birth_date = ?, status = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssssi", $exam_number, $nisn, $name, $class, $birth_date, $status, $id);
            
            if ($update_stmt->execute()) {
                set_flash_message('Data siswa berhasil diperbarui', 'success');
                redirect('/kelulusan2025/admin/students.php');
            } else {
                $error = 'Gagal memperbarui data siswa: ' . $conn->error;
            }
        }
    }
}

// Page title
$page_title = 'Edit Data Siswa';

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
                <li class="breadcrumb-item"><a href="/kelulusan2025/admin/students.php">Data Siswa</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Siswa</li>
            </ol>
        </nav>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Form Edit Siswa</h5>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="exam_number" class="form-label">Nomor Ujian <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="exam_number" name="exam_number" value="<?php echo htmlspecialchars($student['exam_number']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nisn" name="nisn" value="<?php echo htmlspecialchars($student['nisn']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="class" class="form-label">Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="class" name="class" value="<?php echo htmlspecialchars($student['class']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="birth_date" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($student['birth_date']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status Kelulusan <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="lulus" <?php echo ($student['status'] === 'lulus') ? 'selected' : ''; ?>>Lulus</option>
                            <option value="tidak_lulus" <?php echo ($student['status'] === 'tidak_lulus') ? 'selected' : ''; ?>>Tidak Lulus</option>
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/kelulusan2025/admin/students.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
