<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect('/admin/login.php');
}

// Check if this is a POST request and bulk_delete flag is set
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['bulk_delete'])) {
    set_flash_message('Akses tidak sah', 'danger');
    redirect('/admin/students.php');
}

// Get filter values
$search = isset($_POST['search']) ? clean_input($_POST['search']) : '';
$class = isset($_POST['class']) ? clean_input($_POST['class']) : '';
$jurusan = isset($_POST['jurusan']) ? clean_input($_POST['jurusan']) : '';
$status = isset($_POST['status']) ? clean_input($_POST['status']) : '';

// Build the DELETE query with filters
$sql = "DELETE FROM students WHERE 1=1";
$params = [];
$types = "";

// Add search filter
if (!empty($search)) {
    $search = '%' . $search . '%';
    $sql .= " AND (name LIKE ? OR exam_number LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

// Add class filter
if (!empty($class)) {
    $sql .= " AND class = ?";
    $params[] = $class;
    $types .= "s";
}

// Add jurusan filter
if (!empty($jurusan)) {
    $sql .= " AND jurusan = ?";
    $params[] = $jurusan;
    $types .= "s";
}

// Add status filter
if (!empty($status)) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

// Prepare and execute the statement
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Execute and get count of deleted rows
if ($stmt->execute()) {
    $deleted_count = $stmt->affected_rows;
    
    if ($deleted_count > 0) {
        set_flash_message($deleted_count . ' data siswa berhasil dihapus', 'success');
    } else {
        set_flash_message('Tidak ada data siswa yang dihapus', 'info');
    }
} else {
    set_flash_message('Gagal menghapus data siswa: ' . $conn->error, 'danger');
}

// Redirect back to students page
redirect('/admin/students.php');
