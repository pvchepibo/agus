<?php
// SEMUA LOGIKA PHP ADA DI SINI, DI BAGIAN ATAS
require_once 'config.php';

// --- Logika Pelacak Aktivitas Online ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$session_id = session_id();
$waktu_sekarang = date('Y-m-d H:i:s');
// Cek apakah user sudah ada di tabel
$query_cek_online = "SELECT * FROM tabel_aktivitas_online WHERE session_id = '$session_id'";
$result_cek_online = mysqli_query($koneksi, $query_cek_online);
if ($result_cek_online && mysqli_num_rows($result_cek_online) > 0) {
    // Jika ada, update waktu terakhir
    $query_update_online = "UPDATE tabel_aktivitas_online SET waktu_terakhir = '$waktu_sekarang' WHERE session_id = '$session_id'";
    mysqli_query($koneksi, $query_update_online);
} else {
    // Jika tidak ada, insert data baru
    $query_insert_online = "INSERT INTO tabel_aktivitas_online (session_id, waktu_terakhir) VALUES ('$session_id', '$waktu_sekarang')";
    mysqli_query($koneksi, $query_insert_online);
}
// Hapus session yang sudah tidak aktif (lebih dari 5 menit)
$waktu_limit = date('Y-m-d H:i:s', strtotime('-5 minutes'));
$query_hapus_online = "DELETE FROM tabel_aktivitas_online WHERE waktu_terakhir < '$waktu_limit'";
mysqli_query($koneksi, $query_hapus_online);
// --- Akhir Logika Pelacak Aktivitas ---


// --- Logika Statistik untuk Kartu ---
$query_judul = "SELECT COUNT(id_buku) as total FROM tabel_buku";
$result_judul = mysqli_query($koneksi, $query_judul);
$total_judul_buku = mysqli_fetch_assoc($result_judul)['total'];

$query_anggota = "SELECT COUNT(id_anggota) as total FROM tabel_anggota";
$result_anggota = mysqli_query($koneksi, $query_anggota);
$total_anggota = mysqli_fetch_assoc($result_anggota)['total'];

$query_stok = "SELECT SUM(jumlah_buku) as total FROM tabel_buku";
$result_stok = mysqli_query($koneksi, $query_stok);
$total_stok_buku = mysqli_fetch_assoc($result_stok)['total'];
// --- Akhir Logika Statistik ---


// --- Logika Aktivitas Terbaru ---
$query_aktivitas = "
    SELECT 
        ta.nama_lengkap as nama_anggota,
        tb.judul_buku
    FROM tabel_transaksi tt
    JOIN tabel_anggota ta ON tt.id_anggota = ta.id_anggota
    JOIN tabel_buku tb ON tt.id_buku = tb.id_buku
    ORDER BY tt.id_transaksi DESC
    LIMIT 5
";
$result_aktivitas = mysqli_query($koneksi, $query_aktivitas);
// --- Akhir Logika Aktivitas ---


// --- LOGIKA AMBIL PENGUMUMAN TERBARU ---
$query_pengumuman = "SELECT * FROM tabel_pengumuman ORDER BY tanggal_posting DESC LIMIT 3";
$result_pengumuman = mysqli_query($koneksi, $query_pengumuman);
// ---------------------------------------------


// TAMPILAN (HTML) DIMULAI DI SINI
require_once 'templates/public_header.php'; 
?>

<!-- Hero Section -->
<div class="hero-section text-center text-white">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <h1 class="display-3 fw-bold" data-aos="fade-up">Temukan Duniamu dalam Kata</h1>
        <p class="lead col-lg-8 mx-auto" data-aos="fade-up" data-aos-delay="200">
            Jelajahi ribuan koleksi buku dari berbagai genre. Baca, pinjam, dan perluas wawasanmu bersama kami.
        </p>
        <a href="katalog.php" class="btn btn-primary btn-lg mt-3" data-aos="fade-up" data-aos-delay="400">
            <i class="fas fa-search me-2"></i> Jelajahi Katalog
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="container my-5">
    <!-- Kartu Statistik -->
    <div class="row text-center stats-section">
        <div class="col-md-4 mb-4" data-aos="fade-up"><div class="card h-100 shadow-sm"><div class="card-body"><i class="fas fa-book fa-3x text-primary mb-3"></i><h3 class="card-title"><?php echo number_format($total_judul_buku); ?></h3><p class="card-text text-muted">Total Judul Buku</p></div></div></div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200"><div class="card h-100 shadow-sm"><div class="card-body"><i class="fas fa-users fa-3x text-success mb-3"></i><h3 class="card-title"><?php echo number_format($total_anggota); ?></h3><p class="card-text text-muted">Anggota Terdaftar</p></div></div></div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400"><div class="card h-100 shadow-sm"><div class="card-body"><i class="fas fa-layer-group fa-3x text-info mb-3"></i><h3 class="card-title"><?php echo number_format($total_stok_buku); ?></h3><p class="card-text text-muted">Total Stok Buku</p></div></div></div>
    </div>
    
    <!-- Bagian Pengumuman (DIPERBARUI) -->
    <?php if ($result_pengumuman && mysqli_num_rows($result_pengumuman) > 0): ?>
    <section id="pengumuman" class="py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Pengumuman & Acara Terbaru</h2>
            <div class="row">
                <?php while($pengumuman = mysqli_fetch_assoc($result_pengumuman)): ?>
                <div class="col-lg-4 mb-4" data-aos="fade-up">
                    <div class="card announcement-card h-100 d-flex flex-column">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($pengumuman['judul_pengumuman']); ?></h5>
                            <p class="card-subtitle mb-2 text-muted"><small>Diposting pada <?php echo date('d F Y', strtotime($pengumuman['tanggal_posting'])); ?></small></p>
                            <p class="card-text">
                                <?php 
                                    // --- PERUBAHAN UTAMA DI SINI ---
                                    $isi_lengkap = htmlspecialchars($pengumuman['isi_pengumuman']);
                                    $limit_karakter = 50; // Batasi menjadi 100 karakter
                                    $cuplikan = $isi_lengkap; 

                                    if (strlen($isi_lengkap) > $limit_karakter) {
                                        $pos = strrpos(substr($isi_lengkap, 0, $limit_karakter), ' ');
                                        if ($pos !== false) {
                                            $cuplikan = substr($isi_lengkap, 0, $pos) . '...';
                                        } else {
                                            $cuplikan = substr($isi_lengkap, 0, $limit_karakter) . '...';
                                        }
                                    }
                                    
                                    echo nl2br($cuplikan); 
                                ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 mt-auto">
                             <a href="pengumuman_detail.php?id=<?php echo $pengumuman['id_pengumuman']; ?>" class="btn btn-outline-primary btn-sm">Lihat Selengkapnya</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
     <!-- Bagian Tentang Kami -->
     <section id="tentang-kami" class="py-5 my-5 bg-light rounded shadow-sm" data-aos="fade-up"><div class="container"><div class="row align-items-center"><div class="col-lg-6"><h2 class="display-5 fw-bold mb-4">Selamat Datang di Perpustakaan Daerah Manokwari</h2><p class="lead">Kami adalah pusat informasi, pengetahuan, dan layanan pustaka bagi masyarakat yang dikelola oleh Dinas Perpustakaan dan Kearsipan (Disarpus) Kabupaten Manokwari.</p><p>Dengan koleksi ribuan buku dan layanan internet gratis, kami berkomitmen untuk meningkatkan literasi dan menyediakan akses pengetahuan yang luas bagi semua kalangan. Kami juga bekerja sama dengan berbagai perpustakaan mitra untuk menjangkau lebih banyak pembaca.</p><ul class="list-unstyled mt-4"><li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Koleksi Lengkap:</strong> Lebih dari 9.000 buku dengan 5.000 judul.</li><li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Internet Gratis:</strong> Akses internet tanpa batas untuk semua pengunjung.</li><li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong>Perpustakaan Mitra:</strong> Jaringan luas dengan Perpustakaan Rumah Noken dan lainnya.</li></ul></div><div class="col-lg-6 text-center"><img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=1470&auto=format&fit=crop" class="img-fluid rounded shadow" alt="Interior Perpustakaan yang nyaman"></div></div></div></section>
    
    <!-- Bagian Aktivitas & Pengguna Online -->
    <div class="row mt-5"><div class="col-lg-6 mb-4" data-aos="fade-right"><div class="card h-100"><div class="card-header bg-dark text-white"><h4 class="mb-0"><i class="fas fa-history me-2"></i> Aktivitas Terbaru</h4></div><div class="card-body"><ul class="list-group list-group-flush"><?php if ($result_aktivitas && mysqli_num_rows($result_aktivitas) > 0): ?><?php while($aktivitas = mysqli_fetch_assoc($result_aktivitas)): ?><li class="list-group-item"><i class="fas fa-book-reader text-muted me-2"></i><strong><?php echo htmlspecialchars($aktivitas['nama_anggota']); ?></strong> baru saja meminjam buku <em>"<?php echo htmlspecialchars($aktivitas['judul_buku']); ?>"</em>.</li><?php endwhile; ?><?php else: ?><li class="list-group-item">Belum ada aktivitas terbaru.</li><?php endif; ?></ul></div></div></div><div class="col-lg-6 mb-4" data-aos="fade-left"><div class="card h-100"><div class="card-header bg-dark text-white"><h4 class="mb-0"><i class="fas fa-chart-line me-2"></i> Pengguna Online</h4></div><div class="card-body"><canvas id="grafikPenggunaOnline"></canvas></div></div></div></div>
</div>

<?php require_once 'templates/public_footer.php'; ?>