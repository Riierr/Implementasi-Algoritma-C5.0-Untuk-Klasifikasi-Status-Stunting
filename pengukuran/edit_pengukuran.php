<?php
// Ambil ID pengukuran dari URL
$id_pengukuran = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data pengukuran yang akan diedit
$data_pengukuran = null;
if ($id_pengukuran > 0) {
    $query = "SELECT p.*, b.nama_balita 
              FROM pengukuran p 
              INNER JOIN balita b ON p.id_balita = b.id_balita 
              WHERE p.id_pengukuran = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $id_pengukuran);
    $stmt->execute();
    $result = $stmt->get_result();
     
    if ($result->num_rows > 0) {
        $data_pengukuran = $result->fetch_assoc();
    } else {
        echo "
        <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Data tidak ditemukan',
                text: 'Data pengukuran tidak ditemukan',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'index.php?stunting=pengukuran';
                }
            });
        </script>
        ";
        exit();
    }
} else {
    header("Location: index.php?stunting=pengukuran");
    exit();
}

// Logika untuk update data pengukuran
if (isset($_POST['update'])) {
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
            
            // Update data pengukuran
            $sql = "UPDATE pengukuran 
                    SET id_balita = ?, 
                        bulan_ukur = ?, 
                        usia_bulan = ?, 
                        berat_badan = ?, 
                        tinggi_badan = ?, 
                        berat_badan_tambah = ?, 
                        tinggi_badan_tambah = ?, 
                        status_stunting = ?
                    WHERE id_pengukuran = ?";
            
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("isssssssi", $id_balita, $bulan_ukur, $usia_bulan, $berat_badan, 
                              $tinggi_badan, $berat_badan_tambah, $tinggi_badan_tambah, 
                              $status_stunting, $id_pengukuran);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo "
                    <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data pengukuran berhasil diperbarui',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location = 'index.php?stunting=pengukuran';
                            }
                        });
                    </script>
                    ";
                } else {
                    echo "
                    <script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                    <script>
                        Swal.fire({
                            icon: 'info',
                            title: 'Tidak ada perubahan',
                            text: 'Data tidak ada perubahan',
                            confirmButtonText: 'OK'
                        });
                    </script>
                    ";
                }
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
                    <h1 class="m-0 text-dark"><strong>Edit Pengukuran Balita</strong></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?stunting=home">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php?stunting=pengukuran">Pengukuran</a></li>
                        <li class="breadcrumb-item active">Edit Pengukuran</li>
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
                            <h5 class="card-title">Form Edit Data Pengukuran</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="nama_balita">Nama Balita</label>
                                                <select name="nama_balita" id="nama_balita" class="form-control"
                                                    required>
                                                    <option value="">Pilih Nama Balita...</option>
                                                    <?php
                                                $sql = "SELECT * FROM balita ORDER BY nama_balita ASC";
                                                $result = $koneksi->query($sql);
                                                while ($row = $result->fetch_assoc()) {
                                                    $selected = ($data_pengukuran && $row['nama_balita'] == $data_pengukuran['nama_balita']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $row['nama_balita'] ?>" <?= $selected ?>>
                                                        <?= $row['nama_balita'] ?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="bulan_ukur">Bulan Pengukuran</label>
                                                <input type="date" name="bulan_ukur" id="bulan_ukur"
                                                    class="form-control"
                                                    value="<?= $data_pengukuran ? $data_pengukuran['bulan_ukur'] : '' ?>"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="usia_bulan">Usia Bulan</label>
                                                <input type="number" name="usia_bulan" id="usia_bulan"
                                                    class="form-control" placeholder="Masukan Usia Bulan..."
                                                    value="<?= $data_pengukuran ? $data_pengukuran['usia_bulan'] : '' ?>"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="berat_badan">Berat Badan (Kg)</label>
                                                <input type="number" step="0.01" name="berat_badan" id="berat_badan"
                                                    class="form-control" placeholder="Contoh: 10.2" min="0" max="100"
                                                    value="<?= $data_pengukuran ? $data_pengukuran['berat_badan'] : '' ?>"
                                                    required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="tinggi_badan">Tinggi Badan (cm)</label>
                                                <input type="number" step="0.01" name="tinggi_badan" id="tinggi_badan"
                                                    class="form-control" placeholder="Contoh: 90" min="0" max="200"
                                                    value="<?= $data_pengukuran ? $data_pengukuran['tinggi_badan'] : '' ?>"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="berat_badan_tambah">Perubahan Berat Badan</label>
                                                <select name="berat_badan_tambah" id="berat_badan_tambah"
                                                    class="form-control" required>
                                                    <option value="">Pilih Perubahan Berat Badan...</option>
                                                    <option value="Ya"
                                                        <?= ($data_pengukuran && $data_pengukuran['berat_badan_tambah'] == 'Ya') ? 'selected' : '' ?>>
                                                        Ya</option>
                                                    <option value="Tidak"
                                                        <?= ($data_pengukuran && $data_pengukuran['berat_badan_tambah'] == 'Tidak') ? 'selected' : '' ?>>
                                                        Tidak</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="tinggi_badan_tambah">Perubahan Tinggi Badan</label>
                                                <select name="tinggi_badan_tambah" id="tinggi_badan_tambah"
                                                    class="form-control" required>
                                                    <option value="">Pilih Perubahan Tinggi Badan...</option>
                                                    <option value="Ya"
                                                        <?= ($data_pengukuran && $data_pengukuran['tinggi_badan_tambah'] == 'Ya') ? 'selected' : '' ?>>
                                                        Ya</option>
                                                    <option value="Tidak"
                                                        <?= ($data_pengukuran && $data_pengukuran['tinggi_badan_tambah'] == 'Tidak') ? 'selected' : '' ?>>
                                                        Tidak</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="form-group">
                                                <label for="status_stunting">Status Stunting</label>
                                                <select name="status_stunting" id="status_stunting" class="form-control"
                                                    required>
                                                    <option value="">Pilih Status Stunting...</option>
                                                    <option value="Stunting"
                                                        <?= ($data_pengukuran && $data_pengukuran['status_stunting'] == 'Stunting') ? 'selected' : '' ?>>
                                                        Stunting</option>
                                                    <option value="Tidak Stunting"
                                                        <?= ($data_pengukuran && $data_pengukuran['status_stunting'] == 'Tidak Stunting') ? 'selected' : '' ?>>
                                                        Tidak Stunting</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="update" class="btn btn-primary mr-1">
                                    <i class="fas fa-save"></i> Update Data
                                </button>
                                <a href="index.php?stunting=pengukuran" class="btn btn-default">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>