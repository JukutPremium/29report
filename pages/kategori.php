<?php
if (!is_admin()) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            Akses ditolak!
          </div>';
    exit;
}

mysqli_set_charset($conn, "utf8mb4");
mysqli_query($conn, "SET NAMES utf8mb4");

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$search = trim($_GET['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['tambah'])) {
        $nama_kategori = trim($_POST['nama_kategori']);
        $deskripsi = trim($_POST['deskripsi']);
        $icon = trim($_POST['icon']);

        $stmt = mysqli_prepare($conn, "SELECT id FROM kategori WHERE nama_kategori = ?");
        mysqli_stmt_bind_param($stmt, "s", $nama_kategori);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    Kategori sudah ada!
                  </div>';
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO kategori (nama_kategori, deskripsi, icon) VALUES (?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "sss", $nama_kategori, $deskripsi, $icon);

            if (mysqli_stmt_execute($stmt)) {
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        Kategori berhasil ditambahkan!
                      </div>';
                $action = 'list';
            }
        }
    }

    if (isset($_POST['edit'])) {
        $nama_kategori = trim($_POST['nama_kategori']);
        $deskripsi = trim($_POST['deskripsi']);
        $icon = trim($_POST['icon']);

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE kategori SET nama_kategori=?, deskripsi=?, icon=? WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, "sssi", $nama_kategori, $deskripsi, $icon, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    Kategori berhasil diupdate!
                  </div>';
            $action = 'list';
        }
    }
}


if ($action === 'delete' && $id > 0) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) FROM buku 
         WHERE kategori = (SELECT nama_kategori FROM kategori WHERE id = ?)"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total);
    mysqli_stmt_fetch($stmt);

    if ($total > 0) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                Kategori tidak bisa dihapus karena digunakan oleh ' . $total . ' buku
              </div>';
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM kategori WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                Kategori berhasil dihapus!
              </div>';
    }
    $action = 'list';
}


if ($action === 'add' || $action === 'edit') {

    $kategori = ['nama_kategori' => '', 'deskripsi' => '', 'icon' => '📚'];

    if ($action === 'edit') {
        $stmt = mysqli_prepare($conn, "SELECT * FROM kategori WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $kategori = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    $icons = ['📚', '💻', '📖', '🔬', '🎨', '⚽', '🎵', '🌍', '🏛️', '📕', '📗', '📘', '📙', '👤', '💡', '🎓', '📝', '🎭', '🏆', '🔧'];
    ?>
    <div class="bg-white rounded-2xl p-8 shadow-lg">
        <h3 class="text-2xl font-bold mb-6">
            <?php echo $action === 'add' ? 'Tambah' : 'Edit'; ?> Kategori
        </h3>

        <form method="POST" class="space-y-4">
            <input type="text" name="nama_kategori" required
                value="<?php echo htmlspecialchars($kategori['nama_kategori']); ?>" placeholder="Nama kategori"
                class="w-full border px-4 py-2 rounded-lg">

            <textarea name="deskripsi" rows="3" placeholder="Deskripsi" class="w-full border px-4 py-2 rounded-lg"><?php
            echo htmlspecialchars($kategori['deskripsi']);
            ?></textarea>

            <div>
                <input type="text" name="icon" id="iconInput" value="<?php echo $kategori['icon']; ?>"
                    class="w-24 text-2xl text-center border rounded-lg">
                <div class="flex flex-wrap gap-2 mt-2">
                    <?php foreach ($icons as $ic): ?>
                        <button type="button" onclick="document.getElementById('iconInput').value='<?php echo $ic; ?>'"
                            class="text-3xl p-2 hover:bg-slate-100 rounded">
                            <?php echo $ic; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" name="<?php echo $action === 'add' ? 'tambah' : 'edit'; ?>"
                    class="bg-blue-500 text-white px-6 py-2 rounded-lg">
                    Simpan
                </button>
                <a href="dashboard.php?page=kategori" class="bg-slate-300 px-6 py-2 rounded-lg">Batal</a>
            </div>
        </form>
    </div>

    <?php

} else {

    $where = '';
    if ($search !== '') {
        $safe = mysqli_real_escape_string($conn, $search);
        $where = "WHERE nama_kategori LIKE '%$safe%'";
    }

    $result = mysqli_query(
        $conn,
        "SELECT k.*,
        (SELECT COUNT(*) FROM buku WHERE kategori = k.nama_kategori) AS jumlah_buku
        FROM kategori k $where
        ORDER BY nama_kategori"
    );
    ?>
    <div class="bg-white rounded-2xl p-8 shadow-lg">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Daftar Kategori Buku</h3>
                <p class="text-slate-600">
                    Total: <?php echo mysqli_num_rows($result); ?> kategori
                </p>
            </div>
            <a href="dashboard.php?page=kategori&action=add"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                Tambah Kategori
            </a>
        </div>

        <!-- SEARCH -->
        <div class="mb-6">
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="page" value="kategori">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Cari kategori..." class="flex-1 px-4 py-2 border rounded-lg">
                <button class="bg-blue-500 text-white px-6 py-2 rounded-lg">
                    Cari
                </button>
                <?php if ($search): ?>
                    <a href="dashboard.php?page=kategori" class="bg-slate-300 px-4 py-2 rounded-lg">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- LIST -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="border rounded-xl p-6 hover:shadow-lg transition">
                    <div class="text-5xl"><?php echo $row['icon']; ?></div>
                    <h4 class="font-bold mt-3"><?php echo htmlspecialchars($row['nama_kategori']); ?></h4>
                    <p class="text-sm text-slate-600"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                    <div class="mt-3 flex justify-between">
                        <span><?php echo $row['jumlah_buku']; ?> Buku</span>
                        <div class="flex gap-2">
                            <a href="dashboard.php?page=kategori&action=edit&id=<?php echo $row['id']; ?>">Edit</a>
                            <a href="dashboard.php?page=kategori&action=delete&id=<?php echo $row['id']; ?>"
                                onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php } ?>