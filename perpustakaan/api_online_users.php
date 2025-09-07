<?php
require_once 'config.php';

// Menghitung pengguna yang aktif dalam 5 menit terakhir
$query = "SELECT COUNT(id_aktivitas) as total_online FROM tabel_aktivitas_online WHERE waktu_terakhir > (NOW() - INTERVAL 5 MINUTE)";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

// Mengirimkan data dalam format JSON
header('Content-Type: application/json');
echo json_encode(['online_users' => (int)$data['total_online']]);
?>