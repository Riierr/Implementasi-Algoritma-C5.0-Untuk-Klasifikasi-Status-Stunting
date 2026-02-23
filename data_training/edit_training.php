<?php
require_once 'koneksi.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Ambil data training dengan join ke tabel balita
$query = "SELECT t.*, b.nama_balita, p.bulan_ukur
          FROM training_stunting t
          JOIN balita b ON t.id_balita = b.id_balita
          JOIN pengukuran p ON t.id_pengukuran = p.id_pengukuran
          WHERE t.id_training = $id";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php?stunting=training");
    exit();
}

if (isset($_POST['update'])) {
    $usia_bulan = $_POST['usia_bulan'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $berat_badan = $_POST['berat_badan'];
    $tinggi_badan = $_POST['tinggi_badan'];
    $status_stunting = $_POST['status_stunting'];
    $tipe_data = $_POST['tipe_data'];
    
    $update = "UPDATE training_stunting SET
                usia_bulan = '$usia_bulan',
                jenis_kelamin = '$jenis_kelamin',
                berat_badan = '$berat_badan',
                tinggi_badan = '$tinggi_badan',
                status_stunting = '$status_stunting',
                tipe_data = '$tipe_data'
                WHERE id_training = $id";
    
    if (mysqli_query($koneksi, $update)) {
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
                                window.location = 'index.php?stunting=training';
                            }
                        });
                    </script>
                    ";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Data Training</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Nama Balita</label>
                                    <input type="text" class="form-control" value="<?= $data['nama_balita'] ?>"
                                        readonly>
                                </div>

                                <div class="mb-3">
                                    <label>Bulan Pengukuran</label>
                                    <input type="date" class="form-control" value="<?= $data['bulan_ukur'] ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label>Usia Bulan</label>
                                    <input type="number" name="usia_bulan" class="form-control"
                                        value="<?= $data['usia_bulan'] ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label>Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-control" required>
                                        <option value="L" <?= $data['jenis_kelamin']=='L'?'selected':'' ?>>Laki-Laki
                                        </option>
                                        <option value="P" <?= $data['jenis_kelamin']=='P'?'selected':'' ?>>Perempuan
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Berat Badan (kg)</label>
                                    <input type="number" step="0.01" name="berat_badan" class="form-control"
                                        value="<?= $data['berat_badan'] ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label>Tinggi Badan (cm)</label>
                                    <input type="number" step="0.01" name="tinggi_badan" class="form-control"
                                        value="<?= $data['tinggi_badan'] ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label>Status Stunting</label>
                                    <select name="status_stunting" class="form-control" required>
                                        <option value="Stunting"
                                            <?= $data['status_stunting']=='Stunting'?'selected':'' ?>>Stunting</option>
                                        <option value="Tidak Stunting"
                                            <?= $data['status_stunting']=='Tidak Stunting'?'selected':'' ?>>Tidak
                                            Stunting</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label>Tipe Data</label>
                                    <select name="tipe_data" class="form-control" required>
                                        <option value="Training" <?= $data['tipe_data']=='Training'?'selected':'' ?>>
                                            Training</option>
                                        <option value="Testing" <?= $data['tipe_data']=='Testing'?'selected':'' ?>>
                                            Testing</option>
                                    </select> 
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="update" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="index.php?stunting=training" class="btn btn-default">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>