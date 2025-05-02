<?php
require_once '../includes/functions.php';
require '../vendor/autoload.php'; // Autoload PhpSpreadsheet library

// Import PhpSpreadsheet classes
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Check if user is logged in
if (!is_logged_in() || !is_admin()) {
    redirect(base_url('/admin/login.php'));
}

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Siswa');

// Set headers with styling
$headers = ['No. Ujian', 'Password', 'NISN', 'Nama Siswa', 'Kelas', 'Jurusan', 'Tanggal Lahir', 'Status', 'Status Administrasi'];
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
];

// Apply headers
foreach ($headers as $colIndex => $header) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
    $sheet->setCellValue($colLetter . '1', $header);
}
$sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

// Auto size columns for better readability
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Get students data
$sql = "SELECT * FROM students ORDER BY class, name";
$result = $conn->query($sql);

// Data styling
$dataBorderStyle = [
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
];

if ($result->num_rows > 0) {
    $rowIndex = 2; // Start from row 2 (after the header)
    
    while ($row = $result->fetch_assoc()) {
        $status = ($row['status'] === 'lulus') ? 'Lulus' : 'Tidak Lulus';
        $administrasi = isset($row['status_administrasi']) && $row['status_administrasi'] == 1 ? 'Lunas' : 'Belum Lunas';
        
        // Write data to Excel
        $sheet->setCellValue('A' . $rowIndex, $row['exam_number']);
        $sheet->setCellValue('B' . $rowIndex, isset($row['password']) ? $row['password'] : 'N/A');
        $sheet->setCellValue('C' . $rowIndex, $row['nisn']);
        $sheet->setCellValue('D' . $rowIndex, $row['name']);
        $sheet->setCellValue('E' . $rowIndex, $row['class']);
        $sheet->setCellValue('F' . $rowIndex, isset($row['jurusan']) ? $row['jurusan'] : 'N/A');
        $sheet->setCellValue('G' . $rowIndex, $row['birth_date']);
        $sheet->setCellValue('H' . $rowIndex, $status);
        $sheet->setCellValue('I' . $rowIndex, $administrasi);
        
        // Apply conditional formatting for status columns
        if ($status === 'Lulus') {
            $sheet->getStyle('H' . $rowIndex)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('C6EFCE'); // Light green
        } else {
            $sheet->getStyle('H' . $rowIndex)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFCCCC'); // Light red
        }
        
        if ($administrasi === 'Lunas') {
            $sheet->getStyle('I' . $rowIndex)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('C6EFCE'); // Light green
        } else {
            $sheet->getStyle('I' . $rowIndex)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFEB9C'); // Light yellow
        }
        
        $rowIndex++;
    }
    
    // Apply borders to all data cells
    $lastRow = $rowIndex - 1;
    if ($lastRow >= 2) {
        $sheet->getStyle('A2:I' . $lastRow)->applyFromArray($dataBorderStyle);
    }
}

// Set filename
$filename = 'data_siswa_' . date('Y-m-d') . '.xlsx';

// Set headers for Excel download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Save to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
