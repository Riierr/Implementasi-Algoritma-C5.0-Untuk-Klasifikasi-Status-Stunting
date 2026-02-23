<?php

require_once "koneksi.php";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    
    
    $role = 'user';
    
    // Validasi input
    if (empty($nama) || empty($username) || empty($password) || empty($email)) {
        $error = "Semua field wajib diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Cek apakah username sudah ada
        $check_user = $koneksi->prepare("SELECT id FROM users WHERE username = ?");
        $check_user->bind_param("s", $username);
        $check_user->execute();
        $check_user->store_result();
        
        if ($check_user->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Cek apakah email sudah ada
            $check_email = $koneksi->prepare("SELECT id FROM users WHERE email = ?");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            $check_email->store_result();
            
            if ($check_email->num_rows > 0) {
                $error = "Email sudah terdaftar!";
            } else {
                // Hash password dengan SHA256
                $hashed_password = hash('sha256', $password);
                
                // Insert data ke database
                $query = "INSERT INTO users (nama, username, password, email, role, tanggal_buat) 
                         VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $koneksi->prepare($query);
                $stmt->bind_param("sssss", $nama, $username, $hashed_password, $email, $role);
                
                if ($stmt->execute()) {
                    $success = "Pendaftaran berhasil! Silakan login.";
                } else {
                    $error = "Gagal mendaftar. Silakan coba lagi.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Registrasi | Sistem C5.0 Stunting</title>
    <link rel="shortcut icon" href="img/bayi.jpg" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Nunito', sans-serif;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .register-container {
        width: 100%;
        max-width: 450px;
    }

    .register-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .register-header {
        background: linear-gradient(135deg, #4A90E2 0%, #2ECC71 100%);
        color: white;
        padding: 25px;
        text-align: center;
        position: relative;
    }

    .back-to-login {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s;
    }

    .back-to-login:hover {
        opacity: 0.8;
    }

    .back-to-login i {
        margin-right: 5px;
    }

    .register-header h1 {
        font-size: 22px;
        margin-bottom: 5px;
        font-weight: 700;
    }

    .register-header p {
        font-size: 14px;
        opacity: 0.9;
    }

    .logo {
        font-size: 40px;
        margin-bottom: 10px;
        display: block;
    }

    .register-body {
        padding: 25px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-size: 14px;
        color: #333;
        font-weight: 600;
    }

    .form-group label .required {
        color: #e74c3c;
    }

    .input-with-icon {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #667eea;
        z-index: 1;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 2px solid #e1e5eb;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
        background: #f8f9fa;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #667eea;
        cursor: pointer;
        font-size: 14px;
    }

    .btn-register {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 10px;
    }

    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-register:active {
        transform: translateY(0);
    }

    .btn-register i {
        margin-right: 8px;
    }

    .alert-message {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        text-align: center;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-error {
        background: #fee;
        color: #e74c3c;
        border-left: 4px solid #e74c3c;
    }

    .alert-message i {
        margin-right: 8px;
    }

    .register-footer {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e1e5eb;
        font-size: 14px;
    }

    .register-footer a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }

    .register-footer a:hover {
        text-decoration: underline;
    }

    .password-strength {
        margin-top: 5px;
        height: 4px;
        border-radius: 2px;
        background: #e1e5eb;
        overflow: hidden;
    }

    .strength-bar {
        height: 100%;
        width: 0%;
        transition: all 0.3s;
    }

    .strength-weak {
        background: #e74c3c;
        width: 33%;
    }

    .strength-medium {
        background: #f39c12;
        width: 66%;
    }

    .strength-strong {
        background: #2ecc71;
        width: 100%;
    }

    .password-hint {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    @media (max-width: 480px) {
        .register-body {
            padding: 20px;
        }
        
        .register-header {
            padding: 20px;
        }
        
        .register-header h1 {
            font-size: 20px;
        }
        
        .back-to-login span {
            display: none;
        }
        
        .back-to-login i {
            margin-right: 0;
            font-size: 16px;
        }
    }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <a href="login.php" class="back-to-login">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
                <i class="fas fa-user-plus logo"></i>
                <h1>Daftar Akun Baru</h1>
                <p>Bergabung dengan Sistem C5.0 Stunting</p>
            </div>

            <div class="register-body">
                <?php if($error): ?>
                <div class="alert-message alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                <div class="alert-message alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <br><small>Anda akan dialihkan ke halaman login dalam 5 detik...</small>
                </div>
                <?php endif; ?>

                <form action="" method="POST" id="registerForm">
                    <div class="form-group">
                        <label>Nama Lengkap <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" 
                                   class="form-control" 
                                   placeholder="Masukkan nama lengkap"
                                   name="nama" 
                                   id="nama"
                                   required
                                   value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Username <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <i class="fas fa-at input-icon"></i>
                            <input type="text" 
                                   class="form-control" 
                                   placeholder="Pilih username"
                                   name="username" 
                                   id="username"
                                   required
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" 
                                   class="form-control" 
                                   placeholder="contoh@email.com"
                                   name="email" 
                                   id="email"
                                   required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   class="form-control" 
                                   placeholder="Minimal 6 karakter"
                                   name="password" 
                                   id="password"
                                   required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar" id="passwordStrength"></div>
                        </div>
                        <div class="password-hint" id="passwordHint"></div>
                    </div>

                    <div class="form-group">
                        <label>Konfirmasi Password <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   class="form-control" 
                                   placeholder="Ketik ulang password"
                                   name="confirm_password" 
                                   id="confirm_password"
                                   required>
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye" id="toggleConfirmIcon"></i>
                            </button>
                        </div>
                        <div class="password-hint" id="confirmHint"></div>
                    </div>

                    <button type="submit" class="btn-register" id="submitBtn">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>
                </form>

                <div class="register-footer">
                    Sudah punya akun? <a href="login.php">Login di sini</a>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        // Auto-focus nama field
        $('#nama').focus();

        // Toggle password visibility
        $('#togglePassword').click(function() {
            const passwordInput = $('#password');
            const toggleIcon = $('#toggleIcon');
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                toggleIcon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                toggleIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Toggle confirm password visibility
        $('#toggleConfirmPassword').click(function() {
            const confirmInput = $('#confirm_password');
            const toggleIcon = $('#toggleConfirmIcon');
            
            if (confirmInput.attr('type') === 'password') {
                confirmInput.attr('type', 'text');
                toggleIcon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                confirmInput.attr('type', 'password');
                toggleIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Password strength checker
        $('#password').on('keyup', function() {
            const password = $(this).val();
            const strengthBar = $('#passwordStrength');
            const hint = $('#passwordHint');
            
            let strength = 0;
            let message = '';
            
            if (password.length === 0) {
                strengthBar.removeClass().addClass('strength-bar');
                strengthBar.css('width', '0%');
                hint.text('');
                return;
            }
            
            // Check length
            if (password.length >= 6) strength += 1;
            
            // Check for lowercase
            if (/[a-z]/.test(password)) strength += 1;
            
            // Check for uppercase
            if (/[A-Z]/.test(password)) strength += 1;
            
            // Check for numbers
            if (/[0-9]/.test(password)) strength += 1;
            
            // Check for special characters
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength bar
            if (strength <= 2) {
                strengthBar.removeClass().addClass('strength-bar strength-weak');
                message = 'Password lemah';
            } else if (strength <= 4) {
                strengthBar.removeClass().addClass('strength-bar strength-medium');
                message = 'Password cukup kuat';
            } else {
                strengthBar.removeClass().addClass('strength-bar strength-strong');
                message = 'Password sangat kuat';
            }
            
            hint.text(message);
        });

        // Confirm password checker
        $('#confirm_password').on('keyup', function() {
            const password = $('#password').val();
            const confirmPassword = $(this).val();
            const hint = $('#confirmHint');
            
            if (confirmPassword.length === 0) {
                hint.text('');
                return;
            }
            
            if (password === confirmPassword) {
                hint.html('<i class="fas fa-check" style="color:#2ecc71"></i> Password cocok');
            } else {
                hint.html('<i class="fas fa-times" style="color:#e74c3c"></i> Password tidak cocok');
            }
        });

        // Form submission
        $('#registerForm').submit(function() {
            const btn = $('#submitBtn');
            btn.html('<i class="fas fa-spinner fa-spin"></i> Mendaftarkan...');
            btn.prop('disabled', true);
            return true;
        });

        // Redirect jika sukses
        <?php if($success): ?>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 5000);
        <?php endif; ?>

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // Ctrl+Enter to submit
            if (e.ctrlKey && e.keyCode === 13) {
                e.preventDefault();
                $('#registerForm').submit();
            }
        });
    });
    </script>
</body>
</html>