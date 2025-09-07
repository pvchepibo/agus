<?php
require_once 'config.php';

// Cek apakah ada ID pengumuman di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('index.php');
}
$id_pengumuman = (int)$_GET['id'];

// --- PERUBAHAN DI SINI: Query sekarang juga mengambil 'level' pengguna ---
$query = "SELECT p.*, u.level as level_penulis 
          FROM tabel_pengumuman p
          JOIN tabel_pengguna u ON p.id_pengguna = u.id_pengguna
          WHERE p.id_pengumuman = $id_pengumuman";

$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    // Jika pengumuman tidak ditemukan, kembalikan ke halaman utama
    redirect('index.php');
}

$pengumuman = mysqli_fetch_assoc($result);

require_once 'templates/public_header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="pengumuman-detail-container">
                <!-- Judul Pengumuman -->
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($pengumuman['judul_pengumuman']); ?></h1>

                <!-- --- PERUBAHAN DI SINI: Menampilkan Level, bukan Nama --- -->
                <p class="text-muted border-bottom pb-3 mb-4">
                    <i class="fas fa-user-shield me-2"></i>Diposting oleh: <strong><?php echo ucfirst(htmlspecialchars($pengumuman['level_penulis'])); ?></strong>
                    <span class="mx-2">|</span>
                    <i class="fas fa-calendar-alt me-2"></i><?php echo date('d F Y, H:i', strtotime($pengumuman['tanggal_posting'])); ?>
                </p>
                <!-- ======================================================= -->

                <!-- Isi Pengumuman -->
                <div class="isi-pengumuman">
                    <?php echo nl2br(htmlspecialchars($pengumuman['isi_pengumuman'])); ?>
                </div>

                <!-- Tombol Kembali -->
                <div class="mt-5">
                    <a href="index.php#pengumuman" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/public_footer.php'; ?>