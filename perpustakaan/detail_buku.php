<?php
require_once 'config.php';
// ... (logika PHP di atas tetap sama, tidak perlu diubah) ...
if (!isset($_GET['id']) || empty($_GET['id'])) { redirect('katalog.php'); } $id_buku = (int)$_GET['id']; $id_anggota = $_SESSION['id_anggota'] ?? 0;
$query = "SELECT b.*, k.nama_kategori, AVG(u.rating) as rata_rating, COUNT(u.id_ulasan) as jumlah_ulasan FROM tabel_buku b LEFT JOIN tabel_kategori k ON b.id_kategori = k.id_kategori LEFT JOIN tabel_ulasan u ON b.id_buku = u.id_buku WHERE b.id_buku = $id_buku GROUP BY b.id_buku";
$result = mysqli_query($koneksi, $query); if (mysqli_num_rows($result) == 0) { redirect('katalog.php'); } $buku = mysqli_fetch_assoc($result);
if ($_SERVER['REQUEST_METHOD'] == 'POST') { if (!isset($_SESSION['id_anggota'])) { redirect('login.php'); } if (isset($_POST['tambah_wishlist'])) { $query_wishlist = "INSERT INTO tabel_wishlist (id_buku, id_anggota) VALUES ($id_buku, $id_anggota)"; if (mysqli_query($koneksi, $query_wishlist)) { $_SESSION['pesan_sukses'] = "Buku berhasil ditambahkan ke wishlist!"; } else { $_SESSION['pesan_error'] = "Buku ini sudah ada di wishlist Anda."; } redirect('detail_buku.php?id=' . $id_buku); } if (isset($_POST['kirim_ulasan'])) { $rating = (int)$_POST['rating']; $komentar = clean_input($koneksi, $_POST['komentar']); $query_ulasan = "INSERT INTO tabel_ulasan (id_buku, id_anggota, rating, komentar) VALUES ($id_buku, $id_anggota, $rating, '$komentar') ON DUPLICATE KEY UPDATE rating=VALUES(rating), komentar=VALUES(komentar)"; if (mysqli_query($koneksi, $query_ulasan)) { $_SESSION['pesan_sukses'] = "Terima kasih atas ulasan Anda!"; } else { $_SESSION['pesan_error'] = "Gagal mengirim ulasan."; } redirect('detail_buku.php?id=' . $id_buku); } }
$ada_di_wishlist = false; if ($id_anggota > 0) { $query_cek_wishlist = "SELECT id_wishlist FROM tabel_wishlist WHERE id_buku = $id_buku AND id_anggota = $id_anggota"; $result_cek_wishlist = mysqli_query($koneksi, $query_cek_wishlist); if (mysqli_num_rows($result_cek_wishlist) > 0) { $ada_di_wishlist = true; } }
$query_get_ulasan = "SELECT u.*, a.nama_lengkap FROM tabel_ulasan u JOIN tabel_anggota a ON u.id_anggota = a.id_anggota WHERE u.id_buku = $id_buku ORDER BY u.tanggal_ulasan DESC";
$result_get_ulasan = mysqli_query($koneksi, $query_get_ulasan);
require_once 'templates/public_header.php';
?>
<div class="container my-5">
    <?php if(isset($_SESSION['pesan_sukses'])): ?><div class="alert alert-success"><?php echo $_SESSION['pesan_sukses']; unset($_SESSION['pesan_sukses']); ?></div><?php endif; ?>
    <?php if(isset($_SESSION['pesan_error'])): ?><div class="alert alert-danger"><?php echo $_SESSION['pesan_error']; unset($_SESSION['pesan_error']); ?></div><?php endif; ?>
    <div class="row">
        <div class="col-md-4 text-center">
            <?php
                // --- PERUBAHAN UTAMA DI SINI ---
                $sampul_path_detail = !empty($buku['sampul']) ? 'uploads/sampul/' . htmlspecialchars($buku['sampul']) : 'https://placehold.co/300x450/EFEFEF/333333?text=Cover';
            ?>
            <img src="<?php echo $sampul_path_detail; ?>" alt="Cover Buku <?php echo htmlspecialchars($buku['judul_buku']); ?>" class="img-fluid rounded shadow-sm mb-3" onerror="this.onerror=null;this.src='https://placehold.co/300x450/EFEFEF/333333?text=Error';">
        </div>
        <div class="col-md-8">
            <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($buku['judul_buku']); ?></h1>
            <p class="text-muted fs-5">oleh <?php echo htmlspecialchars($buku['pengarang']); ?></p>
            <div class="d-flex align-items-center mb-3"><div class="star-rating"><?php for($i = 1; $i <= 5; $i++): ?><i class="fas fa-star <?php echo ($i <= $buku['rata_rating']) ? 'text-warning' : 'text-secondary'; ?>"></i><?php endfor; ?></div><span class="ms-2 text-muted">(<?php echo number_format($buku['rata_rating'], 1); ?> dari <?php echo $buku['jumlah_ulasan']; ?> ulasan)</span></div>
            <table class="table table-borderless table-sm">
                <tr><th style="width: 150px;">Kategori</th><td>: <span class="badge bg-info"><?php echo htmlspecialchars($buku['nama_kategori'] ?: 'Umum'); ?></span></td></tr>
                <tr><th>Penerbit</th><td>: <?php echo htmlspecialchars($buku['penerbit']); ?></td></tr>
                <tr><th>Tahun Terbit</th><td>: <?php echo htmlspecialchars($buku['tahun_terbit']); ?></td></tr>
                <tr><th>ISBN</th><td>: <?php echo htmlspecialchars($buku['isbn']); ?></td></tr>
                <tr><th>Stok Tersedia</th><td>: <strong><?php echo $buku['jumlah_buku']; ?></strong></td></tr>
            </table>
            <h4 class="mt-4">Sinopsis</h4><p><?php echo nl2br(htmlspecialchars($buku['sinopsis'] ?: 'Sinopsis belum tersedia.')); ?></p>
            <div class="mt-4 d-flex align-items-center gap-2">
                <?php if ($buku['jumlah_buku'] > 0): ?><a href="katalog.php?pinjam=<?php echo $id_buku; ?>" class="btn btn-primary btn-lg"><i class="fas fa-hand-holding-heart me-2"></i> Pinjam</a><?php else: ?><button class="btn btn-secondary btn-lg" disabled>Stok Habis</button><?php endif; ?>
                <form method="POST" action="" class="d-inline-block"><?php if (isset($_SESSION['id_anggota'])): ?><?php if ($ada_di_wishlist): ?><button class="btn btn-success btn-lg" disabled><i class="fas fa-check me-2"></i> Di Wishlist</button><?php else: ?><button type="submit" name="tambah_wishlist" class="btn btn-outline-secondary btn-lg"><i class="fas fa-heart me-2"></i> Wishlist</button><?php endif; ?><?php endif; ?></form>
                <a href="katalog.php" class="btn btn-outline-dark btn-lg ms-auto"><i class="fas fa-arrow-left me-2"></i> Kembali</a>
            </div>
        </div>
    </div>
    <!-- Bagian Ulasan -->
    <hr class="my-5"><div class="row"><div class="col-lg-8 mx-auto"><h3 class="mb-4">Ulasan Buku</h3><?php if (isset($_SESSION['id_anggota'])): ?><div class="card mb-4"><div class="card-body"><h5 class="card-title">Tulis Ulasan Anda</h5><form action="detail_buku.php?id=<?php echo $id_buku; ?>" method="POST"><div class="mb-3"><label for="rating" class="form-label">Beri Peringkat:</label><div class="star-rating-input"><?php for($i = 5; $i >= 1; $i--): ?><input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required/><label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars"><i class="fas fa-star"></i></label><?php endfor; ?></div></div><div class="mb-3"><label for="komentar" class="form-label">Komentar</label><textarea name="komentar" id="komentar" class="form-control" rows="3"></textarea></div><button type="submit" name="kirim_ulasan" class="btn btn-primary">Kirim</button></form></div></div><?php endif; ?><?php if ($result_get_ulasan && mysqli_num_rows($result_get_ulasan) > 0): ?><?php while($ulasan = mysqli_fetch_assoc($result_get_ulasan)): ?><div class="d-flex mb-4"><div class="flex-shrink-0"><img src="https://placehold.co/64x64/0d6efd/white?text=<?php echo strtoupper(substr($ulasan['nama_lengkap'], 0, 1)); ?>" class="rounded-circle" alt="User"></div><div class="ms-3"><h5 class="mt-0"><?php echo htmlspecialchars($ulasan['nama_lengkap']); ?></h5><div class="star-rating mb-2"><?php for($i = 1; $i <= 5; $i++): ?><i class="fas fa-star <?php echo ($i <= $ulasan['rating']) ? 'text-warning' : 'text-secondary'; ?>"></i><?php endfor; ?></div><p><?php echo nl2br(htmlspecialchars($ulasan['komentar'])); ?></p><small class="text-muted">Diulas pada <?php echo date('d F Y', strtotime($ulasan['tanggal_ulasan'])); ?></small></div></div><?php endwhile; ?><?php else: ?><p>Belum ada ulasan untuk buku ini.</p><?php endif; ?></div></div>
</div>
<?php require_once 'templates/public_footer.php'; ?>