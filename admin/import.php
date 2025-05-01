<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect('/kelulusan2025/admin/login.php');
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error uploading file. Please try again.';
    } else {
        // Check file type
        $file_info = pathinfo($file['name']);
        $extension = strtolower($file_info['extension']);
        
        if ($extension !== 'csv') {
            $error = 'Only CSV files are allowed.';
        } else {
            // Open the file
            $handle = fopen($file['tmp_name'], 'r');
            
            if ($handle !== false) {
                // Skip header row
                $header = fgetcsv($handle);
                
                // Prepare insert statement
                $sql = "INSERT INTO students (exam_number, nisn, name, class, birth_date, status) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                // Count successful imports
                $imported = 0;
                $skipped = 0;
                $conn->begin_transaction();
                
                try {
                    // Process each row
                    while (($data = fgetcsv($handle)) !== false) {
                        // Check if we have at least 6 columns
                        if (count($data) >= 6) {
                            $exam_number = trim($data[0]);
                            $nisn = trim($data[1]);
                            $name = trim($data[2]);
                            $class = trim($data[3]);
                            $birth_date = trim($data[4]);
                            $status = strtolower(trim($data[5]));
                            
                            // Validate status
                            if ($status !== 'lulus' && $status !== 'tidak_lulus') {
                                $status = 'tidak_lulus';
                            }
                            
                            // Check if exam number already exists
                            $check_sql = "SELECT id FROM students WHERE exam_number = ?";
                            $check_stmt = $conn->prepare($check_sql);
                            $check_stmt->bind_param("s", $exam_number);
                            $check_stmt->execute();
                            $check_result = $check_stmt->get_result();
                            
                            if ($check_result->num_rows > 0) {
                                // Skip existing records
                                $skipped++;
                            } else {
                                // Insert new record
                                $stmt->bind_param("ssssss", $exam_number, $nisn, $name, $class, $birth_date, $status);
                                $stmt->execute();
                                $imported++;
                            }
                        }
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    $success = "Import successful. Imported $imported records. Skipped $skipped existing records.";
                    
                } catch (Exception $e) {
                    // Rollback on error
                    $conn->rollback();
                    $error = 'Error importing data: ' . $e->getMessage();
                }
                
                // Close file
                fclose($handle);
            } else {
                $error = 'Error reading file.';
            }
        }
    }
}

// Page title
$page_title = 'Import Data Siswa';

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
                <li class="breadcrumb-item active" aria-current="page">Import Data</li>
            </ol>
        </nav>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Upload File CSV</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="csv_file" class="form-label">File CSV</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                            <div class="form-text">File CSV harus dalam format: nomor_ujian, nisn, nama, kelas, tanggal_lahir (YYYY-MM-DD), status (lulus/tidak_lulus)</div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/kelulusan2025/admin/students.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Upload dan Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Panduan Import</h5>
                </div>
                <div class="card-body">
                    <p>Format CSV yang dibutuhkan:</p>
                    <ol>
                        <li>Baris pertama harus header (akan diabaikan)</li>
                        <li>Kolom harus berisi:
                            <ul>
                                <li>Nomor Ujian</li>
                                <li>NISN</li>
                                <li>Nama Siswa</li>
                                <li>Kelas</li>
                                <li>Tanggal Lahir (format: YYYY-MM-DD)</li>
                                <li>Status Kelulusan (lulus/tidak_lulus)</li>
                            </ul>
                        </li>
                    </ol>
                    <hr>
                    <p>Contoh isi file CSV:</p>
                    <pre class="bg-light p-2 rounded">nomor_ujian,nisn,nama,kelas,tanggal_lahir,status
12345,1234567890,John Doe,XII-RPL-1,2000-01-15,lulus
67890,0987654321,Jane Doe,XII-RPL-2,2000-02-20,tidak_lulus</pre>
                    <hr>
                    <p>Catatan:</p>
                    <ul>
                        <li>Data dengan nomor ujian yang sudah ada akan dilewati</li>
                        <li>Status harus berisi "lulus" atau "tidak_lulus"</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
