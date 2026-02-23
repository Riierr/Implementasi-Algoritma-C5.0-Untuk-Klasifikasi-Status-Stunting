<?php
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/model_c50.php';

// Inisialisasi variabel
$hasil_prediksi = null;
$message = '';
$usia = $jk = $berat = $tinggi = '';
$balita_id = 0;
$perhitungan_entropy = '';

// Handle form submission untuk prediksi tunggal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Klasifikasi tunggal
    if (isset($_POST['prediksi'])) {
        try {
            // Ambil data dari form
            $usia = isset($_POST['usia']) ? (int)$_POST['usia'] : 0;
            $jk = isset($_POST['jk']) ? $_POST['jk'] : '';
            $berat = isset($_POST['berat']) ? (float)$_POST['berat'] : 0;
            $tinggi = isset($_POST['tinggi']) ? (float)$_POST['tinggi'] : 0;
            $balita_id = !empty($_POST['nama_balita']) ? (int)$_POST['nama_balita'] : null;

            // Validasi sederhana
            if ($usia < 0 || $usia > 60 || $berat < 2 || $berat > 30 || $tinggi < 40 || $tinggi > 120 || !in_array($jk, ['L', 'P'])) {
                $message = '<div class="alert alert-danger">Mohon isi data dengan benar!</div>';
            } else {
                // Load dan training model C5.0
                $c50Model = new C5_0_Classifier($koneksi);
                $c50Model->trainFromDatabase();

                // Persiapkan data untuk Klasifikasi 
                $data = [
                    'usia' => $usia,
                    'jk' => $jk,
                    'berat' => $berat,
                    'tinggi' => $tinggi
                ];

                // Lakukan prediksi menggunakan C5.0
                $hasil_prediksi = $c50Model->predict($data);

                // SIMPAN PREDIKSI KE DATABASE
                if ($hasil_prediksi && !isset($hasil_prediksi['error'])) {
                    // Cari id_pengukuran jika ada balita_id           
                    $id_pengukuran = null;

                    if ($balita_id) {
                        // Cari pengukuran terakhir untuk balita ini
                        $sqlPengukuran = "SELECT id_pengukuran FROM pengukuran 
                                        WHERE id_balita = $balita_id 
                                        ORDER BY bulan_ukur DESC, id_pengukuran DESC LIMIT 1";
                        $resultPengukuran = mysqli_query($koneksi, $sqlPengukuran);

                        if ($resultPengukuran && mysqli_num_rows($resultPengukuran) > 0) {
                            $pengukuran = mysqli_fetch_assoc($resultPengukuran);
                            $id_pengukuran = $pengukuran['id_pengukuran'];
                        }
                    }

                    // Set default values untuk database
                    $balita_id = $balita_id ? intval($balita_id) : 'NULL';
                    $id_pengukuran = $id_pengukuran ? intval($id_pengukuran) : 'NULL';
                    $modelId = 2; // ID untuk C5.0
                    $prediksi_status = mysqli_real_escape_string($koneksi, $hasil_prediksi['status']);
                    $confidence = floatval($hasil_prediksi['confidence']);

                    // Query untuk menyimpan prediksi
                    $sql = "INSERT INTO prediksi_stunting 
                            (id_balita, id_pengukuran, id_model, usia_bulan, berat_badan, tinggi_badan, 
                             prediksi, confidence, benar_salah, tanggal_prediksi) 
                            VALUES ($balita_id, $id_pengukuran, $modelId, $usia, $berat, $tinggi, 
                                    '$prediksi_status', $confidence, 'Belum Dicek', NOW())";

                    if (mysqli_query($koneksi, $sql)) {
                        $id_prediksi = mysqli_insert_id($koneksi);
                        $message = '<div class="alert alert-success">Prediksi berhasil disimpan (ID: ' . $id_prediksi . ')</div>';
                    } else {
                        $message = '<div class="alert alert-warning">Prediksi berhasil tapi gagal disimpan: ' . mysqli_error($koneksi) . '</div>';
                    }
                } elseif (isset($hasil_prediksi['error'])) {
                    $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($hasil_prediksi['error']) . '</div>';
                }
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    // Prediksi massal data testing
    if (isset($_POST['predict_testing'])) {
        try {
            // Load dan training model C5.0
            $c50Model = new C5_0_Classifier($koneksi);
            $c50Model->trainFromDatabase();

            // Prediksi semua data testing
            $saveToDb = isset($_POST['save_to_db']) ? true : false;
            $testingResult = $c50Model->predictAllTestingData($saveToDb);

            if ($testingResult['success']) {
                $_SESSION['testing_results'] = $testingResult;
                $message = '<div class="alert alert-success">Berhasil Mengklasifikasian ' . $testingResult['summary']['total_data'] . ' data testing. Akurasi: ' . $testingResult['accuracy_percentage'] . '%</div>';
            } else {
                $message = '<div class="alert alert-danger">Error: ' . $testingResult['message'] . '</div>';
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    // Build tree (training) dari form
    if (isset($_POST['build_tree'])) {
        try {
            $c50Model = new C5_0_Classifier($koneksi);
            $result = $c50Model->trainFromDatabase();

            if ($result) {
                $message = '<div class="alert alert-success">Pohon keputusan C5.0 berhasil dibangun!</div>';
                // Tampilkan pohon
                echo '<script>$(document).ready(function() { $("#tree-tab").tab("show"); });</script>';
            } else {
                $message = '<div class="alert alert-warning">Pohon dibangun dengan rules default</div>';
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error building tree: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Ambil data balita untuk dropdown (sederhana)
$balitas = [];
if (isset($koneksi)) {
    $sqlBalita = "SELECT id_balita, nama_balita FROM balita ORDER BY nama_balita LIMIT 1000";
    $result = mysqli_query($koneksi, $sqlBalita);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $balitas[] = $row;
        }
    }
}

// Ambil jumlah data testing
$totalTesting = 0;
$sqlTestingCount = "SELECT COUNT(*) as total FROM training_stunting WHERE tipe_data = 'Testing' AND status_stunting IS NOT NULL";
$resultCount = mysqli_query($koneksi, $sqlTestingCount);
if ($resultCount) {
    $row = mysqli_fetch_assoc($resultCount);
    $totalTesting = $row['total'];
}

// Ambil jumlah data training
$totalTraining = 0;
$sqlTrainingCount = "SELECT COUNT(*) as total FROM training_stunting WHERE tipe_data = 'Training' AND status_stunting IS NOT NULL";
$resultTrainingCount = mysqli_query($koneksi, $sqlTrainingCount);
if ($resultTrainingCount) {
    $row = mysqli_fetch_assoc($resultTrainingCount);
    $totalTraining = $row['total'];
}

// Cek apakah ada hasil testing yang disimpan di session
$testingResults = isset($_SESSION['testing_results']) ? $_SESSION['testing_results'] : null;

// Cek model C5.0
$model_info = '<span class="badge badge-info">Model C5.0</span>';

// Tampilkan tree jika diminta
$showTree = isset($_POST['show_tree']) || isset($_GET['show_tree']);
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><strong>Klasifikasi Stunting dengan C5.0</strong></h1>
                    <small class="text-muted"><?php echo $model_info; ?></small>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php echo $message; ?>

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="single-tab" data-toggle="tab" href="#single" role="tab"
                        aria-controls="single" aria-selected="true">
                        <i class="fas fa-user"></i> Klasifikasi Tunggal
                    </a>
                </li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" id="testing-tab" data-toggle="tab" href="#testing" role="tab"
                            aria-controls="testing" aria-selected="false">
                            <i class="fas fa-database"></i> Klasifikasi Testing
                        </a>
                    </li>
                    <?php if ($testingResults): ?>
                        <li class="nav-item">
                            <a class="nav-link" id="results-tab" data-toggle="tab" href="#results" role="tab"
                                aria-controls="results" aria-selected="false">
                                <i class="fas fa-chart-bar"></i> Hasil Testing
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <div class="tab-content mt-3" id="myTabContent">
                <!-- Tab 1: Prediksi Tunggal -->
                <div class="tab-pane fade show active" id="single" role="tabpanel" aria-labelledby="single-tab">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h5 class="card-title">Form Klasifikasi Stunting</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="formPrediksi">
                                        <!-- Form dengan Select2 -->
                                        <div class="form-group">
                                            <label>Pilih Balita (Opsional)</label>
                                            <select name="nama_balita" id="select-balita" class="form-control select2">
                                                <option value="">-- Pilih balita --</option>
                                                <?php foreach ($balitas as $balita): ?>
                                                    <option value="<?php echo $balita['id_balita']; ?>"
                                                        <?php echo ($balita_id == $balita['id_balita']) ? 'selected' : ''; ?>
                                                        data-nama="<?php echo htmlspecialchars($balita['nama_balita']); ?>"
                                                        data-usia="<?php echo isset($balita['usia_bulan']) ? $balita['usia_bulan'] : ''; ?>"
                                                        data-berat="<?php echo isset($balita['berat_terakhir']) ? $balita['berat_terakhir'] : ''; ?>"
                                                        data-tinggi="<?php echo isset($balita['tinggi_terakhir']) ? $balita['tinggi_terakhir'] : ''; ?>"
                                                        data-jk="<?php echo isset($balita['jenis_kelamin']) ? $balita['jenis_kelamin'] : ''; ?>">
                                                        <?php echo htmlspecialchars($balita['nama_balita']); ?>
                                                        <?php if (isset($balita['usia_bulan'])): ?><?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="form-text text-muted">Ketik untuk mencari nama balita</small>
                                        </div>

                                        <style>
                                            /* Custom styling untuk Select2 */
                                            .select2-container--default .select2-selection--single {
                                                border: 1px solid #ced4da;
                                                border-radius: 0.25rem;
                                                height: calc(2.25rem + 2px);
                                                padding: 0.375rem 0.75rem;
                                            }

                                            .select2-container--default .select2-selection--single .select2-selection__rendered {
                                                line-height: 1.5;
                                                padding-left: 0;
                                            }

                                            .select2-container--default .select2-selection--single .select2-selection__arrow {
                                                height: calc(2.25rem + 2px);
                                            }

                                            .select2-container--default .select2-search--dropdown .select2-search__field {
                                                border: 1px solid #ced4da;
                                                border-radius: 0.25rem;
                                            }

                                            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                                                background-color: #4A90E2;
                                            }

                                            .balita-notification {
                                                margin-top: 10px;
                                                animation: slideDown 0.3s ease;
                                            }

                                            @keyframes slideDown {
                                                from {
                                                    opacity: 0;
                                                    transform: translateY(-10px);
                                                }

                                                to {
                                                    opacity: 1;
                                                    transform: translateY(0);
                                                }
                                            }
                                        </style>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Usia (bulan) *</label>
                                                    <input type="number" name="usia" class="form-control" min="0"
                                                        max="60" value="<?php echo htmlspecialchars($usia); ?>"
                                                        required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Jenis Kelamin *</label>
                                                    <select name="jk" class="form-control" required>
                                                        <option value="">-- Pilih --</option>
                                                        <option value="L" <?php echo ($jk == 'L') ? 'selected' : ''; ?>>
                                                            Laki-laki</option>
                                                        <option value="P" <?php echo ($jk == 'P') ? 'selected' : ''; ?>>
                                                            Perempuan</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Berat Badan (kg) *</label>
                                                    <input type="number" name="berat" class="form-control" step="0.1"
                                                        min="2" max="30" value="<?php echo htmlspecialchars($berat); ?>"
                                                        required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Tinggi Badan (cm) *</label>
                                                    <input type="number" name="tinggi" class="form-control" step="0.1"
                                                        min="40" max="120"
                                                        value="<?php echo htmlspecialchars($tinggi); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group text-center mt-4">
                                            <button type="submit" name="prediksi" class="btn btn-primary btn-lg">
                                                <i class="fas fa-search"></i> Prediksi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <?php if ($hasil_prediksi && !isset($hasil_prediksi['error'])): ?>
                                <?php
                                // Fungsi untuk menghitung berat dan tinggi ideal berdasarkan WHO
                                function hitungBeratTinggiIdealWHO($usia_bulan, $jenis_kelamin)
                                {
                                    // Data referensi WHO untuk berat badan (kg) berdasarkan usia
                                    $berat_ideal_laki = [
                                        0 => 3.3,   // 0 bulan
                                        1 => 4.5,   // 1 bulan
                                        3 => 6.0,   // 3 bulan
                                        6 => 7.9,   // 6 bulan
                                        9 => 9.2,   // 9 bulan
                                        12 => 10.2, // 12 bulan
                                        18 => 11.5, // 18 bulan
                                        24 => 12.7, // 24 bulan
                                        30 => 13.9, // 30 bulan
                                        36 => 15.0, // 36 bulan
                                        42 => 16.1, // 42 bulan
                                        48 => 17.2, // 48 bulan
                                        54 => 18.3, // 54 bulan
                                        60 => 19.4  // 60 bulan
                                    ];

                                    $berat_ideal_perempuan = [
                                        0 => 3.2,   // 0 bulan
                                        1 => 4.2,   // 1 bulan
                                        3 => 5.4,   // 3 bulan
                                        6 => 7.3,   // 6 bulan
                                        9 => 8.6,   // 9 bulan
                                        12 => 9.5,  // 12 bulan
                                        18 => 10.8, // 18 bulan
                                        24 => 12.1, // 24 bulan
                                        30 => 13.3, // 30 bulan
                                        36 => 14.6, // 36 bulan
                                        42 => 15.8, // 42 bulan
                                        48 => 17.1, // 48 bulan
                                        54 => 18.3, // 54 bulan
                                        60 => 19.5  // 60 bulan
                                    ];

                                    // Data referensi WHO untuk tinggi badan (cm) berdasarkan usia
                                    $tinggi_ideal_laki = [
                                        0 => 49.9,  // 0 bulan
                                        1 => 54.7,  // 1 bulan
                                        3 => 61.4,  // 3 bulan
                                        6 => 67.6,  // 6 bulan
                                        9 => 72.3,  // 9 bulan
                                        12 => 76.1, // 12 bulan
                                        18 => 82.4, // 18 bulan
                                        24 => 87.8, // 24 bulan
                                        30 => 92.4, // 30 bulan
                                        36 => 96.1, // 36 bulan
                                        42 => 99.9, // 42 bulan
                                        48 => 103.3, // 48 bulan
                                        54 => 106.7, // 54 bulan
                                        60 => 110.0 // 60 bulan
                                    ];

                                    $tinggi_ideal_perempuan = [
                                        0 => 49.1,  // 0 bulan
                                        1 => 53.7,  // 1 bulan
                                        3 => 59.8,  // 3 bulan
                                        6 => 65.7,  // 6 bulan
                                        9 => 70.4,  // 9 bulan
                                        12 => 74.3, // 12 bulan
                                        18 => 80.2, // 18 bulan
                                        24 => 86.4, // 24 bulan
                                        30 => 91.2, // 30 bulan
                                        36 => 95.1, // 36 bulan
                                        42 => 98.7, // 42 bulan
                                        48 => 102.1, // 48 bulan
                                        54 => 105.5, // 54 bulan
                                        60 => 108.7 // 60 bulan
                                    ];

                                    // Pilih data berdasarkan jenis kelamin
                                    $berat_data = ($jenis_kelamin == 'L') ? $berat_ideal_laki : $berat_ideal_perempuan;
                                    $tinggi_data = ($jenis_kelamin == 'L') ? $tinggi_ideal_laki : $tinggi_ideal_perempuan;

                                    // Cari usia terdekat
                                    $usia_keys = array_keys($berat_data);
                                    $usia_terdekat = 0;

                                    foreach ($usia_keys as $usia_key) {
                                        if ($usia_bulan >= $usia_key) {
                                            $usia_terdekat = $usia_key;
                                        }
                                    }

                                    // Interpolasi untuk usia di antara data referensi
                                    $next_usia = 0;
                                    foreach ($usia_keys as $usia_key) {
                                        if ($usia_key > $usia_terdekat) {
                                            $next_usia = $usia_key;
                                            break;
                                        }
                                    }

                                    if ($next_usia > 0) {
                                        // Hitung berat ideal dengan interpolasi linear
                                        $berat1 = $berat_data[$usia_terdekat];
                                        $berat2 = $berat_data[$next_usia];
                                        $selisih_usia = $next_usia - $usia_terdekat;
                                        $selisih_berat = $berat2 - $berat1;

                                        $berat_ideal = $berat1 + (($usia_bulan - $usia_terdekat) * ($selisih_berat / $selisih_usia));

                                        // Hitung tinggi ideal dengan interpolasi linear
                                        $tinggi1 = $tinggi_data[$usia_terdekat];
                                        $tinggi2 = $tinggi_data[$next_usia];
                                        $selisih_tinggi = $tinggi2 - $tinggi1;

                                        $tinggi_ideal = $tinggi1 + (($usia_bulan - $usia_terdekat) * ($selisih_tinggi / $selisih_usia));
                                    } else {
                                        $berat_ideal = $berat_data[$usia_terdekat];
                                        $tinggi_ideal = $tinggi_data[$usia_terdekat];
                                    }

                                    // Bulatkan hasil
                                    $berat_ideal = round($berat_ideal, 1);
                                    $tinggi_ideal = round($tinggi_ideal, 1);

                                    return [
                                        'berat_ideal' => $berat_ideal,
                                        'tinggi_ideal' => $tinggi_ideal
                                    ];
                                }

                                // Hitung berat dan tinggi ideal
                                $ideal = hitungBeratTinggiIdealWHO($usia, $jk);
                                $berat_ideal = $ideal['berat_ideal'];
                                $tinggi_ideal = $ideal['tinggi_ideal'];

                                // Hitung selisih
                                $selisih_berat = round($berat_ideal - $berat, 1);
                                $selisih_tinggi = round($tinggi_ideal - $tinggi, 1);

                                // Tentukan status
                                $status_berat = ($selisih_berat > 0) ? 'Kurang' : (($selisih_berat < -1) ? 'Lebih' : 'Normal');
                                $status_tinggi = ($selisih_tinggi > 0) ? 'Pendek' : 'Normal';

                                $warna_berat = ($status_berat == 'Kurang') ? 'danger' : (($status_berat == 'Lebih') ? 'warning' : 'success');
                                $warna_tinggi = ($status_tinggi == 'Pendek') ? 'danger' : 'success';

                                // Tentukan warna status utama
                                $status_warna = ($hasil_prediksi['status'] == 'Stunting') ? 'danger' : (($hasil_prediksi['status'] == 'Berisiko Stunting') ? 'warning' : 'success');
                                ?>
                                <div class="card mt-3">
                                    <div class="card-header bg-<?= $status_warna ?> text-white">
                                        <h5 class="card-title mb-0">HASIL KLASIFIKASI C5.0</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <h2 class="text-<?= $status_warna ?>">
                                                <?= $hasil_prediksi['status']; ?>
                                            </h2>
                                            <p class="lead">Confidence:
                                                <?= round($hasil_prediksi['confidence'] * 100, 1); ?>%</p>
                                        </div>

                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle"></i> Data Input:</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Usia:</strong> <?= $usia; ?> bulan</p>
                                                    <p class="mb-1"><strong>Jenis Kelamin:</strong>
                                                        <?= $jk == 'L' ? 'Laki-laki' : 'Perempuan'; ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Berat Badan:</strong> <?= $berat; ?> kg</p>
                                                    <p class="mb-1"><strong>Tinggi Badan:</strong> <?= $tinggi; ?> cm</p>
                                                </div>
                                            </div>

                                            <!-- Standar Ideal -->
                                            <hr>
                                            <h6><i class="fas fa-balance-scale"></i> Standar Ideal WHO:</h6>
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <strong>Berat Ideal:</strong> <?= $berat_ideal ?> kg
                                                            <small class="text-white d-block">Untuk <?= $usia ?>
                                                                bulan</small>
                                                        </div>
                                                        <div>
                                                            <?php if ($status_berat == 'Kurang'): ?>
                                                                <span class="badge badge-danger">
                                                                    <i class="fas fa-arrow-down"></i> Kurang
                                                                    <?= abs($selisih_berat) ?> kg
                                                                </span>
                                                            <?php elseif ($status_berat == 'Lebih'): ?>
                                                                <span class="badge badge-warning">
                                                                    <i class="fas fa-arrow-up"></i> Lebih
                                                                    <?= abs($selisih_berat) ?> kg
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge badge-success">
                                                                    <i class="fas fa-check"></i> Normal
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <strong>Tinggi Ideal:</strong> <?= $tinggi_ideal ?> cm
                                                            <small class="text-white">Untuk <?= $usia ?>
                                                                bulan</small>
                                                        </div>
                                                        <div>
                                                            <?php if ($status_tinggi == 'Pendek'): ?>
                                                                <span class="badge badge-danger">
                                                                    <i class="fas fa-arrow-down"></i> Pendek
                                                                    <?= abs($selisih_tinggi) ?> cm
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge badge-success">
                                                                    <i class="fas fa-check"></i> Normal
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                            <?php if (isset($hasil_prediksi['rule_used'])): ?>
                                                <hr>
                                                <h6><i class="fas fa-code-branch"></i> Rule yang digunakan:</h6>
                                                <p class="mb-0"><?= htmlspecialchars($hasil_prediksi['rule_used']); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="alert alert-<?= $status_warna ?>">
                                            <h6><i class="fas fa-stethoscope"></i> Rekomendasi:</h6>
                                            <p class="mb-0">
                                                <?php
                                                if ($hasil_prediksi['status'] == 'Stunting') {
                                                    echo "Segera konsultasi ke dokter anak. ";
                                                    if ($status_berat == 'Kurang') {
                                                        echo "Tingkatkan asupan gizi untuk menambah berat badan sekitar <strong>" . abs($selisih_berat) . " kg</strong>. ";
                                                    }
                                                    if ($status_tinggi == 'Pendek') {
                                                        echo "Tingkatkan asupan kalsium dan vitamin D untuk menambah tinggi badan sekitar <strong>" . abs($selisih_tinggi) . " cm</strong>. ";
                                                    }
                                                    echo "Lakukan pemeriksaan rutin setiap bulan.";
                                                } elseif ($hasil_prediksi['status'] == 'Berisiko Stunting') {
                                                    echo "Perhatikan asupan gizi anak. ";
                                                    if ($status_berat == 'Kurang') {
                                                        echo "Targetkan penambahan berat badan <strong>" . abs($selisih_berat) . " kg</strong>. ";
                                                    }
                                                    if ($status_tinggi == 'Pendek') {
                                                        echo "Targetkan penambahan tinggi badan <strong>" . abs($selisih_tinggi) . " cm</strong>. ";
                                                    }
                                                    echo "Pantau pertumbuhan secara rutin di posyandu setiap bulan.";
                                                } else {
                                                    echo "Pertahankan pola makan sehat dan bergizi seimbang. ";
                                                    echo "Berat dan tinggi badan sudah sesuai standar WHO. ";
                                                    echo "Terus berikan ASI jika masih dalam usia ASI. ";
                                                    echo "Lakukan kontrol rutin ke posyandu untuk memantau perkembangan.";
                                                }
                                                ?>
                                            </p>

                                            <?php if ($hasil_prediksi['status'] == 'Stunting' || $hasil_prediksi['status'] == 'Berisiko Stunting'): ?>
                                                <hr class="my-2">
                                                <h6><i class="fas fa-lightbulb"></i> Tips Pencapaian Target:</h6>
                                                <ul class="mb-0">
                                                    <?php if ($status_berat == 'Kurang'): ?>
                                                        <li>Berikan makanan tinggi protein: telur, ikan, daging, tempe, tahu</li>
                                                        <li>Tambah frekuensi makan menjadi 3x utama + 2x selingan</li>
                                                    <?php endif; ?>

                                                    <?php if ($status_tinggi == 'Pendek'): ?>
                                                        <li>Tingkatkan asupan kalsium: susu, keju, yoghurt</li>
                                                        <li>Pastikan cukup vitamin D dari sinar matahari pagi</li>
                                                    <?php endif; ?>

                                                    <li>Konsumsi makanan bergizi seimbang</li>
                                                    <li>Periksa ke posyandu/puskesmas setiap bulan</li>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Prediksi Testing -->
                <div class="tab-pane fade" id="testing" role="tabpanel" aria-labelledby="testing-tab">
                    <div class="card card-success">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-database"></i> Klasifikasi Data Testing
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-database"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Data Testing Tersedia</span>
                                            <span class="info-box-number"><?php echo $totalTesting; ?> Data</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                            <span class="progress-description">
                                                Dari tabel training_stunting
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Model Aktif</span>
                                            <span class="info-box-number">C5.0</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                            <span class="progress-description">
                                                Algoritma dengan Gain Ratio
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <h5><i class="fas fa-info-circle"></i> Informasi</h5>
                                <p>Fitur ini akan melakukan prediksi terhadap semua data testing yang ada di database.
                                    Data testing diambil dari tabel <strong>training_stunting</strong> dengan tipe_data
                                    = 'Testing'.</p>
                                <p><strong>Proses:</strong></p>
                                <ol>
                                    <li>Load dan training model C5.0</li>
                                    <li>Konversi data testing ke interval</li>
                                    <li>Prediksi setiap data dengan tree</li>
                                    <li>Hitung akurasi dan metrik</li>
                                    <li>Simpan hasil (opsional)</li>
                                </ol>
                            </div>

                            <form method="POST" class="mt-4">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="saveToDb"
                                            name="save_to_db" value="1" checked>
                                        <label class="custom-control-label" for="saveToDb">
                                            <strong>Simpan hasil prediksi ke database</strong>
                                            <small class="text-muted d-block">Hasil akan disimpan di tabel
                                                prediksi_testing_c50 untuk analisis lebih lanjut</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" name="predict_testing" class="btn btn-success btn-lg">
                                        <i class="fas fa-play"></i> Jalankan Prediksi Massal
                                    </button>
                                    <p class="text-muted mt-2">
                                        <small>Proses mungkin memakan waktu beberapa detik tergantung jumlah
                                            data</small>
                                    </p>
                                </div>
                            </form>

                            <?php if ($totalTesting == 0): ?>
                                <div class="alert alert-warning mt-3">
                                    <h5><i class="fas fa-exclamation-triangle"></i> Peringatan</h5>
                                    <p>Tidak ada data testing yang ditemukan di database.</p>
                                    <p>Pastikan tabel <strong>training_stunting</strong> memiliki data dengan tipe_data =
                                        'Testing' dan status_stunting IS NOT NULL.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Hasil Testing (jika ada) -->
                <?php if ($testingResults): ?>
                    <div class="tab-pane fade" id="results" role="tabpanel" aria-labelledby="results-tab">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar"></i> Hasil Klasifikasi Data Testing - C5.0
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Ringkasan Hasil -->
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="info-box bg-success">
                                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Akurasi</span>
                                                <span
                                                    class="info-box-number"><?php echo $testingResults['accuracy_percentage']; ?>%</span>
                                                <div class="progress">
                                                    <div class="progress-bar"
                                                        style="width: <?php echo $testingResults['accuracy_percentage']; ?>%">
                                                    </div>
                                                </div>
                                                <span class="progress-description">
                                                    <?php echo $testingResults['summary']['correct_predictions']; ?> dari
                                                    <?php echo $testingResults['summary']['total_data']; ?> benar
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="info-box bg-info">
                                            <span class="info-box-icon"><i class="fas fa-thumbs-up"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Klasifikasi Benar</span>
                                                <span
                                                    class="info-box-number"><?php echo $testingResults['summary']['correct_predictions']; ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar"
                                                        style="width: <?php echo ($testingResults['summary']['correct_predictions'] / $testingResults['summary']['total_data']) * 100; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="info-box bg-danger">
                                            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Klasifikasi Salah</span>
                                                <span
                                                    class="info-box-number"><?php echo $testingResults['summary']['incorrect_predictions']; ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar"
                                                        style="width: <?php echo ($testingResults['summary']['incorrect_predictions'] / $testingResults['summary']['total_data']) * 100; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="info-box bg-warning">
                                            <span class="info-box-icon"><i class="fas fa-database"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Data</span>
                                                <span
                                                    class="info-box-number"><?php echo $testingResults['summary']['total_data']; ?></span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Confusion Matrix -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-table"></i> Confusion Matrix</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-center">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th colspan="3">Confusion Matrix - C5.0</th>
                                                    </tr>
                                                    <tr>
                                                        <th></th>
                                                        <th>Klasifikasi: Stunting</th>
                                                        <th>Klasifikasi: Tidak Stunting</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Aktual: Stunting</strong></td>
                                                        <td class="bg-success text-white">
                                                            <strong><?php echo $testingResults['summary']['confusion_matrix']['true_stunting']; ?></strong><br>
                                                            <small>True Positive</small>
                                                        </td>
                                                        <td class="bg-danger text-white">
                                                            <strong><?php echo $testingResults['summary']['confusion_matrix']['false_tidak_stunting']; ?></strong><br>
                                                            <small>False Negative</small>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Aktual: Tidak Stunting</strong></td>
                                                        <td class="bg-danger text-white">
                                                            <strong><?php echo $testingResults['summary']['confusion_matrix']['false_stunting']; ?></strong><br>
                                                            <small>False Positive</small>
                                                        </td>
                                                        <td class="bg-success text-white">
                                                            <strong><?php echo $testingResults['summary']['confusion_matrix']['true_tidak_stunting']; ?></strong><br>
                                                            <small>True Negative</small>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Performance Metrics -->
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-chart-pie"></i> Distribusi Kelas</h6>
                                                <ul class="list-group">
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center">
                                                        Aktual Stunting
                                                        <span
                                                            class="badge badge-danger badge-pill"><?php echo $testingResults['summary']['class_distribution']['actual']['Stunting'] ?? 0; ?></span>
                                                    </li>
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center">
                                                        Aktual Tidak Stunting
                                                        <span
                                                            class="badge badge-success badge-pill"><?php echo $testingResults['summary']['class_distribution']['actual']['Tidak Stunting'] ?? 0; ?></span>
                                                    </li>
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center">
                                                        Klasifikasi Stunting
                                                        <span
                                                            class="badge badge-warning badge-pill"><?php echo $testingResults['summary']['class_distribution']['predicted']['Stunting'] ?? 0; ?></span>
                                                    </li>
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center">
                                                        Klasifikasi Tidak Stunting
                                                        <span
                                                            class="badge badge-info badge-pill"><?php echo $testingResults['summary']['class_distribution']['predicted']['Tidak Stunting'] ?? 0; ?></span>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="col-md-6">
                                                <h6><i class="fas fa-calculator"></i> Metrik Performa</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th>Metrik</th>
                                                                <th>Kelas Stunting</th>
                                                                <th>Kelas Tidak Stunting</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Precision</td>
                                                                <td><?php echo number_format($testingResults['summary']['performance_metrics']['precision_stunting'], 4); ?>
                                                                </td>
                                                                <td><?php echo number_format($testingResults['summary']['performance_metrics']['precision_tidak'], 4); ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Recall</td>
                                                                <td><?php echo number_format($testingResults['summary']['performance_metrics']['recall_stunting'], 4); ?>
                                                                </td>
                                                                <td><?php echo number_format($testingResults['summary']['performance_metrics']['recall_tidak'], 4); ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>F1-Score</td>
                                                                <td><?php echo number_format($testingResults['summary']['performance_metrics']['f1_stunting'], 4); ?>
                                                                </td>
                                                                <td><?php echo number_format($testingResults['summary']['performance_metrics']['f1_tidak'], 4); ?>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detail Hasil Klasifikasi -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-list"></i> Detail Hasil Klasifikasi
                                            <span
                                                class="badge badge-secondary"><?php echo count($testingResults['results']); ?>
                                                Data</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped" id="resultsTable">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>ID Data</th>
                                                        <th>Usia</th>
                                                        <th>Berat</th>
                                                        <th>Tinggi</th>
                                                        <th>Aktual</th>
                                                        <th>Klasifikasi</th>
                                                        <th>Confidence</th>
                                                        <th>Status</th>
                                                        <th>Rule</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($testingResults['results'] as $index => $result): ?>
                                                        <tr
                                                            class="<?php echo $result['is_correct'] ? 'table-success' : 'table-danger'; ?>">
                                                            <td><?php echo $index + 1; ?></td>
                                                            <td><?php echo $result['id_training']; ?></td>
                                                            <td><?php echo $result['usia_bulan']; ?> bln</td>
                                                            <td><?php echo $result['berat_badan']; ?> kg</td>
                                                            <td><?php echo $result['tinggi_badan']; ?> cm</td>
                                                            <td>
                                                                <span
                                                                    class="badge badge-<?php echo $result['actual_status'] == 'Stunting' ? 'danger' : 'success'; ?>">
                                                                    <?php echo $result['actual_status']; ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge badge-<?php echo $result['predicted_status'] == 'Stunting' ? 'warning' : 'info'; ?>">
                                                                    <?php echo $result['predicted_status']; ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo round($result['confidence'] * 100, 1); ?>%</td>
                                                            <td>
                                                                <?php if ($result['is_correct']): ?>
                                                                    <span class="badge badge-success">Benar</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-danger">Salah</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted"
                                                                    title="<?php echo htmlspecialchars($result['rule_used']); ?>">
                                                                    <?php echo strlen($result['rule_used']) > 30 ? substr($result['rule_used'], 0, 30) . '...' : $result['rule_used']; ?>
                                                                </small>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="text-center mt-3">
                                            <button class="btn btn-outline-primary" onclick="exportToCSV()">
                                                <i class="fas fa-download"></i> Export ke CSV
                                            </button>
                                            <button class="btn btn-outline-info ml-2" onclick="clearResults()">
                                                <i class="fas fa-trash"></i> Clear Results
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
    // Validasi form Klasifikasi tunggal
    function validateForm() {
        const usia = document.querySelector('input[name="usia"]').value;
        const berat = document.querySelector('input[name="berat"]').value;
        const tinggi = document.querySelector('input[name="tinggi"]').value;
        const jk = document.querySelector('select[name="jk"]').value;

        if (!usia || usia < 0 || usia > 60) {
            alert('Usia harus antara 0-60 bulan');
            return false;
        }
        if (!berat || berat < 2 || berat > 30) {
            alert('Berat harus antara 2-30 kg');
            return false;
        }
        if (!tinggi || tinggi < 40 || tinggi > 120) {
            alert('Tinggi harus antara 40-120 cm');
            return false;
        }
        if (!jk) {
            alert('Pilih jenis kelamin');
            return false;
        }
        return true;
    }

    // Export hasil ke CSV
    function exportToCSV() {
        <?php if ($testingResults): ?>
            const results = <?php echo json_encode($testingResults['results']); ?>;

            // Header CSV
            let csv = 'ID Data,Usia (bln),Berat (kg),Tinggi (cm),Aktual,Prediksi,Confidence%,Status,Rule\n';

            // Data rows
            results.forEach(result => {
                csv += `${result.id_training},${result.usia_bulan},${result.berat_badan},${result.tinggi_badan},`;
                csv += `${result.actual_status},${result.predicted_status},`;
                csv += `${Math.round(result.confidence * 100)}%,${result.is_correct ? 'Benar' : 'Salah'},`;
                csv += `"${result.rule_used.replace(/"/g, '""')}"\n`;
            });

            // Download file
            const blob = new Blob([csv], {
                type: 'text/csv'
            });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `hasil_prediksi_c50_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        <?php endif; ?>
    }

    // Clear hasil dari session
    function clearResults() {
        if (confirm('Apakah Anda yakin ingin menghapus hasil testing dari session?')) {
            window.location.href = '?clear_results=true&tab=testing';
        }
    }

    // Inisialisasi DataTable untuk tabel hasil
    $(document).ready(function() {
        if ($('#resultsTable').length) {
            $('#resultsTable').DataTable({
                "pageLength": 10,
                "order": [
                    [0, "asc"]
                ],
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Berikut",
                        "previous": "Sebelum"
                    }
                }
            });
        }

        // Aktifkan form validasi
        const form = document.getElementById('formPrediksi');
        if (form) {
            form.onsubmit = function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
                return true;
            };
        }

        // Tab navigation
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab) {
            $(`#${tab}-tab`).tab('show');
        }

        // Clear results parameter
        if (urlParams.get('clear_results') === 'true') {
            // Send AJAX request to clear session
            $.post('clear_results.php', function() {
                window.location.href = 'klasifikasi.php?tab=testing';
            });
        }
    });

    // Scroll ke tab yang dipilih
    document.querySelectorAll('a[data-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const target = $(e.target).attr("href");
            window.location.hash = target;
        });
    });
</script>

<style>
    .info-box {
        min-height: 90px;
        margin-bottom: 15px;
    }

    .info-box .info-box-icon {
        height: 90px;
        line-height: 90px;
    }

    .info-box .progress {
        height: 5px;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, .075);
    }

    .badge-pill {
        padding-right: 0.6em;
        padding-left: 0.6em;
    }

    .nav-tabs .nav-link {
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        border-bottom-color: #fff;
        font-weight: 600;
    }

    .card-title small {
        font-size: 0.8em;
    }
</style>