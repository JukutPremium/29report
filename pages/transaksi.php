<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action == 'return' && $id > 0) {
    $transaksi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transaksi WHERE id=$id"));
    
    if ($transaksi) {
        $tanggal_kembali = date('Y-m-d');
        $denda = hitung_denda($transaksi['tanggal_harus_kembali'], $tanggal_kembali);
        $status = $denda > 0 ? 'terlambat' : 'dikembalikan';
        
        mysqli_query($conn, "UPDATE transaksi SET 
            tanggal_kembali='$tanggal_kembali', 
            status='$status', 
            denda=$denda 
            WHERE id=$id");
        
        mysqli_query($conn, "UPDATE buku SET jumlah_tersedia = jumlah_tersedia + 1 WHERE id={$transaksi['id_buku']}");
        
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                Buku berhasil dikembalikan! ' . ($denda > 0 ? 'Denda: Rp ' . number_format($denda) : '') . '
              </div>';
        $action = 'list';
    }
}

$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';

$where = [];
if (!is_admin()) {
    $where[] = "t.id_user = {$_SESSION['user_id']}";
}
if ($search) {
    $where[] = "(b.judul LIKE '%$search%' OR u.nama_lengkap LIKE '%$search%' OR b.kode_buku LIKE '%$search%')";
}
if ($filter_status) {
    $where[] = "t.status = '$filter_status'";
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "SELECT t.*, b.judul, b.kode_buku, u.nama_lengkap, u.kelas 
          FROM transaksi t 
          JOIN buku b ON t.id_buku = b.id 
          JOIN users u ON t.id_user = u.id 
          $where_clause 
          ORDER BY t.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<div class="bg-white rounded-2xl p-8 shadow-lg">
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-slate-800">
            <?php echo is_admin() ? 'Semua Transaksi' : 'Transaksi Saya'; ?>
        </h3>
        <p class="text-slate-600">Total: <?php echo mysqli_num_rows($result); ?> transaksi</p>
    </div>

    <!-- Filter & Search -->
    <div class="mb-6 grid md:grid-cols-2 gap-4">
        <form method="GET" class="flex gap-2">
            <input type="hidden" name="page" value="transaksi">
            <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Cari transaksi..."
                   class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                Cari
            </button>
        </form>

        <form method="GET" class="flex gap-2">
            <input type="hidden" name="page" value="transaksi">
            <select name="status" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="dipinjam" <?php echo $filter_status == 'dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
                <option value="dikembalikan" <?php echo $filter_status == 'dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
                <option value="terlambat" <?php echo $filter_status == 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
            </select>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                Filter
            </button>
            <?php if ($search || $filter_status): ?>
            <a href="dashboard.php?page=transaksi" class="bg-slate-300 hover:bg-slate-400 text-slate-800 px-4 py-2 rounded-lg font-semibold transition">
                Reset
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-100 border-b-2 border-slate-300">
                    <th class="px-4 py-3 text-left font-bold text-slate-700">Kode Buku</th>
                    <th class="px-4 py-3 text-left font-bold text-slate-700">Judul Buku</th>
                    <?php if (is_admin()): ?>
                    <th class="px-4 py-3 text-left font-bold text-slate-700">Peminjam</th>
                    <?php endif; ?>
                    <th class="px-4 py-3 text-left font-bold text-slate-700">Tgl Pinjam</th>
                    <th class="px-4 py-3 text-left font-bold text-slate-700">Tgl Harus Kembali</th>
                    <th class="px-4 py-3 text-left font-bold text-slate-700">Tgl Kembali</th>
                    <th class="px-4 py-3 text-center font-bold text-slate-700">Status</th>
                    <th class="px-4 py-3 text-center font-bold text-slate-700">Denda</th>
                    <th class="px-4 py-3 text-center font-bold text-slate-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)):
                    $is_late = ($row['status'] == 'dipinjam' && date('Y-m-d') > $row['tanggal_harus_kembali']);
                    $current_denda = $is_late ? hitung_denda($row['tanggal_harus_kembali']) : $row['denda'];
                ?>
                <tr class="border-b border-slate-200 hover:bg-slate-50 <?php echo $is_late ? 'bg-red-50' : ''; ?>">
                    <td class="px-4 py-3 font-mono text-sm"><?php echo $row['kode_buku']; ?></td>
                    <td class="px-4 py-3 font-semibold"><?php echo $row['judul']; ?></td>
                    <?php if (is_admin()): ?>
                    <td class="px-4 py-3">
                        <div class="font-semibold"><?php echo $row['nama_lengkap']; ?></div>
                        <div class="text-xs text-slate-500"><?php echo $row['kelas']; ?></div>
                    </td>
                    <?php endif; ?>
                    <td class="px-4 py-3 text-sm"><?php echo format_tanggal($row['tanggal_pinjam']); ?></td>
                    <td class="px-4 py-3 text-sm"><?php echo format_tanggal($row['tanggal_harus_kembali']); ?></td>
                    <td class="px-4 py-3 text-sm">
                        <?php echo $row['tanggal_kembali'] ? format_tanggal($row['tanggal_kembali']) : '-'; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php 
                        $status_class = [
                            'dipinjam' => 'bg-blue-100 text-blue-700',
                            'dikembalikan' => 'bg-green-100 text-green-700',
                            'terlambat' => 'bg-red-100 text-red-700'
                        ];
                        $display_status = $is_late ? 'TERLAMBAT' : strtoupper($row['status']);
                        $class = $is_late ? $status_class['terlambat'] : $status_class[$row['status']];
                        ?>
                        <span class="px-3 py-1 <?php echo $class; ?> text-xs font-semibold rounded-full">
                            <?php echo $display_status; ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center font-semibold <?php echo $current_denda > 0 ? 'text-red-600' : 'text-slate-600'; ?>">
                        <?php echo $current_denda > 0 ? 'Rp ' . number_format($current_denda) : '-'; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php if ($row['status'] == 'dipinjam'): ?>
                            <a href="dashboard.php?page=transaksi&action=return&id=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Konfirmasi pengembalian buku?')"
                               class="block text-center bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                                Kembalikan
                            </a>
                        <?php else: ?>
                            <span class="text-slate-400">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="<?php echo is_admin() ? '9' : '8'; ?>" class="px-4 py-8 text-center text-slate-500">
                        Tidak ada data transaksi
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>