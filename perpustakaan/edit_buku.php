<?php
require_once 'config.php';
if (!isset($_SESSION['id_pengguna'])) { redirect('login.php'); }
if (!isset($_GET['id']) || empty($_GET['id'])) { redirect('buku.php'); }
$id_buku = (int)$_GET['id'];

$pesan_sukses = "";
$pesan_error = "";

// --- LOGIKA UPDATE BUKU (DENGAN UPLOAD GAMBAR) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_buku'])) {
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
    $sampul_lama = $_POST['sampul_lama']; // Ambil nama file sampul yang lama
    $nama_file_sampul = $sampul_lama; // Defaultnya pakai nama lama

    // Proses jika ada file sampul baru yang diupload
    if (isset($_FILES['sampul']) && $_FILES['sampul']['error'] == 0) {
        $target_dir = "uploads/sampul/";
        $nama_asli = basename($_FILES["sampul"]["name"]);
        $ekstensi_file = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
        $nama_file_unik = uniqid() . '_' . time() . '.' . $ekstensi_file;
        $target_file = $target_dir . $nama_file_unik;
        $upload_ok = 1;

        $ekstensi_diizinkan = array("jpg", "jpeg", "png", "gif");
        if (!in_array($ekstensi_file, $ekstensi_diizinkan)) { $pesan_error = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan."; $upload_ok = 0; }
        if ($_FILES["sampul"]["size"] > 2000000) { $pesan_error = "Maaf, ukuran file Anda terlalu besar (maks 2MB)."; $upload_ok = 0; }

        if ($upload_ok == 1) {
            if (move_uploaded_file($_FILES["sampul"]["tmp_name"], $target_file)) {
                $nama_file_sampul = $nama_file_unik;
                // Jika upload berhasil, hapus file lama (jika ada)
                if (!empty($sampul_lama) && file_exists($target_dir . $sampul_lama)) {
                    unlink($target_dir . $sampul_lama);
                }
            } else {
                $pesan_error = "Maaf, terjadi error saat mengupload file baru Anda.";
            }
        }
    }

    if (empty($pesan_error)) {
        $query_update = "UPDATE tabel_buku SET 
                            judul_buku = '$judul', pengarang = '$pengarang', penerbit = '$penerbit',
                            tahun_terbit = '$tahun_terbit', isbn = '$isbn', jumlah_buku = '$jumlah_buku',
                            lokasi = '$lokasi', id_kategori = '$id_kategori', sinopsis = '$sinopsis',
                            sampul = '$nama_file_sampul'
                        WHERE id_buku = $id_buku";
        if (mysqli_query($koneksi, $query_update)) {
            $pesan_sukses = "Data buku berhasil diperbarui!";
        } else {
            $pesan_error = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    }
}

// Ambil data buku yang akan diedit
$query_get = "SELECT * FROM tabel_buku WHERE id_buku = $id_buku";
$result_get = mysqli_query($koneksi, $query_get);
if (mysqli_num_rows($result_get) == 0) { redirect('buku.php'); }
$buku = mysqli_fetch_assoc($result_get);

$kategori_list = mysqli_query($koneksi, "SELECT * FROM tabel_kategori ORDER BY nama_kategori ASC");

require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h3>Edit Data Buku</h3>
        <hr>
        <?php if ($pesan_sukses): ?><div class="alert alert-success"><?php echo $pesan_sukses; ?></div><?php endif; ?>
        <?php if ($pesan_error): ?><div class="alert alert-danger"><?php echo $pesan_error; ?></div><?php endif; ?>

        <form action="edit_buku.php?id=<?php echo $id_buku; ?>" method="POST" enctype="multipart/form-data">
             <input type="hidden" name="sampul_lama" value="<?php echo htmlspecialchars($buku['sampul']); ?>">
             <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3"><label for="judul_buku" class="form-label">Judul Buku</label><input type="text" class="form-control" name="judul_buku" value="<?php echo htmlspecialchars($buku['judul_buku']); ?>" required></div>
                                    <div class="mb-3"><label for="pengarang" class="form-label">Pengarang</label><input type="text" class="form-control" name="pengarang" value="<?php echo htmlspecialchars($buku['pengarang']); ?>" required></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3"><label for="penerbit" class="form-label">Penerbit</label><input type="text" class="form-control" name="penerbit" value="<?php echo htmlspecialchars($buku['penerbit']); ?>" required></div>
                                    <div class="mb-3"><label for="tahun_terbit" class="form-label">Tahun Terbit</label><input type="number" class="form-control" name="tahun_terbit" value="<?php echo htmlspecialchars($buku['tahun_terbit']); ?>" required></div>
                                </div>
                            </div>
                            <div class="mb-3"><label for="sinopsis" class="form-label">Sinopsis</label><textarea class="form-control" name="sinopsis" rows="5"><?php echo htmlspecialchars($buku['sinopsis']); ?></textarea></div>
                        </div>
                    </div>
                </div>
                 <div class="col-md-4">
                     <div class="card">
                         <div class="card-body">
                            <div class="mb-3"><label for="isbn" class="form-label">ISBN</label><input type="text" class="form-control" name="isbn" value="<?php echo htmlspecialchars($buku['isbn']); ?>"></div>
                            <div class="mb-3"><label for="id_kategori" class="form-label">Kategori</label><select class="form-select" name="id_kategori" required><option value="">-- Pilih Kategori --</option><?php while($kategori = mysqli_fetch_assoc($kategori_list)): ?><option value="<?php echo $kategori['id_kategori']; ?>" <?php if($kategori['id_kategori'] == $buku['id_kategori']) echo 'selected'; ?>><?php echo htmlspecialchars($kategori['nama_kategori']); ?></option><?php endwhile; ?></select></div>
                            <div class="mb-3"><label for="jumlah_buku" class="form-label">Jumlah Stok</label><input type="number" class="form-control" name="jumlah_buku" value="<?php echo htmlspecialchars($buku['jumlah_buku']); ?>" required></div>
                             <div class="mb-3"><label for="lokasi" class="form-label">Lokasi Rak</label><input type="text" class="form-control" name="lokasi" value="<?php echo htmlspecialchars($buku['lokasi']); ?>"></div>
                         </div>
                     </div>
                     <div class="card mt-3">
                        <div class="card-body">
                            <label class="form-label">Sampul Saat Ini</label>
                            <?php if (!empty($buku['sampul'])): ?>
                                <img src="uploads/sampul/<?php echo htmlspecialchars($buku['sampul']); ?>" alt="Cover" class="img-thumbnail mb-2">
                            <?php else: ?>
                                <p class="text-muted">Belum ada sampul.</p>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label for="sampul" class="form-label">Ubah Sampul (Opsional)</label>
                                <input type="file" class="form-control" name="sampul" id="sampul">
                            </div>
                        </div>
                     </div>
                 </div>
            </div>
            <div class="mt-4">
                <button type="submit" name="edit_buku" class="btn btn-primary">Simpan Perubahan</button>
                <a href="buku.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>