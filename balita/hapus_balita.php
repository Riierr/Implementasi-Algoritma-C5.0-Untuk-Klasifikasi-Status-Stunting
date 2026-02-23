<?php

$id_balita = $_GET['id'];

$balita = mysqli_query($koneksi, "SELECT * FROM balita WHERE id_balita = $id_balita");
$hapus = mysqli_fetch_array($balita);

mysqli_query($koneksi, "DELETE FROM balita WHERE id_balita = '$id_balita'");


?>

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: 'Data Berhasil Dihapus',
    confirmButtonText: 'OK'
}).then((result) => {
    if (result.isConfirmed) {
        window.location = 'index.php?stunting=balita';
    }
});
</script>