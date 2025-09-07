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
    // Tambah Kategori
    if (isset($_POST['tambah_kategori'])) {
        $nama_kategori = clean_input($koneksi, $_POST['nama_kategori']);
        $query = "INSERT INTO tabel_kategori (nama_kategori) VALUES ('$nama_kategori')";
        if (mysqli_query($koneksi, $query)) {
            $pesan_sukses = "Kategori baru berhasil ditambahkan.";
        } else {
            $pesan_error = "Gagal menambahkan kategori.";
        }
    }
    // Edit Kategori
    if (isset($_POST['edit_kategori'])) {
        $id_kategori = (int)$_POST['id_kategori'];
        $nama_kategori = clean_input($koneksi, $_POST['nama_kategori_edit']);
        $query = "UPDATE tabel_kategori SET nama_kategori = '$nama_kategori' WHERE id_kategori = $id_kategori";
        if (mysqli_query($koneksi, $query)) {
            $pesan_sukses = "Kategori berhasil diperbarui.";
        } else {
            $pesan_error = "Gagal memperbarui kategori.";
        }
    }
}

// --- LOGIKA HAPUS KATEGORI ---
if (isset($_GET['hapus'])) {
    $id_kategori = (int)$_GET['hapus'];
    // Note: Karena ada foreign key 'ON DELETE SET NULL', buku dengan kategori ini tidak akan terhapus,
    // hanya id_kategori-nya akan menjadi NULL. Ini aman.
    $query = "DELETE FROM tabel_kategori WHERE id_kategori = $id_kategori";
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['pesan_sukses_kategori'] = "Kategori berhasil dihapus.";
    } else {
        $_SESSION['pesan_error_kategori'] = "Gagal menghapus kategori.";
    }
    redirect('kategori.php');
}

// Cek pesan dari session
if(isset($_SESSION['pesan_sukses_kategori'])) {
    $pesan_sukses = $_SESSION['pesan_sukses_kategori'];
    unset($_SESSION['pesan_sukses_kategori']);
}

// Ambil semua data kategori untuk ditampilkan
$result = mysqli_query($koneksi, "SELECT * FROM tabel_kategori ORDER BY nama_kategori ASC");

require_once 'templates/header.php';
?>

<div class="d-flex">
    <?php require_once 'templates/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h3>Kelola Kategori Buku</h3>
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
                    <div class="card-header">Tambah Kategori Baru</div>
                    <div class="card-body">
                        <form action="kategori.php" method="POST">
                            <div class="mb-3">
                                <label for="nama_kategori" class="form-label">Nama Kategori</label>
                                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" required>
                            </div>
                            <button type="submit" name="tambah_kategori" class="btn btn-primary">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Daftar Kategori</div>
                    <div class="card-body">
                         <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Kategori</th>
                                        <th width="150px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                        <?php while ($kategori = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($kategori['nama_kategori']); ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editKategoriModal" data-id="<?php echo $kategori['id_kategori']; ?>" data-nama="<?php echo htmlspecialchars($kategori['nama_kategori']); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="kategori.php?hapus=<?php echo $kategori['id_kategori']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center">Belum ada data kategori.</td>
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

<!-- Modal Edit Kategori -->
<div class="modal fade" id="editKategoriModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="kategori.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_kategori" id="edit_id_kategori">
                    <div class="mb-3">
                        <label for="edit_nama_kategori" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="edit_nama_kategori" name="nama_kategori_edit" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_kategori" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script untuk mengisi modal edit secara dinamis
document.addEventListener('DOMContentLoaded', function () {
    var editKategoriModal = document.getElementById('editKategoriModal');
    editKategoriModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var nama = button.getAttribute('data-nama');
        
        var modalTitle = editKategoriModal.querySelector('.modal-title');
        var modalInputId = editKategoriModal.querySelector('#edit_id_kategori');
        var modalInputNama = editKategoriModal.querySelector('#edit_nama_kategori');

        modalInputId.value = id;
        modalInputNama.value = nama;
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>