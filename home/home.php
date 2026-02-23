<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><strong>Dashboard</strong></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?stunting=home">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="nav-icon fa-solid fa-baby"></i></span>

                        <div class="info-box-content">
                            <?php
                            $balita = mysqli_query($koneksi, "SELECT * FROM balita");
                            $jumlah = mysqli_num_rows($balita);
                            
                            ?>
                            <span class="info-box-text">Data Balita</span>
                            <span class="info-box-number">
                                <?= $jumlah ?>
                                <small>Data</small>
                            </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1"><i
                                class="nav-icon fa-solid fa-expand-arrows-alt"></i></span>

                        <div class="info-box-content">
                            <?php
                            $pengukuran = mysqli_query($koneksi, "SELECT * FROM pengukuran");
                            $ukur = mysqli_num_rows($pengukuran);
                            
                            ?>
                            <span class="info-box-text">Data Pengukuran</span>
                            <span class="info-box-number"><?= $ukur ?> <small>Data</small></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->

                <!-- fix for small devices only -->
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i
                                class="nav-icon fa-solid fa-database"></i></span>

                        <div class="info-box-content">
                            <?php
                        $training = mysqli_query($koneksi, "SELECT * FROM training_stunting");
                        $data_training = mysqli_num_rows($training);
                        
                        ?>
                            <span class="info-box-text">Data Training</span>
                            <span class="info-box-number"><?= $data_training ?> <small>Data</small></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning elevation-1">
                            <i class="nav-icon fas fa-vial"></i></span>

                        <div class="info-box-content">
                            <?php
                        $totalTesting = 0;
                        $sqlTestingCount = "SELECT COUNT(*) as total FROM training_stunting WHERE tipe_data = 'Testing' AND status_stunting IS NOT NULL";
                        $resultCount = mysqli_query($koneksi, $sqlTestingCount);
                        if ($resultCount) {
                            $row = mysqli_fetch_assoc($resultCount);
                            $totalTesting = $row['total'];
                        }
                        
                        ?>
                            <span class="info-box-text">Data Testing</span>
                            <span class="info-box-number"><?= $totalTesting ?><small> Data</small></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
    </section>
    <!-- /.content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Data Hasil Prediksi Stunting</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Query untuk mengambil data prediksi_stunting dengan JOIN ke tabel balita
                            $hasil = query("SELECT 
                                ps.id_prediksi,
                                ps.id_balita,
                                b.nama_balita,
                                ps.usia_bulan,
                                ps.berat_badan,
                                ps.tinggi_badan,
                                ps.prediksi,
                                ps.confidence,
                                ps.benar_salah,
                                ps.tanggal_prediksi
                            FROM prediksi_stunting ps
                            INNER JOIN balita b ON ps.id_balita = b.id_balita
                            ORDER BY ps.tanggal_prediksi DESC");
                            
                            // Data untuk grafik
                            $stunting_count = query("SELECT COUNT(*) as total FROM prediksi_stunting WHERE prediksi = 'Stunting'")[0]['total'];
                            $tidak_stunting_count = query("SELECT COUNT(*) as total FROM prediksi_stunting WHERE prediksi = 'Tidak Stunting'")[0]['total'];
                            
                            $benar_count = query("SELECT COUNT(*) as total FROM prediksi_stunting WHERE benar_salah = 'Benar'")[0]['total'];
                            $salah_count = query("SELECT COUNT(*) as total FROM prediksi_stunting WHERE benar_salah = 'Salah'")[0]['total'];
                            $belum_count = query("SELECT COUNT(*) as total FROM prediksi_stunting WHERE benar_salah = 'Belum Dicek'")[0]['total'];
                            
                            // Data untuk tren bulanan (contoh)
                            $monthly_data = query("SELECT 
                                DATE_FORMAT(tanggal_prediksi, '%Y-%m') as bulan,
                                SUM(CASE WHEN prediksi = 'Stunting' THEN 1 ELSE 0 END) as stunting,
                                SUM(CASE WHEN prediksi = 'Tidak Stunting' THEN 1 ELSE 0 END) as tidak_stunting
                            FROM prediksi_stunting
                            GROUP BY DATE_FORMAT(tanggal_prediksi, '%Y-%m')
                            ORDER BY bulan DESC
                            LIMIT 6");
                            
                            // Balik urutan untuk chart dari terlama ke terbaru
                            $monthly_data = array_reverse($monthly_data);
                            ?>

                            <div class="table-responsive">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Balita</th>
                                            <th>Usia (Bulan)</th>
                                            <th>Berat (kg)</th>
                                            <th>Tinggi (cm)</th>
                                            <th>Prediksi</th>
                                            <th>Confidence</th>
                                            <th>Validasi</th>
                                            <th>Tanggal Prediksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        foreach ($hasil as $p): 
                                            // Tentukan warna label berdasarkan prediksi
                                            $badge_class = ($p['prediksi'] == 'Stunting') ? 'danger' : 'success';
                                            $validasi_class = '';
                                            
                                            // Tentukan warna untuk kolom validasi
                                            if ($p['benar_salah'] == 'Benar') {
                                                $validasi_class = 'success';
                                            } elseif ($p['benar_salah'] == 'Salah') {
                                                $validasi_class = 'danger';
                                            } else {
                                                $validasi_class = 'warning';
                                            }
                                        ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($p['nama_balita']) ?></td>
                                            <td class="text-center"><?= $p['usia_bulan'] ?></td>
                                            <td class="text-center"><?= $p['berat_badan'] ?></td>
                                            <td class="text-center"><?= $p['tinggi_badan'] ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $badge_class ?>">
                                                    <?= $p['prediksi'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">
                                                    <?= number_format($p['confidence'] * 100, 0) ?>%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $validasi_class ?>">
                                                    <?= $p['benar_salah'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('d-m-Y H:i', strtotime($p['tanggal_prediksi'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <br>
                            <!-- Grafik dan Statistik -->
                            <div class="row">
                                <!-- Grafik Perbandingan Stunting -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Perbandingan Stunting vs Tidak Stunting</h3>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="stuntingChart" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Grafik Validasi Prediksi -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Validasi Prediksi</h3>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="validationChart" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- /.content-wrapper -->

<!-- Modal untuk detail prediksi -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Prediksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detailContent">
                    <!-- Detail akan dimuat via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Fungsi untuk menampilkan detail prediksi
function showDetail(id_prediksi) {
    fetch(`ajax/get_prediksi_detail.php?id=${id_prediksi}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('detailContent').innerHTML = data;
            $('#detailModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detailContent').innerHTML =
                '<div class="alert alert-danger">Gagal memuat data detail.</div>';
            $('#detailModal').modal('show');
        });
}

// Data untuk grafik dari PHP
const stuntingData = {
    stunting: <?= $stunting_count ?>,
    tidakStunting: <?= $tidak_stunting_count ?>
};

const validationData = {
    benar: <?= $benar_count ?>,
    salah: <?= $salah_count ?>,
    belum: <?= $belum_count ?>
};

const monthlyLabels = <?= json_encode(array_column($monthly_data, 'bulan')) ?>;
const monthlyStunting = <?= json_encode(array_column($monthly_data, 'stunting')) ?>;
const monthlyTidakStunting = <?= json_encode(array_column($monthly_data, 'tidak_stunting')) ?>;

// Format bulan untuk label chart
const formattedMonthlyLabels = monthlyLabels.map(label => {
    const [year, month] = label.split('-');
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return `${monthNames[parseInt(month)-1]} ${year}`;
});

// Grafik Perbandingan Stunting
const stuntingChart = new Chart(document.getElementById('stuntingChart'), {
    type: 'pie',
    data: {
        labels: ['Stunting', 'Tidak Stunting'],
        datasets: [{
            data: [stuntingData.stunting, stuntingData.tidakStunting],
            backgroundColor: [
                '#dc3545', 
                '#28a745' 
            ],
            borderColor: [
                '#c82333',
                '#218838'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 12
                    },
                    padding: 20
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        const total = stuntingData.stunting + stuntingData.tidakStunting;
                        const percentage = Math.round((context.raw / total) * 100);
                        label += context.raw + ' data (' + percentage + '%)';
                        return label;
                    }
                }
            }
        }
    }
});

// Grafik Validasi Prediksi
const validationChart = new Chart(document.getElementById('validationChart'), {
    type: 'doughnut',
    data: {
        labels: ['Benar', 'Salah', 'Belum Dicek'],
        datasets: [{
            data: [validationData.benar, validationData.salah, validationData.belum],
            backgroundColor: [
                '#28a745', // Hijau untuk benar
                '#dc3545', // Merah untuk salah
                '#ffc107' // Kuning untuk belum dicek
            ],
            borderColor: [
                '#218838',
                '#c82333',
                '#e0a800'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 12
                    },
                    padding: 20
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        const total = validationData.benar + validationData.salah + validationData.belum;
                        const percentage = Math.round((context.raw / total) * 100);
                        label += context.raw + ' data (' + percentage + '%)';
                        return label;
                    }
                }
            }
        }
    }
});

// Grafik Tren Bulanan
const monthlyTrendChart = new Chart(document.getElementById('monthlyTrendChart'), {
    type: 'line',
    data: {
        labels: formattedMonthlyLabels,
        datasets: [{
                label: 'Stunting',
                data: monthlyStunting,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Tidak Stunting',
                data: monthlyTidakStunting,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Jumlah Prediksi'
                },
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Bulan'
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});

// Inisialisasi DataTable
$(document).ready(function() {
    $('#example1').DataTable({
        "responsive": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [
            [0, "asc"]
        ],
        "language": {
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Tidak ada data ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada data tersedia",
            "infoFiltered": "(disaring dari _MAX_ total data)",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });
});
</script>

<style>
/* Styling tambahan untuk chart */
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid #dee2e6;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.info-box {
    cursor: pointer;
    transition: transform 0.2s;
}

.info-box:hover {
    transform: translateY(-5px);
}

.info-box-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.chart-container {
    position: relative;
    height: 250px;
}
</style>