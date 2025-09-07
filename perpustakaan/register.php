<?php
// Sertakan file konfigurasi
require_once 'config.php';

// Jika pengguna sudah login, arahkan ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    redirect('dashboard.php');
}

$error_message = '';
$success_message = '';

// Cek jika form telah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Amankan input dari form
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    $level = clean_input($_POST['level']);

    // Validasi dasar
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($level)) {
        $error_message = 'Semua kolom wajib diisi!';
    } else {
        // Cek apakah username sudah ada
        $query_cek = "SELECT username FROM tabel_pengguna WHERE username = ?";
        $stmt_cek = mysqli_prepare($koneksi, $query_cek);
        mysqli_stmt_bind_param($stmt_cek, "s", $username);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($result_cek) > 0) {
            $error_message = 'Username sudah digunakan, silakan pilih yang lain!';
        } else {
            // Username tersedia, lanjutkan registrasi
            // Enkripsi password sebelum disimpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Query untuk memasukkan pengguna baru
            $query_insert = "INSERT INTO tabel_pengguna (nama_lengkap, username, password, level) VALUES (?, ?, ?, ?)";
            
            $stmt_insert = mysqli_prepare($koneksi, $query_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssss", $nama_lengkap, $username, $hashed_password, $level);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $success_message = 'Registrasi berhasil! Silakan login.';
            } else {
                $error_message = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <h3 class="card-title text-center mb-4">Registrasi Akun Baru</h3>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <form action="register.php" method="POST">
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
                                <option value="" disabled selected>-- Pilih Level --</option>
                                <option value="admin">Admin</option>
                                <option value="petugas">Petugas</option>
                            </select>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Daftar</button>
                        </div>
                        <div class="text-center">
                            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
