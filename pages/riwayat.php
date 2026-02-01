<?php
if (is_admin()) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Halaman ini hanya untuk siswa!</div>';
    exit;
}

$filter = isset($_GET['filter']) ? clean_input($_GET['filter']) : 'all';

$where = "t.id_user = {$_SESSION['user_id']}";
if ($filter == 'dipinjam') {
    $where .= " AND t.status = 'dipinjam'";
} elseif ($filter == 'selesai') {
    $where .= " AND (t.status = 'dikembalikan' OR t.status = 'terlambat')";
}

$query = "SELECT t.*, b.judul, b.kode_buku, b.pengarang 
          FROM transaksi t 
          JOIN buku b ON t.id_buku = b.id 
          WHERE $where 
          ORDER BY t.created_at DESC";
$result = mysqli_query($conn, $query);

$stats = [
    'total' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE id_user = {$_SESSION['user_id']}"))['total'],
    'dipinjam' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE id_user = {$_SESSION['user_id']} AND status = 'dipinjam'"))['total'],
    'selesai' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE id_user = {$_SESSION['user_id']} AND (status = 'dikembalikan' OR status = 'terlambat')"))['total'],
    'terlambat' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE id_user = {$_SESSION['user_id']} AND status = 'terlambat'"))['total'],
];
?>

<div class="mb-6 grid md:grid-cols-4 gap-4">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
        <p class="text-blue-100 mb-1">Total Transaksi</p>
        <h3 class="text-4xl font-bold"><?php echo $stats['total']; ?></h3>
    </div>
    
    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg">
        <p class="text-yellow-100 mb-1">Sedang Dipinjam</p>
        <h3 class="text-4xl font-bold"><?php echo $stats['dipinjam']; ?></h3>
    </div>
    
    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
        <p class="text-green-100 mb-1">Selesai</p>
        <h3 class="text-4xl font-bold"><?php echo $stats['selesai']; ?></h3>
    </div>
    
    <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl p-6 shadow-lg">
        <p class="text-red-100 mb-1">Terlambat</p>
        <h3 class="text-4xl font-bold"><?php echo $stats['terlambat']; ?></h3>
    </div>
</div>

<div class="bg-white rounded-2xl p-8 shadow-lg">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Riwayat Peminjaman</h3>
            <p class="text-slate-600">History transaksi peminjaman buku Anda</p>
        </div>
        
        <div class="flex gap-2">
            <a href="dashboard.php?page=riwayat&filter=all" 
               class="px-4 py-2 rounded-lg font-semibold transition <?php echo $filter == 'all' ? 'bg-blue-500 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'; ?>">
                Semua
            </a>
            <a href="dashboard.php?page=riwayat&filter=dipinjam" 
               class="px-4 py-2 rounded-lg font-semibold transition <?php echo $filter == 'dipinjam' ? 'bg-blue-500 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'; ?>">
                Dipinjam
            </a>
            <a href="dashboard.php?page=riwayat&filter=selesai" 
               class="px-4 py-2 rounded-lg font-semibold transition <?php echo $filter == 'selesai' ? 'bg-blue-500 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'; ?>">
                Selesai
            </a>
        </div>
    </div>

    <div class="space-y-4">
        <?php while ($row = mysqli_fetch_assoc($result)): 
            $is_late = ($row['status'] == 'dipinjam' && date('Y-m-d') > $row['tanggal_harus_kembali']);
            $current_denda = $is_late ? hitung_denda($row['tanggal_harus_kembali']) : $row['denda'];
            
            $status_config = [
                'dipinjam' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'badge' => 'bg-blue-100 text-blue-700', 'icon' => '📖'],
                'dikembalikan' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'badge' => 'bg-green-100 text-green-700', 'icon' => '✅'],
                'terlambat' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'badge' => 'bg-red-100 text-red-700', 'icon' => '⚠️']
            ];
            
            $status = $is_late ? 'terlambat' : $row['status'];
            $config = $status_config[$status];
        ?>
        <div class="border-2 <?php echo $config['border']; ?> <?php echo $config['bg']; ?> rounded-xl p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-3xl"><?php echo $config['icon']; ?></span>
                        <div>
                            <h4 class="font-bold text-lg text-slate-800"><?php echo $row['judul']; ?></h4>
                            <p class="text-sm text-slate-600">
                                <?php echo $row['pengarang']; ?> • Kode: <?php echo $row['kode_buku']; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500 mb-1">📅 Tanggal Pinjam</p>
                            <p class="font-semibold text-slate-700"><?php echo format_tanggal($row['tanggal_pinjam']); ?></p>
                        </div>
                        <div>
                            <p class="text-slate-500 mb-1">⏰ Harus Kembali</p>
                            <p class="font-semibold text-slate-700"><?php echo format_tanggal($row['tanggal_harus_kembali']); ?></p>
                        </div>
                        <div>
                            <p class="text-slate-500 mb-1">🔄 Tanggal Kembali</p>
                            <p class="font-semibold text-slate-700">
                                <?php echo $row['tanggal_kembali'] ? format_tanggal($row['tanggal_kembali']) : '-'; ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($current_denda > 0): ?>
                    <div class="mt-4 bg-red-100 border border-red-300 rounded-lg p-3">
                        <p class="font-semibold text-red-700">💰 Denda: Rp <?php echo number_format($current_denda); ?></p>
                        <p class="text-xs text-red-600 mt-1">
                            <?php 
                            if ($is_late) {
                                $hari = ceil((strtotime(date('Y-m-d')) - strtotime($row['tanggal_harus_kembali'])) / 86400);
                                echo "Terlambat $hari hari (Rp 1.000/hari)";
                            }
                            ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($row['catatan']): ?>
                    <div class="mt-3 text-sm text-slate-600">
                        <p class="font-semibold">📝 Catatan:</p>
                        <p><?php echo $row['catatan']; ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="ml-4">
                    <span class="px-4 py-2 <?php echo $config['badge']; ?> text-sm font-semibold rounded-full whitespace-nowrap">
                        <?php echo strtoupper($status); ?>
                    </span>
                    
                    <?php if ($row['status'] == 'dipinjam'): ?>
                    <div class="mt-3">
                        <a href="dashboard.php?page=transaksi&action=return&id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Konfirmasi pengembalian buku?')"
                           class="block text-center bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                            Kembalikan
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        
        <?php if (mysqli_num_rows($result) == 0): ?>
        <div class="text-center py-12 text-slate-500">
            <!-- <div class="text-6xl mb-4">📚</div> -->
            <p class="text-lg font-semibold mb-2">Tidak ada riwayat transaksi</p>
            <p class="text-sm">Mulai pinjam buku untuk melihat riwayat</p>
            <a href="dashboard.php?page=pinjam" class="inline-block mt-4 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                Pinjam Buku
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>