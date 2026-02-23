<?php
// Validasi apakah parameter id ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<script>
    Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "ID tidak valid",
        confirmButtonText: "OK"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "index.php?mknn=settings";
        }
    });
    </script>';
    exit();
}

// Pastikan koneksi database sudah tersedia
if (!isset($koneksi)) {
    require_once "../koneksi.php";
}

// Sanitasi dan validasi input
$id = intval($_GET['id']); // Konversi ke integer untuk keamanan

if ($id <= 0) {
    echo '<script>
    Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "ID tidak valid", 
        confirmButtonText: "OK"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "index.php?stunting=settings";
        }
    });
    </script>';
    exit();
}

// Cek apakah user yang akan dihapus ada
$check_query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $check_query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt); 
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo '<script>
    Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "User tidak ditemukan",
        confirmButtonText: "OK"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "index.php?stunting=settings";
        }
    });
    </script>';
    exit();
}

// Optional: Cegah penghapusan admin utama jika diperlukan
$user_data = mysqli_fetch_assoc($result);
if ($user_data['username'] == 'admin' || $user_data['role'] == 'superadmin') {
    echo '<script>
    Swal.fire({
        icon: "warning",
        title: "Perhatian",
        text: "Admin utama tidak dapat dihapus",
        confirmButtonText: "OK"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "index.php?stunting=settings";
        }
    });
    </script>';
    exit();
}

// Hapus data menggunakan prepared statement
$delete_query = "DELETE FROM users WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $delete_query);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    // Hapus berhasil
    echo '<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: "Data berhasil dihapus",
        confirmButtonText: "OK"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "index.php?stunting=settings";
        }
    });
    </script>';
} else {
    // Hapus gagal
    echo '<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "Gagal menghapus data",
        confirmButtonText: "OK"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "index.php?stunting=settings";
        }
    });
    </script>';
}

// Tutup statement
mysqli_stmt_close($stmt);
?>