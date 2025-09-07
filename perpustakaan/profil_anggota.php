<?php
require_once 'config.php';
require_once 'templates/header.php';

// Cek jika anggota belum login, arahkan ke halaman login
if (!isset($_SESSION['id_anggota'])) {
    redirect('login.php');
}

$id_anggota = $_SESSION['id_anggota'];

// Ambil data profil anggota
$query_anggota = "SELECT * FROM tabel_anggota WHERE id_anggota = ?";
$stmt_anggota = mysqli_prepare($koneksi, $query_anggota);
mysqli_stmt_bind_param($stmt_anggota, "i", $id_anggota);
mysqli_stmt_execute($stmt_anggota);
$result_anggota = mysqli_stmt_get_result($stmt_anggota);
$profil = mysqli_fetch_assoc($result_anggota);

// Ambil riwayat peminjaman anggota
$query_riwayat = "SELECT b.judul_buku, t.tgl_pinjam, t.tgl_kembali, t.status, t.tgl_dikembalikan
                  FROM tabel_transaksi t
                  JOIN tabel_buku b ON t.id_buku = b.id_buku
                  WHERE t.id_anggota = ?
                  ORDER BY t.tgl_pinjam DESC";
$stmt_riwayat = mysqli_prepare($koneksi, $query_riwayat);
mysqli_stmt_bind_param($stmt_riwayat, "i", $id_anggota);
mysqli_stmt_execute($stmt_riwayat);
$riwayat_peminjaman = mysqli_stmt_get_result($stmt_riwayat);
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Profil Anggota</h1>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Selamat Datang, <?php echo htmlspecialchars($profil['nama_lengkap']); ?>!</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>No.Anggota:</strong> <?php echo htmlspecialchars($profil['nim']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($profil['email']); ?></p>
                    <p><strong>Program Studi:</strong> <?php echo htmlspecialchars($profil['prodi']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($profil['jenis_kelamin']); ?></p>
                    <p><strong>Tanggal Lahir:</strong> <?php echo date('d-m-Y', strtotime($profil['tgl_lahir'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Riwayat Peminjaman Buku
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Judul Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Batas Kembali</th>
                            <th>Tanggal Dikembalikan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($riwayat_peminjaman) > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($riwayat = mysqli_fetch_assoc($riwayat_peminjaman)): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($riwayat['judul_buku']); ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($riwayat['tgl_pinjam'])); ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($riwayat['tgl_kembali'])); ?></td>
                                    <td><?php echo $riwayat['tgl_dikembalikan'] ? date('d-m-Y', strtotime($riwayat['tgl_dikembalikan'])) : '-'; ?></td>
                                    <td>
                                        <span class="badge <?php echo $riwayat['status'] == 'Dipinjam' ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                            <?php echo htmlspecialchars($riwayat['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Anda belum pernah meminjam buku.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
