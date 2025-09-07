<?php
require_once 'config.php';
// Autentikasi hanya untuk admin dan petugas
if (!isset($_SESSION['id_pengguna'])) {
    redirect('login.php');
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('anggota.php');
}
$id_anggota = (int)$_GET['id'];

$pesan_sukses = '';
$pesan_error = '';

// --- LOGIKA UPDATE ANGGOTA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_anggota'])) {
    $nim = clean_input($koneksi, $_POST['nim']);
    $nama_lengkap = clean_input($koneksi, $_POST['nama_lengkap']);
    $email = clean_input($koneksi, $_POST['email']);
    $tempat_lahir = clean_input($koneksi, $_POST['tempat_lahir']);
    $tgl_lahir = clean_input($koneksi, $_POST['tgl_lahir']);
    $jenis_kelamin = clean_input($koneksi, $_POST['jenis_kelamin']);
    $prodi = clean_input($koneksi, $_POST['prodi']);
    $status = clean_input($koneksi, $_POST['status']); // Ambil data status baru
    $password_baru = $_POST['password'];

    $query_update = "UPDATE tabel_anggota SET 
                        nim = '$nim', 
                        nama_lengkap = '$nama_lengkap', 
                        email = '$email',
                        tempat_lahir = '$tempat_lahir',
                        tgl_lahir = '$tgl_lahir',
                        jenis_kelamin = '$jenis_kelamin',
                        prodi = '$prodi',
                        status = '$status' "; // Tambahkan status ke query update

    // Hanya update password jika diisi
    if (!empty($password_baru)) {
        $password_hashed = password_hash($password_baru, PASSWORD_BCRYPT);
        $query_update .= ", password = '$password_hashed' ";
    }

    $query_update .= " WHERE id_anggota = $id_anggota";

    if (mysqli_query($koneksi, $query_update)) {
        $pesan_sukses = "Data anggota berhasil diperbarui!";
    } else {
        $pesan_error = "Gagal memperbarui data. NIM atau Email mungkin sudah digunakan.";
    }
}

// Ambil data anggota yang akan diedit
$query_get = "SELECT * FROM tabel_anggota WHERE id_anggota = $id_anggota";
$result_get = mysqli_query($koneksi, $query_get);
if (mysqli_num_rows($result_get) == 0) {
    redirect('anggota.php');
}
$anggota = mysqli_fetch_assoc($result_get);

require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h3>Edit Data Anggota</h3>
        <hr>
        
        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="alert alert-danger"><?php echo $pesan_error; ?></div>
        <?php endif; ?>

        <form action="edit_anggota.php?id=<?php echo $id_anggota; ?>" method="POST">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                             <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nim" class="form-label">NIM / No. Anggota</label>
                                        <input type="text" class="form-control" id="nim" name="nim" value="<?php echo htmlspecialchars($anggota['nim']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($anggota['nama_lengkap']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($anggota['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?php echo htmlspecialchars($anggota['tempat_lahir']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                                        <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" value="<?php echo htmlspecialchars($anggota['tgl_lahir']); ?>">
                                    </div>
                                     <div class="mb-3">
                                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                            <option value="Laki-laki" <?php if ($anggota['jenis_kelamin'] == 'Laki-laki') echo 'selected'; ?>>Laki-laki</option>
                                            <option value="Perempuan" <?php if ($anggota['jenis_kelamin'] == 'Perempuan') echo 'selected'; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="prodi" class="form-label">Program Studi / Fakultas</label>
                                <input type="text" class="form-control" id="prodi" name="prodi" value="<?php echo htmlspecialchars($anggota['prodi']); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                     <div class="card">
                        <div class="card-body">
                             <div class="mb-3">
                                <label for="status" class="form-label">Status Akun</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Aktif" <?php if ($anggota['status'] == 'Aktif') echo 'selected'; ?>>Aktif</option>
                                    <option value="Menunggu Persetujuan" <?php if ($anggota['status'] == 'Menunggu Persetujuan') echo 'selected'; ?>>Menunggu Persetujuan</option>
                                    <option value="Ditangguhkan" <?php if ($anggota['status'] == 'Ditangguhkan') echo 'selected'; ?>>Ditangguhkan</option>
                                    <option value="Dinonaktifkan" <?php if ($anggota['status'] == 'Dinonaktifkan') echo 'selected'; ?>>Dinonaktifkan</option>
                                </select>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Baru (Opsional)</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" name="edit_anggota" class="btn btn-primary">Simpan Perubahan</button>
                <a href="anggota.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>