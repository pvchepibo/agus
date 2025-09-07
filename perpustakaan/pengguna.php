<?php
require_once 'config.php';

// Cek otentikasi dan pastikan levelnya adalah admin
if (!isset($_SESSION['id_pengguna']) || $_SESSION['level'] !== 'admin') {
    header("location: login.php");
    exit;
}

$pesan_sukses = "";
$pesan_error = "";

// --- PROSES AKSI (HAPUS & TAMBAH) ---

// Proses Hapus Pengguna
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    // Admin tidak bisa menghapus akunnya sendiri
    if ($id_hapus == $_SESSION['id_pengguna']) {
        $_SESSION['pesan_error'] = "Anda tidak bisa menghapus akun Anda sendiri.";
    } else {
        $query_hapus = "DELETE FROM tabel_pengguna WHERE id_pengguna = $id_hapus";
        if (mysqli_query($koneksi, $query_hapus)) {
            $_SESSION['pesan_sukses'] = "Pengguna berhasil dihapus!";
        } else {
            $_SESSION['pesan_error'] = "Gagal menghapus pengguna.";
        }
    }
    header("Location: pengguna.php");
    exit;
}

// Proses Tambah Pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_pengguna'])) {
    // Menggunakan fungsi clean_input() dari config.php
    $nama_lengkap = clean_input($koneksi, $_POST['nama_lengkap']);
    $username = clean_input($koneksi, $_POST['username']);
    $level = clean_input($koneksi, $_POST['level']);
    
    // Validasi dan enkripsi password
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Cek apakah username sudah ada
        $cek_user = mysqli_query($koneksi, "SELECT username FROM tabel_pengguna WHERE username = '$username'");
        if (mysqli_num_rows($cek_user) > 0) {
            $_SESSION['pesan_error'] = "Username '$username' sudah terdaftar. Silakan gunakan username lain.";
        } else {
            $query_tambah = "INSERT INTO tabel_pengguna (nama_lengkap, username, password, level) VALUES ('$nama_lengkap', '$username', '$password', '$level')";
            if (mysqli_query($koneksi, $query_tambah)) {
                $_SESSION['pesan_sukses'] = "Pengguna baru berhasil ditambahkan!";
            } else {
                $_SESSION['pesan_error'] = "Gagal menambahkan pengguna: " . mysqli_error($koneksi);
            }
        }
    } else {
        $_SESSION['pesan_error'] = "Password tidak boleh kosong.";
    }
    header("Location: pengguna.php");
    exit;
}


// Ambil pesan dari session
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_sukses = $_SESSION['pesan_sukses'];
    unset($_SESSION['pesan_sukses']);
}
if (isset($_SESSION['pesan_error'])) {
    $pesan_error = $_SESSION['pesan_error'];
    unset($_SESSION['pesan_error']);
}

// Ambil semua data pengguna untuk ditampilkan
$query_pengguna = "SELECT * FROM tabel_pengguna ORDER BY id_pengguna DESC";
$result_pengguna = mysqli_query($koneksi, $query_pengguna);

require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Kelola Pengguna Sistem</h2>

        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $pesan_sukses; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $pesan_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tambahPenggunaModal">
            <i class="fas fa-user-plus me-2"></i>Tambah Pengguna
        </button>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Level</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_pengguna && mysqli_num_rows($result_pengguna) > 0): ?>
                                <?php while($pengguna = mysqli_fetch_assoc($result_pengguna)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pengguna['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($pengguna['username']); ?></td>
                                        <td><?php echo htmlspecialchars($pengguna['level']); ?></td>
                                        <td>
                                            <a href="edit_pengguna.php?id=<?php echo $pengguna['id_pengguna']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($pengguna['id_pengguna'] != $_SESSION['id_pengguna']): // Tombol hapus tidak muncul untuk user yg sedang login ?>
                                            <a href="pengguna.php?hapus=<?php echo $pengguna['id_pengguna']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada data pengguna.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal fade" id="tambahPenggunaModal" tabindex="-1" aria-labelledby="tambahPenggunaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahPenggunaModalLabel">Form Tambah Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="pengguna.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="level" class="form-label">Level</label>
                        <select class="form-select" id="level" name="level" required>
                            <option value="admin">Admin</option>
                            <option value="petugas">Petugas</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_pengguna" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>