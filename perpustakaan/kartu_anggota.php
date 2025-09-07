<?php
require_once 'config.php';
// Autentikasi hanya untuk admin dan petugas
if (!isset($_SESSION['id_pengguna'])) {
    redirect('login.php');
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('anggota.php');
}
$id_anggota = (int)$_GET['id'];

// Ambil data anggota dari database
$query = "SELECT * FROM tabel_anggota WHERE id_anggota = $id_anggota";
$result = mysqli_query($koneksi, $query);
if (mysqli_num_rows($result) == 0) {
    redirect('anggota.php'); // Jika anggota tidak ditemukan
}
$anggota = mysqli_fetch_assoc($result);

// Ambil nama perpustakaan dari pengaturan
$query_pengaturan = "SELECT nama_perpustakaan FROM tabel_pengaturan WHERE id_setting = 1";
$result_pengaturan = mysqli_query($koneksi, $query_pengaturan);
$pengaturan = mysqli_fetch_assoc($result_pengaturan);
$nama_perpustakaan = $pengaturan ? $pengaturan['nama_perpustakaan'] : 'Perpustakaan Daerah';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Anggota - <?php echo htmlspecialchars($anggota['nama_lengkap']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="d-flex justify-content-center mb-4 d-print-none">
        <a href="anggota.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>Cetak Kartu</button>
    </div>

    <!-- Desain Kartu Anggota -->
    <div class="kartu-anggota shadow mx-auto">
        <div class="kartu-header">
            <div class="logo">
                <i class="fas fa-book-open fa-2x"></i>
            </div>
            <div class="nama-perpus">
                <h5 class="mb-0">KARTU ANGGOTA</h5>
                <p class="mb-0"><?php echo htmlspecialchars($nama_perpustakaan); ?></p>
            </div>
        </div>
        <div class="kartu-body">
            <div class="foto-profil">
                <img src="https://placehold.co/100x120/EFEFEF/333333?text=Foto" alt="Foto Anggota">
            </div>
            <div class="data-diri">
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($anggota['nama_lengkap']); ?></p>
                <p><strong>No. Anggota:</strong> <?php echo htmlspecialchars($anggota['nim']); ?></p>
                <p><strong>Prodi:</strong> <?php echo htmlspecialchars($anggota['prodi']); ?></p>
            </div>
        </div>
        <div class="kartu-footer">
            <svg id="barcode"></svg>
        </div>
    </div>
</div>

<!-- Library JsBarcode -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    // Generate barcode dari NIM anggota
    JsBarcode("#barcode", "<?php echo htmlspecialchars($anggota['nim']); ?>", {
        format: "CODE128",
        lineColor: "#000",
        width: 2,
        height: 40,
        displayValue: true
    });
</script>

</body>
</html>