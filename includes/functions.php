<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get base URL for the application
 * 
 * @param string $path Path to append to base URL
 * @return string Complete URL
 */
function base_url($path = '') {
    // Include environment configuration if not already included
    if (!isset($_ENV['APP_URL'])) {
        require_once __DIR__ . '/../config/env.php';
    }
    
    // Get base URL from environment configuration
    $base_url = $_ENV['APP_ENV'] === 'development' ? '/kelulusan2025' : '';
    
    // Make sure path starts with a slash if not empty
    if (!empty($path) && substr($path, 0, 1) !== '/') {
        $path = '/' . $path;
    }
    
    // For empty path, normalize with trailing slash for consistency
    if (empty($path) || $path === '/') {
        return rtrim($base_url, '/') . '/';
    }
    
    // Return clean path without double slashes
    return rtrim($base_url, '/') . $path;
}

// Include database connection
require_once __DIR__ . '/../config/database.php';

/**
 * Get site configuration value
 * 
 * @param string $key Configuration key
 * @param mixed $default Default value if not found
 * @return mixed Configuration value
 */
function get_config($key, $default = '') {
    global $conn;
    
    $sql = "SELECT config_value FROM site_config WHERE config_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['config_value'];
    }
    
    return $default;
}

/**
 * Update site configuration
 * 
 * @param string $key Configuration key
 * @param mixed $value Configuration value
 * @return bool Success status
 */
function update_config($key, $value) {
    global $conn;
    
    $sql = "UPDATE site_config SET config_value = ? WHERE config_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $value, $key);
    
    return $stmt->execute();
}

/**
 * Check if user is logged in
 * 
 * @return bool Login status
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin or superadmin
 * 
 * @return bool Admin status
 */
function is_admin() {
    return isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'superadmin');
}

/**
 * Check if user is superadmin
 * 
 * @return bool Superadmin status
 */
function is_superadmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'superadmin';
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @param bool $use_base_url Whether to prepend base_url to the URL
 * @return void
 */
function redirect($url, $use_base_url = true) {
    if ($use_base_url) {
        $url = base_url($url);
    }
    header("Location: $url");
    exit;
}

/**
 * Display flash message
 * 
 * @return void
 */
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'info';
        
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>";
        echo $message;
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
        echo "</div>";
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Set flash message
 * 
 * @param string $message Message to display
 * @param string $type Message type (success, info, warning, danger)
 * @return void
 */
function set_flash_message($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Generate random password
 * 
 * @param int $length Length of password
 * @return string Random password
 */
function generate_random_password($length = 10) {
    // Karakter yang digunakan untuk password (huruf, angka, dan simbol)
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $symbols = '!@#$%&*-_+.'; // Simbol yang lebih sederhana dan tidak terlalu banyak
    
    // Pastikan panjang password minimal 10 karakter
    if ($length < 10) $length = 10;
    
    // Generate password dengan komposisi dominan huruf
    $password = [];
    
    // Pastikan password memiliki minimal:
    // - 1 huruf kapital
    // - 1 huruf kecil
    // - 1 angka
    // - 1 simbol (opsional, tapi direkomendasikan)
    $password[] = $uppercase[rand(0, strlen($uppercase) - 1)]; // Min 1 uppercase
    $password[] = $lowercase[rand(0, strlen($lowercase) - 1)]; // Min 1 lowercase
    $password[] = $numbers[rand(0, strlen($numbers) - 1)]; // Min 1 number
    $password[] = $symbols[rand(0, strlen($symbols) - 1)]; // Min 1 simbol
    
    // Untuk sisa karakter, dominan huruf (70% huruf, 20% angka, 10% simbol)
    $remaining_length = $length - count($password);
    
    for ($i = 0; $i < $remaining_length; $i++) {
        $rand = rand(1, 10);
        
        if ($rand <= 7) { // 70% huruf
            // 50-50 huruf besar dan kecil
            if (rand(0, 1) == 0) {
                $password[] = $uppercase[rand(0, strlen($uppercase) - 1)];
            } else {
                $password[] = $lowercase[rand(0, strlen($lowercase) - 1)];
            }
        } else if ($rand <= 9) { // 20% angka
            $password[] = $numbers[rand(0, strlen($numbers) - 1)];
        } else { // 10% simbol
            $password[] = $symbols[rand(0, strlen($symbols) - 1)];
        }
    }
    
    // Acak urutan karakter password
    shuffle($password);
    
    // Gabungkan array menjadi string
    return implode('', $password);
}

/**
 * Generate unique random password
 * 
 * @param int $length Length of password
 * @return string Unique random password
 */
function generate_unique_password($length = 10) {
    global $conn;
    
    // Coba hingga 10 kali untuk mendapatkan password unik
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $password = generate_random_password($length);
        
        // Periksa apakah password sudah ada di database
        $sql = "SELECT COUNT(*) as count FROM students WHERE password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Jika password unik, kembalikan
        if ($row['count'] == 0) {
            return $password;
        }
    }
    
    // Jika setelah 10 percobaan masih belum unik, tambahkan timestamp ke password
    return generate_random_password($length - 5) . substr(time(), -5);
}

/**
 * Verify student by exam number and password
 * 
 * @param string $exam_number Exam number to verify
 * @param string $password Password to verify
 * @return array|bool Student data or false if not found or password incorrect
 */
function verify_student($exam_number, $password = '') {
    global $conn;
    
    // If password is empty (old method), just check exam number
    if (empty($password)) {
        $sql = "SELECT * FROM students WHERE exam_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $exam_number);
    } else {
        // Check both exam number and password
        $sql = "SELECT * FROM students WHERE exam_number = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $exam_number, $password);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Check if announcement is active
 * 
 * @return bool Announcement status
 */
function is_announcement_active() {
    $active = get_config('announcement_active', 'false');
    return $active === 'true';
}

/**
 * Format date to Indonesian format
 * 
 * @param string $date Date to format
 * @return string Formatted date
 */
function format_tanggal_indo($date) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );

    $split = explode('-', $date);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

/**
 * Clean input data
 * 
 * @param string $data Data to clean
 * @return string Clean data
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Count total students
 * 
 * @return int Total students
 */
function count_students() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total FROM students";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    return $row['total'];
}

/**
 * Count students by status
 * 
 * @param string $status Status to count
 * @return int Total students
 */
function count_students_by_status($status) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total FROM students WHERE status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'];
}
?>
