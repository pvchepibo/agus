<?php
require_once 'config.php';
// Autentikasi hanya untuk admin dan petugas
if (!isset($_SESSION['id_pengguna'])) {
    redirect('login.php');
}

$tanggal_mulai = isset($_GET['mulai']) ? $_GET['mulai'] : date('Y-m-01');
$tanggal_selesai = isset($_GET['selesai']) ? $_GET['selesai'] : date('Y-m-t');

// Query untuk mengambil semua data transaksi dalam rentang tanggal
$query_transaksi = "
    SELECT tt.*, ta.nama_lengkap, tb.judul_buku 
    FROM tabel_transaksi tt
    JOIN tabel_anggota ta ON tt.id_anggota = ta.id_anggota
    JOIN tabel_buku tb ON tt.id_buku = tb.id_buku
    WHERE tt.tgl_pinjam BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'
    ORDER BY tt.tgl_pinjam DESC
";
$result_transaksi = mysqli_query($koneksi, $query_transaksi);

require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Laporan Peminjaman</h3>
            <div class="d-print-none">
                <!-- === TOMBOL BARU DITAMBAHKAN DI SINI === -->
                <a href="export_laporan.php?mulai=<?php echo $tanggal_mulai; ?>&selesai=<?php echo $tanggal_selesai; ?>" class="btn btn-success">
                    <i class="fas fa-file-excel me-2"></i>Ekspor ke Excel
                </a>
                <!-- ==================================== -->
                <button class="btn btn-info" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Form Filter Tanggal -->
        <div class="card mb-4 d-print-none">
            <div class="card-body">
                <form action="laporan.php" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <label for="mulai" class="form-label">Dari Tanggal</label>
                        <input type="date" name="mulai" id="mulai" class="form-control" value="<?php echo $tanggal_mulai; ?>">
                    </div>
                    <div class="col-md-5">
                        <label for="selesai" class="form-label">Sampai Tanggal</label>
                        <input type="date" name="selesai" id="selesai" class="form-control" value="<?php echo $tanggal_selesai; ?>">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-secondary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Area Laporan yang akan dicetak -->
        <div class="printable-area">
            <h4 class="text-center mb-3">Laporan Peminjaman Buku</h4>
            <p class="text-center text-muted">Periode: <?php echo date('d F Y', strtotime($tanggal_mulai)) . ' - ' . date('d F Y', strtotime($tanggal_selesai)); ?></p>
            <hr>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Peminjam</th>
                                    <th>Judul Buku</th>
                                    <th>Tgl Pinjam</th>
                                    <th>Tgl Kembali</th>
                                    <th>Status</th>
                                    <th>Denda (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_transaksi && mysqli_num_rows($result_transaksi) > 0): ?>
                                    <?php $no = 1; ?>
                                    <?php while ($transaksi = mysqli_fetch_assoc($result_transaksi)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($transaksi['nama_lengkap']); ?></td>
                                            <td><?php echo htmlspecialchars($transaksi['judul_buku']); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($transaksi['tgl_pinjam'])); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($transaksi['tgl_kembali'])); ?></td>
                                            <td>
                                                <?php if($transaksi['status'] == 'Dikembalikan'): ?>
                                                    <span class="badge bg-success">Dikembalikan</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Dipinjam</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo number_format($transaksi['denda']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data transaksi pada periode ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'templates/footer.php'; ?>