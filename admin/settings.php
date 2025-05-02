<?php
require_once '../includes/functions.php';

// Hanya superadmin yang boleh mengakses
if (!is_logged_in() || !is_superadmin()) {
    redirect('/admin/login.php');
}

$page_title = 'Pengaturan Website';
include '../includes/header.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update site title
    if (isset($_POST['site_title'])) {
        $site_title = clean_input($_POST['site_title']);
        update_config('site_title', $site_title);
    }
    
    // Update school name
    if (isset($_POST['school_name'])) {
        $school_name = clean_input($_POST['school_name']);
        update_config('school_name', $school_name);
    }
    
    // Update school year
    if (isset($_POST['school_year'])) {
        $school_year = clean_input($_POST['school_year']);
        update_config('school_year', $school_year);
    }
    
    // Update welcome message
    if (isset($_POST['welcome_message'])) {
        $welcome_message = clean_input($_POST['welcome_message']);
        update_config('welcome_message', $welcome_message);
    }
    
    // Update announcement date
    if (isset($_POST['announcement_date'])) {
        $announcement_date = clean_input($_POST['announcement_date']);
        update_config('announcement_date', $announcement_date);
    }
    
    // Update announcement active status
    $announcement_active = isset($_POST['announcement_active']) ? 'true' : 'false';
    update_config('announcement_active', $announcement_active);
    
    // Set flash message
    set_flash_message('Pengaturan berhasil disimpan', 'success');
    
    // Redirect to refresh
    redirect('/admin/settings.php');
}

// Get current configurations
$site_title = get_config('site_title');
$school_name = get_config('school_name');
$school_year = get_config('school_year');
$welcome_message = get_config('welcome_message');
$announcement_date = get_config('announcement_date');
$announcement_active = get_config('announcement_active') === 'true';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Pengaturan Website</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo base_url('/admin/'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
            </ol>
        </nav>
    </div>
    
    <?php display_flash_message(); ?>
    
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Pengaturan Umum</h5>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="row mb-3">
                    <label for="site_title" class="col-sm-3 col-form-label">Judul Website</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="site_title" name="site_title" value="<?php echo htmlspecialchars($site_title); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="school_name" class="col-sm-3 col-form-label">Nama Sekolah</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="school_year" class="col-sm-3 col-form-label">Tahun Ajaran</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="school_year" name="school_year" value="<?php echo htmlspecialchars($school_year); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="welcome_message" class="col-sm-3 col-form-label">Pesan Selamat Datang</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="welcome_message" name="welcome_message" rows="3"><?php echo htmlspecialchars($welcome_message); ?></textarea>
                    </div>
                </div>
                
                <hr>
                
                <div class="row mb-3">
                    <label for="announcement_date" class="col-sm-3 col-form-label">Tanggal Pengumuman</label>
                    <div class="col-sm-9">
                        <input type="date" class="form-control" id="announcement_date" name="announcement_date" value="<?php echo htmlspecialchars($announcement_date); ?>" required>
                        <div class="form-text">Tanggal ini akan ditampilkan di halaman depan sebagai tanggal pengumuman kelulusan.</div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-9 offset-sm-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="announcement_active" name="announcement_active" <?php echo $announcement_active ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="announcement_active">Aktifkan Pengumuman</label>
                        </div>
                        <div class="form-text text-danger">Ketika diaktifkan, pengumuman kelulusan akan dapat diakses oleh siswa.</div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Simpan Pengaturan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
