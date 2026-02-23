<?php
// download_template.php
require_once __DIR__ . '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Judul
$sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA BALITA');
$sheet->mergeCells('A1:D1');
$sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Petunjuk
$sheet->setCellValue('A2', 'Petunjuk:');
$sheet->getStyle('A2')->getFont()->setBold(true);

$sheet->setCellValue('A3', '1. Isi data mulai dari baris 7');
$sheet->setCellValue('A4', '2. Jenis Kelamin: "Laki-laki" atau "Perempuan"');
$sheet->setCellValue('A5', '3. Format Tanggal: YYYY-MM-DD (contoh: 2023-12-31)');

// Header tabel
$headers = ['Nama Balita', 'Jenis Kelamin', 'Tanggal Lahir', 'Alamat'];
$row = 7;

foreach ($headers as $col => $header) {
    $cell = chr(65 + $col) . $row; // A7, B7, C7, D7
    $sheet->setCellValue($cell, $header);
    
    // Style header
    $sheet->getStyle($cell)->getFont()->setBold(true);
    $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID);
    $sheet->getStyle($cell)->getFill()->getStartColor()->setARGB('FFE0E0E0');
    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Contoh data
$examples = [
    ['Budi Santoso', 'Laki-laki', '2020-05-15', 'Jl. Merdeka No. 1'],
    ['Siti Aminah', 'Perempuan', '2021-08-20', 'Jl. Sudirman No. 45'],
    ['Ahmad Rifai', 'Laki-laki', '2022-03-10', 'Jl. Gatot Subroto']
];

$row = 8;
foreach ($examples as $data) {
    $sheet->setCellValue('A' . $row, $data[0]);
    $sheet->setCellValue('B' . $row, $data[1]);
    $sheet->setCellValue('C' . $row, $data[2]);
    $sheet->setCellValue('D' . $row, $data[3]);
    $row++;
}

// Auto size columns
foreach (range('A', 'D') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Set border untuk data
$lastRow = $row - 1;
$dataRange = 'A7:D' . $lastRow;
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
];
$sheet->getStyle($dataRange)->applyFromArray($styleArray);

// Download file
$filename = 'template_import_balita_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>