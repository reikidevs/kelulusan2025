<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect('/admin/login.php');
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $exam_number = clean_input($_POST['exam_number']);
    $nisn = clean_input($_POST['nisn']);
    $name = clean_input($_POST['name']);
    $class = clean_input($_POST['class']);
    $jurusan = clean_input($_POST['jurusan']);
    $birth_date = clean_input($_POST['birth_date']);
    $status = clean_input($_POST['status']);
    
    // Generate unique random password (10 karakter dengan kombinasi simbol)
    $password = generate_unique_password(10);
    
    // Validate input
    if (empty($exam_number) || empty($nisn) || empty($name) || empty($class) || empty($jurusan) || empty($birth_date)) {
        $error = 'Semua field harus diisi';
    } else {
        // Check if exam number already exists
        $check_sql = "SELECT * FROM students WHERE exam_number = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $exam_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Nomor ujian sudah terdaftar';
        } else {
            // Insert new student
            $sql = "INSERT INTO students (exam_number, password, nisn, name, class, jurusan, birth_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $exam_number, $password, $nisn, $name, $class, $jurusan, $birth_date, $status);
            
            if ($stmt->execute()) {
                set_flash_message('Data siswa berhasil ditambahkan', 'success');
                redirect('/admin/students.php');
            } else {
                $error = 'Gagal menambahkan data siswa: ' . $conn->error;
            }
        }
    }
}

// Page title
$page_title = 'Tambah Data Siswa';

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
                <li class="breadcrumb-item"><a href="<?php echo base_url('/admin/students.php'); ?>">Data Siswa</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Siswa</li>
            </ol>
        </nav>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Form Tambah Siswa</h5>
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
                        <input type="text" class="form-control" id="exam_number" name="exam_number" value="<?php echo isset($_POST['exam_number']) ? htmlspecialchars($_POST['exam_number']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nisn" name="nisn" value="<?php echo isset($_POST['nisn']) ? htmlspecialchars($_POST['nisn']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="class" class="form-label">Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="class" name="class" value="<?php echo isset($_POST['class']) ? htmlspecialchars($_POST['class']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="jurusan" class="form-label">Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="jurusan" name="jurusan" value="<?php echo isset($_POST['jurusan']) ? htmlspecialchars($_POST['jurusan']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="birth_date" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo isset($_POST['birth_date']) ? htmlspecialchars($_POST['birth_date']) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status Kelulusan <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="lulus" <?php echo (isset($_POST['status']) && $_POST['status'] === 'lulus') ? 'selected' : ''; ?>>Lulus</option>
                            <option value="tidak_lulus" <?php echo (isset($_POST['status']) && $_POST['status'] === 'tidak_lulus') ? 'selected' : ''; ?>>Tidak Lulus</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4">
                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo base_url('/admin/students.php'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                            <p class="text-muted small mt-3">Password akan digenerate otomatis dan ditampilkan setelah data disimpan.</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
