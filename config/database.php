<?php
// Database Configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "kelulusan2025";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exist
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select database
$conn->select_db($db_name);

// Create users table if not exists
$sql = "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'superadmin') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating users table: " . $conn->error);
}

// Create students table if not exists
$sql = "CREATE TABLE IF NOT EXISTS `students` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `exam_number` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(100) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `class` VARCHAR(50) NOT NULL,
    `jurusan` VARCHAR(100) NOT NULL,
    `status` ENUM('lulus', 'tidak_lulus') NOT NULL,
    `status_administrasi` TINYINT(1) DEFAULT 0 NOT NULL COMMENT '0=Belum Lunas, 1=Lunas',
    `nisn` VARCHAR(20) NOT NULL,
    `birth_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating students table: " . $conn->error);
}

// Create site_config table if not exists
$sql = "CREATE TABLE IF NOT EXISTS `site_config` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `config_key` VARCHAR(50) NOT NULL UNIQUE,
    `config_value` TEXT NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creating site_config table: " . $conn->error);
}

// Insert default site configuration if not exists
$default_configs = [
    ['site_title', 'Pengumuman Kelulusan SMK NU 1 Slawi'],
    ['announcement_date', date('Y-m-d')],
    ['announcement_active', 'false'],
    ['school_name', 'SMK NU 1 Slawi'],
    ['school_year', '2024/2025'],
    ['school_logo', 'logo/logo-skanu.png'],
    ['welcome_message', 'Selamat datang di sistem pengumuman kelulusan']
];

foreach ($default_configs as $config) {
    $key = $config[0];
    $value = $config[1];
    
    // Check if configuration already exists
    $check_sql = "SELECT * FROM `site_config` WHERE `config_key` = '$key'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows == 0) {
        // Insert default configuration
        $insert_sql = "INSERT INTO `site_config` (`config_key`, `config_value`) VALUES ('$key', '$value')";
        if ($conn->query($insert_sql) !== TRUE) {
            die("Error inserting default configuration: " . $conn->error);
        }
    }
}

// Create default superadmin account if not exists
$check_sql = "SELECT * FROM `users` WHERE `username` = 'superadmin'";
$result = $conn->query($check_sql);

if ($result->num_rows == 0) {
    // Insert default superadmin account
    $default_password = password_hash("admin123", PASSWORD_DEFAULT);
    $insert_sql = "INSERT INTO `users` (`username`, `password`, `name`, `role`) 
                   VALUES ('superadmin', '$default_password', 'Super Admin', 'superadmin')";
                   
    if ($conn->query($insert_sql) !== TRUE) {
        die("Error creating default superadmin account: " . $conn->error);
    }
}
?>
