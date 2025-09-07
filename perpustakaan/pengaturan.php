<?php
require_once 'config.php';
// Autentikasi hanya untuk admin
if (!isset($_SESSION['id_pengguna']) || $_SESSION['level'] !== 'admin') {
    redirect('dashboard.php');
}

$pesan_sukses = '';
$pesan_error = '';

// --- LOGIKA SIMPAN PENGATURAN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_pengaturan'])) {
    $nama_perpustakaan = clean_input($koneksi, $_POST['nama_perpustakaan']);
    $alamat = clean_input($koneksi, $_POST['alamat']);
    $email = clean_input($koneksi, $_POST['email']);
    $no_telp = clean_input($koneksi, $_POST['no_telp']);
    $denda_per_hari = (int)$_POST['denda_per_hari'];

    // Menggunakan query INSERT ... ON DUPLICATE KEY UPDATE agar lebih efisien
    // Jika id_setting 1 sudah ada, akan di-update. Jika belum, akan di-insert.
    $query_simpan = "INSERT INTO tabel_pengaturan (id_setting, nama_perpustakaan, alamat, email, no_telp, denda_per_hari) 
                     VALUES (1, '$nama_perpustakaan', '$alamat', '$email', '$no_telp', $denda_per_hari)
                     ON DUPLICATE KEY UPDATE
                        nama_perpustakaan = VALUES(nama_perpustakaan),
                        alamat = VALUES(alamat),
                        email = VALUES(email),
                        no_telp = VALUES(no_telp),
                        denda_per_hari = VALUES(denda_per_hari)";

    if (mysqli_query($koneksi, $query_simpan)) {
        $pesan_sukses = "Pengaturan berhasil disimpan!";
    } else {
        $pesan_error = "Terjadi kesalahan: " . mysqli_error($koneksi);
    }
}


// --- PERBAIKAN DIMULAI DI SINI ---

// Ambil data pengaturan yang ada
$query_pengaturan = "SELECT * FROM tabel_pengaturan WHERE id_setting = 1";
$result_pengaturan = mysqli_query($koneksi, $query_pengaturan);
$pengaturan = mysqli_fetch_assoc($result_pengaturan);

// Jika tidak ada data pengaturan, buat array kosong default untuk mengisi form
// Ini akan mencegah error "Trying to access array offset on value of type null"
if (!$pengaturan) {
    $pengaturan = [
        'nama_perpustakaan' => '',
        'alamat' => '',
        'email' => '',
        'no_telp' => '',
        'denda_per_hari' => 0
    ];
}

// --- PERBAIKAN SELESAI ---


require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h3>Pengaturan Sistem</h3>
        <hr>

        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="alert alert-danger"><?php echo $pesan_error; ?></div>
        <?php endif; ?>

        <form action="pengaturan.php" method="POST">
            <div class="row">
                <!-- Kolom Pengaturan Umum -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Pengaturan Umum
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="nama_perpustakaan" class="form-label">Nama Perpustakaan</label>
                                <input type="text" class="form-control" id="nama_perpustakaan" name="nama_perpustakaan" value="<?php echo htmlspecialchars($pengaturan['nama_perpustakaan']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($pengaturan['alamat']); ?></textarea>
                            </div>
                             <div class="mb-3">
                                <label for="email" class="form-label">Email Kontak</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($pengaturan['email']); ?>" required>
                            </div>
                             <div class="mb-3">
                                <label for="no_telp" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?php echo htmlspecialchars($pengaturan['no_telp']); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Kolom Pengaturan Sistem -->
                <div class="col-md-6">
                     <div class="card">
                        <div class="card-header">
                            Pengaturan Peminjaman
                        </div>
                        <div class="card-body">
                             <div class="mb-3">
                                <label for="denda_per_hari" class="form-label">Denda per Hari (Rp)</label>
                                <input type="number" class="form-control" id="denda_per_hari" name="denda_per_hari" value="<?php echo htmlspecialchars($pengaturan['denda_per_hari']); ?>" required min="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" name="simpan_pengaturan" class="btn btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>

