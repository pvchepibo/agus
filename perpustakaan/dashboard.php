<?php
require_once 'config.php';
// Autentikasi hanya untuk admin dan petugas
if (!isset($_SESSION['id_pengguna'])) {
    redirect('login.php');
}

// --- LOGIKA MENGAMBIL DATA STATISTIK ---

// 1. Total Judul Buku
$query_total_buku = "SELECT COUNT(id_buku) as total FROM tabel_buku";
$result_total_buku = mysqli_query($koneksi, $query_total_buku);
$total_buku = mysqli_fetch_assoc($result_total_buku)['total'];

// 2. Total Anggota
$query_total_anggota = "SELECT COUNT(id_anggota) as total FROM tabel_anggota WHERE status = 'Aktif'";
$result_total_anggota = mysqli_query($koneksi, $query_total_anggota);
$total_anggota = mysqli_fetch_assoc($result_total_anggota)['total'];

// 3. Total Buku Sedang Dipinjam
$query_dipinjam = "SELECT COUNT(id_transaksi) as total FROM tabel_transaksi WHERE status = 'Dipinjam'";
$result_dipinjam = mysqli_query($koneksi, $query_dipinjam);
$total_dipinjam = mysqli_fetch_assoc($result_dipinjam)['total'];

// 4. Ambil 5 Transaksi Terakhir
$query_transaksi_terakhir = "
    SELECT tt.*, ta.nama_lengkap, tb.judul_buku 
    FROM tabel_transaksi tt
    JOIN tabel_anggota ta ON tt.id_anggota = ta.id_anggota
    JOIN tabel_buku tb ON tt.id_buku = tb.id_buku
    ORDER BY tt.id_transaksi DESC
    LIMIT 5
";
$result_transaksi_terakhir = mysqli_query($koneksi, $query_transaksi_terakhir);


require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h3>Dashboard</h3>
        <hr>
        <p>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>! Berikut adalah ringkasan aktivitas perpustakaan.</p>

        <!-- Kartu Statistik -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title fs-2"><?php echo number_format($total_buku); ?></h5>
                            <p class="card-text">Total Judul Buku</p>
                        </div>
                        <i class="fas fa-book fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
             <div class="col-md-4 mb-4">
                <div class="card text-white bg-success h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title fs-2"><?php echo number_format($total_anggota); ?></h5>
                            <p class="card-text">Total Anggota Aktif</p>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
             <div class="col-md-4 mb-4">
                <div class="card text-white bg-warning h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title fs-2"><?php echo number_format($total_dipinjam); ?></h5>
                            <p class="card-text">Buku Sedang Dipinjam</p>
                        </div>
                        <i class="fas fa-exchange-alt fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Aktivitas Terakhir -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>5 Transaksi Terakhir
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Peminjam</th>
                                <th>Judul Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_transaksi_terakhir && mysqli_num_rows($result_transaksi_terakhir) > 0): ?>
                                <?php while ($transaksi = mysqli_fetch_assoc($result_transaksi_terakhir)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($transaksi['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($transaksi['judul_buku']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($transaksi['tgl_pinjam'])); ?></td>
                                        <td>
                                            <?php 
                                                if ($transaksi['status'] == 'Dikembalikan') {
                                                    echo '<span class="badge bg-success">Dikembalikan</span>';
                                                } else {
                                                    echo '<span class="badge bg-warning text-dark">Dipinjam</span>';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada transaksi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>