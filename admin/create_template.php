<?php
// Set absolut path
$basePath = __DIR__ . '/..';
require_once $basePath . '/includes/functions.php';
require $basePath . '/vendor/autoload.php'; // Autoload PhpSpreadsheet library

// Import PhpSpreadsheet classes
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Siswa');

// Set column widths
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(30);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(20);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(15);

// Create the instructions sheet
$instructionSheet = $spreadsheet->createSheet();
$instructionSheet->setTitle('Petunjuk');

// Add instructions
$instructionSheet->setCellValue('A1', 'PETUNJUK PENGISIAN DATA SISWA');
$instructionSheet->mergeCells('A1:G1');
$instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$instructionSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$instructions = [
    ['Kolom', 'Deskripsi', 'Format', 'Contoh', 'Keterangan'],
    ['No. Ujian', 'Nomor ujian siswa', 'Teks', '1-23-01', 'Wajib diisi. Harus unik.'],
    ['NISN', 'Nomor Induk Siswa Nasional', 'Teks', '0012345678', 'Wajib diisi.'],
    ['Nama Siswa', 'Nama lengkap siswa', 'Teks', 'Budi Santoso', 'Wajib diisi.'],
    ['Kelas', 'Kelas siswa', 'Teks', 'XII RPL 1', 'Wajib diisi.'],
    ['Jurusan', 'Jurusan/program keahlian', 'Teks', 'Rekayasa Perangkat Lunak', 'Wajib diisi.'],
    ['Tanggal Lahir', 'Tanggal lahir siswa', 'DD/MM/YYYY', '15/06/2007', 'Wajib diisi. Format: tanggal/bulan/tahun'],
    ['Status', 'Status kelulusan', 'lulus/tidak_lulus', 'lulus', 'Wajib diisi. Hanya bisa diisi "lulus" atau "tidak_lulus"'],
    ['Status Administrasi', 'Status administrasi', '0/1 atau Belum Lunas/Lunas', '1', 'Wajib diisi. 0 = Belum Lunas, 1 = Lunas']
];

$instructionSheet->fromArray($instructions, NULL, 'A3');

// Style the instruction headers
$instructionSheet->getStyle('A3:E3')->getFont()->setBold(true);
$instructionSheet->getStyle('A3:E3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
$instructionSheet->getStyle('A3:E3')->getFont()->getColor()->setRGB('FFFFFF');
$instructionSheet->getStyle('A3:E' . (count($instructions) + 2))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Adjust column widths for instruction sheet
$instructionSheet->getColumnDimension('A')->setWidth(18);
$instructionSheet->getColumnDimension('B')->setWidth(25);
$instructionSheet->getColumnDimension('C')->setWidth(15);
$instructionSheet->getColumnDimension('D')->setWidth(15);
$instructionSheet->getColumnDimension('E')->setWidth(40);

// Add additional notes
$lastRow = count($instructions) + 4;
$instructionSheet->setCellValue('A' . $lastRow, 'CATATAN PENTING:');
$instructionSheet->getStyle('A' . $lastRow)->getFont()->setBold(true);
$lastRow++;

$notes = [
    '1. Password siswa akan digenerate otomatis saat import data.',
    '2. Jika nomor ujian sudah ada di database, data tidak akan diimport (akan dilewati).',
    '3. Status hanya bisa diisi dengan "lulus" atau "tidak_lulus" (lowercase, dengan underscore).',
    '4. Status Administrasi diisi dengan "0" untuk Belum Lunas atau "1" untuk Lunas.',
    '5. Tanggal lahir harus menggunakan format DD/MM/YYYY (tanggal/bulan/tahun), contoh: 15/06/2007.',
    '6. Pastikan semua kolom wajib telah diisi dengan benar sebelum mengimport data.',
    '7. Baris header (baris pertama) akan dilewati saat proses import.'
];

foreach ($notes as $note) {
    $instructionSheet->setCellValue('A' . $lastRow, $note);
    $instructionSheet->mergeCells('A' . $lastRow . ':G' . $lastRow);
    $lastRow++;
}

// Set headers in the main sheet (first sheet)
$spreadsheet->setActiveSheetIndex(0);
$headers = ['No. Ujian', 'NISN', 'Nama Siswa', 'Kelas', 'Jurusan', 'Tanggal Lahir', 'Status', 'Status Administrasi'];
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4472C4']],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
];

// Write headers
foreach ($headers as $colIndex => $header) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
    $sheet->setCellValue($colLetter . '1', $header);
}
$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

// Add sample data for better understanding
$sampleData = [
    ['1-23-01', '0012345678', 'Budi Santoso', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '15/06/2007', 'lulus', '1'],
    ['1-23-02', '0012345679', 'Ani Wijaya', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '22/08/2007', 'lulus', '1'],
    ['1-23-03', '0012345680', 'Dewi Putri', 'XII TKJ 2', 'Teknik Komputer Jaringan', '10/03/2007', 'lulus', '0'],
    ['1-23-04', '0012345681', 'Eko Prasetyo', 'XII TKJ 2', 'Teknik Komputer Jaringan', '05/12/2006', 'tidak_lulus', '0'],
    ['1-23-05', '0012345682', 'Fitri Handayani', 'XII MM 1', 'Multimedia', '17/05/2007', 'lulus', '1']
];

// Add data to the sheet
$rowIndex = 2;
foreach ($sampleData as $data) {
    $sheet->fromArray($data, NULL, 'A' . $rowIndex);
    $rowIndex++;
}

// Apply borders to the sample data
$sheet->getStyle('A1:H' . ($rowIndex - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Add data validation for Status column (column G)
$validation = $sheet->getCell('G2')->getDataValidation();
$validation->setType(DataValidation::TYPE_LIST)
    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
    ->setAllowBlank(false)
    ->setShowInputMessage(true)
    ->setShowErrorMessage(true)
    ->setShowDropDown(true)
    ->setErrorTitle('Input error')
    ->setError('Value is not in list.')
    ->setPromptTitle('Pick from list')
    ->setPrompt('Please select a value from the drop-down list.')
    ->setFormula1('"lulus,tidak_lulus"');

// Copy validation to all status cells (G2:G100)
$sheet->setDataValidation('G2:G100', $validation);

// Add data validation for Status Administrasi column (column H)
$validation = $sheet->getCell('H2')->getDataValidation();
$validation->setType(DataValidation::TYPE_LIST)
    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
    ->setAllowBlank(false)
    ->setShowInputMessage(true)
    ->setShowErrorMessage(true)
    ->setShowDropDown(true)
    ->setErrorTitle('Input error')
    ->setError('Value is not in list.')
    ->setPromptTitle('Pick from list')
    ->setPrompt('0 = Belum Lunas, 1 = Lunas')
    ->setFormula1('"0,1,Belum Lunas,Lunas"');

// Copy validation to all status administrasi cells (H2:H100)
$sheet->setDataValidation('H2:H100', $validation);

// Add highlighting for status cells
for ($i = 2; $i <= 6; $i++) {
    // Status column formatting
    if ($sheet->getCell('G' . $i)->getValue() === 'lulus') {
        $sheet->getStyle('G' . $i)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C6EFCE'); // Light green
    } else {
        $sheet->getStyle('G' . $i)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFCCCC'); // Light red
    }
    
    // Status Administrasi column formatting
    if ($sheet->getCell('H' . $i)->getValue() === '1') {
        $sheet->getStyle('H' . $i)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C6EFCE'); // Light green
    } else {
        $sheet->getStyle('H' . $i)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFEB9C'); // Light yellow
    }
}

// Save the Excel file
$writer = new Xlsx($spreadsheet);
$writer->save(__DIR__ . '/template_import.xlsx');

echo "Template Excel berhasil dibuat di " . __DIR__ . '/template_import.xlsx';
?>
