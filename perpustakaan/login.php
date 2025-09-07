<?php
require_once 'config.php';

// Jika sudah login, langsung arahkan ke halaman yang sesuai
if (isset($_SESSION['id_pengguna'])) {
    redirect('dashboard.php'); // Arahkan admin/petugas ke dashboard
}
if (isset($_SESSION['id_anggota'])) {
    redirect('index.php'); // Arahkan anggota ke halaman utama
}

$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = clean_input($koneksi, $_POST['identifier']); // Bisa username, nim, atau email
    $password = $_POST['password'];

    // Cek di tabel pengguna (admin/petugas)
    $query_pengguna = "SELECT * FROM tabel_pengguna WHERE username = '$identifier'";
    $result_pengguna = mysqli_query($koneksi, $query_pengguna);

    if ($result_pengguna && mysqli_num_rows($result_pengguna) == 1) {
        $pengguna = mysqli_fetch_assoc($result_pengguna);
        if (password_verify($password, $pengguna['password'])) {
            $_SESSION['id_pengguna'] = $pengguna['id_pengguna'];
            $_SESSION['nama_lengkap'] = $pengguna['nama_lengkap'];
            $_SESSION['level'] = $pengguna['level'];
            redirect('dashboard.php');
        } else {
            $pesan_error = "Username atau password salah.";
        }
    } else {
        // Jika tidak ditemukan di pengguna, cek di tabel anggota
        $query_anggota = "SELECT * FROM tabel_anggota WHERE (nim = '$identifier' OR email = '$identifier')";
        $result_anggota = mysqli_query($koneksi, $query_anggota);

        if ($result_anggota && mysqli_num_rows($result_anggota) == 1) {
            $anggota = mysqli_fetch_assoc($result_anggota);

            // PERUBAHAN UTAMA: Cek status akun anggota
            if ($anggota['status'] === 'Ditangguhkan') {
                $pesan_error = "Akun Anda telah ditangguhkan. Silakan hubungi petugas perpustakaan.";
            }
            elseif($anggota['status'] === 'Dinonaktifkan') {
                $pesan_error = "Anda Melakukan Pelangaran. Silakan hubungi petugas perpustakaan untuk mengaktifkan akun anda.";
            } 
            elseif ($anggota['status'] === 'Menunggu Persetujuan') {
                 $pesan_error = "Akun Anda belum aktif. Harap tunggu persetujuan dari admin.";
            } elseif (password_verify($password, $anggota['password'])) {
                $_SESSION['id_anggota'] = $anggota['id_anggota'];
                $_SESSION['nama_lengkap'] = $anggota['nama_lengkap'];
                $_SESSION['level'] = 'anggota';
                redirect('index.php');
            } else {
                $pesan_error = "No.Anggota/Email atau password salah.";
            }
        } else {
            $pesan_error = "Akun tidak ditemukan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan Daerah Manokwari</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card login-card shadow-lg" style="width: 24rem;">
            <div class="card-body p-5">
                <h3 class="card-title text-center mb-4">Perpustakaan Daerah Manokwari</h3>
                
                <?php if ($pesan_error): ?>
                    <div class="alert alert-danger py-2"><?php echo $pesan_error; ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="identifier" class="form-label">Username / No. Anggota / Email</label>
                        <input type="text" class="form-control" id="identifier" name="identifier" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-1">Belum punya akun? <a href="register_anggota.php">Daftar sebagai anggota</a></p>
                    <small><a href="index.php">Kembali ke Beranda</a></small>
                </div>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const icon = togglePassword.querySelector('i');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>