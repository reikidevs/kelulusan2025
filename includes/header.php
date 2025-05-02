<?php
require_once __DIR__ . '/functions.php';
$site_title = get_config('site_title', 'Pengumuman Kelulusan SMK NU 1 Slawi');
$school_name = get_config('school_name', 'SMK NU 1 Slawi');
$school_logo = get_config('school_logo', 'logo/logo-skanu.png');

// Menentukan base URL secara dinamis
$base_url = '';
$server_name = $_SERVER['SERVER_NAME'] ?? '';

// Jika di development (localhost atau IP lokal)
if ($server_name == 'localhost' || $server_name == '127.0.0.1' || strpos($server_name, '192.168.') === 0) {
    $base_url = '/kelulusan2025';
}

// Untuk memastikan base_url memiliki trailing slash jika tidak kosong
if (!empty($base_url) && substr($base_url, -1) !== '/') {
    $base_url .= '/';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Pengumuman Kelulusan SMK NU 1 Slawi - Cek status kelulusan siswa secara online">
    <meta name="keywords" content="kelulusan, SMK NU 1 Slawi, pengumuman, siswa">
    <meta name="author" content="SMK NU 1 Slawi">
    <title><?php echo $site_title; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/animations.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-nu shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_url; ?>">
                <img src="<?php echo $base_url; ?>assets/images/logo/logo-skanu.png" alt="<?php echo $school_name; ?>" class="school-logo me-2">
                <span class="fw-bold text-white"><?php echo $school_name; ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>#cek-kelulusan">Cek Kelulusan</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <?php if (is_admin() || is_superadmin()): ?>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>admin/"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <?php endif; ?>
                            <?php if (is_superadmin()): ?>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>admin/admins.php"><i class="fas fa-user-shield me-2"></i>Kelola Admin</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>admin/settings.php"><i class="fas fa-cogs me-2"></i>Pengaturan Website</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $base_url; ?>admin/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <main class="pb-4 pt-0" style="margin-top: -1px;">
        <div class="container flash-container">
            <?php display_flash_message(); ?>
        </div>
