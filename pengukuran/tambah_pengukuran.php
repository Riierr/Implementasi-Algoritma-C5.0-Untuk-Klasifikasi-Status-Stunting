<?php
// Logika untuk menambah data pengukuran
if (isset($_POST['pengukuran'])) {
    $nama_balita            = htmlspecialchars($_POST['nama_balita']);
    $bulan_ukur             = htmlspecialchars($_POST['bulan_ukur']);
    $usia_bulan             = htmlspecialchars($_POST['usia_bulan']);
    $berat_badan            = htmlspecialchars($_POST['berat_badan']);
    $tinggi_badan           = htmlspecialchars($_POST['tinggi_badan']);
    $berat_badan_tambah     = htmlspecialchars($_POST['berat_badan_tambah']);
    $tinggi_badan_tambah    = htmlspecialchars($_POST['tinggi_badan_tambah']);
    $status_stunting        = htmlspecialchars($_POST['status_stunting']);

    try {
        // Ambil id_balita berdasarkan nama_balita
        $query = "SELECT id_balita FROM balita WHERE nama_balita = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("s", $nama_balita);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id_balita = $row['id_balita'];
                        
            $sql = "INSERT INTO pengukuran (id_balita, bulan_ukur, usia_bulan, berat_badan, tinggi_badan, berat_badan_tambah, tinggi_badan_tambah, status_stunting) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("isssssss", $id_balita, $bulan_ukur, $usia_bulan, $berat_badan, $tinggi_badan, $berat_badan_tambah, $tinggi_badan_tambah, $status_stunting);
            
            if ($stmt->execute()) {
                echo "
                <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data pengukuran berhasil ditambahkan',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'index.php?stunting=pengukuran';
                        }
                    });
                </script>
                ";
            } else {
                throw new Exception("Gagal mengeksekusi query: " . $stmt->error);
            }
        } else {
            throw new Exception("Nama balita tidak ditemukan");
        }
    } catch (Exception $e) {
        echo "
        <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '" . addslashes($e->getMessage()) . "',
                confirmButtonText: 'OK'
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
                    <h1 class="m-0 text-dark"><strong>Tambah Pengukuran Balita</strong></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?stunting=home">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php?stunting=pengukuran">Pengukuran</a></li>
                        <li class="breadcrumb-item active">Tambah Pengukuran</li>
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
                            <h5 class="card-title">Form Tambah Data Pengukuran</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="nama_balita">Nama Balita</label>
                                                <select name="nama_balita" id="nama_balita"
                                                    class="form-control select2">
                                                    <option value="">Cari nama balita...</option>
                                                    <?php
                                                    $sql = "SELECT * FROM balita ORDER BY nama_balita ASC";
                                                    $result = $koneksi->query($sql);
                                                    while ($row = $result->fetch_assoc()) {
                                                ?>
                                                    <option value="<?= $row['nama_balita'] ?>">
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
                                                <label for="bulan_ukur">Bulan Pengukuran</label>
                                                <input type="date" name="bulan_ukur" id="bulan_ukur"
                                                    class="form-control" autocomplete="off">
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="usia_bulan">Usia Bulan</label>
                                                <input type="number" name="usia_bulan" id="usia_bulan"
                                                    class="form-control" placeholder="Masukan Usia Bulan..." required
                                                    autocomplete="off">
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="berat_badan">Berat Badan</label>
                                                <input type="number" step="0.01" name="berat_badan" id="berat_badan"
                                                    class="form-control" placeholder="Contoh: 10.2 Kg" min="0"
                                                    max="100">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="tinggi_badan">Tinggi Badan</label>
                                                <input type="number" step="0.01" name="tinggi_badan" id="tinggi_badan"
                                                    class="form-control" placeholder="Contoh: 90 cm" min="0" max="200">
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="berat_badan_tambah">Berat Badan Tambah</label>
                                                <select name="berat_badan_tambah" id="berat_badan_tambah"
                                                    class="form-control">
                                                    <option value="">Pilih Perubahan Berat Badan...</option>
                                                    <option value="Ya">Ya</option>
                                                    <option value="Tidak">Tidak</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="tinggi_badan_tambah">Tinggi Badan Tambah</label>
                                                <select name="tinggi_badan_tambah" id="tinggi_badan_tambah"
                                                    class="form-control">
                                                    <option value="">Pilih Perubahan Tinggi Badan...</option>
                                                    <option value="Ya">Ya</option>
                                                    <option value="Tidak">Tidak</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="status_stunting">Status Stunting</label>
                                                <select name="status_stunting" id="status_stunting"
                                                    class="form-control">
                                                    <option value="">Pilih Status Stunting...</option>
                                                    <option value="Stunting">Stunting</option>
                                                    <option value="Tidak Stunting">Tidak Stunting</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" name="pengukuran" class="btn btn-primary mr-1">
                                            <i class="fas fa-save"></i> Simpan
                                        </button>
                                        <a href="index.php?stunting=pengukuran" class="btn btn-default">
                                            <i class="fas fa-times"></i> Keluar
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
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

.select2-container .select2-selection--single .select2-selection__clear {
    margin-right: 25px;
}
</style>