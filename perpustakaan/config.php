<?php
// Mulai sesi hanya jika belum ada sesi yang aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'perpustakaan_db';

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Fungsi untuk membersihkan input
function clean_input($koneksi, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($koneksi, $data);
}

// Fungsi untuk redirect halaman
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// FUNGSI PAGINASI BARU YANG LEBIH PINTAR
function generate_pagination_links($halaman_aktif, $total_halaman, $url_params = '') {
    if ($total_halaman <= 1) {
        return '';
    }

    $html = '<nav aria-label="Navigasi Halaman"><ul class="pagination justify-content-center">';

    // Tombol Previous
    $prev_class = ($halaman_aktif <= 1) ? 'disabled' : '';
    $html .= '<li class="page-item ' . $prev_class . '"><a class="page-link" href="?page=' . ($halaman_aktif - 1) . $url_params . '">Previous</a></li>';

    // Logika nomor halaman yang lebih pintar
    $window = 2; // Jumlah halaman di kiri dan kanan halaman aktif
    if ($total_halaman <= (2 * $window + 5)) {
        // Tampilkan semua jika total halaman sedikit
        for ($i = 1; $i <= $total_halaman; $i++) {
            $active_class = ($i == $halaman_aktif) ? 'active' : '';
            $html .= '<li class="page-item ' . $active_class . '"><a class="page-link" href="?page=' . $i . $url_params . '">' . $i . '</a></li>';
        }
    } else {
        // Tampilkan halaman pertama
        $html .= '<li class="page-item ' . ($halaman_aktif == 1 ? 'active' : '') . '"><a class="page-link" href="?page=1' . $url_params . '">1</a></li>';

        // Ellipsis kiri (...)
        if ($halaman_aktif > $window + 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        // Halaman di sekitar halaman aktif
        $start = max(2, $halaman_aktif - $window);
        $end = min($total_halaman - 1, $halaman_aktif + $window);

        for ($i = $start; $i <= $end; $i++) {
            $active_class = ($i == $halaman_aktif) ? 'active' : '';
            $html .= '<li class="page-item ' . $active_class . '"><a class="page-link" href="?page=' . $i . $url_params . '">' . $i . '</a></li>';
        }

        // Ellipsis kanan (...)
        if ($halaman_aktif < $total_halaman - $window - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        // Tampilkan halaman terakhir
        $html .= '<li class="page-item ' . ($halaman_aktif == $total_halaman ? 'active' : '') . '"><a class="page-link" href="?page=' . $total_halaman . $url_params . '">' . $total_halaman . '</a></li>';
    }

    // Tombol Next
    $next_class = ($halaman_aktif >= $total_halaman) ? 'disabled' : '';
    $html .= '<li class="page-item ' . $next_class . '"><a class="page-link" href="?page=' . ($halaman_aktif + 1) . $url_params . '">Next</a></li>';

    $html .= '</ul></nav>';
    return $html;
}
?>