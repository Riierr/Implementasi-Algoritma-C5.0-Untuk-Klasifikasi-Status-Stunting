<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><strong>Data Pengukuran Balita</strong></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?stunting=home">Home</a></li>
                        <li class="breadcrumb-item active">Pengukuran</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Tabel Data Pengukuran</h5>
                        </div>
                        <div class="card-body">
                            <a href="index.php?stunting=tambah_pengukuran" class="btn btn-primary mb-2">
                                <i class="fa-solid fa-square-plus"></i> Tambah Data Pengukuran
                            </a>

                            <!-- Tombol Import Excel -->
                            <button type="button" class="btn btn-success mb-2" data-toggle="modal"
                                data-target="#modalImportPengukuran">
                                <i class="fa-solid fa-file-excel"></i> Import dari Excel
                            </button>

                            <!-- Modal Import Excel Pengukuran -->
                            <div class="modal fade" id="modalImportPengukuran" tabindex="-1"
                                aria-labelledby="modalImportPengukuranLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success">
                                            <h5 class="modal-title" id="modalImportPengukuranLabel">
                                                <strong><i class="fas fa-file-excel"></i> Import Data Pengukuran dari
                                                    Excel</strong>
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="" method="POST" enctype="multipart/form-data"
                                                id="formImportPengukuran">
                                                <div class="form-group">
                                                    <label for="file_excel_pengukuran">Pilih File Excel</label>
                                                    <div class="custom-file">
                                                        <input type="file" name="file_excel_pengukuran"
                                                            id="file_excel_pengukuran" class="custom-file-input"
                                                            accept=".xlsx, .xls, .csv" required>
                                                        <label class="custom-file-label"
                                                            for="file_excel_pengukuran">Pilih file...</label>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Format yang didukung: .xlsx, .xls, .csv (maks. 5MB)
                                                    </small>
                                                </div>

                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="skipFirstRowPengukuran" name="skipFirstRowPengukuran"
                                                            checked>
                                                        <label class="custom-control-label"
                                                            for="skipFirstRowPengukuran">
                                                            Lewati baris pertama (header)
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="text-center mt-3">
                                                    <a href="template_excel/download_template_pengukuran.php"
                                                        class="btn btn-outline-success btn-sm" target="_blank">
                                                        <i class="fas fa-download"></i> Download Template Excel
                                                    </a>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                <i class="fas fa-times"></i> Batal
                                            </button>
                                            <button type="submit" name="import_pengukuran" form="formImportPengukuran"
                                                class="btn btn-success">
                                                <i class="fas fa-upload"></i> Import Data
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Modal Import Excel -->

                            <?php
                            
                            $pengukuran = query("SELECT 
                                p.id_pengukuran, 
                                p.id_balita, 
                                p.bulan_ukur,
                                p.usia_bulan, 
                                p.berat_badan, 
                                p.tinggi_badan, 
                                p.berat_badan_tambah,
                                p.tinggi_badan_tambah, 
                                p.status_stunting, 
                                p.tanggal_input, 
                                b.nama_balita 
                            FROM pengukuran p
                            INNER JOIN balita b ON p.id_balita = b.id_balita
                            ORDER BY p.bulan_ukur DESC, p.tanggal_input DESC");

                            // PERBAIKAN: Proses Import Excel untuk Pengukuran 
                            if (isset($_POST['import_pengukuran'])) {
                                // Enable error reporting untuk debugging
                                error_reporting(E_ALL);
                                ini_set('display_errors', 1);
                                
                                try {
                                    
                                    $autoloadPath = __DIR__ . './../vendor/autoload.php';
                                    
                                    if (!file_exists($autoloadPath)) {
                                        throw new Exception("File autoload.php tidak ditemukan di: $autoloadPath");
                                    }
                                    
                                    require_once $autoloadPath;
                                    
                                    // Cek apakah class IOFactory ada
                                    if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                                        throw new Exception("PHPSpreadsheet tidak terinstall dengan benar");
                                    }
                                    
                                    $skipFirstRow = isset($_POST['skipFirstRowPengukuran']) ? true : false;
                                    
                                    if (isset($_FILES['file_excel_pengukuran']) && $_FILES['file_excel_pengukuran']['error'] == 0) {
                                        $allowed_ext = ['xls', 'xlsx', 'csv'];
                                        $file_name = $_FILES['file_excel_pengukuran']['name'];
                                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                        $file_size = $_FILES['file_excel_pengukuran']['size'];
                                        $file_tmp = $_FILES['file_excel_pengukuran']['tmp_name'];
                                        
                                        if (!in_array($file_ext, $allowed_ext)) {
                                            throw new Exception("Format file tidak didukung. Gunakan .xls, .xlsx, atau .csv");
                                        }
                                        
                                        if ($file_size > 5242880) {
                                            throw new Exception("Ukuran file maksimal 5MB");
                                        }
                                        
                                        // Load file Excel
                                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_tmp);
                                        $worksheet = $spreadsheet->getActiveSheet();
                                        $rows = $worksheet->toArray();
                                        
                                        if (empty($rows)) {
                                            throw new Exception("File Excel kosong atau tidak memiliki data");
                                        }
                                        
                                        $success_count = 0;
                                        $error_count = 0;
                                        $error_messages = [];
                                        $start_row = $skipFirstRow ? 1 : 0;
                                        
                                        // Start transaction
                                        mysqli_begin_transaction($koneksi);
                                        
                                        for ($i = $start_row; $i < count($rows); $i++) {
                                            $row = $rows[$i];
                                            $row_number = $i + 1;
                                            
                                            // Skip jika semua kolom kosong
                                            $isEmptyRow = true;
                                            foreach ($row as $cell) {
                                                if ($cell !== null && trim($cell) !== '' && trim($cell) !== '0') {
                                                    $isEmptyRow = false;
                                                    break;
                                                }
                                            }
                                            
                                            if ($isEmptyRow) {
                                                continue;
                                            }
                                                                                        
                                            $id_balita = isset($row[0]) ? trim($row[0]) : '';
                                            $bulan_ukur = isset($row[1]) ? trim($row[1]) : '';
                                            $usia_bulan = isset($row[2]) ? trim($row[2]) : '';
                                            $berat_badan = isset($row[3]) ? trim($row[3]) : '';
                                            $tinggi_badan = isset($row[4]) ? trim($row[4]) : '';
                                            $berat_badan_tambah = isset($row[5]) ? trim($row[5]) : '';
                                            $tinggi_badan_tambah = isset($row[6]) ? trim($row[6]) : '';
                                            $status_stunting = isset($row[7]) ? trim($row[7]) : '';
                                            
                                            // Validasi data
                                            $errors = [];
                                            
                                            // 1. Validasi ID Balita
                                            if (empty($id_balita)) {
                                                $errors[] = "ID Balita kosong";
                                            } elseif (!is_numeric($id_balita)) {
                                                $errors[] = "ID Balita harus angka";
                                            } else {
                                                // Cek apakah ID balita ada di database
                                                $query_cek = "SELECT id_balita FROM balita WHERE id_balita = ?";
                                                $stmt = mysqli_prepare($koneksi, $query_cek);
                                                mysqli_stmt_bind_param($stmt, 'i', $id_balita);
                                                mysqli_stmt_execute($stmt);
                                                $result = mysqli_stmt_get_result($stmt);
                                                
                                                if (mysqli_num_rows($result) == 0) {
                                                    $errors[] = "ID Balita $id_balita tidak ditemukan";
                                                }
                                                mysqli_stmt_close($stmt);
                                            }
                                            
                                            // 2. Validasi Bulan Ukur
                                            if (empty($bulan_ukur)) {
                                                $errors[] = "Bulan ukur kosong";
                                            } else {
                                                // Coba parsing berbagai format tanggal
                                                $parsed_date = date_parse($bulan_ukur);
                                                if ($parsed_date['error_count'] == 0 && checkdate($parsed_date['month'], $parsed_date['day'], $parsed_date['year'])) {
                                                    $bulan_ukur = sprintf("%04d-%02d-%02d", 
                                                        $parsed_date['year'], 
                                                        $parsed_date['month'], 
                                                        $parsed_date['day']);
                                                } else {
                                                    // Coba format lain
                                                    $timestamp = strtotime($bulan_ukur);
                                                    if ($timestamp !== false) {
                                                        $bulan_ukur = date('Y-m-d', $timestamp);
                                                    } else {
                                                        $errors[] = "Format bulan ukur tidak valid. Gunakan YYYY-MM-DD";
                                                    }
                                                }
                                            }
                                            
                                            // 3. Validasi Usia Bulan
                                            if (!empty($usia_bulan)) {
                                                if (!is_numeric($usia_bulan)) {
                                                    $errors[] = "Usia bulan harus angka";
                                                } elseif ($usia_bulan < 0 || $usia_bulan > 60) {
                                                    $errors[] = "Usia bulan harus antara 0-60";
                                                }
                                            } else {
                                                $usia_bulan = null;
                                            }
                                            
                                            // 4. Validasi Berat Badan
                                            if (empty($berat_badan)) {
                                                $errors[] = "Berat badan kosong";
                                            } elseif (!is_numeric($berat_badan)) {
                                                $errors[] = "Berat badan harus angka";
                                            } elseif ($berat_badan < 1 || $berat_badan > 30) {
                                                $errors[] = "Berat badan harus antara 1-30 kg";
                                            }
                                            
                                            // 5. Validasi Tinggi Badan
                                            if (empty($tinggi_badan)) {
                                                $errors[] = "Tinggi badan kosong";
                                            } elseif (!is_numeric($tinggi_badan)) {
                                                $errors[] = "Tinggi badan harus angka";
                                            } elseif ($tinggi_badan < 30 || $tinggi_badan > 150) {
                                                $errors[] = "Tinggi badan harus antara 30-150 cm";
                                            }
                                            
                                            // 6. Validasi Berat Badan Tambah 
                                            if (!empty($berat_badan_tambah)) {
                                                $berat_badan_tambah = ucfirst(strtolower($berat_badan_tambah));
                                                if (!in_array($berat_badan_tambah, ['Ya', 'Tidak'])) {
                                                    $errors[] = "Berat badan tambah harus 'Ya' atau 'Tidak'";
                                                }
                                            } else {
                                                $berat_badan_tambah = null;
                                            }
                                            
                                            // 7. Validasi Tinggi Badan Tambah
                                            if (!empty($tinggi_badan_tambah)) {
                                                $tinggi_badan_tambah = ucfirst(strtolower($tinggi_badan_tambah));
                                                if (!in_array($tinggi_badan_tambah, ['Ya', 'Tidak'])) {
                                                    $errors[] = "Tinggi badan tambah harus 'Ya' atau 'Tidak'";
                                                }
                                            } else {
                                                $tinggi_badan_tambah = null;
                                            }
                                            
                                            // 8. Validasi Status Stunting
                                            if (!empty($status_stunting)) {
                                                $status_stunting = ucwords(strtolower($status_stunting));
                                                if (!in_array($status_stunting, ['Stunting', 'Tidak stunting'])) {
                                                    // Coba format lain
                                                    if (strtolower($status_stunting) == 'normal') {
                                                        $status_stunting = 'Tidak stunting';
                                                    } elseif (strtolower($status_stunting) == 'tidak') {
                                                        $status_stunting = 'Tidak stunting';
                                                    } elseif (strtolower($status_stunting) == 'ya') {
                                                        $status_stunting = 'Stunting';
                                                    } elseif (strtolower($status_stunting) == 'tidak stunting') {
                                                        $status_stunting = 'Tidak stunting';
                                                    } else {
                                                        $errors[] = "Status stunting harus 'Stunting' atau 'Tidak Stunting'";
                                                    }
                                                }
                                            } else {
                                                $status_stunting = null;
                                            }
                                            
                                            // Jika ada error, skip row
                                            if (!empty($errors)) {
                                                $error_count++;
                                                $error_messages[] = "Baris $row_number: " . implode(", ", $errors);
                                                continue;
                                            }
                                            
                                            // Cek duplikasi (id_balita + bulan_ukur harus unik)
                                            $query_cek_duplikat = "SELECT id_pengukuran FROM pengukuran 
                                                                    WHERE id_balita = ? AND bulan_ukur = ?";
                                            $stmt_cek = mysqli_prepare($koneksi, $query_cek_duplikat);
                                            mysqli_stmt_bind_param($stmt_cek, 'is', $id_balita, $bulan_ukur);
                                            mysqli_stmt_execute($stmt_cek);
                                            mysqli_stmt_store_result($stmt_cek);
                                            
                                            if (mysqli_stmt_num_rows($stmt_cek) > 0) {
                                                // Update data yang sudah ada
                                                $query = "UPDATE pengukuran SET 
                                                        usia_bulan = ?, 
                                                        berat_badan = ?, 
                                                        tinggi_badan = ?, 
                                                        berat_badan_tambah = ?, 
                                                        tinggi_badan_tambah = ?, 
                                                        status_stunting = ? 
                                                        WHERE id_balita = ? AND bulan_ukur = ?";
                                                
                                                $stmt = mysqli_prepare($koneksi, $query);
                                                mysqli_stmt_bind_param($stmt, 'iddsssss', 
                                                    $usia_bulan,
                                                    $berat_badan,
                                                    $tinggi_badan,
                                                    $berat_badan_tambah,
                                                    $tinggi_badan_tambah,
                                                    $status_stunting,
                                                    $id_balita,
                                                    $bulan_ukur
                                                );
                                            } else {
                                                // Insert data baru
                                                $query = "INSERT INTO pengukuran 
                                                        (id_balita, bulan_ukur, usia_bulan, berat_badan, tinggi_badan, 
                                                        berat_badan_tambah, tinggi_badan_tambah, status_stunting) 
                                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                                                
                                                $stmt = mysqli_prepare($koneksi, $query);
                                                mysqli_stmt_bind_param($stmt, 'isddssss', 
                                                    $id_balita,
                                                    $bulan_ukur,
                                                    $usia_bulan,
                                                    $berat_badan,
                                                    $tinggi_badan,
                                                    $berat_badan_tambah,
                                                    $tinggi_badan_tambah,
                                                    $status_stunting
                                                );
                                            }
                                            
                                            mysqli_stmt_close($stmt_cek);
                                            
                                            if (mysqli_stmt_execute($stmt)) {
                                                $success_count++;
                                            } else {
                                                $error_count++;
                                                $error_messages[] = "Baris $row_number: Database error - " . mysqli_error($koneksi);
                                            }
                                            
                                            mysqli_stmt_close($stmt);
                                        }
                                        
                                        // Commit transaction
                                        mysqli_commit($koneksi);
                                        
                                        // Tampilkan hasil
                                        $icon = 'success';
                                        $title = 'Import Berhasil';
                                        
                                        if ($success_count == 0 && $error_count > 0) {
                                            $icon = 'error';
                                            $title = 'Import Gagal';
                                        } elseif ($success_count > 0 && $error_count > 0) {
                                            $icon = 'warning';
                                            $title = 'Import dengan Beberapa Error';
                                        }
                                        
                                        $html_message = "<div style='text-align: left;'>";
                                        $html_message .= "<h5><i class='fas fa-chart-line'></i> Hasil Import Data Pengukuran</h5>";
                                        $html_message .= "<div class='row'>";
                                        $html_message .= "<div class='col-md-4'><p><span class='badge badge-success' style='font-size: 14px;'>✅ Berhasil:</span> <strong>$success_count</strong> data</p></div>";
                                        $html_message .= "<div class='col-md-4'><p><span class='badge badge-danger' style='font-size: 14px;'>❌ Gagal:</span> <strong>$error_count</strong> data</p></div>";
                                        $html_message .= "<div class='col-md-4'><p><span class='badge badge-info' style='font-size: 14px;'>📊 Total Baris:</span> <strong>" . count($rows) . "</strong> baris</p></div>";
                                        $html_message .= "</div>";
                                        
                                        if (!empty($error_messages)) {
                                            $html_message .= "<hr><h6><i class='fas fa-times-circle'></i> Detail Error:</h6>";
                                            $html_message .= "<div style='max-height: 200px; overflow-y: auto; padding: 10px; background: #f8f9fa; border-radius: 5px;'>";
                                            foreach ($error_messages as $error) {
                                                $html_message .= "<p style='margin: 5px 0; color: #721c24; font-size: 12px;'>• $error</p>";
                                            }
                                            $html_message .= "</div>";
                                        }
                                        
                                        $html_message .= "</div>";
                                        
                                        echo "
                                        <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                                        <script>
                                            Swal.fire({
                                                icon: '$icon',
                                                title: '$title',
                                                html: `$html_message`,
                                                confirmButtonText: 'OK',
                                                width: '700px',
                                                customClass: {
                                                    popup: 'import-pengukuran-result'
                                                }
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location = 'index.php?stunting=pengukuran';
                                                }
                                            });
                                        </script>
                                        ";
                                        
                                    } else {
                                        $error_msg = 'Tidak ada file yang diupload';
                                        if (isset($_FILES['file_excel_pengukuran'])) {
                                            switch ($_FILES['file_excel_pengukuran']['error']) {
                                                case 1: $error_msg = 'File terlalu besar (php.ini)'; break;
                                                case 2: $error_msg = 'File terlalu besar (form)'; break;
                                                case 3: $error_msg = 'File hanya terupload sebagian'; break;
                                                case 4: $error_msg = 'Tidak ada file yang dipilih'; break;
                                                case 6: $error_msg = 'Folder temporary tidak ditemukan'; break;
                                                case 7: $error_msg = 'Gagal menulis file ke disk'; break;
                                                case 8: $error_msg = 'Ekstensi file dihentikan'; break;
                                            }
                                        }
                                        
                                        throw new Exception("Gagal upload file: $error_msg");
                                    }
                                    
                                } catch (Exception $e) {
                                    // Rollback jika ada transaction
                                    if (isset($koneksi) && mysqli_ping($koneksi)) {
                                        mysqli_rollback($koneksi);
                                    }
                                    
                                    echo "
                                    <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                                    <script>
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Import Gagal',
                                            html: `<div style='text-align: left;'>
                                                   <p><strong>Terjadi kesalahan:</strong></p>
                                                   <pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; color: #dc3545;'>" . addslashes($e->getMessage()) . "</pre>
                                                   </div>`,
                                            confirmButtonText: 'OK',
                                            width: '700px'
                                        });
                                    </script>
                                    ";
                                }
                            }
                            ?>

                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Bulan Pengukuran</th>
                                        <th>Usia Bulan</th>
                                        <th>Berat Badan (kg)</th>
                                        <th>Tinggi Badan (cm)</th>
                                        <th>Berat Badan Tambah</th>
                                        <th>Tinggi Badan Tambah</th>
                                        <th>Status Stunting</th>
                                        <th>Tanggal Input</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; ?>
                                    <?php foreach ($pengukuran as $data) : ?>
                                    <tr>
                                        <td><?= $i; ?></td>
                                        <td><?= htmlspecialchars($data['nama_balita']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($data['bulan_ukur'])) ?></td>
                                        <td><?= $data['usia_bulan'] ?? '-' ?></td>
                                        <td><?= number_format($data['berat_badan'], 2) ?></td>
                                        <td><?= number_format($data['tinggi_badan'], 2) ?></td>
                                        <td>
                                            <?php if ($data['berat_badan_tambah']): ?>
                                            <span
                                                class="badge badge-<?= $data['berat_badan_tambah'] == 'Ya' ? 'success' : 'warning' ?>">
                                                <?= $data['berat_badan_tambah'] ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($data['tinggi_badan_tambah']): ?>
                                            <span
                                                class="badge badge-<?= $data['tinggi_badan_tambah'] == 'Ya' ? 'success' : 'warning' ?>">
                                                <?= $data['tinggi_badan_tambah'] ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($data['status_stunting']): ?>
                                            <span
                                                class="badge badge-<?= $data['status_stunting'] == 'Stunting' ? 'danger' : 'success' ?>">
                                                <?= $data['status_stunting'] ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($data['tanggal_input'])) ?></td>
                                        <td class="d-flex justify-content-start">
                                            <a href="index.php?stunting=edit_pengukuran&id=<?= $data['id_pengukuran'] ?>"
                                                class="btn btn-info mr-1" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="javascript:void(0);"
                                                onclick="konfirmasiHapusPengukuran(<?= $data['id_pengukuran'] ?>)"
                                                class="btn btn-danger" title="Hapus">
                                                <i class="fa-solid fa-circle-xmark"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php $i++; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Preview nama file
document.getElementById('file_excel_pengukuran').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file...';
    var label = e.target.nextElementSibling;
    label.textContent = fileName;
});

// Konfirmasi hapus data pengukuran
function konfirmasiHapusPengukuran(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data pengukuran akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php?stunting=hapus_pengukuran&id=' + id;
        }
    });
}

// SweetAlert untuk error handling
if (typeof Swal !== 'undefined') {
    // Global error handler
    window.addEventListener('error', function(e) {
        if (e.message.includes('PHPSpreadsheet') || e.message.includes('Excel')) {
            Swal.fire({
                icon: 'error',
                title: 'Error PHPExcel',
                text: 'Terjadi kesalahan dalam membaca file Excel. Pastikan file tidak rusak dan formatnya benar.',
                confirmButtonText: 'OK'
            });
        }
    });
}
</script>