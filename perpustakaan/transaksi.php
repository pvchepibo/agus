<?php
require_once 'config.php';
// Autentikasi hanya untuk admin dan petugas
if (!isset($_SESSION['id_pengguna'])) {
    redirect('login.php');
}

$pesan_sukses = '';
$pesan_error = '';

// Ambil pengaturan denda
$query_pengaturan = "SELECT denda_per_hari FROM tabel_pengaturan WHERE id_setting = 1";
$result_pengaturan = mysqli_query($koneksi, $query_pengaturan);
$pengaturan = mysqli_fetch_assoc($result_pengaturan);
$denda_per_hari = $pengaturan ? (int)$pengaturan['denda_per_hari'] : 0;

// --- LOGIKA PROSES PENGEMBALIAN ---
if (isset($_GET['kembalikan'])) {
    $id_transaksi = (int)$_GET['kembalikan'];
    $query_get_transaksi = "SELECT * FROM tabel_transaksi WHERE id_transaksi = $id_transaksi";
    $result_get_transaksi = mysqli_query($koneksi, $query_get_transaksi);
    $transaksi = mysqli_fetch_assoc($result_get_transaksi);
    $id_buku = $transaksi['id_buku'];
    
    $tgl_sekarang = new DateTime();
    $tgl_kembali = new DateTime($transaksi['tgl_kembali']);
    $denda = 0;
    if ($tgl_sekarang > $tgl_kembali) {
        $selisih_hari = $tgl_sekarang->diff($tgl_kembali)->days;
        $denda = $selisih_hari * $denda_per_hari;
    }

    mysqli_begin_transaction($koneksi);
    try {
        $query_kembalikan = "UPDATE tabel_transaksi SET status = 'Dikembalikan', tgl_dikembalikan = CURDATE(), denda = $denda WHERE id_transaksi = $id_transaksi";
        mysqli_query($koneksi, $query_kembalikan);
        $query_update_stok = "UPDATE tabel_buku SET jumlah_buku = jumlah_buku + 1 WHERE id_buku = $id_buku";
        mysqli_query($koneksi, $query_update_stok);
        mysqli_commit($koneksi);
        $_SESSION['pesan_sukses_transaksi'] = "Buku berhasil dikembalikan. Denda: Rp " . number_format($denda);
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($koneksi);
        $_SESSION['pesan_error_transaksi'] = "Gagal memproses pengembalian.";
    }
    redirect('transaksi.php');
}

// --- LOGIKA BARU: PERPANJANGAN PEMINJAMAN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['perpanjang_peminjaman'])) {
    $id_transaksi = (int)$_POST['id_transaksi'];
    $jumlah_hari = (int)$_POST['jumlah_hari'];

    // Validasi jumlah hari
    if ($jumlah_hari >= 1 && $jumlah_hari <= 7) {
        // Ambil tanggal kembali saat ini
        $query_get_tgl = "SELECT tgl_kembali FROM tabel_transaksi WHERE id_transaksi = $id_transaksi";
        $result_get_tgl = mysqli_query($koneksi, $query_get_tgl);
        $tgl_kembali_lama = mysqli_fetch_assoc($result_get_tgl)['tgl_kembali'];

        // Hitung tanggal kembali baru
        $tgl_kembali_baru = date('Y-m-d', strtotime($tgl_kembali_lama . " +$jumlah_hari days"));

        // Update ke database
        $query_perpanjang = "UPDATE tabel_transaksi SET tgl_kembali = '$tgl_kembali_baru' WHERE id_transaksi = $id_transaksi";
        if(mysqli_query($koneksi, $query_perpanjang)) {
            $_SESSION['pesan_sukses_transaksi'] = "Peminjaman berhasil diperpanjang selama $jumlah_hari hari.";
        } else {
            $_SESSION['pesan_error_transaksi'] = "Gagal memperpanjang peminjaman.";
        }
    } else {
         $_SESSION['pesan_error_transaksi'] = "Jumlah hari perpanjangan tidak valid.";
    }
    redirect('transaksi.php');
}

// --- LOGIKA PROSES PEMINJAMAN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_transaksi'])) {
    $id_anggota = (int)$_POST['id_anggota'];
    $id_buku = (int)$_POST['id_buku'];
    $query_stok = "SELECT jumlah_buku FROM tabel_buku WHERE id_buku = $id_buku";
    $result_stok = mysqli_query($koneksi, $query_stok);
    $stok = mysqli_fetch_assoc($result_stok)['jumlah_buku'];

    if ($stok > 0) {
        mysqli_begin_transaction($koneksi);
        try {
            $tgl_pinjam = date('Y-m-d');
            $tgl_kembali = date('Y-m-d', strtotime('+7 days'));
            $query_pinjam = "INSERT INTO tabel_transaksi (id_buku, id_anggota, tgl_pinjam, tgl_kembali, status, denda) 
                             VALUES ($id_buku, $id_anggota, '$tgl_pinjam', '$tgl_kembali', 'Dipinjam', 0)";
            mysqli_query($koneksi, $query_pinjam);
            $query_update_stok = "UPDATE tabel_buku SET jumlah_buku = jumlah_buku - 1 WHERE id_buku = $id_buku";
            mysqli_query($koneksi, $query_update_stok);
            mysqli_commit($koneksi);
            $_SESSION['pesan_sukses_transaksi'] = "Peminjaman buku berhasil dicatat.";
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($koneksi);
            $_SESSION['pesan_error_transaksi'] = "Gagal memproses peminjaman.";
        }
    } else {
        $_SESSION['pesan_error_transaksi'] = "Stok buku habis, peminjaman gagal.";
    }
    redirect('transaksi.php');
}

// Cek pesan dari session
if(isset($_SESSION['pesan_sukses_transaksi'])) {
    $pesan_sukses = $_SESSION['pesan_sukses_transaksi'];
    unset($_SESSION['pesan_sukses_transaksi']);
}
if(isset($_SESSION['pesan_error_transaksi'])) {
    $pesan_error = $_SESSION['pesan_error_transaksi'];
    unset($_SESSION['pesan_error_transaksi']);
}

// Ambil data untuk form dan tabel
$anggota_list = mysqli_query($koneksi, "SELECT * FROM tabel_anggota WHERE status = 'Aktif' ORDER BY nama_lengkap");
$buku_list = mysqli_query($koneksi, "SELECT * FROM tabel_buku WHERE jumlah_buku > 0 ORDER BY judul_buku");
$transaksi_berlangsung = mysqli_query($koneksi, "
    SELECT tt.*, ta.nama_lengkap, tb.judul_buku 
    FROM tabel_transaksi tt
    JOIN tabel_anggota ta ON tt.id_anggota = ta.id_anggota
    JOIN tabel_buku tb ON tt.id_buku = tb.id_buku
    WHERE tt.status = 'Dipinjam'
    ORDER BY tt.tgl_pinjam DESC
");

require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h3>Transaksi Peminjaman & Pengembalian</h3>
        <hr>
        <?php if ($pesan_sukses): ?><div class="alert alert-success"><?php echo $pesan_sukses; ?></div><?php endif; ?>
        <?php if ($pesan_error): ?><div class="alert alert-danger"><?php echo $pesan_error; ?></div><?php endif; ?>

        <!-- Form Peminjaman Baru -->
        <div class="card mb-4">
            <div class="card-header">Form Peminjaman Baru</div>
            <div class="card-body">
                <form action="transaksi.php" method="POST">
                    <div class="mb-3">
                        <label for="id_anggota" class="form-label">Pilih Anggota</label>
                        <select class="form-select" id="id_anggota" name="id_anggota" required>
                            <option value="">-- Pilih Anggota --</option>
                            <?php while($anggota = mysqli_fetch_assoc($anggota_list)): ?>
                                <option value="<?php echo $anggota['id_anggota']; ?>"><?php echo htmlspecialchars($anggota['nama_lengkap']) . ' (' . htmlspecialchars($anggota['nim']) . ')'; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="id_buku" class="form-label">Pilih Buku (Hanya yang Tersedia)</label>
                        <select class="form-select" id="id_buku" name="id_buku" required>
                            <option value="">-- Pilih Judul Buku --</option>
                            <?php while($buku = mysqli_fetch_assoc($buku_list)): ?>
                                <option value="<?php echo $buku['id_buku']; ?>"><?php echo htmlspecialchars($buku['judul_buku']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="simpan_transaksi" class="btn btn-primary">Simpan Transaksi</button>
                </form>
            </div>
        </div>

        <!-- Tabel Peminjaman Berlangsung -->
        <div class="card">
            <div class="card-header">Daftar Buku yang Sedang Dipinjam</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Peminjam</th>
                                <th>Judul Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php if (mysqli_num_rows($transaksi_berlangsung) > 0): ?>
                                <?php $no = 1; ?>
                                <?php while ($transaksi = mysqli_fetch_assoc($transaksi_berlangsung)): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($transaksi['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($transaksi['judul_buku']); ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($transaksi['tgl_pinjam'])); ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($transaksi['tgl_kembali'])); ?></td>
                                        <td>
                                            <a href="transaksi.php?kembalikan=<?php echo $transaksi['id_transaksi']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Proses pengembalian buku ini?')">Kembalikan</a>
                                            <!-- TOMBOL BARU UNTUK PERPANJANGAN -->
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#perpanjangModal" data-id="<?php echo $transaksi['id_transaksi']; ?>">Perpanjang</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Tidak ada buku yang sedang dipinjam.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL BARU UNTUK PERPANJANGAN -->
<div class="modal fade" id="perpanjangModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perpanjang Masa Peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="transaksi.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_transaksi" id="perpanjang_id_transaksi">
                    <div class="mb-3">
                        <label for="jumlah_hari" class="form-label">Tambah Durasi Pinjam (1-7 hari)</label>
                        <input type="number" class="form-control" id="jumlah_hari" name="jumlah_hari" required min="1" max="7" value="7">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="perpanjang_peminjaman" class="btn btn-primary">Simpan Perpanjangan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script untuk mengisi modal perpanjangan secara dinamis
document.addEventListener('DOMContentLoaded', function () {
    var perpanjangModal = document.getElementById('perpanjangModal');
    perpanjangModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var modalInputId = perpanjangModal.querySelector('#perpanjang_id_transaksi');
        modalInputId.value = id;
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>