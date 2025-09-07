<?php
require_once 'config.php';
require_once 'templates/header.php';

// Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['id_pengguna']) || $_SESSION['level'] != 'admin') {
    redirect('login.php');
}

$pesan_sukses = '';
$pesan_error = '';
$pengguna = null;

// Ambil ID pengguna dari URL
if (isset($_GET['id'])) {
    $id_pengguna_edit = (int)$_GET['id'];

    // Ambil data pengguna yang akan diedit untuk ditampilkan di form
    $query_select = "SELECT id_pengguna, nama_lengkap, username, level FROM tabel_pengguna WHERE id_pengguna = $id_pengguna_edit";
    $result_select = mysqli_query($koneksi, $query_select);
    if ($result_select && mysqli_num_rows($result_select) > 0) {
        $pengguna = mysqli_fetch_assoc($result_select);
    } else {
        $pesan_error = "Pengguna tidak ditemukan.";
    }
} else {
    // Jika tidak ada ID di URL, kembali ke halaman pengguna
    redirect('pengguna.php');
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_pengguna'])) {
    $nama_lengkap = clean_input($koneksi, $_POST['nama_lengkap']);
    $username = clean_input($koneksi, $_POST['username']);
    $level = clean_input($koneksi, $_POST['level']);
    $password_baru = $_POST['password']; // Tidak perlu di-clean karena akan di-hash

    // Cek apakah username sudah dipakai oleh pengguna lain
    $query_cek_username = "SELECT id_pengguna FROM tabel_pengguna WHERE username = '$username' AND id_pengguna != $id_pengguna_edit";
    $result_cek_username = mysqli_query($koneksi, $query_cek_username);

    if (mysqli_num_rows($result_cek_username) > 0) {
        $pesan_error = "Username '$username' sudah digunakan. Silakan pilih username lain.";
    } else {
        // Logika update password
        if (!empty($password_baru)) {
            // Jika password baru diisi, hash password tersebut
            $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            $query_update = "UPDATE tabel_pengguna SET nama_lengkap = '$nama_lengkap', username = '$username', level = '$level', password = '$password_hashed' WHERE id_pengguna = $id_pengguna_edit";
        } else {
            // Jika password tidak diisi, jangan update kolom password
            $query_update = "UPDATE tabel_pengguna SET nama_lengkap = '$nama_lengkap', username = '$username', level = '$level' WHERE id_pengguna = $id_pengguna_edit";
        }

        if (mysqli_query($koneksi, $query_update)) {
            $_SESSION['pesan_sukses'] = "Data pengguna berhasil diperbarui.";
            redirect('pengguna.php');
        } else {
            $pesan_error = "Gagal memperbarui data pengguna.";
        }
    }
}

?>

<!-- STRUKTUR LAYOUT BARU MENGGUNAKAN FLEXBOX -->
<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>

    <!-- Wrapper untuk konten utama -->
    <div class="flex-grow-1 p-4">
        <main>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Pengguna</h1>
            </div>

            <?php if ($pesan_error): ?>
                <div class="alert alert-danger"><?php echo $pesan_error; ?></div>
            <?php endif; ?>

            <?php if ($pengguna): ?>
            <div class="card">
                <div class="card-body">
                    <form action="edit_pengguna.php?id=<?php echo $id_pengguna_edit; ?>" method="POST">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($pengguna['nama_lengkap']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($pengguna['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="level" class="form-label">Level</label>
                            <select class="form-select" id="level" name="level" required>
                                <option value="admin" <?php echo ($pengguna['level'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="petugas" <?php echo ($pengguna['level'] == 'petugas') ? 'selected' : ''; ?>>Petugas</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru (Opsional)</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                        </div>
                        <button type="submit" name="update_pengguna" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="pengguna.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
            <?php else: ?>
                <p>Data pengguna tidak dapat ditemukan.</p>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>

