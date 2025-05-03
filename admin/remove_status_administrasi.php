<?php
// Fix path for command line execution
$base_path = __DIR__ . '/..';

// Include functions file with absolute path
require_once $base_path . '/includes/functions.php';

// Check if running from command line or browser
$is_cli = (php_sapi_name() === 'cli');

// Only check login if running from browser
if (!$is_cli && (!is_logged_in() || !is_admin())) {
    redirect('/admin/login.php');
}

// Pastikan koneksi database tersedia
if (!isset($conn) || !$conn) {
    // Jika tidak ada koneksi di functions.php, buat koneksi baru
    require_once $base_path . '/config/database.php';
}

// Periksa apakah kolom status_administrasi ada dalam tabel students
$check_column_sql = "SHOW COLUMNS FROM students LIKE 'status_administrasi'";
$column_exists = $conn->query($check_column_sql)->num_rows > 0;

// Hapus kolom status_administrasi dari table students jika ada
if ($column_exists) {
    $sql = "ALTER TABLE students DROP COLUMN status_administrasi";
} else {
    if ($is_cli) {
        echo "Kolom status_administrasi tidak ditemukan dalam tabel students.\n";
    }
    exit(0); // Keluar dengan sukses jika kolom tidak ada
}

try {
    if ($conn->query($sql) === TRUE) {
        $message = 'Kolom status_administrasi berhasil dihapus dari database';
        if ($is_cli) {
            echo $message . "\n";
        } else {
            set_flash_message($message, 'success');
        }
    } else {
        $error = 'Gagal menghapus kolom status_administrasi: ' . $conn->error;
        if ($is_cli) {
            echo $error . "\n";
        } else {
            set_flash_message($error, 'danger');
        }
    }
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    if ($is_cli) {
        echo $error . "\n";
    } else {
        set_flash_message($error, 'danger');
    }
}

// Redirect hanya jika dari browser
if (!$is_cli) {
    redirect('/admin/students.php');
}
?>
