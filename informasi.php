<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

// Jika user sudah login, redirect ke halaman utama
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'dokter') {
            header('Location: index.php?stunting=home');
        } else {
            header('Location: index.php?stunting=home_pasien');
        }
    }
    exit();
}

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/klasifikasi/model_c50.php';

// Inisialisasi variabel
$hasil_prediksi = null;
$message = '';
$usia = $jk = $berat = $tinggi = '';
$balita_id = 0;

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submission untuk prediksi tunggal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

            // Persiapkan data untuk prediksi
            $data = [
                'usia' => $usia,
                'jk' => $jk,
                'berat' => $berat,
                'tinggi' => $tinggi
            ];

            // Lakukan prediksi menggunakan C5.0
            $hasil_prediksi = $c50Model->predict($data);

            // Debug: Tampilkan hasil prediksi
            error_log("Hasil prediksi: " . print_r($hasil_prediksi, true));

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
                $balita_id_db = $balita_id ? intval($balita_id) : 'NULL';
                $id_pengukuran_db = $id_pengukuran ? intval($id_pengukuran) : 'NULL';
                $modelId = 2; // ID untuk C5.0
                $prediksi_status = mysqli_real_escape_string($koneksi, $hasil_prediksi['status']);
                $confidence = floatval($hasil_prediksi['confidence']);

                // Query untuk menyimpan prediksi
                $sql = "INSERT INTO prediksi_stunting 
                        (id_balita, id_pengukuran, id_model, usia_bulan, berat_badan, tinggi_badan, 
                         prediksi, confidence, benar_salah, tanggal_prediksi) 
                        VALUES ($balita_id_db, $id_pengukuran_db, $modelId, $usia, $berat, $tinggi, 
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
        error_log("Error prediksi: " . $e->getMessage());
    }
}

// Ambil data balita untuk dropdown
$balitas = [];
if (isset($koneksi)) {
    $sqlBalita = "SELECT id_balita, nama_balita FROM balita ORDER BY nama_balita LIMIT 100";
    $result = mysqli_query($koneksi, $sqlBalita);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $balitas[] = $row;
        }
    }
}

// Ambil jumlah data training
$totalTraining = 0;
$sqlTrainingCount = "SELECT COUNT(*) as total FROM training_stunting WHERE tipe_data = 'Training' AND status_stunting IS NOT NULL";
$resultTrainingCount = mysqli_query($koneksi, $sqlTrainingCount);
if ($resultTrainingCount) {
    $row = mysqli_fetch_assoc($resultTrainingCount);
    $totalTraining = $row['total'];
}

// Ambil data statistik
$jumlah_balita = 0;
$jumlah_prediksi = 0;
$balita_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM balita");
if ($balita_query) {
    $data_balita = mysqli_fetch_assoc($balita_query);
    $jumlah_balita = $data_balita['total'];
}

$prediksi_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM prediksi_stunting");
if ($prediksi_query) {
    $data_prediksi = mysqli_fetch_assoc($prediksi_query);
    $jumlah_prediksi = $data_prediksi['total'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sistem C5.0 Stunting</title>
    <link rel="shortcut icon" href="img/bayi.jpg" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fa;
            padding-top: 70px;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: white !important;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: bold;
            color: #4A90E2 !important;
        }

        .navbar-brand i {
            margin-right: 10px;
        }

        .nav-link {
            font-weight: 500;
            color: #555 !important;
        }

        .nav-link:hover {
            color: #4A90E2 !important;
        }

        .nav-link.active {
            color: #4A90E2 !important;
            font-weight: 600;
        }

        .btn-login {
            background: #4A90E2;
            color: white !important;
            border: none;
            padding: 8px 20px;
            font-weight: 600;
            border-radius: 5px;
        }

        .btn-login:hover {
            background: #3a80d2;
            color: white !important;
        }

        .container {
            max-width: 1200px;
        }

        .hero {
            background: linear-gradient(135deg, #4A90E2 0%, #2ECC71 100%);
            color: white;
            padding: 60px 0;
            border-radius: 10px;
            margin-bottom: 40px;
        }

        .hero h1 {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: white;
            border-bottom: 2px solid #4A90E2;
            font-weight: 600;
            color: #4A90E2;
        }

        .btn-primary {
            background: #4A90E2;
            border: none;
            padding: 10px 30px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #3a80d2;
        }

        .page-section {
            display: none;
        }

        .page-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .footer {
            margin-top: 40px;
            padding: 20px 0;
            border-top: 1px solid #ddd;
            color: #666;
            text-align: center;
        }

        .stat-box {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .stat-icon {
            font-size: 30px;
            color: #4A90E2;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .feature-card {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            height: 100%;
        }

        .feature-icon {
            font-size: 40px;
            color: #4A90E2;
            margin-bottom: 15px;
        }

        /* Select2 Custom */
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            height: calc(2.25rem + 2px);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
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

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding-top: 60px;
            }

            .hero {
                padding: 40px 20px;
            }

            .hero h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#beranda" data-target="beranda">
                <i class="fas fa-child"></i> Sistem C5.0 Stunting
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#beranda" data-target="beranda">
                            Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang" data-target="tentang">
                            Tentang Stunting
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#klasifikasi" data-target="klasifikasi">
                            Klasifikasi
                        </a>
                    </li>
                    <li class="nav-item ml-2">
                        <a href="login.php" class="btn btn-login">
                            Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Beranda -->
        <section id="beranda" class="page-section active">
            <div class="hero">
                <div class="text-center">
                    <h1>Sistem Klasifikasi Stunting Balita</h1>
                    <p class="lead">Deteksi dini stunting menggunakan algoritma C5.0</p>
                    <a href="#klasifikasi" class="btn btn-light" data-target="klasifikasi">
                        Coba Klasifikasi Sekarang
                    </a>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-icon">
                            <i class="fas fa-baby"></i>
                        </div>
                        <div class="stat-value"><?= $jumlah_balita ?></div>
                        <div class="stat-label">Balita Terdata</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value">71%</div>
                        <div class="stat-label">Akurasi Sistem</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <div class="stat-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="stat-value"><?= $jumlah_prediksi ?></div>
                        <div class="stat-label">Prediksi Berhasil</div>
                    </div>
                </div>
            </div>

            <!-- Fitur -->
            <h3 class="mb-4">Fitur Utama</h3>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h5>Algoritma C5.0</h5>
                        <p>Menggunakan algoritma decision tree untuk klasifikasi yang akurat</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5>Prediksi Cepat</h5>
                        <p>Hasil analisis instan dengan tingkat kepercayaan</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h5>Monitoring</h5>
                        <p>Pantau perkembangan balita secara berkala</p>
                    </div>
                </div>
            </div>

            <!-- Info Stunting -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Apa itu Stunting?</h5>
                </div>
                <div class="card-body">
                    <p>Stunting adalah kondisi gagal tumbuh pada anak balita akibat kekurangan gizi kronis.</p>
                    <div class="alert alert-warning">
                        <strong>Dampak Stunting:</strong>
                        <ul class="mb-0">
                            <li>Perkembangan otak terhambat</li>
                            <li>Kemampuan belajar menurun</li>
                            <li>Produktivitas rendah saat dewasa</li>
                        </ul>
                    </div>
                    <a href="#tentang" class="btn btn-outline-primary" data-target="tentang">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>
        </section>

        <!-- Tentang Stunting -->
        <section id="tentang" class="page-section">
            <h3 class="mb-4">Tentang Stunting</h3>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Apa itu Stunting?</h5>
                </div>
                <div class="card-body">
                    <p><strong>Stunting</strong> adalah kondisi gagal tumbuh pada balita akibat kekurangan gizi kronis dan infeksi berulang selama
                        1.000 hari pertama kehidupan. Secara fisik, anak stunting memiliki tinggi badan di bawah standar usianya, namun dampak yang lebih serius adalah
                        terhambatnya perkembangan otak serta rendahnya daya tahan tubuh yang bersifat permanen hingga dewasa. Pencegahan utamanya dilakukan dengan memastikan asupan
                        protein hewani yang cukup, menjaga kebersihan lingkungan, serta memberikan gizi optimal bagi ibu hamil dan bayi guna menjamin produktivitas anak di masa depan.</p>
                    <div class="alert alert-info">
                        <strong>1000 HPK:</strong> MASA KRITIS DARI JANIN HINGGA ANAK USIA 2 TAHUN
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Penyebab Stunting</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul>
                                <li>Kekurangan gizi</li>
                                <li>Infeksi berulang</li>
                                <li>Asupan gizi anak yang tidak mencukupi</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul>
                                <li>ASI tidak eksklusif</li>
                                <li>MPASI tidak bergizi</li>
                                <li>Gizi ibu hamil kurang</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Pencegahan Stunting</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 text-center">
                            <i class="fas fa-heartbeat fa-2x text-primary mb-2"></i>
                            <h6>Gizi Ibu Hamil</h6>
                        </div>
                        <div class="col-md-4 mb-3 text-center">
                            <i class="fas fa-baby fa-2x text-primary mb-2"></i>
                            <h6>ASI Eksklusif</h6>
                        </div>
                        <div class="col-md-4 mb-3 text-center">
                            <i class="fas fa-utensils fa-2x text-primary mb-2"></i>
                            <h6>MPASI Bergizi</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Deteksi Dini Penting</h5>
                </div>
                <div class="card-body">
                    <p>Deteksi dini stunting sangat penting untuk:</p>
                    <ul>
                        <li>Intervensi lebih cepat</li>
                        <li>Hasil yang lebih baik</li>
                        <li>Biaya pengobatan lebih rendah</li>
                    </ul>
                    <a href="#klasifikasi" class="btn btn-primary" data-target="klasifikasi">
                        Deteksi Sekarang
                    </a>
                </div>
            </div>
        </section>

        <!-- Klasifikasi -->
        <section id="klasifikasi" class="page-section">
            <h3 class="mb-4">Klasifikasi Stunting</h3>
            <?php echo $message; ?>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Form Klasifikasi Stunting</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="formPrediksi">
                        <div class="form-group">
                            <label>Pilih Balita (Opsional)</label>
                            <select name="nama_balita" id="select-balita" class="form-control select2">
                                <option value="">-- Pilih balita --</option>
                                <?php foreach ($balitas as $balita): ?>
                                    <option value="<?= $balita['id_balita'] ?>"
                                        <?= ($balita_id == $balita['id_balita']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($balita['nama_balita']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Ketik untuk mencari nama balita</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Usia (bulan) *</label>
                                    <input type="number" name="usia" class="form-control" min="0" max="60"
                                        value="<?= htmlspecialchars($usia) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis Kelamin *</label>
                                    <select name="jk" class="form-control" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="L" <?= ($jk == 'L') ? 'selected' : '' ?>>Laki-laki</option>
                                        <option value="P" <?= ($jk == 'P') ? 'selected' : '' ?>>Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Berat Badan (kg) *</label>
                                    <input type="number" name="berat" class="form-control" step="0.1" min="2" max="30"
                                        value="<?= htmlspecialchars($berat) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tinggi Badan (cm) *</label>
                                    <input type="number" name="tinggi" class="form-control" step="0.1" min="40"
                                        max="120" value="<?= htmlspecialchars($tinggi) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" name="prediksi" class="btn btn-primary btn-lg">
                                <i class="fas fa-search"></i> Klasifikasi
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="fillDemoData()">
                                <i class="fas fa-magic"></i> Data Demo
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($hasil_prediksi && !isset($hasil_prediksi['error'])):
                $status_color = '';
                $status_bg = '';

                if ($hasil_prediksi['status'] == 'Stunting') {
                    $status_color = 'danger';
                    $status_bg = 'bg-danger';
                } elseif ($hasil_prediksi['status'] == 'Berisiko Stunting') {
                    $status_color = 'warning';
                    $status_bg = 'bg-warning';
                } else {
                    $status_color = 'success';
                    $status_bg = 'bg-success';
                }
            ?>
                <div class="card mb-4">
                    <div class="card-header <?= $status_bg ?> text-white">
                        <h5 class="mb-0">HASIL KLASIFIKASI</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h2 class="text-<?= $status_color ?>">
                                <?= htmlspecialchars($hasil_prediksi['status']) ?>
                            </h2>
                            <p class="lead">Confidence:
                                <strong><?= round($hasil_prediksi['confidence'] * 100, 1) ?>%</strong>
                            </p>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Data Input:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Usia:</strong> <?= htmlspecialchars($usia) ?> bulan</p>
                                    <p class="mb-1"><strong>Jenis Kelamin:</strong>
                                        <?= $jk == 'L' ? 'Laki-laki' : 'Perempuan' ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Berat Badan:</strong> <?= htmlspecialchars($berat) ?> kg</p>
                                    <p class="mb-1"><strong>Tinggi Badan:</strong> <?= htmlspecialchars($tinggi) ?> cm</p>
                                </div>
                            </div>

                            <?php
                            // Fungsi untuk menghitung berat dan tinggi ideal berdasarkan WHO
                            function hitungBeratTinggiIdeal($usia_bulan, $jenis_kelamin)
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

                            // Hitung berat dan tinggi ideal jika status stunting
                            if ($hasil_prediksi['status'] == 'Stunting' || $hasil_prediksi['status'] == 'Berisiko Stunting') {
                                $ideal = hitungBeratTinggiIdeal($usia, $jk);
                                $berat_ideal = $ideal['berat_ideal'];
                                $tinggi_ideal = $ideal['tinggi_ideal'];

                                // Hitung selisih
                                $selisih_berat = round($berat_ideal - $berat, 1);
                                $selisih_tinggi = round($tinggi_ideal - $tinggi, 1);

                                // Tentukan status berat dan tinggi
                                $status_berat = ($selisih_berat > 0) ? 'Kurang' : (($selisih_berat < -2) ? 'Lebih' : 'Normal');
                                $status_tinggi = ($selisih_tinggi > 0) ? 'Pendek' : 'Normal';

                                $warna_berat = ($status_berat == 'Kurang') ? 'danger' : (($status_berat == 'Lebih') ? 'warning' : 'success');
                                $warna_tinggi = ($status_tinggi == 'Pendek') ? 'danger' : 'success';
                            ?>
                                <hr>
                                <h6><i class="fas fa-balance-scale"></i> Standar Ideal (WHO):</h6>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="card mb-2">
                                            <div class="card-body p-3">
                                                <h6 class="mb-2"><i class="fas fa-weight text-primary"></i> Berat Badan Ideal
                                                </h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5 class="mb-0"><?= $berat_ideal ?> kg</h5>
                                                        <small class="text-muted">Untuk usia <?= $usia ?> bulan</small>
                                                    </div>
                                                    <div>
                                                        <span class="badge badge-<?= $warna_berat ?>">
                                                            <?php if ($status_berat == 'Kurang'): ?>
                                                                <i class="fas fa-arrow-down"></i> Kurang <?= abs($selisih_berat) ?>
                                                                kg
                                                            <?php elseif ($status_berat == 'Lebih'): ?>
                                                                <i class="fas fa-arrow-up"></i> Lebih <?= abs($selisih_berat) ?> kg
                                                            <?php else: ?>
                                                                <i class="fas fa-check"></i> Normal
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card mb-2">
                                            <div class="card-body p-3">
                                                <h6 class="mb-2"><i class="fas fa-ruler-vertical text-primary"></i> Tinggi Badan
                                                    Ideal</h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5 class="mb-0"><?= $tinggi_ideal ?> cm</h5>
                                                        <small class="text-muted">Untuk usia <?= $usia ?> bulan</small>
                                                    </div>
                                                    <div>
                                                        <span class="badge badge-<?= $warna_tinggi ?>">
                                                            <?php if ($status_tinggi == 'Pendek'): ?>
                                                                <i class="fas fa-arrow-down"></i> Pendek <?= abs($selisih_tinggi) ?>
                                                                cm
                                                            <?php else: ?>
                                                                <i class="fas fa-check"></i> Normal
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if (isset($hasil_prediksi['rule_used'])): ?>
                                <hr>
                                <h6><i class="fas fa-code-branch"></i> Rule yang digunakan:</h6>
                                <p class="mb-0"><?= htmlspecialchars($hasil_prediksi['rule_used']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="alert alert-<?= $status_color ?>">
                            <h6><i class="fas fa-stethoscope"></i> Rekomendasi:</h6>
                            <p class="mb-0">
                                <?php
                                if ($hasil_prediksi['status'] == 'Stunting') {
                                    echo "Segera konsultasi ke dokter anak dan tingkatkan asupan gizi protein, vitamin, dan mineral. ";
                                    echo "Berat badan perlu ditingkatkan sekitar <strong>" . abs($selisih_berat) . " kg</strong> ";
                                    echo "dan tinggi badan perlu ditingkatkan sekitar <strong>" . abs($selisih_tinggi) . " cm</strong> ";
                                    echo "untuk mencapai standar WHO. Lakukan pemeriksaan rutin setiap bulan.";
                                } elseif ($hasil_prediksi['status'] == 'Berisiko Stunting') {
                                    echo "Perhatikan asupan gizi anak. Tingkatkan konsumsi makanan bergizi seimbang. ";
                                    echo "Targetkan penambahan berat badan <strong>" . abs($selisih_berat) . " kg</strong> ";
                                    echo "dan tinggi badan <strong>" . abs($selisih_tinggi) . " cm</strong> ";
                                    echo "untuk mencapai standar ideal. Pantau pertumbuhan secara rutin di posyandu setiap bulan.";
                                } else {
                                    echo "Pertahankan pola makan sehat dan bergizi seimbang. Terus berikan ASI jika masih dalam usia ASI. ";
                                    echo "Lakukan kontrol rutin ke posyandu untuk memantau perkembangan.";
                                }
                                ?>
                            </p>

                            <?php if (($hasil_prediksi['status'] == 'Stunting' || $hasil_prediksi['status'] == 'Berisiko Stunting') && isset($berat_ideal) && isset($tinggi_ideal)): ?>
                                <hr class="my-2">
                                <h6><i class="fas fa-lightbulb"></i> Tips Pencapaian Target:</h6>
                                <ul class="mb-0">
                                    <?php if ($selisih_berat > 0): ?>
                                        <li>Tingkatkan asupan protein (telur, ikan, daging, tempe) untuk mencapai target berat
                                            <?= $berat_ideal ?> kg</li>
                                    <?php elseif ($selisih_berat < 0): ?>
                                        <li>Pertahankan berat badan ideal <?= $berat_ideal ?> kg dengan gizi seimbang</li>
                                    <?php endif; ?>

                                    <?php if ($selisih_tinggi > 0): ?>
                                        <li>Tingkatkan asupan kalsium (susu, keju, yoghurt) dan vitamin D untuk mencapai target
                                            tinggi <?= $tinggi_ideal ?> cm</li>
                                        <li>Aktivitas fisik ringan dan cukup tidur membantu pertumbuhan tinggi badan</li>
                                    <?php endif; ?>

                                    <li>Konsumsi makanan bergizi 3x utama + 2x selingan per hari</li>
                                    <li>Periksa ke posyandu/puskesmas setiap bulan untuk pemantauan</li>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php elseif (isset($hasil_prediksi['error'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Error Prediksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h6>Error Details:</h6>
                            <p><?= htmlspecialchars($hasil_prediksi['error']) ?></p>
                        </div>
                        <p class="text-muted small">Silakan periksa input data atau hubungi administrator sistem.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Parameter yang Dianalisis</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-ruler-vertical"></i> Parameter Antropometri</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Tinggi Badan <span class="badge badge-primary">cm</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Berat Badan <span class="badge badge-primary">kg</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Usia <span class="badge badge-primary">bulan</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Jenis Kelamin <span class="badge badge-primary">L/P</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-info-circle"></i> Informasi Sistem</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Algoritma <span class="badge badge-info">C5.0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Data Training <span
                                        class="badge badge-<?= $totalTraining > 0 ? 'success' : 'warning' ?>">
                                        <?= $totalTraining ?> sampel
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Metode Validasi <span class="badge badge-info">Decision Tree</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Status Model <span class="badge badge-success">Aktif</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Implementasi Algoritma C5.0 Untuk Klasifikasi Penyakit Stunting Pada Balita</p>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2
            $('#select-balita').select2({
                placeholder: 'Ketik nama balita...',
                allowClear: true,
                width: '100%'
            });

            // Navigation between sections
            $('a[data-target]').on('click', function(e) {
                e.preventDefault();
                var target = $(this).data('target');

                // Update active nav link
                $('.nav-link').removeClass('active');
                $(this).addClass('active');

                // Show target section
                $('.page-section').removeClass('active');
                $('#' + target).addClass('active');

                // Update URL hash
                history.pushState(null, null, '#' + target);

                // Scroll to top on mobile
                if ($(window).width() < 768) {
                    $('html, body').animate({
                        scrollTop: 0
                    }, 300);
                }
            });

            // Check URL hash on page load
            var hash = window.location.hash.substring(1);
            if (hash) {
                $('.page-section').removeClass('active');
                $('#' + hash).addClass('active');
                $('.nav-link').removeClass('active');
                $('a[data-target="' + hash + '"]').addClass('active');
            }

            // Form validation
            $('#formPrediksi').on('submit', function(e) {
                var usia = $('input[name="usia"]').val();
                var berat = $('input[name="berat"]').val();
                var tinggi = $('input[name="tinggi"]').val();
                var jk = $('select[name="jk"]').val();

                var isValid = true;
                var errorMessage = '';

                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Validation
                if (!usia || usia < 0 || usia > 60) {
                    $('input[name="usia"]').addClass('is-invalid');
                    errorMessage = 'Usia harus antara 0-60 bulan';
                    isValid = false;
                }
                if (!berat || berat < 2 || berat > 30) {
                    $('input[name="berat"]').addClass('is-invalid');
                    errorMessage = 'Berat harus antara 2-30 kg';
                    isValid = false;
                }
                if (!tinggi || tinggi < 40 || tinggi > 120) {
                    $('input[name="tinggi"]').addClass('is-invalid');
                    errorMessage = 'Tinggi harus antara 40-120 cm';
                    isValid = false;
                }
                if (!jk) {
                    $('select[name="jk"]').addClass('is-invalid');
                    errorMessage = 'Pilih jenis kelamin';
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    if (!$('.form-error').length) {
                        $('#formPrediksi').prepend(
                            '<div class="alert alert-danger form-error">' + errorMessage + '</div>'
                        );
                    }
                    return false;
                }

                // Show loading
                $('button[name="prediksi"]').html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                $('button[name="prediksi"]').prop('disabled', true);
            });

            // Real-time validation
            $('input, select').on('input change', function() {
                $(this).removeClass('is-invalid');
                $('.form-error').remove();
            });

            // Auto focus on first input in form
            if ($('#klasifikasi').hasClass('active')) {
                $('input[name="usia"]').focus();
            }
        });

        // Demo data button
        function fillDemoData() {
            $('input[name="usia"]').val(24);
            $('select[name="jk"]').val('L');
            $('input[name="berat"]').val(12.5);
            $('input[name="tinggi"]').val(85);

            // Remove any error messages
            $('.is-invalid').removeClass('is-invalid');
            $('.form-error').remove();

            // Show message
            alert('Data demo telah diisi. Klik "Klasifikasi" untuk melihat hasil.');
        }
    </script>
</body>

</html>