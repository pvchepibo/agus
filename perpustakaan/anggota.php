<?php
require_once 'config.php';
// Autentikasi hanya untuk admin dan petugas
if (!isset($_SESSION['id_pengguna'])) {
    redirect('login.php');
}

$pesan_sukses = "";
$pesan_error = "";

// --- LOGIKA MENYETUJUI ANGGOTA ---
if (isset($_GET['setujui'])) {
    $id_anggota = (int)$_GET['setujui'];
    $query_setujui = "UPDATE tabel_anggota SET status = 'Aktif' WHERE id_anggota = $id_anggota";
    if (mysqli_query($koneksi, $query_setujui)) {
        $_SESSION['pesan_sukses_anggota'] = "Anggota berhasil disetujui dan sekarang dapat login.";
    } else {
        $_SESSION['pesan_error_anggota'] = "Gagal menyetujui anggota.";
    }
    redirect('anggota.php');
}


// --- LOGIKA TAMBAH ANGGOTA (DARI ADMIN) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_anggota'])) {
    $nim = clean_input($koneksi, $_POST['nim']);
    $nama_lengkap = clean_input($koneksi, $_POST['nama_lengkap']);
    $email = clean_input($koneksi, $_POST['email']);
    $password = $_POST['password'];
    $tempat_lahir = clean_input($koneksi, $_POST['tempat_lahir']);
    $tgl_lahir = clean_input($koneksi, $_POST['tgl_lahir']);
    $jenis_kelamin = clean_input($koneksi, $_POST['jenis_kelamin']);
    $prodi = clean_input($koneksi, $_POST['prodi']);
    
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
    
    $query = "INSERT INTO tabel_anggota (nim, nama_lengkap, email, password, tempat_lahir, tgl_lahir, jenis_kelamin, prodi, status) 
              VALUES ('$nim', '$nama_lengkap', '$email', '$password_hashed', '$tempat_lahir', '$tgl_lahir', '$jenis_kelamin', '$prodi', 'Aktif')";
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['pesan_sukses_anggota'] = "Anggota baru berhasil ditambahkan.";
    } else {
        $_SESSION['pesan_error_anggota'] = "Gagal menambahkan anggota. NIM atau Email mungkin sudah terdaftar.";
    }
    redirect('anggota.php');
}

// --- LOGIKA HAPUS ANGGOTA ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $query = "DELETE FROM tabel_anggota WHERE id_anggota = $id";
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['pesan_sukses_anggota'] = "Anggota berhasil dihapus.";
    } else {
        $_SESSION['pesan_error_anggota'] = "Gagal menghapus anggota.";
    }
    redirect('anggota.php');
}


// Cek pesan dari session
if(isset($_SESSION['pesan_sukses_anggota'])) {
    $pesan_sukses = $_SESSION['pesan_sukses_anggota'];
    unset($_SESSION['pesan_sukses_anggota']);
}
if(isset($_SESSION['pesan_error_anggota'])) {
    $pesan_error = $_SESSION['pesan_error_anggota'];
    unset($_SESSION['pesan_error_anggota']);
}

// --- LOGIKA PENCARIAN, FILTER, DAN PAGINASI ---
$limit = 10;
$halaman_aktif = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($halaman_aktif - 1) * $limit;

$kata_kunci = isset($_GET['search']) ? clean_input($koneksi, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? clean_input($koneksi, $_GET['status']) : '';

$query_data = "SELECT * FROM tabel_anggota";
$query_total = "SELECT COUNT(*) as total FROM tabel_anggota";
$where_clauses = [];
$url_params = "";

if (!empty($kata_kunci)) {
    $where_clauses[] = "(nama_lengkap LIKE '%$kata_kunci%' OR nim LIKE '%$kata_kunci%' OR email LIKE '%$kata_kunci%')";
    $url_params .= "&search=" . urlencode($kata_kunci);
}

if (!empty($filter_status)) {
    $where_clauses[] = "status = '$filter_status'";
    $url_params .= "&status=" . urlencode($filter_status);
}

if (!empty($where_clauses)) {
    $where_clause_string = " WHERE " . implode(' AND ', $where_clauses);
    $query_data .= $where_clause_string;
    $query_total .= $where_clause_string;
}

$query_data .= " ORDER BY id_anggota DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query_data);
$total_result = mysqli_query($koneksi, $query_total);
$total_data = mysqli_fetch_assoc($total_result)['total'];
$total_halaman = ceil($total_data / $limit);

require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Kelola Data Anggota</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahAnggotaModal">
                <i class="fas fa-plus me-2"></i>Tambah Anggota
            </button>
        </div>

        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="alert alert-danger"><?php echo $pesan_error; ?></div>
        <?php endif; ?>

        <!-- Form Pencarian dan Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="anggota.php" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, NIM, atau email..." value="<?php echo htmlspecialchars($kata_kunci); ?>">
                    </div>
                    <div class="col-md-5">
                         <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="Aktif" <?php if($filter_status == 'Aktif') echo 'selected'; ?>>Aktif</option>
                            <option value="Menunggu Persetujuan" <?php if($filter_status == 'Menunggu Persetujuan') echo 'selected'; ?>>Menunggu Persetujuan</option>
                            <option value="Ditangguhkan" <?php if($filter_status == 'Ditangguhkan') echo 'selected'; ?>>Ditangguhkan</option>
                            <option value="Dinonaktifkan" <?php if($filter_status == 'Dinonaktifkan') echo 'selected'; ?>>Ditangguhkan</option>
                        </select>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>No.Anggota</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php $no = $offset + 1; ?>
                                <?php while ($anggota = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($anggota['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($anggota['nim']); ?></td>
                                        <td><?php echo htmlspecialchars($anggota['email']); ?></td>
                                        <td>
                                            <?php 
                                                if ($anggota['status'] == 'Aktif') {
                                                    echo '<span class="badge bg-success">Aktif</span>';
                                                } elseif ($anggota['status'] == 'Menunggu Persetujuan') {
                                                    echo '<span class="badge bg-warning text-dark">Menunggu</span>';
                                                } else { // Ditangguhkan
                                                    echo '<span class="badge bg-danger">Ditangguhkan</span>';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($anggota['status'] == 'Menunggu Persetujuan'): ?>
                                                <a href="anggota.php?setujui=<?php echo $anggota['id_anggota']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Setujui anggota ini?')">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <!-- === TOMBOL BARU DITAMBAHKAN DI SINI === -->
                                            <a href="kartu_anggota.php?id=<?php echo $anggota['id_anggota']; ?>" class="btn btn-secondary btn-sm" target="_blank" title="Cetak Kartu Anggota">
                                                <i class="fas fa-id-card"></i>
                                            </a>
                                            <!-- ==================================== -->
                                            <a href="edit_anggota.php?id=<?php echo $anggota['id_anggota']; ?>" class="btn btn-warning btn-sm" title="Edit Anggota">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="anggota.php?hapus=<?php echo $anggota['id_anggota']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus anggota ini?')" title="Hapus Anggota">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Data anggota tidak ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                 <div class="mt-3">
                    <?php echo generate_pagination_links($halaman_aktif, $total_halaman, $url_params); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Anggota -->
<div class="modal fade" id="tambahAnggotaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Anggota Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="anggota.php" method="POST">
                <div class="modal-body">
                     <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nim" class="form-label">No. Anggota</label>
                                <input type="text" class="form-control" name="nim" required>
                            </div>
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                             <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="mb-3">
                                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tempat_lahir">
                            </div>
                             <div class="mb-3">
                                <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" name="tgl_lahir">
                            </div>
                            <div class="mb-3">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                <select class="form-select" name="jenis_kelamin">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="prodi" class="form-label">Pekerjaan</label>
                                <input type="text" class="form-control" name="prodi">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_anggota" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once 'templates/footer.php'; ?>