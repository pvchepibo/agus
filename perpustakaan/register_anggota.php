<?php
require_once 'config.php';

$pesan_sukses = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil semua data dari form
    $nim = clean_input($koneksi, $_POST['nim']);
    $nama_lengkap = clean_input($koneksi, $_POST['nama_lengkap']);
    $email = clean_input($koneksi, $_POST['email']);
    $tempat_lahir = clean_input($koneksi, $_POST['tempat_lahir']);
    $tgl_lahir = clean_input($koneksi, $_POST['tgl_lahir']);
    $jenis_kelamin = clean_input($koneksi, $_POST['jenis_kelamin']);
    $prodi = clean_input($koneksi, $_POST['prodi']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi
    if ($password !== $konfirmasi_password) {
        $pesan_error = "Password dan konfirmasi password tidak cocok.";
    } else {
        // Cek apakah NIM atau email sudah terdaftar
        $query_cek = "SELECT * FROM tabel_anggota WHERE nim = '$nim' OR email = '$email'";
        $result_cek = mysqli_query($koneksi, $query_cek);
        if (mysqli_num_rows($result_cek) > 0) {
            $pesan_error = "NIM atau Email sudah terdaftar.";
        } else {
            // Enkripsi password
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database dengan status 'Menunggu Persetujuan'
            $query_insert = "INSERT INTO tabel_anggota (nim, nama_lengkap, email, password, tempat_lahir, tgl_lahir, jenis_kelamin, prodi, status) 
                             VALUES ('$nim', '$nama_lengkap', '$email', '$password_hashed', '$tempat_lahir', '$tgl_lahir', '$jenis_kelamin', '$prodi', 'Menunggu Persetujuan')";
            
            if (mysqli_query($koneksi, $query_insert)) {
                $pesan_sukses = 'Pendaftaran berhasil! Akun Anda akan aktif setelah disetujui oleh admin. Silakan tunggu atau hubungi perpustakaan untuk verifikasi.';
            } else {
                $pesan_error = 'Terjadi kesalahan. Gagal mendaftar.';
            }
        }
    }
}

require_once 'templates/public_header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h3 class="card-title text-center mb-4">Form Pendaftaran Anggota Baru</h3>
                    
                    <?php if ($pesan_sukses): ?>
                        <div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
                    <?php endif; ?>
                    <?php if ($pesan_error): ?>
                        <div class="alert alert-danger"><?php echo $pesan_error; ?></div>
                    <?php endif; ?>

                    <form action="register_anggota.php" method="POST">
                        <div class="mb-3">
                            <label for="nim" class="form-label">NIM / No. Anggota</label>
                            <input type="text" class="form-control" id="nim" name="nim" required>
                        </div>
                         <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir">
                            </div>
                             <div class="col-md-6 mb-3">
                                <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir">
                            </div>
                        </div>
                         <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="prodi" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" id="prodi" name="prodi">
                        </div>
                        <hr class="my-4">
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Daftar</button>
                        </div>
                    </form>
                    <p class="text-center mt-3">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/public_footer.php'; ?>