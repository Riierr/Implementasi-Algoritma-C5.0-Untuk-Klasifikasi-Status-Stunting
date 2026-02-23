<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><strong>Data Training</strong></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?stunting=home">Home</a></li>
                        <li class="breadcrumb-item active">Training</li>
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
                            <h5 class="card-title">Tabel Data Training</h5>
                        </div>
                        <div class="card-body">
                            <a href="index.php?stunting=tambah_training" class="btn btn-primary mb-2"><i
                                    class="fa-solid fa-square-plus"></i> Tambah Data
                                Training</a>

                            <!-- Button Import Excel -->
                            <button type="button" class="btn btn-success mb-2" data-toggle="modal"
                                data-target="#modalImport">
                                <i class="fa-solid fa-file-excel"></i> Import dari Excel
                            </button>

                            <!-- Modal Import Excel-->
                            <div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success">
                                            <h5 class="modal-title" id="modalImportLabel"><strong>Import Data Training
                                                    dari
                                                    Excel</strong></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="" method="POST" enctype="multipart/form-data"
                                                id="formImportExcel">
                                                <div class="form-group">
                                                    <label for="file_excel">Pilih File Excel</label>
                                                    <div class="custom-file">
                                                        <input type="file" name="file_excel" id="file_excel"
                                                            class="custom-file-input" accept=".xlsx, .xls, .csv"
                                                            required>
                                                        <label class="custom-file-label" for="file_excel">Pilih
                                                            file...</label>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Format yang didukung: .xlsx, .xls, .csv<br>
                                                    </small>
                                                </div>

                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="skipFirstRow" name="skipFirstRow" checked>
                                                        <label class="custom-control-label" for="skipFirstRow">Lewati
                                                            baris pertama (header)</label>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <a href="template_excel/download_template_training.php"
                                                        class="btn btn-outline-success btn-sm" download>
                                                        <i class="fas fa-download"></i> Download Template
                                                    </a>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                                <i class="fas fa-times"></i> Batal
                                            </button>
                                            <button type="submit" name="import_training" form="formImportExcel"
                                                class="btn btn-success">
                                                <i class="fas fa-upload"></i> Import Data
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Modal Import Excel -->

                            <?php
                            // Query untuk menampilkan data training
                            $training = query("SELECT t.id_training, 
                                t.id_balita, 
                                t.id_pengukuran, 
                                t.usia_bulan, 
                                t.jenis_kelamin,
                                t.berat_badan, 
                                t.tinggi_badan, 
                                t.status_stunting, 
                                t.tipe_data, 
                                t.tanggal_input, 
                                b.nama_balita, 
                                p.bulan_ukur
                            FROM training_stunting t 
                            INNER JOIN balita b ON t.id_balita = b.id_balita
                            INNER JOIN pengukuran p ON t.id_pengukuran = p.id_pengukuran");

                            // PROSES IMPORT DATA TRAINING
                            if (isset($_POST['import_training'])) {
                                // Enable error reporting untuk debugging
                                error_reporting(E_ALL);
                                ini_set('display_errors', 1);
                                
                                try {
                                    // Cek jika file diupload
                                    if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] != 0) {
                                        $error_msg = 'Tidak ada file yang diupload';
                                        if (isset($_FILES['file_excel'])) {
                                            switch ($_FILES['file_excel']['error']) {
                                                case 1: $error_msg = 'File terlalu besar (php.ini)'; break;
                                                case 2: $error_msg = 'File terlalu besar (form)'; break;
                                                case 3: $error_msg = 'File hanya terupload sebagian'; break;
                                                case 4: $error_msg = 'Tidak ada file yang dipilih'; break;
                                                case 6: $error_msg = 'Folder temporary tidak ditemukan'; break;
                                                case 7: $error_msg = 'Gagal menulis file ke disk'; break;
                                                case 8: $error_msg = 'Ekstensi file dihentikan'; break;
                                            }
                                        }
                                        throw new Exception($error_msg);
                                    }
                                    
                                    // Cek ekstensi file
                                    $allowed_ext = ['xls', 'xlsx', 'csv'];
                                    $file_name = $_FILES['file_excel']['name'];
                                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                    $file_size = $_FILES['file_excel']['size'];
                                    $file_tmp = $_FILES['file_excel']['tmp_name'];
                                    
                                    if (!in_array($file_ext, $allowed_ext)) {
                                        throw new Exception("Format file tidak didukung. Gunakan .xls, .xlsx, atau .csv");
                                    }
                                    
                                    if ($file_size > 5242880) {
                                        throw new Exception("Ukuran file maksimal 5MB");
                                    }
                                    
                                    // Load PHPSpreadsheet
                                    require_once __DIR__ . '/../vendor/autoload.php';
                                    
                                    // Load file Excel
                                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_tmp);
                                    $worksheet = $spreadsheet->getActiveSheet();
                                    $rows = $worksheet->toArray();
                                    
                                    if (empty($rows)) {
                                        throw new Exception("File Excel kosong atau tidak memiliki data");
                                    }
                                    
                                    $skipFirstRow = isset($_POST['skipFirstRow']) ? true : false;
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
                                        
                                        // Ambil data dari Excel (sesuai struktur tabel training_stunting)
                                        $id_balita       = isset($row[0]) ? trim($row[0]) : '';
                                        $id_pengukuran   = isset($row[1]) ? trim($row[1]) : '';                                            
                                        $usia_bulan      = isset($row[2]) ? trim($row[2]) : '';
                                        $jenis_kelamin   = isset($row[3]) ? trim($row[3]) : '';
                                        $berat_badan     = isset($row[4]) ? trim($row[4]) : '';
                                        $tinggi_badan    = isset($row[5]) ? trim($row[5]) : '';
                                        $status_stunting = isset($row[6]) ? trim($row[6]) : '';
                                        $tipe_data       = isset($row[7]) ? trim($row[7]) : 'Training';
                                        
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

                                        // 2. Validasi ID Pengukuran
                                        if (empty($id_pengukuran)) {
                                            $errors[] = "ID Pengukuran kosong";
                                        } elseif (!is_numeric($id_pengukuran)) {
                                            $errors[] = "ID Pengukuran harus angka";
                                        } else {
                                            // Cek apakah ID Pengukuran ada di database
                                            $query_cek = "SELECT id_pengukuran FROM pengukuran WHERE id_pengukuran = ?";
                                            $stmt = mysqli_prepare($koneksi, $query_cek);
                                            mysqli_stmt_bind_param($stmt, 'i', $id_pengukuran);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);
                                            
                                            if (mysqli_num_rows($result) == 0) {
                                                $errors[] = "ID Pengukuran $id_pengukuran tidak ditemukan";
                                            }
                                            mysqli_stmt_close($stmt);
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

                                        // 4. Validasi Jenis Kelamin
                                        if (!empty($jenis_kelamin)) {
                                            $jk_lower = strtolower($jenis_kelamin);
                                            if (in_array($jk_lower, ['perempuan', 'wanita', 'female', 'p'])) {
                                                $jenis_kelamin = 'L';
                                            } elseif (in_array($jk_lower, ['laki-laki', 'laki', 'pria', 'male', 'l'])) {
                                                $jenis_kelamin = 'P';
                                            } else {
                                                $errors[] = "Jenis kelamin tidak valid. Gunakan: Laki-laki atau Perempuan";
                                            }
                                        } else {
                                            $jenis_kelamin = null;
                                        }
                                        
                                        // 5. Validasi Berat Badan
                                        if (empty($berat_badan)) {
                                            $errors[] = "Berat badan kosong";
                                        } elseif (!is_numeric($berat_badan)) {
                                            $errors[] = "Berat badan harus angka";
                                        } elseif ($berat_badan < 1 || $berat_badan > 30) {
                                            $errors[] = "Berat badan harus antara 1-30 kg";
                                        }
                                        
                                        // 6. Validasi Tinggi Badan
                                        if (empty($tinggi_badan)) {
                                            $errors[] = "Tinggi badan kosong";
                                        } elseif (!is_numeric($tinggi_badan)) {
                                            $errors[] = "Tinggi badan harus angka";
                                        } elseif ($tinggi_badan < 30 || $tinggi_badan > 150) {
                                            $errors[] = "Tinggi badan harus antara 30-150 cm";
                                        }
                                        
                                        // 7. Validasi Status Stunting
                                        if (!empty($status_stunting)) {
                                            $status_lower = strtolower($status_stunting);
                                            if (in_array($status_lower, ['stunting', 'ya', 's'])) {
                                                $status_stunting = 'Stunting';
                                            } elseif (in_array($status_lower, ['tidak stunting', 'tidak', 'normal', 't'])) {
                                                $status_stunting = 'Tidak stunting';
                                            } else {
                                                $errors[] = "Status stunting tidak valid. Gunakan: Stunting atau Tidak stunting";
                                            }
                                        } else {
                                            $status_stunting = null;
                                        }
                                        
                                        // 8. Validasi Tipe Data
                                        if (!empty($tipe_data)) {
                                            $tipe_lower = strtolower($tipe_data);
                                            if (in_array($tipe_lower, ['training', 'train', 'data latih'])) {
                                                $tipe_data = 'Training';
                                            } elseif (in_array($tipe_lower, ['testing', 'test', 'data uji'])) {
                                                $tipe_data = 'Testing';
                                            } else {
                                                $tipe_data = 'Training'; // default
                                            }
                                        } else {
                                            $tipe_data = 'Training';
                                        }
                                        
                                        // Jika ada error, skip row
                                        if (!empty($errors)) {
                                            $error_count++;
                                            $error_messages[] = "Baris $row_number: " . implode(", ", $errors);
                                            continue;
                                        }
                                        
                                        // Cek duplikasi (id_balita + id_pengukuran harus unik)
                                        $query_cek_duplikat = "SELECT id_training FROM training_stunting 
                                                                WHERE id_balita = ? AND id_pengukuran = ?";
                                        $stmt_cek = mysqli_prepare($koneksi, $query_cek_duplikat);
                                        mysqli_stmt_bind_param($stmt_cek, 'ii', $id_balita, $id_pengukuran);
                                        mysqli_stmt_execute($stmt_cek);
                                        mysqli_stmt_store_result($stmt_cek);
                                        
                                        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
                                            // Update data yang sudah ada
                                            $query = "UPDATE training_stunting SET 
                                                    usia_bulan = ?, 
                                                    jenis_kelamin = ?,
                                                    berat_badan = ?, 
                                                    tinggi_badan = ?, 
                                                    status_stunting = ?, 
                                                    tipe_data = ?,
                                                    tanggal_input = NOW()
                                                    WHERE id_balita = ? AND id_pengukuran = ?";
                                            
                                            $stmt = mysqli_prepare($koneksi, $query);
                                            mysqli_stmt_bind_param($stmt, 'ssddssii', 
                                                $usia_bulan,
                                                $jenis_kelamin,
                                                $berat_badan,
                                                $tinggi_badan,
                                                $status_stunting,
                                                $tipe_data,
                                                $id_balita,
                                                $id_pengukuran
                                            );
                                        } else {
                                            // Insert data baru
                                            $query = "INSERT INTO training_stunting 
                                                    (id_balita, id_pengukuran, usia_bulan, jenis_kelamin, 
                                                    berat_badan, tinggi_badan, status_stunting, tipe_data, tanggal_input) 
                                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                                            
                                            $stmt = mysqli_prepare($koneksi, $query);
                                            mysqli_stmt_bind_param($stmt, 'iiisdsss', 
                                                $id_balita,
                                                $id_pengukuran,
                                                $usia_bulan,
                                                $jenis_kelamin,
                                                $berat_badan,
                                                $tinggi_badan,
                                                $status_stunting,
                                                $tipe_data
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
                                    $html_message .= "<h5><i class='fas fa-database'></i> Hasil Import Data Training</h5>";
                                    $html_message .= "<div class='row'>";
                                    $html_message .= "<div class='col-md-4'><p><span class='badge badge-success' style='font-size: 14px;'>✅ Berhasil:</span> <strong>$success_count</strong> data</p></div>";
                                    $html_message .= "<div class='col-md-4'><p><span class='badge badge-danger' style='font-size: 14px;'>❌ Gagal:</span> <strong>$error_count</strong> data</p></div>";
                                    $html_message .= "<div class='col-md-4'><p><span class='badge badge-info' style='font-size: 14px;'>📊 Total Baris:</span> <strong>" . (count($rows) - $start_row) . "</strong> baris</p></div>";
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
                                            width: '700px'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location = 'index.php?stunting=training';
                                            }
                                        });
                                    </script>
                                    ";
                                    
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
                                                   <p><small>Pastikan file Excel memiliki format yang benar.</small></p>
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
                                        <th>Jenis Kelamin</th>
                                        <th>Berat Badan</th>
                                        <th>Tinggi Badan</th>
                                        <th>Status Stunting</th>
                                        <th>Tipe Data</th>
                                        <th>Tanggal Input</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; ?>
                                    <?php foreach ($training as $data) : ?>
                                    <tr>
                                        <td><?= $i; ?></td>
                                        <td><?= $data['nama_balita'] ?></td>
                                        <td><?= $data['bulan_ukur'] ?></td>
                                        <td><?= $data['usia_bulan'] ?></td>
                                        <td><?= $data['jenis_kelamin'] ?></td>
                                        <td><?= $data['berat_badan'] ?></td>
                                        <td><?= $data['tinggi_badan'] ?></td>
                                        <td><?= $data['status_stunting'] ?></td>
                                        <td><?= $data['tipe_data'] ?></td>
                                        <td><?= $data['tanggal_input'] ?></td>
                                        <td class="d-flex justify-content-start">
                                            <a href="index.php?stunting=edit_training&id=<?= $data['id_training'] ?>"
                                                class="btn btn-info mr-1" title="Edit"><i
                                                    class="fa-solid fa-pen-to-square"></i></a>

                                            <a href="index.php?stunting=hapus_training&id=<?= $data['id_training'] ?>"
                                                class="btn btn-danger" id="hapus-training" title="Hapus"><i
                                                    class="fa-solid fa-circle-xmark"></i></a>
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
// Script untuk menampilkan nama file yang dipilih
document.getElementById('file_excel').addEventListener('change', function(e) {
    var fileName = e.target.files[0].name;
    var label = document.querySelector('.custom-file-label');
    label.textContent = fileName;
});
</script>