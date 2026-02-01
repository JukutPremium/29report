<?php
if (is_admin()) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Halaman ini hanya untuk siswa!</div>';
    exit;
}

$check_pinjam = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE id_user = {$_SESSION['user_id']} AND status = 'dipinjam'");
$total_pinjam = mysqli_fetch_assoc($check_pinjam)['total'];

$max_pinjam = 3; 
$can_borrow = $total_pinjam < $max_pinjam;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pinjam'])) {
    if (!$can_borrow) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                Anda sudah mencapai batas maksimal peminjaman (3 buku)!
              </div>';
    } else {
        $id_buku = (int)$_POST['id_buku'];
        $tanggal_pinjam = date('Y-m-d');
        $tanggal_harus_kembali = date('Y-m-d', strtotime('+7 days'));
        
        $buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM buku WHERE id=$id_buku"));
        
        if ($buku && $buku['jumlah_tersedia'] > 0) {
            $query = "INSERT INTO transaksi (id_user, id_buku, tanggal_pinjam, tanggal_harus_kembali, status) 
                      VALUES ({$_SESSION['user_id']}, $id_buku, '$tanggal_pinjam', '$tanggal_harus_kembali', 'dipinjam')";
            
            if (mysqli_query($conn, $query)) {
                mysqli_query($conn, "UPDATE buku SET jumlah_tersedia = jumlah_tersedia - 1 WHERE id=$id_buku");
                
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        Buku berhasil dipinjam! Harap dikembalikan sebelum ' . format_tanggal($tanggal_harus_kembali) . '
                      </div>';
                
                $total_pinjam++;
                $can_borrow = $total_pinjam < $max_pinjam;
            } else {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        Gagal meminjam buku: ' . mysqli_error($conn) . '
                      </div>';
            }
        } else {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    Buku tidak tersedia atau stok habis!
                  </div>';
        }
    }
}

$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';

$where = ["jumlah_tersedia > 0"];
if ($search) {
    $where[] = "(judul LIKE '%$search%' OR pengarang LIKE '%$search%' OR kode_buku LIKE '%$search%')";
}
if ($kategori) {
    $where[] = "kategori = '$kategori'";
}

$where_clause = 'WHERE ' . implode(' AND ', $where);

$query = "SELECT * FROM buku $where_clause ORDER BY judul ASC";
$result = mysqli_query($conn, $query);
?>

<div class="mb-6 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl p-6 shadow-lg">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold mb-2">Status Peminjaman Anda</h3>
            <p class="text-blue-100">Buku yang sedang dipinjam: <?php echo $total_pinjam; ?> dari <?php echo $max_pinjam; ?></p>
        </div>
        <!-- <div class="text-6xl">📚</div> -->
    </div>
    <?php if (!$can_borrow): ?>
    <div class="mt-4 bg-red-500/30 border border-red-300 rounded-lg p-3">
        <p class="font-semibold">⚠️ Anda sudah mencapai batas maksimal peminjaman!</p>
        <p class="text-sm">Kembalikan buku terlebih dahulu untuk meminjam buku lain.</p>
    </div>
    <?php endif; ?>
</div>

<div class="bg-white rounded-2xl p-8 shadow-lg">
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-slate-800 mb-2">Daftar Buku Tersedia</h3>
        <p class="text-slate-600">Pilih buku yang ingin Anda pinjam</p>
    </div>

    <!-- Filter & Search -->
    <div class="mb-6 grid md:grid-cols-2 gap-4">
        <form method="GET" class="flex gap-2">
            <input type="hidden" name="page" value="pinjam">
            <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Cari buku..."
                   class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                Cari
            </button>
        </form>

        <form method="GET" class="flex gap-2">
            <input type="hidden" name="page" value="pinjam">
            <select name="kategori" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Semua Kategori</option>
                <?php 
                $kategori_list = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
                while ($kat = mysqli_fetch_assoc($kategori_list)): 
                ?>
                <option value="<?php echo $kat['nama_kategori']; ?>" <?php echo $kategori == $kat['nama_kategori'] ? 'selected' : ''; ?>>
                    <?php echo $kat['icon'] . ' ' . $kat['nama_kategori']; ?>
                </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                Filter
            </button>
            <?php if ($search || $kategori): ?>
            <a href="dashboard.php?page=pinjam" class="bg-slate-300 hover:bg-slate-400 text-slate-800 px-4 py-2 rounded-lg font-semibold transition">
                Reset
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Daftar Buku dalam Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php while ($buku = mysqli_fetch_assoc($result)): ?>
        <div class="border-2 border-slate-200 rounded-xl p-6 hover:border-blue-500 hover:shadow-lg transition">
            <div class="mb-4">
                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                    <?php echo $buku['kategori']; ?>
                </span>
                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full ml-2">
                    <?php echo $buku['jumlah_tersedia']; ?> tersedia
                </span>
            </div>
            
            <!-- <div class="text-5xl mb-4 text-center">📖</div> -->
            
            <h4 class="font-bold text-lg text-slate-800 mb-2 line-clamp-2"><?php echo $buku['judul']; ?></h4>
            <p class="text-slate-600 text-sm mb-1">📝 <?php echo $buku['pengarang']; ?></p>
            <p class="text-slate-500 text-xs mb-1">🏢 <?php echo $buku['penerbit']; ?> (<?php echo $buku['tahun_terbit']; ?>)</p>
            <p class="text-slate-500 text-xs mb-4">📍 Rak: <?php echo $buku['lokasi_rak']; ?></p>
            
            <form method="POST" class="mt-4">
                <input type="hidden" name="id_buku" value="<?php echo $buku['id']; ?>">
                <button type="submit" name="pinjam" 
                        <?php echo !$can_borrow ? 'disabled' : ''; ?>
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg font-semibold transition disabled:bg-slate-300 disabled:cursor-not-allowed">
                    <?php echo $can_borrow ? 'Pinjam Buku' : 'Limit Tercapai'; ?>
                </button>
            </form>
        </div>
        <?php endwhile; ?>
        
        <?php if (mysqli_num_rows($result) == 0): ?>
        <div class="col-span-full text-center py-12 text-slate-500">
            <!-- <div class="text-6xl mb-4">📚</div> -->
            <p class="text-lg">Tidak ada buku tersedia</p>
        </div>
        <?php endif; ?>
    </div>
</div>