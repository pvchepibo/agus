<?php
require_once 'config.php';
// Autentikasi hanya untuk admin dan petugas
if (!isset($_SESSION['id_pengguna'])) {
    redirect('login.php');
}

$pesan_sukses = '';
$pesan_error = '';

// --- LOGIKA PROSES FORM ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = clean_input($koneksi, $_POST['judul']);
    $isi = clean_input($koneksi, $_POST['isi']);
    $id_pengguna = $_SESSION['id_pengguna'];

    // Tambah Pengumuman
    if (isset($_POST['tambah_pengumuman'])) {
        $query = "INSERT INTO tabel_pengumuman (judul_pengumuman, isi_pengumuman, id_pengguna) VALUES ('$judul', '$isi', $id_pengguna)";
        if (mysqli_query($koneksi, $query)) {
            $pesan_sukses = "Pengumuman berhasil diposting.";
        } else {
            $pesan_error = "Gagal memposting pengumuman.";
        }
    }

    // Edit Pengumuman
    if (isset($_POST['edit_pengumuman'])) {
        $id_pengumuman = (int)$_POST['id_pengumuman'];
        $query = "UPDATE tabel_pengumuman SET judul_pengumuman = '$judul', isi_pengumuman = '$isi' WHERE id_pengumuman = $id_pengumuman";
        if (mysqli_query($koneksi, $query)) {
            $pesan_sukses = "Pengumuman berhasil diperbarui.";
        } else {
            $pesan_error = "Gagal memperbarui pengumuman.";
        }
    }
}

// --- LOGIKA HAPUS PENGUMUMAN ---
if (isset($_GET['hapus'])) {
    $id_pengumuman = (int)$_GET['hapus'];
    $query = "DELETE FROM tabel_pengumuman WHERE id_pengumuman = $id_pengumuman";
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['pesan_sukses_pengumuman'] = "Pengumuman berhasil dihapus.";
    } else {
        $_SESSION['pesan_error_pengumuman'] = "Gagal menghapus pengumuman.";
    }
    redirect('pengumuman.php');
}

// Cek pesan dari session
if(isset($_SESSION['pesan_sukses_pengumuman'])) {
    $pesan_sukses = $_SESSION['pesan_sukses_pengumuman'];
    unset($_SESSION['pesan_sukses_pengumuman']);
}

// Ambil semua data pengumuman untuk ditampilkan
$result = mysqli_query($koneksi, "SELECT p.*, u.nama_lengkap FROM tabel_pengumuman p JOIN tabel_pengguna u ON p.id_pengguna = u.id_pengguna ORDER BY p.tanggal_posting DESC");

require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h3>Kelola Pengumuman & Acara</h3>
        <hr>

        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if ($pesan_error): ?>
            <div class="alert alert-danger"><?php echo $pesan_error; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Buat Pengumuman Baru</div>
                    <div class="card-body">
                        <form action="pengumuman.php" method="POST">
                            <div class="mb-3">
                                <label for="judul" class="form-label">Judul</label>
                                <input type="text" class="form-control" id="judul" name="judul" required>
                            </div>
                            <div class="mb-3">
                                <label for="isi" class="form-label">Isi Pengumuman</label>
                                <textarea class="form-control" id="isi" name="isi" rows="5" required></textarea>
                            </div>
                            <button type="submit" name="tambah_pengumuman" class="btn btn-primary">Posting</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Daftar Pengumuman</div>
                    <div class="card-body">
                         <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Tanggal Posting</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                        <?php while ($item = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['judul_pengumuman']); ?></td>
                                                <td><?php echo date('d M Y, H:i', strtotime($item['tanggal_posting'])); ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editPengumumanModal" data-id="<?php echo $item['id_pengumuman']; ?>" data-judul="<?php echo htmlspecialchars($item['judul_pengumuman']); ?>" data-isi="<?php echo htmlspecialchars($item['isi_pengumuman']); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="pengumuman.php?hapus=<?php echo $item['id_pengumuman']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus pengumuman ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Belum ada pengumuman.</td>
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
</div>

<!-- Modal Edit Pengumuman -->
<div class="modal fade" id="editPengumumanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pengumuman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="pengumuman.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_pengumuman" id="edit_id_pengumuman">
                    <div class="mb-3">
                        <label for="edit_judul" class="form-label">Judul</label>
                        <input type="text" class="form-control" id="edit_judul" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_isi" class="form-label">Isi Pengumuman</label>
                        <textarea class="form-control" id="edit_isi" name="isi" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_pengumuman" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editPengumumanModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var judul = button.getAttribute('data-judul');
        var isi = button.getAttribute('data-isi');
        
        editModal.querySelector('#edit_id_pengumuman').value = id;
        editModal.querySelector('#edit_judul').value = judul;
        editModal.querySelector('#edit_isi').value = isi;
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>