<?php
require_once '../includes/functions.php';
require '../vendor/autoload.php'; // Autoload PhpSpreadsheet library

// Import PhpSpreadsheet classes
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect(base_url('/admin/login.php'));
}

$page_title = 'Import Data Siswa';
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error uploading file. Please try again.';
    } else {
        // Check file type
        $file_info = pathinfo($file['name']);
        $extension = strtolower($file_info['extension']);
        
        if ($extension !== 'xlsx' && $extension !== 'xls') {
            $error = 'Only Excel files (xlsx, xls) are allowed.';
        } else {
            try {
                // Load the Excel file
                $spreadsheet = IOFactory::load($file['tmp_name']);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                
                // Get the header row and skip it
                $header = array_shift($rows); // Removes and returns the first row (header)
                
                // Prepare insert statement
                $sql = "INSERT INTO students (exam_number, password, nisn, name, class, jurusan, birth_date, status, status_administrasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                // Count successful imports
                $imported = 0;
                $skipped = 0;
                $conn->begin_transaction();
                
                // Process each row
                foreach ($rows as $row) {
                    // Check if we have valid data (at least 7 columns for all required fields)
                    // Format: No Ujian, NISN, Nama, Kelas, Jurusan, Tanggal Lahir, Status, Status Administrasi
                    if (count($row) >= 7 && !empty($row[0])) { // Make sure we have at least the exam number
                        $exam_number = trim($row[0]);
                        // Password will be generated automatically
                        $password = generate_random_password();
                        $nisn = trim($row[1]);
                        $name = trim($row[2]);
                        $class = trim($row[3]);
                        $jurusan = trim($row[4]);
                        
                        // Convert birth date from DD/MM/YYYY to YYYY-MM-DD
                        $birth_date_input = trim($row[5]);
                        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $birth_date_input, $matches)) {
                            // Get day, month, year from the matches
                            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                            $year = $matches[3];
                            $birth_date = "$year-$month-$day";
                        } else {
                            // Assume it's already in the correct format or handle error
                            $birth_date = $birth_date_input;
                        }
                        
                        $status = strtolower(trim($row[6]));
                        
                        // Status administrasi (optional, default to 0)
                        $status_administrasi = 0; // Default to 'Belum Lunas'
                        if (isset($row[7])) {
                            $admin_status = strtolower(trim($row[7]));
                            if ($admin_status === 'lunas' || $admin_status === '1' || $admin_status === 'true') {
                                $status_administrasi = 1;
                            }
                        }
                        
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
                            $stmt->bind_param("ssssssssi", $exam_number, $password, $nisn, $name, $class, $jurusan, $birth_date, $status, $status_administrasi);
                            $stmt->execute();
                            $imported++;
                        }
                    }
                }
                
                // Commit transaction
                $conn->commit();
                $success = "Import successful. Imported $imported records. Skipped $skipped existing records. Password otomatis digenerate untuk semua siswa baru.";
                
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $error = 'Error importing data: ' . $e->getMessage();
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><?php echo $page_title; ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo base_url('/admin/index.php'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo base_url('/admin/students.php'); ?>">Data Siswa</a></li>
                <li class="breadcrumb-item active" aria-current="page">Import Data</li>
            </ol>
        </nav>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Import Data Siswa dari Excel</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <h6>Petunjuk Import:</h6>
                    <ol>
                        <li>Download template Excel <a href="<?php echo base_url('/admin/template_import.xlsx'); ?>" download>di sini</a></li>
                        <li>Isi data sesuai format yang tersedia</li>
                        <li>Kolom yang wajib diisi: No. Ujian, NISN, Nama Siswa, Kelas, Jurusan, Tanggal Lahir, Status</li>
                        <li>Nilai Status: "lulus" atau "tidak_lulus"</li>
                        <li>Nilai Status Administrasi: "0" untuk "Belum Lunas", "1" untuk "Lunas"</li>
                        <li>Password akan digenerate otomatis untuk setiap siswa baru</li>
                        <li>Upload file yang sudah diisi</li>
                    </ol>
                </div>

                <div class="mb-3">
                    <label for="excel_file" class="form-label">File Excel (xlsx, xls)</label>
                    <input type="file" class="form-control" id="excel_file" name="excel_file" required accept=".xlsx,.xls">
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?php echo base_url('/admin/students.php'); ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
