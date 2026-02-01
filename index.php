<?php
session_start();
require_once 'config/database.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username' AND status = 'aktif'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            redirect('dashboard.php');
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Karla:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Karla', sans-serif;
        }
        .font-display {
            font-family: 'Playfair Display', serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #581c87 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .book-icon {
            animation: book-open 3s ease-in-out infinite;
        }
        @keyframes book-open {
            0%, 100% { transform: rotateY(0deg); }
            50% { transform: rotateY(15deg); }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <!-- Decorative Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-32 h-32 bg-yellow-400 rounded-full opacity-20 animate-float"></div>
        <div class="absolute bottom-20 right-10 w-40 h-40 bg-pink-400 rounded-full opacity-20 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 right-1/4 w-24 h-24 bg-blue-300 rounded-full opacity-20 animate-float" style="animation-delay: 4s;"></div>
    </div>

    <div class="w-full max-w-5xl relative z-10">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <!-- Left Side - Branding -->
            <div class="text-white space-y-6 hidden md:block">
                <div class="book-icon">
                    <svg class="w-32 h-32 mx-auto mb-8" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 4h2v5l-1-.75L9 9V4zm9 16H6V4h1v9l3-2.25L13 13V4h5v16z"/>
                    </svg>
                </div>
                <h1 class="font-display text-5xl md:text-6xl font-black leading-tight">
                    Perpustakaan<br/>Digital
                </h1>
                <p class="text-xl text-blue-100 font-light">
                    Sistem manajemen peminjaman buku modern untuk sekolah masa depan
                </p>
                <div class="flex gap-4 text-sm">
                    <div class="glass-effect px-4 py-2 rounded-lg">
                        <p class="font-bold">📚 Digital</p>
                    </div>
                    <div class="glass-effect px-4 py-2 rounded-lg">
                        <p class="font-bold">⚡ Cepat</p>
                    </div>
                    <div class="glass-effect px-4 py-2 rounded-lg">
                        <p class="font-bold">🔒 Aman</p>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="glass-effect rounded-3xl p-8 md:p-10 shadow-2xl">
                <div class="mb-8">
                    <h2 class="font-display text-3xl font-bold text-white mb-2">Selamat Datang</h2>
                    <p class="text-blue-100">Silakan login untuk melanjutkan</p>
                </div>

                <?php if ($error): ?>
                <div class="mb-6 bg-red-500/20 border border-red-400 text-white px-4 py-3 rounded-lg">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-white font-semibold mb-2">Username</label>
                        <input type="text" name="username" required 
                               class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition"
                               placeholder="Masukkan username">
                    </div>

                    <div>
                        <label class="block text-white font-semibold mb-2">Password</label>
                        <input type="password" name="password" required 
                               class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition"
                               placeholder="Masukkan password">
                    </div>

                    <button type="submit" 
                            class="w-full bg-yellow-400 hover:bg-yellow-300 text-blue-900 font-bold py-4 rounded-lg transition transform hover:scale-105 shadow-lg">
                        Masuk ke Sistem
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-white/20">
                    <p class="text-blue-100 text-sm text-center mb-3">Belum punya akun?</p>
                    <a href="register.php" 
                       class="block text-center text-yellow-400 hover:text-yellow-300 font-semibold transition">
                        Daftar sebagai Anggota →
                    </a>
                </div>

                <div class="mt-6 text-center text-xs text-blue-200">
                    <p>Default Login:</p>
                    <p class="font-semibold">Admin: admin / password</p>
                    <p class="font-semibold">Siswa: siswa001 / password</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>