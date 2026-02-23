<?php
// Cek apakah ini diakses dari halaman training
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$isTraining = strpos($referer, 'stunting=training') !== false;

if (!$isTraining) {
    // Optional: Anda bisa menambahkan validasi keamanan di sini
    // header('Location: index.php?stunting=training');
    // exit;
}

$autoloadPath1 = __DIR__ . '/../../vendor/autoload.php';
$autoloadPath2 = __DIR__ . '/../vendor/autoload.php';

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
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Judul
$sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA TRAINING BALITA');
$sheet->mergeCells('A1:I1');
$sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->setColor(new Color(Color::COLOR_WHITE));
$sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF28A745');
$sheet->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center');
$sheet->getRowDimension(1)->setRowHeight(30);

// Petunjuk singkat
$sheet->setCellValue('A3', 'PETUNJUK:');
$sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11);
$sheet->setCellValue('A4', '1. Isi data mulai dari baris 7 (baris 6 adalah header)');
$sheet->setCellValue('A5', '2. Kolom dengan tanda * wajib diisi');
$sheet->setCellValue('A6', '3. ID Balita dan ID Pengukuran harus sudah terdaftar di database');

// Header dengan contoh (SESUAI STRUKTUR TABEL TRAINING)
$headers = [
    ['A', 'ID Balita*', 'Angka (contoh: 70)'],
    ['B', 'ID Pengukuran*', 'Angka (contoh: 100)'],
    ['C', 'Usia Bulan', '42'],
    ['D', 'Jenis Kelamin*', 'L / P'],
    ['E', 'Berat Badan* (kg)', '10.00'],
    ['F', 'Tinggi Badan* (cm)', '90.00'],
    ['G', 'Status Stunting', 'Stunting / Tidak Stunting'],
    ['H', 'Tipe Data', 'Training / Testing'],
    ['I', 'Keterangan', 'Tidak perlu diisi']
];

$row = 7;
foreach ($headers as $header) {
    $cell = $header[0] . $row;
    $sheet->setCellValue($cell, $header[1]);
    
    // Contoh data di baris berikutnya
    if ($row == 7) { // Hanya untuk baris pertama contoh
        $sheet->setCellValue($header[0] . ($row + 1), $header[2]);
    }
    
    // Style header
    $sheet->getStyle($cell)->getFont()->setBold(true)->setSize(10);
    $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8F5E9');
    
    if (strpos($header[1], '*') !== false) {
        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFF0000');
    }
    
    // Set width
    if ($header[0] == 'I') {
        $sheet->getColumnDimension($header[0])->setWidth(30);
    } else {
        $sheet->getColumnDimension($header[0])->setWidth(20);
    }
}

// Data contoh untuk Training
$example_data = [
    [70, 100, 42, 'Laki-laki', 10.00, 90.00, 'Stunting', 'Training', 'Contoh data 1'],
    [71, 101, 36, 'Perempuan', 12.50, 95.00, 'Tidak Stunting', 'Training', 'Contoh data 2'],
    [72, 102, 24, 'Laki-laki', 11.00, 85.00, 'Stunting', 'Testing', 'Contoh data 3'],
    [73, 103, 48, 'Perempuan', 14.20, 102.50, 'Tidak Stunting', 'Training', 'Contoh data 4'],
    [74, 104, 18, 'Laki-laki', 9.50, 80.00, 'Stunting', 'Testing', 'Contoh data 5']
];

// Contoh data di baris 8-12
$start_data_row = $row + 1;
foreach ($example_data as $index => $data) {
    $current_row = $start_data_row + $index;
    
    $sheet->setCellValue('A' . $current_row, $data[0]);  // ID Balita
    $sheet->setCellValue('B' . $current_row, $data[1]);  // ID Pengukuran
    $sheet->setCellValue('C' . $current_row, $data[2]);  // Usia Bulan
    $sheet->setCellValue('D' . $current_row, $data[3]);  // Jenis Kelamin
    $sheet->setCellValue('E' . $current_row, $data[4]);  // Berat Badan
    $sheet->setCellValue('F' . $current_row, $data[5]);  // Tinggi Badan
    $sheet->setCellValue('G' . $current_row, $data[6]);  // Status Stunting
    $sheet->setCellValue('H' . $current_row, $data[7]);  // Tipe Data
    $sheet->setCellValue('I' . $current_row, $data[8]);  // Keterangan
}

// Format angka dengan 2 desimal untuk berat dan tinggi
$last_row = $start_data_row + count($example_data) - 1;
$sheet->getStyle('E' . $start_data_row . ':E' . $last_row)
      ->getNumberFormat()
      ->setFormatCode('#,##0.00');
$sheet->getStyle('F' . $start_data_row . ':F' . $last_row)
      ->getNumberFormat()
      ->setFormatCode('#,##0.00');

// Border untuk data (header + contoh)
$data_range = 'A' . $row . ':I' . $last_row;
$border_style = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FFCCCCCC'],
        ],
    ],
];
$sheet->getStyle($data_range)->applyFromArray($border_style);

// Alternating row color untuk data contoh
for ($i = $start_data_row; $i <= $last_row; $i++) {
    if ($i % 2 == 0) {
        $sheet->getStyle('A' . $i . ':I' . $i)
              ->getFill()
              ->setFillType(Fill::FILL_SOLID)
              ->getStartColor()
              ->setARGB('FFF9F9F9');
    }
}

// Validasi data dengan Data Validation
// 1. Validasi Jenis Kelamin
$validation = $sheet->getCell('D8')->getDataValidation();
$validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
$validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
$validation->setAllowBlank(false);
$validation->setShowInputMessage(true);
$validation->setShowErrorMessage(true);
$validation->setShowDropDown(true);
$validation->setErrorTitle('Input error');
$validation->setError('Nilai tidak valid untuk Jenis Kelamin');
$validation->setPromptTitle('Pilih Jenis Kelamin');
$validation->setPrompt('Pilih dari daftar: Laki-laki atau Perempuan');
$validation->setFormula1('"Laki-laki,Perempuan"');

// 2. Validasi Status Stunting
$validation2 = $sheet->getCell('G8')->getDataValidation();
$validation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
$validation2->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
$validation2->setAllowBlank(true);
$validation2->setShowInputMessage(true);
$validation2->setShowErrorMessage(true);
$validation2->setShowDropDown(true);
$validation2->setErrorTitle('Input error');
$validation2->setError('Nilai tidak valid untuk Status Stunting');
$validation2->setPromptTitle('Pilih Status Stunting');
$validation2->setPrompt('Pilih dari daftar: Stunting atau Tidak Stunting');
$validation2->setFormula1('"Stunting,Tidak Stunting"');

// 3. Validasi Tipe Data
$validation3 = $sheet->getCell('H8')->getDataValidation();
$validation3->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
$validation3->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
$validation3->setAllowBlank(true);
$validation3->setShowInputMessage(true);
$validation3->setShowErrorMessage(true);
$validation3->setShowDropDown(true);
$validation3->setErrorTitle('Input error');
$validation3->setError('Nilai tidak valid untuk Tipe Data');
$validation3->setPromptTitle('Pilih Tipe Data');
$validation3->setPrompt('Pilih dari daftar: Training atau Testing');
$validation3->setFormula1('"Training,Testing"');

// 4. Copy validasi ke semua baris data contoh
for ($i = $start_data_row + 1; $i <= $last_row; $i++) {
    $sheet->getCell('D' . $i)->setDataValidation(clone $validation);
    $sheet->getCell('G' . $i)->setDataValidation(clone $validation2);
    $sheet->getCell('H' . $i)->setDataValidation(clone $validation3);
}

// Informasi tambahan
$info_row = $last_row + 2;
$sheet->setCellValue('A' . $info_row, 'CATATAN PENTING:');
$sheet->getStyle('A' . $info_row)->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFD35400');
$sheet->mergeCells('A' . $info_row . ':I' . $info_row);

$sheet->setCellValue('A' . ($info_row + 1), '• ID Balita dan ID Pengukuran harus sesuai dengan data yang ada di database');
$sheet->setCellValue('A' . ($info_row + 2), '• Usia bulan harus antara 0-60 bulan (jika diisi)');
$sheet->setCellValue('A' . ($info_row + 3), '• Berat badan harus antara 1-30 kg');
$sheet->setCellValue('A' . ($info_row + 4), '• Tinggi badan harus antara 30-150 cm');
$sheet->setCellValue('A' . ($info_row + 5), '• Kolom Keterangan hanya untuk catatan, tidak akan diimport');

// Format informasi
$sheet->getStyle('A' . $info_row . ':I' . ($info_row + 5))
      ->getFill()
      ->setFillType(Fill::FILL_SOLID)
      ->getStartColor()
      ->setARGB('FFFFF8E1');

// Set alignment
$sheet->getStyle('A7:I' . $last_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('D8:D' . $last_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
$sheet->getStyle('I8:I' . $last_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// Download
$filename = 'template_import_training_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>