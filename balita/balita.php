<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><strong>Data Balita</strong></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?stunting=home">Home</a></li>
                        <li class="breadcrumb-item active">Balita</li>
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
                            <h5 class="card-title">Tabel Data Balita</h5>
                        </div>
                        <div class="card-body">
                            <!-- Button Tambah -->
                            <button type="button" class="btn btn-primary mb-2" data-toggle="modal"
                                data-target="#modalTambah">
                                <i class="fa-solid fa-square-plus"></i> Tambah Data Balita
                            </button>

                            <!-- Modal Tambah-->
                            <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary">
                                            <h5 class="modal-title" id="modalTambahLabel"><strong>Form Tambah Data
                                                    Balita</strong></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="" method="POST" id="formTambahBalita">
                                                <div class="form-group">
                                                    <label for="nama_balita">Nama Balita</label>
                                                    <input type="text" name="nama_balita" id="nama_balita"
                                                        class="form-control" placeholder="Masukan Nama Balita" required
                                                        autocomplete="off">
                                                </div>
                                                <div class="form-group">
                                                    <label for="jenis_kelamin">Jenis Kelamin</label>
                                                    <select name="jenis_kelamin" id="jenis_kelamin"
                                                        class="form-control">
                                                        <option value="">Pilih Jenis Kelamin...</option>
                                                        <option value="Laki-laki">Laki-laki</option>
                                                        <option value="Perempuan">Perempuan</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="tanggal_lahir">Tanggal Lahir</label>
                                                    <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                                                        class="form-control" required autocomplete="off">
                                                </div>
                                                <div class="form-group">
                                                    <label for="alamat">Alamat</label>
                                                    <textarea name="alamat" id="alamat" class="form-control"
                                                        placeholder="Masukan Alamat"></textarea>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default"
                                                        data-dismiss="modal"><i class="fas fa-times"></i>
                                                        Keluar</button>
                                                    <button type="submit" name="tambah" form="formTambahBalita"
                                                        class="btn btn-primary"><i class="fas fa-save"></i>
                                                        Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Modal Tambah -->

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
                                            <h5 class="modal-title" id="modalImportLabel"><strong>Import Data dari
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
                                                        Format yang didukung: .xlsx, .xls, .csv
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
                                                    <a href="template_excel/download_template.php"
                                                        class="btn btn-outline-success btn-sm" target="_blank">
                                                        <i class="fas fa-download"></i> Download Template Excel
                                                    </a>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                                <i class="fas fa-times"></i> Batal
                                            </button>
                                            <button type="submit" name="import" form="formImportExcel"
                                                class="btn btn-success">
                                                <i class="fas fa-upload"></i> Import Data
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Modal Import Excel -->
                            <?php
                            // Query untuk memanggil data dari tabel balita
                            $balita = query("SELECT * FROM balita");

                            // Kondisi untuk menambah data balita
                            if (isset($_POST['tambah'])) {
                                if (tambah ($_POST) > 0) {
                                     echo "
                                    <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                                    <script>
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil',
                                            text: 'Data berhasil ditambahkan',
                                            confirmButtonText: 'OK'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location = 'index.php?stunting=balita';
                                            }
                                        });
                                    </script>
                                    ";
                                } else {
                                     echo "
                                    <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                                    <script>
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal',
                                            text: 'Terjadi kesalahan saat menambahkan data',
                                            confirmButtonText: 'OK'
                                        });
                                    </script>
                                    ";
                                }
                                
                            }                                                                                  
                            ?>

                            <?php
                            // Proses Import Excel - WORKING VERSION
                            if (isset($_POST['import'])) {
                                // Enable error reporting untuk debugging
                                error_reporting(E_ALL);
                                ini_set('display_errors', 1);
                                
                                try {
                                    // Load PHPSpreadsheet
                                    require_once __DIR__ . '/../vendor/autoload.php';
                                    
                                    $skipFirstRow = isset($_POST['skipFirstRow']) ? true : false;
                                    
                                    if (isset($_FILES['file_excel']) && $_FILES['file_excel']['error'] == 0) {
                                        $allowed_ext = ['xls', 'xlsx', 'csv'];
                                        $file_name = $_FILES['file_excel']['name'];
                                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                        $file_size = $_FILES['file_excel']['size'];
                                        $file_tmp = $_FILES['file_excel']['tmp_name'];
                                        
                                        // Validasi ekstensi file
                                        if (!in_array($file_ext, $allowed_ext)) {
                                            throw new Exception("Format file tidak didukung. Gunakan .xls, .xlsx, atau .csv");
                                        }
                                        
                                        // Validasi ukuran file (max 5MB)
                                        if ($file_size > 5242880) {
                                            throw new Exception("Ukuran file maksimal 5MB");
                                        }
                                        
                                        // Load file Excel
                                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_tmp);
                                        $worksheet = $spreadsheet->getActiveSheet();
                                        $rows = $worksheet->toArray();
                                        
                                        $success_count = 0;
                                        $error_count = 0;
                                        $error_messages = [];
                                        $start_row = $skipFirstRow ? 1 : 0;
                                        
                                        // Start database transaction
                                        mysqli_begin_transaction($koneksi);
                                        
                                        for ($i = $start_row; $i < count($rows); $i++) {
                                            $row = $rows[$i];
                                            $row_number = $i + 1;
                                            
                                            // Skip empty rows
                                            $row_has_data = false;
                                            foreach ($row as $cell) {
                                                if ($cell !== null && trim($cell) !== '') {
                                                    $row_has_data = true;
                                                    break;
                                                }
                                            }
                                            
                                            if (!$row_has_data) {
                                                continue;
                                            }
                                            
                                            // Ambil data
                                            $nama_balita = isset($row[0]) ? trim($row[0]) : '';
                                            $jenis_kelamin = isset($row[1]) ? trim($row[1]) : '';
                                            $tanggal_lahir = isset($row[2]) ? trim($row[2]) : '';
                                            $alamat = isset($row[3]) ? trim($row[3]) : '';
                                            
                                            // Validasi data wajib
                                            if (empty($nama_balita)) {
                                                $error_messages[] = "Baris $row_number: Nama balita kosong";
                                                $error_count++;
                                                continue;
                                            }
                                            
                                            if (empty($tanggal_lahir)) {
                                                $error_messages[] = "Baris $row_number: Tanggal lahir kosong";
                                                $error_count++;
                                                continue;
                                            }
                                            
                                            // Proses tanggal lahir
                                            $tanggal_valid = false;
                                            $tanggal_format = '';
                                            
                                            // Coba format Excel numeric
                                            if (is_numeric($tanggal_lahir)) {
                                                try {
                                                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal_lahir);
                                                    $tanggal_format = $date->format('Y-m-d');
                                                    $tanggal_valid = true;
                                                } catch (Exception $e) {
                                                    // Fallback manual
                                                    $unix_date = ($tanggal_lahir - 25569) * 86400;
                                                    $tanggal_format = date('Y-m-d', $unix_date);
                                                    $tanggal_valid = true;
                                                }
                                            } else {
                                                // Coba parsing string date
                                                $timestamp = strtotime($tanggal_lahir);
                                                if ($timestamp !== false) {
                                                    $tanggal_format = date('Y-m-d', $timestamp);
                                                    $tanggal_valid = true;
                                                }
                                            }
                                            
                                            if (!$tanggal_valid) {
                                                $error_messages[] = "Baris $row_number: Format tanggal tidak valid: '$tanggal_lahir'";
                                                $error_count++;
                                                continue;
                                            }
                                            
                                            // Validasi jenis kelamin
                                            $jenis_kelamin_fixed = 'Laki-laki'; // Default
                                            if (!empty($jenis_kelamin)) {
                                                $jk_lower = strtolower($jenis_kelamin);
                                                if (in_array($jk_lower, ['perempuan', 'wanita', 'female', 'p'])) {
                                                    $jenis_kelamin_fixed = 'Perempuan';
                                                } elseif (in_array($jk_lower, ['laki-laki', 'laki', 'pria', 'male', 'l'])) {
                                                    $jenis_kelamin_fixed = 'Laki-laki';
                                                }
                                            }
                                            
                                            // Escape SQL
                                            $nama_balita_sql = mysqli_real_escape_string($koneksi, $nama_balita);
                                            $jenis_kelamin_sql = mysqli_real_escape_string($koneksi, $jenis_kelamin_fixed);
                                            $alamat_sql = mysqli_real_escape_string($koneksi, $alamat);
                                            
                                            // Insert ke database
                                            $query = "INSERT INTO balita (nama_balita, jenis_kelamin, tanggal_lahir, alamat, tanggal_daftar) 
                                                    VALUES ('$nama_balita_sql', '$jenis_kelamin_sql', '$tanggal_format', '$alamat_sql', NOW())";
                                            
                                            if (mysqli_query($koneksi, $query)) {
                                                $success_count++;
                                            } else {
                                                $error_count++;
                                                $error_messages[] = "Baris $row_number: Database error - " . mysqli_error($koneksi);
                                            }
                                        }
                                        
                                        // Commit transaction
                                        mysqli_commit($koneksi);
                                        
                                        // Tampilkan hasil
                                        $icon = 'success';
                                        $title = 'Import Berhasil';
                                        
                                        if ($success_count == 0) {
                                            $icon = 'error';
                                            $title = 'Import Gagal';
                                        } elseif ($error_count > 0) {
                                            $icon = 'warning';
                                            $title = 'Import dengan Beberapa Error';
                                        }
                                        
                                        $html_message = "<div style='text-align: left;'>";
                                        $html_message .= "<h5>Hasil Import Data Balita</h5>";
                                        $html_message .= "<p><span style='color: green; font-weight: bold;'>✅ Berhasil diimport:</span> $success_count data</p>";
                                        $html_message .= "<p><span style='color: red; font-weight: bold;'>❌ Gagal diimport:</span> $error_count data</p>";
                                        
                                        if (!empty($error_messages)) {
                                            $html_message .= "<hr><h6>Detail Error:</h6>";
                                            $html_message .= "<div style='max-height: 200px; overflow-y: auto; padding: 10px; background: #f8f9fa; border-radius: 5px;'>";
                                            foreach ($error_messages as $error) {
                                                $html_message .= "<p style='margin: 5px 0; color: #dc3545; font-size: 12px;'>• $error</p>";
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
                                                width: '650px',
                                                customClass: {
                                                    popup: 'import-result-popup'
                                                }
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location = 'index.php?stunting=balita';
                                                }
                                            });
                                        </script>
                                        ";
                                        
                                    } else {
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
                                                <p>Pastikan file Excel tidak rusak dan formatnya benar.</p>
                                                </div>`,
                                            confirmButtonText: 'OK',
                                            width: '700px'
                                        });
                                    </script>
                                    ";
                                }
                            }
                            ?>

                            <!-- Tabel data balita -->
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Balita</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Alamat</th>
                                        <th>Tanggal Pendaftaran</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; ?>
                                    <?php foreach ($balita as $data) : ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td><?= $data["nama_balita"]; ?></td>
                                        <td><?= $data["jenis_kelamin"]; ?></td>
                                        <td><?= $data["tanggal_lahir"]; ?></td>
                                        <td><?= $data["alamat"]; ?></td>
                                        <td><?= $data["tanggal_daftar"]; ?></td>
                                        <td class="d-flex justify-content-start">
                                            <!-- Button Edit Data -->
                                            <button type="button" class="btn btn-primary mr-1" title="Edit"
                                                data-toggle="modal" data-target="#modalEdit<?= $data['id_balita'] ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>

                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="modalEdit<?= $data['id_balita'] ?>"
                                                tabindex="-1" aria-labelledby="modalEditLabel<?= $data['id_balita'] ?>"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-info">
                                                            <h5 class="modal-title"
                                                                id="modalEditLabel<?= $data['id_balita'] ?>">
                                                                <strong>Form
                                                                    Edit
                                                                    Data Balita</strong>
                                                            </h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="" method="POST">
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label
                                                                        for="nama_balita_<?= $data['id_balita'] ?>">Nama
                                                                        Balita</label>
                                                                    <input type="text" name="nama_balita"
                                                                        id="nama_balita_<?= $data['id_balita'] ?>"
                                                                        class="form-control"
                                                                        value="<?= $data['nama_balita'] ?>"
                                                                        placeholder="Masukan Nama Balita" required
                                                                        autocomplete="off">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label
                                                                        for="jenis_kelamin_<?= $data['id_balita'] ?>">Jenis
                                                                        Kelamin</label>
                                                                    <select name="jenis_kelamin"
                                                                        id="jenis_kelamin_<?= $data['id_balita'] ?>"
                                                                        class="form-control">
                                                                        <option value="<?= $data['jenis_kelamin'] ?>">
                                                                            <?= $data['jenis_kelamin'] ?></option>
                                                                        <option value="Laki-laki">Laki-laki</option>
                                                                        <option value="Perempuan">Perempuan</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label
                                                                        for="tanggal_lahir_<?= $data['id_balita'] ?>">Tanggal
                                                                        Lahir</label>
                                                                    <input type="date" name="tanggal_lahir"
                                                                        id="tanggal_lahir_<?= $data['id_balita'] ?>"
                                                                        value="<?= $data['tanggal_lahir'] ?>"
                                                                        class="form-control" required
                                                                        autocomplete="off">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label
                                                                        for="alamat_<?= $data['id_balita'] ?>">Alamat</label>
                                                                    <textarea name="alamat"
                                                                        id="alamat_<?= $data['id_balita'] ?>"
                                                                        value="<?= $data['alamat'] ?>"
                                                                        class="form-control"
                                                                        placeholder="Masukan Alamat"><?= $data['alamat'] ?></textarea>
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <input type="hidden" name="id_balita"
                                                                        value="<?= $data['id_balita'] ?>">
                                                                    <button type="button" class="btn btn-default"
                                                                        data-dismiss="modal"><i
                                                                            class="fas fa-times"></i>
                                                                        Keluar</button>
                                                                    <button type="submit" name="update"
                                                                        class="btn btn-info"><i class="fas fa-save"></i>
                                                                        Update</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Modal Edit -->

                                            <a href="javascript:void(0);"
                                                onclick="konfirmasiHapus(<?= $data['id_balita'] ?>)"
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
// Untuk menampilkan nama file di custom file input
document.getElementById('file_excel').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file...';
    var nextSibling = e.target.nextElementSibling;
    nextSibling.innerText = fileName;
});

// SweetAlert untuk konfirmasi hapus
function konfirmasiHapus(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php?stunting=balita&hapus=' + id;
        }
    });
}
</script>

<?php
if (isset($_POST['update'])) {
    $id_balita             = mysqli_real_escape_string($koneksi, $_POST['id_balita']);
    $nama_balita           = mysqli_real_escape_string($koneksi, $_POST['nama_balita']);
    $jenis_kelamin         = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $tanggal_lahir         = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $alamat                = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    $query = "UPDATE balita SET 
            nama_balita     = '$nama_balita',
            jenis_kelamin   = '$jenis_kelamin',
            tanggal_lahir   = '$tanggal_lahir',
            alamat          = '$alamat'
            WHERE id_balita = '$id_balita'";
    
    if (mysqli_query($koneksi, $query)) {
        if ($query) {
            echo "
            <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil diperbarui',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'index.php?stunting=balita';
                    }
                });
            </script>
            ";
        } else {
            echo "
            <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat memperbarui data',
                    confirmButtonText: 'OK'
                });
            </script>
            ";
        }
    }
}


?>