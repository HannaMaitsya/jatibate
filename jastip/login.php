<?php
session_start();
require_once 'config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Username atau password salah';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Jastip</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #ffffff;
        }

        .left-side {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .left-side::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            top: -200px;
            right: -200px;
        }

        .left-side::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -100px;
            left: -100px;
        }

        .brand-section {
            position: relative;
            z-index: 10;
            text-align: center;
        }

        .brand-logo {
            width: 180px;
            height: 180px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            overflow: hidden;
            border: 6px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            background: #ffffff;
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            line-height: 1.6;
        }

        .right-side {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #fafafa;
        }

        .login-box {
            width: 100%;
            max-width: 440px;
        }

        .login-header {
            margin-bottom: 2.5rem;
        }

        .login-header h1 {
            font-size: 2.25rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            font-size: 1rem;
            color: #64748b;
            font-weight: 500;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            color: #0f172a;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            outline: none;
            border-color: #dc2626;
            background: #ffffff;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: #dc2626;
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }

        .btn-login:hover {
            background: #b91c1c;
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        @media (max-width: 968px) {
            body {
                grid-template-columns: 1fr;
            }

            .left-side {
                display: none;
            }

            .right-side {
                background: #ffffff;
            }
        }

        @media (max-width: 640px) {
            .login-header h1 {
                font-size: 1.875rem;
            }

            .login-box {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="left-side">
        <div class="brand-section">
            <div class="brand-logo">
                <?php if (file_exists('assets/img/pfp.png')): ?>
                    <img src="assets/img/pfp.png" alt="Logo Jastip">
                <?php else: ?>
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php endif; ?>
            </div>
            <h1 class="brand-title">JATIBATE</h1>
            <p class="brand-subtitle">Jasa Titip Barang terpecaya</p>
        </div>
    </div>

    <div class="right-side">
        <div class="login-box">
            <div class="login-header">
                <h1>Masuk</h1>
                <p>Silakan masuk dengan akun Anda</p>
            </div>

            <?php if ($error): ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           placeholder="Masukkan username" 
                           required 
                           autocomplete="off">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Masukkan password" 
                           required>
                </div>

                <button type="submit" class="btn-login">Masuk</button>
            </form>
        </div>
    </div>

    <script>
        <?php if ($error): ?>
        document.getElementById('username').value = '';
        document.getElementById('password').value = '';
        document.getElementById('username').focus();
        <?php endif; ?>
    </script>
</body>
</html>
