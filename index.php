<?php
date_default_timezone_set('Asia/Jakarta');
// session_start();
require_once "koneksi.php";

require_once "session_helper.php";

// Cek login
requireLogin();

// Sanitasi parameter GET
$mknn = isset($_GET['stunting']) ? strtolower(trim($_GET['stunting'])) : 'home';

// Jika user bukan admin, redirect halaman tertentu
if (!isAdmin() && in_array($mknn, ['user', 'data'])) {
    $mknn = 'home';
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>Halaman | <?= ucfirst($_GET['stunting']); ?></title>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="assets/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/plugins/sweetalert2/sweetalert2.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Tambahkan CSS Select2 di head -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Load Select2 CSS & JS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <link rel="shortcut icon" href="img/bayi.jpg" type="image/x-icon">
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#"><i
                            class="fas fa-th-large"></i></a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="#" class="brand-link">
                <i class="fas fa-praying-hands fa-lg brand-image img-circle elevation-3"></i>
                <span class="brand-text font-weight-light"><strong>BALITA STUNTING</strong></span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="assets/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?= $_SESSION['nama'] ?> | <?= $_SESSION['role'] ?></a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a href="index.php?stunting=home"
                                    class="nav-link <?= (isset($_GET['stunting']) && $_GET['stunting'] == 'home') ? 'active' : "" ?>">
                                    <i class="nav-icon fas fa-tachometer-alt"></i>
                                    <p>
                                        Dashboard
                                    </p>
                                </a>
                            </li>

                            <!-- <li class="nav-header">DATA MASTER</li> -->
                            <li class="nav-item">
                                <a href="index.php?stunting=balita"
                                    class="nav-link <?= (isset($_GET['stunting']) && $_GET['stunting'] == 'balita') ? 'active' : "" ?>">
                                    <i class="nav-icon fa-solid fa-baby"></i>
                                    <p>
                                        Data Balita
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="index.php?stunting=pengukuran"
                                    class="nav-link <?= (isset($_GET['stunting']) && $_GET['stunting'] == 'pengukuran') ? "active" : "" ?>">
                                    <i class="nav-icon fa-solid fa-expand-arrows-alt"></i>
                                    <p>
                                        Data Pengukuran
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="index.php?stunting=training"
                                    class="nav-link <?= (isset($_GET['stunting']) && $_GET['stunting'] == 'training') ? "active" : "" ?>">
                                    <i class="nav-icon fa-solid fa-database"></i>
                                    <p>
                                        Data Training / Testing
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($_SESSION['role'] === 'user'): ?>
                            <li class="nav-item">
                                <a href="index.php?stunting=home_pasien"
                                    class="nav-link <?= (isset($_GET['stunting']) && $_GET['stunting'] == 'home_pasien') ? 'active' : "" ?>">
                                    <i class="nav-icon fas fa-tachometer-alt"></i>
                                    <p>
                                        Dashboard
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a href="index.php?stunting=klasifikasi"
                                class="nav-link <?= (isset($_GET['stunting']) && $_GET['stunting'] == 'klasifikasi') ? "active" : "" ?>">
                                <i class="nav-icon fa-solid fa-calculator"></i>
                                <p>
                                    Klasifikasi Model
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?stunting=hasil"
                                class="nav-link <?= (isset($_GET['stunting']) && $_GET['stunting'] == 'hasil') ? "active" : "" ?>">
                                <i class="nav-icon fas fa-diagnoses"></i>
                                <p>
                                    Hasil Klasifikasi C5.0 
                                </p>
                            </a>
                        </li>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a href="index.php?stunting=settings"
                                    class="nav-link <?= (isset($_GET['stunting']) && $_GET['stunting'] == 'settings') ? 'active' : "" ?>">
                                    <i class="nav-icon fa-solid fa-user-gear"></i>
                                    <p>
                                        Settings
                                    </p>
                                </a>
                            </li>
                        <?php endif ?>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="nav-icon fa-solid fa-right-from-bracket"></i>
                                <p>
                                    Logout
                                </p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <?php
        if (isset($_GET['stunting'])) {
            switch ($_GET['stunting']) {

                case 'home':
                    include "home/home.php";
                    break;

                case 'home_pasien':
                    include "home/home_pasien.php";
                    break;

                // Halaman Balita  
                case 'balita':
                    include "balita/balita.php";
                    break;

                case 'hapus_balita':
                    include "balita/hapus_balita.php";
                    break;
                // End Balita 

                // Halaman Pengukuran
                case 'pengukuran';
                    include "pengukuran/pengukuran.php";
                    break;

                case 'edit_pengukuran';
                    include "pengukuran/edit_pengukuran.php";
                    break;

                case 'hapus_pengukuran';
                    include "pengukuran/hapus_pengukuran.php";
                    break;

                case 'tambah_pengukuran':
                    include "pengukuran/tambah_pengukuran.php";
                    break;
                // end halaman pengukuran

                // Halaman data training
                case 'training':
                    include "data_training/training.php";
                    break;

                case 'tambah_training':
                    include "data_training/tambah_training.php";
                    break;

                case 'edit_training':
                    include "data_training/edit_training.php";
                    break;

                case 'hapus_training':
                    include "data_training/hapus_training.php";
                    break;
                // end halaman training

                // Halaman klasifikasi prediksi
                case 'klasifikasi':
                    include "klasifikasi/klasifikasi.php";
                    break;

                case 'model_c50':
                    include "klasifikasi/model_c50.php";
                    break;

                case 'hasil':
                    include "klasifikasi/hasil_klasifikasi.php";
                    break;

                case 'train_model':
                    include "klasifikasi/train_model.php";
                    break;
                // End klasifikasi  

                // Users 
                case 'settings':
                    include "users/settings.php";
                    break;

                case 'hapus_user':
                    include "users/hapus_user.php";
                    break;

                default:
                    echo "Halaman Tidak Ditemukan";
                    break;
            }
        }

        ?>


        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <footer class="main-footer text-center">
            <strong>&copy; <?= date('Y') ?></strong>
            Implementasi Algoritma C5.0 Untuk Klasifikasi Penyakit Stunting Pada Balita
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <!-- jQuery -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <!-- AdminLTE App -->
    <script src="assets/dist/js/adminlte.js"></script>

    <!-- OPTIONAL SCRIPTS -->
    <script src="assets/dist/js/demo.js"></script>

    <!-- PAGE PLUGINS -->
    <!-- jQuery Mapael -->
    <script src="assets/plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
    <script src="assets/plugins/raphael/raphael.min.js"></script>
    <script src="assets/plugins/jquery-mapael/jquery.mapael.min.js"></script>
    <script src="assets/plugins/jquery-mapael/maps/usa_states.min.js"></script>
    <!-- ChartJS -->
    <script src="assets/plugins/chart.js/Chart.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="assets/plugins/datatables/jquery.dataTables.js"></script>
    <script src="assets/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
    <script src="assets/plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <!-- PAGE SCRIPTS -->
    <script src="assets/dist/js/pages/dashboard2.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Tambahkan JS Select2 di footer -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk dropdown nama balita
            $('#balita').select2({
                placeholder: 'Ketik nama balita...',
                allowClear: true,
                minimumInputLength: 2,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Data tidak ditemukan";
                    },
                    searching: function() {
                        return "Mencari...";
                    },
                    inputTooShort: function(args) {
                        return "Masukkan " + (args.minimum - args.input.length) + " karakter lagi";
                    }
                }
            });

            // Inisialisasi Select2 untuk dropdown bulan pengukuran
            $('#bulan').select2({
                placeholder: 'Ketik bulan/tahun...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Data tidak ditemukan";
                    }
                }
            });

            // Auto-fill data berdasarkan nama balita yang dipilih
            $('#balita').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var namaBalita = $(this).val();

                if (namaBalita) {
                    // AJAX untuk mengambil data balita
                    $.ajax({
                        url: 'get_balita_data.php',
                        method: 'GET',
                        data: {
                            nama_balita: namaBalita
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Isi data ke form jika ada
                                if (response.jenis_kelamin) {
                                    $('#jenis_kelamin').val(response.jenis_kelamin);
                                }
                            }
                        }
                    });
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk dropdown nama balita
            $('.select2').select2({
                placeholder: 'Ketik nama balita...',
                allowClear: true,
                minimumInputLength: 2,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Data tidak ditemukan";
                    },
                    searching: function() {
                        return "Mencari...";
                    },
                    inputTooShort: function(args) {
                        var remainingChars = args.minimum - args.input.length;
                        return "Masukkan " + remainingChars + " karakter lagi";
                    }
                }
            });

            // Set tanggal default untuk bulan pengukuran (hari ini)
            document.getElementById('bulan_ukur').valueAsDate = new Date();
        });
    </script>

    <!-- Script untuk Select2 dan auto-fill -->
    <script>
        $(document).ready(function() {

            $('#select-balita').select2({
                placeholder: 'Ketik nama balita...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Balita tidak ditemukan";
                    },
                    searching: function() {
                        return "Mencari...";
                    }
                }
            });

            // Fungsi untuk auto-fill form ketika balita dipilih
            $('#select-balita').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var balitaId = selectedOption.val();

                if (balitaId) {
                    // Ambil data dari atribut data-*
                    var nama = selectedOption.data('nama');
                    var usia = selectedOption.data('usia');
                    var berat = selectedOption.data('berat');
                    var tinggi = selectedOption.data('tinggi');
                    var jk = selectedOption.data('jk');

                    // Isi form dengan data balita (jika data tersedia)
                    if (usia) {
                        $('input[name="usia"]').val(usia).trigger('change');
                    }

                    if (jk) {
                        // Konversi ke format form (L/P)
                        var jkForm = (jk === 'Laki-laki' || jk === 'L') ? 'L' : 'P';
                        $('select[name="jk"]').val(jkForm).trigger('change');
                    }

                    if (berat) {
                        $('input[name="berat"]').val(berat).trigger('change');
                    }

                    if (tinggi) {
                        $('input[name="tinggi"]').val(tinggi).trigger('change');
                    }

                    // Tampilkan notifikasi
                    showNotification('Data balita "' + nama + '" telah dimuat');
                } else {
                    // Kosongkan form jika tidak ada balita yang dipilih
                    clearFormData();
                }
            });

            // Fungsi untuk menampilkan notifikasi
            function showNotification(message) {
                // Hapus notifikasi sebelumnya
                $('.balita-notification').remove();

                // Buat notifikasi baru
                var notification = $(
                    '<div class="alert alert-info balita-notification alert-dismissible fade show" role="alert">' +
                    '<i class="fas fa-check-circle"></i> ' + message +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>');

                // Sisipkan setelah select
                $('#select-balita').closest('.form-group').after(notification);

                // Auto hide setelah 3 detik
                setTimeout(function() {
                    notification.alert('close');
                }, 3000);
            }
        });
    </script>

    <script>
        $(function() {
            $("#example1").DataTable();
            $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
            });
        });
    </script>

    <!-- Sweetalert Hapus -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data balita akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'index.php?stunting=hapus_balita&id=' + id;
                }
            });
        }
    </script>

    <script>
        $(document).on('click',
            '#hapus-pengukuran',
            function(e) {
                e.preventDefault();
                var link = $(this).attr('href');

                Swal.fire({
                    title: "Apakah Anda Yakin?",
                    text: "Data Akan Dihapus!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Hapus!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = link;
                    }
                });
            })
    </script>

    <script>
        $(document).on('click',
            '#hapus-training',
            function(e) {
                e.preventDefault();
                var link = $(this).attr('href');

                Swal.fire({
                    title: "Apakah Anda Yakin?",
                    text: "Data Akan Dihapus!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Ya, Hapus!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location = link;
                    }
                });
            })
    </script>
</body>

</html>