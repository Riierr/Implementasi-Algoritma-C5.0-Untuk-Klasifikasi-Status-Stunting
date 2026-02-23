<?php
$koneksi = mysqli_connect("localhost", "root", "", "stunting_c5.0");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Validasi Query
function query($query) { 
    global $koneksi;

    $result = mysqli_query($koneksi, $query);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;

}
return $rows;
} 

function tambah ($data) {
    global $koneksi;
    $nama_balita        = htmlspecialchars($data['nama_balita']);
    $jenis_kelamin      = htmlspecialchars($data['jenis_kelamin']);
    $tanggal_lahir      = htmlspecialchars($data['tanggal_lahir']);
    $alamat             = htmlspecialchars($data['alamat']);

    $query = "INSERT INTO balita (nama_balita, jenis_kelamin, tanggal_lahir, alamat) VALUES ('$nama_balita', '$jenis_kelamin', '$tanggal_lahir', '$alamat')";
    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);

}
?>