<?php
// Inisialisasi variabel
$hasil_prediksi = [];
$error = '';
$total = $stunting = $tidak_stunting = $benar = $salah = $belum = 0;

// Handle validasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validasi'])) {
    $id_prediksi = $_POST['id_prediksi'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if (!isset($koneksi)) {
        $_SESSION['message'] = "❌ Error: Koneksi database tidak ditemukan";
    } else {
        $id_prediksi = mysqli_real_escape_string($koneksi, $id_prediksi);
        $status = mysqli_real_escape_string($koneksi, $status);
        
        $sql = "UPDATE prediksi_stunting SET benar_salah = '$status' WHERE id_prediksi = $id_prediksi";
        
        if (mysqli_query($koneksi, $sql)) {
            $_SESSION['message'] = "✅ Validasi berhasil disimpan!";
        } else {
            $_SESSION['message'] = "❌ Error: " . mysqli_error($koneksi);
        }
    }
    
    header('Location: index.php?stunting=hasil');
    exit();
}

// Ambil data prediksi
if (isset($koneksi)) {
    $sql = "SELECT 
                ps.id_prediksi,
                ps.usia_bulan,
                ps.berat_badan,
                ps.tinggi_badan,
                ps.prediksi,
                ps.confidence,
                ps.benar_salah,
                ps.tanggal_prediksi,
                b.nama_balita,
                m.nama_model
            FROM prediksi_stunting ps
            LEFT JOIN balita b ON ps.id_balita = b.id_balita
            LEFT JOIN model_c50 m ON ps.id_model = m.id_model
            ORDER BY ps.tanggal_prediksi DESC";
    
    $result = mysqli_query($koneksi, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $hasil_prediksi[] = $row;
        }
        
        // Hitung statistik
        $total = count($hasil_prediksi);
        foreach ($hasil_prediksi as $p) {
            if ($p['prediksi'] == 'Stunting') $stunting++;
            if ($p['prediksi'] == 'Tidak Stunting') $tidak_stunting++;
            if ($p['benar_salah'] == 'Benar') $benar++;
            if ($p['benar_salah'] == 'Salah') $salah++;
            if ($p['benar_salah'] == 'Belum Dicek') $belum++;
        }
        
        // Hitung akurasi
        $akurasi = ($benar + $salah) > 0 ? round(($benar / ($benar + $salah)) * 100, 2) : 0;
        
    } else {
        $error = "Gagal mengambil data: " . mysqli_error($koneksi);
    }
} else {
    $error = "Koneksi database tidak tersedia";
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><strong>Hasil Klasifikasi</strong></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php?stunting=klasifikasi">Klasifikasi</a></li>
                        <li class="breadcrumb-item active">Hasil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Statistik -->
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-database"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Prediksi</span>
                            <span class="info-box-number"><?php echo $total; ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box bg-danger">
                        <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Stunting</span>
                            <span class="info-box-number"><?php echo $stunting; ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tidak Stunting</span>
                            <span class="info-box-number"><?php echo $tidak_stunting; ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Akurasi</span>
                            <span class="info-box-number"><?php echo $akurasi; ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="card">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#semua">
                                <i class="fas fa-list"></i> Semua Data
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#prediksi">
                                <i class="fas fa-chart-pie"></i> Berdasarkan Prediksi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#parameter">
                                <i class="fas fa-chart-bar"></i> Berdasarkan Parameter
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content">

                        <!-- Tab 1: Semua Data -->
                        <div class="tab-pane active" id="semua">
                            <?php if (empty($hasil_prediksi)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                <h4>Belum ada data prediksi</h4>
                                <p>Silakan buat prediksi terlebih dahulu</p>
                                <a href="index.php?stunting=klasifikasi" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Buat Prediksi
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table id="example1" class="table table-bordered table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Nama Balita</th>
                                            <th>Usia</th>
                                            <th>Berat</th>
                                            <th>Tinggi</th>
                                            <th>Prediksi</th>
                                            <th>Confidence</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                            <th>Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($hasil_prediksi as $index => $p): ?>
                                        <tr>
                                            <td><?= $index + 1; ?></td>
                                            <td><?= !empty($p['nama_balita']) ? htmlspecialchars($p['nama_balita']) : '-'; ?>
                                            </td>
                                            <td><?= $p['usia_bulan']; ?> bln</td>
                                            <td><?= $p['berat_badan']; ?> kg</td>
                                            <td><?= $p['tinggi_badan']; ?> cm</td>
                                            <td>
                                                <?php if ($p['prediksi'] == 'Stunting'): ?>
                                                <span class="badge bg-danger">Stunting</span>
                                                <?php else: ?>
                                                <span class="badge bg-success">Tidak Stunting</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php $confidence = round($p['confidence'] * 100, 1); ?>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar 
                                                        <?= $confidence >= 80 ? 'bg-success' : ($confidence >= 60 ? 'bg-warning' : 'bg-danger'); ?>"
                                                        style="width: <?= $confidence; ?>%">
                                                        <?= $confidence; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $statusClass = $p['benar_salah'] == 'Benar' ? 'success' : 
                                                              ($p['benar_salah'] == 'Salah' ? 'danger' : 'warning');
                                                ?>
                                                <span class="badge bg-<?= $statusClass; ?>">
                                                    <?= $p['benar_salah']; ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($p['tanggal_prediksi'])); ?></td>
                                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                            <td>
                                                <form method="POST" class="d-flex">
                                                    <input type="hidden" name="id_prediksi"
                                                        value="<?= $p['id_prediksi']; ?>">
                                                    <select name="status" class="form-control form-control-sm mr-1"
                                                        style="width: 120px;">
                                                        <option value="">-- Validasi --</option>
                                                        <option value="Benar"
                                                            <?= $p['benar_salah'] == 'Benar' ? 'selected' : ''; ?>>Benar
                                                        </option>
                                                        <option value="Salah"
                                                            <?= $p['benar_salah'] == 'Salah' ? 'selected' : ''; ?>>Salah
                                                        </option>
                                                        <option value="Belum Dicek"
                                                            <?= $p['benar_salah'] == 'Belum Dicek' ? 'selected' : ''; ?>>
                                                            Reset</option>
                                                    </select>
                                                    <button type="submit" name="validasi" class="btn btn-sm btn-primary"
                                                        title="Simpan">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab 2: Berdasarkan Prediksi -->
                        <div class="tab-pane" id="prediksi">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-danger">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-exclamation-triangle"></i> Stunting
                                            </h3>
                                            <span class="badge bg-danger float-right"><?= $stunting; ?> kasus</span>
                                        </div>
                                        <div class="card-body">
                                            <h5>Statistik Stunting:</h5>
                                            <?php
                                            // Hitung rata-rata untuk stunting
                                            $avg_usia_stunting = 0;
                                            $avg_berat_stunting = 0;
                                            $avg_tinggi_stunting = 0;
                                            $avg_conf_stunting = 0;
                                            
                                            $stunting_data = array_filter($hasil_prediksi, function($item) {
                                                return $item['prediksi'] == 'Stunting';
                                            });
                                            
                                            if (count($stunting_data) > 0) {
                                                $sum_usia = $sum_berat = $sum_tinggi = $sum_conf = 0;
                                                foreach ($stunting_data as $item) {
                                                    $sum_usia += $item['usia_bulan'];
                                                    $sum_berat += $item['berat_badan'];
                                                    $sum_tinggi += $item['tinggi_badan'];
                                                    $sum_conf += $item['confidence'];
                                                }
                                                $avg_usia_stunting = round($sum_usia / count($stunting_data), 1);
                                                $avg_berat_stunting = round($sum_berat / count($stunting_data), 2);
                                                $avg_tinggi_stunting = round($sum_tinggi / count($stunting_data), 2);
                                                $avg_conf_stunting = round(($sum_conf / count($stunting_data)) * 100, 1);
                                            }
                                            ?>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="small-box bg-danger">
                                                        <div class="inner">
                                                            <h3><?= $avg_usia_stunting; ?></h3>
                                                            <p>Usia Rata-rata (bln)</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fas fa-calendar"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="small-box bg-warning">
                                                        <div class="inner">
                                                            <h3><?= $avg_conf_stunting; ?>%</h3>
                                                            <p>Confidence Rata-rata</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fas fa-chart-line"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Berat Rata-rata:</strong></td>
                                                    <td><?= $avg_berat_stunting; ?> kg</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tinggi Rata-rata:</strong></td>
                                                    <td><?= $avg_tinggi_stunting; ?> cm</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card card-success">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-check-circle"></i> Tidak Stunting
                                            </h3>
                                            <span class="badge bg-success float-right"><?= $tidak_stunting; ?>
                                                kasus</span>
                                        </div>
                                        <div class="card-body">
                                            <h5>Statistik Tidak Stunting:</h5>
                                            <?php
                                            // Hitung rata-rata untuk tidak stunting
                                            $avg_usia_sehat = 0;
                                            $avg_berat_sehat = 0;
                                            $avg_tinggi_sehat = 0;
                                            $avg_conf_sehat = 0;
                                            
                                            $sehat_data = array_filter($hasil_prediksi, function($item) {
                                                return $item['prediksi'] == 'Tidak Stunting';
                                            });
                                            
                                            if (count($sehat_data) > 0) {
                                                $sum_usia = $sum_berat = $sum_tinggi = $sum_conf = 0;
                                                foreach ($sehat_data as $item) {
                                                    $sum_usia += $item['usia_bulan'];
                                                    $sum_berat += $item['berat_badan'];
                                                    $sum_tinggi += $item['tinggi_badan'];
                                                    $sum_conf += $item['confidence'];
                                                }
                                                $avg_usia_sehat = round($sum_usia / count($sehat_data), 1);
                                                $avg_berat_sehat = round($sum_berat / count($sehat_data), 2);
                                                $avg_tinggi_sehat = round($sum_tinggi / count($sehat_data), 2);
                                                $avg_conf_sehat = round(($sum_conf / count($sehat_data)) * 100, 1);
                                            }
                                            ?>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="small-box bg-success">
                                                        <div class="inner">
                                                            <h3><?= $avg_usia_sehat; ?></h3>
                                                            <p>Usia Rata-rata (bln)</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fas fa-calendar"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="small-box bg-info">
                                                        <div class="inner">
                                                            <h3><?= $avg_conf_sehat; ?>%</h3>
                                                            <p>Confidence Rata-rata</p>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fas fa-chart-line"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Berat Rata-rata:</strong></td>
                                                    <td><?= $avg_berat_sehat; ?> kg</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tinggi Rata-rata:</strong></td>
                                                    <td><?= $avg_tinggi_sehat; ?> cm</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Grafik Perbandingan -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-bar"></i> Perbandingan Stunting vs Tidak Stunting
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <canvas id="comparisonChart" height="150"></canvas>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="list-group">
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Stunting</strong>
                                                        <span class="badge bg-danger"><?= $stunting; ?></span>
                                                    </div>
                                                    <small class="text-muted"><?= round(($stunting/$total)*100, 1); ?>%
                                                        dari total</small>
                                                </div>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Tidak Stunting</strong>
                                                        <span class="badge bg-success"><?= $tidak_stunting; ?></span>
                                                    </div>
                                                    <small
                                                        class="text-muted"><?= round(($tidak_stunting/$total)*100, 1); ?>%
                                                        dari total</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Berdasarkan Parameter -->
                        <div class="tab-pane" id="parameter">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card card-info">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-baby"></i> Usia Risiko
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <h5>Balita usia 0-24 bulan</h5>
                                            <?php
                                            $usia_risiko = array_filter($hasil_prediksi, function($item) {
                                                return $item['usia_bulan'] <= 24;
                                            });
                                            $stunting_usia_risiko = array_filter($usia_risiko, function($item) {
                                                return $item['prediksi'] == 'Stunting';
                                            });
                                            ?>
                                            <div class="text-center">
                                                <h1 class="display-4"><?= count($usia_risiko); ?></h1>
                                                <p>Total balita usia risiko</p>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-danger"
                                                        style="width: <?= count($usia_risiko)>0 ? round((count($stunting_usia_risiko)/count($usia_risiko))*100, 1) : 0; ?>%">
                                                        <?= count($stunting_usia_risiko); ?> Stunting
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?= count($stunting_usia_risiko); ?> dari
                                                    <?= count($usia_risiko); ?> berisiko stunting</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card card-warning">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-weight"></i> Berat Rendah
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <h5>Berat badan < 10kg</h5>
                                                    <?php
                                            $berat_rendah = array_filter($hasil_prediksi, function($item) {
                                                return $item['berat_badan'] < 10;
                                            });
                                            $stunting_berat_rendah = array_filter($berat_rendah, function($item) {
                                                return $item['prediksi'] == 'Stunting';
                                            });
                                            ?>
                                                    <div class="text-center">
                                                        <h1 class="display-4"><?= count($berat_rendah); ?></h1>
                                                        <p>Total balita berat rendah</p>
                                                        <div class="progress" style="height: 25px;">
                                                            <div class="progress-bar bg-danger"
                                                                style="width: <?= count($berat_rendah)>0 ? round((count($stunting_berat_rendah)/count($berat_rendah))*100, 1) : 0; ?>%">
                                                                <?= count($stunting_berat_rendah); ?> Stunting
                                                            </div>
                                                        </div>
                                                        <small class="text-muted"><?= count($stunting_berat_rendah); ?>
                                                            dari <?= count($berat_rendah); ?> berat rendah</small>
                                                    </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card card-warning">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-ruler-vertical"></i> Tinggi Rendah
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <h5>Tinggi badan < 85cm</h5>
                                                    <?php
                                            $tinggi_rendah = array_filter($hasil_prediksi, function($item) {
                                                return $item['tinggi_badan'] < 85;
                                            });
                                            $stunting_tinggi_rendah = array_filter($tinggi_rendah, function($item) {
                                                return $item['prediksi'] == 'Stunting';
                                            });
                                            ?>
                                                    <div class="text-center">
                                                        <h1 class="display-4"><?= count($tinggi_rendah); ?></h1>
                                                        <p>Total balita tinggi rendah</p>
                                                        <div class="progress" style="height: 25px;">
                                                            <div class="progress-bar bg-danger"
                                                                style="width: <?= count($tinggi_rendah)>0 ? round((count($stunting_tinggi_rendah)/count($tinggi_rendah))*100, 1) : 0; ?>%">
                                                                <?= count($stunting_tinggi_rendah); ?> Stunting
                                                            </div>
                                                        </div>
                                                        <small class="text-muted"><?= count($stunting_tinggi_rendah); ?>
                                                            dari <?= count($tinggi_rendah); ?> tinggi rendah</small>
                                                    </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabel Ringkasan Parameter -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-table"></i> Ringkasan Parameter
                                    </h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Parameter</th>
                                                <th>Total Data</th>
                                                <th>Stunting</th>
                                                <th>% Stunting</th>
                                                <th>Confidence Rata-rata</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Data untuk semua
                                            $avg_conf_all = 0;
                                            if ($total > 0) {
                                                $sum_conf = 0;
                                                foreach ($hasil_prediksi as $item) {
                                                    $sum_conf += $item['confidence'];
                                                }
                                                $avg_conf_all = round(($sum_conf / $total) * 100, 1);
                                            }
                                            ?>
                                            <tr>
                                                <td><strong>Semua Data</strong></td>
                                                <td><?= $total; ?></td>
                                                <td><?= $stunting; ?></td>
                                                <td><?= $total > 0 ? round(($stunting/$total)*100, 1) : 0; ?>%</td>
                                                <td><?= $avg_conf_all; ?>%</td>
                                            </tr>

                                            <?php
                                            // Data untuk usia risiko
                                            $avg_conf_usia = 0;
                                            if (count($usia_risiko) > 0) {
                                                $sum_conf = 0;
                                                foreach ($usia_risiko as $item) {
                                                    $sum_conf += $item['confidence'];
                                                }
                                                $avg_conf_usia = round(($sum_conf / count($usia_risiko)) * 100, 1);
                                            }
                                            ?>
                                            <tr>
                                                <td>Usia Risiko (0-24 bln)</td>
                                                <td><?= count($usia_risiko); ?></td>
                                                <td><?= count($stunting_usia_risiko); ?></td>
                                                <td><?= count($usia_risiko) > 0 ? round((count($stunting_usia_risiko)/count($usia_risiko))*100, 1) : 0; ?>%
                                                </td>
                                                <td><?= $avg_conf_usia; ?>%</td>
                                            </tr>

                                            <?php
                                            // Data untuk berat rendah
                                            $avg_conf_berat = 0;
                                            if (count($berat_rendah) > 0) {
                                                $sum_conf = 0;
                                                foreach ($berat_rendah as $item) {
                                                    $sum_conf += $item['confidence'];
                                                }
                                                $avg_conf_berat = round(($sum_conf / count($berat_rendah)) * 100, 1);
                                            }
                                            ?>
                                            <tr>
                                                <td>Berat Rendah (<10kg)< /td>
                                                <td><?= count($berat_rendah); ?></td>
                                                <td><?= count($stunting_berat_rendah); ?></td>
                                                <td><?= count($berat_rendah) > 0 ? round((count($stunting_berat_rendah)/count($berat_rendah))*100, 1) : 0; ?>%
                                                </td>
                                                <td><?= $avg_conf_berat; ?>%</td>
                                            </tr>

                                            <?php
                                            // Data untuk tinggi rendah
                                            $avg_conf_tinggi = 0;
                                            if (count($tinggi_rendah) > 0) {
                                                $sum_conf = 0;
                                                foreach ($tinggi_rendah as $item) {
                                                    $sum_conf += $item['confidence'];
                                                }
                                                $avg_conf_tinggi = round(($sum_conf / count($tinggi_rendah)) * 100, 1);
                                            }
                                            ?>
                                            <tr>
                                                <td>Tinggi Rendah (<85cm)< /td>
                                                <td><?= count($tinggi_rendah); ?></td>
                                                <td><?= count($stunting_tinggi_rendah); ?></td>
                                                <td><?= count($tinggi_rendah) > 0 ? round((count($stunting_tinggi_rendah)/count($tinggi_rendah))*100, 1) : 0; ?>%
                                                </td>
                                                <td><?= $avg_conf_tinggi; ?>%</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Total: <?= $total; ?> data | Stunting: <?= $stunting; ?> | Tidak Stunting:
                        <?= $tidak_stunting; ?> | Akurasi: <?= $akurasi; ?>%
                    </small>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validasi form
    const forms = document.querySelectorAll('form[method="POST"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const select = this.querySelector('select[name="status"]');
            if (!select.value) {
                e.preventDefault();
                alert('Silakan pilih status validasi terlebih dahulu!');
                select.focus();
                return false;
            }
            return true;
        });
    });

    // Inisialisasi chart
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Stunting', 'Tidak Stunting'],
            datasets: [{
                label: 'Jumlah Kasus',
                data: [<?= $stunting; ?>, <?= $tidak_stunting; ?>],
                backgroundColor: ['#dc3545', '#28a745'],
                borderColor: ['#c82333', '#218838'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Auto-refresh jika ada data belum divalidasi
    <?php if ($belum > 0): ?>
    setTimeout(() => location.reload(), 60000);
    <?php endif; ?>
});
</script>

<style>
.info-box {
    margin-bottom: 1rem;
}

.small-box {
    margin-bottom: 1rem;
}

.nav-tabs .nav-link {
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    font-weight: 600;
    border-bottom-color: #fff;
}

.progress {
    margin-bottom: 0.5rem;
}
</style>