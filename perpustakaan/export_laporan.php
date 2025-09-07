<?php
require_once 'config.php';
// Autentikasi hanya untuk admin dan petugas
if (!isset($_SESSION['id_pengguna'])) {
    // Jika tidak ada sesi, kita tidak bisa melakukan apa-apa, jadi hentikan saja.
    exit('Akses ditolak.');
}

// Ambil rentang tanggal dari URL, atau gunakan default bulan ini
$tanggal_mulai = isset($_GET['mulai']) ? $_GET['mulai'] : date('Y-m-01');
$tanggal_selesai = isset($_GET['selesai']) ? $_GET['selesai'] : date('Y-m-t');

// Siapkan nama file untuk di-download
$nama_file = "laporan_peminjaman_" . date('d-m-Y', strtotime($tanggal_mulai)) . "_sd_" . date('d-m-Y', strtotime($tanggal_selesai)) . ".csv";

// Atur header HTTP agar browser menganggap ini sebagai file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nama_file . '"');

// Buka "output" PHP sebagai file untuk ditulis
$output = fopen('php://output', 'w');

// Tulis baris header (judul kolom) ke dalam file CSV
fputcsv($output, array('No', 'Nama Peminjam', 'Judul Buku', 'Tanggal Pinjam', 'Tanggal Kembali', 'Tanggal Dikembalikan', 'Status', 'Denda (Rp)'));

// Query untuk mengambil data transaksi sesuai rentang tanggal
$query_transaksi = "
    SELECT tt.*, ta.nama_lengkap, tb.judul_buku 
    FROM tabel_transaksi tt
    JOIN tabel_anggota ta ON tt.id_anggota = ta.id_anggota
    JOIN tabel_buku tb ON tt.id_buku = tb.id_buku
    WHERE tt.tgl_pinjam BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'
    ORDER BY tt.tgl_pinjam ASC
";
$result_transaksi = mysqli_query($koneksi, $query_transaksi);

if ($result_transaksi && mysqli_num_rows($result_transaksi) > 0) {
    $no = 1;
    // Loop setiap baris data dari database
    while ($transaksi = mysqli_fetch_assoc($result_transaksi)) {
        // Siapkan data untuk baris CSV
        $baris_csv = array(
            $no++,
            $transaksi['nama_lengkap'],
            $transaksi['judul_buku'],
            date('d-m-Y', strtotime($transaksi['tgl_pinjam'])),
            date('d-m-Y', strtotime($transaksi['tgl_kembali'])),
            $transaksi['tgl_dikembalikan'] ? date('d-m-Y', strtotime($transaksi['tgl_dikembalikan'])) : '-',
            $transaksi['status'],
            $transaksi['denda']
        );
        // Tulis baris ke file CSV
        fputcsv($output, $baris_csv);
    }
}

// Tutup file output
fclose($output);
// Hentikan script agar tidak ada output lain
exit();
?>