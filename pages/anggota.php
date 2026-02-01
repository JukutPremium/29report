<?php
if (!is_admin()) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Akses ditolak!</div>';
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah'])) {
        $username = clean_input($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $nama_lengkap = clean_input($_POST['nama_lengkap']);
        $kelas = clean_input($_POST['kelas']);
        $no_telp = clean_input($_POST['no_telp']);
        $alamat = clean_input($_POST['alamat']);

        $query = "INSERT INTO users (username, password, nama_lengkap, role, kelas, no_telp, alamat) 
                  VALUES ('$username', '$password', '$nama_lengkap', 'siswa', '$kelas', '$no_telp', '$alamat')";

        if (mysqli_query($conn, $query)) {
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Anggota berhasil ditambahkan!</div>';
            $action = 'list';
        } else {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Error: ' . mysqli_error($conn) . '</div>';
        }
    } elseif (isset($_POST['edit'])) {
        $username = clean_input($_POST['username']);
        $nama_lengkap = clean_input($_POST['nama_lengkap']);
        $kelas = clean_input($_POST['kelas']);
        $no_telp = clean_input($_POST['no_telp']);
        $alamat = clean_input($_POST['alamat']);
        $status = clean_input($_POST['status']);

        $password_update = '';
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $password_update = ", password='$password'";
        }

        $query = "UPDATE users SET 
                  username='$username', nama_lengkap='$nama_lengkap', kelas='$kelas', 
                  no_telp='$no_telp', alamat='$alamat', status='$status' $password_update
                  WHERE id=$id";

        if (mysqli_query($conn, $query)) {
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Anggota berhasil diupdate!</div>';
            $action = 'list';
        } else {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Error: ' . mysqli_error($conn) . '</div>';
        }
    }
}

if ($action == 'delete' && $id > 0) {
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='siswa'");
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Anggota berhasil dihapus!</div>';
    $action = 'list';
}

if ($action == 'add' || $action == 'edit') {
    $user = ['username' => '', 'nama_lengkap' => '', 'kelas' => '', 'no_telp' => '', 'alamat' => '', 'status' => 'aktif'];

    if ($action == 'edit' && $id > 0) {
        $result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
        $user = mysqli_fetch_assoc($result);
    }
    ?>
    <div class="bg-white rounded-2xl p-8 shadow-lg">
        <h3 class="text-2xl font-bold text-slate-800 mb-6"><?php echo $action == 'add' ? 'Tambah' : 'Edit'; ?> Anggota</h3>
        <form method="POST" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Username *</label>
                    <input type="text" name="username" value="<?php echo $user['username']; ?>" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Password
                        <?php echo $action == 'edit' ? '(kosongkan jika tidak diubah)' : '*'; ?></label>
                    <input type="password" name="password" <?php echo $action == 'add' ? 'required' : ''; ?>
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-semibold mb-2">Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" value="<?php echo $user['nama_lengkap']; ?>" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Kelas *</label>
                    <input type="text" name="kelas" value="<?php echo $user['kelas']; ?>" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">No. Telepon *</label>
                    <input type="text" name="no_telp" value="<?php echo $user['no_telp']; ?>" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-slate-700 font-semibold mb-2">Alamat *</label>
                <textarea name="alamat" required rows="3"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"><?php echo $user['alamat']; ?></textarea>
            </div>

            <?php if ($action == 'edit'): ?>
                <div>
                    <label class="block text-slate-700 font-semibold mb-2">Status *</label>
                    <select name="status" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="aktif" <?php echo $user['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="nonaktif" <?php echo $user['status'] == 'nonaktif' ? 'selected' : ''; ?>>Non-Aktif</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="flex gap-3 pt-4">
                <button type="submit" name="<?php echo $action == 'add' ? 'tambah' : 'edit'; ?>"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                    <?php echo $action == 'add' ? 'Tambah' : 'Update'; ?> Anggota
                </button>
                <a href="dashboard.php?page=anggota"
                    class="bg-slate-300 hover:bg-slate-400 text-slate-800 px-6 py-2 rounded-lg font-semibold transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
    <?php
} else {
    $search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
    $where = $search ? "AND (nama_lengkap LIKE '%$search%' OR username LIKE '%$search%' OR kelas LIKE '%$search%')" : '';

    $query = "SELECT * FROM users WHERE role='siswa' $where ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    ?>

    <div class="bg-white rounded-2xl p-8 shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-2xl font-bold text-slate-800">Daftar Anggota</h3>
                <p class="text-slate-600">Total: <?php echo mysqli_num_rows($result); ?> anggota</p>
            </div>
            <a href="dashboard.php?page=anggota&action=add"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center gap-2">
                Tambah Anggota
            </a>
        </div>

        <div class="mb-6">
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="page" value="anggota">
                <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Cari anggota..."
                    class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold transition">
                    Cari
                </button>
                <?php if ($search): ?>
                    <a href="dashboard.php?page=anggota"
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
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Username</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Nama Lengkap</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">Kelas</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-700">No. Telepon</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-700">Status</th>
                        <th class="px-4 py-3 text-center font-bold text-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-sm"><?php echo $row['username']; ?></td>
                            <td class="px-4 py-3 font-semibold"><?php echo $row['nama_lengkap']; ?></td>
                            <td class="px-4 py-3"><?php echo $row['kelas']; ?></td>
                            <td class="px-4 py-3 text-slate-600"><?php echo $row['no_telp']; ?></td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-3 py-1 <?php echo $row['status'] == 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> text-xs font-semibold rounded-full">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="dashboard.php?page=anggota&action=edit&id=<?php echo $row['id']; ?>"
                                    class="text-blue-600 hover:text-blue-800 mx-1">Edit</a>
                                <a href="dashboard.php?page=anggota&action=delete&id=<?php echo $row['id']; ?>"
                                    onclick="return confirm('Yakin hapus anggota ini?')"
                                    class="text-red-600 hover:text-red-800 mx-1">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>