<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
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
 * @return void
 */
function redirect($url) {
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
 * Verify student by exam number
 * 
 * @param string $exam_number Exam number to verify
 * @return array|bool Student data or false if not found
 */
function verify_student($exam_number) {
    global $conn;
    
    $sql = "SELECT * FROM students WHERE exam_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $exam_number);
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
