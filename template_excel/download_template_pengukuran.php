<?php
$autoloadPath1 = __DIR__ . '../../vendor/autoload.php';
$autoloadPath2 = __DIR__ . '../../vendor/autoload.php';

if (file_exists($autoloadPath1)) {
    require_once $autoloadPath1; 
} elseif (file_exists($autoloadPath2)) {
    require_once $autoloadPath2;
} else {
    // Jika tidak ditemukan, berikan pesan error
    header('Content-Type: text/html');
    echo "<h3>Error: PHPSpreadsheet tidak ditemukan</h3>";
    echo "<p>Pastikan Anda sudah menjalankan: <code>composer require phpoffice/phpspreadsheet</code></p>";
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Judul
$sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA PENGUKURAN BALITA');
$sheet->mergeCells('A1:H1');
$sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->setColor(new Color(Color::COLOR_WHITE));
$sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF28A745');
$sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

// Petunjuk singkat
$sheet->setCellValue('A3', 'PETUNJUK:');
$sheet->getStyle('A3')->getFont()->setBold(true);
$sheet->setCellValue('A4', '1. Isi data mulai dari baris 7 (baris 6 adalah header)');
$sheet->setCellValue('A5', '2. Kolom dengan tanda * wajib diisi');
$sheet->setCellValue('A6', '3. ID Balita harus sudah terdaftar di database');

// Header dengan contoh
$headers = [
    ['A', 'ID Balita*', 'Angka (contoh: 70)'],
    ['B', 'Bulan Ukur* (YYYY-MM-DD)', '2025-12-26'],
    ['C', 'Usia Bulan', '42'],
    ['D', 'Berat Badan* (kg)', '10.00'],
    ['E', 'Tinggi Badan* (cm)', '90.00'],
    ['F', 'Berat Badan Tambah (Ya/Tidak)', 'Tidak'],
    ['G', 'Tinggi Badan Tambah (Ya/Tidak)', 'Ya'],
    ['H', 'Status Stunting (Stunting/Tidak Stunting)', 'Stunting']
];

$row = 7;
foreach ($headers as $header) {
    $cell = $header[0] . $row;
    $sheet->setCellValue($cell, $header[1]);
    $sheet->setCellValue($header[0] . ($row + 1), $header[2]);
    
    // Style header
    $sheet->getStyle($cell)->getFont()->setBold(true);
    if (strpos($header[1], '*') !== false) {
        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFF0000');
    }
    
    // Set width
    $sheet->getColumnDimension($header[0])->setWidth(25);
}

// Contoh data tambahan
$example_data = [
    [71, '2025-12-01', 36, 12.50, 95.00, 'Ya', 'Ya', 'Tidak Stunting'],
    [72, '2025-11-15', 24, 11.00, 85.00, 'Tidak', 'Tidak', 'Stunting'],
    [73, '2025-10-20', 48, 14.20, 102.50, 'Ya', 'Ya', 'Tidak Stunting']
];

$start_data_row = $row + 2;
foreach ($example_data as $index => $data) {
    $current_row = $start_data_row + $index;
    $sheet->setCellValue('A' . $current_row, $data[0]);
    $sheet->setCellValue('B' . $current_row, $data[1]);
    $sheet->setCellValue('C' . $current_row, $data[2]);
    $sheet->setCellValue('D' . $current_row, $data[3]);
    $sheet->setCellValue('E' . $current_row, $data[4]);
    $sheet->setCellValue('F' . $current_row, $data[5]);
    $sheet->setCellValue('G' . $current_row, $data[6]);
    $sheet->setCellValue('H' . $current_row, $data[7]);
}

// Format angka dengan 2 desimal
$last_row = $start_data_row + count($example_data) - 1;
$sheet->getStyle('D8:D' . $last_row)->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getStyle('E8:E' . $last_row)->getNumberFormat()->setFormatCode('#,##0.00');

// Border untuk data
$data_range = 'A7:H' . $last_row;
$border_style = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];
$sheet->getStyle($data_range)->applyFromArray($border_style);

// Alternating row color
for ($i = 7; $i <= $last_row; $i++) {
    if ($i % 2 == 0) {
        $sheet->getStyle('A' . $i . ':H' . $i)
              ->getFill()
              ->setFillType(Fill::FILL_SOLID)
              ->getStartColor()
              ->setARGB('FFF2F2F2');
    }
}

// Download
$filename = 'template_pengukuran_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>