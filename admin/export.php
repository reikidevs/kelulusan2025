<?php
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect('/kelulusan2025/admin/login.php');
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data_siswa_' . date('Y-m-d') . '.csv');

// Create a file handle for output
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV header row
fputcsv($output, ['No. Ujian', 'NISN', 'Nama Siswa', 'Kelas', 'Tanggal Lahir', 'Status']);

// Get students data
$sql = "SELECT * FROM students ORDER BY class, name";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = ($row['status'] === 'lulus') ? 'lulus' : 'tidak_lulus';
        
        // Write row to CSV
        fputcsv($output, [
            $row['exam_number'],
            $row['nisn'],
            $row['name'],
            $row['class'],
            $row['birth_date'],
            $status
        ]);
    }
}

// Close the file handle
fclose($output);
exit;
?>
