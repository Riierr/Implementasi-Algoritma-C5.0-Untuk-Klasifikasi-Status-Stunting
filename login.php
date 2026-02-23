<?php
// login.php
require_once "koneksi.php";
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Hash password dengan SHA256
    $hashed_password = hash('sha256', $password);
    
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if ($user['password'] === $hashed_password) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Update last login
            $update_query = "UPDATE users SET terakhir_login = NOW() WHERE id = ?";
            $update_stmt = $koneksi->prepare($update_query);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute(); 
            
           // Redirect berdasarkan role
                redirectUserByRole();

            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}

function redirectUserByRole() {
    if (isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {
            case 'admin':
            case 'dokter':
                header('Location: index.php?stunting=home');
                break;
            case 'user':
            case 'pasien':
                header('Location: index.php?stunting=home_pasien'); 
                break;
            default:
                header('Location: index.php?stunting=home');
                break;
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login | Sistem C5.0 Stunting</title>
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

    .login-container {
        width: 100%;
        max-width: 400px;
    }

    .login-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .login-header {
        background: linear-gradient(135deg, #4A90E2 0%, #2ECC71 100%);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .login-header h1 {
        font-size: 24px;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .login-header p {
        font-size: 14px;
        opacity: 0.9;
    }

    .logo {
        font-size: 50px;
        margin-bottom: 15px;
        display: block;
    }

    .login-body {
        padding: 30px;
    }

    .form-group {
        margin-bottom: 20px;
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

    .btn-login {
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

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login i {
        margin-right: 8px;
    }

    .error-message {
        background: #fee;
        color: #e74c3c;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        text-align: center;
        border-left: 4px solid #e74c3c;
    }

    .error-message i {
        margin-right: 8px;
    }

    .login-footer {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e1e5eb;
    }

    .login-footer a {
        color: #667eea;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.3s;
    }

    .login-footer a:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    .login-footer a i {
        margin-right: 5px;
    }

    @media (max-width: 480px) {
        .login-body {
            padding: 20px;
        }

        .login-header {
            padding: 20px;
        }

        .login-header h1 {
            font-size: 20px;
        }
    }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-child logo"></i>
                <h1>Sistem C5.0</h1>
                <p>Klasifikasi Balita Stunting</p>
            </div>

            <div class="login-body">
                <?php if($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form action="" method="POST" id="loginForm">
                    <div class="form-group">
                        <div class="input-with-icon">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" class="form-control" placeholder="Username" name="username" id="username"
                                required autocomplete="username">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-with-icon">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" class="form-control" placeholder="Password" name="password"
                                id="password" required autocomplete="current-password">
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <div class="login-footer">
                    <a href="registrasi.php">
                        <i class="fas fa-key"></i> Lupa Password?
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        // Auto-focus username field
        $('#username').focus();

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

        // Form submission animation
        $('#loginForm').submit(function() {
            const btn = $(this).find('.btn-login');
            btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
            btn.prop('disabled', true);
        });

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // Ctrl+U to focus username
            if (e.ctrlKey && e.keyCode === 85) {
                e.preventDefault();
                $('#username').focus().select();
            }
            // Ctrl+P to toggle password
            if (e.ctrlKey && e.keyCode === 80) {
                e.preventDefault();
                $('#togglePassword').click();
            }
            // Enter to submit if form has focus
            if (e.keyCode === 13 && $('#loginForm input:focus').length > 0) {
                $('#loginForm').submit();
            }
        });
    });
    </script>
</body>

</html>