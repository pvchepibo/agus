<?php
require_once 'config.php';

// ... (logika PHP di atas tetap sama, tidak perlu diubah) ...
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pinjam_buku'])) { if (!isset($_SESSION['id_anggota'])) { redirect('login.php'); } $id_anggota = $_SESSION['id_anggota']; $id_buku = (int)$_POST['id_buku']; $query_buku_pinjam = "SELECT * FROM tabel_buku WHERE id_buku = $id_buku"; $buku_pinjam_result = mysqli_query($koneksi, $query_buku_pinjam); $buku_pinjam = mysqli_fetch_assoc($buku_pinjam_result); if ($buku_pinjam['jumlah_buku'] > 0) { mysqli_begin_transaction($koneksi); try { $query_update_stok = "UPDATE tabel_buku SET jumlah_buku = jumlah_buku - 1 WHERE id_buku = $id_buku"; mysqli_query($koneksi, $query_update_stok); $tgl_pinjam = date('Y-m-d'); $tgl_kembali = date('Y-m-d', strtotime('+7 days')); $query_insert_transaksi = "INSERT INTO tabel_transaksi (id_buku, id_anggota, tgl_pinjam, tgl_kembali, status) VALUES ('$id_buku', '$id_anggota', '$tgl_pinjam', '$tgl_kembali', 'Dipinjam')"; mysqli_query($koneksi, $query_insert_transaksi); mysqli_commit($koneksi); $_SESSION['pesan_sukses_katalog'] = "Buku '" . htmlspecialchars($buku_pinjam['judul_buku']) . "' berhasil dipinjam!"; } catch (mysqli_sql_exception $exception) { mysqli_rollback($koneksi); $_SESSION['pesan_error_katalog'] = "Gagal meminjam buku."; } } else { $_SESSION['pesan_error_katalog'] = "Stok buku habis."; } redirect('katalog.php'); }
$limit = 12; $halaman_aktif = isset($_GET['page']) ? (int)$_GET['page'] : 1; $offset = ($halaman_aktif - 1) * $limit;
$kata_kunci = isset($_GET['search']) ? clean_input($koneksi, $_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? clean_input($koneksi, $_GET['sort']) : 'terbaru';
$id_kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$query_data = "SELECT b.*, k.nama_kategori FROM tabel_buku b LEFT JOIN tabel_kategori k ON b.id_kategori = k.id_kategori";
$query_total = "SELECT COUNT(*) as total FROM tabel_buku b";
$where_clauses = []; $url_params = "";
if ($id_kategori_filter > 0) { $where_clauses[] = "b.id_kategori = $id_kategori_filter"; $url_params .= "&kategori=" . $id_kategori_filter; }
if (!empty($kata_kunci)) { $where_clauses[] = "(b.judul_buku LIKE '%$kata_kunci%' OR b.pengarang LIKE '%$kata_kunci%' OR b.isbn LIKE '%$kata_kunci%')"; $url_params .= "&search=" . urlencode($kata_kunci); }
if (!empty($where_clauses)) { $where_clause_string = " WHERE " . implode(' AND ', $where_clauses); $query_data .= $where_clause_string; $query_total .= $where_clause_string; }
$order_clause = " ORDER BY ";
switch ($sort_by) {
    case 'judul_asc': $order_clause .= "b.judul_buku ASC"; break; case 'judul_desc': $order_clause .= "b.judul_buku DESC"; break;
    case 'pengarang_asc': $order_clause .= "b.pengarang ASC"; break; case 'pengarang_desc': $order_clause .= "b.pengarang DESC"; break;
    case 'terlama': $order_clause .= "b.id_buku ASC"; break; case 'stok_terbanyak': $order_clause .= "b.jumlah_buku DESC"; break;
    default: $order_clause .= "b.id_buku DESC";
}
$url_params .= "&sort=" . urlencode($sort_by);
$query_data .= $order_clause . " LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query_data);
$total_result = mysqli_query($koneksi, $query_total);
$total_data = mysqli_fetch_assoc($total_result)['total'];
$total_halaman = ceil($total_data / $limit);
$kategori_list = mysqli_query($koneksi, "SELECT * FROM tabel_kategori ORDER BY nama_kategori ASC");
require_once 'templates/public_header.php';
?>
<div class="container my-5">
    <div class="row">
        <!-- Kolom Filter -->
        <div class="col-lg-3">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-body"><h5 class="card-title mb-3">Filter & Urutkan</h5><form action="katalog.php" method="GET"><div class="mb-3"><label for="search" class="form-label">Cari Buku</label><input type="text" name="search" id="search" class="form-control" placeholder="Judul..." value="<?php echo htmlspecialchars($kata_kunci); ?>"></div><div class="mb-3"><label for="kategori" class="form-label">Kategori</label><select name="kategori" id="kategori" class="form-select"><option value="">Semua Kategori</option><?php while($kat = mysqli_fetch_assoc($kategori_list)): ?><option value="<?php echo $kat['id_kategori']; ?>" <?php if($id_kategori_filter == $kat['id_kategori']) echo 'selected'; ?>><?php echo htmlspecialchars($kat['nama_kategori']); ?></option><?php endwhile; ?></select></div><div class="mb-3"><label for="sort" class="form-label">Urutkan</label><select name="sort" id="sort" class="form-select"><option value="terbaru" <?php if($sort_by == 'terbaru') echo 'selected'; ?>>Terbaru</option><option value="judul_asc" <?php if($sort_by == 'judul_asc') echo 'selected'; ?>>Judul (A-Z)</option></select></div><div class="d-grid"><button type="submit" class="btn btn-primary">Terapkan</button></div></form></div>
            </div>
        </div>
        <!-- Kolom Daftar Buku -->
        <div class="col-lg-9">
            <h1 class="mb-4">Katalog Buku</h1>
            <?php if(isset($_SESSION['pesan_sukses_katalog'])): ?><div class="alert alert-success"><?php echo $_SESSION['pesan_sukses_katalog']; unset($_SESSION['pesan_sukses_katalog']); ?></div><?php endif; ?>
            <?php if(isset($_SESSION['pesan_error_katalog'])): ?><div class="alert alert-danger"><?php echo $_SESSION['pesan_error_katalog']; unset($_SESSION['pesan_error_katalog']); ?></div><?php endif; ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                 <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($buku = mysqli_fetch_assoc($result)): ?>
                    <div class="col">
                        <div class="card h-100 book-card shadow-sm">
                            <?php
                                // --- PERUBAHAN UTAMA DI SINI ---
                                $sampul_path = !empty($buku['sampul']) ? 'uploads/sampul/' . htmlspecialchars($buku['sampul']) : 'https://placehold.co/600x800/EFEFEF/333333?text=Cover';
                            ?>
                            <img src="<?php echo $sampul_path; ?>" class="card-img-top book-cover" alt="Cover <?php echo htmlspecialchars($buku['judul_buku']); ?>" onerror="this.onerror=null;this.src='https://placehold.co/600x800/EFEFEF/333333?text=Error';">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><a href="detail_buku.php?id=<?php echo $buku['id_buku']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($buku['judul_buku']); ?></a></h5>
                                <p class="card-text text-muted small">Oleh: <?php echo htmlspecialchars($buku['pengarang']); ?></p>
                                <span class="badge bg-secondary align-self-start mb-2"><?php echo htmlspecialchars($buku['nama_kategori'] ?: 'Umum'); ?></span>
                                <div class="mt-auto">
                                    <form method="POST" action="katalog.php"><input type="hidden" name="id_buku" value="<?php echo $buku['id_buku']; ?>"><?php if(isset($_SESSION['id_anggota'])): ?><?php if($buku['jumlah_buku'] > 0): ?><button type="submit" name="pinjam_buku" class="btn btn-primary w-100">Pinjam Buku Ini</button><?php else: ?><button class="btn btn-secondary w-100" disabled>Stok Habis</button><?php endif; ?><?php else: ?><a href="login.php" class="btn btn-outline-primary w-100">Login untuk Meminjam</a><?php endif; ?></form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12"><div class="alert alert-warning">Tidak ada buku yang cocok.</div></div>
                <?php endif; ?>
            </div>
            <div class="mt-5"><?php echo generate_pagination_links($halaman_aktif, $total_halaman, $url_params); ?></div>
        </div>
    </div>
</div>
<?php require_once 'templates/public_footer.php'; ?>