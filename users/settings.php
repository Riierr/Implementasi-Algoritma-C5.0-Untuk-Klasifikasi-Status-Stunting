<?php
require_once "./session_helper.php";
require_once "koneksi.php";

// Cek apakah user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Hanya admin yang dapat mengakses halaman ini!',
            confirmButtonColor: '#3085d6',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php?stunting=home';
            }
        });
    </script>";
    exit(); 
}

// Inisialisasi variabel
$message = '';
$message_type = '';
$success = '';
$error = '';

// Handle tambah user
if (isset($_POST['add_user'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);
    $nama = htmlspecialchars(trim($_POST['nama']));
    $email = htmlspecialchars(trim($_POST['email']));
    $role = $_POST['role'];
    
    // Validasi input
    if (empty($username) || empty($password) || empty($nama)) {
        $error = "Username, password, dan nama harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Validasi username unik
        $check_query = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $koneksi->prepare($check_query);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password dengan SHA256
            $hashed_password = hash('sha256', $password);
            
            $query = "INSERT INTO users (nama, username, password, email, role, tanggal_buat) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("sssss", $nama, $username, $hashed_password, $email, $role);
            
            if ($stmt->execute()) {
                $success = "User berhasil ditambahkan!";
                
                echo '<script>document.getElementById("addUserForm").reset();</script>';
            } else {
                $error = "Gagal menambahkan user: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}

// Handle edit user
if (isset($_POST['edit_user'])) {
    $id = intval($_POST['id']);
    $nama = htmlspecialchars(trim($_POST['nama']));
    $email = htmlspecialchars(trim($_POST['email']));
    $role = $_POST['role'];
    $password = trim($_POST['password']);
    
    // Validasi input
    if (empty($nama)) {
        $error = "Nama harus diisi!";
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Jika password diisi, update password juga
        if (!empty($password)) {
            $hashed_password = hash('sha256', $password);
            $query = "UPDATE users SET nama = ?, email = ?, role = ?, password = ? WHERE id = ?";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("ssssi", $nama, $email, $role, $hashed_password, $id);
        } else {
            $query = "UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("sssi", $nama, $email, $role, $id);
        }
        
        if ($stmt->execute()) {
            $success = "User berhasil diupdate!";
        } else {
            $error = "Gagal mengupdate user: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Ambil semua users
$query = "SELECT * FROM users ORDER BY 
          CASE role 
            WHEN 'admin' THEN 1 
            ELSE 2 
          END, 
          nama ASC";
$users_result = $koneksi->query($query);

// Hitung jumlah user per role
$stats_query = "SELECT role, COUNT(*) as jumlah FROM users GROUP BY role";
$stats_result = $koneksi->query($stats_query);
$stats = [];
while ($row = $stats_result->fetch_assoc()) {
    $stats[$row['role']] = $row['jumlah'];
}
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-users-cog"></i> <strong>User Management</strong></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?stunting=home">Home</a></li>
                        <li class="breadcrumb-item active">User Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Notifikasi -->
            <?php if ($success): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                        <?= $success ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        <?= $error ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-12">
                    <!-- Form Tambah User -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-plus"></i> Tambah User Baru</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="addUserForm" onsubmit="return validateAddUserForm()">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Username <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="username" id="addUsername"
                                                placeholder="Username" required maxlength="50"
                                                oninput="checkUsernameAvailability(this.value)">
                                            <small id="usernameHelp" class="form-text"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="password"
                                                    id="newPassword" placeholder="Password" required minlength="6">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="button"
                                                        onclick="togglePassword('newPassword', this)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-muted">Minimal 6 karakter</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nama" id="addNama"
                                                placeholder="Nama Lengkap" required maxlength="100">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Role <span class="text-danger">*</span></label>
                                            <select class="form-control select2" name="role" id="addRole" required
                                                style="width: 100%;">
                                                <option value="user">User</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" name="add_user" class="btn btn-success btn-block">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" class="form-control" name="email" id="addEmail"
                                                placeholder="Email (opsional)" maxlength="100">
                                            <small id="emailHelp" class="form-text"></small>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Daftar Users -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Users
                                (<?= $users_result->num_rows ?> user)</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <div class="btn-group ml-2">
                                    <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item filter-role" href="#" data-role="all">Semua</a>
                                        <a class="dropdown-item filter-role" href="#" data-role="admin">Admin</a>
                                        <a class="dropdown-item filter-role" href="#" data-role="user">User</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped projects" id="usersTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 1%">#</th>
                                            <th style="width: 25%">User</th>
                                            <th>Info</th>
                                            <th>Role</th>
                                            <th style="width: 20%">Aktivitas</th>
                                            <th style="width: 15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        // Reset pointer result
                                        $users_result->data_seek(0);
                                        while ($user = $users_result->fetch_assoc()): 
                                            $is_current_user = ($user['id'] == $_SESSION['user_id']);
                                            $is_main_admin = ($user['id'] == 1);
                                            $last_login = $user['terakhir_login'] ? 
                                                date('d M Y H:i', strtotime($user['terakhir_login'])) : 
                                                'Belum pernah login';
                                        ?>
                                        <tr data-role="<?= $user['role'] ?>">
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="dist/img/user2-160x160.jpg" class="img-circle img-sm mr-2"
                                                        alt="User Image">
                                                    <div>
                                                        <strong><?= htmlspecialchars($user['nama']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-user mr-1"></i>
                                                            <?= htmlspecialchars($user['username']) ?>
                                                            <?php if ($is_current_user): ?>
                                                            <span class="badge badge-info ml-1">Saya</span>
                                                            <?php endif; ?>
                                                            <?php if ($is_main_admin): ?>
                                                            <span class="badge badge-danger ml-1">Utama</span>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($user['email']): ?>
                                                <div>
                                                    <i class="fas fa-envelope mr-1 text-primary"></i>
                                                    <small><?= htmlspecialchars($user['email']) ?></small>
                                                </div>
                                                <?php endif; ?>
                                                <div>
                                                    <i class="fas fa-calendar mr-1 text-secondary"></i>
                                                    <small class="text-muted">
                                                        Bergabung:
                                                        <?= date('d M Y', strtotime($user['tanggal_buat'])) ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-<?= $user['role'] == 'admin' ? 'danger' : 'success' ?>">
                                                    <i
                                                        class="fas fa-<?= $user['role'] == 'admin' ? 'user-shield' : 'user' ?>"></i>
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="fas fa-sign-in-alt mr-1"></i>
                                                    <?= $last_login ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-info btn-sm btn-edit mr-2"
                                                        onclick="editUser(<?= $user['id'] ?>, 
                                                                     '<?= htmlspecialchars(addslashes($user['username'])) ?>', 
                                                                     '<?= htmlspecialchars(addslashes($user['nama'])) ?>', 
                                                                     '<?= htmlspecialchars(addslashes($user['email'] ?? '')) ?>', 
                                                                     '<?= $user['role'] ?>')" title="Edit User">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <?php if (!$is_main_admin && !$is_current_user): ?>
                                                    <a href="index.php?stunting=hapus_user&id=<?= $user['id'] ?>"
                                                        class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <?php else: ?>
                                                    <button class="btn btn-danger btn-sm" disabled
                                                        title="<?= $is_main_admin ? 'Tidak bisa hapus admin utama' : 'Tidak bisa hapus akun sendiri' ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <div class="float-right">
                                <small class="text-muted">
                                    Menampilkan <?= $users_result->num_rows ?> user
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </section>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="editUserForm" onsubmit="return validateEditUserForm()">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title"><i class="fas fa-edit"></i> Edit User</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editUserId">
                    <input type="hidden" name="edit_user" value="1">

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" id="editUsername" disabled>
                        <small class="text-muted">Username tidak bisa diubah</small>
                    </div>

                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" id="editNama" required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" id="editEmail" maxlength="100">
                        <small id="editEmailHelp" class="form-text"></small>
                    </div>

                    <div class="form-group">
                        <label>Role <span class="text-danger">*</span></label>
                        <select class="form-control" name="role" id="editRole" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Password Baru (Opsional)</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="editPassword"
                                placeholder="Kosongkan jika tidak ingin mengubah" minlength="6">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePassword('editPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Minimal 6 karakter (diisi hanya jika ingin mengubah password)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteConfirmModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus user <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Hapus
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Function untuk toggle password visibility
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Function untuk edit user
function editUser(id, username, nama, email, role) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUsername').value = username;
    document.getElementById('editNama').value = nama;
    document.getElementById('editEmail').value = email;
    document.getElementById('editRole').value = role;

    // Clear validation messages
    document.getElementById('editEmailHelp').innerHTML = '';

    // Show modal
    $('#editUserModal').modal('show');
}

// Function untuk konfirmasi hapus user
function confirmDelete(id, username) {
    document.getElementById('deleteUserName').textContent = username;
    document.getElementById('confirmDeleteBtn').href = 'index.php?stunting=user&delete=' + id;
    $('#deleteConfirmModal').modal('show');
}

// Function untuk check username availability (AJAX)
function checkUsernameAvailability(username) {
    if (username.length < 3) {
        document.getElementById('usernameHelp').innerHTML = '';
        return;
    }

    // Tampilkan loading
    document.getElementById('usernameHelp').innerHTML =
        '<span class="text-info"><i class="fas fa-spinner fa-spin"></i> Checking...</span>';

    // Simulasi AJAX request (ganti dengan AJAX real jika diperlukan)
    setTimeout(() => {
        // Ini hanya simulasi, di implementasi real gunakan AJAX
        // Untuk sekarang, asumsikan username tersedia
        document.getElementById('usernameHelp').innerHTML =
            '<span class="text-success"><i class="fas fa-check"></i> Username tersedia</span>';
    }, 500);
}

// Form validation
function validateAddUserForm() {
    const username = document.getElementById('addUsername').value.trim();
    const password = document.getElementById('newPassword').value;
    const nama = document.getElementById('addNama').value.trim();
    const email = document.getElementById('addEmail').value.trim();

    if (!username || !password || !nama) {
        Swal.fire('Error', 'Username, password, dan nama harus diisi!', 'error');
        return false;
    }

    if (password.length < 6) {
        Swal.fire('Error', 'Password minimal 6 karakter!', 'error');
        return false;
    }

    if (email && !validateEmail(email)) {
        Swal.fire('Error', 'Format email tidak valid!', 'error');
        return false;
    }

    return true;
}

function validateEditUserForm() {
    const nama = document.getElementById('editNama').value.trim();
    const email = document.getElementById('editEmail').value.trim();
    const password = document.getElementById('editPassword').value;

    if (!nama) {
        Swal.fire('Error', 'Nama harus diisi!', 'error');
        return false;
    }

    if (email && !validateEmail(email)) {
        document.getElementById('editEmailHelp').innerHTML =
            '<span class="text-danger">Format email tidak valid!</span>';
        return false;
    }

    if (password && password.length < 6) {
        Swal.fire('Error', 'Password minimal 6 karakter!', 'error');
        return false;
    }

    return true;
}

// Email validation helper
function validateEmail(email) {
    const re =
        /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

// Filter users by role
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Auto focus pada form tambah user
    $('#addUsername').focus();

    // Filter users by role
    $('.filter-role').click(function(e) {
        e.preventDefault();
        const role = $(this).data('role');

        if (role === 'all') {
            $('#usersTable tbody tr').show();
        } else {
            $('#usersTable tbody tr').hide();
            $('#usersTable tbody tr[data-role="' + role + '"]').show();
        }

        // Update active filter
        $('.filter-role').removeClass('active');
        $(this).addClass('active');
    });

    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Show loading when submitting forms
    $('form').submit(function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

        // Reset button after 5 seconds (fallback)
        setTimeout(() => {
            submitBtn.html(originalText).prop('disabled', false);
        }, 5000);
    });
});
</script>

<style>
/* Custom styles for user management */
.img-sm {
    width: 40px;
    height: 40px;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.8em;
    padding: 4px 8px;
}

.btn-group .btn-sm {
    padding: 0.25rem 0.5rem;
}

.filter-role.active {
    background-color: #007bff;
    color: white !important;
}

/* Highlight current user row */
tr[data-user="<?= $_SESSION['user_id'] ?>"] {
    background-color: #f8f9fa;
}

/* Responsive table */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.9em;
    }

    .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.8em;
    }
}
</style>