CREATE DATABASE IF NOT EXISTS perpustakaan_digital;
USE perpustakaan_digital;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'siswa') NOT NULL,
    kelas VARCHAR(20),
    no_telp VARCHAR(15),
    alamat TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kategori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(50) UNIQUE NOT NULL,
    deskripsi TEXT,
    icon VARCHAR(10) DEFAULT '📚',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE buku (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_buku VARCHAR(20) UNIQUE NOT NULL,
    judul VARCHAR(200) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100),
    tahun_terbit YEAR,
    kategori VARCHAR(50),
    jumlah_total INT NOT NULL DEFAULT 0,
    jumlah_tersedia INT NOT NULL DEFAULT 0,
    lokasi_rak VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    id_buku INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE,
    tanggal_harus_kembali DATE NOT NULL,
    status ENUM('dipinjam', 'dikembalikan', 'terlambat') DEFAULT 'dipinjam',
    denda DECIMAL(10,2) DEFAULT 0,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_buku) REFERENCES buku(id) ON DELETE CASCADE
);

INSERT INTO kategori (nama_kategori, deskripsi, icon) VALUES 
('Teknologi', 'Buku-buku tentang teknologi, programming, dan komputer', '💻'),
('Novel', 'Karya fiksi, cerita, dan literatur', '📖'),
('Pelajaran', 'Buku pelajaran dan akademik', '📚'),
('Sejarah', 'Buku tentang sejarah dan peradaban', '🏛️'),
('Referensi', 'Kamus, ensiklopedia, dan buku referensi', '📕'),
('Sains', 'Buku sains, matematika, dan fisika', '🔬'),
('Biografi', 'Kisah hidup tokoh-tokoh penting', '👤');

INSERT INTO users (username, password, nama_lengkap, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

INSERT INTO users (username, password, nama_lengkap, role, kelas, no_telp) VALUES 
('siswa001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Fauzi', 'siswa', 'XII RPL 1', '08123456789'),
('siswa002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Nurhaliza', 'siswa', 'XII RPL 2', '08198765432');

INSERT INTO buku (kode_buku, judul, pengarang, penerbit, tahun_terbit, kategori, jumlah_total, jumlah_tersedia, lokasi_rak) VALUES 
('BK001', 'Pemrograman Web dengan PHP', 'Budi Raharjo', 'Informatika', 2023, 'Teknologi', 5, 5, 'A1'),
('BK002', 'Database MySQL untuk Pemula', 'Andi Prasetyo', 'Andi Publisher', 2022, 'Teknologi', 3, 3, 'A2'),
('BK003', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 'Novel', 10, 10, 'B1'),
('BK004', 'Matematika Kelas XII', 'Tim Penulis', 'Erlangga', 2023, 'Pelajaran', 20, 20, 'C1'),
('BK005', 'Fisika untuk SMA', 'Dr. Yohanes', 'Erlangga', 2023, 'Pelajaran', 15, 15, 'C2');