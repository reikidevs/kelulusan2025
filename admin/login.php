<?php
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/admin/');
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        // Check user in database
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                // Semua role (admin/superadmin) diarahkan ke dashboard admin
                redirect('/admin/');
            } else {
                $error = 'Username atau Password salah';
            }
        } else {
            $error = 'Username tidak ditemukan';
        }
    }
}

$site_title = get_config('site_title', 'Pengumuman Kelulusan SMK NU 1 Slawi');
$school_name = get_config('school_name', 'SMK NU 1 Slawi');
$school_logo = get_config('school_logo', 'logo/logo-skanu.png');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - <?php echo $site_title; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_url('/assets/css/style.css'); ?>">
</head>
<body class="bg-light">
    <div class="container">
        <div class="login-container card shadow">
            <div class="text-center mb-4">
                <h3>Login Admin</h3>
                <a href="<?php echo base_url('/'); ?>">
                <img src="<?php echo base_url('/assets/images/logo/logo-skanu.png'); ?>" alt="<?php echo $school_name; ?>" class="school-logo me-2">
                </a>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        <span class="input-group-text toggle-password" toggle="#password" style="cursor: pointer;">
                            <i class="fa fa-eye"></i>
                        </span>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i> Masuk
                    </button>
                    <a href="<?php echo base_url('/'); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i> Kembali ke Beranda
                    </a>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> <?php echo $school_name; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (untuk beberapa fungsionalitas) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo base_url('/assets/js/script.js'); ?>"></script>
</body>
</html>
