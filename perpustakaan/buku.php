<?php
require_once 'config.php';
// Autentikasi hanya untuk admin dan petugas
if (!isset($_SESSION['id_pengguna'])) {
    redirect('login.php');
}

$pesan_sukses = "";
$pesan_error = "";

// --- LOGIKA TAMBAH BUKU (DENGAN UPLOAD GAMBAR) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_buku'])) {
    // Ambil data teks
    $judul = clean_input($koneksi, $_POST['judul_buku']);
    $pengarang = clean_input($koneksi, $_POST['pengarang']);
    $penerbit = clean_input($koneksi, $_POST['penerbit']);
    $tahun_terbit = clean_input($koneksi, $_POST['tahun_terbit']);
    $isbn = clean_input($koneksi, $_POST['isbn']);
    $jumlah_buku = clean_input($koneksi, $_POST['jumlah_buku']);
    $lokasi = clean_input($koneksi, $_POST['lokasi']);
    $id_kategori = (int)$_POST['id_kategori'];
    $sinopsis = clean_input($koneksi, $_POST['sinopsis']);
    $nama_file_sampul = null;

    // Proses upload file sampul
    if (isset($_FILES['sampul']) && $_FILES['sampul']['error'] == 0) {
        $target_dir = "uploads/sampul/";
        $nama_asli = basename($_FILES["sampul"]["name"]);
        $ekstensi_file = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
        // Buat nama file unik untuk menghindari duplikasi
        $nama_file_unik = uniqid() . '_' . time() . '.' . $ekstensi_file;
        $target_file = $target_dir . $nama_file_unik;
        $upload_ok = 1;

        // Cek ekstensi file
        $ekstensi_diizinkan = array("jpg", "jpeg", "png", "gif");
        if (!in_array($ekstensi_file, $ekstensi_diizinkan)) {
            $pesan_error = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $upload_ok = 0;
        }

        // Cek ukuran file (misal: maks 2MB)
        if ($_FILES["sampul"]["size"] > 2000000) {
            $pesan_error = "Maaf, ukuran file Anda terlalu besar (maks 2MB).";
            $upload_ok = 0;
        }

        if ($upload_ok == 1) {
            if (move_uploaded_file($_FILES["sampul"]["tmp_name"], $target_file)) {
                $nama_file_sampul = $nama_file_unik;
            } else {
                $pesan_error = "Maaf, terjadi error saat mengupload file Anda.";
            }
        }
    }

    if (empty($pesan_error)) {
        $query = "INSERT INTO tabel_buku (judul_buku, pengarang, penerbit, tahun_terbit, isbn, jumlah_buku, lokasi, id_kategori, sinopsis, sampul) 
                  VALUES ('$judul', '$pengarang', '$penerbit', '$tahun_terbit', '$isbn', '$jumlah_buku', '$lokasi', '$id_kategori', '$sinopsis', '$nama_file_sampul')";
        
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['pesan_sukses_buku'] = "Buku berhasil ditambahkan!";
        } else {
            $_SESSION['pesan_error_buku'] = "Error: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['pesan_error_buku'] = $pesan_error;
    }
    redirect('buku.php');
}

// ... (logika hapus, pesan session, filter, paginasi tetap sama) ...
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // Ambil nama file sampul untuk dihapus
    $q_get_sampul = mysqli_query($koneksi, "SELECT sampul FROM tabel_buku WHERE id_buku = $id");
    $data_sampul = mysqli_fetch_assoc($q_get_sampul);
    if ($data_sampul && !empty($data_sampul['sampul'])) {
        $file_path = 'uploads/sampul/' . $data_sampul['sampul'];
        if (file_exists($file_path)) {
            unlink($file_path); // Hapus file dari server
        }
    }
    $query_hapus = "DELETE FROM tabel_buku WHERE id_buku = $id";
    if (mysqli_query($koneksi, $query_hapus)) { $_SESSION['pesan_sukses_buku'] = "Buku berhasil dihapus."; } 
    else { $_SESSION['pesan_error_buku'] = "Gagal menghapus buku."; }
    redirect('buku.php');
}
if(isset($_SESSION['pesan_sukses_buku'])) { $pesan_sukses = $_SESSION['pesan_sukses_buku']; unset($_SESSION['pesan_sukses_buku']); }
if(isset($_SESSION['pesan_error_buku'])) { $pesan_error = $_SESSION['pesan_error_buku']; unset($_SESSION['pesan_error_buku']); }

$limit = 10; $halaman_aktif = isset($_GET['page']) ? (int)$_GET['page'] : 1; $offset = ($halaman_aktif - 1) * $limit;
$kata_kunci = isset($_GET['search']) ? clean_input($koneksi, $_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? clean_input($koneksi, $_GET['sort']) : 'terbaru';
$query_data = "SELECT b.*, k.nama_kategori FROM tabel_buku b LEFT JOIN tabel_kategori k ON b.id_kategori = k.id_kategori";
$query_total = "SELECT COUNT(*) as total FROM tabel_buku b";
$where_clause = ""; $url_params = "";
if (!empty($kata_kunci)) { $where_clause = " WHERE (b.judul_buku LIKE '%$kata_kunci%' OR b.pengarang LIKE '%$kata_kunci%' OR b.isbn LIKE '%$kata_kunci%')"; $url_params .= "&search=" . urlencode($kata_kunci); }
$query_data .= $where_clause; $query_total .= $where_clause;
$order_clause = " ORDER BY ";
switch ($sort_by) {
    case 'judul_asc': $order_clause .= "b.judul_buku ASC"; break;
    case 'judul_desc': $order_clause .= "b.judul_buku DESC"; break;
    case 'pengarang_asc': $order_clause .= "b.pengarang ASC"; break;
    case 'pengarang_desc': $order_clause .= "b.pengarang DESC"; break;
    case 'terlama': $order_clause .= "b.id_buku ASC"; break;
    case 'stok_terbanyak': $order_clause .= "b.jumlah_buku DESC"; break;
    default: $order_clause .= "b.id_buku DESC";
}
$url_params .= "&sort=" . urlencode($sort_by);
$query_data .= $order_clause . " LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query_data);
$total_buku_result = mysqli_query($koneksi, $query_total);
$total_buku = mysqli_fetch_assoc($total_buku_result)['total'];
$total_halaman = ceil($total_buku / $limit);
$kategori_list = mysqli_query($koneksi, "SELECT * FROM tabel_kategori ORDER BY nama_kategori ASC");

require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Kelola Data Buku</h3>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahBukuModal"><i class="fas fa-plus me-2"></i>Tambah Buku</button>
        </div>
        <?php if ($pesan_sukses): ?><div class="alert alert-success"><?php echo $pesan_sukses; ?></div><?php endif; ?>
        <?php if ($pesan_error): ?><div class="alert alert-danger"><?php echo $pesan_error; ?></div><?php endif; ?>
        
        <!-- Form Pencarian & Filter -->
        <div class="card mb-4"><div class="card-body"><form action="buku.php" method="GET" class="row g-3 align-items-center"><div class="col-md-6"><input type="text" name="search" class="form-control" placeholder="Cari..." value="<?php echo htmlspecialchars($kata_kunci); ?>"></div><div class="col-md-4"><select name="sort" class="form-select"><option value="terbaru" <?php if($sort_by == 'terbaru') echo 'selected'; ?>>Terbaru</option><option value="terlama" <?php if($sort_by == 'terlama') echo 'selected'; ?>>Terlama</option><option value="judul_asc" <?php if($sort_by == 'judul_asc') echo 'selected'; ?>>Judul (A-Z)</option><option value="judul_desc" <?php if($sort_by == 'judul_desc') echo 'selected'; ?>>Judul (Z-A)</option></select></div><div class="col-md-2"><button type="submit" class="btn btn-secondary w-100">Filter</button></div></form></div></div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Sampul</th>
                                <th>Judul Buku</th>
                                <th>Kategori</th>
                                <th>Pengarang</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while ($buku = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($buku['sampul'])): ?>
                                                <img src="uploads/sampul/<?php echo htmlspecialchars($buku['sampul']); ?>" alt="Cover" width="50" class="img-thumbnail">
                                            <?php else: ?>
                                                <img src="https://placehold.co/50x70/EFEFEF/333333?text=N/A" alt="No Cover" class="img-thumbnail">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($buku['judul_buku']); ?></td>
                                        <td><?php echo htmlspecialchars($buku['nama_kategori'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($buku['pengarang']); ?></td>
                                        <td><?php echo htmlspecialchars($buku['jumlah_buku']); ?></td>
                                        <td>
                                            <a href="edit_buku.php?id=<?php echo $buku['id_buku']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                            <a href="buku.php?hapus=<?php echo $buku['id_buku']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus buku ini?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Data buku tidak ditemukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3"><?php echo generate_pagination_links($halaman_aktif, $total_halaman, $url_params); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Buku -->
<div class="modal fade" id="tambahBukuModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Buku Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- PENTING: Tambahkan enctype untuk upload file -->
            <form action="buku.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3"><label for="judul_buku" class="form-label">Judul Buku</label><input type="text" class="form-control" name="judul_buku" required></div>
                            <div class="mb-3"><label for="pengarang" class="form-label">Pengarang</label><input type="text" class="form-control" name="pengarang" required></div>
                            <div class="mb-3"><label for="penerbit" class="form-label">Penerbit</label><input type="text" class="form-control" name="penerbit" required></div>
                            <div class="mb-3"><label for="id_kategori" class="form-label">Kategori</label><select class="form-select" name="id_kategori" required><option value="">-- Pilih Kategori --</option><?php mysqli_data_seek($kategori_list, 0); while($kategori = mysqli_fetch_assoc($kategori_list)): ?><option value="<?php echo $kategori['id_kategori']; ?>"><?php echo htmlspecialchars($kategori['nama_kategori']); ?></option><?php endwhile; ?></select></div>
                        </div>
                        <div class="col-md-6">
                             <div class="mb-3"><label for="tahun_terbit" class="form-label">Tahun Terbit</label><input type="number" class="form-control" name="tahun_terbit" required max="<?php echo date('Y'); ?>"></div>
                             <div class="mb-3"><label for="isbn" class="form-label">ISBN</label><input type="text" class="form-control" name="isbn"></div>
                            <div class="row">
                                <div class="col-6"><div class="mb-3"><label for="jumlah_buku" class="form-label">Jumlah Stok</label><input type="number" class="form-control" name="jumlah_buku" required min="0"></div></div>
                                <div class="col-6"><div class="mb-3"><label for="lokasi" class="form-label">Lokasi Rak</label><input type="text" class="form-control" name="lokasi"></div></div>
                            </div>
                            <!-- === INPUT FILE BARU === -->
                             <div class="mb-3">
                                <label for="sampul" class="form-label">Gambar Sampul (Opsional)</label>
                                <input type="file" class="form-control" name="sampul" id="sampul">
                            </div>
                            <!-- ======================== -->
                        </div>
                    </div>
                     <div class="mb-3"><label for="sinopsis" class="form-label">Sinopsis</label><textarea class="form-control" name="sinopsis" rows="4"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_buku" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>