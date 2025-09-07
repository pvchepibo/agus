<?php
require_once 'config.php';
// Halaman ini hanya untuk anggota yang sudah login
if (!isset($_SESSION['id_anggota'])) {
    redirect('login.php');
}
$id_anggota = $_SESSION['id_anggota'];

// Logika Hapus Wishlist
if(isset($_GET['hapus_wishlist'])) {
    $id_wishlist = (int)$_GET['hapus_wishlist'];
    $query_hapus = "DELETE FROM tabel_wishlist WHERE id_wishlist = $id_wishlist AND id_anggota = $id_anggota";
    mysqli_query($koneksi, $query_hapus);
    redirect('profil.php#wishlist');
}

// Ambil data profil anggota
$query_profil = "SELECT * FROM tabel_anggota WHERE id_anggota = $id_anggota";
$result_profil = mysqli_query($koneksi, $query_profil);
$profil = mysqli_fetch_assoc($result_profil);

// Ambil riwayat peminjaman
$query_riwayat = "SELECT tt.*, tb.judul_buku FROM tabel_transaksi tt JOIN tabel_buku tb ON tt.id_buku = tb.id_buku WHERE tt.id_anggota = $id_anggota ORDER BY tt.tgl_pinjam DESC";
$result_riwayat = mysqli_query($koneksi, $query_riwayat);

// Ambil data buku yang sedang dipinjam
$query_dipinjam = "SELECT judul_buku, tgl_kembali FROM tabel_transaksi JOIN tabel_buku ON tabel_transaksi.id_buku = tabel_buku.id_buku WHERE id_anggota = $id_anggota AND status = 'Dipinjam'";
$result_dipinjam = mysqli_query($koneksi, $query_dipinjam);

// Ambil data wishlist
$query_wishlist = "SELECT w.id_wishlist, b.id_buku, b.judul_buku, b.pengarang FROM tabel_wishlist w JOIN tabel_buku b ON w.id_buku = b.id_buku WHERE w.id_anggota = $id_anggota ORDER BY w.tanggal_ditambahkan DESC";
$result_wishlist = mysqli_query($koneksi, $query_wishlist);

require_once 'templates/public_header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-body text-center">
                    <img src="https://placehold.co/150x150/0d6efd/white?text=<?php echo strtoupper(substr($profil['nama_lengkap'], 0, 1)); ?>" class="rounded-circle mb-3" alt="Foto Profil">
                    <h4 class="card-title"><?php echo htmlspecialchars($profil['nama_lengkap']); ?></h4>
                    <p class="text-muted mb-1"><?php echo htmlspecialchars($profil['nim']); ?></p>
                    <p class="text-muted"><?php echo htmlspecialchars($profil['email']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <!-- PENGINGAT KETERLAMBATAN -->
            <?php 
            $tgl_sekarang = new DateTime();
            while($buku_dipinjam = mysqli_fetch_assoc($result_dipinjam)):
                $tgl_kembali = new DateTime($buku_dipinjam['tgl_kembali']);
                if($tgl_sekarang > $tgl_kembali):
            ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><strong>Peringatan:</strong> Buku "<?php echo htmlspecialchars($buku_dipinjam['judul_buku']); ?>" sudah melewati batas waktu pengembalian.</div>
            <?php 
                endif;
            endwhile;
            ?>

            <!-- Navigasi Tab -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat" type="button">Riwayat Peminjaman</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" id="wishlist-tab" data-bs-toggle="tab" data-bs-target="#wishlist" type="button">Wishlist</button></li>
            </ul>

            <!-- Konten Tab -->
            <div class="tab-content" id="myTabContent">
                <!-- Tab Riwayat Peminjaman -->
                <div class="tab-pane fade show active" id="riwayat" role="tabpanel">
                    <div class="card card-tab">
                        <div class="card-body">
                            <div class="table-responsive">
                                <!-- Tabel Riwayat -->
                                <table class="table table-hover">
                                    <thead><tr><th>Judul Buku</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Denda (Rp)</th></tr></thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result_riwayat) > 0): ?>
                                            <?php while($riwayat = mysqli_fetch_assoc($result_riwayat)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($riwayat['judul_buku']); ?></td>
                                                    <td><?php echo date('d-m-Y', strtotime($riwayat['tgl_pinjam'])); ?></td>
                                                    <td><?php echo date('d-m-Y', strtotime($riwayat['tgl_kembali'])); ?></td>
                                                    <td>
                                                        <?php $status_class = $riwayat['status'] == 'Dipinjam' ? 'bg-warning text-dark' : 'bg-success';
                                                        echo '<span class="badge ' . $status_class . '">' . htmlspecialchars($riwayat['status']) . '</span>';?>
                                                    </td>
                                                    <td><?php echo number_format($riwayat['denda']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center">Anda belum pernah meminjam buku.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Wishlist -->
                <div class="tab-pane fade" id="wishlist" role="tabpanel">
                    <div class="card card-tab">
                        <div class="card-body">
                             <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead><tr><th>Judul Buku</th><th>Pengarang</th><th>Aksi</th></tr></thead>
                                    <tbody>
                                        <?php if ($result_wishlist && mysqli_num_rows($result_wishlist) > 0): ?>
                                            <?php while($item = mysqli_fetch_assoc($result_wishlist)): ?>
                                                <tr>
                                                    <td><a href="detail_buku.php?id=<?php echo $item['id_buku']; ?>"><?php echo htmlspecialchars($item['judul_buku']); ?></a></td>
                                                    <td><?php echo htmlspecialchars($item['pengarang']); ?></td>
                                                    <td><a href="profil.php?hapus_wishlist=<?php echo $item['id_wishlist']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus dari wishlist?')"><i class="fas fa-trash"></i></a></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center">Wishlist Anda masih kosong.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/public_footer.php'; ?>