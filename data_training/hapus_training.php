<?php

$id_training = $_GET['id'];

$id_training = intval ($id_training); 

$sql = mysqli_query($koneksi, "SELECT t.*, b.nama_balita, p.bulan_ukur
FROM training_stunting t INNER JOIN balita b ON t.id_balita = b.id_balita 
INNER JOIN pengukuran p ON t.id_pengukuran = p.id_pengukuran
WHERE t.id_training = '$id_training'");

$data = mysqli_fetch_array($sql);

$delete_sql = "DELETE FROM training_stunting WHERE id_training = '$id_training'";

if (mysqli_query($koneksi, $delete_sql)) {
     echo '<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
     echo '<script>
        Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: "Data Berhasil Dihapus",
            confirmButtonText: "OK"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = "index.php?stunting=training";
            }
        });
        </script>';
} else {
    echo '<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '<script>
    Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "Data Gagal Dihapus",
        confirmButtonText: "OK"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "index.php?stunting=training";
        }
    });
    </script>';
}

?>