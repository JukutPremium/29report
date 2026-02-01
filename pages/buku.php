<?php
if (!is_admin()) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Akses ditolak!</div>';
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah'])) {
        $kode_buku = clean_input($_POST['kode_buku']);
        $judul = clean_input($_POST['judul']);
        $pengarang = clean_input($_POST['pengarang']);
        $penerbit = clean_input($_POST['penerbit']);
        $tahun_terbit = clean_input($_POST['tahun_terbit']);
        $kategori = clean_input($_POST['kategori']);
        $jumlah = clean_input($_POST['jumlah_total']);
        $lokasi_rak = clean_input($_POST['lokasi_rak']);

        $query = "INSERT INTO buku (kode_buku, judul, pengarang, penerbit, tahun_terbit, kategori, jumlah_total, jumlah_tersedia, lokasi_rak) 
                  VALUES ('$kode_buku', '$judul', '$pengarang', '$penerbit', '$tahun_terbit', '$kategori', $jumlah, $jumlah, '$lokasi_rak')";

        if (mysqli_query($conn, $query)) {
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Buku berhasil ditambahkan!</div>';
            $action = 'list';
        } else {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Error: ' . mysqli_error($conn) . '</div>';
        }
    } elseif (isset($_POST['edit'])) {
        $kode_buku = clean_input($_POST['kode_buku']);
        $judul = clean_input($_POST['judul']);
        $pengarang = clean_input($_POST['pengarang']);
        $penerbit = clean_input($_POST['penerbit']);
        $tahun_terbit = clean_input($_POST['tahun_terbit']);
        $kategori = clean_input($_POST['kategori']);
        $jumlah = clean_input($_POST['jumlah_total']);
        $lokasi_rak = clean_input($_POST['lokasi_rak']);

        $query = "UPDATE buku SET 
                  kode_buku='$kode_buku', judul='$judul', pengarang='$pengarang', 
                  penerbit='$penerbit', tahun_terbit='$tahun_terbit', kategori='$kategori', 
                  jumlah_total=$jumlah, lokasi_rak='$lokasi_rak'
                  WHERE id=$id";

        if (mysqli_query($conn, $query)) {
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Buku berhasil diupdate!</div>';
            $action = 'list';
        } else {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Error: ' . mysqli_error($conn) . '</div>';
        }
    }
}

if ($action == 'delete' && $id > 0) {
    mysqli_query($conn, "DELETE FROM buku WHERE id=$id");
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Buku berhasil dihapus!</div>';
    $action = 'list';
}

if ($action == 'add' || $action == 'edit') {
    $buku = ['kode_buku' => '', 'judul' => '', 'pengarang' => '', 'penerbit' => '', 'tahun_terbit' => '', 'kategori' => '', 'jumlah_total' => '', 'lokasi_rak' => ''];

    if ($action == 'edit' && $id > 0) {
        $result = mysqli_query($conn, "SELECT * FROM buku WHERE id=$id");
        $buku = mysqli_fetch_assoc($result);
    }
    ?>
    <div class="bg-white rounded-2xl p-8 shadow-lg">
        <h3 class="text-2xl font-bold text-slate-800 mb-6"><?php echo $action == 'add' ? 'Tambah' : 'Edit'; ?> Buku</h3>
        <form method="POST" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Kode Buku *</label>
                    <input type="text" name="kode_buku" value="<?php echo $buku['kode_buku']; ?>" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Kategori *</label>
                    <select name="kategori" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Pilih Kategori</option>
                        <?php
                        $kategori_query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
                        while ($kat = mysqli_fetch_assoc($kategori_query)) {
                            $selected = $buku['kategori'] == $kat['nama_kategori'] ? 'selected' : '';
                            echo "<option value='{$kat['nama_kategori']}' $selected>{$kat['icon']} {$kat['nama_kategori']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-semibold mb-2">Judul Buku *</label>
                <input type="text" name="judul" value="<?php echo $buku['judul']; ?>" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Pengarang *</label>
                    <input type="text" name="pengarang" value="<?php echo $buku['pengarang']; ?>" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Penerbit</label>
                    <input type="text" name="penerbit" value="<?php echo $buku['penerbit']; ?>"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" value="<?php echo $buku['tahun_terbit']; ?>" min="1900"
                        max="2100"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Jumlah Buku *</label>
                    <input type="number" name="jumlah_total" value="<?php echo $buku['jumlah_total']; ?>" required min="1"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Lokasi Rak</label>
                    <input type="text" name="lokasi_rak" value="<?php echo $buku['lokasi_rak']; ?>" placeholder="Contoh: A1"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" name="<?php echo $action == 'add' ? 'tambah' : 'edit'; ?>"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                    <?php echo $action == 'add' ? 'Tambah' : 'Update'; ?> Buku
                </button>
                <a href="dashboard.php?page=buku"
                    class="bg-slate-300 hover:bg-slate-400 text-slate-800 px-6 py-2 rounded-lg font-semibold transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
    <?php
} else {
    $search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
    $where = $search ? "WHERE judul LIKE '%$search%' OR pengarang LIKE '%$search%' OR kode_buku LIKE '%$search%'" : '';

    $query = "SELECT * FROM buku $where ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    ?>

    <div class="bg-white rounded-2xl p-8 shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Daftar Buku</h3>
                <p class="text-slate-600">Total: <?php echo mysqli_num_rows($result); ?> buku</p>
            </div>
            <a href="dashboard.php?page=buku&action=add"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center gap-2">
                Tambah Buku
            </a>
        </div>

        <div class="mb-6">
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="page" value="buku">
                <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Cari buku..."
                    class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                    Cari
                </button>
                <?php if ($search): ?>
                    <a href="dashboard.php?page=buku"
                        class="bg-slate-300 hover:bg-slate-400 text-slate-800 px-4 py-2 rounded-lg font-semibold transition">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-slate-300">
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Kode</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Judul Buku</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Pengarang</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Kategori</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-700">Tersedia</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-700">Total</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-sm"><?php echo $row['kode_buku']; ?></td>
                            <td class="px-4 py-3 font-semibold"><?php echo $row['judul']; ?></td>
                            <td class="px-4 py-3 text-slate-600"><?php echo $row['pengarang']; ?></td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                    <?php echo $row['kategori']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="text-lg font-bold <?php echo $row['jumlah_tersedia'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $row['jumlah_tersedia']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold"><?php echo $row['jumlah_total']; ?></td>
                            <td class="px-4 py-3 text-center">
                                <a href="dashboard.php?page=buku&action=edit&id=<?php echo $row['id']; ?>"
                                    class="text-blue-600 hover:text-blue-800 mx-1">Edit</a>
                                <a href="dashboard.php?page=buku&action=delete&id=<?php echo $row['id']; ?>"
                                    onclick="return confirm('Yakin hapus buku ini?')"
                                    class="text-red-600 hover:text-red-800 mx-1">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>