<?php
session_start();
require_once 'config/database.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$total_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM buku"))['total'];
$total_anggota = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='siswa'"))['total'];
$total_dipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE status='dipinjam'"))['total'];

$user_pinjaman = 0;
if (!is_admin()) {
    $user_pinjaman = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE id_user = {$_SESSION['user_id']} AND status='dipinjam'"))['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Perpustakaan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Karla:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Karla', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        .sidebar-active { background: rgba(250, 204, 21, 0.2); border-left: 4px solid #facc15; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-72 bg-gradient-to-b from-blue-900 to-purple-900 min-h-screen text-white p-6 fixed">
            <div class="mb-10">
                <h1 class="text-2xl font-bold mb-1">Perpustakaan</h1>
                <p class="text-blue-200 text-sm">Digital System</p>
            </div>

            <div class="mb-8 p-4 bg-white/10 rounded-lg backdrop-blur">
                <p class="text-sm text-blue-200 mb-1">Logged in as</p>
                <p class="font-bold text-lg"><?php echo $_SESSION['nama']; ?></p>
                <span class="inline-block mt-2 px-3 py-1 bg-yellow-400 text-blue-900 text-xs font-bold rounded-full">
                    <?php echo strtoupper($_SESSION['role']); ?>
                </span>
            </div>

            <nav class="space-y-2">
                <a href="dashboard.php?page=home" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo $page == 'home' ? 'sidebar-active' : ''; ?>">
                    <!-- <span class="text-xl">🏠</span> -->
                    <span class="font-semibold">Dashboard</span>
                </a>

                <?php if (is_admin()): ?>
                <a href="dashboard.php?page=buku" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo $page == 'buku' ? 'sidebar-active' : ''; ?>">
                    <!-- <span class="text-xl">📖</span> -->
                    <span class="font-semibold">Data Buku</span>
                </a>

                <a href="dashboard.php?page=kategori" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo $page == 'kategori' ? 'sidebar-active' : ''; ?>">
                    <!-- <span class="text-xl">🏷️</span> -->
                    <span class="font-semibold">Kategori Buku</span>
                </a>

                <a href="dashboard.php?page=anggota" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo $page == 'anggota' ? 'sidebar-active' : ''; ?>">
                    <!-- <span class="text-xl">👥</span> -->
                    <span class="font-semibold">Data Anggota</span>
                </a>
                <?php endif; ?>

                <a href="dashboard.php?page=transaksi" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo $page == 'transaksi' ? 'sidebar-active' : ''; ?>">
                    <!-- <span class="text-xl">🔄</span> -->
                    <span class="font-semibold">Transaksi</span>
                </a>

                <?php if (!is_admin()): ?>
                <a href="dashboard.php?page=pinjam" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo $page == 'pinjam' ? 'sidebar-active' : ''; ?>">
                    <!-- <span class="text-xl">➕</span> -->
                    <span class="font-semibold">Pinjam Buku</span>
                </a>

                <a href="dashboard.php?page=riwayat" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-white/10 transition <?php echo $page == 'riwayat' ? 'sidebar-active' : ''; ?>">
                    <!-- <span class="text-xl">📋</span> -->
                    <span class="font-semibold">Riwayat Saya</span>
                </a>
                <?php endif; ?>

                <a href="logout.php" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-red-500/20 transition text-red-300 hover:text-red-200 mt-8">
                    <!-- <span class="text-xl">🚪</span> -->
                    <span class="font-semibold">Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="ml-72 flex-1 p-8">
            <!-- Header -->
            <div class="mb-8">
                <h2 class="text-4xl font-bold text-slate-800 mb-2">
                    <?php 
                    $titles = [
                        'home' => 'Dashboard',
                        'buku' => 'Data Buku',
                        'kategori' => 'Kategori Buku',
                        'anggota' => 'Data Anggota',
                        'transaksi' => 'Transaksi Peminjaman',
                        'pinjam' => 'Pinjam Buku',
                        'riwayat' => 'Riwayat Peminjaman'
                    ];
                    echo $titles[$page] ?? 'Dashboard';
                    ?>
                </h2>
                <p class="text-slate-600">Selamat datang, <?php echo $_SESSION['nama']; ?>!</p>
            </div>

            <?php if ($page == 'home'): ?>
            <!-- Dashboard Home -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 mb-1">Total Buku</p>
                            <h3 class="text-4xl font-bold text-blue-600"><?php echo $total_buku; ?></h3>
                        </div>
                        <!-- <div class="text-5xl">📚</div> -->
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 mb-1">Total Anggota</p>
                            <h3 class="text-4xl font-bold text-green-600"><?php echo $total_anggota; ?></h3>
                        </div>
                        <!-- <div class="text-5xl">👥</div> -->
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-600 mb-1"><?php echo is_admin() ? 'Sedang Dipinjam' : 'Buku Saya'; ?></p>
                            <h3 class="text-4xl font-bold text-yellow-600"><?php echo is_admin() ? $total_dipinjam : $user_pinjaman; ?></h3>
                        </div>
                        <!-- <div class="text-5xl">📖</div> -->
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl p-8 shadow-lg">
                <h3 class="text-2xl font-bold text-slate-800 mb-6">Quick Actions</h3>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php if (is_admin()): ?>
                    <a href="dashboard.php?page=buku&action=add" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-xl hover:scale-105 transition transform">
                        <!-- <div class="text-3xl mb-2">➕</div> -->
                        <p class="font-semibold">Tambah Buku</p>
                    </a>
                    <a href="dashboard.php?page=anggota&action=add" class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-xl hover:scale-105 transition transform">
                        <!-- <div class="text-3xl mb-2">👤</div> -->
                        <p class="font-semibold">Tambah Anggota</p>
                    </a>
                    <?php else: ?>
                    <a href="dashboard.php?page=pinjam" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-xl hover:scale-105 transition transform">
                        <!-- <div class="text-3xl mb-2">📖</div> -->
                        <p class="font-semibold">Pinjam Buku</p>
                    </a>
                    <a href="dashboard.php?page=riwayat" class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-xl hover:scale-105 transition transform">
                        <!-- <div class="text-3xl mb-2">📋</div> -->
                        <p class="font-semibold">Riwayat</p>
                    </a>
                    <?php endif; ?>
                    <a href="dashboard.php?page=transaksi" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-xl hover:scale-105 transition transform">
                        <!-- <div class="text-3xl mb-2">🔄</div> -->
                        <p class="font-semibold">Lihat Transaksi</p>
                    </a>
                </div>
            </div>

            <?php else: ?>
                <!-- Include halaman sesuai parameter -->
                <?php
                $allowed_pages = is_admin() 
                    ? ['buku', 'kategori', 'anggota', 'transaksi'] 
                    : ['transaksi', 'pinjam', 'riwayat'];
                
                if (in_array($page, $allowed_pages)) {
                    $file = "pages/$page.php";
                    if (file_exists($file)) {
                        include $file;
                    }
                } else {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Akses ditolak!</div>';
                }
                ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>