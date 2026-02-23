<?php

$id_pengukuran = $_GET['id'];

$id_pengukuran = intval ($id_pengukuran); 

$sql = mysqli_query($koneksi, "SELECT pengukuran.id_pengukuran, pengukuran.id_balita,
pengukuran.bulan_ukur, pengukuran.usia_bulan, pengukuran.berat_badan, pengukuran.tinggi_badan,
pengukuran.berat_badan_tambah, pengukuran.tinggi_badan_tambah, pengukuran.status_stunting,
pengukuran.tanggal_input, balita.nama_balita FROM pengukuran INNER JOIN balita 
ON pengukuran.id_balita = balita.id_balita WHERE pengukuran.id_pengukuran = '$id_pengukuran'");

$data = mysqli_fetch_array($sql);

$delete_sql = "DELETE FROM pengukuran WHERE id_pengukuran = '$id_pengukuran'";

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
                window.location = "index.php?stunting=pengukuran";
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
            window.location = "index.php?stunting=pengukuran";
        }
    });
    </script>';
}

?>