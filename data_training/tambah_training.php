<?php
if (isset($_POST['training'])) {
    $nama_balita      = htmlspecialchars($_POST['nama_balita']);
    $bulan_ukur       = htmlspecialchars($_POST['bulan_ukur']);
    $usia_bulan       = htmlspecialchars($_POST['usia_bulan']);
    $jenis_kelamin    = htmlspecialchars($_POST['jenis_kelamin']);
    $berat_badan      = htmlspecialchars($_POST['berat_badan']);
    $tinggi_badan     = htmlspecialchars($_POST['tinggi_badan']);
    $status_stunting  = htmlspecialchars($_POST['status_stunting']);
    $tipe_data        = htmlspecialchars($_POST['tipe_data']);

    try {
        // 1. Ambil id_balita berdasarkan nama_balita
        $query_balita = "SELECT id_balita FROM balita WHERE nama_balita = ?";
        $stmt_balita = $koneksi->prepare($query_balita);
        $stmt_balita->bind_param("s", $nama_balita); 
        $stmt_balita->execute(); 
        $result_balita = $stmt_balita->get_result();
        
        if ($result_balita->num_rows === 0) {
            throw new Exception("Nama balita tidak ditemukan");
        }
        $row_balita = $result_balita->fetch_assoc();
        $id_balita = $row_balita['id_balita'];
        $stmt_balita->close();

        // 2. Ambil id_pengukuran berdasarkan bulan_ukur dan id_balita
        $query_pengukuran = "SELECT id_pengukuran FROM pengukuran 
                            WHERE bulan_ukur = ? AND id_balita = ?";
        $stmt_pengukuran = $koneksi->prepare($query_pengukuran);
        $stmt_pengukuran->bind_param("si", $bulan_ukur, $id_balita);
        $stmt_pengukuran->execute();
        $result_pengukuran = $stmt_pengukuran->get_result();
        
        if ($result_pengukuran->num_rows === 0) {
            throw new Exception("Data pengukuran untuk bulan ini tidak ditemukan");
        }
        $row_pengukuran = $result_pengukuran->fetch_assoc();
        $id_pengukuran = $row_pengukuran['id_pengukuran'];
        $stmt_pengukuran->close();
 
        // 3. Validasi data duplikat (optional)
        $query_cek = "SELECT id_training FROM training_stunting 
                     WHERE id_balita = ? AND id_pengukuran = ?";
        $stmt_cek = $koneksi->prepare($query_cek);
        $stmt_cek->bind_param("ii", $id_balita, $id_pengukuran);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();
        
        if ($result_cek->num_rows > 0) {
            throw new Exception("Data training untuk balita ini pada bulan ini sudah ada");
        }
        $stmt_cek->close();

        // 4. Insert data training
        $sql_insert = "INSERT INTO training_stunting 
                      (id_balita, id_pengukuran, usia_bulan, jenis_kelamin, 
                       berat_badan, tinggi_badan, status_stunting, tipe_data) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_insert = $koneksi->prepare($sql_insert);
        $stmt_insert->bind_param("iissddss", 
            $id_balita, 
            $id_pengukuran, 
            $usia_bulan, 
            $jenis_kelamin,
            $berat_badan, 
            $tinggi_badan,  
            $status_stunting, 
            $tipe_data
        );
        
        if ($stmt_insert->execute()) {
            echo "
            <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data training berhasil ditambahkan',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = 'index.php?stunting=training';
                    }
                });
            </script>
            ";
        } else {
            throw new Exception("Gagal menyimpan data: " . $stmt_insert->error);
        }
        
        $stmt_insert->close();
        
    } catch (Exception $e) {
        echo "
        <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '" . addslashes($e->getMessage()) . "',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.history.back();
                }
            });
        </script>
        ";
    }
}
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><strong>Tambah Data Training</strong></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?stunting=home">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php?stunting=trainig">Training</a></li>
                        <li class="breadcrumb-item">Tambah Training</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Form Tambah Data Training</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label>Nama Balita</label>
                                            <select name="nama_balita" id="balita" class="form-control select2">
                                                <option value="">Cari nama balita...</option>
                                                <?php
                                            $sql = "SELECT * FROM balita ORDER BY nama_balita ASC";
                                            $result = $koneksi->query($sql);
                                            while ($row = $result->fetch_assoc()) {                                    
                                            ?>
                                                <option value="<?= htmlspecialchars($row['nama_balita']) ?>">
                                                    <?= htmlspecialchars($row['nama_balita']) ?>
                                                    <?php if(!empty($row['nama_ibu'])): ?>
                                                    - Ibu: <?= htmlspecialchars($row['nama_ibu']) ?>
                                                    <?php endif; ?>
                                                </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label>Bulan Pengukuran</label>
                                            <select name="bulan_ukur" id="bulan" class="form-control select2">
                                                <option value="">Cari bulan pengukuran...</option>
                                                <?php
                                            $ukur = "SELECT DISTINCT bulan_ukur FROM pengukuran ORDER BY bulan_ukur DESC";
                                            $results = $koneksi->query($ukur);
                                            while ($rows = $results->fetch_assoc()) {                                                                                          
                                            ?>
                                                <option value="<?= $rows['bulan_ukur'] ?>">
                                                    <?= date('d-m-y', strtotime($rows['bulan_ukur'])) ?>
                                                </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label for="usia_bulan">Usia Bulan</label>
                                            <input type="number" name="usia_bulan" id="usia_bulan" class="form-control"
                                                placeholder="Masukan Usia Bulan..." min="0" max="100" required
                                                autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label for="jenis_kelamin">Jenis Kelamin</label>
                                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control">
                                                <option value="">Pilih Jenis Kelamin...</option>
                                                <option value="L">Laki-Laki</option>
                                                <option value="P">Perempuan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label for="berat_badan">Berat Badan</label>
                                            <input type="number" step="0.01" name="berat_badan" id="berat_badan"
                                                class="form-control" placeholder="Contoh: 10.2 Kg" min="0" max="100">
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label for="tinggi_badan">Tinggi Badan</label>
                                            <input type="number" step="0.01" name="tinggi_badan" id="tinggi_badan"
                                                class="form-control" placeholder="Contoh: 90 cm" min="0" max="200">
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label for="status_stunting">Status Stunting</label>
                                            <select name="status_stunting" id="status_stunting" class="form-control">
                                                <option value="">Pilih Status Stunting...</option>
                                                <option value="Stunting">Stunting</option>
                                                <option value="Tidak Stunting">Tidak Stunting</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="form-group">
                                            <label for="tipe_data">Tipe Data</label>
                                            <select name="tipe_data" id="tipe_data" class="form-control">
                                                <option value="">Pilih Tipe Data...</option>
                                                <option value="Training">Training</option>
                                                <option value="Testing">Testing</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="submit" name="training" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Tambah Data
                                    </button>
                                    <a href="index.php?stunting=training" class="btn btn-default">
                                        <i class="fas fa-times"></i> Keluar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 5px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #007bff;
    color: white;
}
</style>