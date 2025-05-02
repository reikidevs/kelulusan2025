<?php
// Include required files
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(base_url('/admin/login.php'));
}

// Validate request
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('ID siswa tidak valid', 'danger');
    redirect(base_url('/admin/students.php'));
}

$id = (int)$_GET['id'];

// Delete student
$sql = "DELETE FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    set_flash_message('Data siswa berhasil dihapus', 'success');
} else {
    set_flash_message('Gagal menghapus data siswa: ' . $conn->error, 'danger');
}

// Redirect back to students page
redirect('/admin/students.php');
?>
