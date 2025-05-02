<?php
require_once 'includes/functions.php';

$announcement_active = is_announcement_active();
$announcement_date = get_config('announcement_date', date('Y-m-d'));
$school_year = get_config('school_year', '2024/2025');
$welcome_message = get_config('welcome_message', 'Selamat datang di sistem pengumuman kelulusan');

$show_result = false;
$student = null;
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exam_number'])) {
    $exam_number = clean_input($_POST['exam_number']);
    $password = isset($_POST['password']) ? clean_input($_POST['password']) : '';
    
    // Check if announcement is active
    if (!$announcement_active) {
        $error = 'Pengumuman kelulusan belum dibuka!';
    } else {
        // Verify student
        $student = verify_student($exam_number, $password);
        
        if ($student) {
            $show_result = true;
        } else {
            $error = 'Nomor ujian atau password tidak valid!';
        }
    }
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero animated-bg">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="mb-3 fade-in" style="font-size: 3.2rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">Pengumuman Kelulusan</h1>
                <h2 class="fs-3 fw-normal text-white fade-in fade-in-delay-1"><?php echo $school_name; ?></h2>
                <div class="d-inline-block bg-dark bg-opacity-25 rounded-pill px-4 py-2 mb-3 fade-in fade-in-delay-2">
                    <p class="lead mb-0"><i class="fas fa-calendar-alt me-2"></i> Tahun Ajaran <?php echo $school_year; ?></p>
                </div>
                <div class="d-inline-block bg-dark bg-opacity-25 rounded-pill px-4 py-2 mb-3 fade-in fade-in-delay-2">
                    <p class="lead mb-0"><i class="fas fa-clock me-2"></i> Tanggal Pengumuman: <?php echo format_tanggal_indo($announcement_date); ?> pukul 18.00 WIB</p>
                </div>
                <?php if ($announcement_active): ?>
                <div class="alert bg-white text-nu d-inline-block mt-1 mb-4 pulse shadow-sm" style="border-left: 4px solid var(--gold);">
                    <i class="fas fa-bullhorn me-2"></i> Pengumuman kelulusan <strong>sudah dibuka</strong>
                </div>
                <div class="mt-3 fade-in fade-in-delay-3">
                    <a href="#cek-kelulusan" class="btn btn-lg btn-nu me-2 mb-2 mb-md-0 btn-shine">
                        <i class="fas fa-search me-2"></i> Cek Kelulusan
                    </a>
                    <a href="#tata-cara" class="btn btn-lg btn-outline-light mb-2 mb-md-0">
                        <i class="fas fa-info-circle me-2"></i> Tata Cara
                    </a>
                </div>
                <?php else: ?>
                <div class="alert bg-white text-dark d-inline-block mt-1 mb-4 pulse shadow-sm" style="border-left: 4px solid var(--warning);">
                    <i class="fas fa-clock me-2"></i> Pengumuman kelulusan akan dibuka pada <strong><?php echo format_tanggal_indo($announcement_date); ?> pukul 18.00 WIB</strong>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-5 d-none d-md-block text-center position-relative">
                <div class="position-relative">
                    <div class="position-absolute top-50 start-50 translate-middle" style="width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%; z-index: 1;"></div>
                    <img src="assets/images/logo/logo-skanu.png" class="img-fluid mx-auto float position-relative" alt="Logo SMK NU 1 Slawi" style="max-height: 300px; z-index: 2; filter: drop-shadow(0 5px 15px rgba(0,0,0,0.2));">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Box -->
<section id="cek-kelulusan" class="container">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="search-box shadow circle-bg fade-in">
                <h3 class="text-center mb-4 gradient-text">Cek Kelulusan Siswa</h3>
                
                <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center gap-3 bounce">
                    <i class="fas fa-exclamation-circle fa-2x"></i>
                    <div><?php echo $error; ?></div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showErrorModal('Nomor Ujian Tidak Valid', '<?php echo $error; ?>');
                    });
                </script>
                <?php endif; ?>
                
                <?php if ($show_result): ?>
                <!-- Show Result -->
                <?php if ($student['status_administrasi'] == 0): ?>
                <!-- Tampilkan peringatan jika status administrasi belum lunas -->
                <div class="result-card warning p-4 rounded-3 mb-4 shadow bounce">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        <h4 class="mb-2 mb-md-0"><?php echo $student['name']; ?></h4>
                        <span class="badge badge-warning py-2 px-3 rounded-pill">
                            ADMINISTRASI BELUM LUNAS
                        </span>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Nomor Ujian</p>
                            <p class="mb-0 fw-bold"><?php echo $student['exam_number']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">NISN</p>
                            <p class="mb-0 fw-bold"><?php echo $student['nisn']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Kelas</p>
                            <p class="mb-0 fw-bold"><?php echo $student['class']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Jurusan</p>
                            <p class="mb-0 fw-bold"><?php echo $student['jurusan']; ?></p>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3 mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Perhatian!</strong> Status administrasi Anda belum lunas. Silakan hubungi bagian administrasi sekolah untuk menyelesaikan kewajiban administrasi.
                    </div>
                    
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> Status kelulusan akan ditampilkan setelah Anda menyelesaikan kewajiban administrasi.
                    </div>
                </div>
                <?php else: ?>
                <!-- Tampilkan hasil kelulusan jika status administrasi sudah lunas -->
                <div class="result-card <?php echo $student['status'] === 'lulus' ? 'success' : 'failed'; ?> p-4 rounded-3 mb-4 shadow bounce">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        <h4 class="mb-2 mb-md-0"><?php echo $student['name']; ?></h4>
                        <span class="badge <?php echo $student['status'] === 'lulus' ? 'badge-lulus' : 'badge-tidak_lulus'; ?> py-2 px-3 rounded-pill">
                            <?php echo $student['status'] === 'lulus' ? 'LULUS' : 'TIDAK LULUS'; ?>
                        </span>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Nomor Ujian</p>
                            <p class="mb-0 fw-bold"><?php echo $student['exam_number']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">NISN</p>
                            <p class="mb-0 fw-bold"><?php echo $student['nisn']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Kelas</p>
                            <p class="mb-0 fw-bold"><?php echo $student['class']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Jurusan</p>
                            <p class="mb-0 fw-bold"><?php echo $student['jurusan']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Tanggal Lahir</p>
                            <p class="mb-0 fw-bold"><?php echo format_tanggal_indo($student['birth_date']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Status Administrasi</p>
                            <p class="mb-0 fw-bold"><span class="badge bg-success">LUNAS</span></p>
                        </div>
                    </div>
                    
                    <?php if ($student['status'] === 'lulus'): ?>
                    <div class="alert bg-nu text-white mt-3 mb-0">
                        <i class="fas fa-check-circle me-2"></i> Selamat! Anda dinyatakan <strong>LULUS</strong>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="fas fa-times-circle me-2"></i> Mohon maaf, Anda dinyatakan <strong>TIDAK LULUS</strong>
                    </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <button class="btn btn-outline-primary btn-print">
                            <i class="fas fa-print me-2"></i> Cetak Hasil
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                
                <!-- Search Form -->
                <form id="verification-form" method="POST" action="#cek-kelulusan" class="<?php echo $show_result ? 'd-none' : ''; ?>">
                    <div class="mb-3">
                        <label for="exam_number" class="form-label fw-bold"><i class="fas fa-id-card me-2 text-nu"></i>Nomor Ujian</label>
                        <div class="input-group">
                            <span class="input-group-text bg-nu text-white"><i class="fas fa-user-graduate"></i></span>
                            <input type="text" class="form-control" id="exam_number" name="exam_number" placeholder="Masukkan nomor ujian" required>
                        </div>
                        <div class="form-text">Masukkan nomor ujian siswa</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold"><i class="fas fa-key me-2 text-nu"></i>Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-nu text-white"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                            <span class="input-group-text toggle-password" toggle="#password" style="cursor: pointer;">
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                        <div class="form-text">Masukkan password yang telah diberikan oleh sekolah</div>
                    </div>
                    
                    <!-- Tombol alternatif jika di atas tidak muncul -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-lg" style="background-color: #126E51; color: white; padding: 15px 30px; font-weight: bold; font-size: 1.25rem;">
                            <i class="fas fa-search-plus me-2"></i> CEK KELULUSAN
                        </button>
                    </div>
                </form>
                
                <?php if ($show_result): ?>
                <div class="mt-4 text-center">
                    <a href="index.php#cek-kelulusan" class="btn btn-outline-nu">
                        <i class="fas fa-redo me-2"></i> Cek Nomor Lain
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Instructions -->
<section class="py-5 circle-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4 gradient-text">Tata Cara Pengecekan Kelulusan</h2>
                <p class="lead">Ikuti langkah berikut untuk memeriksa hasil kelulusan:</p>
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="col-md-4 col-sm-12 mb-4 fade-in">
                <div class="card h-100 shadow-sm border-0 card-hover">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-nu text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-id-card fa-2x"></i>
                        </div>
                        <h4>1. Siapkan Nomor Ujian</h4>
                        <p>Pastikan Anda memiliki nomor ujian yang benar sebelum melakukan pengecekan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12 mb-4 fade-in fade-in-delay-1">
                <div class="card h-100 shadow-sm border-0 card-hover">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-nu text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-search fa-2x"></i>
                        </div>
                        <h4>2. Masukkan Nomor Ujian</h4>
                        <p>Masukkan nomor ujian pada form di atas dan klik tombol "Cek Hasil Kelulusan"</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12 mb-4 fade-in fade-in-delay-2">
                <div class="card h-100 shadow-sm border-0 card-hover">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-nu text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                        <h4>3. Lihat Hasil</h4>
                        <p>Hasil kelulusan akan ditampilkan beserta informasi data siswa</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Print Section (Hidden on screen, shown on print) -->
<?php if ($show_result): ?>
<section class="d-none print-only">
    <div class="container py-4">
        <div class="text-center mb-4">
            <h2>HASIL PENGUMUMAN KELULUSAN</h2>
            <h3><?php echo $school_name; ?></h3>
            <p>Tahun Ajaran <?php echo $school_year; ?></p>
        </div>
        
        <div class="card border p-4">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12 text-center mb-4">
                        <h4><?php echo $student['status'] === 'lulus' ? 'LULUS' : 'TIDAK LULUS'; ?></h4>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Nomor Ujian:</strong></p>
                        <p><?php echo $student['exam_number']; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>NISN:</strong></p>
                        <p><?php echo $student['nisn']; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Nama Lengkap:</strong></p>
                        <p><?php echo $student['name']; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Kelas:</strong></p>
                        <p><?php echo $student['class']; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Tanggal Lahir:</strong></p>
                        <p><?php echo format_tanggal_indo($student['birth_date']); ?></p>
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-md-6 offset-md-6 text-center">
                        <p>Slawi, <?php echo format_tanggal_indo(date('Y-m-d')); ?></p>
                        <p>Kepala Sekolah</p>
                        <br><br><br>
                        <p><strong>.........................</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<div id="alert-container" class="notification"></div>

<?php include 'includes/footer.php'; ?>
