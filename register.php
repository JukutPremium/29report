<?php
session_start();
require_once 'config/database.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $kelas = clean_input($_POST['kelas']);
    $no_telp = clean_input($_POST['no_telp']);
    $alamat = clean_input($_POST['alamat']);
    
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Username sudah digunakan!';
    } else {
        $query = "INSERT INTO users (username, password, nama_lengkap, role, kelas, no_telp, alamat) 
                  VALUES ('$username', '$password', '$nama_lengkap', 'siswa', '$kelas', '$no_telp', '$alamat')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $error = 'Registrasi gagal: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota - Perpustakaan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Karla:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Karla', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #581c87 100%); }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl relative z-10">
        <div class="glass-effect rounded-3xl p-8 md:p-10 shadow-2xl">
            <div class="mb-8">
                <h2 class="font-display text-3xl font-bold text-white mb-2">Daftar Anggota Baru</h2>
                <p class="text-blue-100">Lengkapi formulir di bawah untuk mendaftar</p>
            </div>

            <?php if ($success): ?>
            <div class="mb-6 bg-green-500/20 border border-green-400 text-white px-4 py-3 rounded-lg">
                <?php echo $success; ?>
                <a href="index.php" class="underline ml-2">Login sekarang</a>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-500/20 border border-red-400 text-white px-4 py-3 rounded-lg">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-white font-semibold mb-2">Username *</label>
                        <input type="text" name="username" required 
                               class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>

                    <div>
                        <label class="block text-white font-semibold mb-2">Password *</label>
                        <input type="password" name="password" required 
                               class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" required 
                           class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>

                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-white font-semibold mb-2">Kelas *</label>
                        <input type="text" name="kelas" required placeholder="Contoh: XII RPL 1"
                               class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>

                    <div>
                        <label class="block text-white font-semibold mb-2">No. Telepon *</label>
                        <input type="text" name="no_telp" required 
                               class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                    </div>
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">Alamat *</label>
                    <textarea name="alamat" required rows="3"
                              class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-yellow-400"></textarea>
                </div>

                <button type="submit" 
                        class="w-full bg-yellow-400 hover:bg-yellow-300 text-blue-900 font-bold py-4 rounded-lg transition transform hover:scale-105 shadow-lg">
                    Daftar Sekarang
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="index.php" class="text-yellow-400 hover:text-yellow-300 font-semibold">
                    ← Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>