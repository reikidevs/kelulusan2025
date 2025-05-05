<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect('/admin/login.php');
}

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('ID siswa tidak valid', 'danger');
    redirect('/admin/students.php');
}

$id = (int)$_GET['id'];

// Get student data
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash_message('Data siswa tidak ditemukan', 'danger');
    redirect('/admin/students.php');
}

$student = $result->fetch_assoc();

// Generate new password
$password = generate_unique_password(10);

// Update student password
$update_sql = "UPDATE students SET password = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("si", $password, $id);

if ($update_stmt->execute()) {
    // Success
    $_SESSION['reset_password_data'] = [
        'student_name' => $student['name'],
        'password' => $password
    ];
    set_flash_message('Password siswa berhasil di-reset', 'success');
    redirect('/admin/students.php');
} else {
    // Error
    set_flash_message('Gagal me-reset password siswa: ' . $conn->error, 'danger');
    redirect('/admin/students.php');
}
?>
